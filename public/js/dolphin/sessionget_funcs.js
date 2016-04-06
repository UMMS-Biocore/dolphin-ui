/*
 *Author: Nicholas Merowsky
 *Date: 24 Apr 2015
 *Ascription:
 */

function tableToggle(table){
	var type;
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/sessionrequests.php",
		data: { p:"tableToggle", table:table },
		async: false,
		success : function(s)
		{
			type = s;
		}
	});
	return type;
}

function getTableToggle(table){
	var type;
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/sessionrequests.php",
		data: { p:"getTableToggle", table:table },
		async: false,
		success : function(s)
		{
			type = s;
		}
	});
	return type;
}

function sessionTest(){
	var uid = '';
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/sessionrequests.php",
		data: { p:"sessionTest" },
		async: false,
		success : function(s)
		{
			uid = s;
		}
	});
	return uid;
}

function sendBasketInfo(id){
	$.ajax({ type: "POST",
		url: BASE_PATH+"/public/ajax/sessionrequests.php",
		data: { p:"sendBasketInfo", id:id },
		async: false,
		success : function(s)
		{
		}
	});
}

function sendBasketInfoBulk(ids){
	$.ajax({ type: "POST",
		url: BASE_PATH+"/public/ajax/sessionrequests.php",
		data: { p:"sendBasketInfoBulk", ids:ids },
		async: false,
		success : function(s)
		{
		}
	});
}

function getBasketInfo() {
	basket = "";
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/sessionrequests.php",
		data: { p:"getBasketInfo" },
		async: false,
		success : function(s)
		{
			basket = s;
		}
	});
	if (basket == "") {
		return undefined;
	}else{
		return basket;
	}
}

function getAdditionalBasketInfo() {
	basket = "";
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/sessionrequests.php",
		data: { p:"getAdditionalBasketInfo" },
		async: false,
		success : function(s)
		{
			basket = s;
		}
	});
	if (basket == "") {
		return undefined;
	}else{
		return basket;
	}
}

function sendWKey(wkey){
	$.ajax({ type: "POST",
		url: BASE_PATH+"/public/ajax/sessionrequests.php",
		data: { p:"sendWKey", wkey:wkey },
		async: false,
		success : function(s)
		{
		}
	});
}

function getWKey() {
	wkey = "";
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/sessionrequests.php",
		data: { p:"getWKey" },
		async: false,
		success : function(s)
		{
			wkey = s;
		}
	});
	if (wkey == "") {
		return undefined;
	}else{
		return wkey;
	}
}

function removeBasketInfo(id){
	$.ajax({ type: "POST",
		url: BASE_PATH+"/public/ajax/sessionrequests.php",
		data: { p:"removeBasketInfo", id:id },
		async: false,
		success : function(s)
		{
		}
	});
}

function removeBasketInfoBulk(ids){
	$.ajax({ type: "POST",
		url: BASE_PATH+"/public/ajax/sessionrequests.php",
		data: { p:"removeBasketInfoBulk", ids:ids },
		async: false,
		success : function(s)
		{
		}
	});
}

function flushBasketInfo() {
	$.ajax({ type: "POST",
		url: BASE_PATH+"/public/ajax/sessionrequests.php",
		data: { p:"flushBasketInfo"},
		async: false,
		success : function(s)
		{
		}
	});
}

function flushWKey() {
	$.ajax({ type: "POST",
		url: BASE_PATH+"/public/ajax/sessionrequests.php",
		data: { p:"flushWKey"},
		async: false,
		success : function(s)
		{
		}
	});
}