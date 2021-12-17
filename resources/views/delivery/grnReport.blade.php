
    @extends('layouts.default')

    @extends('layouts.header')

    @extends('layouts.sideview')

    @section('content')

    @section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
    {{HTML::style('css/bootstrap-select.css')}}
    <!-- {{HTML::script ('jqwidgets/jqxpopover.js')}} -->
    @stop
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/css/bootstrap-select.css" />
    <link rel="stylesheet" href="../jqwidgets/styles/jqx.base.css" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css"/>
  
    {{HTML::script('jqwidgets/jqxcore.js')}}

    {{HTML::script('jqwidgets/jqxbuttons.js')}}

    {{HTML::script('jqwidgets/jqxscrollbar.js')}}

    {{HTML::script('jqwidgets/jqxmenu.js')}}

    {{HTML::script('jqwidgets/jqxgrid.js')}}

    {{HTML::script('jqwidgets/jqxgrid.selection.js')}}

    {{HTML::script('jqwidgets/jqxgrid.columnsresize.js')}}

    {{HTML::script('jqwidgets/jqxdata.js')}}

    {{HTML::script('scripts/demos.js')}}

    {{HTML::script('jqwidgets/jqxlistbox.js')}}

    {{HTML::script('jqwidgets/jqxdropdownlist.js')}}

    {{HTML::script('jqwidgets/jqxgrid.pager.js')}}

    {{HTML::script('jqwidgets/jqxgrid.sort.js')}}

    {{HTML::script('jqwidgets/jqxgrid.filter.js')}}

    {{HTML::script('jqwidgets/jqxgrid.storage.js')}}

    {{HTML::script('jqwidgets/jqxgrid.columnsreorder.js')}}

    {{HTML::script('jqwidgets/jqxpanel.js')}}
    <!-- {{HTML::script('jqwidgets/jqxpopover.js')}} -->
    {{HTML::script('jqwidgets/jqxcheckbox.js')}}
    {{HTML::script('jqwidgets/jqxdata.export.js')}}
    {{HTML::script('jqwidgets/jqxgrid.export.js')}}
    {{HTML::script('jqwidgets/jqxgrid.sort.js')}}
    {{HTML::script('jqwidgets/jqxbuttons.js')}}
    
    {{HTML::script ('jqwidgets/jqxwindow.js')}}

    
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/js/bootstrap-select.js"></script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
<script >

  $("#jqxButton").on('click', function ()
            {
                $("#events").find('span').remove();
                $("#events").append('<span>Button Clicked</span');
            });
</script>
    <style>
      .modal-footer {     
        text-align: right;
        border-top: none !important;
      }
      .addLabelHeight{
            margin-top: 32px;
      }
      a:hover {
            background-color:white;
      }
      #export_Excel{
        margin-bottom: 10px;
        
        
        
    }
    </style>

    <div class="box">

    <div class="modal fade"  role="dialog" id="getIOT">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 id='tp_data_title'><strong>GRN Number - </strong></h4> 
           
        </div>
        <div class="modal-body">
          <div>
<!--             <p class="document_no">Delivery : </p>
 -->    <button class="btn btn-primary" style="float: right" id="export_Excel" >Download</button>
      </div>
    <br>
        <div id='tp_list_grid'> 
        </div>
        </div>
        <div class="modal-footer">
          
        </div>
      </div>
    </div>
  </div>
  

<div class="box">  
<!--             <div class="loader" id="loader"></div>
 -->            
    <div class="box-body">
      <h3 class="box-title"><strong>GRN </strong> Report</h3>
      <br>
      <div id="tablegrid"></div>
         
    </div>
</div>
     
  </div>
  <style>
.loader {
  border: 5px solid #f3f3f3;
  border-top-color: rgb(243, 243, 243);
  border-top-style: solid;
  border-top-width: 5px;
  -webkit-animation: spin 1s linear infinite;
  animation: spin 1s linear infinite;
  border-top: 5px solid #555;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  display:none;
  position: absolute;
  opacity: 0.7;
  z-index: 99;
  text-align: center;
  opacity: .9;
}

/* Safari */
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>

</div>

    <script >
      

/*function download(document_no){
  alert(document_no);
        $("#tp_list_grid").jqxGrid('exportdata', 'csv', 'GRN Report'+document_no);
}
*/
 $("#export_Excel").click(function() {
  var doc=document.getElementById('tp_data_title').innerHTML;
  //var doc_num=doc.slice(12, 25);
 // alert(doc_num);
 var doc_num=doc.split(":")[1];
 //alert(doc_num);
        $("#tp_list_grid").jqxGrid('exportdata', 'csv', 'GRN Report -'+doc_num);
    });
   
    function getGrnIOT(document_no) {
            
                $('#getIOT').modal('toggle');
                var  url="/delivery/getGrnIOT/"+document_no;
                 $('#tp_data_title').empty(); 
                var div = document.getElementById('tp_data_title');
              // $('.document_no').append(document_no);
              
                div.innerHTML +='GRN Number : '+document_no;
               $.ajax({
                    type: "get",
                    url:url,
                    success: function(resultData) {

                    },
                    dataType: "json"
            });

            var source =
            {
              datatype: "json",
              datafields:
                [
                {name: 'primary_id', type: 'string'},
                {name: 'batch_no', type: 'string'},
          
                {name: 'material_code', type: 'string'},
                {name: 'description', type: 'string'},
                 
                {name: 'ean', type: 'string'},
                {name: 'pkg_qty', type: 'string'} 
                  
                ],
             url: url,
            pager: function (pagenum, pagesize, oldpagenum) {
            }
        };
            var dataAdapter = new $.jqx.dataAdapter(source);
            poconfrimGrid1(dataAdapter);
          
}

            var  cols1=[
            { text: 'IOT',  datafield: 'primary_id',width:200 },
            { text: 'Batch Number',  datafield: 'batch_no',width:100},
            { text: 'Product',  datafield: 'description',width:250},
            { text: 'Material Code',  datafield: 'material_code',width:100},
            { text: 'EAN',  datafield: 'ean',width:180},
            { text: 'Quantity',  datafield: 'pkg_qty',width:80}
           

             ];
var getdetailsforpo;
var createOrderpopup;
$(document).ready(function(){


/* GRID TO DISPLAY CONFIRMED PO */
createOrderpopup=function(){
 
// if(eseal_doc_no)
  $('#poModal').modal('toggle');
  
  
   var  url="";
   $.ajax({
  type: "POST",
  url: "",
  success: function(resultData) { 

  /*$('.createdDt').html(resultData.createdDt);
  $('.poQty').html(resultData.po_qty+' ('+resultData.po_uom+') ');
  $('.palQty').html(resultData.qty+' (PAL) ');
  $('.pkdQty').html(resultData.packedqty+' (PAL) ');
  $('.conQty').html(resultData.confirmQty+' (PAL) ');
  $('.cnfZ01').html(resultData.çonfirm_cartons);
  $('.cnfEA').html(resultData.confirm_EA);
  $('.productionRemark').html(resultData.remark);*/
   // alert("Save Complete"); 
   

},
  dataType: "json"
});

   var source =
          {
            //localdata:[],
              datatype: "json",
              datafields:
                [
                  {name: 'timestamp', type: 'string'},
                  {name: 'batch_no', type: 'string'},
                  {name: 'reference_value', type: 'string' },
                  {name:'qty',type:'string'},
                  {name: 'status',type:'string'},
                  {name: 'actions',type:'string'}
                ],
              url: url,
              pager: function (pagenum, pagesize, oldpagenum) {
                }

          };
             var dataAdapter = new $.jqx.dataAdapter(source);
                
                poconfrimGrid(dataAdapter);
}
 var  cols=[
                { text: 'Date',  datafield: 'timestamp',width:'15%'},
                { text: 'Batch No',  datafield: 'batch_no',width:'20%'},
                { text: 'Reference Value ', datafield:'reference_value',width:'20%'},
                {text: 'Quantity',datafield:'qty',width:'10%'} , 
                {text:'Status',datafield:'status',width:'20%'},              
                {text:'Action',datafield:'actions',width:'15%'}              
                ];
  
/* GRID TO DISPLAY  PO DETAILS  */  
getdetailsforpo=function ()
{

  var  url="/delivery/getPutaway";
   var source =
          {
            //localdata:[],
              datatype: "json",
              datafields:
                [
                  {name: 'document_no', type: 'string'},
                  {name: 'warehouse_no',type:'string'},
                  {name:'to_no',type:'string'},
                  {name: 'src_bin', type: 'string'},
                  {name: 'delivery_no', type: 'string'},
                  {name: 'Dest_Loc',type:'string'},
                  {name: 'tp_id',type:'string'},
                  /*{name: 'doc_date',type:'string'},
                  {name: 'grn_number',type:'string'},
                  {name: 'actions',type:'string'},*/
                  {name: 'status',type:'string'},
                  {name: 'actions',type:'string'},
                  {name: 'timestamp',type:'string'},
                  /*{name: 'qty',type:'string'},
                  {name: 'packed_qty',type:'string'}*/
                ],
              url: url,
              pager: function (pagenum, pagesize, oldpagenum) {
                }

          };

            console.log(source);
             var dataAdapter = new $.jqx.dataAdapter(source);
                
                createGrid(dataAdapter);
               
}
 var  columns=[
                { text: 'GRN number', datafield:'document_no',width:110 },
                { text: 'Warehouse No.',  datafield:'warehouse_no',width:110},
                { text: 'TO number', datafield: 'to_no',width:100},
                { text: 'STO', datafield:'src_bin',width:110},
                { text: 'Delivery No.', datafield:'delivery_no',width:110},
                { text: 'Receiving Plant', datafield:'Dest_Loc',width:120},
                /*{ text: 'Sending Plant', datafield: 'Source_Loc',width:120},*/
                { text: 'Master QR', datafield:'tp_id',width:180},
                { text: 'Status', datafield:'status',width:140},                
                { text: 'Created Date', datafield:'timestamp',width:180},
                { text: 'IOT', datafield:'actions',cellsalign: 'center',width:86}              
                ];

function createGrid(source)  
{
  $("#tablegrid").jqxGrid({

                        width: "100%",
                        source: source,
                        //selectionmode: 'multiplerowsextended',
                        selectionmode: 'multiplecellsextended',
                        sortable: false,
                        pageable: true,
                        autoheight: true,
                        autoloadstate: false,
                        autosavestate: false,
                        columnsresize: true,
                        columnsreorder: true,
                        showfilterrow: true,
                        filterable: true,
                        autoshowloadelement: false,
                        columns: columns              
    });

} 

function poconfrimGrid(source){
  $("#po_confirm_grid").jqxGrid({

                        /*width: "100%",
                        source: source,
                        //selectionmode: 'multiplerowsextended',
                        selectionmode: 'multiplecellsextended',
                        sortable: true,
                        pageable: true,
                        autoheight: true,
                        autoloadstate: false,
                        autosavestate: false,
                        columnsresize: true,
                        columnsreorder: true,
                        showfilterrow: true,
                        autoshowloadelement: false,
                        filterable: true,
                        columns: cols */


                        source: source,

                        //selectionmode: 'multiplerowsextended',
                        selectionmode: 'multiplecellsextended',
                        sortable: false,

                        pageable: true,

                        autoheight: true,

                        autoloadstate: false,

                        autosavestate: false,

                        columnsresize: true,

                        //columnsreorder: true,

                        showfilterrow: true,

                        autoshowloadelement: false,

                        filterable: true,

                        columns: cols                   
    });
}
    getdetailsforpo();
});

function poconfrimGrid1(source){

  $("#tp_list_grid").jqxGrid({

 

                        width: "100%",

                        source: source,

                        //selectionmode: 'multiplerowsextended',
                        selectionmode: 'multiplecellsextended',
                        sortable: false,

                        pageable: true,

                        autoheight: true,

                        autoloadstate: false,

                        autosavestate: false,

                        columnsresize: true,

                        columnsreorder: true,

                        showfilterrow: true,

                        autoshowloadelement: false,

                        filterable: true,

                        columns: cols1           

    });

}
    </script>
    @stop
