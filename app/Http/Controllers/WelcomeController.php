<?php
namespace App\Http\Controllers;
use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use Illuminate\Http\Request;
use Session;
use DB;
use View;
use Input;
use Validator;
use Redirect;
use Log;
use Mail;
class WelcomeController extends BaseController {        

        public function __construct(RoleRepo $roleAccess, CustomerRepo $custRepoObj,Request $request) {
                $this->roleAccess = $roleAccess;
                $this->custRepoObj = $custRepoObj;
                $this->_request = $request;
               // echo "test"; exit;
             
                // if(!Session::has('userId')){
                //     dd('constructor works');
                //     return Redirect::to('/login');
                // }
        }



	public function index()
	{
        if(!Session::has('azureUser')){
            //dd('no azureUser found in session');
            return Redirect::to('/login');
        }
        
/*            $userInputEmail=strtolower(Session::get('userInputEmail'));*/
        $userInputEmail = isset($_COOKIE['userInputEmail']) ? $_COOKIE['userInputEmail'] : '';
        $roleID = isset($_COOKIE['roleId']) ? $_COOKIE['roleId'] : '';
        $azureData=Session::get('azureUser');
        //dd($$azureData);die;
/*        if($userInputEmail != strtolower($azureData->email))*/
if(urldecode($userInputEmail) != strtolower($azureData->email))   
     {
                     // Session::flash('message', 'This is a message!'); 
//Session::flash('alert-class', 'alert-danger'); 
            // $errorMsg = "User input email mismatched with azure email";
            //dd('email not match');
              Session::put('flash_message','User input email mismatched with azure email');
             return Redirect::to('/logout');


        }
                $result = DB::table('users')
                ->leftJoin('eseal_customer','users.customer_id','=','eseal_customer.customer_id')
                ->where('users.azure_id','=',$azureData->id)
                ->select('users.*')
                ->addSelect('eseal_customer.eseal_erp')
                ->get()->toArray();
                Session::put('userId',$result[0]->user_id);
                    Session::put('userName',$result[0]->username);
                    Session::put('userType',$result[0]->user_type);
                    Session::put('customerId',$result[0]->customer_id);
                    Session::put('esealErp',$result[0]->eseal_erp);
                    Session::put('roleId',$roleID);
                    /*added new*/
                    $cur_loc_id=DB::table('users')->where('user_id','=',$result[0]->user_id)->value('location_id');
                    $cur_loc_name=DB::table('locations')->where('location_id','=',$cur_loc_id)->value('location_name');
                    Session::put('user_cur_loca_name',$cur_loc_name);
/*                Session::put('userInputEmail',$email);
*/        parent::Breadcrumbs(array('Home'=>'#')); 

        $dispatchControlJson = '[{ "Month": "April", "Supplier_Name": "KALAKRITI INFOTECH PVT. LTD.", "Product_Name": "DU 875 PRO", "Material_Code":"1500218", "Betch_no": "40022017", "Sales_PO_No": "6000122101", "Sales_PO_Creation_Date": "April 01,2017", "Sales_PO_Creation_Location": "Gurgaon.Hub", "Sales_PO_Qty": "5720", "Dispatch_Qty": "667", "Dispatch_Date": "April 01,2017", "Differance": "5053","Expected_Delivery_Date":"April 01,2017", "Current_Status": "Dispatched","MRP":"1500.00","total_value":"1000500","TP":"4567897232672334" }, { "Month": "April", "Supplier_Name": "KALAKRITI INFOTECH PVT. LTD.", "Product_Name": "DU 875 PRO", "Material_Code":"1500218", "Betch_no": "40022017", "Sales_PO_No": "6000122101", "Sales_PO_Creation_Date": "April 01,2017", "Sales_PO_Creation_Location": "Gurgaon.Hub", "Sales_PO_Qty": "5720", "Dispatch_Qty": "1450", "Dispatch_Date": "April 01,2017", "Differance": "3603", "Expected_Delivery_Date":"April 01,2017","Current_Status": "Dispatched","MRP":"1500.00","total_value":"2175000","TP":"4567897232678834"}, { "Month": "April", "Supplier_Name": "KALAKRITI INFOTECH PVT. LTD.", "Product_Name": "DU 875 PRO", "Material_Code":"1500218", "Betch_no": "40022017", "Sales_PO_No": "6000122101", "Sales_PO_Creation_Date": "April 01,2017", "Sales_PO_Creation_Location": "Gurgaon.Hub", "Sales_PO_Qty": "5720", "Dispatch_Qty": "3603", "Dispatch_Date": "April 06,2017", "Differance": "0", "Expected_Delivery_Date":"April 01,2017", "Current_Status": "Receive","MRP":"1500.00","total_value":"5404500","TP":"4567897665678834"}, { "Month": "April", "Supplier_Name": "SURYA BATTERIES.", "Product_Name": "EI POWER 750", "Material_Code":"1500212", "Betch_no": "40022017", "Sales_PO_No": "6000125005", "Sales_PO_Creation_Date": "April 03,2017", "Sales_PO_Creation_Location": "Vijaywada", "Sales_PO_Qty": "660", "Dispatch_Qty": "660", "Dispatch_Date": "April 06,2017", "Differance": "0", "Expected_Delivery_Date":"April 04,2017", "Current_Status": "Dispatched","MRP":"1200.00","total_value":"792000","TP":"4567897665678886"}]';
        $inventoryMonitorJson = '[{ "Month": "May", "Supplier_Name": "ADVANCE ELECTRONICS.", "Product_Name": "DU 875 PRO", "Material_Code":"1500218",  "ATP": "5720", "Actaual": "1200", "Differance": "4520","Manufacturer_Date":"May 8,2017","Ege":"20 Days","MRP":"1500.00","total_value":"1800000.00"}, { "Month": "May", "Supplier_Name": "ADVANCE ELECTRONICS.", "Product_Name": "EI POWER 750", "Material_Code":"1500218",  "ATP": "3000", "Actaual": "1740", "Differance": "2260","Manufacturer_Date":"May 15,2017","Ege":"15 Days","MRP":"1500.00","total_value":"1800000.00"}, {"Month": "May", "Supplier_Name": "ADVANCE ELECTRONICS.", "Product_Name": "EI POWER 950", "Material_Code":"1500218",  "ATP": "5720", "Actaual": "3260", "Differance": "2460","Manufacturer_Date":"May 02,2017","Ege":"10 Days","MRP":"1500.00","total_value":"1800000.00"}, { "Month": "May", "Supplier_Name": "AGGARWAL TRANSFORMERS.", "Product_Name": "VEW 400 PLUS", "Material_Code":"1500218",  "ATP": "6000", "Actaual": "5800", "Differance": "200","Manufacturer_Date":"May 15,2017","Ege":"15 Days","MRP":"1500.00","total_value":"1800000.00"},{ "Month": "May", "Supplier_Name": "AGGARWAL TRANSFORMERS.", "Product_Name": "VEW 500 PLUS", "Material_Code":"1500218",  "ATP": "5720", "Actaual": "984", "Differance": "4836","Manufacturer_Date":"April 1,2017","Ege":"30 Days","MRP":"1500.00","total_value":"1800000.00"}]';
        $performaceJson = '[{ "Date": "May 16,2017", "location": "Location 1", "Product_Name": "DU 875 PRO", "Material_Code":"1500218",  "Batch_no": "05212017", "PO_No": "1317601", "shift_no":"A", "line":"Line 1", "Shift_Incharge":"S Kumar", "Machine_no":"M1", "Sync_Date": "May 16,2017","Sync_time":"10:00 AM","Qty":"500"}, {  "Date": "May 16,2017", "location": "location 2", "Product_Name": "DU 875 PRO", "Material_Code":"1500218",  "Batch_no": "05212017", "PO_No": "1317601", "shift_no":"A", "line":"Line 4","Shift_Incharge":"S Kumar", "Machine_no":"M1",  "Sync_Date": "May 16,2017","Sync_time":"10:00 AM","Qty":"500"}, { "Date": "May 16,2017", "location": "Location 1", "Product_Name": "EI POWER 750", "Material_Code":"1500219",  "Batch_no": "05212017", "PO_No": "1317601","shift_no":"A", "line":"Line 3", "Shift_Incharge":"S Kumar", "Machine_no":"M1",  "Sync_Date": "May 16,2017","Sync_time":"10:00 AM","Qty":"500"}, {"Date": "May 16,2017", "location": "Location 3", "Product_Name": "EI POWER 750", "Material_Code":"1500219",  "Batch_no": "05212017", "PO_No": "1317601", "shift_no":"A","line":"Line 2","Shift_Incharge":"S Kumar", "Machine_no":"M1",  "Sync_Date": "May 16,2017","Sync_time":"10:00 AM","Qty":"500"}]';
        $inspectionControlJson='[{ "Date": "May 16,2017", "Inspection_location": "QC 1", "Product_Name": "DU 875 PRO", "Material_Code":"1500218",  "Batch_no": "05212017", "PO_No": "1317601", "shift_no":"A", "line":"Line 1", "Stage": "Stator Mappping","Inspector":"Inspection Person 1", "Pass": "300", "Reject": "0"}, {  "Date": "May 16,2017", "Inspection_location": "QC 2", "Product_Name": "DU 875 PRO", "Material_Code":"1500218",  "Batch_no": "05212017", "PO_No": "1317601", "shift_no":"A", "line":"Line 1","Stage": "Fianl Testing", "Inspector":"Inspection Person 1", "Pass": "299", "Reject": "1"}, { "Date": "May 16,2017", "Inspection_location": "QC 1", "Product_Name": "EI POWER 750", "Material_Code":"1500219",  "Batch_no": "05212017", "PO_No": "1317601","shift_no":"A", "line":"Line 1", "Stage": "Stator Mappping", "Inspector":"Inspection Person 1", "Pass": "276", "Reject": "3"}, {"Date": "May 16,2017", "Inspection_location": "QC 2", "Product_Name": "EI POWER 750", "Material_Code":"1500219",  "Batch_no": "05212017", "PO_No": "1317601", "shift_no":"A","line":"Line 1","Stage": "Fianl Testing", "Inspector":"Inspection Person 1", "Pass": "250", "Reject": "0"}]';
        $inventoryAccountingJson='[{"Location_Code": "6063", "Location":"Kasipur", "Product_Name": "DU 875 PRO", "Material_Code":"1500218", "Manufacturer_Date":"April 20,2017" ,"Qty": "20000", "eSeal_Qty": "19500", "Differance": "500", "eSeal_Location": "Kasipur", "MRP": "1500", "Total_Value": "30000000"},{"Location_Code": "6063", "Location":"Kasipur Depo", "Product_Name": "DU 875 PRO", "Material_Code":"1500218", "Manufacturer_Date":"Jan 8,2017"  ,"Qty": "20000", "eSeal_Qty": "19500", "Differance": "500", "eSeal_Location": "Kasipur", "MRP": "1500", "Total_Value": "30000000"}, {"Location_Code": "6063", "Location":"Kasipur",  "Product_Name": "EI POWER 750", "Material_Code":"1500219", "Manufacturer_Date":"April 20,2017"   ,"Qty": "5000", "eSeal_Qty": "5000", "Differance": "0", "eSeal_Location": "Kasipur", "MRP": "1700", "Total_Value": "8500000"}, {"Location_Code": "6063", "Location":"Kasipur", "Product_Name": "EI POWER 950", "Material_Code":"1500210", "Manufacturer_Date":"April 20,2017"  ,"Qty": "1670", "eSeal_Qty": "1670", "Differance": "0", "eSeal_Location": "Kasipur", "MRP": "1300", "Total_Value": "2171000"}]';
        $dispatchAutomationJson = '[{ "Date": "May 16,2017", "STN":"23456", "Product_Name": "DU 875 PRO", "Material_Code":"1500218",  "Batch_no": "05212017", "Qty": "5000", "Dispatch_Location": "Kasipur Plant", "Status": "Dispatched"}, {"Date": "May 16,2017", "STN":"23456",  "Product_Name": "EI POWER 750", "Material_Code":"1500219",  "Batch_no": "05212017", "Qty": "2000", "Dispatch_Location": "Kasipur Plant", "Status": "Dispatched"}, {"Date": "May 16,2017", "STN":"23456", "Product_Name": "EI POWER 950", "Material_Code":"1500210",  "Batch_no": "05212017", "Qty": "300", "Dispatch_Location": "Kasipur Plant", "Status": "Dispatched"}, {"Date": "May 16,2017", "STN":"23456",  "Product_Name": "EI POWER 950", "Material_Code":"1500210",  "Batch_no": "05212017",  "Qty": "500", "Dispatch_Location": "Kasipur Plant2", "Status": "Dispatched"}]';
        $grnAutomationJson= '[{ "Date": "May 16,2017", "GRN_NO":"23456", "Product_Name": "DU 875 PRO", "Material_Code":"1500218",  "Batch_no": "05212017", "grn_location": "Gurgoan Depo", "Dispatch_Date": "May 13,2017", "Dispatch_Location": "Kasipur Plant", "Status": "Receive","Stock_Age":"9 Days"}, {"Date": "May 16,2017", "GRN_NO":"23456",  "Product_Name": "EI POWER 750", "Material_Code":"1500219",  "Batch_no": "05212017", "grn_location": "Gurgoan Depo", "Dispatch_Date": "May 13,2017", "Dispatch_Location": "KALAKRITI INFOTECH", "Status": "Receive","Stock_Age":"9 Days"}, {"Date": "May 16,2017", "GRN_NO":"23456", "Product_Name": "EI POWER 950", "Material_Code":"1500210",  "Batch_no": "05212017", "grn_location": "Gurgoan Depo", "Dispatch_Date": "May 10,2017", "Dispatch_Location": "SURYA BATTERIES", "Status": "Receive","Stock_Age":"9 Days"}, {"Date": "May 16,2017", "GRN_NO":"23456",  "Product_Name": "EI POWER 950", "Material_Code":"1500210",  "Batch_no": "05212017", "grn_location": "VijayWada", "Dispatch_Date": "May 12,2017", "Dispatch_Location": "SURYA BATTERIES", "Status": "Receive","Stock_Age":"9 Days"}]';
        $pickupautomationJson = '[{"Date": "May 16,2017", "STN":"23456", "Product_Name": "DU 875 PRO", "Material_Code":"1500218",  "Batch_no": "05212017", "Qty": "5000", "Order_Qty": "4500", "Differance": "500", "Location": "Location 1", "Time": "10:30"}, {"Date": "May 16,2017", "STN":"23456",  "Product_Name": "EI POWER 750", "Material_Code":"1500219",  "Batch_no": "05212017", "Qty": "2000", "Order_Qty": "2245", "Differance": "245", "Location": "Location 2", "Time": "8:30"}, {"Date": "May 16,2017", "STN":"23456", "Product_Name": "EI POWER 950", "Material_Code":"1500210",  "Batch_no": "05212017", "Qty": "300", "Order_Qty": "300", "Differance": "0", "Location": "Location 1", "Time": "10:30"}]';
        $putawayJson = '[{"Date": "May 16,2017", "STN":"23456", "Product_Name": "DU 875 PRO", "Material_Code":"1500218",  "Batch_no": "05212017", "Qty": "5000", "Order_Qty": "4500", "Differance": "500", "Location": "Location 1", "Time": "10:30"}, {"Date": "May 16,2017", "STN":"23456",  "Product_Name": "EI POWER 750", "Material_Code":"1500219",  "Batch_no": "05212017", "Qty": "2000", "Order_Qty": "2245", "Differance": "245", "Location": "Location 2", "Time": "8:30"}, {"Date": "May 16,2017", "STN":"23456", "Product_Name": "EI POWER 950", "Material_Code":"1500210",  "Batch_no": "05212017", "Qty": "300", "Order_Qty": "300", "Differance": "0", "Location": "Location 1", "Time": "10:30"}]';
        $returnVerificationJson = '[{"Date": "May 16,2017", "Product_Name": "DU 875 PRO", "Material_Code":"1500218",  "Batch_no": "05212017", "Return_Qty": "5","Invoice_Date" : "April 12,2017" ,"Return_Location": "Location 1", "Reason": "Test Reason","Status": "Pass"},{"Date": "May 16,2017",  "Product_Name": "DU 875 PRO", "Material_Code":"1500218",  "Batch_no": "05212017", "Return_Qty": "5","Invoice_Date" : "April 12,2017" , "Return_Location": "Location 1", "Reason": "Test Reason","Status": "Failed"}, {"Date": "May 16,2017",  "Product_Name": "EI POWER 750", "Material_Code":"1500219",  "Batch_no": "05212017", "Return_Qty": "2","Invoice_Date" : "April 12,2017" ,"Return_Location": "Location 1", "Reason": "Test Reason","Status": "Pass"}, {"Date": "May 16,2017",  "Product_Name": "EI POWER 750", "Material_Code":"1500219",  "Batch_no": "05212017", "Return_Qty": "5","Invoice_Date" : "April 12,2017" ,"Return_Location": "Location 1", "Reason": "Test Reason","Status": "Failed"}, {"Date": "May 16,2017",  "Product_Name": "EI POWER 950", "Material_Code":"1500210",  "Batch_no": "05212017", "Return Qty": "5","Invoice_Date" : "April 12,2017" ,"Return Location": "Location 1", "Reason": "Test Reason","Status": "Failed"}]';
        $secondarySale = '[{ "Date": "May 10,2017", "Product_Name": "DU 875 PRO", "Material_Code":"1500218",  "Distributor_Available_Stock": "5000", "Distributor_Location": "Distributor 1", "Distributor_Sale_Date": "May 1,2017", "Distributor_Sale_Qty": "500","Retailer_Stock": "500", "Retailer_Location": "Retailer 1", "GRN_Date": "May 1,2017", "Retailer_Sale_Qty": "400"}, {  "Date": "May 10,2017", "Product_Name": "EI POWER 750", "Material_Code":"1500219",  "Distributor_Available_Stock": "2000", "Distributor_Location": "Distributor 1", "Distributor_Sale_Date": "May 12,2017", "Distributor_Sale_Qty": "1500","Retailer_Stock": "300", "Retailer_Location": "Retailer 1", "GRN_Date": "May 12,2017", "Retailer_Sale_Qty": "200"},  { "Date": "May 16,2017", "Product_Name": "EI POWER 950", "Material_Code":"1500210",  "Distributor_Available_Stock": "1450", "Distributor_Location": "Distributor 1", "Distributor_Sale_Date": "May 16,2017", "Distributor_Sale_Qty": "200","Retailer_Stock": "50", "Retailer_Location": "Retailer 1", "GRN_Date": "May 16,2017", "Retailer_Sale_Qty": "2"}]';
        $varArray = array('dispatchControlJson'=>$dispatchControlJson,'inventoryMonitorJson'=>$inventoryMonitorJson,'performaceJson'=>$performaceJson,'inspectionControlJson'=>$inspectionControlJson,'inventoryAccountingJson'=>$inventoryAccountingJson,'dispatchAutomationJson'=>$dispatchAutomationJson,'grnAutomationJson'=>$grnAutomationJson,'pickupautomationJson'=>$pickupautomationJson,'putawayJson'=>$putawayJson,'returnVerificationJson'=>$returnVerificationJson,'secondarySale'=>$secondarySale);
        return View::make('welcome.hello')->with($varArray);
    }
    
    public function checkingLocationOnLoop(){
       $user_id=Session::get('userId');
/*       $user_location=Session::get('usercurrloc');
*/      $user_location= isset($_COOKIE['usercurrloc']) ? $_COOKIE['usercurrloc'] : '';
       $db_location = DB::table('users')
       ->where('user_id',$user_id)
       ->value('location_id');
      
       if($user_location != null) {
         if($user_location != $db_location) {
            Session::put('flash_message1','Already logged in with other location');
            //dd('Already logged in with other location');
            return Redirect::to('/logout');
       }
    }
}


        public function sendEmail() {
                $data = array('name'=>"Eseal Administrator");
   
                Mail::send(['text'=>'emails.testemail'], $data, function($message) {
                        $message->to('deepanshu.jha@eseal.io', 'Test Email')->subject
                        ('UNilever Testing Email');
                        $message->from('noreply@esealprod.unilever.com','Eseal Administrator');
                });
                echo "Basic Email Sent. Check your inbox.";
        }


        public function sendmail(){
        /*;
                Mail::to('ruchitaganni@eseal.io')->send(new sendGridEmail($data));
                $subject = 'This';
                $name = 'Jane'; 
        */

        $file=$this->_request->file('file');
        $dbfile=$this->_request->file('dbfile');
        $sub=$this->_request->input('subject');
        $body=$this->_request->input('body');
        $file_size=$this->_request->file('file')->getSize();
        $dbfile_size=$this->_request->file('dbfile')->getSize();
        $file_size=number_format($file_size / 1048576,2);
        $dbfile_size=number_format($dbfile_size / 1048576,2);
        if($file_size>10){
          throw new Exception("File size is too large", 1);
          
        }
        if($file_size>10){
          throw new Exception("File size is too large", 1);
          
        }
        
        /*echo $file->getClientOriginalName();
        echo $dbfile;
        /*$message->to('techsupport@eseal.io', 'Techsupport')*/

        $data = array('message'=>'This is a test!','File'=>$file);
        Mail::send(['html'=>'email'], $data, function($message) use($data, $file,$dbfile,$sub){
                
                $message->to('eseal_operations_alert@suneratech.com', 'eseal_operations_alert')
                        ->subject($sub);
                        //->subject('Eseal App Crash Report - Unilever SL');
                $message->attach($file->getRealPath(),['as' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                ]);
                $message->attach($dbfile->getRealPath(),['as' => $dbfile->getClientOriginalName(),
                    'mime' => $dbfile->getClientMimeType(),
                ]);
                $message->from('noreply@esealprod.unilever.com','Eseal Administrator');
                });
        /*echo $address;  */     
}

public function matMaster(){
    $data=$this->_request->input('data');

    echo count($data);
    exit;
}


}

?>