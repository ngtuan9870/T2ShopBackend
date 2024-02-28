<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\OrderWarehouse;
use App\Models\OrderItemWareHouse;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Models\product;
use App\Models\Category;
use App\Models\ProductWH;
use App\Models\DeliveryBill;
use App\Models\DetailDeliveryBill;
use App\Models\Inventory;
use App\Models\BallotImport;
use App\Models\DetailBallotImport;
use App\Models\HangTon;
use App\Models\BallotExport;
use App\Models\DetailBallotExport;
use App\Models\CTPX_LN;
use App\Models\HistoryPrice;
use App\Models\StoreWHInventory;
use App\Models\StoreWarehouse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    public function show(){
        $warehouse=Warehouse::all();
        // $capacity1= array();
        // foreach($warehouse as $w){
        //     $inventory=Inventory::where('wh_id',$w->warehouse_id)->sum('amount');
        //     array_push($capacity1,$inventory);
        // }
        return response()->json(['message'=>'success','warehouse'=>$warehouse]);
    }
    public function add(Request $request){
       
    }
    public function getSupplier(){
        $supplier=Supplier::all();
        return response()->json(['message'=>'success','supplier'=>$supplier]);
    }
    public function addOrder(Request $request){
        $orderWarehouse= new OrderWarehouse();
        $orderWarehouse->user = $request->user_id;
        $orderWarehouse->warehouse_id = $request->supplier_id;
        $orderWarehouse->status = 0;
        $orderWarehouse->cost = $request->cost;
        $orderWarehouse->money = $request->money;
        $orderWarehouse->debt = $request->debt;
        $orderWarehouse->time = $request->time;
        $orderWarehouse->save();
        // $data2 = array();
        $data = json_decode($request->item,true);
        // foreach($data as $item){
        //     array_push($data2,$item['STT']);
        // }
        foreach($data as $item){
            $orderitem = new OrderItemWareHouse();
            $orderitem->product_id = $item['Mã hàng'];
            $orderitem->name_item = $item['Tên hàng'];
            $orderitem->unit_item = $item['ĐVT'];
            $orderitem->amount_item = $item['Số lượng'];
            $orderitem->cost_item = $item['Đơn giá'];
            $orderitem->price_item = $item['Thành tiền'];
            $orderitem->orderWh_id = $orderWarehouse->orderWh_id;
            $orderitem->save();
        }
        $user=User::where('user_id',$request->user_id)->first();
        $supplier = Supplier::where('supplier_id',$request->supplier_id)->first();

        $_SESSION['email'] = $supplier->supplier_email;
        $data2 = [
            'data' => $data,
            'orderWh_id'=>$orderWarehouse->orderWh_id,
            'user_name'=> $user->user_name,
            'cost'=> $orderWarehouse->cost,
            'money'=> $orderWarehouse->money,
            'debt'=>$orderWarehouse->debt,
            'time'=>$orderWarehouse->time,
        ];
        Mail::send('mailorderWarehouse', $data2, function($message){
            $message->from('ntuan9870@gmail.com', 'T2Shop');
            $message->to($_SESSION['email']);
            $message->subject('Đơn đặt hàng từ T2shop!');
        });
        return response()->json(['message'=>'success']);
    }

    public function addOrder1(Request $request){
        $orderWarehouse= new OrderWarehouse();
        $orderWarehouse->user = $request->user_id;
        $orderWarehouse->warehouse_id = $request->supplier_id;
        $orderWarehouse->status = 0;
        $orderWarehouse->cost = $request->cost;
        $orderWarehouse->money = $request->money;
        $orderWarehouse->debt = $request->debt;
        $orderWarehouse->time = $request->time;
        $orderWarehouse->save();
        $data = json_decode($request->item,true);
        $amount = json_decode($request->amount,true);
        $product_price = json_decode($request->product_price,true);
        for($i=0;$i<count($data);$i++){
            $orderitem = new OrderItemWareHouse();
            $orderitem->product_id=$data[$i]['prod_id'];
            $orderitem->name_item = $data[$i]['prod_name'];
            $orderitem->unit_item = $data[$i]['unit'];
            $orderitem->amount_item = $amount[$i];
            $orderitem->cost_item =  $data[$i]['price'];
            $orderitem->price_item = $product_price[$i];
            $orderitem->orderWh_id = $orderWarehouse->orderWh_id;
            $orderitem->save();
        }
        $user=User::where('user_id',$request->user_id)->first();
        $supplier = Supplier::where('supplier_id',$request->supplier_id)->first();

        $_SESSION['email'] = $supplier->supplier_email;
        $data2 = [
            'data' => $data,
            'amount' => $amount,
            'product_price' => $product_price,
            'orderWh_id'=>$orderWarehouse->orderWh_id,
            'user_name'=> $user->user_name,
            'cost'=> $orderWarehouse->cost,
            'money'=> $orderWarehouse->money,
            'debt'=>$orderWarehouse->debt,
            'time'=>$orderWarehouse->time,
        ];
        Mail::send('mailorderWarehouse1', $data2, function($message){
            $message->from('ntuan9870@gmail.com', 'T2Shop');
            $message->to($_SESSION['email']);
            $message->subject('Đơn đặt hàng từ T2shop!');
        });
        return response()->json(['message'=>'success']);
    }

    public function getOrderWareHouse(){
        $orderWH=OrderWarehouse::all();
        return response()->json(['orderWH'=>$orderWH]);
    }
    public function getOrderWHID($id){
        $orderWH=OrderWarehouse::where('orderWh_id','like','%'.$id.'%')->first();
        return response()->json(['orderWH'=>$orderWH]);
    }
    public function removeorder(Request $request){
        // OrderWarehouse::destroy($request->orderWh_id);
        $OrderWH=OrderWarehouse::find($request->orderWh_id);
        $OrderWH->status=4;
        $OrderWH->save();
        return response()->json(['message'=>'success']);
    }
    public function updateorder(Request $request){
        $OrderWH=OrderWarehouse::find($request->orderWh_id);
        $OrderWH->status=1;
        $OrderWH->save();
        return response()->json(['message'=>'success']);
    }
    public function getdetailOrder(Request $request){
        // $orderWh = OrderWarehouse::find($request->orderWh_id);
        $orderWh = OrderWarehouse::where('orderWh_id',$request->orderWh_id)->get();
        foreach( $orderWh as $data){
            $user=User::where('user_id',$data->user)->select('user_name')->first();
            $supplier=Supplier::where('supplier_id',$data->warehouse_id)->select('supplier_name')->first();
        }
        $detail = OrderItemWareHouse::where('orderWh_id',$request->orderWh_id)->get();
        return response()->json(['message'=>'success','orderWh'=>$orderWh,'detail'=>$detail,'user'=>$user,'supplier'=>$supplier]); 
    }
    public function postStatus(Request $request){
        // $OrderWH=OrderWarehouse::find($request->orderWh_id);
        $OrderWH= new OrderWarehouse;
        $arr['status']=$request->status;
        $OrderWH::where('orderWh_id',$request->orderWh_id)->update($arr);

        // $OrderWH=OrderWarehouse::find($request->orderWh_id);
        // $OrderWH->status=$request->status;
        // $OrderWH->save();
        return response()->json(['message'=>'success']);
    }

    public function search(Request $request){
        $product= ProductWH::where('prod_name','LIKE','%'.$request->key.'%')->get();
        $inventory = array();
        foreach($product as $p){
            $amount=Inventory::find($p->prod_id);
            array_push($inventory,$amount);
        }
        return response()->json(['product'=>$product,'inventory'=>$inventory]);
    }
    public function search2(Request $request){
        $product= DB::table('hang_ton')->join('products', 'products.product_id','=','hang_ton.product_id')->where('hang_ton.warehouse_id',$request->warehouse_id)->where('products.product_name','LIKE','%'.$request->key.'%')->select('products.*')->get();
        return response()->json(['message'=>'success','products'=>$product]);
    }
    public function getCategory(){
        $category=Category::all();
        return response()->json(['category'=>$category]);
    }
    public function checkname(Request $request){
        if(ProductWH::where('prod_name',$request->prod_name)->exists()){
            return response()->json(['message'=>'error']);
        }
    }
    public function postProductWH(Request $request){
        $validator = Validator::make($request->all(),[
            'prod_name' =>['unique:productwh,prod_name'],
            
        ],[
            'product_id.unique' => 'Tên sản phẩm đã tồn tại !',
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()->all()]);
        }
        $warehouse=Warehouse::where('warehouse_id',$request->wh_id)->first();
        if($request->prod_amount>$warehouse->empty){
            return response()->json(['error'=>'Kho không đủ chứa']);
        }else{
            $productwH =  new ProductWH;
            // $productwH->prod_id = $request->prod_id;
            $productwH->prod_name = $request->prod_name;
            // $productwH->prod_amount = $request->prod_amount;
            $productwH->unit = "cái";
            $productwH->price = $request->price;
            $productwH->prod_price = $request->prod_price;
            $productwH->cate_id = $request->cate_id;
            $productwH->wh_id = $request->wh_id;
            $productwH->save();
            $inventory = new Inventory;
            $inventory->id=$productwH->prod_id;
            $inventory->wh_id=$request->wh_id;
            $inventory->amount=$request->prod_amount;
            $inventory->save();
            $warehouse=Warehouse::find($request->wh_id);
            $warehouse->empty-=$request->prod_amount;
            $warehouse->save();
            return response()->json(['message'=>'success']);
        }
    }
    public function getProductWH(Request $request){
        $product=ProductWH::where('wh_id',$request->wh_id)->get();
        $inventory = array();
        foreach($product as $p){
            $amount=Inventory::find($p->prod_id);
            array_push($inventory,$amount);
        }
        return response()->json(['product'=>$product,'inventory'=>$inventory]);
    }
    public function updateProductWHamount(Request $request){
        $product=Inventory::find($request->prod_id);
        $warehouse=Warehouse::find($product->wh_id);
        if($warehouse->empty>$request->prod_amount){
            $product->amount=$request->prod_amount;
            $product->save();
            return response()->json(['message'=>'success']);
        }else{
            return response()->json(['message'=>'error']);
        }
       
    }
    public function updateProductWHprice(Request $request){
        $product=ProductWH::find($request->prod_id);
        $product->prod_price=$request->prod_price;
        $product->save();
        return response()->json(['message'=>'success']);
    }
    public function addWareHouse(Request $request){
        $validator = Validator::make($request->all(),[
            'warehouse_id' =>['unique:warehouse,warehouse_id'],
            
        ],[
            'warehouse_id.unique' => 'Mã kho  đã tồn tại !',
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()->all()]);
        }
        $warehouse=new Warehouse();
        $warehouse->warehouse_id=$request->warehouse_id;
        $warehouse->warehouse_name=$request->warehouse_name;
        $warehouse->warehouse_address=$request->warehouse_address;
        $warehouse->capacity=$request->capacity;
        $warehouse->unit= "cái";
        $warehouse->empty=$request->capacity;
        $warehouse->save();
        return response()->json(['message'=>'success']);
    }
    public function getwarehouse(){
        $warehouses=Warehouse::all();
        return response()->json(['message'=>'success','warehouses'=>$warehouses]);
    }
    public function addDeliverybill(Request $request){
        $deliveryBill= new DeliveryBill();
        $deliveryBill->user=$request->user_id;
        $deliveryBill->date=$request->date;
        $deliveryBill->save();
        $data = json_decode($request->item,true);
        $data1 = json_decode($request->prod_amount,true);
        //  $data1 =$request->prod_amount;
         for($i=0;$i<count($data);$i++){
            $detail= new DetailDeliveryBill();
            $detail->productid=$data[$i]['prod_id'];
            $detail->deliverybillid=$deliveryBill->deliverybill_id;
            $detail->amount=$data1[$i];
            $detail->price=$data[$i]['prod_price'];
            $detail->save();
        }
        return response()->json(['message'=>'success','data'=>$data1]);
    }
    public function getDeliverybill(){
        $deliveryBill=DeliveryBill::all();
        $users = array();
        foreach($deliveryBill as $d){
            $name = User::find($d->user);
            array_push($users,$name);
        }
        return response()->json(['message'=>'success','deliveryBill'=>$deliveryBill,'users'=>$users]);
    }
    public function getDetailDeBill(Request $request){
        $detail=DetailDeliveryBill::where('deliverybillid',$request->db_id)->get();
        $nameproduct= array();
        foreach($detail as $p){
            $name = ProductWH::find($p->productid);
            array_push($nameproduct,$name);
        }
        return response()->json(['message'=>'success','detail'=>$detail,'nameproduct'=>$nameproduct]);
    }
    public function minusamount(Request $request){
        $inventory=Inventory::find($request->prod_id);
        $inventory->amount-=$request->amount;
        $inventory->save();
        $wh=Warehouse::where('warehouse_id',$inventory->wh_id)->first();
        $wh->empty+=$request->amount;
        $wh->save();
        return response()->json(['message'=>'success']);
    }
    public function plusamount(Request $request){
        $inventory=Inventory::find($request->prod_id);
        $inventory->amount+=$request->amount;
        $inventory->save();
        $wh=Warehouse::where('warehouse_id',$inventory->wh_id)->first();
        $wh->empty-=$request->amount;
        $wh->save();
        return response()->json(['message'=>'success']);
    }
    public function checkcapcity(Request $request){
        $warehouse=Warehouse::where('warehouse_id',$request->wh_id)->first();
        // $amount=Inventory::where('wh_id',$request->wh_id)->sum('amount');
        if($request->prod_amount>($warehouse->empty)){
            return response()->json(['message'=>'error']);
        }
    }
    public function getdetailWH(Request $request){
        $warehouse=Warehouse::where('warehouse_id',$request->wh_id)->first();
        return response()->json(['message'=>'success','warehouse'=>$warehouse]);
    }
    public function EditWareHouse(Request $request){
        $wh=Warehouse::find($request->warehouse_id);
        $wh->warehouse_id=$request->warehouse_id;
        $wh->warehouse_name=$request->warehouse_name;
        $wh->warehouse_address=$request->warehouse_address;
        $wh->capacity=$request->capacity;
        $wh->empty=$request->empty;
        $wh->save();
        return response()->json(['message'=>'success']);
    }
    public function Removewh(Request $request){
        Warehouse::destroy($request->wh_id);
        return response()->json(['message'=>'success']);
    }

    public function importProductWH(Request $request){
        $data = json_decode($request->product, true);
        $bi = new BallotImport();
        $bi->user_id = $request->user_id;
        $bi->warehouse_id = $request->wh_id;
        $bi->sum_amount = $request->sum_amount;
        $bi->sum_product = $request->sum_product; 
        $bi->supplier_id = $request->supplier_id;
        $bi->save();
        foreach($data as $de){
            $dbi = new DetailBallotImport();
            $dbi->bi_id = $bi->bi_id;
            $dbi->product_id = $de['product']['product_id'];
            $dbi->amount = $de['amount'];
            $dbi->price = $de['price'];
            $dbi->exported = 0; 
            $dbi->save();
            $ht = HangTon::where('product_id',$de['product']['product_id'])->where('warehouse_id',$request->wh_id)->first();
            if(!$ht){
                $ht = new HangTon();
                $ht->warehouse_id = $request->wh_id;
                $ht->product_id = $de['product']['product_id'];
                $ht->amount = $de['amount'];
            }else{
                $ht->amount += $de['amount'];
            }
            $ht->save();
            $wh = WareHouse::find($request->wh_id);
            if(!$wh){
                return response()->json(['error'=>'Đã xảy ra lỗi!']);
            }
            $wh->empty = $wh->empty - $de['amount'];
            $wh->save();
        }
        return response()->json(['message'=>'success']);
    }
    public function getAllBI(){
        $bis = BallotImport::orderBy('updated_at', 'DESC')->get();
        return response()->json(['message'=>'success', 'bis'=>$bis]);
    }
    public function getAllBDIByBIID(Request $request){
        $dbis = DetailBallotImport::where('bi_id',$request->bi_id)->join('products', 'products.product_id', '=', 'detail_ballot_import.product_id')->get();
        return response()->json(['message'=>'success', 'dbis'=>$dbis]);
    }
    public function getAllBIEligible(Request $request){
        $bis = BallotImport::join('detail_ballot_import','ballotimports.bi_id','=','detail_ballot_import.bi_id')->where('ballotimports.warehouse_id',$request->wh_id)->where('detail_ballot_import.product_id',$request->product_id)->get();
        return response()->json(['message'=>'success', 'bis'=>$bis]);
    }
    public function checkOutOfProduct(Request $request){
        $dbi = DetailBallotImport::where('bi_id',$request->bi_id)->where('product_id',$request->product_id)->first();
        if(((int)$request->ae+(int)$dbi->exported)>(int)($dbi->amount)){
            return response()->json(['message'=>'fail']);
        }
        return response()->json(['message'=>'success', 'dbi'=>$dbi->amount]);
    }
    public function changeAmountElement(Request $request){
        $dbi = DetailBallotImport::where('bi_id',$request->bi_id)->where('product_id',$request->product_id)->first();
        if(((int)$request->ae+(int)$dbi->exported)>(int)($dbi->amount)){
            return response()->json(['message'=>'fail', 'max'=>(int)$dbi->amount-(int)$dbi->exported]);
        }
        return response()->json(['message'=>'success', 'dbi'=>$dbi->amount]);
    }
    public function addBE(Request $request){
        $data = json_decode($request->dbes, true);
        $be = new BallotExport();
        $be->user_id = $request->user_id;
        $be->sum_amount = $request->sum_amount;
        $be->store_wh_id = $request->store_wh_id;
        $be->wh_id = $request->wh_id;
        $be->save();
        foreach($data as $de){
            $dbe = new DetailBallotExport();
            $dbe->be_id = $be->be_id;
            $dbe->product_id = $de['product_id'];
            $dbe->amount = $de['amount'];
            // $dbe->price = $de['price'];
            $dbe->save();
            foreach($de['ctpx_ln'] as $c){
                $cl = new CTPX_LN();
                $cl->dbe_id = $dbe['dbe_id'];
                $cl->bi_id = $c['bi_id'];
                $cl->amount = $c['amount'];
                $cl->save();
                $dbi = DetailBallotImport::where('bi_id',$c['bi_id'])->where('product_id',$de['product_id'])->first();
                $dbi->exported=$dbi->exported + $c['amount'];
                $dbi->save();
                $bi=BallotImport::find($c['bi_id']);
                $ht = HangTon::where('warehouse_id',$bi->warehouse_id)->where('product_id',$de['product_id'])->first();
                $ht->amount -=  $c['amount'];
                if($ht->amount <= 0){
                    $ht->delete(); 
                }else{
                    $ht->save();
                }
                $wh=WareHouse::find($bi->warehouse_id);
                $wh->empty+=$c['amount'];
                $wh->save();
            }
            // $hp = new HistoryPrice();
            // $hp->product_id=$de['product_id'];
            // $hp->product_price=$de['price'];
            // $hp->save();
            // $p = product::find($de['product_id']);
            // $p->product_price = $de['price'];
            // $p->product_amount = $p->product_amount + $de['amount'];
            // $p->save();
            $storeInventory = StoreWHInventory::where('product_id',$de['product_id'])->where('store_wh_id',$request->store_wh_id)->first();
            if($storeInventory){
                $storeInventory->amount += $de['amount'];
            }else{
                $storeInventory = new StoreWHInventory();
                $storeInventory->store_wh_id = $request->store_wh_id;
                $storeInventory->product_id = $de['product_id'];
                $storeInventory->amount = $de['amount'];
            }
            $storeInventory->save();
            $store_wh = StoreWarehouse::where('store_wh_id',$storeInventory->store_wh_id)->first();
            $store_wh->wh_empty-=$de['amount'];
            $store_wh->save();
        }
        return response()->json(['message'=>'success']);
    }
    public function getBallotExport(){
        $bes = BallotExport::orderBy('created_at', 'DESC')->get();
        return response()->json(['message'=>'success', 'bes'=>$bes]);
    }
    public function getAllDBEByBEID(Request $request){
        $dbes = DetailBallotExport::where('be_id',$request->be_id)->join('products', 'products.product_id', '=', 'detail_ballot_export.product_id')->get();
        return response()->json(['message'=>'success', 'dbes'=>$dbes]);
    }
    public function getAllCTPXLN(Request $request){
        $dbes = CTPX_LN::where('dbe_id',$request->dbe_id)->get();
        return response()->json(['message'=>'success', 'dbes'=>$dbes]);
    }
    public function getAllP(Request $request){
        $hts = HangTon::join('products','products.product_id','=','hang_ton.product_id')->where('warehouse_id',$request->wh_id)->get();
        return response()->json(['message'=>'success', 'hts'=>$hts]);
    }
    public function getAllDBIByProductId(Request $request){
        $dbis = DetailBallotImport::where('product_id',$request->product_id)->orderBy('created_at', 'asc')->get();
        return response()->json(['message'=>'success', 'dbis'=>$dbis]);
    }
    public function getAllDWHP(Request $request){
        $dbi = DetailBallotImport::join('ballotimports','detail_ballot_import.bi_id','=','ballotimports.bi_id')->where('detail_ballot_import.product_id',$request->product_id)
        ->where('ballotimports.warehouse_id',$request->wh_id)->orderBy('ballotimports.created_at', 'DESC')->get();
        return response()->json(['message'=>'success', 'dbi'=>$dbi]);
    }
}
