function getTrackingDataGeneric(){
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/trackingdb.php",
		data: { p: 'getTrackingData' },
		async: false,
		success : function(s)
		{
			for(var i = 0; i < s.length; i++ ){
				s[i].options = "<input type='checkbox' class='ngs_checkbox' name='"+
				  s[i].sample_id+"' id='sample_checkbox_"+s[i].sample_id+"'></td>";
			}
			groupsStreamTable = createStreamTable('generic_tracking', s, "", true, [10,20,50,100], 20, true, true);
		}
	});
}

function getTrackingDataUnfiltered(){
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/trackingdb.php",
		data: { p: 'getTrackingDataUnfiltered' },
		async: false,
		success : function(s)
		{
			for(var i = 0; i < s.length; i++ ){
				s[i].options = "<input type='checkbox' class='ngs_checkbox' name='"+
				  s[i].sample_id+"' id='sample_checkbox_"+s[i].sample_id+"'></td>";
			}
			groupsStreamTable = createStreamTable('tracking_all', s, "", true, [10,20,50,100], 20, true, true);
		}
	});
}

function getTrackingDataAmazon(){
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/trackingdb.php",
		data: { p: 'getTrackingDataAmazon' },
		async: false,
		success : function(s)
		{
			for(var i = 0; i < s.length; i++ ){
				s[i].options = "<input type='checkbox' class='ngs_checkbox' name='"+
				  s[i].sample_id+"' id='sample_checkbox_"+s[i].sample_id+"'></td>";
			}
			groupsStreamTable = createStreamTable('tracking_amazon', s, "", true, [10,20,50,100], 20, true, true);
		}
	});
}

function getTrackingDataBackup(){
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/trackingdb.php",
		data: { p: 'getTrackingDataBackup' },
		async: false,
		success : function(s)
		{
			for(var i = 0; i < s.length; i++ ){
				s[i].options = "<input type='checkbox' class='ngs_checkbox' name='"+
				  s[i].sample_id+"' id='sample_checkbox_"+s[i].sample_id+"'></td>";
			}
			groupsStreamTable = createStreamTable('tracking_backup', s, "", true, [10,20,50,100], 20, true, true);
		}
	});
}


$(function() {
	"use strict";


	//	GROUPS
	getTrackingDataGeneric();
	getTrackingDataUnfiltered();
	getTrackingDataAmazon();
	getTrackingDataBackup();
});
