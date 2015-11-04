
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
			$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/profiledb.php",
				data: { p: 'alterAccessKey', id: access_key_change[x].split("_")[0], a_key: document.getElementById(access_key_change).value},
				async: false,
				success : function(s)
				{
				}
			});
		}
	}
	if (secret_key_change.length > 0) {
		for(var x = 0; x < secret_key_change.length; x++){
			$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/profiledb.php",
				data: { p: 'alterSecretKey', id: secret_key_change[x].split("_")[0], s_key: document.getElementById(secret_key_change).value},
				async: false,
				success : function(s)
				{
				}
			});
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

function obtainKeys(){
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/profiledb.php",
			data: { p: 'obtainAmazonKeys' },
			async: false,
			success : function(s)
			{
				var new_json_array = [];
				for(var i = 0; i < s.length; i++) {
					if (obtainPermissions(s[i].id)) {
						new_json_array.push(
						{"bucket":s[i].bucket,
						"access_key":'<input id="'+s[i].id+'_access" type="textbox" class="input-group col-md-12" value="'+s[i].aws_access_key_id+'" onchange="credentials_change(this.id)">',
						"secret_key":'<input id="'+s[i].id+'_secret" type="textbox" class="input-group col-md-12" value="'+s[i].aws_secret_access_key+'" onchange="credentials_change(this.id)">'});
					}else{
						new_json_array.push(
						{"bucket":s[i].bucket,
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
			testdata = s;
			console.log(s);
			groupsStreamTable = createStreamTable('groups', s, "", true, [20,50], 20, true, true);
		}
	});
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
		document.getElementById('cancelGroupButton').setAttribute('onClick', 'window.location.reload()');
	}
}

function requestJoinGroup(){
	$('#groupModal').modal({
		show: true
	});
	document.getElementById('myModalLabel').innerHTML = 'Request to join a group';
	document.getElementById('groupLabel').innerHTML ='Select a group to join';
	document.getElementById('groupModalDiv').innerHTML = '<select id="joinGroup" class="form-control" size="25" multiple>';
	document.getElementById('confirmGroupButton').setAttribute('onClick', 'submitJoinRequest()');
	document.getElementById('confirmGroupButton').setAttribute('style', 'display:show');
	document.getElementById('confirmGroupButton').setAttribute('data-dismiss', '');
	document.getElementById('cancelGroupButton').setAttribute('onClick', '');
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/profiledb.php",
		data: { p: 'joinGroupList' },
		async: false,
		success : function(s)
		{
			for (var x = 0; x < s.length; x++) {
				document.getElementById('joinGroup').innerHTML += '<option>'+s[x].name+'</option>';
			}
		}
	});
}

function submitJoinRequest(){
	if (document.querySelector("select").selectedOptions.length > 0) {
		//	Add request to pending DB table
		$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/profiledb.php",
		data: { p: 'sendJoinGroupRequest' },
		async: false,
		success : function(s)
		{

		}
	});
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
	//	AMAZON KEYS
	obtainKeys();
});