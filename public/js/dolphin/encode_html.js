$( function(){
	//Donor
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/encode_tables.php",
		data: { p: "getDonors", samples:basket_info },
		async: false,
		success : function(s)
		{
			console.log(s);
			var donortable = $('#jsontable_donors').dataTable();
			for(var x = 0; x < s.length; x++){
				donortable.fnAddData([
					s[x].id,
					s[x].donor,
					s[x].life_stage,
					s[x].age,
					s[x].sex,
					s[x].donor_acc,
					s[x].donor_uuid
				]);
			}
		}
	});
	//Experiment
	/*
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/encode_tables.php",
		data: { p: "getExperiments", samples:basket_info },
		async: false,
		success : function(s)
		{
			console.log(s);
		}
	});
	*/
});