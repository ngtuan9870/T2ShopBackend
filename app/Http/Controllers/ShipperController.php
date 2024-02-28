<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shipper;
use Illuminate\Support\Facades\Validator;
use Nexmo\Laravel\Facade\Nexmo;
use App\Models\Order;
use App\Models\Ship;

class ShipperController extends Controller
{
    public function  add(Request $request){
        $validator = Validator::make($request->all(),[
            'shipper_phone' =>'unique:shipper,shipper_phone|min:9|max:10',
        ],[
            'shipper_phone.unique' => 'Số điện thoại đã tồn tại !',
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()->all()]);
        }
        $shipper= new Shipper;
        $shipper->shipper_phone=$request->shipper_phone;
        $shipper->shipper_name=$request->shipper_name;
        $shipper->shipper_address=$request->shipper_address;
        $shipper->shipper_password=encrypt($request->shipper_password);
        $shipper->save();
        // Nexmo::message()->send([
        //     'to'   => '84'.$request->shipper_phone,
        //     'from' => 'T2SHOP',
        //     'text' => 'Using the facade to send a message.'
        // ]);

        return response()->json(['message'=>"success"]);
    }
    public function getallshipper(){
        $shipper= Shipper::all();
        return response()->json(['message'=>"success",'shipper'=>$shipper]);
    }
    public function remove(Request $request){
        Shipper::destroy($request->shipper_phone);
        return response()->json(['message'=>"success"]);
    }
    public function getdetailSH(Request $request){
        $shipper=Shipper::find($request->sh_phone);
        return response()->json(['shipper'=>$shipper]);
    }
    public function edit (Request $request){
        $validator = Validator::make($request->all(),[
            'newsh_phone' =>'min:9|max:10',
        ],[
            'newsh_phone.min' => 'Số điện thoại không nhỏ hơn 9 số !',
            'newsh_phone.max' => 'Số điện thoại không lớn hơn 10 số !',
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()->all()]);
        }
        $s=Shipper::find($request->sh_phone);
        if($request->newsh_phone!=''){
            if(Shipper::where('shipper_phone',$request->newsh_phone)->exists()){
                return response()->json(['message'=>"Số điện thoại đã tồn tại"]);
            }else{
                $s->shipper_phone=$request->newsh_phone;
            }
        }else{
            $s->shipper_phone=$request->sh_phone;
        }
        $s->shipper_name=$request->sh_name;
        $s->shipper_address=$request->sh_address;
        $s->save();
        return response()->json(['message'=>"success"]);
    }
    public function loginshipper(Request $request){
        $shipper = Shipper::where('shipper_phone',$request->phone)->first();
        if(!$shipper){
            return response()->json(['result'=>'fail']);
        }
        // $password = $shipper->shipper_password;
        $password = decrypt($shipper->shipper_password);
        if($password==$request->password){
            return response()->json(['shipper'=>$shipper,'result'=>'success']);
        }else{
            return response()->json(['result'=>'fail']);
        }
    }
    public function showorders(){
        $orders=Order::where([['status','=',0],['ready','=',1],['ship','=','0']])->orderBy('created_at','DESC')->get();
        return response()->json(['orders'=>$orders]);
    }
    public function addShip(Request $request){
        $ship =new Ship;
        $ship->order_id=$request->order_id;
        $ship->shipper_phone=$request->shipper_phone;
        $ship->status=0;
        $ship->save();
        $order=Order::find($request->order_id);
        $order->ship=1;
        $order->save();
        return response()->json(['message'=>"success"]);
    }
    public function showship(Request $request){
        $ships=Ship::where('shipper_phone',$request->shipper_phone)->get();
        $orders = array();
        foreach($ships as $s){
            $o=Order::find($s->order_id);
            array_push($orders,$o);
        }
        return response()->json(['ships'=>$ships,'orders'=>$orders]);
    }
    public function changePassword(Request $request){
        $shipper = Shipper::find($request->shipper_phone);
        $shipper->shipper_password=encrypt($request->shipper_password);
        $shipper->save();
        return response()->json(['message'=>"success"]);
    }
    public function UpdateOrder(Request $request){
        $order=Order::find($request->order_id);
        $order->status=1;
        $order->save();
        $ship=Ship::where('order_id',$request->order_id)->first();
        $ship->status=1;
        $ship->save();
        $shipper=Shipper::find($request->shipper_phone);
        $shipper->points+=5;
        $shipper->save();
        return response()->json(['message'=>"success"]);
    }
    public function destroyShip(Request $request){
        Ship::destroy($request->ship_id);
        // $ship=Ship::where('order_id',$request->order_id)->first();
        // $ship->delete();
        // $ship->save();
        $order=Order::find($request->order_id);
        $order->ship=0;
        $order->save();
        return response()->json(['message'=>"success"]);
    }
    public function getOrderShipper(Request $request){
        $ships=Ship::where('shipper_phone',$request->sh_phone)->get();
        $orders = array();
        foreach($ships as $s){
            $o=Order::find($s->order_id);
            array_push($orders,$o);
        }
        return response()->json(['ships'=>$ships,'orders'=>$orders]);
    }
    public function forgot(Request $request){
        $request = new Request();
        $request->setUrl('https://d7sms.p.rapidapi.com/secure/send');
        $request->setMethod(HTTP_METH_POST);

        $request->setHeaders([
            'content-type' => 'application/json',
            'x-rapidapi-key' => 'fc7d122dd4msh827675e7091b6e5p1bc642jsn9d10e324a567',
            'x-rapidapi-host' => 'd7sms.p.rapidapi.com'
        ]);

        $request->setBody('{
            "coding": "8",
            "from": "T2Shop",
            "hex-content": "00480065006c006c006f",
            "to": 976132445
        }');

        try {
            $response = $request->send();

            echo $response->getBody();
        } catch (HttpException $ex) {
            echo $ex;
        }
    }
}
