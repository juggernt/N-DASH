var refreshRate = 60000;
var accountuuid = '';
var arenaproc = 0;
var loadjsondata = '';

//Create a new mining account
function addAccount() {
	var newaccountnametosend = $('#newaccountname').val();
	var addpasswordtosend = $('#newaccountpassword').val();
	$('#newaccountpassword').val("");
	$('#creataccountbutton').prop("disabled", true);
	
	$.post( "ajax/miningactions.php?action=createaccount", { accountname: newaccountnametosend, addpassword: addpasswordtosend })
	 .done(function( data ) {
	 	var data = jQuery.parseJSON(data);
	 	if(data['message']==undefined) {
	 		$('#creationmessage').html("<div class='alert alert-info'>Mining account created</div>");
	 		
	 		setTimeout(function(){ 
				$('#newaccountname').val("");
				$('#addwitnessaccount').modal('toggle');
				$('#creationmessage').html("");
				$('#creataccountbutton').prop("disabled", false);
				loadjsondata();
			}, 5000);
	 	} else {
	 		$('#creationmessage').html("<div class='alert alert-danger'>"+data['message']+"</div>");
	 		$('#creataccountbutton').prop("disabled", false);
	 	}
		//console.log(data);
	});
}

function changeAccount(uuid) {
	accountuuid = uuid;
	$('#currentaccountname').html("<img src='images/loading.gif' border='0' height='64' width='64'> Loading....");
	
	loadjsondata();
}

//Delete witness account
function confirmDeleteAccount(deletesure) {
	var selectedminingaccountdelete = $('#selectedminingaccountdelete').val();
	var delpass = $('#delpass').val();
	$('#delpass').val("");
	
	if(deletesure == "true") {
		$.post( "ajax/miningactions.php?action=deleteaccount", { selectedaccount: selectedminingaccountdelete, pass: delpass })
		 .done(function( data ) {
		 	var data = jQuery.parseJSON(data);
			//console.log(data);
			$('#showdelresponse').html(data);
			setTimeout(function(){ 
	 			$('#deleteaccount').modal('toggle');
	 			$('#showdelresponse').html("");
				loadjsondata();
			}, 5000);
			
		});
	} else {
		$('#deleteaccount').modal('toggle');
	}
}

function changeAddress() {
	$.post( "ajax/miningactions.php?action=changeaddress", { newaddress: $('#newrewardaddress').val() })
	 .done(function( data ) {
		var data = jQuery.parseJSON(data);
        if (data['result'] == "true") {
            $('#currentrewardaddress').val(data['address']);
            $('#newrewardaddress').val('');
        }
        $('#showchangeresponse').html(data['msg']);
     });
     loadjsondata();
}

//Handle mining starting and stopping
function handleArenaSetup() {
    arenaproc = $("#slider").slider("option", "value");
    $('#generateresult').html("<div class='alert alert-warning'>Arena Setup will use " + arenaproc + " thread(s). Set #threads and memory size used for mining..</div>");
    $("#slider2").slider("enable");
    $("#miningbutton1").prop("disabled", true);
    $("#miningbutton2").prop("disabled", false);
}

//Handle mining starting and stopping
function handleMining(uuid, rewardaddress) {
    var generate;
    var numproc;
    var memsize;
    if ($("#miningbutton2").text().substring(0, 5) === "Start") {
        numproc = $("#slider").slider("option", "value");
        memsize = $("#slider2").slider("option", "value");
        $("#slider").slider("disable");
        $("#slider2").slider("disable");
        $("#numproc2").text(": "+numproc);
        $("#memsize2").text(": "+memsize+" MB");
        generate = "true";
        document.getElementById("miningbutton2").innerHTML = "Stop mining";
    }
    else {
        $("#slider").slider("enable");
        $("#slider2").slider("disable");
        $("#numproc2").text("");
        $("#memsize2").text("");
        arenaproc = 0;
        numproc = 0;
        memsize = 0;
        $("#miningbutton1").prop("disabled", false);
        $("#miningbutton2").prop("disabled", true);
        generate = "false";
        document.getElementById("miningbutton2").innerHTML = "Start mining";
    }
    $.post( "ajax/miningactions.php?action=handlemining",
             { generate: generate, uuid: uuid, numproc: numproc, arenaproc: arenaproc, memsize: memsize, rewardaddress: rewardaddress })
     .done(function( data ) {
        var data = jQuery.parseJSON(data);
        $('#generateresult').html(data);
    });
}

$(document).ready(function() {
loadjsondata = function() {
    $.getJSON("ajax/mining.php?account=" + accountuuid, function(data) {
        var miningactionsbody = '';
        var miningaccountsbody = '';
        var miningpanelbody = '';
        var miningpanelbody2 = '';
        var miningbuttons = '';
        var miningpanelbody3 = '';
        var generateresult = '';

        if(data['errors'] != '') {
		  	$('#errordiv').html("<div class='alert alert-warning'>"+data['errors']+"</div>");
		}
		else {
            // Only 1 mining account possible
            var numAccounts = data['accountlist'].length;

            if (numAccounts > 0) {
                // Actions: Rename and Delete
                miningactionsbody +=
                        '<ul><li><a data-toggle="modal" href="#renameaccount">Rename account</a></li>' +
                        '<li><a data-toggle="modal" href="#deleteaccount">Delete account</a></li>' +
                        '<li><a data-toggle="modal" href="#changeaddress">Change reward address</a></li></ul>';
            
                // Put account name in list and current account name in header
		  	  	accountuuid = data['selectedaccount'];
                var rewardaddress;
                var numproc = data['numproc'];
                var memsize = data['memsize'];
                if (arenaproc > 0 && numproc == 0) return;

		  	  	$.each(data['accountlist'], function( index, value ) {
		  	  		if(value['UUID'] == accountuuid) {
                        accountname = value['label'];
                        $('#currentaccountname').html(accountname);
                        $('#selectedminingaccountdelete').val(accountname);
                        rewardaddress = value['rewardaddress'];
                        $('#currentrewardaddress').val(rewardaddress);
                    }
		  	  		miningaccountsbody += '<button type="button" class="btn-link" onclick="changeAccount(' +
                        value['UUID'] + ')">' + value['label'] + '</button><br>';
                });
                
                // Mining reward address
                miningpanelbody += '<div style="margin-top: 20px"><b>Mining reward address: </b>' + rewardaddress + '</div>';
                
                // Mining info
                miningpanelbody += '<div style="margin-top: 10px">';
                miningpanelbody += '<b>Mining info:</b><br>';
                miningpanelbody += 'Blocks: ' + data['mininginfo']['blocks'] + '<br>';
                miningpanelbody += 'Current Block Size: ' + data['mininginfo']['currentblocksize'] + '<br>';
                miningpanelbody += 'Current Block Weight: ' + data['mininginfo']['currentblockweight'] + '<br>';
                miningpanelbody += 'Current Block Tx: ' + data['mininginfo']['currentblocktx'] + '<br>';
                miningpanelbody += 'Difficulty: ' + data['mininginfo']['difficulty'] + '<br>';
                miningpanelbody += 'Errors: ' + data['mininginfo']['errors'] + '<br>';
                miningpanelbody += 'Mining Thread Limit: ' + data['mininginfo']['genproclimit'] + '<br>';
                miningpanelbody += 'Mining Memory Limit: ' + data['mininginfo']['genmemlimit'] + '<br>';
                miningpanelbody += 'Network Hash PS: ' + data['mininginfo']['networkhashps'] + '<br>';
                miningpanelbody += 'Pooled Tx: ' + data['mininginfo']['pooledtx'] + '<br>';
                miningpanelbody += 'Chain: ' + data['mininginfo']['chain'] + '<br>';
                miningpanelbody += '</div>';

                // Select # processors
                var numcpu = data['numcpu'];
                if ( !$("#slider").data("ui-slider") ) {
                    $("#slider").slider(
                        {range: "min", min: 1, step: 1, max: numcpu, value: 1,
                        create: function() {$("#numproc").text( $( this ).slider( "value" ));},
                        slide: function(event, ui) {$("#numproc").text( ui.value );}});
                }
                
                miningpanelbody += '<div style="margin-top: 30px; text-align: center"><b># Threads (max: 8)</b><b id="numproc2"></b></div>';
                $('#miningpanelbody').html(miningpanelbody);
                
                if (numproc == 0) numproc = $("#slider").slider("value");
                else {
                    $("#slider").slider("value", numproc);
                    $("#slider").slider("disable");
                    $("#numproc").text(numproc);
                    $("#numproc2").text(": "+numproc);
                }
                
                // Select memory size
                if ( !$("#slider2").data("ui-slider") ) {
                    $("#slider2").slider(
                        {range: "min", min: 512, step: 512, max: 4096, value: 512,
                        create: function() {$("#memsize").text( $( this ).slider( "value" ));},
                        slide: function(event, ui) {$("#memsize").text( ui.value );}});
                }
                
                miningpanelbody2 += '<b>Memory (max: 4096 MB)</b><b id="memsize2"></b>';
                $('#miningpanelbody2').html(miningpanelbody2);
                
                if (memsize == 0) memsize = $("#slider2").slider("value");
                else {
                    $("#slider2").slider("value", memsize);
                    $("#slider2").slider("disable");
                    $("#memsize").text(memsize);
                    $("#memsize2").text(": "+memsize+" MB");
                }
                
                // Mining buttons
                miningbuttons += '<center><button type="button" class="btn btn-success" id="miningbutton1" ' +
                        'onclick="handleArenaSetup()" style="margin-right: 50px">Arena Setup</button><button type="button" class="btn btn-success" id="miningbutton2" onclick="handleMining(' + "'" + accountuuid + "'" + ', ' + "'" + rewardaddress + "'" + ')">';
                if (data['miningstate'] === false) {
                    miningbuttons += 'Start';
                    generateresult = '<div class="alert alert-warning">Mining not active, set #threads for Arena Setup..</div>';
                    $("#slider2").slider("disable");
                }
                else {
                    miningbuttons += 'Stop';
                    generateresult = '<div class="alert alert-warning">Mining is active..</div>';
                }
                miningbuttons += ' Mining</button></center>';

                // Mining statistics
                if (data['statistics'] != '') {
                    var stats = data['statistics'];
                    miningpanelbody3 += "<b>Mining Statistics:</b><p>";
                    miningpanelbody3 += "Last Reported: " + stats['last_reported'] + "<br>";
                    miningpanelbody3 += "Rolling Average: " + stats['rolling_average'] + "<br>";
                    miningpanelbody3 += "Best Reported: " + stats['best_reported'] + "<br>";
                    miningpanelbody3 += "Arena Setup: " + stats['arena_setup'];
                }
            }
            else miningactionsbody += '<ul><li><a data-toggle="modal" href="#addminingaccount">Create account</a></li></ul>';

            $('#miningactionspanel').html(miningactionsbody + '</ul>');
            $('#miningaccountspanel').html(miningaccountsbody);
            $('#miningbuttons').html(miningbuttons);
            $('#miningpanelbody3').html(miningpanelbody3);
            $('#generateresult').html(generateresult);
            if (data['miningstate'] === false) $("#miningbutton2").prop("disabled", true);
            else $("#miningbutton1").prop("disabled", true);
        }
    });
};
loadjsondata();
//Load the json data for the dashboard every x seconds
setInterval (function () {loadjsondata()}, refreshRate)
});
