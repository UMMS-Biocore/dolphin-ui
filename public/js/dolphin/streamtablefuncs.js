
function createStreamTable(type, objList, dataURL, perPageOn, perPageOpts, perPageNumber, searchOn, paginationOn){
	console.log(objList);
	//	Obtain objList keys
	var keys = obtainObjectKeys(objList[0]);
	console.log(keys);
	
	var table = document.getElementById('jsontable_'+type);
	
	createStreamScript(keys, type);
	var data = objList, html = $.trim($("#template_"+type).html()), template = Mustache.compile(html);
	
	var view = function(record, index){
		var mergeRecords = '<tr>';
		for(var x = 0; x < keys.length; x++){
			mergeRecords += '<td>';
			mergeRecords += record[keys[x]];
			mergeRecords += '</td>';
		}
		mergeRecords += '</tr>';
		return mergeRecords;
	};
	
	var callbacks = {
		after_add: function(){
			//Only for example: Stop ajax streaming beacause from localfile data size never going to empty.
			if (this.data.length == objList.length){
				this.stopStreaming();
			}
		}
	}
	st = StreamTable('#jsontable_' + type,
	  { view: view,
		data_url: dataURL,
		stream_after: 0.2,
		fetch_data_limit: 100,
		callbacks: callbacks,
		pagination:{
			per_page_select: perPageOn,
			per_page_opts: perPageOpts,
			per_page: perPageNumber,
			span: 5,
			next_text: 'Next',
			prev_text: 'Previous',
			ul_class: type,
		},
	  },
	 data, type);
	
	if (perPageOn) {
		var num_search = document.getElementById('st_num_search');
		num_search.id = 'st_num_search_' + type;
		num_search.setAttribute('class',"st_per_page margin pull-left input-sm");
		
		var newlabel = createElement('label', ['class'], ['margin']);
		newlabel.setAttribute("for",'st_num_search_'+type);
		newlabel.innerHTML = " entries per page";
		document.getElementById('table_div_'+type).insertBefore(newlabel, document.getElementById('jsontable_'+type));
	}
	
	var search = document.getElementById('st_search');
	if (searchOn) {
		search.id = 'st_search_' + type;
		search.setAttribute('class',"st_search margin pull-right");
	}else{
		search.remove();
	}
	
	var pagination = document.getElementById('st_pagination')
	if (paginationOn) {
		pagination.id = 'st_pagination_' + type;
		pagination.setAttribute('class',"st_pagination_"+type+" margin");
		pagination.setAttribute('style',"text-align:right");
	}else{
		pagination.remove();
	}
}

function createStreamScript(keys, type){
	var masterScript = createElement('script', ['id', 'type'], ['template_'+type, 'text/html']);
	var tr = createElement('tr', [], []);
	
	for(var x = 0; x < keys.length; x++){
		var td = createElement('td', [], []);
		td.innerHTML = "{{record."+keys[x]+"}}";
		tr.appendChild(td);
	}
	masterScript.appendChild(tr);
	document.getElementsByTagName('body')[0].appendChild(masterScript);
}

function obtainObjectKeys(obj){
	var keys = [];
	for (var key in obj) {
		if (obj.hasOwnProperty(key)) {
			keys.push(key)
		}
	}
	return keys;
}