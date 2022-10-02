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
    <div class="panel-heading"><b>v2.2</b></div>
    <div class="panel-body">
		<ul>
            <li>Made arena setup configurable</li>
            <li>Cron notifications every 60 min.</li>
            <li>Stex: Florin trading pair NGL/XFL added</li>
            <li>Array checks added to avoid php errors</li>
        </ul>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading"><b>v2.1</b></div>
    <div class="panel-body">
		<ul>
            <li>Change novo to florin in dactual block link on dashboard</li>
            <li>Total value out adapted to changed rewards</li>
            <li>Mining changed (arena added in Novo/Florin 2.0.4)</li>
        </ul>
    </div>
  </div>
  
  <div class="panel panel-default">
    <div class="panel-heading"><b>v2.0</b></div>
    <div class="panel-body">
		<ul>
            <li>Total value out calculation adjusted</li>
            <li>Cron for stats removed</li>
            <li>Node tab removed from N-DASH settings</li>
            <li>Check for duplicate names on creating holding accounts</li>
            <li>Version check for N-DASH and Florin only once after login</li>
            <li>Adapted for rename Novo to Florin (see N-DASH manual)
        </ul>
    </div>
  </div>
  
  <div class="panel panel-default">
    <div class="panel-heading"><b>v1.2</b></div>
    <div class="panel-body">
		<ul>
            <li>Total value out calculation adjusted</li>
            <li>Exchange rate not available handled correctly</li>
            <li>CoinRequest link updated</li>
        </ul>
    </div>
  </div>
  
  <div class="panel panel-default">
    <div class="panel-heading"><b>v1.1</b></div>
    <div class="panel-body">
		<ul>
            <li>N-DASH version check at login added</li>
			<li>Settings/Settings/Notifications: 'Send a notification if an update for N-DASH is available' enabled</li>
        </ul>
    </div>
  </div>
  
  <div class="panel panel-default">
    <div class="panel-heading"><b>v1.0</b></div>
    <div class="panel-body">
		<ul>
            <li>N-DASH forked from G-DASH 1.3</li>
			<li>BUG: Adding witness keys needed an extra parameter in RPC</li>
			<li>ENHANCEMENT: Removed exchanges GT and Nocks</li>
			<li>ENHANCEMENT: CoinGecko exchange added</li>
			<li>BUG: Changed the way that the list of last 30 wallet transactions is derived</li>
			<li>BUG: Gulden earned by witness displayed correctly</li>
        </ul>
        Changes regarding conversion Gulden to Novo:
        <ul>
			<li>All references to 'GuldenD' changed to 'Novo-daemon'</li>
			<li>All references to 'Gulden-cli' changed to 'Novo-cli'</li>
			<li>All references to 'G-DASH' changed to 'N-DASH' where applicable</li>
			<li>All references to 'witness' changed to 'holding' or 'holder' where applicable</li>
			<li>G-DASH version check removed</li>
			<li>Novo version check adapted to Novo release on GitHub</li>
			<li>DASHBOARD: Block# is a link to dactual.com for more details</li>
			<li>DASHBOARD: 'Total value out' added to block data (needs txindex=1 in novo.conf, otherwise 'N/A')</li>
			<li>NODE: Same layout for all items</li>
			<li>WALLET: More compact layout and Novo exchange rate added to wallet details</li>
			<li>HOLDING: Extend holding account added to holding drop down menu</li>
			<li>MINING: Mining function added</li>
			<li>Settings/Settings/Wallet: Stex exchange added</li>
			<li>Settings/Config Check: Full Node Port Forward is no longer supported</li>
		</ul>
    </div>
  </div>
  

  </div><!--/row-->
</div>
