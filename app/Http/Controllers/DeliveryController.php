<?php 
namespace App\Http\Controllers;
use App\Models\Products;
set_time_limit(0);
ini_set('memory_limit', '-1');
/*use Central\Repositories\CustomerRepo;
//use Illuminate\Routing\Controller as BaseController;
use Central\Repositories\RoleRepo;*/

use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use App\Repositories\ConnectErp;
use App\Models\Conversions;
use Maatwebsite\Excel\Facades\Excel;

use Session;
use DB;
use View;
use Validator;
use Redirect;
use Log;
use Exception;
use PDF;
use QrCode;
use File;

//use Carbon;

class DeliveryController extends BaseController{

    protected $CustomerObj;
    protected $roleAccessObj;
    protected $roleid;
    public $_request;
    public $erp;
            
     public function __construct(Request $request)
    { 
        $product = new Products\Products();
        $productattr = new Products\ProductAttributes();
        $this->_product = $product;
        $this->_productattr = $productattr;
        $this->roleRepo = new RoleRepo;
        $this->_request = $request; 
        $this->mfg_id=Session::get('customerId'); 
        $this->_manufacturerId = $this->_product->getManufacturerId();
    }
    public function number_format($number)
    {
        setlocale(LC_MONETARY,"en_IN");
        $temp = money_format("%i",$number);
        return str_replace("INR ", "", $temp);
    }

    public function index($arg)
    {

         $manufacturerId = Session::get('customerId');
        //  echo "hai"
        // echo 'manufacturerId => '.$manufacturerId;exit;
        //parent::Breadcrumbs(array('Home'=>'/',ucfirst($arg)=>'#'));
        parent::Breadcrumbs(array('Home'=>'/',strtoupper($arg)=>'#'));


        if($manufacturerId)
        {        
            $manu = DB::Table('eseal_customer')
                    ->where('customer_id', $manufacturerId)
                    ->orWhere('parent_company_id',$manufacturerId)
                    ->select('customer_id', 'brand_name')
                    ->get()->toArray();
        }else{
            $manu = DB::Table('eseal_customer')
                ->select('customer_id', 'brand_name')
                ->get();
        }
        $userType = Session::get('userId');
        $custType=DB::table('users')
                ->join('eseal_customer','users.customer_id','=','eseal_customer.customer_id')
                ->join('master_lookup','master_lookup.value','=','eseal_customer.customer_type_id')
                ->where('users.customer_type','!=',7001)
                ->where('user_id',$userType)
                ->select('eseal_customer.customer_type_id')
                ->get()->toArray();
        /* $custType=json_encode($custType);*/
        $manufactuerArray = array();
        if(!empty($manu))
        {
            foreach($manu as $manufacturer)
            {
                $manufactuerArray[$manufacturer->customer_id] = $manufacturer->brand_name;
            }
        }
             $getLocId = DB::table('users')->where('user_id',$userType)->value('location_id');
              $getPrd = DB::table('products')->where('product_type_id','=',8003)->where('manufacturer_id',$manufacturerId)->select('name','product_id')->get()->toArray();
               return View::make('delivery/sto')
                        ->with('manu', $manu)
                        ->with('arg',$arg)
                        ->with('products',$getPrd)
                        ->with('manufacturerData', $manufactuerArray)
                        ->with('custType',$custType);
/*          if($arg==1){
               return View::make('delivery/Sto')
                        ->with('manu', $manu)
                        ->with('products',$getPrd)
                        ->with('manufacturerData', $manufactuerArray)
                        ->with('custType',$custType);
                    }
          elseif ($arg==0) {
                return View::make('delivery/deliveries')
                        ->with('manu', $manu)
                        ->with('products',$getPrd)
                        ->with('manufacturerData', $manufactuerArray)
                        ->with('custType',$custType);
                      
                      }*/
     }

  public function add()
    {
    parent::Breadcrumbs(array('Home'=>'/','Delivery Details'=>'delivery/Sto','Create Delivery'=>'#')); 
    $custId=Session::get('customerId');
    $userId = Session::get('userId');
    $p_id=$this->_request->get('item');
    
     $getLocId = DB::table('users')->where('user_id',$userId)->value('location_id');
     $locations = DB::table('locations as l')->join('location_types as lt','lt.location_type_id','=','l.location_type_id')->where('l.manufacturer_id',$custId)->where('l.location_id','=',$getLocId)->whereIn('lt.location_type_name',array('Customer','Plant','Warehouse','Depot','distributor'))
       ->select('location_id','erp_code',DB::raw('concat(erp_code,"-",location_name) as location_name'))->get();
       $locErpCode = '';
       if(!empty($locations)){
          $locErpCode = $locations[0]->erp_code;
       }

       $issueStrLoc = DB::table('storage_location_master')->where('parent_location_code', $locErpCode)->get();

         //print_r($locations);exit;
        $Tolocations = DB::table('locations as l')->join('location_types as lt','lt.location_type_id','=','l.location_type_id')->where('l.manufacturer_id',$this->mfg_id)->where('l.location_id','!=',$getLocId)->whereIn('lt.location_type_name',array('Plant','Warehouse','Depot','distributor'))
       ->select('location_id',DB::raw('concat(erp_code,"-",location_name) as location_name'))->get();
       //print_r($Tolocations);exit;
     $getType = DB::table('master_lookup')->where('description','po_docType')->select('name','value', 'label')->get();
     //$getPrd = DB::table('products as p')->join('product_locations as pl','pl.product_id','=','p.product_id')->where('location_id',$getLocId)->where('product_type_id','=',8003)->select('name','p.product_id as product_id')->get();
     //dd($getType);die;
 $getPrd = DB::table('products')->where('product_type_id','=',8003)->where('manufacturer_id',$custId)->select(DB::raw('concat(material_code,"-",name) as name'),'product_id')->get();
//concat(name,"-",material_code)
 

    return View::make('delivery/add')->with(array('tolocations'=>$Tolocations,'locations'=>$locations, 'issueStrLoc'=>$issueStrLoc, 'types'=>$getType,'products'=>$getPrd,'locId'=>$getLocId));      
    }
    public function getPlantStrLocation($plantCode){
      $locationsData = DB::table('locations')->where('location_id', $plantCode)->first();
      if($locationsData != NULL){
        $plantStrLoc = DB::table('storage_location_master')
                      ->where('parent_location_code', $locationsData->erp_code);
        $plantStrLoc = $plantStrLoc->pluck('storage_location', 'storage_desc')->toArray();
        return json_encode($plantStrLoc);  
      }
      
    }

    public function getConversion($qty,$UOM,$pid){
        $convt=new Conversions();
        $grnQty = $convt->getUom($pid, $qty, 'CV', $UOM);
        return $grnQty;
      }
      
    
   public function editDeliveryDetails($delivery_id,$product_id)
    {

      $products = DB::table('delivery_details')->where('product_id',$product_id)->where('ref_id',$delivery_id)->select('product_id','qty','ref_id as delivery_id')->first();
          
    return Response()->json($products);

    }
    public function checkpo_orderExists($newSTO){
        $data=DB::table('delivery_master');
        $data->where('document_no',$newSTO);
        $data=$data->count();
        return $data;
    }
    public function save()
    {
       $data = $this->_request->all();
         // dd($data);die;
        $custId=Session::get('customerId');
        unset($data['_token']);
        $userId = Session::get('userId');
        $date = date("Y-m-d H:i:s");
        //dd($date);die; 
        $docid=0;
        do{
          sleep(rand(0,4));
          $docid='6'.time();
        }while($this->checkpo_orderExists($docid));

        $getDeliveryId = DB::table('delivery_master')->insertGetId(['document_no'=>$docid,'frm_location'=>$data['from_location_id'],'to_location'=>$data['to_location_id'],'receving_location'=>$data['to_sloc'],'is_sto'=>1,'user_id'=>$userId,'type'=>$data['type'],'doc_date'=>$date,'manufacturer_id'=>$custId, 'issue_location' => $data['storage_loc']]);
        
        //dd($getDeliveryId);die;
         $line = 1;
         // dd($data['data']);die;
         if(isset($data['data'])){
        foreach($data['data'] as $insert){
          $insert = json_decode($insert);
          //dd($convert->product_id);die;
          // echo "<pre/>";
          // print_r($insert);exit;
          $insertTo = DB::table('delivery_details')->insert(['ref_id'=>$getDeliveryId,'product_id'=>$insert->product_id,'qty'=>$insert->qty_id,'line_item_no'=>$line,'batch_no'=>trim($insert->b_noo)]);
          $line++;
        }
       }
       else{
            $insertTo = DB::table('delivery_details')->insert(['ref_id'=>$getDeliveryId,'product_id'=>$data['item'],'qty'=>$data['qty'],'line_item_no'=>1]);
       }

        return Redirect::to('delivery/Sto'); 
    }

    public function createDeliveryOrders(){
        $status=0;
        $scStatus=0;
        $deliveryId=0;
        $message='Something Went wrong,Please check logs for more info';
        $input = Input::all();
        try{
          DB::beginTransaction();
          if(!isset($input['sto_no']))
            throw new Exception("sto_no should not be empty", 1);
          if(!isset($input['document_no']))
            throw new Exception("document_no should not be empty", 1);
//           $sto=DB::table('delivery_master')->where('sto_no',$input['sto_no'])->value('id');
           $sto=DB::table('delivery_master')->where('sto_no',$input['sto_no'])->where('is_sto',1)->get()->toArray();
           if(count($sto)<=0)
            throw new Exception("sto not exists in Eseal", 1);
            else
              $sto=$sto[0];
          if(!isset($input['shipment_no']))
            throw new Exception("shipment_no missing", 1); 
          if(!isset($input['delivery_shipment_flag']))
            throw new Exception("delivery_shipment_flag missing", 1);
          if(!isset($input['items']))
            throw new Exception("items missing", 1);
          $items=$input['items'];
          if(count($items)<=0)
            throw new Exception("itemms data missing", 1);

           $delivery=DB::table('delivery_master')->where('document_no',$input['document_no'])->value('id');
          if(!$delivery)
          {
              $deliveryData['document_no']=$input['document_no'];
              $deliveryData['action_code']=$input['action_code'];
              $deliveryData['sto_no']=$input['sto_no'];
              $deliveryData['frm_location']=$sto->frm_location;
              $deliveryData['to_location']=$sto->to_location;
              $deliveryData['receving_location']=$sto->receving_location;
              $deliveryData['manufacturer_id']=$sto->manufacturer_id;
              $deliveryData['user_id']=$sto->user_id;
              // $deliveryData['type']=$sto->type;
              $deliveryData['is_sto']=0;

              //changes by ruchita.
              $deliveryData['shipment_no']=$input['shipment_no'];
              $deliveryId=DB::table("delivery_master")->insertGetId($deliveryData);
            } else 
            throw new Exception("delivery already exist, Plese consult team for further", 1);

          foreach ($items as $key => $item) {
            $product_id=DB::table('products')->where('material_code',$item['material_code'])->value('product_id');
            $dd=[];
            $dd['src_stor_type']=$item['src_stor_type'];
            $dd['src_stor_sec']=$item['src_stor_sec'];
            $dd['src_bin']=$item['src_bin'];
            $dd['dest_stor_sec']=$item['dest_stor_sec'];
            $dd['dest_stor_type']=$item['dest_stor_type'];
            $dd['dest_bin']=$item['dest_bin'];
            $dd['batch_no']=$item['batch_no'];
            $dd['ref_id']=$deliveryId;
            $dd['product_id']=$product_id;
            $dd['qty']=$item['qty'];
            $dd['line_item_no']=$item['line_item'];
            $dd['to_no']=$item['to_no'];
            $dd['to_line_no']=$item['to_line_no'];
            $delivery_details_in=DB::table("delivery_details")->insertGetId($dd);
            if(!$delivery_details_in)
              $scStatus=1;
          }

          if(!$scStatus){
            $update=['shipment_no'=>$input['shipment_no'],'delivery_shipment_flag'=>$input['delivery_shipment_flag'],'delivery_info'=>$input['delivery_info']];
              $stoupdate=DB::table('delivery_master')->where('id',$sto->id)->update($update);
            $status=1;
            $message='Delivery Created Sucessfully';
            DB::commit();
          } else {
            throw new Exception("error in inserting deliveries", 1);
          }

        } catch(Exception $e){
          DB::rollBack();
          $status=0;
          $message = $e->getMessage();
    } 

      $result=json_encode(['Status'=>$status,'Message'=>'Server:' .$message]);
      $this->erp=new ConnectErp(6);
      $this->erp->captureReqLog('createDeliveryOrders',json_encode($input),$result);
    return $result;
  }

    public function createStoEcc(){   

        $status=0;
        $message="no records found";
        $deliveries=DB::table('delivery_master')->whereIn('is_processed',[0,3,4])->where('is_sto',1)->orderBy('id','DESC')->limit(1)->get()->toArray();
        if(count($deliveries)>0) {

          $delivery=$deliveries[0];
          // echo"<prev/>";print_r($delivery);exit;
          $ud=DB::table('delivery_master')->where('id',$delivery->id)->update(['is_processed'=>2]);

          try{

          $type=DB::table('master_lookup')->where('value',$delivery->type)->value('name');
          $sloc=DB::table('locations')->where('location_id',$delivery->frm_location)->value('erp_code');
          $dloc=DB::table('locations')->where('location_id',$delivery->to_location)->value('erp_code');
          $issueLocation = DB::table('delivery_master')->where('id',$delivery->id)->value('issue_location');
          $flag=0;
          if($delivery->sto_no!=''){
            $flag='M';
          } else {
            $flag='C';
          }


          $itemData=[];
          $items=DB::table('delivery_details as dd')->join('products as p','p.product_id','=','dd.product_id')->where('ref_id',$delivery->id)->get(['dd.product_id','dd.qty','dd.ref_id','dd.id','p.material_code','dd.batch_no'])->toArray();
          // print_r($items);

          foreach ($items as $pkey => $pvalue) {
              $temp=[];
              $temp['material']=$pvalue->material_code;
              //$temp['material']="94000001";
              $temp['quantity']=(int) $pvalue->qty;
              if($pvalue->batch_no!='')
              $temp['batch']=$pvalue->batch_no;
              $itemData[]=$temp;
              
          }
// /print_r($itemData);exit;
          $method = 'stoCreation';
          $methodType='POST';
          $params='';
          $headerData=[];

          $body=array('headerData'=>array('po_docType'=>$type,'sto_number'=>$delivery->sto_no,'reference_number'=>$delivery->document_no,'sending_plant'=>$sloc, 'sending_location'=>$issueLocation,'receving_plant'=>$dloc,'receving_location'=>$delivery->receving_location,'flag'=>$flag),'itemData'=>$itemData);


          
          $this->erp=new ConnectErp($delivery->manufacturer_id);
          $result=$this->erp->request($method,$params,$body,$methodType);
          $result=json_decode($result);

         /*if(isset($result->status)){
            $ud=DB::table('delivery_master')->where('sto_no',$delivery->id)->update(['is_processed'=>1]);
            
          //  $result=$result->STO_creation_reponse;
          }

*/       //print_r($result);exit;

            if($result->status){
            $sto_no=$result->data->STO_doc_number;
              $ud=DB::table('delivery_master')->where('id',$delivery->id)->update(['is_processed'=>1,'sto_no'=>$sto_no,'cron_status'=>$sto_no,'remarks'=>'E-'.$result->message]);
               $status=1;
            $message="updated Sucessfully";
            } else {
              $ud=DB::table('delivery_master')->where('id',$delivery->id)->update(['is_processed'=>3,'remarks'=>'E-'.$result->message]);
               $status=0;
            $message='E-'.$result->message;
            }
          } catch (Exception $e){

               $ud=DB::table('delivery_master')->where('id',$delivery->id)->update(['is_processed'=>3,'remarks'=>'E-'.$result->message]);

               
            $status=0;
            $message = $e->getMessage();
          }
        } else {
          $status=0;
          $msg="NO orders for create delivery";
        }

        echo json_encode(['Status'=>$status,'Message'=>$message]);

    }

      public function updateDeliveryDetails($delivery_id,$product_id)
    {
        $data=Input::all();
        //dd($data);die;
                             $validator = \Validator::make(
                                    array(
                                'product_id' => isset($data['product_id']) ? $data['product_id'] : '',
                                'qty' => isset($data['qty']) ? $data['qty'] : ''
                                    ), array(
                                'product_id' => 'required',
                                'qty' => 'required'
                                ));
                    if($validator->fails())
                    {
                        $errorMessages = json_decode($validator->messages());
                        $errorMessage = '';
                        if(!empty($errorMessages))
                        {
                            foreach($errorMessages as $field => $message)
                            {
                                $errorMessage = implode(',', $message);
                            }
                        }
                        return response()->json([
                                'status' => false,
                                'message' => $errorMessage
                    ]);
                    }

        DB::table('delivery_details')
                ->where('product_id', $product_id)
                ->where('ref_id',$delivery_id)
                ->update(array(
                    'qty' => Input::get('qty')));
        
        return response()->json([
                    'status' => true,
                    'message' => 'Sucessfully updated.'
        ]);
        //->withCallback($request->input('callback'));;
    }

    public function delwithProduct($Id,$product_id)
    {
      DB::table('delivery_details')->where('ref_id',$Id)->where('product_id',$product_id)->delete();
        return 1;   

    }
   
    public function getElementdata($arg,$id=0){ 

         try
         {
        $userId = Session::get('userId');
             $getLocId = DB::table('users')->where('user_id',$userId)->value('location_id');  
              // dd($getLocId);die;
         if($arg==1){  
      
           //DB::enableQueryLog();
           $getDeliveryMaster = DB::table('delivery_master as dm')
                      ->Leftjoin('master_lookup as ml','ml.value','=','dm.type')
                    ->select('dm.document_no as document_no','dm.id as id', 'ml.name as name','dm.receving_location as StorLoc', 'dm.shipment_no', 'dm.remarks',
                      DB::raw("DATE_FORMAT(dm.doc_date, '%d-%m-%Y %H-%i-%s') as date"),DB::Raw("CONCAT('<a 
         data-href=\"javascript:void(0)\" onclick = \"getgrid(',dm.sto_no,')\">',dm.sto_no, '</a>')as DeliveryId "),DB::Raw("(select location_name from locations where location_id = dm.to_location ) as DestLoc"),DB::Raw("(select  location_name from locations where location_id = dm.frm_location) as SrcLoc "),DB::raw('case when is_processed=0 then "Created" when is_processed=1 then "ECC confirmed" end as status'))
                   //->where('is_sto',$o)
                    ->orderBy('dm.doc_date','desc')
                    //->where('user_id',$userId)
                    ->where('frm_location',$getLocId)
                    //->where('is_sto',1)
                    ->get()->toArray();
                    // $convet = new Conversions();
                    // $t_qty=$convet->getUom(1,24,'Z01');
                    // print_r($t_qty);exit;
                   // $laQuery = DB::getQueryLog();
            //echo "<pre>";print_r($laQuery); exit;        
            $agarr = array();
            $prodarr = array();
            // print_r($getDeliveryMaster);exit;
            // dd($getDeliveryMaster);die;
            $ags = json_decode(json_encode($getDeliveryMaster), true);            
            $DelDetails = DB::table('delivery_master as dm')
                        ->Join('delivery_details as dd', 'dd.ref_id', '=', 'dm.id')
                        ->join('products as p','p.product_id','=','dd.product_id')
                        ->select(DB::raw("p.name as PName,dd.product_id as product_id,dd.ref_id as id,dd.line_item_no as lineNo, dd.batch_no, dd.qty as Qty,(CASE WHEN is_processed = 0 THEN  CONCAT('<a data-href=\"/delivery/editDeliveryDetails/',dd.ref_id,'/',p.product_id,'\" data-toggle=\"modal\" data-target=\"#basicvalCodeModal1\"><span class=\"badge bg-light-blue\"><i class=\"fa fa-pencil\"></i></span></a><span style=\"padding-left:10px;\" ></span> <a 
                            data-href=\"javascript:void(0)\" onclick = \"delwithProduct(',dd.ref_id,',',p.product_id,')\"><span class=\" badge bg-red\"><i class=\"fa fa-trash-o\"></i></span></a><span style=\"padding-left:10px;\" ></span>')ELSE '' END) as actions"))
                       ->whereIn('dd.ref_id',array_column($ags, 'id'))
                        ->get()->toArray();
             $finalarr=[];
             $finalarr['masterData'] = $ags;
            $finalarr['detailsData'] = json_decode(json_encode($DelDetails),true);
            //dd($finalarr);die;
            return $finalarr;
           }
           elseif($arg==0){
           $userId = Session::get('userId');
          //   //  //dd($userId);
            $getLocId = DB::table('users')->where('user_id',$userId)->value('location_id');
          //    //echo ($getLocId);exit;
            
            $getDeliveryMaster = DB::table('delivery_master as dm')
                       ->leftJoin('master_lookup as ml','ml.value','=','dm.type')
                     /*->select('dm.sto_no as deliveryId','dm.document_no as document_no','dm.id as id', 'ml.name as name',
                      DB::raw("DATE_FORMAT(dm.doc_date, '%d-%m-%Y %H-%i-%s') as date"),DB::Raw("(select location_name from locations where location_id = dm.to_location ) as DestLoc"))*/
                      ->Join('delivery_details as dd', 'dd.ref_id', '=', 'dm.id')
                     ->select('dm.document_no as document_no', 'dm.is_processed','dm.id as id', 'ml.name as name','dm.receving_location as StorLoc','dm.shipment_no','dd.to_no',
                      DB::raw("DATE_FORMAT(dm.doc_date, '%d-%m-%Y %H-%i-%s') as date"),DB::Raw("(select location_name from locations where location_id = dm.to_location ) as DestLoc"),DB::Raw("(select  location_name from locations where location_id = dm.frm_location) as SrcLoc "),DB::raw('case when is_processed=0 then "Created" when is_processed=1 then "ECC confirmed" end as status'))
                    ->orderBy('dm.doc_date','desc')
                   //->where('action_code','<=',4)
                    ->where('is_sto',0)
                    //->where('user_id',$userId)
                   ->where('frm_location',$getLocId);
                   if($id!=0){
                   $getDeliveryMaster=$getDeliveryMaster->where('sto_no',$id);
                   }
                   $getDeliveryMaster=$getDeliveryMaster->get()->toArray();
          //   //echo "<pre>";print_r($getDeliveryMaster); exit;        
             $agarr = array();
             $prodarr = array();
             $newDMdata = array();
             foreach ($getDeliveryMaster as $DMvalue) {
               $DMvalue->tpUrl = '';
              if($DMvalue->is_processed == 1)
              {
                $DMvalue->tpUrl = '<a href="'.url('/').'/deliveries/getPdfDetailsForTp/'.$DMvalue->document_no.'" target="_blank"> View </a>'; 
              }
               $newDMdata[] = $DMvalue;
             }
             $getDeliveryMaster = $newDMdata;
          //   //dd($getDeliveryMaster);die;
             $ags = json_decode(json_encode($getDeliveryMaster), true);            
             $DelDetails = DB::table('delivery_master as dm')
                         ->Join('delivery_details as dd', 'dd.ref_id', '=', 'dm.id')
                         ->join('products as p','p.product_id','=','dd.product_id')
                         ->select(DB::raw("p.name as PName,dd.product_id as product_id,dd.ref_id as id,dd.line_item_no as lineNo, dd.qty as Qty, dd.batch_no ,CONCAT(' <a 
                             data-href=\"javascript:void(0)\" onclick = \"delwithProduct(',dd.ref_id,',',p.product_id,')\"><span class=\" badge bg-red\"><i class=\"fa fa-trash-o\"></i></span></a><span style=\"padding-left:10px;\" ></span>')   as actions"))
                        ->whereIn('dd.ref_id',array_column($ags, 'id'))
                         ->get()->toArray();
             $finalarr=[];
              $finalarr['masterData'] = $ags;
            $finalarr['detailsData'] = json_decode(json_encode($DelDetails),true);
           // dd($finalarr);die;
             return $finalarr;

          }
        } catch (\ErrorException $ex) {
            return json_encode($ex->getMessage());

        }
     }

    public  function getDeliveries($id){
      $userId = Session::get('userId');
      $sto_no=DB::table('delivery_master as dm ')->where('id',$id)->value('sto_no');
      $getLocId = DB::table('users')->where('user_id',$userId)->value('location_id');  
      $getDeliveryMaster = DB::table('delivery_master as dm')
                      ->leftJoin('master_lookup as ml','ml.value','=','dm.type')
                    ->select('dm.sto_no as DeliveryId','dm.document_no as document_no','dm.id as id', 'ml.name as name',
                      DB::raw("DATE_FORMAT(dm.doc_date, '%d-%m-%Y %H-%i-%s') as date"),DB::Raw("(select location_name from locations where location_id = dm.to_location ) as DestLoc"))
                    ->orderBy('dm.doc_date','desc')
                    ->where('dm.sto_no',$sto_no)
                    ->where('action_code',1)
                    ->where('is_sto',0)
                    ->where('user_id',$userId)
                    ->where('frm_location',$getLocId)
                    ->get()->toArray();
            //echo "<pre>";print_r($getDeliveryMaster); exit;        
            $agarr = array();
            $prodarr = array();
            //dd($getDeliveryMaster);die;
            $ags = json_decode(json_encode($getDeliveryMaster), true);            
            return $ags;            
      $deliveries= DB::table('delivery_master as dm')
                        ->Join('delivery_details as dd', 'dd.ref_id', '=', 'dm.id')
                        ->join('products as p','p.product_id','=','dd.product_id')
                        ->select(DB::raw("p.name as PName,dd.product_id as product_id,dd.ref_id as id,dd.line_item_no as lineNo, dd.qty as Qty,(CASE WHEN is_processed = 0 THEN  CONCAT('<a data-href=\"/delivery/editDeliveryDetails/',dd.ref_id,'/',p.product_id,'\" data-toggle=\"modal\" data-target=\"#basicvalCodeModal1\"><span class=\"badge bg-light-blue\"><i class=\"fa fa-pencil\"></i></span></a><span style=\"padding-left:10px;\" ></span> <a 
                            data-href=\"javascript:void(0)\" onclick = \"delwithProduct(',dd.ref_id,',',p.product_id,')\"><span class=\" badge bg-red\"><i class=\"fa fa-trash-o\"></i></span></a><span style=\"padding-left:10px;\" ></span>')ELSE '' END) as actions"))
                        ->where('dd.ref_id',$id)
                       //->whereIn('dd.ref_id',array_column($ags, 'id'))
                        ->get()->toArray();

                        $finalarr=[];
             $finalarr['masterData'] = $ags;
            $finalarr['detailsData'] = json_decode(json_encode($DelDetails),true);
                 return $finalarr;      

    }    

    public function getPdfDetailsForTp($docNumber){
      if(empty($docNumber))
      {
        return 'document number is required';
      }
      $deliveryMaster = DB::table('delivery_master as dm')
                        ->join('tp_attributes as tpa', 'tpa.value' , '=', 'dm.document_no')
                        ->select('dm.frm_location','dm.to_location','dm.shipment_no','dm.id','tpa.tp_id')
                        ->where('document_no', $docNumber)
                        ->first();
      if($deliveryMaster == NULL)
      {
        return 'TO not genrated';
      }

      $scrLocation = DB::table('locations')
                      ->where('location_id', $deliveryMaster->frm_location)
                      ->select('location_name', 'location_email', 'location_address', 'location_details', 'pincode')
                      ->first();

      $desLocation = DB::table('locations')
                      ->where('location_id', $deliveryMaster->to_location)
                      ->select('location_name', 'location_email', 'location_address', 'location_details', 'pincode')
                      ->first();

      $deliveryChild = DB::table('delivery_details as dd')
                        ->join('products as p', 'p.product_id', '=', 'dd.product_id')
                        ->where('ref_id', $deliveryMaster->id)
                        ->select('dd.id','dd.qty','dd.batch_no', 'p.product_id', 'p.name','p.description','p.material_code')
                        ->get();
      $deliveryChildNew = array();
      foreach ($deliveryChild as $key => $value) {
        $bea = DB::table('conversions')->where('product_id',$value->product_id)->where('alt_uom', 'EA')->value('base_quantity');
        $aeaAlt = DB::table('conversions')->where('product_id',$value->product_id)->where('alt_uom','CV')->value('alt_quantity');
        $aea = DB::table('conversions')->where('product_id',$value->product_id)->where('alt_uom','CV')->value('base_quantity');
        $palQty = $aea*$aeaAlt/$bea;
        $value->noOfCases = $palQty;
        $deliveryChildNew = $value;
      }

      $tpAttData = DB::table('tp_attributes')
                    ->where('tp_id', $deliveryMaster->tp_id)
                    ->get();
      // genrate qr code
      QrCode::size(300)
      ->format('png')
      ->generate($deliveryMaster->tp_id, public_path('tp-qrcode-images/'.$deliveryMaster->tp_id.'-qrcode.png'));

      $downloadUrl = url('/').'/deliveries/downloadTPPDF/'.$docNumber;
      return View::make('delivery/tpPdf')
                        ->with('title', 'STOCK TRANSFER PGI')
                        ->with('date', date('Y-m-d H:i:s'))
                        ->with('deliveryMaster',$deliveryMaster)
                        ->with('src_location',$scrLocation)
                        ->with('des_location', $desLocation)
                        ->with('deliveryCild',$deliveryChild)
                        ->with('tpAttData', $tpAttData)
                        ->with('downloadUrl',$downloadUrl)
                        ->with('download', 'no');

      return View::make('delivery/tpPdf')->with(
        array(
          'date'=> date('Y-m-d H:i:s'),
          'deliveryMaster',$deliveryMaster,
          'src_location' => $scrLocation,
          'des_location' => $desLocation,
          'deliveryCild' => $deliveryChild,
          'downloadUrl'  => $downloadUrl)
        );
      
    }

    public function TPPdfDownload($docNumber){
      ini_set('max_execution_time', 300);
      ini_set("memory_limit","512M");
      if(empty($docNumber))
      {
        return 'document number is required';
      }
      $deliveryMaster = DB::table('delivery_master as dm')
                        ->join('tp_attributes as tpa', 'tpa.value' , '=', 'dm.document_no')
                        ->select('dm.frm_location','dm.to_location','dm.shipment_no','dm.id','tpa.tp_id')
                        ->where('document_no', $docNumber)
                        ->first();
      if($deliveryMaster == NULL)
      {
        return 'TO not genrated';
      }

      $scrLocation = DB::table('locations')
                      ->where('location_id', $deliveryMaster->frm_location)
                      ->select('location_name', 'location_email', 'location_address', 'location_details', 'pincode')
                      ->first();

      $desLocation = DB::table('locations')
                      ->where('location_id', $deliveryMaster->to_location)
                      ->select('location_name', 'location_email', 'location_address', 'location_details', 'pincode')
                      ->first();

      $deliveryChild = DB::table('delivery_details as dd')
                        ->join('products as p', 'p.product_id', '=', 'dd.product_id')
                        ->where('ref_id', $deliveryMaster->id)
                        ->select('dd.id','dd.qty','dd.batch_no', 'p.product_id', 'p.name','p.description','p.material_code')
                        ->get();

      $deliveryChildNew = array();
      foreach ($deliveryChild as $key => $value) {
        $bea = DB::table('conversions')->where('product_id',$value->product_id)->where('alt_uom', 'EA')->value('base_quantity');
        $aeaAlt = DB::table('conversions')->where('product_id',$value->product_id)->where('alt_uom','CV')->value('alt_quantity');
        $aea = DB::table('conversions')->where('product_id',$value->product_id)->where('alt_uom','CV')->value('base_quantity');
        $palQty = $aea*$aeaAlt/$bea;
        $value->noOfCases = $palQty;
        $deliveryChildNew = $value;
      }

      $tpAttData = DB::table('tp_attributes')
                    ->where('tp_id', $deliveryMaster->tp_id)
                    ->get();
      $downloadUrl = url('/').'/deliveries/getPdfDetailsForTp/'.$docNumber;
      $data = array(
        'title' => 'STOCK TRANSFER PGI',
        'date'  =>  date('Y-m-d H:i:s'),
        'deliveryMaster'  => $deliveryMaster,
        'src_location'    => $scrLocation,
        'des_location'    => $desLocation,
        'deliveryCild'    => $deliveryChild,
        'tpAttData'       => $tpAttData,
        'downloadUrl'     => $downloadUrl,
        'download'        => 'yes');
      $pdf = PDF::loadView('delivery/tpPdf', $data);
      // $pdf->save(storage_path().'_filename.pdf');
      // return $pdf->download('tpPdf.pdf');
      return $pdf->download('tp-invoice-'.$docNumber.'.pdf');
      return View::make('delivery/tpPdf')->with(
        array(
          'date'=> date('Y-m-d H:i:s'),
          'deliveryMaster',$deliveryMaster,
          'src_location' => $scrLocation,
          'des_location' => $desLocation,
          'deliveryCild' => $deliveryChild)
        );
      
    }

    public function matBatch($p_id){

      $userId = Session::get('userId');
      $getLocId = DB::table('users')->where('user_id',$userId)->value('location_id');
      $batch=db::table('eseal_6 as e')->join('track_history as th','th.track_id','=','e.track_id')->groupby('batch_no')->where('e.pid',$p_id)->where('e.batch_no','!=','')->where('th.dest_loc_id','=',0)->where('th.src_loc_id',$getLocId)->pluck('e.batch_no')->toArray();
      return json_encode($batch);
      /*$batch=db::table('eseal_6')->groupby('batch_no')->where('pid',$p_id)->where('batch_no','!=','')->pluck('batch_no')->toArray();
      return json_encode($batch);*/


    }

    public function conversionImport(){
        try{
            $status=1;
    $conv= $this->_request->input('conversions') ;  
    $sku= $this->_request->input('sku') ;  
    $price= $this->_request->input('price') ;  
    $allowed_Extensions = ['xls','xlsx'];
    if( $this->_request->file('files')){
      $extension= File::extension($this->_request->file('files')->getClientOriginalName());
    if( !in_array($extension, $allowed_Extensions))
    {
       return response()->json([
        'status'=>false,
        'msg' => 'Invalid file given. Please Upload a valid .xls or .xlsx file'
        ]);
    }
    }
    $path =  $this->_request->file('files')->getRealPath();
    $excelheaders =[];
    $data = Excel::load($path, function($reader) {        
        /*if(!empty($data) && $data->count()){
            $data = $data->toArray();
            $count = 0;
            $headers =["alt_uom","alt_quantity","base_uom","base_quantity","material_code"];
           // $excelheaders = array_values($data[0]);
            
            if(count(array_diff($headers, $excelheaders)) >0){
                return response()->json([
                    'status'=>false,
                    'msg' => 'Some Headers are missing. Please Check'
                    ]);
            }
        }*/
    })->get();
    $excelheaders = $data->first()->toArray();
    $conv_insert=[];
    if($conv==1){
        /*$headers =["alt_uom","alt_quantity","base_uom","base_quantity","material_code"];
        if(count(array_diff($headers,array_keys($excelheaders))) >0){
            throw new Exception("missing headers in conversion master", 1);
            
        }*/
        foreach($data  as $key=>$value){
            /*print_r($value);*/
        $product_id=DB::table('products')->where('material_code',$value->material_code)->value('product_id');
        $conInsert[]=['alt_uom'=>$value->alt_uom,'alt_quantity'=>$value->alt_quantity,'product_id'=>$product_id,'base_quantity'=>$value->base_quantity,'base_uom'=>$value->base_uom];
        }

        print_r($conInsert);
        echo '<br>';
    $insert=DB::table('conversions')->insert($conInsert);
    }elseif($price==1) {
        /*$headers =["price_lot","mrp","material_code","location_erp"];
        if(count(array_diff($headers,array_keys($excelheaders))) >0){
            throw new Exception("missing headers in price master", 1);
            
        }*/

        foreach($excelheaders  as $value){
         $product_id=DB::table('products')->where('material_code',$value['material_code'])->value('product_id');
          $lid=DB::table('locations')->where('erp_code',$value['location_erp'])->value('location_id');
        $conInsert[]=['price_lot'=>$value['price_lot'],'mrp'=>$value['mrp'],'product_id'=>$product_id,'location_id'=>$lid];
    }
    $insert=DB::table('price_lot')->insert($conInsert);
       
    } elseif($sku==1) {

        /*$headers =["sku_number","case_config","material_code","location_erp"];
        if(count(array_diff($headers,array_keys($excelheaders))) >0){
            throw new Exception("missing headers in SKU master", 1);
            
        }*/
        foreach($excelheaders  as $value){
            /*print_r($excelheaders);
            $value=json_decode($value,true);*/
             $product_id=DB::table('products')->where('material_code',$value['material_code'])->value('product_id');
             $lid=DB::table('locations')->where('erp_code',$value['location_erp'])->value('location_id');
            $conInsert[]=['sku_number'=>$value['sku_number'],'case_config'=>$value['case_config'],'product_id'=>$product_id,'location_id'=>$lid];
            }
        $insert=DB::table('sku_info')->insert($conInsert);
    }
        $message="Successfully added conversions";
        if($insert){
            return json_encode(['Status'=>$status,'Message'=>$message]);
        }
    
    }catch(Exception $e){
            $status=0;
            $message = $e->getMessage();
            return json_encode(['Status'=>$status,'Message'=>$message]);
     }
    


    }

    public function skuImport(){
        try{
            $status=1;
    $conv= $this->_request->input('conversions') ;  
    $sku= $this->_request->input('sku') ;  
    $price= $this->_request->input('price') ;  
    $allowed_Extensions = ['xls','xlsx'];
    if( $this->_request->file('files')){
      $extension= File::extension($this->_request->file('files')->getClientOriginalName());
    if( !in_array($extension, $allowed_Extensions))
    {
       return response()->json([
        'status'=>false,
        'msg' => 'Invalid file given. Please Upload a valid .xls or .xlsx file'
        ]);
    }
    }
    $path =  $this->_request->file('files')->getRealPath();
    $excelheaders =[];
    $data = Excel::load($path, function($reader) {        
    })->get();
    $excelheaders = $data->first()->toArray();
    $conv_insert=[];
if($price==1){
        foreach($data  as $key => $value){
         $product_id=DB::table('products')->where('material_code',$value->material_code)->value('product_id');
          $lid=DB::table('locations')->where('erp_code',$value->location_erp)->value('location_id');
        $conInsert[]=['price_lot'=>$value->price_lot,'mrp'=>$value->mrp,'product_id'=>$product_id,'location_id'=>$lid];
        
         }
            $insert=DB::table('price_lot')->insert($conInsert);
       
   }elseif ($sku==1) {
    foreach($data  as $key => $value){
    $product_id=DB::table('products')->where('material_code',$value->material_code)->value('product_id');
             $lid=DB::table('locations')->where('erp_code',$value->location_erp)->value('location_id');
            $conInsert[]=['sku_number'=>$value->sku_number,'case_config'=>$value->case_config,'product_id'=>$product_id,'location_id'=>$lid];
        }
         $insert=DB::table('sku_info')->insert($conInsert);
   }
        $message="Successfully added conversions";
        if($insert){
            return json_encode(['Status'=>$status,'Message'=>$message]);
        }
    
    }catch(Exception $e){
            $status=0;
            $message = $e->getMessage();
            return json_encode(['Status'=>$status,'Message'=>$message]);
     }
    


    }

    public function productLocationsimport(){
        try{
    $status=1; 
    $allowed_Extensions = ['xls','xlsx'];
    if( $this->_request->file('files')){
      $extension= File::extension($this->_request->file('files')->getClientOriginalName());
    if( !in_array($extension, $allowed_Extensions))
    {
       return response()->json([
        'status'=>false,
        'msg' => 'Invalid file given. Please Upload a valid .xls or .xlsx file'
        ]);
    }
    }
    $path =  $this->_request->file('files')->getRealPath();
    $excelheaders =[];
    $data = Excel::load($path, function($reader) {        
    })->get();
    $excelheaders = $data->first()->toArray();
    $conv_insert=[];
        foreach($data  as $key => $value){
         $product_id=DB::table('products')->where('material_code',$value->material_code)->value('product_id');
          $lid=DB::table('locations')->where('erp_code',$value->location_erp_code)->value('location_id');
        $conInsert[]=['product_id'=>$product_id,'location_id'=>$lid];
        }
        $insert=DB::table('product_locations')->insert($conInsert);
        $message="Successfully added Mapped Locations";
        if($insert){
            return json_encode(['Status'=>$status,'Message'=>$message]);
        }
    
    }catch(Exception $e){
            $status=0;
            $message = $e->getMessage();
            return json_encode(['Status'=>$status,'Message'=>$message]);
     }
    


    }
    public function dispatchReport(){
      return View::make('delivery.dispatchReport');
    }

    public function dispatchReport2(){

      $transitionId=DB::table('transaction_master')->where('name','=','Delivery Cancel')->value('id');
        $data=DB::table('delivery_master as dm')->join('tp_attributes as ta','ta.value','=','dm.document_no')
       // ->join('track_history as th','th.tp_id','=','ta.tp_id')
        ->select('dm.document_no','dm.sto_no','dm.shipment_no',db::raw('(select location_name from locations where location_id = dm.frm_location) as Source_Loc'),db::raw('(select location_name from locations where location_id = dm.to_location) as Dest_Loc'),'ta.tp_id','dm.doc_date','ta.reference_value as grn_number','dm.action_code',DB::raw('case when dm.is_processed=0 then "Created" when dm.is_processed=1 and  ta.reference_value ="" then "Dispatched" when dm.is_processed=1 and  ISNULL(ta.reference_value) then "Dispatched " when dm.is_processed=1 and  ta.reference_value !="" then "GRN " when dm.action_code>4 and dm.is_processed=0 then "cancellation created" end as status'),DB::raw("CONCAT('<a 
         data-href=\"javascript:void(0)\" onclick = \"getIOT(',ta.tp_id,',',dm.document_no,')\"><i class=\"fa fa-qrcode fa-lg\"></i></a>')as actions"))->orderBy('dm.doc_date','desc')->where('dm.is_sto',0)->where('dm.action_code','<=',4)->get()->toArray();
          //print_r($data);exit
      return $data;
    }
  public function   getIOT($tp_id,$document_no){
    $transitionId=DB::table('transaction_master')->where('name','=','Delivery Cancel')->value('id');
      $list=DB::table('tp_data as td')->where('td.tp_id',$tp_id)->pluck('td.level_ids')->toArray();
      $iddd[] = implode(',',$list);
      $kkk= $iddd[0];
      $eseal_ids=DB::table('eseal_6 as e')->join('products as p','p.product_id','=','e.pid')->join('track_history as th','th.track_id','=','e.track_id')->whereIn('e.primary_id',explode(',',$kkk))->select(DB::raw("CONCAT(e.primary_id, '\t') AS primary_id"),'p.description','material_code','e.batch_no','e.pkg_qty',DB::raw("CONCAT(p.ean, '\t') AS ean"),DB::raw('case when th.dest_loc_id !=0 then "In-Transit" when th.dest_loc_id=0 then "Received" when th.dest_loc_id=0 and th.transition_id='.$transitionId.' then "Canclled"  end as status'))->get()->toArray();
        return $eseal_ids;
  }


public function grnReport(){
      return View::make('delivery.grnReport');
    }

  public function getPutaway(){
      //$grn_doc=5005353922;
          $putaway_data=DB::table('putaway_queue as pq')
          ->join('products as p','p.product_id','=','pq.product_id')
          ->join('delivery_master as dm','dm.sto_no','=','pq.src_bin')
          ->join('tp_attributes as ta','ta.reference_value','=','pq.document_no')
          /*->join('import_grn as ig','ig.po_number','=','pq.src_bin')*/
          //->where('dm.is_sto',0)
          ->groupby('document_no')
          ->select('pq.document_no','pq.warehouse_no','pq.to_no','pq.src_bin',DB::raw('case when pq.status=0 then "TO created" when pq.status=1  then "TO confirmed" when pq.status=2  then "Bin to Bin conversion"  end as status'),DB::raw("CONCAT('<a 
            data-href=\"javascript:void(0)\" onclick = \"getGrnIOT(',pq.document_no,')\"><i class=\"fa fa-qrcode fa-lg\"></i></a>')as actions"),'dm.document_no as delivery_no',db::raw('(select location_name from locations where location_id = dm.to_location) as Dest_Loc'),'ta.tp_id','pq.timestamp')->where('dm.is_sto',0)->orderBy('pq.timestamp','desc')
          ->limit(1000)
          ->get()
          ->toArray();

            return $putaway_data;
          }
          public function getGrnIOT($document_no){
        $tp_no=DB::table('tp_attributes as ta')->where('ta.reference_value',$document_no)->get(['tp_id'])->toArray();
       
          if(count($tp_no)==0){
            $iots1=DB::table('Importpo_mapping as  im')->where('im.grn_number',$document_no)->pluck('IOT')->toArray();
           
          $iddd[] = implode(',',$iots1);
          $kkk= $iddd[0];
          $eseal_ids=DB::table('eseal_6 as e')->join('products as p','p.product_id','=','e.pid')->whereIn('e.primary_id',explode(',',$kkk))->select(DB::raw("CONCAT(e.primary_id, '\t') AS primary_id"),'p.description','material_code','e.batch_no','e.pkg_qty',DB::raw("CONCAT(p.ean, '\t') AS ean"))->get()->toArray();

          }else{
          $iots1=DB::table('tp_data as  td')->where('td.tp_id',$tp_no[0]->tp_id)->pluck('level_ids')->toArray();
            
          $iddd[] = implode(',',$iots1);
          $kkk= $iddd[0];
          $eseal_ids=DB::table('eseal_6 as e')->join('products as p','p.product_id','=','e.pid')->whereIn('e.primary_id',explode(',',$kkk))->select(DB::raw("CONCAT(e.primary_id, '\t') AS primary_id"),'p.description','material_code','e.batch_no','e.pkg_qty',DB::raw("CONCAT(p.ean, '\t') AS ean"))->get()->toArray();
          }

          return $eseal_ids;
           }
    
    

}
