<?php
session_start();

//In case the server is very busy, lower the max execution time to 60 seconds
set_time_limit(60);

if($_SESSION['N-DASH-loggedin']==TRUE) {
include('../lib/functions/functions.php');
include('../lib/settings/settings.php');
include('../config/config.php');
require_once('../lib/EasyGulden/easygulden.php');
$gulden = new Gulden($CONFIG['rpcuser'],$CONFIG['rpcpass'],$CONFIG['rpchost'],$CONFIG['rpcport']);

$guldenD = "Novo-daemon";
$guldenCPU = GetProgCpuUsage($guldenD);
$guldenMEM = GetProgMemUsage($guldenD);
$returnarray = array();

$nodeconfig = readGuldenConf($CONFIG['datadir']."/novo.conf");

session_write_close();

if($guldenCPU > 0 && $guldenMEM > 0) {
	//Novo-daemon info
	$ginfo = $gulden->getinfo();
	$gerrors = $ginfo['errors'];
	
// @1.0	if($nodeconfig['disablewallet']!="1") {
	if(!isset($nodeconfig['disablewallet']) || $nodeconfig['disablewallet'] != "1") { // @1.0
		//Check if the wallet is locked/unlocked
		//Error -15: Not encrypted
		//Error -1: Encrypted & locked
		//Error -17: Encrypted & unlocked
		$gulden->walletpassphrase('', '1');
		$encryptioncheck = $gulden->response['error']['code'];
		if($encryptioncheck != "-15") {
			$gulden->getwalletinfo();
			$checkunlock = $gulden->response['result']['unlocked_until'];
			if($checkunlock > 0) {
				$encryptioncheck = "-17";
			} elseif ($checkunlock == 0) {
				$encryptioncheck = "-1";
			}
		}

		//Check if sync is at 100%
		$currentblock = $ginfo['blocks'];
		$peerinfo = $gulden->getpeerinfo();
		$gallblocks = $peerinfo[0]['synced_headers'];
		$gblockspercent = floor(($currentblock/$gallblocks)*100);
		
		//List all accounts, don't show deleted accounts 
		$accountlist = $gulden->listaccounts("*", "Normal");
		
		//List all accounts, including deleted accounts (comment above, uncomment this one)
		//$accountlist = $gulden->listaccounts();
		
		//Only list wallet accounts
		$accountlist = selectElementWithValue($accountlist, "type", "Desktop");
		
		//TODO: This can be removed after a while, as this is a "between" solution for 
		//Gulden 1.6.10 -> 2.0. Account management changed, so no accounts are visible
		//on 1.6.10 for users who upgraded G-DASH
		if(count($accountlist)==0) {
			$gerrors = "Your Gulden needs to be updated to 2.0 before you can see your accounts again";
		}
		
		//If the users selected an account from the menu
		if(isset($_GET['account'])) { $selectedaccount = $_GET['account']; } else { $selectedaccount = ""; }
		
		if($selectedaccount!="") {
			$selectedaccount = $_GET['account'];
		} else {
			//Select the first account
			$selectedaccount = $accountlist[0]['UUID'];
		}
		
		//Get all addresses from this account
		$addresslist = $gulden->getaddressesbyaccount($selectedaccount);
		
		//If there is no address for this account, create a new one
		if(count($addresslist)==0) {
			$gulden->getnewaddress($selectedaccount);
		}
		
		//Check the address list again and get the latest address
		//This does not work as it is listed in alphabetic order...
		//DONE: Create a workaround
		$addresslist = $gulden->getaddressesbyaccount($selectedaccount);
		$latestaddress = $addresslist[(count($addresslist)-1)];
		
		//Get the total balance of single account
		$confirmedbalance = round($gulden->getbalance($selectedaccount),2);
		$unconfirmedbalance = $gulden->getunconfirmedbalance($selectedaccount);
		if($unconfirmedbalance == "") { $unconfirmedbalance = '0'; }
		
		//Get the balance of all accounts
		$totalbalance = round($gulden->getbalance(),2);
		
		
		//TODO: Create a good function for showing a new unused address
		//Temporary solution until a better way is found
		//Get the 30 latest transactions of this single account
		$numoftransactions = 30;
		$accounttransactions = $gulden->listtransactions($selectedaccount, $numoftransactions);
		
		//Get the addresses of this specific account
		$listreceivedbyaddress = $gulden->listreceivedbyaddress(0, false);
		if(count($accounttransactions)>0) {
			$transactionaddresslist = array();
			foreach ($listreceivedbyaddress as $tavalue) {
				if($tavalue['account']==$selectedaccount) {
					$transactionaddresslist[] = $tavalue['address'];
				}
			}
			
			//Find the differences between the current address list and the transaction list
			//If there are no differences, create a new address
			//If there are differences, use the address that is different
			$diffaddresses = array_values(array_diff($addresslist, $transactionaddresslist));
			if(count($diffaddresses)>0) {
				$latestaddress = $diffaddresses[0];
			} else {
				$gulden->getnewaddress($selectedaccount);
				$addresslist = $gulden->getaddressesbyaccount($selectedaccount);
				$diffaddresses = array_values(array_diff($addresslist, $transactionaddresslist));
				if(count($diffaddresses)>0) {
					$latestaddress = $diffaddresses[0];
				}
			}
		} else {
			$latestaddress = $addresslist[(count($addresslist)-1)];
		}
	
        // Use the user defined exchange to check the exchange rate for Novo
        $novoprovider = $NDASH['nlgrate'];
        $currentnlgprovider = $CONFIG['nlgprovider'];
        if ($currentnlgprovider == "") $currentnlgprovider = "0";
        // Market address
        $provider = $novoprovider[$currentnlgprovider]['market'];
        // Ticker data
        $data = '';
        if (array_key_exists('data', $novoprovider[$currentnlgprovider])) $data = $novoprovider[$currentnlgprovider]['data'];
        // Exchange rate in ticker
        $valuelink = $novoprovider[$currentnlgprovider]['link'];
        // Price symbol
        $pricesymbol = $novoprovider[$currentnlgprovider]['symbol'];
		$currencyrounding = $novoprovider[$currentnlgprovider]['rounding'];
	
        // Get json string from market api
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $provider);
        curl_setopt($ch, CURLOPT_HEADER, "accept: application/json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ticker = json_decode(curl_exec($ch), true);
        curl_close($ch);
    
        // Get exchange rate
        if ($data != '') $ticker = $ticker[$data];
		$valuelinkexpl = explode("->", $valuelink);
        $currentprice = $ticker[$valuelinkexpl[0]][$valuelinkexpl[1]];
        if ($currentprice == '') $currentprice = 0;
		$returnarray['selectedaccount'] = $selectedaccount;
		$returnarray['accountlist'] = $accountlist;
		$returnarray['address'] = $latestaddress;
  		$returnarray['balance'] = $confirmedbalance;
		$returnarray['totalbalance'] = $totalbalance;
		$returnarray['otherbalance'] = round($confirmedbalance * $currentprice, $currencyrounding);
		$returnarray['othertotalbalance'] = round($totalbalance * $currentprice, $currencyrounding);
		$returnarray['otherbalancesymbol'] = $pricesymbol;
		$returnarray['uncbalance'] = $unconfirmedbalance;
		$returnarray['disablewallet'] = "0";
		$returnarray['encryption'] = $encryptioncheck;
		$returnarray['exchange'] = $novoprovider[$currentnlgprovider]['exchange'];
		$returnarray['syncprogress'] = $gblockspercent;
		$returnarray['currentprice'] = $currentprice;
		$returnarray['pricesymbol'] = $pricesymbol;
		$returnarray['rounding'] = $currencyrounding;
	} else {
		$returnarray['selectedaccount'] = "";
		$returnarray['accountlist'] = "";
		$returnarray['address'] = "";
  		$returnarray['balance'] = "";
		$returnarray['totalbalance'] = "";
		$returnarray['uncbalance'] = "";
		$returnarray['disablewallet'] = "1";
		$returnarray['encryption'] = "";
		$returnarray['syncprogress'] = "";
	}
	$returnarray['errors'] = $gerrors;
	$returnarray['server']['cpu'] = $guldenCPU;
	$returnarray['server']['mem'] = $guldenMEM;
} else {
	$returnarray['selectedaccount'] = "";
	$returnarray['accountlist'] = "";
	$returnarray['address'] = "";
	$returnarray['accounttransactions'] = "";
	$returnarray['accounttransactionsdetails'] = "";
  	$returnarray['balance'] = "";
	$returnarray['totalbalance'] = "";
	$returnarray['uncbalance'] = "";
	$returnarray['encryption'] = "";
	$returnarray['syncprogress'] = "";
	$returnarray['currentprice'] = "";
	$returnarray['pricesymbol'] = "";
	$returnarray['rounding'] = "";
	
	$returnarray['errors'] = "";
	$returnarray['server']['cpu'] = "";
	$returnarray['server']['mem'] = "";
	
}

echo json_encode($returnarray);
}
?>
