
var username;
var R1_REGEX;
var R2_REGEX;

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
		R1_REGEX = document.getElementById('read_1_input').value;
		R2_REGEX = document.getElementById('read_2_input').value;
		
		var r1_search = new RegExp(R1_REGEX);
		console.log(r1_search);
		if (document.getElementById('spaired').value == 'yes') {
			var r2_search = new RegExp(R2_REGEX);
		}else{
			var r2_search = undefined;
		}
		console.log(r2_search);
		var R1 = [];
		var R2 = [];
		//	Grab files from directory
		var file_list = directoryGrab(directory, r1_search, r2_search);;
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
					//	Regex R1
					if (r1_search.test(file_list[x])) {
						console.log(r1_search.test(file_list[x]))
						R1.push(file_list[x]);
					//	Regex R2
					}else if (r2_search.test(file_list[x])) {
						R2.push(file_list[x]);
					}
				//	Single end
				}else{
					//	Regex fastq
					if (r1_search.test(file_list[x])) {
						R1.push(file_list[x]);
					}
				}
			}
			
			//	Add to Selection Boxes
			var namingIndex = [];
			var nameOptions = '';
			var selectOptions = '';
			var file_pass = true;
			console.log(R1);
			console.log(R2);
			for(var x = 0; x < R1.length; x++){
				var name = '';
				if (document.getElementById('spaired').value == 'yes') {
					if (R1.length == R2.length) {
						selectOptions += '<option value="'+R1[x]+','+R2[x]+'">'+R1[x]+','+R2[x]+'</option>';
					}else{
						file_pass = false;
					}
				}else{
					selectOptions += '<option value="'+R1[x]+'">'+R1[x]+'</option>';
				}
			}
			if (file_pass) {
				document.getElementById('file_select').innerHTML = selectOptions;
				//	After successful query
				document.getElementById('Directory_toggle').setAttribute('data-toggle', 'tab');
				$('.nav-tabs a[href="#Directory"]').tab('show')
				var files = $('#jsontable_dir_files').dataTable();
				files.fnClearTable();
			}else{
				$('#errorModal').modal({
					show: true
				});
				document.getElementById('errorLabel').innerHTML = 'Directory contains unequal paired end reads based on user regex.  Please try a new regex.';
				document.getElementById('errorAreas').innerHTML = '';
			}
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

function addSelection(type){
	var current_selection = document.getElementById('file_select').options;
	var regex_string = document.getElementById('regex_add_field').value;
	var regex = new RegExp(regex_string);
	var files = $('#jsontable_dir_files').dataTable();
	var file_string = '';
	for(var x = 0; x < current_selection.length; x++){
		if (type == 'adv') {
			if (regex.test(current_selection[x].value)) {
				file_string += current_selection[x].value + ' | '
				$('#file_select option[value="'+current_selection[x].value+'"]')[0].remove();
				x--;
			}
		}else if(type == 'standard'){
			if (current_selection[x].selected) {
				file_string += current_selection[x].value + ' | '
				$('#file_select option[value="'+current_selection[x].value+'"]')[0].remove();
				x--
			}
		}
	}
	file_string = file_string.substring(0, file_string.length - 3);
	var name = file_string.split(R1_REGEX)[0];
	var input = createElement('input', ['id', 'type', 'class', 'value', 'onChange'], [name, 'text', 'form-control', name, 'updateName(this)'])
	var button_div = createElement('div', ['class'], ['text-center'])
	var remove_button = createElement('button', ['class', 'type', 'onclick'],['btn btn-danger text-center', 'button', 'removeRow(this)']);
	var icon = createElement('i', ['class'],['fa fa-times']);
	remove_button.appendChild(icon);
	button_div.appendChild(remove_button);
	files.fnAddData([
		input.outerHTML,
		file_string,
		button_div.outerHTML
	]);
}

function removeRow(button){
	var files_select = document.getElementById('file_select')
	var files_table = $('#jsontable_dir_files').dataTable();
	var row = $(button).closest('tr');
	var files_used = row.children()[1].innerHTML.split(' | ');
	console.log(files_used);
	for(var x = 0; x < files_used.length; x++){
		files_select.innerHTML += '<option value="'+files_used[x]+'">'+files_used[x]+'</option>'
	}
	files_table.fnDeleteRow(row);
	files_table.fnDraw();
}

function smartSelection(){
	var files_select = document.getElementById('file_select');
	var files = $('#jsontable_dir_files').dataTable();
	while (files_select.options.length != 0) {
		var file_string = '';
		//	use prvious regex to find the values before the pivot
		var regex_string = files_select.options[0].value.split(R1_REGEX)[0];
		var file_regex = new RegExp(regex_string);
		for(var x = 0; x < files_select.options.length; x++){
			if (file_regex.test(files_select.options[x].value)) {
				file_string += files_select.options[x].value + ' | '
				$('#file_select option[value="'+files_select.options[x].value+'"]')[0].remove();
				x--;
			}
		}
		file_string = file_string.substring(0, file_string.length - 3);
		var name = regex_string;
		var input = createElement('input', ['id', 'type', 'class', 'value', 'onChange'], [name, 'text', 'form-control', name, 'updateName(this)'])
		var button_div = createElement('div', ['class'], ['text-center'])
		var remove_button = createElement('button', ['class', 'type', 'onclick'],['btn btn-danger text-center', 'button', 'removeRow(this)']);
		var icon = createElement('i', ['class'],['fa fa-times']);
		remove_button.appendChild(icon);
		button_div.appendChild(remove_button);
		files.fnAddData([
			input.outerHTML,
			file_string,
			button_div.outerHTML
		]);
	}
}

function updateName(input){
	input.id = input.value;
}

//	On Open of fastlane
$(function() {
	"use strict";
	//	Disable directory tab
	if (window.location.href.split("/")[window.location.href.split("/").length - 1] != "process") {
		document.getElementById('Directory_toggle').setAttribute('data-toggle', 'none');
		var files = $('#jsontable_dir_files').dataTable();
		files.fnClearTable();
		document.getElementById('jsontable_dir_files').style.width = '100%';
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
