<div class="row row-offcanvas row-offcanvas-left">

 <div class="col-sm-3 col-md-2 sidebar-offcanvas" id="sidebar" role="navigation">
   
    <ul class="nav nav-sidebar">
      <li><a href="?page=settings">Settings</a></li>
<!--      <li><a href="?page=upgrade">Upgrade</a></li> -->
      <li><a href="?page=configcheck">Config Check</a></li>
      <li><a href="?page=debug">Debug Console</a></li>
      <li class="active"><a href="?page=changelog">Changelog</a></li>
    </ul>
 </div><!--/span-->

 <div class="col-sm-9 col-md-10 main">
  
  <!--toggle sidebar button-->
  <p class="visible-xs">
    <button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas"><i class="glyphicon glyphicon-chevron-left"></i></button>
  </p>
  
  <h1 class="page-header">
    Changelog
    <p class="lead">N-DASH changelog</p>
  </h1>
  
  <div class="panel panel-default">
    <div class="panel-heading"><b>1.0</b></div>
    <div class="panel-body">
		<ul>
            <li>N-DASH forked from G-DASH 1.3</li>
			<li>BUG: Adding witness keys needed an extra parameter in RPC.</li>
			<li>ENHANCEMENT: Removed exchanges GT and Nocks.</li>
			<li>ENHANCEMENT: CoinGecko exchange added</li>
			<li>BUG: Changed the way that the list of last 30 wallet transactions is derived and displayed.</li>
			<li>BUG: Gulden earned by witness now correct displayed.</li>
        </ul>
        Changes regarding conversion Gulden to Novo:
        <ul>
			<li>All references to 'GuldenD' changed to 'Novo-daemon'.</li>
			<li>All references to 'Gulden-cli' changed to 'Novo-cli'.</li>
			<li>All references to 'G-DASH' changed to 'N-DASH' where applicable.</li>
			<li>All references to 'witness' changed to 'holding' or 'holder' where applicable.</li>
			<li>G-DASH version check removed.</li>
			<li>Novo version check adapted to Novo release on GitHub.</li>
			<li>DASHBOARD: Block# is a link to dactual.com for more details.</li>
			<li>DASHBOARD: 'Total value out' added to block data (needs txindex=1 in novo.conf, otherwise 'N/A').</li>
			<li>NODE: Same layout for all items.</li>
			<li>WALLET: More compact layout and Novo exchange rate added to wallet details.</li>
			<li>HOLDING: Extend holding account added to holding drop down menu.</li>
			<li>MINING: Mining function added.</li>
			<li>SETTINGS/Settings/Wallet: Stex exchange added.</li>
			<li>SETTINGS/Config Check: Full Node Port Forward is no longer supported.</li>
		</ul>
    </div>
  </div>
  

  </div><!--/row-->
</div>
