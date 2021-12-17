@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
    {{HTML::style('css/bootstrap-select.css')}}
    <!-- {{HTML::script ('jqwidgets/jqxpopover.js')}} -->
    @stop
    
    
<!DOCTYPE html>
<html>
<head>
	<title>Excel import</title>
</head>

<body>

	<div  class="box">
        <div  class="box-body">
             <h3 class="box-title"><strong>Close </strong> Process Orders</h3>
             <br>
            
                  
        @if(Session::has('success'))
	        <div class="alert alert-success" id="success">
        	<button type="button" class="close" data-dismiss="alert" onclick="hide();">X</button>        
            <strong>{{ Session::get('success')}}</strong>        
            </div> 
        @elseif(Session::has('Fail'))
        <div class="alert alert-danger" id="danger">
            <button type="button" class="close" data-dismiss="alert" onclick="hide2();">X</button>        
            <strong>{{ Session::get('Fail')}}</strong>        
            </div> 
        @endif	
<div>
<span style="color: red;">*Upload files with extensions - xls , xlsx</span></div>
<br>
  <form action="{{ route('export_Data') }}" method="POST"  enctype="multipart/form-data">
    @csrf
    <div class="row">
    <div class="col-sm-6">
    
    <input type="file" name="files" id="files" required >
    </div>
    <input type="submit"  value="Import" class="btn btn-primary" style="">
        
    <a class="btn btn-green" href="/download/ClosedProcessOrdersTemplate">Downlaod Sample Excel</a>
    </div>
    </form>      
	</div>
	</div>
</body>
<script type="text/javascript">
    function hide(){
        $.ajax({
        url:"/reset",
        method:"GET",
        data:{},
        success:function(data) {
        $("#success").hide();
        $("#danger").hide();
       
        }
        });
    
    }
    function hide2(){
        $.ajax({
        url:"/reset",
        method:"GET",
        data:{},
        success:function(data) {
        $("#success").toggle();
        $("#danger").toggle();
       console.log("in success"+data);
        }
        });
    
    }
    $( document ).ready(function() {
    setTimeout(hide2, 5000);
});
    
</script>
</html>
 @stop