@extends('layouts.frontLayout.front_design')

@section('content')
    <section id="form" style="margin-top: 20px;"><!--form-->
        <div class="container">
            <div class="row">
                <form action="#">
                    <div class="col-sm-4 col-sm-offset-1">
                        <div class="login-form"><!--login form-->
                            <h2>Billing To</h2>
                            <div class="form-group">
                                <input type="text" class="form-control" name="billing_name" id="billing_name" value="{{$user_details->name}}"  placeholder="Billing Name">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" name="billing_address" id="billing_address" value="{{$user_details->address}}"  placeholder="Billing Address">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" name="billing_city" id="billing_city" value="{{$user_details->city}}"  placeholder="Billing City">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" name="billing_state" id="billing_state" value="{{$user_details->state}}"  placeholder="Billing State">
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="billing_country" id="billing_country" >
                                    <option value="">Select Country</option>
                                    @foreach($countries as $country)
                                        <option value="{{$country->country_name}}" {{$country->country_name == $user_details->country ? "selected":"" }} > {{$country->country_name}} </option>

                                    @endforeach
                                </select>

                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" name="billing_pincode" id="billing_pincode" value="{{$user_details->pincode}}"  placeholder="Billing Pincode">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" name="billing_mobile" id="billing_mobile" value="{{$user_details->mobile}}"  placeholder="Billing Mobile">
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="billToShip">
                                <label class="form-check-label" for="billToShip">Shipping Address same as Billing Address</label>
                            </div>


                        </div><!--/login form-->
                    </div>
                    <div class="col-sm-1">
                        <h2></h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="signup-form"><!--sign up form-->

                            <h2>Shipping To</h2>
                            <div class="form-group">
                                <input type="text" class="form-control" name="shipping_name" id="shipping_name"  placeholder="Shipping Name">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" name="shipping_address" id="shipping_address"  placeholder="Shipping Address">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" name="shipping_city" id="shipping_city"  placeholder="Shipping City">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" name="shipping_state" id="shipping_state"  placeholder="Shipping State">
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="shipping_country" id="shipping_country" >
                                    <option value="">Select Country</option>
                                    @foreach($countries as $country)
                                        <option value="{{$country->country_name}}"> {{$country->country_name}} </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" name="shipping_pincode" id="shipping_pincode"  placeholder="Shipping Pincode">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" name="shipping_mobile" id="shipping_mobile"  placeholder="Shipping Mobile">
                            </div>
                            <div class="form-group">
                                <input type="submit" class="form-control" value="Next">
                            </div>

                        </div><!--/sign up form-->
                    </div>
                </form>



            </div>
        </div>
    </section><!--/form-->
@endsection