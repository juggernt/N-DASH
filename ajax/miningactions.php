<?php
session_start();
if($_SESSION['N-DASH-loggedin']==TRUE) {
include('../lib/functions/functions.php');
include('../config/config.php');
require_once('../lib/EasyGulden/easygulden.php');
$gulden = new Gulden($CONFIG['rpcuser'],$CONFIG['rpcpass'],$CONFIG['rpchost'],$CONFIG['rpcport']);

$guldenD = "Novo-daemon";
$guldenCPU = GetProgCpuUsage($guldenD);
$guldenMEM = GetProgMemUsage($guldenD);

$returnarray = array();

if($guldenCPU > 0 && $guldenMEM > 0) {
if(isset($_GET['action'])) {
	
	//Create a new mining account
	if($_GET['action'] == "createaccount") {
		$addaccountpassword = $_POST['addpassword'];
		//Check the passphrase and unlock the wallet for 10 seconds if password is not empty
		$guldenresponse = "0";
		if($addaccountpassword != "") {
			$gulden->walletpassphrase($addaccountpassword, 10);
			$guldenresponse = $gulden->response['error']['code'];
			$guldenresponsemessage = $gulden->response['error']['message'];
			
			if($guldenresponse != "-14") {
				if(strpos($_POST['accountname'], "*") === false) {
					$accountnamechars = $_POST['accountname'];
					$accountnamechars = str_replace(".", "_",$accountnamechars);
					if ($accountnamechars == "") {
                        $returnarray['code'] = "0";
                        $returnarray['message'] = "No wallet name supplied.";
                    }
					else {
                        $createaccount = $gulden->createminingaccount($accountnamechars);
                        if($createaccount == false) {
                            $returnarray['code'] = $gulden->response['error']['code'];
                            $returnarray['message'] = $gulden->response['error']['message'];
                        } else {
                            $returnarray = $createaccount;
                        }
					}
				}
			} else {
				$returnarray['code'] = $guldenresponse;
				$returnarray['message'] = $guldenresponsemessage;
			}
		} else {
			$returnarray['code'] = "0";
			$returnarray['message'] = "No wallet password supplied.";
		}
		
		echo json_encode($returnarray);
	}
	
	// Rename mining account
	elseif($_GET['action'] == "changeacc") {
		if(isset($_POST['changedacc'])!="" && isset($_POST['currentacc']) != "") {
			if(strpos($_POST['changedacc'], "*") === false) {
				$chaccount = $gulden->changeaccountname($_POST['currentacc'], trim($_POST['changedacc']));
				if($chaccount == "false") {
					$returnarray = $gulden->response;
				} else {
					$returnarray = $chaccount;
				}
				
				echo json_encode($returnarray);
			}
		}
	}
	
	// Delete mining account
	elseif($_GET['action'] == "deleteaccount") {
		$fromaccount = trim($_POST['selectedaccount']);
		$deletepass = trim($_POST['pass']);
		
		// Check the passphrase and unlock the wallet for 10 seconds if password is not empty
		$guldenresponse = "0";
		if($deletepass != "") {
			$gulden->walletpassphrase($deletepass, 10);
			$guldenresponse = $gulden->response['error']['code'];
			$guldenresponsemessage = $gulden->response['error']['message'];
			
			if($guldenresponse!="-14") {
				if(strpos($fromaccount, "*") === false) {
					$deletedaccount = $gulden->deleteaccount($fromaccount);
					if($deletedaccount == false) {
						$returnarray = "<div class='alert alert-warning'>".$gulden->response['error']['message']."</div>";
					} else {
						$returnarray = "<div class='alert alert-success'>Account has been deleted.</div>";
					}
				}
			} else {
				$returnarray = "<div class='alert alert-warning'>".$guldenresponsemessage."</div>";
			}
		} else {
			$returnarray = "<div class='alert alert-warning'>No wallet password supplied.</div>";
		}
		
		echo json_encode($returnarray);
	}
	
	// Change reward address
	elseif($_GET['action'] == "changeaddress") {
        $newaddress = trim($_POST['newaddress']);
		$gulden->setminingrewardaddress($newaddress);
        $guldenresponse = $gulden->response['error']['code'];
        if ($guldenresponse == "-1") {
            $returnarray['result'] = "false";
            $returnarray['msg'] = $gulden->response['error']['message'];
        }
        else {
            $guldenresponse = $gulden->getminingrewardaddress();
            $changeresult = "Mining reward address changed";
            if ($newaddress == '') $changeresult = $changeresult." to default";
            $changeresult = $changeresult.".";
            $returnarray['result'] = "true";
            $returnarray['address'] = $guldenresponse['address'];
            $returnarray['msg'] = $changeresult;
        }
				
		echo json_encode($returnarray);
	}
	
	// Handel mining request
	elseif($_GET['action'] == "handlemining") {
        $generate = $_POST['generate'];
        $UUID =  $_POST['uuid'];
        $numproc = $_POST['numproc'];
        $memsize = $_POST['memsize'];
        $rewardaddress = $_POST['rewardaddress'];
        $setrewardaddressresult = "true";
	
        if ($generate == "true") {
            $generateresult = $gulden->setgenerate(true, (int)$numproc, (int)$numproc, (int)$memsize.'M', $UUID);
        }
        else $generateresult = $gulden->setgenerate(false);
        
		if($setrewardaddressresult == false || $generateresult == "false") {
			$returnarray = '<div class="alert alert-warning">'.$gulden->response['error']['message'].'</div>';
		}
		else {
            // Update array
            if ($generate == "true") {
                $miningvalues = array();
                $miningvalues['numproc'] = $numproc;
                $miningvalues['memsize'] = $memsize;
                $CONFIG['mining'][$UUID] = $miningvalues;
            }
            else unset($CONFIG['mining'][$UUID]);
            
            // Save config
            $newconf = '<?php'.PHP_EOL.'$CONFIG = '.var_export($CONFIG, true).';'.PHP_EOL.'?>';
            file_put_contents('../config/config.php', $newconf);
            $returnarray = '<div class="alert alert-success">'.$generateresult.'</div>';
		}
        echo json_encode($returnarray);
	}
}
}
}
session_write_close();
?>
