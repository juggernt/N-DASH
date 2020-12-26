<?php
session_start();

//In case the server is very busy, lower the max execution time to 60 seconds
set_time_limit(60);

if($_SESSION['N-DASH-loggedin']==TRUE) {
include('../lib/functions/functions.php');
include('../config/config.php');
include('../lib/settings/settings.php');
require_once('../lib/EasyGulden/easygulden.php');
$gulden = new Gulden($CONFIG['rpcuser'],$CONFIG['rpcpass'],$CONFIG['rpchost'],$CONFIG['rpcport']);

$guldenD = "Novo-daemon";
$guldenCPU = GetProgCpuUsage($guldenD);
$guldenMEM = GetProgMemUsage($guldenD);
$returnarray = array();

$localdate = new DateTime();
$localtz = $localdate->getTimezone();

session_write_close();

if($guldenCPU > 0 && $guldenMEM > 0) {
	$ginfo = $gulden->getinfo();
	$gerrors = $ginfo['errors'];	
	
	//Get all "Normal" mining accounts (don't show deleted accounts) 
	$accountlist = $gulden->listaccounts("*", "Normal");
		
	//Only list mining accounts
	$accountlist = selectElementWithValue($accountlist, "type", "Mining");
		
	//If the user selected an account from the menu
	if (isset($_GET['account'])) { $selectedaccount = $_GET['account']; } else { $selectedaccount = ""; }
	
	if ($selectedaccount != "") {
        $selectedaccount = $_GET['account'];
    } else {
		//Select the first account
		$selectedaccount = $accountlist[0]['UUID'];
	}
	
	// Get mining reward address
	$miningaccounts = array();
	foreach ($accountlist as $account) {
        $getrewardaddress = $gulden->getminingrewardaddress();
        $rewardaddress = $getrewardaddress['address'];
        if ($rewardaddress == '') $rewardaddress = $gulden->getnewaddress($account['UUID']);
        $account['rewardaddress'] = $rewardaddress;
        array_push($miningaccounts, $account);
    }
	
	// Get mining info
	$mininginfo = $gulden->getmininginfo();
	
	// Get Mining state
	$generate = $gulden->getgenerate();
	
	// Get mining statistics
	$statistics = array();
	if ($generate == "true") {
        // Is this account busy mining?
        if (array_key_exists($selectedaccount, $CONFIG['mining'])) {
            $stats = array();
            $stats = $gulden->gethashps();
            $statistics['last_reported'] = $stats['last_reported'];
            $statistics['rolling_average'] = $stats['rolling_average'];
            $statistics['best_reported'] = $stats['best_reported'];
        }
    }
	
	// Populate return values
    $returnarray['errors'] = $gerrors;
    $returnarray['miningstate'] = $generate;
    $returnarray['selectedaccount'] = $selectedaccount;
    $returnarray['accountlist'] = $miningaccounts;
    $returnarray['statistics'] = $statistics;
    $returnarray['mininginfo'] = $mininginfo;
    
	// Get numproc and memsize
	if (array_key_exists($selectedaccount, $CONFIG['mining'])) {
        $returnarray['numproc'] = $CONFIG['mining'][$selectedaccount]['numproc'];
        $returnarray['memsize'] = $CONFIG['mining'][$selectedaccount]['memsize'];
    }
    else {
        $returnarray['numproc'] = '0';
        $returnarray['memsize'] = '0';
    }
}
else {
    $returnarray['errors'] = '';
    $returnarray['miningstate'] = '';
    $returnarray['selectedaccount'] = '';
    $returnarray['accountlist'] = '';
	$returnarray['numproc'] = 0;
	$returnarray['memsize'] = 0;
	$returnarray['statistics'] = '';
    $returnarray['mininginfo'] = '';
}
echo json_encode($returnarray);
}
?>
