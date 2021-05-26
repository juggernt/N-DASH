<?php
session_start();

//In case the server is very busy, lower the max execution time to 60 seconds
set_time_limit(10);

if(isset($_SESSION['N-DASH-loggedin']) && $_SESSION['N-DASH-loggedin']==TRUE) {
include('../lib/functions/functions.php');
include('../config/config.php');
require_once('../lib/EasyGulden/easygulden.php');
$gulden = new Gulden($CONFIG['rpcuser'],$CONFIG['rpcpass'],$CONFIG['rpchost'],$CONFIG['rpcport']);

$guldenD = "Novo-daemon";
$guldenCPU = GetProgCpuUsage($guldenD);
$guldenMEM = GetProgMemUsage($guldenD);
$linuxTemp = GetLinuxTemp();
//GetSystemMemUsage();
$returnarray = array();

$localdate = new DateTime();
$localtz = $localdate->getTimezone();

if($guldenCPU > 0 && $guldenMEM > 0) {
	$returnarray['server']['cpu'] = $guldenCPU;
	$returnarray['server']['mem'] = $guldenMEM;
	$returnarray['server']['temperature'] = $linuxTemp;
	
	$guldengetinfo = $gulden->getinfo();
	$guldenprimaryresponsecode = $gulden->response['error']['code'];
	$guldenprimaryresponsemessage = $gulden->response['error']['message'];
	
	if($guldenprimaryresponsecode == "-28") {
		$returnarray['gulden']['version'] = '';
		$returnarray['gulden']['sync'] = '';
		$returnarray['gulden']['uptime'] = '';
		$returnarray['gulden']['protocolversion'] = '';
		$returnarray['node']['connections'] = '';
		$returnarray['node']['inbound'] = '';
		$returnarray['witness']['phase'] = '';
		$returnarray['table'] = "<tr><td colspan='4'>Novo-daemon Upgrading</td></tr>";
		$returnarray['errors'] = $guldenprimaryresponsemessage;
		
		//Write this status update to the log file
		//logger(4, "Novo-daemon", "Upgrading block index");
		
	} elseif($guldengetinfo=="") {
		$returnarray['gulden']['version'] = '';
		$returnarray['gulden']['sync'] = '';
		$returnarray['gulden']['uptime'] = '';
		$returnarray['gulden']['protocolversion'] = '';
		$returnarray['node']['connections'] = '';
		$returnarray['node']['inbound'] = '';
		$returnarray['witness']['phase'] = '';
		$returnarray['table'] = "<tr><td colspan='4'>Novo-daemon error</td></tr>";
		$returnarray['errors'] = "Error connecting to server";
	} else {
	
		//Novo-daemon info
		$ginfo = $guldengetinfo;
		$gversion = $ginfo['version'];
		$gblocks = $ginfo['blocks'];
		$gconnections = $ginfo['connections'];
		$gprotocolversion = $ginfo['protocolversion'];
		$gtimeoffset = $ginfo['timeoffset'];
		$gdifficulty = round($ginfo['difficulty'],3);
		$gerrors = $ginfo['errors'];
		
		//Get total headers via Novo-daemon getpeerinfo
		$peerinfo = $gulden->getpeerinfo();
		
		//Walk through all the peers to get the most updated one and 
		//grab the number of headers from that instance
		$gallblocks = 0;
		foreach ($peerinfo as $peervalue) {
			if($peervalue['synced_headers'] > $gallblocks) {
				$gallblocks = $peervalue['synced_headers'];
			}
		}
		
		//Check if headers are synced and the current number of blocks
		$bcinfo = $gulden->getblockchaininfo();
		$gsyncedblocks = $bcinfo['blocks'];
		$gsyncedheaders = $bcinfo['headers'];
		
		//Check if headers are synced
		if(($gsyncedblocks == 0 && $gsyncedheaders > 0) || $gallblocks == 0 || $gallblocks == -1) {
			$gerrors = $gerrors."<br>Syncing headers. Please wait";
		}
		
		//Calculate the percentage of synced blocks if all blocks > 0
		if($gallblocks>0) {
			$gblockspercent = floor(($gblocks/$gallblocks)*100)."%";
		} else {
			$gblockspercent = "Syncing";
		}
		
		
		//Get the uptime of the Novo-daemon instance
		$guptime = GetTimeAnno(GetProgUpTime("Novo-daemon"));
		
		//Check if Gulden is running a rescan
		$grescancheck = $gulden->getrescanprogress();
		if($grescancheck!=false) {
	  		$gerrors = $gerrors."<br>Running rescan (".$grescancheck."%). Please wait";
	  	}
		
		//Node info
		$gpeerinfo = $gulden->getpeerinfo();
		$ginboundconnections = 0;
		foreach($gpeerinfo as $innerArray) {
		  if($innerArray['inbound']=="1") { $ginboundconnections++; }
		}
		
		//Get information on the network regarding witnessing
		$witnessNetwork = $gulden->getwitnessinfo();
		$currentPhase = $witnessNetwork[0]['pow2_phase'];
		
		//Block info
		$tablerows = "";
		$rawlist = array();
		for ($i=$gallblocks; $i > $gallblocks-10 ; $i--) {
			$blockinfo = $gulden->getblock($gulden->getblockhash($i));
			if ($blockinfo == "") break;
			$age = GetTimeAnno(time() - $blockinfo['time']);
			$transactions = count($blockinfo['tx']);
			$difficulty = round($blockinfo['difficulty'],3);
			$txlist = $blockinfo['tx'];
			$amount = 0;
            $holdingvalue = 0;
			foreach ($txlist as $txid) {
                $rawtx = $gulden->getrawtransaction($txid);
                $txobj = $gulden->decoderawtransaction($rawtx);
                $errorcode = $gulden->response['error']['code'];
                if ($errorcode == "-22") {
                    break;
                }
                $rawresponse = $gulden->raw_response;
                $realtx = json_decode($rawresponse, true);
                $txvin = $realtx['result']['vin'];
                $txvout = $realtx['result']['vout'];
                $numvout = count($txvout);
                $pow2coinbase = false;
                $prevouttype = '';
                foreach ($txvin as $txval) {
                    if (array_key_exists('pow2_coinbase', $txval)) $pow2coinbase = true; 
                    if (array_key_exists('prevout_type', $txval)) $prevouttype = $txval['prevout_type']; 
                }
                $pow2witness = false;
                $witnessvalue = 0;
                foreach ($txvout as $txval) {
                    if ($numvout == 1) {
                        if (!array_key_exists('PoW²-witness', $txval)) $amount += floatval($txval['value']);
                        break;
                    }
                    elseif (array_key_exists('PoW²-witness', $txval)) {
                        if ($txval['n'] == 0) {
                            $pow2witness = true;
                            $witnessvalue = $txval['value'];
                        }
                        else $amount += floatval($txval['value']);
                        continue;
                    }
                    if ($transactions > 2) {
                        if (!$pow2witness && array_key_exists('standard-key-hash', $txval)) $amount += floatval($txval['value']);
                        if ($witnessvalue > 0 && !$pow2coinbase && ($prevouttype == 'index' || $prevouttype == 'hash')) {
                            $amount += $witnessvalue;
                            $witnessvalue = 0;
                        }
                    }
                    if ($pow2witness && $txval['n'] != 0) {
                        if (array_key_exists('standard-key-hash', $txval)) {
                            $amount += floatval($txval['value']);
                            if ($pow2coinbase) $holdingvalue += floatval($txval['value']);
                        }
                    }
                }
            }
            if ($amount == 0) $valueout = "N/A";
            else {
                if ($transactions > 1 && $holdingvalue == 0) $amount += 0.10;
                $valueout = number_format(round($amount, 2), 2, '.', ',');
            }
            $tablerows .= "
			<tr>
	          <td><a href='https://dactual.com/novo/block.php?height=$i' target='_blank'>$i</a></td>
	          <td>$age</td>
	          <td>$transactions</td>
	          <td>$difficulty</td>
	          <td>$valueout</td>
            </tr>
	        ";
		}
		
		//Get witness activity
		$mywitnessaccountsnetwork = $gulden->getwitnessinfo("tip", true, true);
		
		//Get all witness accounts
		$mywitnessaccountsnetwork = $mywitnessaccountsnetwork[0]['witness_address_list'];
		
		//Loop through the witness accounts and find the most recent action	
		$lastwitnessactionblock = 0;
		foreach ($mywitnessaccountsnetwork as $witnessdata) {
			if($witnessdata['last_active_block'] > $lastwitnessactionblock) {
				$witnessdetailsname = $witnessdata['ismine_accountname'];
				$lastwitnessactionblock = $witnessdata['last_active_block'];
			}
		}
		
		// Get the exact time of the last witness action by block date/time
		$lastwitnessactiondate = "Never";
		if($lastwitnessactionblock > 0) {
			$lastactive_blockhash = $gulden->getblockhash($lastwitnessactionblock);
			$lastactive_getblock = $gulden->getblock($lastactive_blockhash);
			$lastactive_blocktime = $lastactive_getblock['time'];
			$lastactive_dt = new DateTime("@$lastactive_blocktime", new DateTimeZone('GMT'));
			$lastactive_dt->setTimezone(new DateTimeZone($localtz->getName()));
			$lastwitnessactiondate = $lastactive_dt->format('d/m/Y H:i:s');
		}
		
		//Data array
		$returnarray['gulden']['version'] = $gversion;
		$returnarray['gulden']['sync'] = $gblockspercent;
		$returnarray['gulden']['uptime'] = $guptime;
		$returnarray['gulden']['protocolversion'] = $gprotocolversion;
		$returnarray['gulden']['timeoffset'] = $gtimeoffset;
		$returnarray['gulden']['difficulty'] = $gdifficulty;
		$returnarray['gulden']['blocks'] = $gblocks;
		$returnarray['gulden']['allblocks'] = $gallblocks;
		
		$returnarray['node']['connections'] = $gconnections;
		$returnarray['node']['inbound'] = $ginboundconnections;
		
		$returnarray['witness']['phase'] = $currentPhase;
		$returnarray['witness']['lastactive'] = $lastwitnessactiondate;
		
		$returnarray['table'] = $tablerows;
		
	  	$returnarray['errors'] = $gerrors;
	}
	
} else {
	$tablerows = "<tr><td colspan='4'>Novo-daemon is not running</td></tr>";
	
	$returnarray['gulden']['version'] = '';
	$returnarray['gulden']['sync'] = '';
	$returnarray['gulden']['uptime'] = '';
	$returnarray['gulden']['protocolversion'] = '';
	$returnarray['node']['connections'] = '';
	$returnarray['node']['inbound'] = '';
	$returnarray['witness']['phase'] = '';
	$returnarray['server']['cpu'] = '';
	$returnarray['server']['mem'] = '';
	$returnarray['server']['temperature'] = '';
	$returnarray['table'] = $tablerows;
	$returnarray['errors'] = '';
}

echo json_encode($returnarray);
}
session_write_close();
?>
