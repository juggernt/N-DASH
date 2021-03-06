<?php
//Only allow this script to run from PHP CLI, not from HTTP
if (php_sapi_name() == "cli") {
	require_once(__DIR__.'/../../config/config.php');
	require_once(__DIR__.'/../../lib/settings/settings.php');
	require_once(__DIR__.'/../../lib/functions/functions.php');
	require_once(__DIR__.'/../../lib/EasyGulden/easygulden.php');
	
	//Connect to Gulden
	$gulden = new Gulden($CONFIG['rpcuser'],$CONFIG['rpcpass'],$CONFIG['rpchost'],$CONFIG['rpcport']);

	//Check if Novo-daemon is running
	if($CONFIG['pushbulletgulden']['active']=="1") {
		
		//Get the info (last message and current message)
		$lastmessage = $CONFIG['pushbulletgulden']['lastmes'];
		$currentmessage = "";
		if($gulden->getinfo()=="") {
			$currentmessage = "Novo-daemon is not running!";
		} else {
			$currentmessage = "Novo-daemon is up and running!";
		}
		
		//Check the last message that was pushed to prevent multiple pushes of the same message
		if($lastmessage!=$currentmessage) {
			
			//The message is different, send a push notification
            $sendpush = shell_exec("curl --header 'Access-Token: ".$CONFIG['pushbullet']."' --header 'Content-Type: application/json' --data-binary '{\"type\": \"note\", \"title\": \"Novo-daemon\", \"body\": \"$currentmessage\"}' --request POST https://api.pushbullet.com/v2/pushes");
            
            //Set the current message as the last message in the config file
			$CONFIG['pushbulletgulden']['lastmes'] = $currentmessage;
			
			//Update the config file
			file_put_contents(__DIR__.'/../../config/config.php', '<?php $CONFIG = '.var_export($CONFIG, true).'; ?>');
		}
	}

	// Check if there is a new version of G-DASH on GitHub
	if($CONFIG['pushbulletgdash']['active']=="1") {
		
		// Get the info (last message and current message)
		$lastmessage = $CONFIG['pushbulletgdash']['lastmes'];
		$currentmessage = "N-DASH is up-to-date";
		$versions = checkDashVersion($NDASH['currentversion']);
		if ($versions['latest'] != "" && $versions['current'] != $versions['latest']) 
			$currentmessage = "A new version of N-DASH is available (".$versions['latest'].")!";
				
		// Check the last message that was pushed to prevent multiple pushes of the same message
		if ($lastmessage != $currentmessage) {
			
            // The message is different, send a push notification
            $sendpush = shell_exec("curl --header 'Access-Token: ".$CONFIG['pushbullet']."' --header 'Content-Type: application/json' --data-binary '{\"type\": \"note\", \"title\": \"N-DASH version\", \"body\": \"$currentmessage\"}' --request POST https://api.pushbullet.com/v2/pushes");
                
            // Set the current message as the last message in the config file
            $CONFIG['pushbulletgdash']['lastmes'] = $currentmessage;
                
            // Update the config file
            file_put_contents(__DIR__.'/../../config/config.php', '<?php $CONFIG = '.var_export($CONFIG, true).'; ?>');
		}
	}
	
	// Check if there is a newer version of Novo on GitHub
	if ($gulden->getinfo() != "" && $CONFIG['pushbulletguldenupdate']['active']=="1") {
		
		// Check latest version on GitHub
		$lastmessage = $CONFIG['pushbulletguldenupdate']['lastmes'];
		$currentmessage = "Novo is up-to-date!";
		$versions = checkNovoVersion($gulden);
		if ($versions['latest'] != "" && $versions['current'] != $versions['latest']) 
			$currentmessage = "A new version of Novo is available (".$versions['latest'].")!";
				
		// Check the last message that was pushed to prevent multiple pushes of the same message
		if ($lastmessage != $currentmessage) {
			
            // The message is different, send a push notification
            $sendpush = shell_exec("curl --header 'Access-Token: ".$CONFIG['pushbullet']."' --header 'Content-Type: application/json' --data-binary '{\"type\": \"note\", \"title\": \"Novo version\", \"body\": \"$currentmessage\"}' --request POST https://api.pushbullet.com/v2/pushes");
                
            // Set the current message as the last message in the config file
            $CONFIG['pushbulletguldenupdate']['lastmes'] = $currentmessage;
                
            // Update the config file
            file_put_contents(__DIR__.'/../../config/config.php', '<?php $CONFIG = '.var_export($CONFIG, true).'; ?>');
		}
	}

/* @1.0
	//Notification if there is a new incoming transaction
	if($CONFIG['pushbullettx']['active']=="1") {
		
		//Create a list of addresses belonging to this wallet
		$addresslistrpc = $gulden->listreceivedbyaddress();
		$addresslist = array_column($addresslistrpc, "address");
		
		//Get the latest transaction for all accounts
		$accounttoshowtx = "*";
		$numoftransactionstoshow = 1;
		$accounttransactions = $gulden->listtransactions($accounttoshowtx, $numoftransactionstoshow);
		
		//List all non-deleted accounts
		$accountlistrpc = $gulden->listaccounts("*", "Normal");
		
		//Get the account name of the last transaction
		$accountname = $accounttransactions[0]['accountlabel'];
		
		//Only get this account from the accountlist array
		$accountlist_thisaccount = selectElementWithValue($accountlistrpc, "label", $accountname);
		
		//Get the type of this account
		$accounttype = $accountlist_thisaccount[0]['type'];
		
		//Check if this is not a witness account
		if($accounttype != "Witness" && $accounttype != "Witness-only witness") 
		{
		
			//Get the raw transaction details
			$transactiondetails = getTransactionDetails($accounttransactions, $numoftransactionstoshow, $addresslist);
			
			//Get only the first item from the function as there is only one to possibly push
			$transactiondetailsitem = $transactiondetails[0];
			
			//Get the amount of Gulden sent/received
			$transactionamount = $transactiondetailsitem['transactionamount'];
			
			//Get the senders address
			$txfromaddress = $transactiondetailsitem['txfromaddress'];
			
			//Get the date and time of the transaction
			$transactiondate = $transactiondetailsitem['transactiondate'];
			
			//Only push a message if it is an incoming transaction
			if($transactionamount > 0) {
				//Get the info (last message and current message)
				$lastmessage = $CONFIG['pushbullettx']['lastmes'];
				$currentmessage = $transactiondate.": $transactionamount Gulden received from $txfromaddress";
				
				//Check the last message that was pushed to prevent multiple pushes of the same message
				if($lastmessage!=$currentmessage) {
					
					//The message is different, send a push notification
					$sendpush = shell_exec("curl --header 'Authorization: Bearer ".$CONFIG['pushbullet']."' -X POST https://api.pushbullet.com/v2/pushes --header 'Content-Type: application/json' --data-binary '{\"type\": \"note\", \"title\": \"Gulden Transaction\", \"body\": \"".$currentmessage."\"}'");
					
					//Set the current message as the last message in the config file
					$CONFIG['pushbullettx']['lastmes'] = $currentmessage;
					
					//Update the config file
					file_put_contents(__DIR__.'/../../config/config.php', '<?php $CONFIG = '.var_export($CONFIG, true).'; ?>');
				}
			}
		}
	}

	//Notification if there is a new incoming witness transaction
	if($CONFIG['pushbulletwitness']['active']=="1") {
		
		//Get witness activity
		$mywitnessaccountsnetwork = $gulden->getwitnessinfo("tip", true, true);
		
		//Get all witness accounts
		$mywitnessaccountsnetwork = $mywitnessaccountsnetwork[0]['witness_address_list'];
		
		//Get the current block height
		$ginfo = $gulden->getinfo();
		$currentblock = $ginfo['blocks'];
		
		//Loop through the witness accounts and find the most recent action	
		$lastwitnessactionblock = 0;
		foreach ($mywitnessaccountsnetwork as $witnessdata) {
			if($witnessdata['last_active_block'] > $lastwitnessactionblock) {
				$witnessdetailsname = $witnessdata['ismine_accountname'];
				$lastwitnessactionblock = $witnessdata['last_active_block'];
				$lastwitnessactiondate = date("d/m/Y H:i:s", time() - (($currentblock - $lastwitnessactionblock) / (576 / (24 * 60 * 60))));
			}
		}
		
		//Get the last block that was active in the config
		$lastblock = $CONFIG['pushbulletwitness']['lastblock'];
		
		//Get the info (last message and current message)
		$lastmessage = $CONFIG['pushbulletwitness']['lastmes'];
		$currentmessage = $lastwitnessactiondate.": New holding action for $witnessdetailsname";
		
		//Check the last message that was pushed to prevent multiple pushes of the same message
		if($lastmessage!=$currentmessage && $lastwitnessactionblock != $lastblock && $witnessdetailsname != "") {
			
			//The message is different, send a push notification
			$sendpush = shell_exec("curl --header 'Authorization: Bearer ".$CONFIG['pushbullet']."' -X POST https://api.pushbullet.com/v2/pushes --header 'Content-Type: application/json' --data-binary '{\"type\": \"note\", \"title\": \"Novo Holding Action\", \"body\": \"".$currentmessage."\"}'");
			
			//Set the current message as the last message in the config file
			$CONFIG['pushbulletwitness']['lastmes'] = $currentmessage;
			$CONFIG['pushbulletwitness']['lastblock'] = $lastwitnessactionblock;
			
			//Update the config file
			file_put_contents(__DIR__.'/../../config/config.php', '<?php $CONFIG = '.var_export($CONFIG, true).'; ?>');
		}
	}
*/
}
?>
