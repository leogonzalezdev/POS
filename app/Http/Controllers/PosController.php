<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;

// use Category;
// use SubCategory;
// use Item;

// use Table;
// use Order;
// use OrderDetail;


class PosController extends Controller
{

    public function href($fun=false,$p1=null,$p2=null){

        if ($p1 && $p2) {
            return $this->$fun($p1);
            // echo "P1 , p2 Test";
        }elseif ($p1) {
            return $this->$fun($p1,$p2);
            // echo "P1 Test";
        }

        return $this->$fun();
    }// #dynamic method Call ( We Dont need to Make New Route for Every Method )


    public function index($view='pos'){

        $variables['categories'] = \App\Category::all();
        $variables['subCategories'] = \App\SubCategory::all();
        $variables['items'] = \App\Item::all();
        $variables['tables'] = \App\Table::all();

        $variables['order'] = $this->get_order_details_item_details(Session::get('order_id'));

        return view('pos.'.$view)->with($variables);

    }// #index Page (MAIN PAGE)


    public function get_items(Request $request){
        // echo dd($request->subCategory_id);

        $variables['items'] = \App\SubCategory::find($request->subCategory_id)->item()->get();
        
        // $variables['SubCategory_id'] = $subCategory_id;
        // echo ($variables['items']);
        return view('pos.item_table')->with($variables);

    } // # get_items();


    public function active_table_select(Request $request)  {  
        
        $table =  \App\Table::find($request->id);
        // dd($request->id);
        
        if ($table->status == "empty") {
            
            $order = \App\Order::create(['table_id'=>$table->id,'status'=>'hold']);

            $table->status = "hold";
            $table->order_id = $order->id;
            $table->save();

            Session::put('active_table',$table->id);
            Session::put('order_id',$order->id);    
            
        }elseif ($table->status == "hold") {
            // return " ";
            // $order = \App\Order::where( ['table_id'=>$table->id, 'status'=>'hold' ] )->first();
            $order = \App\Order::where('table_id', $table->id)->where('status','hold')->first();
            // dd($order->id);
            Session::put('active_table', $table->id);
            Session::put('order_id',$order->id);

        }elseif ($table->status == "unpaid") {
            # code...
        }else{

        }

        $variables['tables'] = \App\Table::all();
        return view('pos.layout.table_select_palette')->with($variables);

    } // active_table_select()


    public function section_order_items(){
        $variables['order'] = $this->get_order_details_item_details(Session::get('order_id'));
        return view('pos.layout.order_items')->with($variables);

    } //section_order_items()


    public function item_add_to_order_details(Request $request){        
        $insert_data = [
            'order_id'=>Session::get('order_id'),
            'table_id'=>Session::get('active_table'),
            'item_id'=>$request->item_id
        ];

        $order_details = \App\OrderDetail::create( $insert_data );
        return ($order_details);
    } // item_add_to_order_details()


    public function check_out(){
        return "Check_Out for Order_id: ".Session::get('order_id')." table_id :".Session::get('active_table');
        // return \App\Order::find(Session::get('order_id'));
    } // check_out()


    public function get_order_details_item_details($order_id){
        $order = \App\Order::find($order_id);
        return ($order) ? $order->order_details() : [];
    }

}