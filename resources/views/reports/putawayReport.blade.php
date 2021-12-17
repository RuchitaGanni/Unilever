@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
@if($errors->any())
<h4>{{$errors->first()}}</h4>
@endif

<div class="box">
<div class="box-body">

    @if(session('status')!==NULL)
    <div class="alert alert-success">
        <strong>hii</strong>
    </div>
    @endif
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
<style>
.btn{margin-left:10px !important;}
.panel-heading .accordion-toggle:after {
    /* symbol for "opening" panels */
    font-family: 'Glyphicons Halflings';  /* essential for enabling glyphicon */
    content: "\e114";    /* adjust as needed, taken from bootstrap.css */
    float: right;        /* adjust as needed */
    color: grey;         /* adjust as needed */
}
.panel-heading .accordion-toggle.collapsed:after {
    /* symbol for "collapsed" panels */
    content: "\e080";    /* adjust as needed, taken from bootstrap.css */
}

</style>
<style type="text/css">
    .error {
        color: red;
    }
    .modal-header h4{margin-bottom:0px !important}
    .fileinput-button i{position:absolute; z-index:-99999!important;}
    .form-control-feedback{top:0px !important;}
    .checkbox input[type=checkbox], .checkbox-inline input[type=checkbox], .radio input[type=radio], .radio-inline input[type=radio]{margin-left:0px !important;}
    .col-sm-1{padding-left:0px;}
    h4{margin-bottom:20px !important;}
</style>



<div><span class="error_message"></span></div>
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs pull-right">
            
            <!-- <li><a href="#transaction" data-toggle="tab">Transaction</a></li>
            <li><a href="#orders" data-toggle="tab">Orders</a></li>
            <li><a href="#products" data-toggle="tab">Products</a></li> -->
            <!-- <li ><a href="#location_types" >Location Types</a></li>         -->
            <!-- <li><a href="#erp_configuration" data-toggle="tab">ERP Configuration</a></li>
            <li class=""><a href="#tax_class" data-toggle="tab" aria-expanded="true">Tax Class</a></li>
            <li><a href="#price_management" data-toggle="tab" aria-expanded="true">Pricing Contract</a></li>
            <li><a href="#eseal_products" data-toggle="tab" aria-expanded="false">eSeal Products</a></li>                          
            <li class="active"><a href="#basic" data-toggle="tab" aria-expanded="false">Basic</a></li> -->
            <li class="pull-left header"><i class="fa fa-th"></i> Putaway Report</li>
        </ul>                
    </div>
    <?php
    $customer_id = isset($customer_id) ? $customer_id : '';
    ?>
    <input type="hidden" id="customer_id" name="customer_id" value="{{ $customer_id }}" />
    <!-- tile body -->
    <div class="row">
                <div class="form-group col-sm-12">
                    <div id="locations_treegrid"></div>
                </div>
            </div>

<div id="customer_approval" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- dialog body -->
      <div class="modal-body">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        Do you want to approve.

      </div>
      <!-- dialog buttons -->
      <div class="modal-footer"><button type="button" class="btn btn-primary">Yes</button><button type="button" class="btn btn-danger">No</button></div>      
    </div>
  </div>
</div>

<div class="col-sm-12">
 <div class="tile-body nopadding">                  
    <div id="jqxgrid"  style="width:100% !important;"></div>
     <button data-toggle="modal" id="edit" class="btn btn-default" data-target="#wizardCodeModal" style="display: none"></button>
    </div>
</div>

<div class="state_name" id="state_name" style="display: none;">
    <label for="opt01">Option 1</label>
</div>
<div class="loader" id="loader"></div>
<div class="modal-body">
    <div id="del_grid">
        
    </div>

</div>
<div class="row" id="checkboxTemplate" style="display: none;">
    <div class="form-group col-sm-6">
        <div class="checkbox">
            <input type="checkbox" value="1" id="opt01" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated">
            <label for="opt01">Option 1</label>
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
  margin-left: 50%;
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
@stop

@section('style')
{{HTML::style('jqwidgets/styles/jqx.base.css')}}
{{HTML::style('css/bootstrap-select.css')}}
{{HTML::style('css/datepicker.min.css')}}
{{HTML::style('css/jquery.fileupload.css')}}
@stop

@section('script')
<!-- location data -->
{{HTML::script('jqwidgets/jqxcore.js')}}
{{HTML::script('jqwidgets/jqxbuttons.js')}}
{{HTML::script('js/plugins/dragdrop/fieldChooser.js')}}
{{HTML::script('jqwidgets/jqxscrollbar.js')}}
{{HTML::script('js/plugins/dragdrop/jquery-ui.js')}}
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
{{HTML::script('jqwidgets/jqxcheckbox.js')}}
{{HTML::script('jqwidgets/jqxdatatable.js')}}
{{HTML::script('jqwidgets/jqxtreegrid.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-select.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-datepicker.min.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-multiselect.js')}}
{{HTML::script('js/plugins/jquery-file-upload/vendor/jquery.ui.widget.js')}}
{{HTML::script('js/plugins/jquery-file-upload/load-image.all.min.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.iframe-transport.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-process.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-image.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-audio.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-video.js')}}
{{HTML::script('js/plugins/jquery-file-upload/jquery.fileupload-validate.js')}}
{{HTML::script('js/plugins/jquery-file-upload/customer-upload-script.js')}}
{{HTML::script('scripts/demos.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-select.js')}}
{{HTML::script('js/plugins/bootstrap-select/bootstrap-multiselect.js')}}
{{HTML::script('js/plugins/bootstrap-select//bootstrap-datepicker.min.js')}}

{{HTML::script('js/plugins/validator/formValidation.min.js')}}
{{HTML::script('js/plugins/validator/validator.bootstrap.min.js')}}
{{HTML::script('js/plugins/validator/jquery.bootstrap.wizard.min.js')}}
<!-- location data end -->

<script type="text/javascript">
$(document).ready(function ()
{   
    var url = "putawayReportList";
    // prepare the data
    var source =
    {
        datatype: "json",
        datafields: [
            { name: 'document_no', type: 'varchar' },
            { name: 'stock_type', type: 'varchar' },
            { name: 'to_no', type: 'varchar' },
            { name: 'qty', type: 'varchar' },
            { name: 'material_code', type: 'varchar' },
            { name: 'batch', type: 'varchar' },
            { name: 'dest_bin', type: 'varchar' },
            { name: 'date', type: 'varchar' },
            { name: 'description', type: 'varchar' },
            { name: 'status', type: 'varchar' },
        ],
        id: 'id',
        url: url,
        pager: function (pagenum, pagesize, oldpagenum) {
            // callback called when a page or page size is changed.
        }
    };

    var dataAdapter = new $.jqx.dataAdapter(source);
    console.log(dataAdapter);
    $("#jqxgrid").jqxGrid(
    {
        width: "100%",
        source: source,
        selectionmode: 'multiplerowsextended',
        sortable: false,
        pageable: true,
        autoheight: true,
        autoloadstate: false,
        autosavestate: false,
        columnsresize: true,
        columnsreorder: true,
        showfilterrow: true,
        filterable: true,
        pagesize: 50,
        columns: [
          { text: 'Date', datafield: 'date', width: "11%" },
          { text: 'Material Code', datafield: 'material_code', width: "10%" },
          { text: 'Material Discreption', datafield: 'description', width: "20%" },
          { text: 'Document Number', datafield: 'document_no', width: "10%" },
          { text: 'Stock Type',  datafield: 'stock_type', width: "7%" },
          { text: 'TO NO', datafield: 'to_no', width:"5%"},
          { text: 'Quantity', datafield: 'qty', width:"7%"},
          { text: 'Batch', datafield: 'batch', width: "10%" },
          { text: 'Destination Bin', datafield: 'dest_bin', width: "10%" },
          { text: 'Status', datafield: 'status', width: "10%" },
        ]              

    });
});
</script>
<!-- transaction data end -->
@stop
@extends('layouts.footer')