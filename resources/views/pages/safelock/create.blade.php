@extends('../layout/' . $layout)

@section('subhead')
    <title>Safelock - Fundz</title>
@endsection

@section('subcontent')
<div>
    <form action="/safelock/lock" method="post">
        @csrf
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12">
                <!-- BEGIN: Form Layout -->
                <div class="intro-y box p-5">
                    <div>
                        <p class="mb-4 text-bold text-blue-600 text-xl">Create a safelock</p>
                        <label for="safelock_amount" class="form-label">Name Of Safelock</label>
                        @error('name')
                        <span style="color: red">{{$message}}</span>
                        @enderror
                        <div class="input-group">
                            <!-- <div id="input-group-3" class="input-group-text"></div> -->
                            <input name="name" id="safelock_name" type="text" class="form-control w-full"
                                   placeholder="E.g School fees 💰💰💰💰" value="{{old('name')}}">
                        </div>
                        <label for="safelock_amount" class="form-label">Enter an amount</label>
                        @error('amount')
                        <span style="color: red">{{$message}}</span>
                        @enderror
                        <div class="input-group">
                            <div id="input-group-3" class="input-group-text">₦</div>
                            <input name="amount" id="safelock_amount" type="number" class="form-control w-full"
                                   placeholder="Example 2000" value="{{old('amount')}}">
                        </div>
                        <label for="crud-form-1" class="mt-2 form-label">Source of fundz</label>
                        <div class="input-group">
                            <label for="fundz_source"></label>
                            <select id="fundz_source" name="source" class="form-control w-full">
                                <option value="" active><--- Where you keep 'em Fundz?----></option>
                                <option value="main_wallet">Your main wallet balance ₦{{ number_format(auth()->user()->balance,0,'.',',') }}</option>
                            </select>
                        </div>

                        <label for="safelock_amount" class="form-label">Return date <span style="color: blue">(Fundz would be returned 12AM on the set date)</span></label>
                        @error('return_date')
                        <span style="color: red">{{$message}}</span>
                        @enderror
                        <div class="input-group">
                            <input name="return_date" id="return_date" type="date" class="form-control w-full"
                                   value="{{old('return_date')}}">
                        </div>

                        @error('description')
                        <span style="color: red">{{$message}}</span>
                        @enderror
                        <label for="crud-form-1" class="mt-2 form-label">Description</label>
                        <div class="input-group">
                            <textarea name="description" id="crud-form-1" type="text" class="form-control w-full"
                                      placeholder="Some Christmas fundz🎄🎅🏽" required>{{old('description')}}</textarea>
                        </div>
                        <br>
                    </div>
                    <div class="text-right mt-5">
                        <button type="submit" class="btn btn-primary w-27">Lock Fundz</button>
                    </div>
                </div>
                <!-- END: Form Layout -->
            </div>
        </div>
    </form>
</div>

@endsection
