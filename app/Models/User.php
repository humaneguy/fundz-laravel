<?php

namespace App\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWallets;
use Faker\Provider\Payment;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\UserReferral;

class User extends Authenticatable implements Wallet,MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, HasWallet, HasWallets, UserReferral;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'gender', 'active', 'phone_number','referred_by', 'payment_hash'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /**
     * The getter that return accessible URL for user photo.
     *
     * @var array
     */

    public function paymentLink(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->morphOne(PaymentLink::class, 'imageable');
    }
}
