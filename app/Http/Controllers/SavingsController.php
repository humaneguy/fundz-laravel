<?php


namespace App\Http\Controllers;


use App\Http\Request\SaveMoneyRequest;
use App\Http\Requests\WithdrawRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Facades\UpdatedRave as Flutterwave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Spatie\Activitylog\Facades\CauserResolver;
use function Symfony\Component\String\b;

class SavingsController extends Controller
{
    public function __construct(public UserRepository $user)
    {
    }

    public function index()
    {
        return view('pages/savings');
    }

    public function save(SaveMoneyRequest $request)
    {
        //This generates a payment reference
        $reference = Flutterwave::generateReference();

        // Enter the details of the payment
        $data = [
            'payment_options' => 'card,banktransfer',
            'amount' => $request->amount,
            'email' => auth()->user()->email,
            'tx_ref' => $reference,
            'currency' => "NGN",
            'redirect_url' => route('callback'),
            'customer' => [
                'email' => auth()->user()->email,
                "phonenumber" => 'I never get',
                "name" => auth()->user()->name
            ],

            "customizations" => [
                "description" => "Fundz dey na",
                "title" => 'Topup your wallet 🤑. E sure for you👊'
            ]
        ];

        $payment = Flutterwave::initializePayment($data);

        if ($payment['status'] !== 'success') {
            // notify something went wrong
            return;
        }

        return redirect($payment['data']['link']);
//        $this->user->topupWallet(userId: auth()->id(), amount: $request->amount);
//        return redirect(route('dashboard-overview-1'));
    }


    public function withdraw()
    {
        $balance = $this->user->getWalletBalance(userId: auth()->id())['balance'];
        return view('pages.withdraw',compact('balance'));
    }

    public function withdrawFundz(WithdrawRequest $request)
    {
        $balance = $this->user->getWalletBalance(auth()->id())['balance'];


        if ($this->fundzNoDey(amount: $request->amount, balance: $balance)) {
            session()->flash('error', 'Fundz you no get! 😕');
            return redirect()->back();
        }

        $reference = Flutterwave::generateReference();

        $data = $this->getWithdrawalPayload($request, $reference);

        $transfer = Flutterwave::transfers()->initiate($data);

        if($transfer['status'] == 'success')
        {
            $this->user->withdraw(amount: $request->amount, userId: auth()->id());

            session()->flash('success', 'Withdrawal successful🙌🏻');

            CauserResolver::setCauser($this->user->getUser(auth()->id()));
            activity()
                ->withProperty('created_at', now())
                ->log("Withdrew ₦{$request->amount} from wallet");

            return redirect(route('dashboard-overview-1'));
        }
        else
        {
            session()->flash('error', 'Withdrawal Failed, No vex boss!😕');
            return redirect()->back();
        }
    }

    public function callback()
    {
        $transactionID = Flutterwave::getTransactionIDFromCallback();
        if (!$transactionID) {
            session()->flash('error', 'An error has occurred!');
            return redirect()->route('dashboard');
        }
        $transactionDetails = Flutterwave::verifyTransaction($transactionID);
        $this->user->topupWallet(amount: $transactionDetails['data']['amount'], userId: auth()->id());
        \session()->flash('success', 'Payment compeleted successfully!');
        return redirect()->route('dashboard');
//        dd($transactionDetails);
        // Get the transaction from your DB using the transaction reference (txref)
        // Check if you have previously given value for the transaction. If you have, redirect to your successpage else, continue
        // Confirm that the $transactionDetails['transactionDetails']['status'] is 'successful'
        // Confirm that the currency on your db transaction is equal to the returned currency
        // Confirm that the db transaction amount is equal to the returned amount
        // Update the db transaction record (including parameters that didn't exist before the transaction is completed. for audit purpose)
        // Give value for the transaction
        // Update the transaction to note that you have given value for the transaction
        // You can also redirect to your success page from here

    }

    /**
     * @param WithdrawRequest $request
     * @param $balance
     * @return bool
     */
    public function fundzNoDey($amount, $balance): bool
    {
        return $amount > (int)$balance;
    }

    /**
     * @param WithdrawRequest $request
     * @param $reference
     * @return array
     */
    public function getWithdrawalPayload(WithdrawRequest $request, $reference): array
    {
        $withdrawableFundz = (98 * $request->amount) / 100;

        return [
            "account_bank" => $request->bank_code,
            "account_number" => $request->account_number,
            "amount" => $withdrawableFundz,
            "narration" => "Transfer from Fundz",
            "currency" => "NGN",
            "debit_currency" => "NGN",
            'reference' => $reference
        ];
    }
}
