var refreshRate = 60000;
var accountuuid = '';
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
function handleMining(uuid, rewardaddress) {
    var generate;
    var numproc;
    var memsize;
    if ($("#miningbutton2").text().substring(0, 5) === "Start") {
        numproc = $("#slider").slider("option", "value");
        if (numproc < 1) {
            $('#generateresult').html("<div class='alert alert-warning'># Threads not given!</div>");
            return;
        }
        else {
            memsize = $("#slider2").slider("option", "value");
            console.log(memsize);
            if (memsize < 1) {
                $('#generateresult').html("<div class='alert alert-warning'>Memory not given!</div>");
                return;
            }
        }
        $("#slider").slider("disable");
        $("#slider2").slider("disable");
        $("#numproc2").text(": "+numproc);
        $("#memsize2").text(": "+memsize+" MB");
        document.getElementById("miningbutton2").innerHTML = "Stop mining";
        generate = "true";
        $('#generateresult').html("<div class='alert alert-warning'>Mining started..</div>");
    }
    else {
        $("#slider").slider("enable");
        $("#slider2").slider("enable");
        $("#numproc2").text("");
        $("#memsize2").text("");
        document.getElementById("miningbutton2").innerHTML = "Start mining";
        numproc = 0;
        memsize = 0;
        generate = "false";
        $('#generateresult').html("<div class='alert alert-warning'>Mining stopped..</div>");
    }
    $("#miningbutton2").button("disable");
    $.post( "ajax/miningactions.php?action=handlemining",
             { generate: generate, uuid: uuid, numproc: numproc, memsize: memsize, rewardaddress: rewardaddress })
     .done(function( data ) {
        console.log(data);
        var data = jQuery.parseJSON(data);
        $('#generateresult').html(data);
    });
    $("#miningbutton2").button("enable");
}

$(document).ready(function() {
loadjsondata = function() {
    $.getJSON("ajax/mining.php?account=" + accountuuid, function(data) {
        var miningactionsbody = '';
        var miningaccountsbody = '';
        var miningpanelbody = '';
        var miningpanelbody2 = '';
        var miningbutton = '';
        var miningpanelbody4 = '';
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
		  	  	$.each(data['accountlist'], function( index, value ) {
		  	  		if(value['UUID'] == accountuuid) {
                        accountname = value['label'];
                        $('#currentaccountname').html(accountname);
                        $('#selectedminingaccountdelete').val(accountname);
                        rewardaddress = value['rewardaddress'];
                        $('#currentrewardaddress').val(rewardaddress);
                    }
		  	  		miningaccountsbody += "<button type=\"button\" class=\"btn-link\" onclick=\"changeAccount('" +
                        value['UUID'] + "')\">" + value['label'] + "</button><br>";
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
                if ( !$("#slider").data("ui-slider") ) {                
                    $("#slider").slider(
                        {range: "min", min: 0, step: 1, max: 8, value: 0,
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
                        {range: "min", min: 0, step: 512, max: 4096, value: 0,
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
                
                // Mining button
                miningbutton += '<center><button type="button" class="btn btn-success" id="miningbutton2" ' +
                        'onclick="handleMining(' + "'" + accountuuid + "'" + ', ' + "'" + rewardaddress + "'" + ')">';
                if (data['miningstate'] === false) {
                    miningbutton += "Start ";
                    generateresult = "<div class='alert alert-warning'>Mining not active..</div>";
                }
                else {
                    miningbutton += "Stop ";
                    generateresult = "<div class='alert alert-warning'>Mining is active..</div>";
                }
                miningbutton += "Mining";
                miningbutton += '</button></center>';
                
                // Mining statistics
                if (data['statistics'] != '') {
                    var stats = data['statistics'];
                    miningpanelbody4 += "<b>Mining Statistics:</b><p>";
                    miningpanelbody4 += "Last Reported: " + stats['last_reported'] + "<br>";
                    miningpanelbody4 += "Rolling Average: " + stats['rolling_average'] + "<br>";
                    miningpanelbody4 += "Best Reported: " + stats['best_reported'] + "<br>";
                    miningpanelbody4 += "Arena Setup: " + stats['arena_setup'];
                }
            }
            else miningactionsbody += "<ul><li><a data-toggle='modal' href='#addminingaccount'>Create account</a></li></ul>";

            $('#miningactionspanel').html(miningactionsbody + "</ul>");
            $('#miningaccountspanel').html(miningaccountsbody);
            $('#miningbutton').html(miningbutton);
            $('#miningpanelbody4').html(miningpanelbody4);
            $('#generateresult').html(generateresult);
        }
    });
};
loadjsondata();
//Load the json data for the dashboard every x seconds
setInterval (function () {loadjsondata()}, refreshRate)
});
