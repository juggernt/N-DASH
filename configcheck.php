<div class="row row-offcanvas row-offcanvas-left">
  <div class="col-sm-3 col-md-2 sidebar-offcanvas" id="sidebar" role="navigation">
   
    <ul class="nav nav-sidebar">
      <li><a href="?page=settings">Settings</a></li>
<!--      <li><a href="?page=upgrade">Upgrade</a></li> -->
      <li class="active"><a href="?page=configcheck">Config Check</a></li>
      <li><a href="?page=debug">Debug Console</a></li>
      <li><a href="?page=changelog">Changelog</a></li>
    </ul>
  </div>
 
  <div class="col-sm-9 col-md-10 main">
  
    <p class="visible-xs">
    <button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas"><i class="glyphicon glyphicon-chevron-left"></i></button>
    </p>
  
    <h1 class="page-header">
    Config Check
    <p class="lead">N-DASH configuration check</p>
    </h1>
    
    <div class="panel panel-default">
      <div class="panel-heading"><b>Prerequisites</b></div>
      <div class="panel-body">
		<?php echo checkRequiredPackages(); ?>
      </div>
    </div>
  
    <div class="panel panel-default">
      <div class="panel-heading"><b>Novo</b></div>
      <div class="panel-body">
		<?php
		$guldenconfperms = getFilePermissions($CONFIG['datadir']."novo.conf");
		if($guldenconfperms['exists']) {
			echo "The owner of novo.conf is \"".$guldenconfperms['owner']['name']."\"<br>";
			echo "The permissions of novo.conf are \"".$guldenconfperms['permissions']."\"<br>";
			if($guldenconfperms['readable']) { echo "<font color='green'>novo.conf is readable</font>"; } else { echo "<font color='red'>novo.conf is not readable</font>"; }
		} else {
			echo "<font color='red'>novo.conf does not exist! Define the correct datadir in the settings.</font>";
		}
		
		echo "<br><br>";
		
		$guldendperms = getFilePermissions($CONFIG['guldenlocation']."Novo-daemon");
		if($guldendperms['exists']) {
			echo "The owner of Novo-daemon is \"".$guldendperms['owner']['name']."\"<br>";
			echo "The permissions of Novo-daemon are \"".$guldendperms['permissions']."\"<br>";
			if($guldendperms['executable']) { echo "<font color='green'>Novo-daemon is executable</font>"; } else { echo "<font color='red'>Novo-daemon is not executable</font>"; }
		} else {
			echo "<font color='red'>Novo-daemon does not exist! Define the correct Novo directory in the settings.</font>";
		}
		
		echo "<br><br>";
		
		$guldencliperms = getFilePermissions($CONFIG['guldenlocation']."Novo-cli");
		if($guldencliperms['exists']) {
			echo "The owner of Novo-cli is \"".$guldencliperms['owner']['name']."\"<br>";
			echo "The permissions of Novo-cli are \"".$guldencliperms['permissions']."\"<br>";
			if($guldencliperms['executable']) { echo "<font color='green'>Novo-cli is executable</font>"; } else { echo "<font color='red'>Novo-cli is not executable</font>"; }
		} else {
			echo "<font color='red'>Novo-cli does not exist! Define the correct Novo directory in the settings.</font>";
		}
		
		echo "<br><br>";
		
		$guldenlogperms = getFilePermissions($CONFIG['datadir']."debug.log");
		if($guldenlogperms['exists']) {
			echo "The owner of debug.log is \"".$guldenlogperms['owner']['name']."\"<br>";
			echo "The permissions of debug.log are \"".$guldenlogperms['permissions']."\"<br>";
			echo "The file size of debug.log is \"".round(filesize($CONFIG['datadir']."debug.log") / pow(1024, 2), 2)."\" MB <br>";
			if($guldenlogperms['readable']) {
				echo "<font color='green'>debug.log is readable</font>"; 
			} else {
				echo "<font color='red'>debug.log is not readable. To make this file readable for N-DASH, use </font><code>chmod 0644 ".$CONFIG['datadir']."debug.log</code>"; }
		} else {
			echo "<font color='red'>debug.log does not exist! Define the correct datadir in the settings.</font>";
		}
		?>
      </div>
    </div>
  
    <div class="panel panel-default">
      <div class="panel-heading"><b>Listening services</b></div>
      <div class="panel-body">
		<?php
		echo getGuldenServices();
		?> 
      </div>
    </div>
  
    <div class="panel panel-default">
      <div class="panel-heading"><b>Full Node port forward</b></div>
      <div class="panel-body">
		<?php
		//	$checks = fullNodeCheck();
			
		//	foreach ($checks as $check) {
		//		echo $check . "<br />";
		//	}
            echo 'No longer available';
		?>
      </div>
    </div>
  
    <div class="panel panel-default">
      <div class="panel-heading"><b>N-DASH</b></div>
      <div class="panel-body">
		<?php
		if(!is_writable("config/config.php")) {
			echo "<font color='red'>The configuration file (config/config.php) is not writable. Make sure the webserver (usually 'www-data') has write permissions.<br>
				 You can set www-data as the owner of the N-DASH folder by using the command 'sudo chown -R www-data:www-data /path/to/n-dash/'.</font><br><br>";
		}
		if($guldenconfperms['exists']) {
			$gconfcontents = readGuldenConf($CONFIG['datadir']."novo.conf");
			if($gconfcontents['rpcuser'] == $CONFIG['rpcuser']) {
				echo "<font color='green'>Username entered in N-DASH matches Novo username</font><br>"; 
			} else {
				echo "<font color='red'>Username entered in N-DASH does not match Novo username</font><br>"; 
			}
			if($gconfcontents['rpcpassword'] == $CONFIG['rpcpass']) {
				echo "<font color='green'>Password entered in N-DASH matches Novo password</font><br>"; 
			} else {
				echo "<font color='red'>Password entered in N-DASH does not match Novo password</font><br>"; 
			}			
		} else {
			echo "<font color='red'>novo.conf does not exist! Define the correct datadir in the settings.</font>";
		}
		?>
      </div>
    </div>
  </div>
</div>
