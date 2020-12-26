<link rel="stylesheet" href="js/jquery-ui/css/jquery-ui.min.css">
<script src="js/dash/mining.js?<?php echo $CONFIG['dashversion']; ?>"></script>
<script src="js/jquery-ui/js/jquery-ui.min.js?<?php echo $CONFIG['dashversion']; ?>"></script>

<div class="row row-offcanvas row-offcanvas-left">

 <div class="col-sm-3 col-md-2 sidebar-offcanvas" id="sidebar" role="navigation">
   
    <ul class="nav nav-sidebar">
      <li><a href="?">Overview</a></li>
      <li><a href="?page=guldend">Novo-daemon</a></li>
      <li><a href="?page=node">Node</a></li>
      <li><a href="?page=wallet">Wallet</a></li>
      <li><a href="?page=witness">Holding</a></li>
      <li class="active"><a href="?page=mining">Mining</a></li>
    </ul>
 </div><!--/span-->

 <div class="col-sm-9 col-md-10 main">
  
  <!--toggle sidebar button-->
  <p class="visible-xs">
    <button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas"><i class="glyphicon glyphicon-chevron-left"></i></button>
  </p>
  
  <h1 class="page-header">
    Mining
  </h1>
  
  <div id="errordiv"></div>
  
  
  <!-- Add account modal content-->
  	<div id="addminingaccount" class="modal fade" role="dialog">
	  <div class="modal-dialog">
	    <div class="modal-content" name="addminingaccountmodal" id="addminingaccountmodal">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal">&times;</button>
	        <h4 class="modal-title">Create a new mining account</h4>
	      </div>
	      
	      <div class="modal-body">
	      	<div class="form-group">
		      <label for="newaccountname"><small>Account name (Normal letters only and no &#42; allowed)</small></label><br>
		      <input id="newaccountname" name="newaccountname" type="text" class="form-control">
		    </div>
		    <div class="form-group">
		      <label for="confirmwithdrawpass"><small>Pass Phrase</small></label><br>
			  <input type="password" id="newaccountpassword" name="newaccountpassword" autocomplete='off'>
		    </div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-success" id="creataccountbutton" onclick="addAccount()">Create account</button>
	      </div>
	      <div id="creationmessage" name="creationmessage"></div>
	    </div>	
	  </div>
	</div>
	<!-- End add account modal content-->

	<!-- Rename account modal content-->
  	<div id="renameaccount" class="modal fade" role="dialog">
	  <div class="modal-dialog">
	    <div class="modal-content" name="renameaccountmodal" id="renameaccountmodal">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal">&times;</button>
	        <div id="renameaccounttitle"><h4 class="modal-title">Rename account</h4></div>
	      </div>
	      
	      <div class="modal-body">
	      	<div class="form-group">
		      <label for="renameaccountname"><small>Account name (Normal letters only and no &#42; allowed)</small></label><br>
		      <input id="renameaccountname" name="renameaccountname" type="text" class="form-control">
		    </div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-success" data-dismiss="modal" onclick="renameWitnessAccount()">Rename account</button>
	      </div>
	      
	    </div>	
	  </div>
	</div>
	<!-- End rename account modal content-->
	
	<!-- Delete account modal content-->
  	<div id="deleteaccount" class="modal fade" role="dialog">
	  <div class="modal-dialog">
	    <div class="modal-content" name="deleteaccountmodal" id="deleteaccountmodal">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal">&times;</button>
	        <div id="deleteaccounttitle"><h4 class="modal-title">Delete account</h4></div>
	      </div>
	      
	      <div class="modal-body">
	      	<div class="form-group">
		      <label for="selectedminingaccountdelete"><small>Selected mining account</small></label><br>
		      <input id="selectedminingaccountdelete" name="selectedminingaccountdelete" type="text" class="form-control" readonly>
		    </div>
		    
	      	Are you sure you want to delete this account? By pressing "yes" this account will be removed from every overview.
	      	<br><br>
	      	<div class="form-group">
		      <label for="delpass"><small>Unlock your wallet with your password</small></label><br>
		      <input id="delpass" name="delpass" type="password" class="form-control" autocomplete='off'>
		    </div>
	      <div id="showdelresponse" name="showdelresponse"></div>
	    </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-danger" id="confirmdelcancel" name="confirmdelcancel" onclick="confirmDeleteAccount()">Cancel</button>
	        <button type="button" class="btn btn-danger" id="confirmdelsubmit" name="confirmdelsubmit" onclick="confirmDeleteAccount('true')">Yes</button>
	      </div>
	    </div>	
	  </div>
	</div>
	<!-- End delete account modal content-->
  
    <!-- Change reward address modal content-->
  	<div id="changeaddress" class="modal fade" role="dialog">
	  <div class="modal-dialog">
	    <div class="modal-content" name="changeaddressmodal" id="changeaddressmodal">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal">&times;</button>
	        <h4 class="modal-title">Specify a new reward address</h4>
	      </div>
	      
	      <div class="modal-body">
	      	<div class="form-group">
		      <label for="currentrewardaddress"><small>Current reward address</small></label><br>
		      <input id="currentrewardaddress" name="currentrewardaddress" type="text" class="form-control" readonly>
		    </div>
		    
	      	<div class="form-group">
		      <label for="newrewardaddress"><small>New reward address</small></label><br>
		      <input id="newrewardaddress" name="newrewardaddress" type="text" class="form-control">
		    </div>
            <div id="showchangeresponse" name="showchangeresponse"></div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-success" id="changeaddressbutton" onclick="changeAddress()">Change address</button>
	      </div>
	      <div id="changemessage" name="changemessage"></div>
	    </div>	
	  </div>
	</div>
	<!-- End change reward address modal content-->
  
  <div class="row">
  	<div class="col-md-3">
  		<div class="panel panel-default">
  			<div class="panel-heading" id="miningactionsheader">Mining actions</div>
		    <div class="panel-body" id="miningactionspanel">
		    	<img src="images/loading.gif" border="0"> Loading....
		    </div>
  		</div>
  		
  		<div class="panel panel-default">
  			<div class="panel-heading" id="miningaccountsheader">Mining account</div>
		    <div class="panel-body" id="miningaccountspanel">
		    	<img src="images/loading.gif" border="0"> Loading....
		    </div>
  		</div>
  	</div>
  	<div class="col-md-9">
  		<div class="panel panel-default">
		    <div class="panel-heading" id="currentaccountname">Current account</div>
                <div class="panel-body" id="mpb">
                    <div id="miningpanelbody" style="margin: 0px 15px">
                        <img src="images/loading.gif" border="0"> Loading....
                    </div>
                    <div id="slider" style="margin: auto; margin-top: 10px; width: 80%"><div id="numproc" class="ui-slider-handle"></div></div>
                    <div id="miningpanelbody2" style="margin-top: 20px; text-align: center"></div>
                    <div id="slider2" style="margin: auto; margin-top: 10px; width: 80%"><div id="memsize" class="ui-slider-handle"></div></div>
                    <div id="miningbutton" style="margin:auto; margin-top: 20px; width: 80%"></div>
                    <div id="miningpanelbody4" style="margin: 30px 15px"></div>
                    <div id="generateresult" style="margin: auto; width: 80%"></div>
                </div>
            </div>
		</div>
  	</div>
  </div><!--/row-->
</div>
