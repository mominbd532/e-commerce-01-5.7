<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Category;
use App\Country;
use App\Coupon;
use App\DeliveryAddress;
use App\Order;
use App\OrderProduct;
use App\Product;
use App\ProductsAttribute;
use App\ProductsImage;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;


class ProductController extends Controller
{
    public function addProduct(Request $request){

        if($request->isMethod('post')){
            $data =$request->all();
            //echo '<pre>'; print_r($data); die;
            if(empty($data['category_id'])){
                return redirect()->back()->with('message1','Under category ID missing');
            }
            $product = new Product;
            $product->category_id = $data['category_id'];
            $product->product_name = $data['product_name'];
            $product->product_code = $data['product_code'];
            $product->product_color = $data['product_color'];

            if(!empty($data['description'])){
                $product->description = $data['description'];
            }
            else{
                $product->description = '';
            }

            if(!empty($data['care'])){
                $product->care = $data['care'];
            }
            else{
                $product->care = '';
            }

            $product->price = $data['price'];

            //upload image

            if($request->hasFile('image')){
                $image_tmp =Input::file('image');
                if($image_tmp->isValid()){
                    $extension =$image_tmp->getClientOriginalExtension();
                    $fileName = rand(111,9999).'.'.$extension;
                    $large_file_path ='images/backend_images/products/large/'.$fileName;
                    $medium_file_path ='images/backend_images/products/medium/'.$fileName;
                    $small_file_path ='images/backend_images/products/small/'.$fileName;

                    //Resize Image

                    Image::make($image_tmp)->save($large_file_path);
                    Image::make($image_tmp)->resize(600,600)->save($medium_file_path);
                    Image::make($image_tmp)->resize(300,300)->save($small_file_path);

                    //Save fie name
                    $product->image = $fileName;
                }

            }

            if(empty($data['status'])){
                $status = 0;
            }else{
                $status =1;
            }

            $product->status = $status;

            $product->save();
            return redirect()->to('/admin/view-product')->with('message','Product has been added successfully');

        }

        $categories =Category::where(['parent_id'=>0])->get();
        $categories_dropdown = "<option selected disabled> Select</option>";
        foreach ($categories as $cat){
            $categories_dropdown .= "<option value='".$cat->id."'>".$cat->name."</option>";
            $sub_categories =Category::where(['parent_id'=>$cat->id])->get();
            foreach ($sub_categories as $sub_cat){
                $categories_dropdown .="<option value='".$sub_cat->id."'>&nbsp;--&nbsp;".$sub_cat->name."</option>";
            }
        }

        return view('admin.products.add_products',compact('categories_dropdown'));

    }

    public function viewProduct(){
        $products = Product::all();
        foreach ($products as $key => $val){
            $category_name = Category::where(['id'=>$val->category_id])->first();
            $products[$key]->category_name = $category_name->name;
        }
        return view('admin.products.view_products',compact('products'));
    }

    public function editProduct(Request $request, $id){

        if($request->isMethod('post')){
            $data =$request->all();
            //echo '<pre>'; print_r($data); die;

            //upload image

            if($request->hasFile('image')){
                $image_tmp =Input::file('image');
                if($image_tmp->isValid()){
                    $extension =$image_tmp->getClientOriginalExtension();
                    $fileName = rand(111,9999).'.'.$extension;
                    $large_file_path ='images/backend_images/products/large/'.$fileName;
                    $medium_file_path ='images/backend_images/products/medium/'.$fileName;
                    $small_file_path ='images/backend_images/products/small/'.$fileName;

                    //Resize Image

                    Image::make($image_tmp)->save($large_file_path);
                    Image::make($image_tmp)->resize(600,600)->save($medium_file_path);
                    Image::make($image_tmp)->resize(300,300)->save($small_file_path);



                }
            }
            else {
                $fileName = $data['current_image'];
            }


            if(empty($data['description'])){
                $data['description'] = "";
            }

            if(empty($data['care'])){
                $data['care'] = "";
            }

            if(empty($data['status'])){
                $status = 0;
            }else{
                $status =1;
            }



            Product::where(['id'=>$id])->update([
                'category_id'=>$data['category_id'],
                'product_name'=>$data['product_name'],
                'product_code'=>$data['product_code'],
                'product_color'=>$data['product_color'],
                'description'=>$data['description'],
                'care'=>$data['care'],
                'price'=>$data['price'],
                'image'=>$fileName,
                'status'=>$status,
            ]);

            return redirect()->back()->with('message','Product Updated Successfully');

        }

        $products = Product::where(['id'=> $id])->first();

        $categories =Category::where(['parent_id'=>0])->get();
        $categories_dropdown = "<option selected disabled> Select</option>";
        foreach ($categories as $cat){
            if($cat->id == $products->category_id){
                $selected = "selected";
            }
            else{
                $selected = "";
            }
            $categories_dropdown .= "<option value='".$cat->id."' ".$selected.">".$cat->name."</option>";
            $sub_categories =Category::where(['parent_id'=>$cat->id])->get();
            foreach ($sub_categories as $sub_cat){
                if($sub_cat->id == $products->category_id){
                    $selected = "selected";
                }
                else{
                    $selected = "";
                }
                $categories_dropdown .="<option value='".$sub_cat->id."' ".$selected.">&nbsp;--&nbsp;".$sub_cat->name."</option>";
            }
        }


        return view('admin.products.edit_products',compact('products','categories_dropdown'));


    }

    public  function deleteProduct($id){
        Product::where(['id'=>$id])->delete();
        return redirect()->back()->with('message1','Product Deleted Successfully');

    }

    public function deleteProductImage($id){

        $product =Product::where(['id'=>$id])->first();

        //Image path

        $large_image_path = "images/backend_images/products/large/";
        $medium_image_path = "images/backend_images/products/medium/";
        $small_image_path = "images/backend_images/products/small/";

        if(file_exists($large_image_path.$product->image)){
            unlink($large_image_path.$product->image);
        }

        if(file_exists($medium_image_path.$product->image)){
            unlink($medium_image_path.$product->image);
        }

        if(file_exists($small_image_path.$product->image)){
            unlink($small_image_path.$product->image);
        }


        Product::where(['id'=>$id])->update(['image'=>""]);
        return redirect()->back()->with('message1','Image Deleted Successfully');
    }

    public function addImages(Request $request,$id){
        $product = Product::with('attributes')->where(['id'=>$id])->first();
        /*$product = json_decode(json_encode($product));*/
        /* echo '<pre>'; print_r($product); die;*/

        if($request->isMethod('post')){
            $data =$request->all();
            //echo '<pre>'; print_r($data); die;
            if($request->hasFile('image')){
                $files =$request->file('image');
                foreach ($files as $file){

                    $image = new ProductsImage;
                    $extension =$file->getClientOriginalExtension();
                    $fileName = rand(111,9999).'.'.$extension;
                    $large_file_path ='images/backend_images/products/large/'.$fileName;
                    $medium_file_path ='images/backend_images/products/medium/'.$fileName;
                    $small_file_path ='images/backend_images/products/small/'.$fileName;

                    //Resize Image

                    Image::make($file)->save($large_file_path);
                    Image::make($file)->resize(600,600)->save($medium_file_path);
                    Image::make($file)->resize(300,300)->save($small_file_path);
                    $image->images = $fileName;
                    $image->product_id = $id;
                    $image->save();

                }
            }

            return redirect()->back()->with('message','Image added successfully');
        }

        $productAltImages =ProductsImage::where(['product_id'=>$id])->get();
        return view('admin.products.add_images',compact('product','productAltImages'));

    }

    public function deleteAltProductImage($id){

        $product =ProductsImage::where(['id'=>$id])->first();

        //Image path

        $large_image_path = "images/backend_images/products/large/";
        $medium_image_path = "images/backend_images/products/medium/";
        $small_image_path = "images/backend_images/products/small/";

        if(file_exists($large_image_path.$product->images)){
            unlink($large_image_path.$product->images);
        }

        if(file_exists($medium_image_path.$product->images)){
            unlink($medium_image_path.$product->images);
        }

        if(file_exists($small_image_path.$product->images)){
            unlink($small_image_path.$product->images);
        }


        ProductsImage::where(['id'=>$id])->delete();
        return redirect()->back()->with('message1','Product Alt Image Deleted Successfully');
    }

    public function addAttribute(Request $request,$id){
        $product = Product::with('attributes')->where(['id'=>$id])->first();
        /*$product = json_decode(json_encode($product));*/
       /* echo '<pre>'; print_r($product); die;*/

        if($request->isMethod('post')){
            $data =$request->all();
            //echo '<pre>'; print_r($data); die;

            foreach ($data['sku']as $key=>$val){
                if(!empty($val)){
                    //Prevent Duplicate SKU Check

                    $skuCount =ProductsAttribute::where('sku',$val)->count();

                    if($skuCount>0){
                        return redirect()->back()->with('message1','SKU already exist, please try another SKU');

                    }

                    //Prevent Duplicate Size Check

                    $sizeCount =ProductsAttribute::where(['product_id'=>$id,'size'=>$data['size'][$key]])->count();

                    if($sizeCount>0){
                        return redirect()->back()->with('message1',''.$data['size'][$key].'   Size already exist for this product, please try another Size');

                    }



                    $attribute = new ProductsAttribute;
                    $attribute->product_id = $id;
                    $attribute->sku = $val;
                    $attribute->size = $data['size'][$key];
                    $attribute->price = $data['price'][$key];
                    $attribute->stock = $data['stock'][$key];
                    $attribute->save();

                }

            }

            return redirect()->back()->with('message','Product attribute added successfully');
        }
        return view('admin.products.add_attribute',compact('product'));

    }

    public function editAttribute(Request $request,$id){
        $data =$request->all();
        //echo "<pre>"; print_r($data); die;
        foreach ($data['idAttr'] as $key => $attr){
            ProductsAttribute::where(['id'=>$data['idAttr'][$key]])->update([
                'price' =>$data['price'][$key],
                'stock' =>$data['stock'][$key],
            ]);
        }

        return redirect()->back()->with('message','Product Attributes Updated Successfully');

    }

    public function deleteAttribute($id){
        ProductsAttribute::where(['id'=>$id])->delete();
        return redirect()->back()->with('message1','Product attribute deleted successfully');
    }

    public function products($url){
        $countCategory = Category::where(['url'=>$url,'status'=>1])->count();
        if($countCategory==0){
            abort(404);
        }

        $categories =Category::with('categories')->where(['parent_id'=>0])->get();

        $categoriesDetails =Category::where(['url'=>$url])->first();

        if($categoriesDetails->parent_id==0){
            $subCategories =Category::where(['parent_id'=>$categoriesDetails->id])->get();

            foreach ($subCategories as $subCat){
                $cat_ids[] =$subCat->id;
            }

            $products =Product::whereIn('category_id',$cat_ids)->where('status',1)->get();
        }
        else{
            //for sub categories
            $products =Product::where(['category_id'=>$categoriesDetails->id])->where('status',1)->get();

        }


        return view('products.listing')->with(compact('categories','products','categoriesDetails'));
    }

    public function product($id){

        $productCount =Product::where(['id'=>$id,'status'=>1])->count();

        if($productCount==0){
            abort(404);
        }

        $categories =Category::with('categories')->where(['parent_id'=>0])->get();

        $productDetails =Product::with('attributes')->where(['id'=>$id])->first();

        $relatedProduct =Product::where('id','!=',$id)->where(['category_id'=>$productDetails->category_id])->get();

        //echo "<pre>"; print_r($relatedProduct); die;

        /*foreach ($relatedProduct->chunk(3) as $chunk){
            foreach ($chunk as $item){
                echo $item; echo "<br>";
            }

            echo "<br><br><br>";
        }

        die;*/

        $productAltImages =ProductsImage::where('product_id',$id)->get();

        $totalStock =ProductsAttribute::where('product_id',$id)->sum('stock');


        return view('products.details')->with(compact('categories','productDetails','productAltImages','totalStock','relatedProduct'));

    }

    public function getProductPrice(Request $request){
        $data =$request->all();
       /* echo "<pre>"; print_r($data);die;*/
        $proArr = explode("-",$data['idSize']);
        /*echo $proArr[0]; echo $proArr[1]; die;*/
        $productAttribute =ProductsAttribute::where(['product_id'=>$proArr[0],'size'=>$proArr[1]])->first();

        echo $productAttribute->price;
        echo "#";
        echo $productAttribute->stock;


    }

    public function addToCart(Request $request){

        Session::forget('CouponAmount');
        Session::forget('CouponCode');

        $data =$request->all();

        //echo "<pre>"; print_r($data); die;

        if(empty(Auth::user()->email)){
            $data['user_email'] = "";
        }else{
            $data['user_email'] = Auth::user()->email;
        }

        $sessionId =Session::get('session_id');

        if(empty($sessionId)){
            $sessionId =str_random(40);
            Session::put('session_id',$sessionId);
        }



        $sizeArr = explode("-",$data['size']);

        $productCount =Cart::where([
            'product_id'=>$data['product_id'],
            'product_color'=>$data['product_color'],
            'size'=> $sizeArr[1],
            'session_id'=> $sessionId,
        ])->count();

        if($productCount>0){
            return redirect()->back()->with('message1','Product already exists on cart');
        }else{
            $getSKU =ProductsAttribute::select('sku')->where(['product_id'=>$data['product_id'],'size'=> $sizeArr[1]])->first();


            Cart::insert([
                'product_id'=>$data['product_id'],
                'product_name'=>$data['product_name'],
                'product_code'=>$getSKU->sku,
                'product_color'=>$data['product_color'],
                'price'=>$data['price'],
                'size'=> $sizeArr[1],
                'quantity'=>$data['quantity'],
                'user_email'=> $data['user_email'],
                'session_id'=> $sessionId,
            ]);
        }



        return redirect()->to('/cart')->with('message','Products Added to Cart successfully');



    }

    public function cart(){

        if(Auth::check()){
            $sessionId =Session::get('session_id');
            $user_email=Auth::user()->email;
            $userCart =Cart::where(['user_email'=>$user_email])->orWhere(['session_id'=>$sessionId])->get();

        }else{
            $sessionId =Session::get('session_id');
            $userCart =Cart::where(['session_id'=>$sessionId])->get();
        }



        foreach($userCart as $key => $product){
            $productDetails =Product::where('id',$product->product_id)->first();
            $userCart[$key]->image = $productDetails->image;
        }

        return view('products.cart')->with(compact('userCart'));
    }

    public function cartDeleteProduct($id){

        Session::forget('CouponAmount');
        Session::forget('CouponCode');

        Cart::where('id',$id)->delete();
        return redirect()->back()->with('message','Your product deleted from cart successfully');

    }

    public function updateProductCartQuantity($id,$quantity){
        Session::forget('CouponAmount');
        Session::forget('CouponCode');

        $getCartDetails =Cart::where('id',$id)->first();
        $getAttributesStock =ProductsAttribute::where(['sku'=>$getCartDetails->product_code])->first();
        $updatedQuantity =$getCartDetails->quantity+$quantity;
        if($getAttributesStock->stock >= $updatedQuantity){
            Cart::where('id',$id)->increment('quantity',$quantity);
            return redirect()->back()->with('message','Product quantity updated successfully');

        }else{
            return redirect()->back()->with('message1','Required Quantity is Not Available');

        }


    }

    public function applyCoupon(Request $request){
        Session::forget('CouponAmount');
        Session::forget('CouponCode');

        $data =$request->all();
        //echo "<pre>"; print_r($data); die;

        $countCoupon =Coupon::where(['coupon_code'=>$data['coupon_code']])->count();

        if($countCoupon == 0){
            return redirect()->back()->with('message1','Your coupon code is invalid');
        }else{

            $couponDetails =Coupon::where(['coupon_code'=>$data['coupon_code']])->first();
            //echo "<pre>"; print_r($couponDetails); die;

            if($couponDetails->status ==0){
                return redirect()->back()->with('message1','Your coupon in not active');
            }

            $expire_date =$couponDetails->expire_date;
            $currentDate =date('Y-m-d');
            if($expire_date < $currentDate){
                return redirect()->back()->with('message1','Your coupon is expired');
            }


            $sessionId =Session::get('session_id');
            $user_email =Auth::user()->email;
            $userCart =Cart::where(['session_id'=>$sessionId])->orWhere(['user_email'=>$user_email])->get();

            $totalAmount = 0;

            foreach($userCart as $item){
                $totalAmount =$totalAmount +($item->price * $item->quantity);
            }

            if($couponDetails->amount_type=="fixed"){
                $couponAmount =$couponDetails->amount;
            }else{
                $couponAmount =$totalAmount * ($couponDetails->amount/100);
            }

            Session::put('CouponAmount',$couponAmount);
            Session::put('CouponCode',$data['coupon_code']);

            return redirect()->back()->with('message','Coupon apply successfully');




        }

    }

    public function checkOut(Request $request){
        $user_id =Auth::user()->id;
        $user_email =Auth::user()->email;
        $user_details =User::find($user_id);
        $countries =Country::all();
        $shippingCount =DeliveryAddress::where('user_id',$user_id)->count();
        if($shippingCount > 0){
            $shippingDetails =DeliveryAddress::where('user_id',$user_id)->first();
        }else{
            DeliveryAddress::create([
                'user_id' => $user_id,
                'user_email' => $user_email,
                'name' => "",
                'address' => "",
                'city' =>"",
                'state' =>"",
                'country' =>"",
                'pincode' =>"",
                'mobile' => ""
            ]);

            $shippingDetails =DeliveryAddress::where('user_id',$user_id)->first();
        }

        //Update Cart Table

        $sessionId =Session::get('session_id');
        Cart::where('session_id',$sessionId)->update(['user_email' => $user_email]);


        if($request->isMethod('post')){
            $data =$request->all();

            if(empty($data['billing_name'])||empty($data['billing_address'])||
               empty($data['billing_city'])||empty($data['billing_state'])||
               empty($data['billing_country'])||empty($data['billing_pincode'])||
                empty($data['billing_mobile'])||empty($data['shipping_name'])||
                empty($data['shipping_address'])||empty($data['shipping_city'])||
                empty($data['shipping_state'])||empty($data['shipping_country'])||
                empty($data['shipping_pincode'])||empty($data['shipping_mobile'])
            ){
                return redirect()->back()->with('message1','Any field must not be empty');
            }else{
                if($shippingCount > 0){
                    DeliveryAddress::where('user_id',$user_id)->update([
                        'name' => $data['shipping_name'],
                        'address' => $data['shipping_address'],
                        'city' =>$data['shipping_city'],
                        'state' =>$data['shipping_state'],
                        'country' =>$data['shipping_country'],
                        'pincode' =>$data['shipping_pincode'],
                        'mobile' =>$data['shipping_mobile']
                    ]);
                }else{
                    DeliveryAddress::create([
                        'user_id' => $user_id,
                        'user_email' => $user_email,
                        'name' => $data['shipping_name'],
                        'address' => $data['shipping_address'],
                        'city' =>$data['shipping_city'],
                        'state' =>$data['shipping_state'],
                        'country' =>$data['shipping_country'],
                        'pincode' =>$data['shipping_pincode'],
                        'mobile' =>$data['shipping_mobile']
                    ]);
                    echo "Added successfully"; die;

                }
                return redirect()->action('ProductController@orderDetails');
            }

        }

        return view('products.check_out',compact('user_details','countries','shippingDetails','shippingDetails'));

    }

    public function orderDetails(){
        $user_id =Auth::user()->id;
        $user_email =Auth::user()->email;
        $user_details =User::find($user_id);
        $sessionId =Session::get('session_id');
        $shippingDetails =DeliveryAddress::where('user_id',$user_id)->first();
        $userCart =Cart::where(['session_id'=>$sessionId])->orWhere(['user_email'=>$user_email])->get();

        foreach($userCart as $key => $product){
            $productDetails =Product::where('id',$product->product_id)->first();
            $userCart[$key]->image = $productDetails->image;
        }


        return view('products.order_details',compact('user_email','user_details','shippingDetails','userCart'));
    }

    public function placeOrder(Request $request){
        if($request->isMethod('post')){
            $data =$request->all();
            $user_id =Auth::user()->id;
            $user_email =Auth::user()->email;
            $shipping_details =DeliveryAddress::where('user_id',$user_id)->first();

            if(empty(Session::get('CouponAmount'))){
                $couponAmount = 0.0;
            }else{
                $couponAmount =Session::get('CouponAmount');
            }


            if(empty(Session::get('CouponCode'))){
                 $couponCode = "";
            }else{
                $couponCode =Session::get('CouponCode');
            }

            if(empty($data['shipping_charge'])){
                $data['shipping_charge'] = 0.0;
            }



            $order = new Order;
            $order->user_id = $user_id;
            $order->user_email = $user_email;
            $order->name = $shipping_details->name;
            $order->address = $shipping_details->address;
            $order->city = $shipping_details->city;
            $order->state = $shipping_details->state;
            $order->pincode = $shipping_details->pincode;
            $order->country = $shipping_details->country;
            $order->mobile = $shipping_details->mobile;
            $order->shipping_charge = $data['shipping_charge'];
            $order->coupon_code = $couponCode;
            $order->coupon_amount = $couponAmount;
            $order->order_status = "new";
            $order->payment_method = $data['payment_method'];
            $order->grand_total = $data['grand_total'];
            $order->save();

            $order_id =DB::getPdo()->lastInsertId();

            $cartProducts =Cart::where('user_email',$user_email)->get();
            foreach ($cartProducts as $cartProduct){
                $cartPro = new OrderProduct;
                $cartPro->order_id =$order_id;
                $cartPro->user_id =$user_id;
                $cartPro->product_id =$cartProduct->product_id;
                $cartPro->product_code =$cartProduct->product_code;
                $cartPro->product_name =$cartProduct->product_name;
                $cartPro->product_size =$cartProduct->size;
                $cartPro->product_color =$cartProduct->product_color;
                $cartPro->product_price =$cartProduct->price;
                $cartPro->product_qty =$cartProduct->quantity;
                $cartPro->save();
            }

            Session::put('order_number',$order_id);
            Session::put('total_amount',$data['grand_total']);

            if($data['payment_method']== "COD"){
                $email =$user_email;
                $productDetails =Order::with('orders')->where('id',$order_id)->first();
                $productDetails =json_decode(json_encode($productDetails),true);
//                echo  '<pre>'; print_r($productDetails); die;
                $userDetails =User::where('id',$user_id)->first();
                $userDetails =json_decode(json_encode($userDetails),true);
//                echo  '<pre>'; print_r($userDetails); die;

                $messageData = [
                    'email'=> $email,
                    'name'=> $shipping_details->name,
                    'order_id'=> $order_id,
                    'productsDetails' =>$productDetails,
                    'userDetails' =>$userDetails,

                ];

                Mail::send('emails.order',$messageData,function($message)use($email){
                    $message->to($email)->subject('Order info for  E-Shopper');
                });

                return redirect()->to('/thanks');
            }else{
                return redirect()->to('/paypal');
            }





        }

    }

    public function thanks(){
        $user_email =Auth::user()->email;
        Cart::where('user_email',$user_email)->delete();
        Session::forget('CouponAmount');
        return view('orders.thanks');
    }

    public function paypal(){
        $user_email =Auth::user()->email;
        Cart::where('user_email',$user_email)->delete();
        Session::forget('CouponAmount');
        return view('orders.paypal');
    }

    public function paypalReturn(){
        return view('orders.paypal_return');
    }

    public function paypalCancelReturn(){
        return view('orders.paypal_cancel_return');
    }


    public function userOrders(){
        $user_id= Auth::user()->id;
        $orders =Order::with('orders')->where('user_id',$user_id)->orderBy('id','DESC')->get();
//        $orders =json_decode(json_encode($orders));
//        echo "<pre>"; print_r($orders); die;

        return view('orders.user_orders',compact('orders'));
    }

    public function userOrderedProducts($id){
        $user_id =Auth::user()->id;
        $order_details =Order::with('orders')->where(['id'=>$id,'user_id'=>$user_id])->first();
        if(empty($order_details)){
            abort(404);
        }
//        $order_details =json_decode(json_encode($order_details));
//        echo "<pre>"; print_r($order_details); die;


        return view('orders.ordered_products',compact('order_details'));
    }

    public function viewOrders(){
        $orders =Order::with('orders')->orderBy('id','DESC')->get();
//        $orders =json_decode(json_encode($orders));
//        echo "<pre>"; print_r($orders); die;

        return view('admin.orders.view_orders',compact('orders'));
    }

    public function viewOrderDetails($id){
        $orderDetails =Order::with('orders')->where('id',$id)->first();
//        $orderDetails =json_decode(json_encode($orderDetails));
//        echo "<pre>"; print_r($orderDetails); die;
        $user_id =$orderDetails->user_id;
        $user_details =User::where('id',$user_id)->first();


        return view('admin.orders.view_order_details',compact('orderDetails','user_details'));

    }

    public function updateOrderStatus(Request $request){
        if($request->isMethod('post')){
            $data =$request->all();
            Order::where('id',$data['order_id'])->update(['order_status'=>$data['status']]);
            return redirect()->back()->with('message','Order updated successfully');
        }
    }





}
