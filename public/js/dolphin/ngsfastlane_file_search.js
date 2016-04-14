
var username;
var selected_name;
var nameEditToggle;
var NAME_FILE_STORAGE = {};
var DISABLED_FILE1 = [];
var DISABLED_FILE2 = [];

function searchDirectoryModal() {
	if (document.getElementById('spaired').value == 'no') {
		document.getElementById('read_2_label').setAttribute('style', 'display:none');
		document.getElementById('read_2_input').setAttribute('style', 'display:none');
	}else{
		document.getElementById('read_2_label').setAttribute('style', 'display:show');
		document.getElementById('read_2_input').setAttribute('style', 'display:show');
	}
	$('#regexModal').modal({
		show: true
	});
}

//	Function called when 'Search Directory' button is clicked
function queryDirectory() {
	//	Grab directory
	var directory = document.getElementById('input_dir').value;
	//	If not empty, search directory
	if (directory != '') {
		//	Grab web variables
		var read_1_input = document.getElementById('read_1_input').value;
		var read_2_input = document.getElementById('read_2_input').value;
		
		var regexRead1 = new RegExp(read_1_input);
		if (document.getElementById('spaired').value == 'yes') {
				var regexRead2 = new RegExp(read_2_input);
		}else{
			var regexRead2 = undefined;
		}
		var R1 = [];
		var R2 = [];
		//	Grab files from directory
		var file_list = directoryGrab(directory, regexRead1, regexRead2);;
		if (file_list[0] == "" || file_list[0].indexOf("ls: cannot access") > -1) {
			$('#errorModal').modal({
				show: true
			});
			document.getElementById('errorLabel').innerHTML = 'You cannot access this directory.';
			document.getElementById('errorAreas').innerHTML = '';
		}else{
			//	If Paired end
			for (var x = 0; x < file_list.length; x++) {
				if (document.getElementById('spaired').value == 'yes') {
					//	Show R2 Options
					document.getElementById('input_file2').setAttribute('style', 'display:show');
					document.getElementById('send_R1_button').setAttribute('style', 'display:show');
					document.getElementById('send_R2_button').setAttribute('style', 'display:show');
					//	Regex R1
					if (regexRead1.test(file_list[x])) {
						R1.push(file_list[x]);
					//	Regex R2
					}else if (regexRead2.test(file_list[x])) {
						R2.push(file_list[x]);
					}
				//	Single end
				}else{
					//	Remove R2 Options
					document.getElementById('input_file2').setAttribute('style', 'display:none');
					document.getElementById('send_R1_button').setAttribute('style', 'display:none');
					document.getElementById('send_R2_button').setAttribute('style', 'display:none');
					//	Regex fastq
					if (regexRead1.test(file_list[x])) {
						R1.push(file_list[x]);
					}
				}
			}
			
			//	Add to Selection Boxes
			var namingIndex = [];
			var nameOptions = '';
			var selectOptions1 = '';
			var selectOptions2 = '';
			for(var x = 0; x < R1.length; x++){
				var name = '';
				if (R1[x].split(read_1_input).length != 1 && read_1_input != '') {
					name = R1[x].split(read_1_input)[0]
				}else if (R1[x].split('.fastq').length != 1){
					name = R1[x].split('.fastq')[0];
				}else{
					name = R1[x].split('.fq')[0];
				}
				if (namingIndex[name] == undefined) {
					namingIndex[name] = R1[x]
					nameOptions += '<option value="'+name+'">'+name+'</option>';
				}
				selectOptions1 += '<option value="'+R1[x]+'">'+R1[x]+'</option>';
			}
			if (R2.length != 0) {
				for(var x = 0; x < R2.length; x++){
					selectOptions2 += '<option value="'+R2[x]+'">'+R2[x]+'</option>';
				}
			}
			document.getElementById('file_names').innerHTML = nameOptions;
			document.getElementById('file1_select').innerHTML = selectOptions1;
			document.getElementById('file2_select').innerHTML = selectOptions2;
			
			//	After successful query
			document.getElementById('Directory_toggle').setAttribute('data-toggle', 'tab');
			$('.nav-tabs a[href="#Directory"]').tab('show')
		}
	}else{
		$('#errorModal').modal({
			show: true
		});
		document.getElementById('errorLabel').innerHTML = 'You cannot search through an empty directory!';
		document.getElementById('errorAreas').innerHTML = '';
	}
}

//	Function called to search the directory
function directoryGrab(directory){
	var file_array = [];
	$.ajax({
		type: 	'GET',
		url: 	BASE_PATH+'/public/api/service.php?func=directoryContents&directory='+directory+'&username='+username.clusteruser,
		async:	false,
		success: function(s)
		{
			console.log(s);
			file_array = s.split('\n');
			file_array.pop();
			console.log(file_array);
		}
	});
	return file_array;
}

function editName() {
	var edit = document.getElementById('editNameInput');
	selected_name = document.getElementById('file_names').value;
	nameEditToggle = true;
	edit.setAttribute('style', 'display:show');
	edit.value = selected_name;
	edit.innerHTML = selected_name;
	document.getElementById('confirm_edit_name_button').setAttribute('style', 'display:show');
	document.getElementById('cancel_edit_name_button').setAttribute('style', 'display:show');
}

function confirmNameEdit() {
	var new_file_name = document.getElementById('editNameInput').value;
	if (nameEditToggle) {
		NAME_FILE_STORAGE[new_file_name] = NAME_FILE_STORAGE[document.getElementById('file_names').value];
		delete NAME_FILE_STORAGE[document.getElementById('file_names').value];
		$('#file_names option[value="'+selected_name+'"]')[0].innerHTML = new_file_name;
		$('#file_names option[value="'+selected_name+'"]')[0].value = new_file_name;
	}else{
		var select = document.getElementById('file_names');
		var option = document.createElement("option");
		option.value = new_file_name;
		option.innerHTML = new_file_name;
		select.add(option);
	}
	selected_name = '';
	document.getElementById('editNameInput').setAttribute('style', 'display:none');
	document.getElementById('confirm_edit_name_button').setAttribute('style', 'display:none');
	document.getElementById('cancel_edit_name_button').setAttribute('style', 'display:none');
}

function cancelNameEdit(){
	selected_name = '';
	document.getElementById('editNameInput').setAttribute('style', 'display:none');
	document.getElementById('confirm_edit_name_button').setAttribute('style', 'display:none');
	document.getElementById('cancel_edit_name_button').setAttribute('style', 'display:none');
}

function addName(){
	nameEditToggle = false;
	var edit = document.getElementById('editNameInput');
	edit.value = '';
	edit.innerHTML = '';
	edit.setAttribute('style', 'display:show');
	document.getElementById('confirm_edit_name_button').setAttribute('style', 'display:show');
	document.getElementById('cancel_edit_name_button').setAttribute('style', 'display:show');
}

function removeName(){
	var selected_name = document.getElementById('file_names').value;
	for(var x = 0; x < NAME_FILE_STORAGE[selected_name].length; x++){
		if (x == 0) {
			for(file in NAME_FILE_STORAGE[selected_name][x]){
				if (DISABLED_FILE1.indexOf(NAME_FILE_STORAGE[selected_name][x][file]) != -1) {
					DISABLED_FILE1.splice(DISABLED_FILE1.indexOf(NAME_FILE_STORAGE[selected_name][x][file]), 1);
				}
			}
		}else{
			for(file in NAME_FILE_STORAGE[selected_name][x]){
				if (DISABLED_FILE2.indexOf(NAME_FILE_STORAGE[selected_name][x][file]) != -1) {
					DISABLED_FILE2.splice(DISABLED_FILE2.indexOf(NAME_FILE_STORAGE[selected_name][x][file]), 1);
				}
			}
		}
	}
	delete NAME_FILE_STORAGE[selected_name];
	selectName();
	$('#file_names option[value="'+selected_name+'"]')[0].remove();
}

function removeFile(read){
	var selected_name = document.getElementById('file_names').value;
	var file_select = document.getElementById('file'+read+'_select');
	for(var x = file_select.options.length - 1; x >= 0; x--){
		if (file_select.options[x].selected) {
			if (read == 1) {
				DISABLED_FILE1.splice(DISABLED_FILE1.indexOf(file_select), 1);
				NAME_FILE_STORAGE[selected_name][0].splice(NAME_FILE_STORAGE[selected_name][0].indexOf(file_select), 1);
			}else{
				DISABLED_FILE2.splice(DISABLED_FILE2.indexOf(file_select), 1);
				NAME_FILE_STORAGE[selected_name][0].splice(NAME_FILE_STORAGE[selected_name][1].indexOf(file_select), 1);
			}
			file_select.options[x].remove();
			if (NAME_FILE_STORAGE[selected_name][0].length == 0 && NAME_FILE_STORAGE[selected_name][1].length == 0) {
				delete NAME_FILE_STORAGE[selected_name];
			}
		}
	}
	selectName();
}

function swapFiles(swap1, swap2) {
	var current_selection = document.getElementById('file_names').selectedOptions;
	var file1_select = document.getElementById('file'+swap1+'_select');
	var file2_select = document.getElementById('file'+swap2+'_select');
	var options_list = [];
	console.log(current_selection);
	for(var x = file1_select.options.length - 1; x >= 0; x--){
		if (file1_select.options[x].selected) {
			if (DISABLED_FILE1.indexOf(file1_select.options[x].value) > -1) {
				NAME_FILE_STORAGE[current_selection[0].value][0].splice(NAME_FILE_STORAGE[current_selection[0].value][0].indexOf(file1_select.options[x].value), 1);
				DISABLED_FILE1.splice(DISABLED_FILE1.indexOf(file1_select.options[x].value), 1);
			}else if (DISABLED_FILE2.indexOf(file1_select.options[x].value) > -1) {
				NAME_FILE_STORAGE[current_selection[0].value][1].splice(NAME_FILE_STORAGE[current_selection[0].value][1].indexOf(file1_select.options[x].value), 1);
				DISABLED_FILE2.splice(DISABLED_FILE2.indexOf(file1_select.options[x].value), 1);
			}
			var option = document.createElement('option');
			option.value = file1_select.options[x].value;
			option.innerHTML = file1_select.options[x].value;
			options_list.push(option);
			file1_select.options[x].remove();
		}
	}
	options_list.reverse();
	for(var x = 0; x < options_list.length; x++){
		file2_select.add(options_list[x]);	
	}
}

function selectName(){
	var current_selection = document.getElementById('file_names').selectedOptions[0].value;
	var select_file1 = document.getElementById('file1_select');
	var select_file2 = document.getElementById('file2_select');
	
	if (NAME_FILE_STORAGE[current_selection] != undefined) {
		for(var x = 0; x < select_file1.options.length; x++){
			if (NAME_FILE_STORAGE[current_selection][0].indexOf(select_file1.options[x].value) != -1) {
				select_file1.options[x].disabled = false;
				select_file1.options[x].selected = true;
			}else if(DISABLED_FILE1.indexOf(select_file1.options[x].value) > -1){
				select_file1.options[x].disabled = true;
				select_file1.options[x].selected = false;
			}else{
				select_file1.options[x].selected = false;
				select_file1.options[x].disabled = false;
			}
		}
		for(var x = 0; x < select_file2.options.length; x++){
			if (NAME_FILE_STORAGE[current_selection][1].indexOf(select_file2.options[x].value) != -1) {
				select_file2.options[x].disabled = false;
				select_file2.options[x].selected = true;
			}else if(DISABLED_FILE2.indexOf(select_file2.options[x].value) > -1){
				select_file2.options[x].disabled = true;
				select_file2.options[x].selected = false;
			}else{
				select_file2.options[x].selected = false;
				select_file2.options[x].disabled = false;
			}
		}
	}else{
		for(var x = 0; x < select_file1.options.length; x++){
			select_file1.options[x].selected = false;
			if(DISABLED_FILE1.indexOf(select_file1.options[x].value) > -1){
				select_file1.options[x].disabled = true;
			}
		}
		for(var x = 0; x < select_file2.options.length; x++){
			select_file2.options[x].selected = false;
			if(DISABLED_FILE2.indexOf(select_file2.options[x].value) > -1){
				select_file2.options[x].disabled = true;
			}
		}
	}
}

function selectFile(read){
	var name_check = document.getElementById('file_names').selectedOptions.length;
	if (name_check > 0) {
		var current_selection = document.getElementById('file_names').selectedOptions[0].value;
		var select_file1 = document.getElementById('file1_select');
		var select_file2 = document.getElementById('file2_select');
		var R1 = [];
		var R2 = [];
		if (current_selection != undefined) {
			console.log(current_selection)
			for(var x = 0; x < select_file1.options.length; x ++){
				if (select_file1.options[x].selected) {
					R1.push(select_file1.options[x].value);
					if (DISABLED_FILE1.indexOf(select_file1.options[x].value) == -1) {
						DISABLED_FILE1.push(select_file1.options[x].value);
					}
				}else if (DISABLED_FILE1.indexOf(select_file1.options[x].value) > -1 && select_file1.options[x].disabled == false) {
					DISABLED_FILE1.splice(DISABLED_FILE1.indexOf(select_file1.options[x].value), 1);
				}
			}
			for(var x = 0; x < select_file2.options.length; x ++){
				if (select_file2.options[x].selected) {
					R2.push(select_file2.options[x].value);
					if (DISABLED_FILE2.indexOf(select_file2.options[x].value) == -1) {
						DISABLED_FILE2.push(select_file2.options[x].value);
					}
				}else if (DISABLED_FILE2.indexOf(select_file2.options[x].value) > -1 && select_file2.options[x].disabled == false) {
					DISABLED_FILE2.splice(DISABLED_FILE2.indexOf(select_file2.options[x].value), 1);
				}
			}
		}
		NAME_FILE_STORAGE[current_selection] = [R1, R2];
	}
}

//	On Open of fastlane
$(function() {
	"use strict";
	//	Disable directory tab
	if (window.location.href.split("/")[window.location.href.split("/").length - 1] != "process") {
		document.getElementById('Directory_toggle').setAttribute('data-toggle', 'none');
	}
	
	//	Get cluster username for file checks
	$.ajax({
		type: 	'GET',
		url: 	BASE_PATH+'/public/ajax/ngsfastlanedb.php',
		data:  	{ p: 'getClusterName' },
		async:	false,
		success: function(s)
		{
			username = s[0];
		}
	});
});