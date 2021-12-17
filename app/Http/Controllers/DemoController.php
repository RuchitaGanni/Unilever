<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
/*use Maatwebsite\Excel\Facades\Excel;*/
use DB;
use View;
use Redirect;
use Excel;
use Exception;
use Session;
use File;


class DemoController extends Controller
{

    public function __construct(Request $request) {

        $this->_request=$request;
    }
    public function download($type){

        $xls_list = ["ClosedProcessOrdersTemplate"];
         ob_end_clean(); //for overcome the unformated data.
         ob_start();
        if(in_array($type,$xls_list)){
            $file= public_path(). "/download/".$type.".xlsx";
        $headers = array(
              'Content-Type: application/vnd.ms-excel',
            );
        return response()->download($file, $type.'.xlsx', $headers);        
        }
        else{
            $file= public_path(). "/download/".$type.".csv";
        $headers = array(
              'Content-Type: application/pdf',
            );
        return response()->download($file, $type.'.csv', $headers);    
        }
        
    
    	
    }
    public function reset(){
        Session::forget('success');
        Session::forget('Fail');
        $x=Session::get('success');
        return $x;
    	//echo "in retured function";
    	
    }

    public function excel_import(){
        
        return view::make('excel_import');
    }
    public function export_Data(){

        try {
        Session::forget('success');
        Session::forget('Fail');
        $allowed_Extensions = ['xls','xlsx'];
        if( $this->_request->file('files')){
          $extension= File::extension($this->_request->file('files')->getClientOriginalName());
            if( !in_array($extension, $allowed_Extensions))
            {
                Session::put('Fail','Invalid file extension /type ');
                    return Redirect::to('excel_import');
            }
        }
    
        if(empty($this->_request->file('files'))){
            Session::put('Fail','PO are not available');
            return Redirect::to('excel_import');
             
        }
        $path = $this->_request->file('files')->getRealPath();
        $tempArray = Excel::load($path, function($reader) {})->get()->toArray();
        
        $firstrow = Excel::load($path, function($reader) {})->get();
        $firstrow = $firstrow->toArray();
        $excelheaders = array_keys($firstrow[0]);
        $headers =["order"];
        if(count(array_diff($headers, $excelheaders)) >0){
          Session::put('Fail','Please check , Missing column - Order');
            return Redirect::to('excel_import');
        }
        $po_number=[];

        foreach ($tempArray as $key=>$value){
            if(!empty($value['order'])){
            array_push($po_number,$value['order']);
            }
        }

        $cnt=DB::table('production_orders as po')
        ->where('po.is_confirm','=',1)
        ->whereIn('po.erp_doc_no',$po_number)
        ->orWhereIn('po.eseal_doc_no',$po_number)->
        get()->toArray();

        $cnt2=DB::table('production_orders as po')
        ->whereIn('po.erp_doc_no',$po_number)
        ->orWhereIn('po.eseal_doc_no',$po_number)->
        get()->toArray();
        if(count($cnt2)==0){
             Session::put('Fail','Orders are not available in eSeal');
            return Redirect::to('excel_import');
        }

        if(count($cnt) == count($cnt2)){
          Session::put('Fail','Available Orders are closed already');
            return Redirect::to('excel_import');  
        }

        
        DB::table('production_orders as po')
        ->whereIn('po.erp_doc_no',$po_number)
        ->orWhereIn('po.eseal_doc_no',$po_number)
        ->update(['po.is_confirm'=>1]);

        $message= "updated available orders";
        $status=1;
       // return json_encode(['Status'=>$status,'Message'=>$message]);
        Session::put('success','PO are closed successfully');
        return Redirect::to('excel_import');
        
    }catch(Exception $e){
         
        $status=0;
        $message = $e->getMessage();
        
        Session::put('Fail',$message);
        return Redirect::to('excel_import');
    //return json_encode(['Status'=>$status,'Message'=>$message]);
    }
         
        
    }
}
