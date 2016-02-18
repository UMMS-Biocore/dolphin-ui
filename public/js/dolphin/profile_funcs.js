
var current_avatar;
var access_key_change = [];
var secret_key_change = [];

function selectAvatar(id){
	$( '#'+id ).iCheck('check');
}

function updateProfile(){
	var change_check = true;
	var change_value;
	var imgs = document.getElementsByName('avatar_sel');
	for(var x = 0; x < imgs.length; x++){
		console.log(document.getElementById(imgs[x].alt));
		if (imgs[x].src == BASE_PATH + current_avatar && document.getElementById(imgs[x].alt).checked == true) {
			change_check = false;
		}
		if (document.getElementById(imgs[x].alt).checked == true) {
			change_value = document.getElementById(imgs[x].alt).value;
		}
	}

	if (access_key_change.length > 0) {
		for(var x = 0; x < access_key_change.length; x++){
			if (access_key_change[x] != 'new_access_key') {
				$.ajax({ type: "GET",
					url: BASE_PATH+"/public/ajax/profiledb.php",
					data: { p: 'alterAccessKey', id: access_key_change[x].split("_")[0], a_key: document.getElementById(access_key_change[x]).value},
					async: false,
					success : function(s)
					{
					}
				});
			}
		}
	}
	if (secret_key_change.length > 0) {
		for(var x = 0; x < secret_key_change.length; x++){
			if (secret_key_change[x] != 'new_secret_key') {
				$.ajax({ type: "GET",
					url: BASE_PATH+"/public/ajax/profiledb.php",
					data: { p: 'alterSecretKey', id: secret_key_change[x].split("_")[0], s_key: document.getElementById(secret_key_change[x]).value},
					async: false,
					success : function(s)
					{
					}
				});
			}
		}
	}
	
	if (change_check) {
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/profiledb.php",
			data: { p: 'updateProfile', img: change_value},
			async: false,
			success : function(s)
			{
			}
		});
	}
	location.reload();
}

function obtainPermissions(id){
	var verdict = false;
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/profiledb.php",
			data: { p: 'checkAmazonPermissions', a_id: id},
			async: false,
			success : function(s)
			{
				console.log(s);
				if (s.length > 0) {
					verdict = true;
				}
			}
	});
	return verdict;
}

function credentials_change(id){
	if (id.split("_")[1] == 'access' && access_key_change.indexOf(id) == -1) {
		access_key_change.push(id);
	}else if (id.split("_")[1] == 'secret' && secret_key_change.indexOf(id) == -1){
		secret_key_change.push(id);
	}
}

function addAWSButton(){
	$('#awsModal').modal({
		show: true
	});
	document.getElementById('myAWSModalLabel').innerHTML = 'Groups you have Permissions to add keys to:';
	document.getElementById('awsModalDiv').innerHTML = '<label id="awsLabelGroup">Group:</label>';
	document.getElementById('awsModalDiv').innerHTML += '<select id="viewAWS" class="form-control">';
	document.getElementById('awsModalDiv').innerHTML += '<label id="awsLabelAccess">Access Key:</label>';
	document.getElementById('awsModalDiv').innerHTML += '<input id="new_access_key" class="form-control">';
	document.getElementById('awsModalDiv').innerHTML += '<label id="awsLabelSecret">Secret Key:</label>';
	document.getElementById('awsModalDiv').innerHTML += '<input id="new_secret_key" class="form-control">';
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/profiledb.php",
		data: { p: 'viewAmazonGroupCreation' },
		async: false,
		success : function(s)
		{
			console.log(s);
			for (var x = 0; x < s.length; x++) {
				document.getElementById('viewAWS').innerHTML += '<option value="'+s[x].id+'">'+s[x].name+'</option>';
			}
		}
	});
}

function awsButton(){
	var confirmPassed;
	var group = document.getElementById('viewAWS').value;
	if (group > 0) {
		var a_key = document.getElementById('new_access_key').value;
		var s_key = document.getElementById('new_secret_key').value;
		console.log(a_key);
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/profiledb.php",
			data: { p: 'createAWSKeys', group: group, a_key: a_key, s_key: s_key },
			async: false,
			success : function(s)
			{
				console.log(s);
				confirmPassed = s;
			}
		});
		if (confirmPassed == 0) {
			document.getElementById('submitLabel').innerHTML ='New AWS keys have been submitted for that group!';
		}else{
			document.getElementById('submitLabel').innerHTML ='An error has occured, please try to add keys again.';
		}
	}else{
		document.getElementById('submitLabel').innerHTML ='You are not the owner of any groups.';
	}
	$('#submitModal').modal({
		show: true
	});
}

function obtainKeys(){
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/profiledb.php",
			data: { p: 'obtainAmazonKeys' },
			async: false,
			success : function(s)
			{
				var new_json_array = [];
				for(var i = 0; i < s.length; i++ ){
					if (obtainPermissions(s[i].id)) {
						new_json_array.push(
						{"group":s[i].name,
						"access_key":'<input id="'+s[i].id+'_access" type="textbox" class="input-group col-md-12" value="'+s[i].aws_access_key_id+'" onchange="credentials_change(this.id)">',
						"secret_key":'<input id="'+s[i].id+'_secret" type="textbox" class="input-group col-md-12" value="'+s[i].aws_secret_access_key+'" onchange="credentials_change(this.id)">'});
					}else{
						new_json_array.push(
						{"group":s[i].name,
						"access_key":'<input type="textbox" class="input-group col-md-12" value="'+Array(17).join('*') + s[i].aws_access_key_id.substring(16, 20)+'" disabled>',
						"secret_key":'<input type="textbox" class="input-group col-md-12" value="'+Array(37).join('*') + s[i].aws_secret_access_key.substring(36,40)+'" disabled>'});
					}
				}
				createStreamTable('amazon', new_json_array, "", true, [20,50], 20, true, true);
			}
	});
}

function obtainGroups(){
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/profiledb.php",
		data: { p: 'obtainGroups' },
		async: false,
		success : function(s)
		{
			var new_json_array = [];
			var uid = s[0].u_id;
			for(var i = 0; i < s.length; i++ ){
				/*
				new_json_array.push(
					{
						"id":s[i].id,
						"name":s[i].name,
						"date_created":s[i].date_created
					}
				)
				*/
				s[i].options = '<div class="btn-group pull-right">' +
				'<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Options ' +
				'<span class="fa fa-caret-down"></span>' +
				'</button>' +
				'<ul class="dropdown-menu" role="menu">' +
				'<li><a href="#" onclick="viewGroupMembers(\''+s[i].id+'\')">View Group Members</a></li>';
				if (uid == s[i].owner_id) {
					s[i].options += '<li class="divider"></li>' +
					'<li><a href="#" onclick="addUsers(\''+s[i].id+'\')">Add Users</a></li>' +
					'<li><a href="#" onclick="addNewUsers(\''+s[i].id+'\')">Add Pending Users</a></li>' +
					'<li><a href="#" onclick="transferOwner(\''+s[i].id+'\')">Change group owner</a></li>' +
					'<li class="divider"></li>' +
					'<li><a href="#" onclick="deleteGroup(\''+s[i].id+'\')">Delete Group</a></li></ul>' +
					'</div>';
				}else{
					s[i].options += '</div>';
				}
				delete s[i].u_id;
				delete s[i].owner_id;
			}
			groupsStreamTable = createStreamTable('groups', s, "", true, [20,50], 20, true, true);
		}
	});
}

function viewGroupMembers(group){
	$('#groupModal').modal({
		show: true
	});
	document.getElementById('myModalLabel').innerHTML = 'User list for Selected Group';
	document.getElementById('groupLabel').innerHTML ='';
	document.getElementById('groupModalDiv').innerHTML = '<select id="viewGroup" class="form-control" size="25" multiple>';
	document.getElementById('confirmGroupButton').setAttribute('onClick', '');
	document.getElementById('confirmGroupButton').setAttribute('style', 'display:none');
	document.getElementById('cancelGroupButton').setAttribute('onClick', '');
	document.getElementById('cancelGroupButton').innerHTML = 'OK';
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/profiledb.php",
		data: { p: 'viewGroupMembers', group: group },
		async: false,
		success : function(s)
		{
			console.log(s);
			for (var x = 0; x < s.length; x++) {
				document.getElementById('viewGroup').innerHTML += '<option>'+s[x].username+'</option>';
			}
		}
	});
}

function addUsers(id){
	$('#groupModal').modal({
		show: true
	});
	document.getElementById('myModalLabel').innerHTML = 'List of All Users';
	document.getElementById('groupLabel').innerHTML ='Select a user to add to this group';
	document.getElementById('groupModalDiv').innerHTML = '<select id="addGroup" class="form-control" size="25" multiple>';
	document.getElementById('confirmGroupButton').setAttribute('onClick', 'confirmAddUser('+id+')');
	document.getElementById('confirmGroupButton').setAttribute('data-dismiss', '');
	document.getElementById('confirmGroupButton').innerHTML = 'Add to group';
	document.getElementById('confirmGroupButton').setAttribute('style', 'display:show');
	document.getElementById('cancelGroupButton').innerHTML = 'Cancel';
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/profiledb.php",
		data: { p: 'getMemberAdd', group_id: id },
		async: false,
		success : function(s)
		{
			for (var x = 0; x < s.length; x++) {
				document.getElementById('addGroup').innerHTML += '<option value="'+s[x].id+'">'+s[x].username+'</option>';
			}
		}
	});
}

function transferOwner(id){
	$('#groupModal').modal({
		show: true
	});
	document.getElementById('myModalLabel').innerHTML = 'Transfer group ownership?';
	document.getElementById('groupLabel').innerHTML ='Select a user to be the owner';
	document.getElementById('groupModalDiv').innerHTML = '<select id="addGroup" class="form-control" size="25" multiple></select>';
	document.getElementById('confirmGroupButton').setAttribute('onClick', 'confirmOwnerTransfer('+id+')');
	document.getElementById('confirmGroupButton').setAttribute('data-dismiss', '');
	document.getElementById('confirmGroupButton').innerHTML = 'Make Owner';
	document.getElementById('confirmGroupButton').setAttribute('style', 'display:show');
	document.getElementById('cancelGroupButton').innerHTML = 'Cancel';
	
	var uid;
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/profiledb.php",
		data: { p: 'getUID' },
		async: false,
		success : function(s)
		{
			uid = s;
		}
	});
	console.log(uid);
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/profiledb.php",
		data: { p: 'viewGroupMembers', group: id },
		async: false,
		success : function(s)
		{
			console.log(s);
			for (var x = 0; x < s.length; x++) {
				if (s[x].id != uid) {
					document.getElementById('addGroup').innerHTML += '<option value="'+s[x].id+'">'+s[x].username+'</option>';
				}
			}
		}
	});
}

function confirmOwnerTransfer(id){
	if (document.querySelector("select").selectedOptions.length > 0) {
		var result = 0;
		//      Add request to pending DB table
		$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/profiledb.php",
				data: { p: 'transferOwner', group_id: id, user_id: document.querySelector("select").selectedOptions[0].value },
				async: false,
				success : function(s)
				{
					result = s;
				}
		});
		document.getElementById('groupModalDiv').innerHTML = '';
		document.getElementById('confirmGroupButton').setAttribute('onClick', '');
		document.getElementById('confirmGroupButton').setAttribute('style', 'display:none');
		document.getElementById('cancelGroupButton').innerHTML = 'OK';
		if (result == 'pass') {
			document.getElementById('groupLabel').innerHTML ='User is now the group owner!';
			document.getElementById('cancelGroupButton').setAttribute('onClick', 'window.location.reload()');
		}else{
			document.getElementById('groupLabel').innerHTML ='Request did not process, please try again.';
		}
	}
}

function addNewUsers(id){
	$('#groupModal').modal({
		show: true
	});
	document.getElementById('myModalLabel').innerHTML = 'Users that want group access';
	document.getElementById('groupLabel').innerHTML ='Select a user to add to this group';
	document.getElementById('groupModalDiv').innerHTML = '<select id="addGroup" class="form-control" size="25" multiple>';
	document.getElementById('confirmGroupButton').setAttribute('onClick', 'confirmAddUser('+id+')');
	document.getElementById('confirmGroupButton').setAttribute('data-dismiss', '');
	document.getElementById('confirmGroupButton').innerHTML = 'Add to group';
	document.getElementById('confirmGroupButton').setAttribute('style', 'display:show');
	document.getElementById('cancelGroupButton').innerHTML = 'Cancel';
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/profiledb.php",
		data: { p: 'getGroupMemberAdd', group_id: id },
		async: false,
		success : function(s)
		{
			for (var x = 0; x < s.length; x++) {
				document.getElementById('addGroup').innerHTML += '<option value="'+s[x].id+'">'+s[x].username+'</option>';
			}
		}
	});
}

function confirmAddUser(id) {
	if (document.querySelector("select").selectedOptions.length > 0) {
		var result = 0;
		//	Add request to pending DB table
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/profiledb.php",
			data: { p: 'addGroupMember', group_id: id, user_id: document.querySelector("select").selectedOptions[0].value },
			async: false,
			success : function(s)
			{
				result = s;
			}
		});
		document.getElementById('groupModalDiv').innerHTML = '';
		document.getElementById('confirmGroupButton').setAttribute('onClick', '');
		document.getElementById('confirmGroupButton').setAttribute('style', 'display:none');
		document.getElementById('cancelGroupButton').innerHTML = 'OK';
		if (result == 0) {
			document.getElementById('groupLabel').innerHTML ='Request did not process, please try again.';
		}else{
			document.getElementById('groupLabel').innerHTML ='User has been added to your group!';
		}
	}
}

function deleteGroup(id){
	$('#groupModal').modal({
		show: true
	});
	document.getElementById('myModalLabel').innerHTML = 'Delete group';
	document.getElementById('groupLabel').innerHTML ='Are you sure you want to delete group id: '+id;
	document.getElementById('groupModalDiv').innerHTML = '';
	document.getElementById('confirmGroupButton').innerHTML = 'Delete';
	document.getElementById('confirmGroupButton').setAttribute('onClick', 'confirmDeleteGroup('+id+')');
	document.getElementById('confirmGroupButton').setAttribute('data-dismiss', '');
	document.getElementById('confirmGroupButton').setAttribute('style', 'display:show');
	document.getElementById('cancelGroupButton').innerHTML = 'Cancel';
}

function confirmDeleteGroup(id) {
	var confirmDelete;
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/profiledb.php",
		data: { p: 'deleteGroup', group_id: id },
		async: false,
		success : function(s)
		{
			console.log(s);
			confirmDelete = s;
		}
	});
	document.getElementById('myModalLabel').innerHTML = 'Delete group';
	if (confirmDelete == 'pass') {
		document.getElementById('groupLabel').innerHTML ='This group has been deleted';
	}else{
		document.getElementById('groupLabel').innerHTML ='Error occured, this group has not been deleted.';
	}
	document.getElementById('groupModalDiv').innerHTML = '';
	document.getElementById('confirmGroupButton').setAttribute('style', 'display:none');
	document.getElementById('cancelGroupButton').innerHTML = 'OK';
	
	document.getElementById('st_search_groups').remove();
	document.getElementById('st_label_groups').remove();
	document.getElementById('st_num_search_groups').remove();
	document.getElementById('st_pagination_groups').remove();
	obtainGroups();
}

function obtainProfileInfo(){
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/profiledb.php",
		data: { p: 'obtainProfileInfo' },
		async: false,
		success : function(s)
		{
			var modified_json = [];
			for(var key in s[0]){
				if (key != 'owner_id' && key != 'group_id' && key !='photo_loc' && key != 'last_modified_user' && key != 'perms') {
					modified_json.push({'id':key,'value':s[0][key]});
				}
			}
			createStreamTable('user_profile', modified_json, "", false, [20], 20, false, false);
		}
	});
}

function obtainEmail(){
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/profiledb.php",
		data: { p: 'obtainEmail' },
		async: false,
		success : function(s)
		{
			document.getElementById('email_address').value = s[0].email
			document.getElementById('email_check').value = s[0].email_toggle;
		}
	});
}

function updateEmail(){
	var email = document.getElementById('email_address').value
	var email_toggle = document.getElementById('email_check').value
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/profiledb.php",
		data: { p: 'changeEmail', email: email, email_toggle: email_toggle },
		async: false,
		success : function(s)
		{
			window.location.reload();
		}
	});
}

function requestNewGroup(){
	$('#groupModal').modal({
		show: true
	});
	document.getElementById('myModalLabel').innerHTML = 'Request for a New Group';
	document.getElementById('groupLabel').innerHTML ='Name the group you wish to create:';
	
	document.getElementById('groupModalDiv').innerHTML = '<input id="groupNew" class="form-control" type="text" placeholder="Group Name">';
	document.getElementById('confirmGroupButton').innerHTML = 'Submit';
	document.getElementById('confirmGroupButton').setAttribute('data-dismiss', '');
	document.getElementById('confirmGroupButton').setAttribute('onClick', 'confirmNewGroup()');
	document.getElementById('confirmGroupButton').setAttribute('style', 'display:show');
	document.getElementById('cancelGroupButton').innerHTML = 'Cancel';
}

function confirmNewGroup() {
	document.getElementById('groupLabel').innerHTML = '';
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/profiledb.php",
		data: { p: 'newGroupProcess', newGroup: document.getElementById('groupNew').value },
		async: false,
		success : function(s)
		{
			document.getElementById('groupLabel').innerHTML = s;
		}
	});
	document.getElementById('groupModalDiv').innerHTML = '';
	document.getElementById('confirmGroupButton').setAttribute('onClick', '');
	document.getElementById('confirmGroupButton').setAttribute('style', 'display:none');
	document.getElementById('cancelGroupButton').innerHTML = 'OK';
	if (document.getElementById('groupLabel').innerHTML == 'Your group has been created') {
		document.getElementById('st_search_groups').remove();
		document.getElementById('st_label_groups').remove();
		document.getElementById('st_num_search_groups').remove();
		document.getElementById('st_pagination_groups').remove();
		obtainGroups();
	}
}

function requestJoinGroup(){
	$('#groupModal').modal({
		show: true
	});
	document.getElementById('myModalLabel').innerHTML = 'Request to join a group';
	document.getElementById('groupLabel').innerHTML ='Select a group to join';
	document.getElementById('groupModalDiv').innerHTML = '<select id="joinGroup" class="form-control" size="25" multiple>';
	document.getElementById('confirmGroupButton').innerHTML = 'Request';
	document.getElementById('confirmGroupButton').setAttribute('onClick', 'submitJoinRequest()');
	document.getElementById('confirmGroupButton').setAttribute('style', 'display:show');
	document.getElementById('confirmGroupButton').setAttribute('data-dismiss', '');
	document.getElementById('cancelGroupButton').setAttribute('onClick', '');
	document.getElementById('cancelGroupButton').innerHTML = 'Cancel';
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/profiledb.php",
		data: { p: 'joinGroupList' },
		async: false,
		success : function(s)
		{
			console.log(s);
			for (var x = 0; x < s.length; x++) {
				document.getElementById('joinGroup').innerHTML += '<option value="'+s[x].id+'">'+s[x].name+'</option>';
			}
		}
	});
}

function submitJoinRequest(){
	if (document.querySelector("select").selectedOptions.length > 0) {
		var result = 0;
		//	Add request to pending DB table
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/profiledb.php",
			data: { p: 'sendJoinGroupRequest', group_id: document.querySelector("select").selectedOptions[0].value },
			async: false,
			success : function(s)
			{
				result = s;
			}
		});
		document.getElementById('groupModalDiv').innerHTML = '';
		document.getElementById('confirmGroupButton').setAttribute('onClick', '');
		document.getElementById('confirmGroupButton').setAttribute('style', 'display:none');
		document.getElementById('cancelGroupButton').innerHTML = 'OK';
		if (result == 0) {
			document.getElementById('groupLabel').innerHTML ='Request did not process, please try again.';
		}else{
			document.getElementById('groupLabel').innerHTML ='Request to join group has been sent to its owner!';
		}
	}
}

$(function() {
	"use strict";
	
	//	PROFILE AVATAR
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/profiledb.php",
			data: { p: 'profileLoad' },
			async: false,
			success : function(s)
			{
				var imgs = document.getElementsByName('avatar_sel');
				current_avatar = s[0].photo_loc;
				for(var x = 0; x < imgs.length; x++){
					if (imgs[x].src == BASE_PATH + current_avatar) {
						imgs[x].click();
					}
				}
			}
	});
	//	PROFILE
	obtainProfileInfo();
	//	GROUPS
	obtainGroups();
	//	EMAIL
	obtainEmail();
	//	AMAZON KEYS
	obtainKeys();
});