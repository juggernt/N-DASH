var refreshRate = 60000;
var accountuuid = '';
var loadjsondata = '';

function changeAccount(uuid) {
	accountuuid = uuid;
	$('#walletinfopanel').html("<img src='images/loading.gif' border='0' height='64' width='64'> Loading....");
	
	loadjsondata();
	
	$('#tabletransactions > tbody:last-child').html("<img src='images/loading.gif' border='0' height='64' width='64'> Loading....");
	loadtxdata();
}

function addAccount() {
	
	var newaccountnametosend = $('#newaccountname').val();
	var accountpassword = $('#createaccpass').val();
	$('#newaccountname').val("");
	$('#createaccpass').val("");
	
	$.post( "ajax/walletactions.php?action=addaccount", { accountname: newaccountnametosend, pass: accountpassword })
	 .done(function( data ) {
	 	var data = jQuery.parseJSON(data);
		//console.log(data);
		loadjsondata();
	});
}

function renameAccount() {
	
	var changedaccsend = $('#renameaccountname').val();
	$('#renameaccountname').val("");
	
	$.post( "ajax/walletactions.php?action=changeacc", { changedacc: changedaccsend, currentacc: accountuuid })
	 .done(function( data ) {
	 	var data = jQuery.parseJSON(data);
		//console.log(data);
		loadjsondata();
	});
	
}

function showRecoveryPhrase() {
	var phrase = $('#rppass').val();
	$('#showrp').html("<img src='images/loading.gif' border='0' height='64' width='64'> Loading....");
	$.post( "ajax/walletactions.php?action=showrecovery", { pass: phrase })
	 .done(function( data ) {
	 	var data = jQuery.parseJSON(data);
		//console.log(data);
		$('#showrp').html(data);
	});
	
	$('#rppass').val("");
	setTimeout(function(){ $('#showrp').html(""); }, 20000);
}

function createTransactionDetails() {
	var popupoptions = {
    	show: true,
        keyboard: false,
        backdrop: 'static'
	};
	
	$('#createtransaction').modal('toggle');
	$('#transactionpopup').modal(popupoptions);
	$('#confirmaddress').html($('#transactionaddress').val());
	$('#confirmamount').html($('#transactionamount').val());
	
	if($.isNumeric($('#transactionamount').val())) {
		
		$('#confirmtransactionmessage').html("");
		$('#conftransmes').html("");
		$('#transactionaddress').val("");
		$('#confirmtransactionpass').prop("disabled", false);
		$('#confirmedsubmit').prop("disabled", false);
				
	} else {
		$('#conftransmes').html("<div class='alert alert-warning'>Transaction amount is not a number!</div>");
		$('#confirmtransactionpass').prop("disabled", true);
		$('#confirmedsubmit').prop("disabled", true);
	}
	
	$('#transactionamount').val("");
}

function confirmTransactionDetails(confirmed) {
	if(confirmed=="true") {
		var transactionaddress = $('#confirmaddress').html();
		var transactionamount = $('#confirmamount').html();
		var transactionpassphrase = $('#confirmtransactionpass').val();
		
		$.post( "ajax/walletactions.php?action=createtransaction", { address: transactionaddress, amount: transactionamount, pass: transactionpassphrase, fromaccount: accountuuid })
		 .done(function( data ) {
		 	var data = jQuery.parseJSON(data);
		 	
		 	if(data=="1") {
		 		$('#conftransmes').html("<div class='alert alert-success'>Creating transaction.</div>");
		 		
				$('#confirmaddress').val("");
				$('#confirmamount').val("");
				$('#confirmtransactionpass').val("");
				setTimeout(function(){ 
					$('#conftransmes').html("");
					$('#transactionpopup').modal('toggle');
					loadjsondata();
					loadtxdata();
					}, 5000);
				
		 	} else if(data=="-1") {
		 		$('#conftransmes').html("<div class='alert alert-warning'>Wallet password incorrect.</div>");
		 		$('#confirmtransactionpass').val("");
		 	} else if(data=="-2") {
		 		$('#conftransmes').html("<div class='alert alert-warning'>Invalid Gulden address.</div>");
		 		$('#confirmtransactionpass').val("");
		 	} else if(data=="-6") {
		 		$('#conftransmes').html("<div class='alert alert-warning'>Insufficient funds.</div>");
		 		$('#confirmtransactionpass').val("");
		 	} else {
		 		console.log(data);
		 		$('#conftransmes').html("<div class='alert alert-warning'>Unknown error creating transaction. See console log.</div>");
		 		$('#confirmtransactionpass').val("");
		 	}
		});
	} else {
		$('#confirmaddress').val("");
		$('#confirmamount').val("");
		$('#confirmtransactionpass').val("");
		$('#conftransmes').html("");
		$('#transactionpopup').modal('toggle');
	}
}

function createNewAddress() {
	$.post( "ajax/walletactions.php?action=newaddress", { account: accountuuid })
	 .done(function( data ) {
	 	var data = jQuery.parseJSON(data);
		//console.log(data);
		loadjsondata();
	});
}

function impRecovery() {
	var imprec = $('#therecphrase').val();
	$.post( "ajax/walletactions.php?action=importrecphrase", { phrase: imprec })
	 .done(function( data ) {
	 	var data = jQuery.parseJSON(data);
	 	$('#importrecstatus').html(data);
		$('#therecphrase').val("");
		setTimeout(function(){ $('#importrecoveryphrase').modal('toggle'); }, 10000);
	});
}

function setPassPhrase() {
	$('#passerror').html("");
	var phrase = $('#rpnewpass').val();
	var phraserepeat = $('#rpnewpasstwo').val();
	
	if(phrase != phraserepeat) {
		$('#passerror').html("<div class='alert alert-warning'>Passwords do not match!</div>");
	} else {
		$.post( "ajax/walletactions.php?action=createpass", { passphrase: phrase })
		 .done(function( data ) {
		 	var data = jQuery.parseJSON(data);
			//console.log(data);
			$('#passerror').html("<div class='alert alert-success'>"+data+"</div>");
			setTimeout(function(){ $('#setuppassphrase').modal('toggle'); }, 10000);
		});
	}
	
	$('#rpnewpass').val("");
	$('#rpnewpasstwo').val("");
}

function changePassPhrase() {
	$('#changepasserror').html("");
	var coldphrase = $('#oldrpnewpass').val();
	var cphrase = $('#changedrpnewpass').val();
	var cphraserepeat = $('#changedrpnewpasstwo').val();
	
	if(cphrase != cphraserepeat) {
		$('#changepasserror').html("<div class='alert alert-warning'>Passwords do not match!</div>");
	} else {
		$('#changepasserror').html("<img src='images/loading.gif' border='0' height='64' width='64'> Loading....");
		$.post( "ajax/walletactions.php?action=newpass", { oldp: coldphrase, newp: cphrase })
		 .done(function( data ) {
		 	var data = jQuery.parseJSON(data);
		 	
		 	if(data != "Success") {
		 		$('#oldrpnewpass').val("");
				$('#changedrpnewpass').val("");
				$('#changedrpnewpasstwo').val("");
				
				$('#changepasserror').html("<div class='alert alert-warning'>"+data+"</div>");
		 	} else {
		 		//console.log(data);
			$('#changepasserror').html("<div class='alert alert-success'>"+data+"</div>");
			setTimeout(function(){ 
				$('#changepassphrase').modal('toggle'); 
				$('#changepasserror').html("");
				}, 5000);
		 	}
			
		});
	}
	
	$('#oldrpnewpass').val("");
	$('#changedrpnewpass').val("");
	$('#changedrpnewpasstwo').val("");
}

function numberWithCommas(x) {
    var parts = x.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}

$(document).ready(function() {
  	//Load json data for the dashboard
	loadjsondata = function() {
	  $.getJSON( "ajax/wallet.php?account="+accountuuid, function( data ) {
	  	
	  	  $('#errordiv').html("");
		  if(data['errors']!='') {
		  	$('#errordiv').html("<div class='alert alert-warning'>"+data['errors']+"</div>");
		  }
		  
		  if(data['server']['cpu']!='') {
		  	  if(data['disablewallet']=="1") {
		  	  	$('#walletinfopanel').html("Wallet disabled in Novo configuration");
		  	  } else {
		  	  	//Check if wallet is encrypted
		  	  	var encryptionerror = data['encryption'];
		  	  	var encryptionpng = '';
		  	  	
		  	  	if(encryptionerror=='-15') {
		  	  		if(data['syncprogress'] < 100) {
		  	  			$('#errordiv').append("<div class='alert alert-warning'>Wallet is not encrypted and can only be encrypted when sync is 100%. Currently " + data['syncprogress'] + "%)</div>");
		  	  		} else {
			  	  		$('#errordiv').append("<div class='alert alert-warning'>Wallet is not encrypted! Set a password to secure your wallet.</div>");
			  	  		$('#unencryptedwallet').show();
		  	  		}
		  	  	} else if(encryptionerror=='-1') {
		  	  		encryptionpng = "<img src='images/locked.png' height='20px' width='20px' title='Encrypted & locked'> ";
		  	  		$('#encryptedwallet').show();
		  	  	} else if(encryptionerror=='-17') {
		  	  		encryptionpng = "<img src='images/unlocked.png' height='20px' width='20px' title='Encrypted & unlocked'> ";
		  	  		$('#encryptedwallet').show();
		  	  	}
		  	  	
		  	  	accountuuid = data['selectedaccount'];
		  	  	$('#accountlistheader').html(encryptionpng+"Account list (N "+numberWithCommas(data['totalbalance'])+" / "+data['otherbalancesymbol']+" "+numberWithCommas(data['othertotalbalance'])+")");
		  	  	$('#accountlistpanel').html("");
		  	  	$.each(data['accountlist'], function( index, value ) {
		  	  		if(value['UUID']==accountuuid) {
		  	  			$('#currentaccountname').html(value['label']+"<small><a data-toggle=\"modal\" href=\"#renameaccount\"> ( Rename account ) </a></small>");
		  	  			$('#renameaccounttitle').html("<h4 class='modal-title'>Rename account '" + value['label'] + "'</h4>")
		  	  		}
		  	  		$('#accountlistpanel').append("<button type=\"button\" class=\"btn-link\" onclick=\"changeAccount('"+value['UUID']+"')\">"+value['label']+"</button><br>");
		  	  	});
		  	  	$('#accountlistpanel').append("<br><br><small><a data-toggle=\"modal\" href=\"#newaccount\"> ( Add account ) </a></small>");
		  	  	
		  	  	//"<b>Unconfirmed:</b> "+data['uncbalance']+"<br>"+
		  	  	//"<b>Address:</b> "+data['address']+" <small><button type=\"button\" class=\"btn-link\" onclick=\"createNewAddress()\">( Generate new address )</button></small><br><br>"+
                
                exchangerate = data['exchange'] + ': ';
                if (data['currentprice'] == 0) exchangerate += 'Not available!';
                else exchangerate += data['pricesymbol'] + " " + data['currentprice'].toFixed(data['rounding']);
		  	  	$('#walletinfopanel').html("<div class='alert alert-success'>Florin exchange rate from " + exchangerate + "</div>" +
                    "<b>Balance:</b> N "+numberWithCommas(data['balance'])+" / "+data['otherbalancesymbol']+" "+numberWithCommas(data['otherbalance'])+"<br>"+
										   "<b>Address:</b> "+data['address']+"<br><br>"+
				  						   "<div id='novoqr'></div>");
				
				var qrcode = new QRCode("novoqr", {
				    text: "novo:"+data['address'],
				    width: 128,
				    height: 128,
				    colorDark : "#000000",
				    colorLight : "#ffffff",
				    correctLevel : QRCode.CorrectLevel.H
				});
				
			  }
		  } else {
		  	  $('#walletinfopanel').html("Novo-daemon is not running");
		  }
	   });
	};
	
	loadjsondata();
	
	//Load the json data for the dashboard every x seconds
	setInterval (function () {
		loadjsondata()
	}, refreshRate)
});
