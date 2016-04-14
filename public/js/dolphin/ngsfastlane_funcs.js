/*
 *Author: Nicholas Merowsky
 *Date: 07 May 2015
 *Ascription:
 */

var id_array = ['genomebuild', 'barcode_sep', 'spaired', 'series_name', 'lane_name', 'input_dir', 'input_files', 'backup_dir', 'amazon_bucket',
				'Barcode Definitions', 'groups', 'perms'];
var formatInfo = ['Barcode is in 5\' end of read 1', 'Barcode is in 3\' end of read 2 or Single end where tag is on 3\' end',
					'Barcode is in Header record using Illumina Casava (pre V1.8) pipeline format', 'There is no barcode on Read 1 of a pair, in this case read 2 must have barcode on 5\' end',
					'Paired end with tag on 5\' end of both reads'];

function expandBarcodeSep(){
	//	Obtain information
	var expandType = document.getElementById('barcode_sep').value;
	var barcodeDiv = document.getElementById('barcode_div');
	var barcodeOptDiv = document.getElementById('barcode_opt_div');
	
	//	Check the expand type
	if (expandType == 'yes') {
		barcodeDiv.style.display = 'inline';
		barcodeOptDiv.style.display = 'inline';
		document.getElementById('input_files').placeholder = "Paired End Example:\nlane_001_R1.fastq.gz lane_001_R2.fastq\nSingle End Example:\nlane_001.fastq.gz";
	}else{
		barcodeDiv.style.display = 'none';
		barcodeOptDiv.style.display = 'none';
		document.getElementById('input_files').placeholder = "Paired End Example:\nlibrary_name_rep1 lib_rep1_R1.fastq.gz lib_rep1_R2.fastq.gz\nSingle End Example:\nlibrary_name_rep1 lib_rep1.fastq.gz";
	}
}

function submitFastlaneButton() {
	var value_array = [];
	//	genomebuild placeholder
	value_array.push("human,hg19");
	for(var x = 0; x < id_array.length; x++){
		if (id_array[x] == 'input_files'){
			//	Manual Text box
			if (document.getElementById('Manual_toggle').parentNode.className == "active") {
				//	obtain value and trim and replace commas and tabs
				var value_str = document.getElementById(id_array[x]).value.trim().replace(/[\t\,]+/g, " ");
				//	push to array
				console.log(value_str);
				value_array.push(value_str);
			//	File-Directory Selection
			}else{
				var value_str = "";
				//	For every selected entry
				for(var key in NAME_FILE_STORAGE ){
					//	Single end
					if (NAME_FILE_STORAGE[key] != undefined && document.getElementById(id_array[3]).value == 'yes') {
						for (var y = 0; y < NAME_FILE_STORAGE[key][0].length; y++) {
							if (y + 1 == NAME_FILE_STORAGE[key][0].length) {
								value_str += key + " " + NAME_FILE_STORAGE[key][0][y];
							}else{
								value_str += key + " " + NAME_FILE_STORAGE[key][0][y] + "\n";
							}
						}
					//	Paired end
					}else if (NAME_FILE_STORAGE[key] != undefined){
						for (var y = 0; y < NAME_FILE_STORAGE[key][0].length; y++) {
							if (y + 1 == NAME_FILE_STORAGE[key][0].length) {
								value_str += key + " " + NAME_FILE_STORAGE[key][0][y] + " " + NAME_FILE_STORAGE[key][1][y];
							}else{
								value_str += key + " " + NAME_FILE_STORAGE[key][0][y] + " " + NAME_FILE_STORAGE[key][1][y] + "\n";
							}
						}
					}
				}
				console.log(value_str);
				value_array.push(value_str);
			}
		}else if (document.getElementById(id_array[x]) != null) {
			//	obtain value and trim and replace commas and tabs
			var value_str = document.getElementById(id_array[x]).value.trim().replace(/[\t\,]+/g, " ");
			//	push to array
			value_array.push(value_str);
		}
		if (id_array[x] == "perms") {
			var perms = $('.checked')[0].children[0].value
			value_array.push(perms);
		}
	}
	sendProcessData(value_array, 'fastlane_values');
	
	var barcode_array = [];
	barcode_array.push(document.getElementById('bar_distance').value);
	barcode_array.push(document.getElementById('bar_format').value);
	sendProcessData(barcode_array, 'barcode_array');
	
	var checked_values = checkFastlaneInput(value_array);
	console.log(checked_values);
	sendProcessData(checked_values, 'pass_fail_values');
	var bad_samples = getBadSamples();
	sendProcessData(bad_samples, 'bad_samples');
}

function backToFastlane(){
	window.history.back();
}

function fastlaneToPipeline(sample_ids){
	window.location.href = BASE_PATH+"/pipeline/selected/" + sample_ids + "$";
}

$(function() {
	if (document.getElementById('barcode_sep') != null) {
		if(document.getElementById('barcode_sep').value == 'yes'){
			var barcodeDiv = document.getElementById('barcode_div');
			barcodeDiv.style.display = 'inline';
			var barcodeOptDiv = document.getElementById('barcode_opt_div');
			barcodeOptDiv.style.display = 'inline';
		}
	}
});