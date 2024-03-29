<?php
/* System functions */

function GetProgCpuUsage($program)
{
    if(!$program) return -1;
    
    $c_pid = exec("ps aux | grep ".$program." | grep -v grep | grep -v su | awk {'print $3'}");
	//logger(5, "GuldenCpuUsage", $c_pid);
	
    return trim($c_pid);
}

function GetProgMemUsage($program)
{
    if(!$program) return -1;
    
    $c_pid = exec("ps aux | grep ".$program." | grep -v grep | grep -v su | awk {'print $4'}");
	//logger(5, "GuldenMemUsage", $c_pid);
	
    return trim($c_pid);
}

function GetProgUpTime($program) 
{
	if(!$program) return -1;
	$c_pid = exec("ps -eo pid,comm,etimes | grep $program | awk {'print $3'}");
	//logger(5, "GuldenUptime", $c_pid);
	
	return trim($c_pid);
}

function GetLinuxTemp() 
{
	$l_temp = exec("cat /sys/class/thermal/thermal_zone0/temp");
	$l_temp = round(trim($l_temp) / 1000, 0);
	//logger(5, "TempCheck", $l_temp);
	
	return $l_temp;
}

function GetSystemMemUsage() 
{
	exec("free", $free);
	$free=implode(' ', $free);
	preg_match_all("/(?<=\s)\d+/", $free, $match);
	list($total_mem,$used_mem,$free_mem,$shared_mem,$buffered_mem,$available_mem)=$match[0];
	
	$used_mem -= ($buffered_mem);
	$percent_used = (int)(($used_mem*100)/$total_mem);
	//logger(5, "MemCheck", $percent_used);
	
	return $percent_used;
}

function AddTrailingSlash($string)
{
	$string = rtrim($string, '/') . '/';
	return $string;
}

/**
 * Log function with log file rotation
 * and loglevel restrictions
 */
function logger($level, $event, $text = null) {
	//Levels for logging:
	//1: System errors
	//2: GuldenD errors
	//3: G-DASH errors
	//4: General warnings
	//5: General (system) stats
	
    $maxsize = 5242880; //Max filesize in bytes (e.q. 5MB)
    $dir = __DIR__."/../../log/";
    $filename = "gdash.log";
    $loglevel = 5;
	$maxlogs = 5;
	
	//Check if file exists and if the filesize exceeds the maxsize
	if(file_exists($dir.$filename) && filesize($dir.$filename) > $maxsize) {
        $nb = 1;
        $logfiles = scandir($dir);
        $oldestlog = "";
		
		//Find the last file name for renaming
        foreach ($logfiles as $file) {
            $tmpnb = substr($file, strlen($filename));
            if($nb < $tmpnb) {
                $nb = $tmpnb;
            }
			
			if($tmpnb != '' && $oldestlog == '') {
				$oldestlog = dir.$filename.$tmpnb;
			}
        }
        
		//Rename the current log file
        rename($dir.$filename, $dir.$filename.($nb + 1));
		
		//Remove the oldest log file if the number of log files is more than maxlogs
		if(count($logfiles) > $maxlogs) {
			unlink($oldestlog);
		}
    }
	
	//Check if the level of this error is less than the restriced error level
    if($level <= $loglevel && file_exists($dir.$filename)) { 
       $data = date('Y-m-d H:i:s').";LEVEL: ".$level.";";
       $data .= "EVENT: ".$event.";".$text.PHP_EOL;
       file_put_contents($dir.$filename, $data, FILE_APPEND);
    }
}

function getFilePermissions($file) {	
	//Array with results of the function
	$resultArray = array();	
	
	//Does the file exist?
	if(file_exists($file)) {
		
		//Yes, exists
		$resultArray['exists'] = TRUE;
	
		//Raw permissions
		$perms = fileperms($file);
		
		//The owner of the file
		$resultArray['owner'] = posix_getpwuid(fileowner($file));
	
		//Type of file
		switch ($perms & 0xF000) {
		    case 0xC000: // socket
		        $type = 's';
		        break;
		    case 0xA000: // symbolic link
		        $type = 'l';
		        break;
		    case 0x8000: // regular
		        $type = 'r';
		        break;
		    case 0x6000: // block special
		        $type = 'b';
		        break;
		    case 0x4000: // directory
		        $type = 'd';
		        break;
		    case 0x2000: // character special
		        $type = 'c';
		        break;
		    case 0x1000: // FIFO pipe
		        $type = 'p';
		        break;
		    default: // unknown
		        $type = 'u';
		}
		
		$resultArray['type'] = $type;
		
		// Owner
		$info = (($perms & 0x0100) ? 'r' : '-');
		$info .= (($perms & 0x0080) ? 'w' : '-');
		$info .= (($perms & 0x0040) ?
		            (($perms & 0x0800) ? 's' : 'x' ) :
		            (($perms & 0x0800) ? 'S' : '-'));
		
		// Group
		$info .= (($perms & 0x0020) ? 'r' : '-');
		$info .= (($perms & 0x0010) ? 'w' : '-');
		$info .= (($perms & 0x0008) ?
		            (($perms & 0x0400) ? 's' : 'x' ) :
		            (($perms & 0x0400) ? 'S' : '-'));
		
		// World
		$info .= (($perms & 0x0004) ? 'r' : '-');
		$info .= (($perms & 0x0002) ? 'w' : '-');
		$info .= (($perms & 0x0001) ?
		            (($perms & 0x0200) ? 't' : 'x' ) :
		            (($perms & 0x0200) ? 'T' : '-'));
		
		$resultArray['permissions'] = $info;
		
		//Writable, readable and executable
		$resultArray['writable'] = is_writable($file);
		$resultArray['readable'] = is_readable($file);
		$resultArray['executable'] = is_executable($file);
	} else {
		//File does not exist
		$resultArray['exists'] = FALSE;
	}
	
	return $resultArray;
}

//Search through a multidimensional array
function array_search_multidimensional($array, $field, $value)
{
   foreach($array as $key => $item)
   {
      if ( $item[$field] === $value )
         return $key;
   }
   return false;
}

//Return an array that contains a specific element value
function selectElementWithValue($array, $field, $value){
	$newArray = array();
	
	foreach($array as $subKey => $subArray){
		if($subArray[$field] == $value){
			$newArray[] = $array[$subKey];
		}
	}
	return $newArray;
}

//Define an usort function to sort by a subkey
function mdarraysorter($key) {
    return function ($a, $b) use ($key) {
        return strnatcmp($a[$key], $b[$key]);
    };
}


/* Check if ports is open from the outside
function fullNodeCheck() {
	$checks = array();
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 0);
	curl_setopt($ch, CURLOPT_URL, "https://guldenticker.com/api/portcheck.json");
	$openportcheck = curl_exec($ch);
	

	if ($openportcheck && $openportreturn = json_decode($openportcheck, true)) {
		$checks[] = $openportreturn['success']?$openportreturn['local_ip'] . ' is listening':$openportreturn['local_ip'] . ' is not reachable';
		$info = curl_getinfo($ch);		
		curl_close($ch);
		
		if(filter_var($info['local_ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) { // check if ipv6 is used, if true do ipv4 check
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 0);
			curl_setopt($ch, CURLOPT_URL, "https://guldenticker.com/api/portcheck.json");
			curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
			$openportcheck = curl_exec($ch);
			if ($openportcheck && $openportreturn = json_decode($openportcheck, true)) {
				$checks[] = $openportreturn['success']?$openportreturn['local_ip'] . ' is listening':$openportreturn['local_ip'] . ' is not reachable';
			}
			curl_close($ch);
		}
	} else {
		$checks[] = 'Check failed';
	}
	
	return $checks;
} */

//Recursive delete dirs
function rrmdir($src) {
	$returnval = false;
	if($src != "") {
	    $dir = opendir($src);
	    while(false !== ( $file = readdir($dir)) ) {
	        if (( $file != '.' ) && ( $file != '..' )) {
	            $full = $src . '/' . $file;
	            if ( is_dir($full) ) {
	                rrmdir($full);
	            }
	            else {
	                unlink($full);
	            }
	        }
	    }
	    closedir($dir);
		
		if(rmdir($src)) {
			$returnval = true;
		}
	}
	return $returnval;
}

/* Gulden specific functions */

function checkOpenPort($address, $port) {
	$connection = @fsockopen($address, $port);
	if (is_resource($connection)) { return TRUE; } else { return FALSE;}
}

function GetTimeAnno($timestamp) 
{
    $how_long_ago = '';
    $seconds = $timestamp; 
    $minutes = (int)($seconds / 60);
    $hours = (int)($minutes / 60);
    $days = (int)($hours / 24);
	$daysplushours = (int)($hours - ($days*24));
	
    if ($days >= 1) {
      $how_long_ago = $days . ' day' . ($days != 1 ? 's' : '');
	  $how_long_ago .= " and " . $daysplushours . ' hour' . ($daysplushours != 1 ? 's' : '');
    } else if ($hours >= 1) {
      $how_long_ago = $hours . ' hour' . ($hours != 1 ? 's' : '');
    } else if ($minutes >= 1) {
      $how_long_ago = $minutes . ' minute' . ($minutes != 1 ? 's' : '');
    } else {
      $how_long_ago = $seconds . ' second' . ($seconds != 1 ? 's' : '');
    }
    return $how_long_ago;
}

function readGuldenConf($file) {
	$contentarray = array();
	$handle = fopen($file, "r");
	if ($handle) {
	    while (($line = fgets($handle)) !== false) {
	        $splittedline = explode("=", $line);
			$var = trim($splittedline[0]);
			$val = trim($splittedline[1]);
			$contentarray[$var] = $val;
	    }
	    fclose($handle);
	} else {
	    $contentarray['error'] = "error";
	}
	
	return $contentarray;
}

function GEOonline($ip) {
	$result = array();
	
	$ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));
	if($ip_data && $ip_data->geoplugin_countryName != null){
	    $result['country'] = $ip_data->geoplugin_countryName;
	} elseif($ip_data->geoplugin_countryName == null) {
		$result['country'] = "unknown";
	}
	
	if($ip_data && $ip_data->geoplugin_city != null){
	    $result['city'] = $ip_data->geoplugin_city;
	} elseif($ip_data->geoplugin_city == null) {
		$result['city'] = "unknown";
	}
	
	return $result;
}

//TODO: This function can be removed at the 1.0 release
function isPasswordUpdated() {
	include('config/config.php');
	if($CONFIG['bcrypt']=="1") {
		return TRUE;
	} else {
		return FALSE;
	}
}

function LoginCheck($guldenConf, $out=FALSE, $disabled="0")
{
	include('config/config.php');
	$returnvalue = FALSE;
	
	//If the dashboard hasn't been configured yet, go directly to the settings page
	if($CONFIG['configured']!="1") {
		$disabled="1";
	}
	
	if(isset($_POST['login'])) {
		$usernameposted = strtolower($_POST['rpcuser']);
		$passwordposted = $_POST['rpcpassword'];
		
		//TODO: Remove the MD5 function and update check at 1.0 release
		if(isPasswordUpdated()==FALSE) {
			$passwordposted = md5(sha1($passwordposted));
		}
		
		//TODO: Remove this part at the 1.0 release
		//This method will fetch the username and password from the config.php file
		if($usernameposted==strtolower($CONFIG['gdashuser']) && $passwordposted==$CONFIG['gdashpassword'] && $CONFIG['bcrypt']!="1") {
			$_SESSION['N-DASH-loggedin']=TRUE;
			$returnvalue = TRUE;
		} elseif($usernameposted==strtolower($CONFIG['gdashuser']) && password_verify($passwordposted, $CONFIG['gdashpassword']) && $CONFIG['bcrypt']=="1") {
			$_SESSION['N-DASH-loggedin']=TRUE;
			$returnvalue = TRUE;			
		} else {
			echo "<div class='alert alert-danger'>
				   <strong>Error:</strong><br>
				   This combination of username/password is incorrect.
				  </div>";
		}
	} elseif($out==TRUE) {
		$returnvalue = FALSE;
		$_SESSION['N-DASH-loggedin']=FALSE;
		session_destroy();
	} elseif($disabled=="1") {
		$returnvalue = TRUE;
		$_SESSION['N-DASH-loggedin'] = TRUE;
	} elseif(isset($_SESSION['N-DASH-loggedin']) && $_SESSION['N-DASH-loggedin'] == TRUE) {
		$returnvalue = TRUE;
	}
	return $returnvalue;
}

function checkRequiredPackages() {
	//Are all required packages installed?
	$installedPackages = array();
	$installedPackages = explode(PHP_EOL, shell_exec("dpkg --get-selections | grep -v deinstall | awk {'print $1'}"));
	$requiredPackages = array("apache2", "php5", "php5-curl", "curl", "php5-json", "libapache2-mod-php5");
	$checkInstalled = "";
	
	if(in_array("php5", $installedPackages)) {
		
		foreach ($requiredPackages as $packvalue) {
			if(in_array($packvalue, $installedPackages)) {
				$checkInstalled .= "<font color='green'>$packvalue - Passed</font><br>";
			} else {
				$checkInstalled .= "<font color='red'>$packvalue - Not found!</font><br>";
			}
		}
		
	} elseif(in_array("php7.0", $installedPackages)) {
		
		$requiredPackages = array("apache2", "php7.0", "php7.0-curl", "curl", "php7.0-json", "libapache2-mod-php7.0");
		foreach ($requiredPackages as $packvalue) {
			if(in_array($packvalue, $installedPackages)) {
				$checkInstalled .= "<font color='green'>$packvalue - Passed</font><br>";
			} else {
				$checkInstalled .= "<font color='red'>$packvalue - Not found!</font><br>";
			}
		}
	} else {
		$checkInstalled = "Check PHP packages!";
	}
	
	return $checkInstalled;
}

function getGuldenServices() {
	//Check the Gulden listening services
	$guldenListeningServices = explode(PHP_EOL, shell_exec("netstat -antl | grep LISTEN | grep -v tcp6 | awk {'print $4'}"));
	$runningService = "";
	
	foreach ($guldenListeningServices as $service) {
		$runningService .= $service."<br>";
	}
	
	return $runningService;
}

//Function to fetch the txdetails live from the wallet
function getLiveTransactionDetails($accounttransactions, $numoftransactionstoshow, $addresslist, $gulden, $selectedaccount) {
	$returntx = array();
	$uniquetxids = array();
	
	//Create an array of all TXID's
	foreach ($accounttransactions as $txvalue) {
		$uniquetxids[] = $txvalue['txid'];
	}
	
	//Make the array list unique
	$uniquetxids = array_unique($uniquetxids);
	
	//Reverse the order of the array
	$uniquetxids = array_reverse($uniquetxids);
	
	//List a maximum number of TX
	$uniquetxids = array_slice($uniquetxids, 0, $numoftransactionstoshow, true);
	
	//reset the keys
	$uniquetxids = array_values($uniquetxids);

    //For each transaction ID
	foreach ($uniquetxids as $transactiontxid) {
		//Get the transaction details from the GuldenD for this transaction
		$txrawdetails = $gulden->gettransaction($transactiontxid);
        $currenttx['txconfirmations'] = $txrawdetails['confirmations'];
   		$transactiondate = date('d/m/Y H:i', $txrawdetails['time']);
        $txdetails = $txrawdetails['details'];
        for ($x = 0; $x <count($txdetails); $x++) {
            if (strcmp($txdetails[$x]['account'], $selectedaccount) == 0) {
                $currenttx['transactiondate'] = $transactiondate;
                $currenttx['transactionamount'] = $txdetails[$x]['amount'];
                if ($x == 0) $currenttx['transactionid'] = $txrawdetails['txid'];
                else $currenttx['transactionid'] = "";
                $returntx[] = $currenttx;
            }
        }
	}

	return $returntx;
}

//Loop through the witness earnings and return the total earnings
function getWitnessTransactions($witnesstransactions) {
	//Get the witness earnings from the transactions of this witness account
	//This is a very ugly temp solution until a better fix is ready. Working on it...
	//TODO: Ugly, but hey, it works
	$tempwitnesstransactions = array();
	$remembertxid = array();
	$lowestfound = "";
	foreach ($witnesstransactions as $witnesstx) {
		//Get the txid from the first item encountered
		$txid = $witnesstx['txid'];
		
		//Don't check the same txid multiple times
		if(!in_array($txid, $remembertxid)) {
		
			//Find others with the same txid
			$listwithtxid = selectElementWithValue($witnesstransactions, "txid", $txid);
			
			//Find if any of the elements with this txid has "orphan" blocks
			$listwithtxidorphans = selectElementWithValue($listwithtxid, "category", "orphan");
			
			//If this is an orphan block, don't take it along
			if(count($listwithtxidorphans)==0) {
			
				//Find the one with the lowest number, but positive number if there are 3 transactions involved
				if(count($listwithtxid) == 3) {
					$lowesttxamount = 99999999;
					foreach ($listwithtxid as $txkey => $listtx) {
						
						if($listtx['amount'] > 0 && $listtx['amount'] < $lowesttxamount) {
							$lowesttxamount = $listtx['amount'];
							$lowestfound = $listwithtxid[$txkey];
						}
					}
					$tempwitnesstransactions[] = $lowestfound;
				} elseif(count($listwithtxid) == 2) {
					//$witnessdetailsarray['originaltxtwo'][] = $listwithtxid;
					//Not negative, not the same amount as initial funding
					if($listtx['amount'] > 0 && $listtx['amount'] != $witnessdata['amount']) {
						$tempwitnesstransactions[] = $listwithtxid[0];
					}
				} elseif(count($listwithtxid) == 1) {
					//$witnessdetailsarray['originaltxsingle'][] = $listwithtxid;
					if($listwithtxid[0]['vout']==2) {
						$tempwitnesstransactions[] = $listwithtxid[0];
					}
				}
				
			}
		}
		
		//Build a list of txids
		$remembertxid[] = $txid;
	}

	return $tempwitnesstransactions;
}

// Check if there is an update for Novo
function checkNovoVersion ($gulden) {
    // Return array_values
    $versions = array();

    // Get installed version
	$checkversioninfo = $gulden->getinfo();
	$currentnovoversion = $checkversioninfo['version'];

    // Get latest Novo version
    $json = json_decode(file_get_contents('https://api.github.com/repos/novocurrency/novocurrency-core/releases/latest', false,
        stream_context_create(['http' => ['header' => "User-Agent: Vestibulum\r\n"]])), true);
    $versions['latest'] = $json['tag_name'];
    
    // Format current version
    $cvarray = str_split($currentnovoversion);
    $cv = "v" . $cvarray[0] . ".";
    if ($cvarray[1] == 0) $cv = $cv . $cvarray[2];
    else $cv = $cv . $cvarray[1] . $cvarray [2];
    $cv = $cv . ".";
    if ($cvarray[5] == 0) $cv = $cv . $cvarray[6];
    else $cv = $cv . $cvarray[5] . $cvarray [6];
	$versions['current'] = $cv;
	
	// Return latest and current version
	return $versions;
}

// Check if there is an update for N-DASH
function checkDashVersion ($currentversion) {
    // Return array_values
    $versions = array();

    // Get latest dash version
    $json = json_decode(file_get_contents('https://api.github.com/repos/juggernt/N-DASH/releases/latest', false,
        stream_context_create(['http' => ['header' => "User-Agent: Vestibulum\r\n"]])), true);
    $versions['latest'] = $json['tag_name'];
    
    // Format current version
    $versions['current'] = "v" . $currentversion;
	
	// Return latest and current version
	return $versions;
}
?>

