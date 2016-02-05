/*
 * Author: Alper Kucukural
 * Co-Editor: Nicholas Merowsky
 * Date: 26 Nov 2014
 * Ascription:
 **/

var basket_info = getBasketInfo();
var table_params = '';

function generateStreamTable(type, queryData, queryType, qvar, rvar, seg, theSearch, uid, gids, basket_info){
	var keys = [];
	var obj_conversion = [];
	if (type == 'generated') {
		keys = queryData.title;
		delete queryData.title;
		var new_header = '<tr>';
		var picard_summary_check = table_params.parameters.indexOf('picard.alignment_summary');
		console.log(picard_summary_check);
		console.log(queryData);
		console.log(keys);
		if (picard_summary_check > -1 && queryData['CATEGORY'] != undefined) {
			delete queryData['CATEGORY'];
		}
		for (var y = 0; y < keys.length; y++) {
			if (table_params.parameters.indexOf('.summary.') > -1) {
				if (!queryData[Object.keys(queryData)[0]][keys[y]].match(/[^$,.\d]/)) {
					new_header += '<th data-sort="'+keys[y].replace(/ /g,"_").replace(/>/g,"_")+'::number" onclick="shiftColumns(this)">'+
								keys[y]+'<i id="'+y+'" class="pull-right fa fa-unsorted"></i></th>';
					keys[y] = keys[y].replace(/ /g,"_").replace(/>/g,"_");
				}else{
					new_header += '<th data-sort="'+keys[y].replace(/ /g,"_").replace(/>/g,"_")+'::string" onclick="shiftColumns(this)">'+
								keys[y]+'<i id="'+y+'" class="pull-right fa fa-unsorted"></i></th>';
					keys[y] = keys[y].replace(/ /g,"_").replace(/>/g,"_");
				}
			}else{
				if (!queryData[Object.keys(queryData)[0]][y].match(/[^$,.\d]/)) {
					new_header += '<th data-sort="'+keys[y]+'::number" onclick="shiftColumns(this)">'+
								keys[y]+'<i id="'+y+'" class="pull-right fa fa-unsorted"></i></th>';
					keys[y] = keys[y].replace(/ /g,"_").replace(/>/g,"_");
				}else{
					new_header += '<th data-sort="'+keys[y]+'::string" onclick="shiftColumns(this)">'+
								keys[y]+'<i id="'+y+'" class="pull-right fa fa-unsorted"></i></th>';
					keys[y] = keys[y].replace(/ /g,"_").replace(/>/g,"_");
				}
			}
		}
		for(var key in queryData){
			if (Array.isArray(queryData[key])) {
				if (picard_summary_check > -1 && key == 'CATEGORY') {
					
				}else{
					obj = {};
					for(var title in keys){
						obj[keys[title]] = queryData[key][title];
					}
					obj_conversion.push(obj);
				}
			}else{
				obj = {};
				for (var title in queryData[key]) {
					obj[title.replace(/ /g,"_").replace(/>/g,"_")] = queryData[key][title];
				}
				obj_conversion.push(obj);
			}
		}
		queryData = obj_conversion;
		new_header += '</tr>';
		$('#jsontable_generated')[0].tHead.innerHTML = new_header; 
	}else{
		for (var key in queryData[0]) {
			if (queryData[0].hasOwnProperty(key)) {
				keys.push(key)
			}
		}
	}
	var masterScript = createElement('script', ['id', 'type'], ['template_'+type, 'text/html']);
	var tr = createElement('tr', [], []);
	
	for(var x = 0; x < keys.length; x++){
		var td = createElement('td', [], []);
		td.innerHTML = "{{record."+keys[x]+"}}";
		tr.appendChild(td);
	}
	masterScript.appendChild(tr);
	document.getElementsByTagName('body')[0].appendChild(masterScript);
	
	var lanes_with_good_samples;
	var owner_ids = [];
	if (type == 'lanes') {
		$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/ngsquerydb.php",
				data: { p: 'getLanesWithSamples' },
				async: false,
				success : function(s)
				{
					lanes_with_good_samples = s;
				}
		});
	}
	
	var tableToggle = getTableToggle(type);
	
	var data = queryData, html = $.trim($("#template_"+type).html()), template = Mustache.compile(html);
	var view = function(record, index){
		//	Samples
		if (record.source == null) {
			record.source = '';
		}
		if (record.organism == null) {
			record.organism = '';
		}
		if (record.molecule == null) {
			record.molecule = '';
		}
		if (record.genotype == null) {
			record.genotype = '';
		}
		if (record.library_type == null) {
			record.library_type = '';
		}
		if (record.biosample_type == null) {
			record.biosample_type = '';
		}
		if (record.instrument_model == null) {
			record.instrument_model = '';
		}
		if (record.treatment_manufacturer == null) {
			record.treatment_manufacturer = '';
		}
		if (record.barcode == null){
			record.barcode = '';
		}
		if (record.description == null){
			record.description = '';
		}
		if (record.avg_insert_size == null){
			record.avg_insert_size = '';
		}
		if (record.read_length == null){
			record.read_length = '';
		}
		if (record.concentration == null){
			record.concentration = '';
		}
		if (record.time == null){
			record.time = '';
		}
		if (record.biological_replica == null){
			record.biological_replica = '';
		}
		if (record.technical_replica == null){
			record.technical_replica = '';
		}
		if (record.spike_ins == null){
			record.spike_ins = '';
		}
		if (record.adapter == null){
			record.adapter = '';
		}
		if (record.notebook_ref == null){
			record.notebook_ref = '';
		}
		
		//	Lanes
		if (record.facility == null) {
			record.facility = '';
		}
		if (record.total_reads == null) {
			record.total_reads = '';
		}
		if (record.total_samples == null) {
			record.total_samples = '';
		}
		if (record.cost == null){
			record.cost = '';
		}
		if (record.phix_requested == null){
			record.phix_requested = '';
		}
		if (record.phix_in_lane == null){
			record.phix_in_lane = '';
		}
		
		//	Experiment Series
		if (record.design == null) {
			record.design = '';
		}
		if (record.lab == null) {
			record.lab = '';
		}
		if (record.organization == null) {
			record.organization = '';
		}
		if (record.grant == null) {
			record.grant = '';
		}

		//	Multiple
		if (record.notes == null){
			record.notes = '';
		}
		
		//	Generated
		for(var x = 0; x < keys.length; x++){
			if (record[keys[x]] == undefined) {
				record[keys[x]] = '';
			}
		}
		
		var initialRunWarning = "<button id=\"perms_"+record.id+"\" class=\"btn btn-default btn-xs\" value=\"initialRunWarning\" onclick=\"initialRunButton('"+type+"', "+record.id+", this)\"><span class=\"fa fa-warning\"></span></button>";
		var sample_name = '';
		
		if (type == 'lanes') {
			for(var y = 0; y < lanes_with_good_samples.length; y++){
				if (lanes_with_good_samples[y].id == record.id) {
					initialRunWarning = '';
				}
			}
		}
		if (record.total_reads != '' && type == 'samples'){
			initialRunWarning = '';
		}
		if (record.samplename == 'null' || record.samplename == 'NULL' || record.samplename == '' || record.samplename == null || record.samplename == undefined) {
			sample_name = record.name;
		}else{
			sample_name = record.samplename;
		}
		var basketSamples = [];
		if (basket_info != undefined) {
			basketSamples = basket_info.split(",");
		}
		var checked = '';
		if (queryType == 'table_create') {
			if (basketSamples.indexOf(record.id) > -1) {
				checked = 'checked';
			}
		}
		
		if (type == 'generated') {
			var row = '<tr>';
			for(var key in keys){
				row += '<td>'+record[keys[key]]+'</td>';
			}
			return row + '</tr>';
		}else if (tableToggle == 'extend') {
			if (type == 'samples') {
				if (queryType == 'getSamples') {
					return "<tr><td>"+record.id+"</td><td>"+"<a href=\""+BASE_PATH+"/search/details/samples/"+record.id+'/'+theSearch+"\">"+sample_name+"</a>"+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'title', 'ngs_samples', this)\">"+record.title+
						"</td><td onclick=\"editBox("+uid+", "+record.id+", 'source', 'ngs_samples', this)\">"+record.source+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'organism', 'ngs_samples', this)\">"+record.organism+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'molecule', 'ngs_samples', this)\">"+record.molecule+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'barcode', 'ngs_samples', this)\">"+record.barcode+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'description', 'ngs_samples', this)\">"+record.description+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'avg_insert_size', 'ngs_samples', this)\">"+record.avg_insert_size+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'read_length', 'ngs_samples', this)\">"+record.read_length+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'concentration', 'ngs_samples', this)\">"+record.concentration+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'time', 'ngs_samples', this)\">"+record.time+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'biological_replica', 'ngs_samples', this)\">"+record.biological_replica+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'technical_replica', 'ngs_samples', this)\">"+record.technical_replica+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'spike_ins', 'ngs_samples', this)\">"+record.spike_ins+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'adapter', 'ngs_samples', this)\">"+record.adapter+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'notebook_ref', 'ngs_samples', this)\">"+record.notebook_ref+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'notes', 'ngs_samples', this)\">"+record.notes+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'genotype', 'ngs_samples', this)\">"+record.genotype+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'library_type', 'ngs_samples', this)\">"+record.library_type+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'biosample_type', 'ngs_samples', this)\">"+record.biosample_type+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'instrument_model', 'ngs_samples', this)\">"+record.instrument_model+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'treatment_manufacturer', 'ngs_samples', this)\">"+record.treatment_manufacturer+"</td>"+
						"<td>"+initialRunWarning+"<input type=\"checkbox\" class=\"ngs_checkbox\" name=\""+record.id+"\" id=\"sample_checkbox_"+record.id+"\" onClick=\"manageChecklists(this.name, 'sample_checkbox')\">"+"</td></tr>";
				}else if (queryType == 'table_create') {
					return "<tr><td>"+record.id+"</td><td>"+"<a href=\""+BASE_PATH+"/search/details/samples/"+record.id+'/'+theSearch+"\">"+sample_name+"</a>"+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'title', 'ngs_samples', this)\">"+record.title+
						"</td><td onclick=\"editBox("+uid+", "+record.id+", 'source', 'ngs_samples', this)\">"+record.source+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'organism', 'ngs_samples', this)\">"+record.organism+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'molecule', 'ngs_samples', this)\">"+record.molecule+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'barcode', 'ngs_samples', this)\">"+record.barcode+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'description', 'ngs_samples', this)\">"+record.description+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'avg_insert_size', 'ngs_samples', this)\">"+record.avg_insert_size+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'read_length', 'ngs_samples', this)\">"+record.read_length+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'concentration', 'ngs_samples', this)\">"+record.concentration+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'time', 'ngs_samples', this)\">"+record.time+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'biological_replica', 'ngs_samples', this)\">"+record.biological_replica+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'technical_replica', 'ngs_samples', this)\">"+record.technical_replica+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'spike_ins', 'ngs_samples', this)\">"+record.spike_ins+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'adapter', 'ngs_samples', this)\">"+record.adapter+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'notebook_ref', 'ngs_samples', this)\">"+record.notebook_ref+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'notes', 'ngs_samples', this)\">"+record.notes+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'genotype', 'ngs_samples', this)\">"+record.genotype+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'library_type', 'ngs_samples', this)\">"+record.library_type+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'biosample_type', 'ngs_samples', this)\">"+record.biosample_type+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'instrument_model', 'ngs_samples', this)\">"+record.instrument_model+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'treatment_manufacturer', 'ngs_samples', this)\">"+record.treatment_manufacturer+"</td>"+
						"<td>"+initialRunWarning+"<input type=\"checkbox\" class=\"ngs_checkbox\" name=\""+record.id+"\" id=\"sample_checkbox_"+record.id+"\" onClick=\"manageCreateChecklists(this.name, this)\" "+ checked + ">"+"</td></tr>";
				}else{
					return "<tr><td>"+record.id+"</td><td>"+"<a href=\""+BASE_PATH+"/search/details/samples/"+record.id+'/'+theSearch+"\">"+sample_name+"</a>"+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'title', 'ngs_samples', this)\">"+record.title+
						"</td><td onclick=\"editBox("+uid+", "+record.id+", 'source', 'ngs_samples', this)\">"+record.source+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'organism', 'ngs_samples', this)\">"+record.organism+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'molecule', 'ngs_samples', this)\">"+record.molecule+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'barcode', 'ngs_samples', this)\">"+record.barcode+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'description', 'ngs_samples', this)\">"+record.description+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'avg_insert_size', 'ngs_samples', this)\">"+record.avg_insert_size+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'read_length', 'ngs_samples', this)\">"+record.read_length+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'concentration', 'ngs_samples', this)\">"+record.concentration+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'time', 'ngs_samples', this)\">"+record.time+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'biological_replica', 'ngs_samples', this)\">"+record.biological_replica+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'technical_replica', 'ngs_samples', this)\">"+record.technical_replica+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'spike_ins', 'ngs_samples', this)\">"+record.spike_ins+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'adapter', 'ngs_samples', this)\">"+record.adapter+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'notebook_ref', 'ngs_samples', this)\">"+record.notebook_ref+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'notes', 'ngs_samples', this)\">"+record.notes+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'genotype', 'ngs_samples', this)\">"+record.genotype+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'library_type', 'ngs_samples', this)\">"+record.library_type+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'biosample_type', 'ngs_samples', this)\">"+record.biosample_type+"</td>"+
						"<td onclick=\"editBox("+uid+", "+record.id+", 'instrument_model', 'ngs_samples', this)\">"+record.instrument_model+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'treatment_manufacturer', 'ngs_samples', this)\">"+
						+ record.treatment_manufacturer+"</td></tr>"
				}
			}else if (type == 'lanes') {
				return "<tr><td>"+record.id+"</td><td>"+"<a href=\""+BASE_PATH+"/search/details/experiments/"+record.id+'/'+theSearch+"\">"+record.name+"</a>"+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'facility', 'ngs_lanes', this)\">"+record.facility+
					"</td><td onclick=\"editBox("+uid+", "+record.id+", 'total_reads', 'ngs_lanes', this)\">"+record.total_reads+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'total_samples', 'ngs_lanes', this)\">"+record.total_samples+"</td>"+
					"<td onclick=\"editBox("+uid+", "+record.id+", 'cost', 'ngs_lanes', this)\">"+record.cost+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'phix_requested', 'ngs_lanes', this)\">"+record.phix_requested+"</td>"+
					"<td onclick=\"editBox("+uid+", "+record.id+", 'phix_in_lane', 'ngs_lanes', this)\">"+record.phix_in_lane+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'notes', 'ngs_lanes', this)\">"+record.notes+"</td>"+
					"<td>"+initialRunWarning+"<input type=\"checkbox\" class=\"ngs_checkbox\" name=\""+record.id+"\" id=\"lane_checkbox_"+record.id+"\" onClick=\"manageChecklists(this.name, 'lane_checkbox')\">"+"</td></tr>";
					
			}else if(type == 'experiments'){
				return "<tr><td>"+record.id+"</td><td>"+"<a href=\""+BASE_PATH+"/search/details/experiment_series/"+record.id+'/'+theSearch+"\">"+record.experiment_name+"</a>"+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'summary', 'ngs_experiment_series', this)\">"+record.summary+
					"</td><td onclick=\"editBox("+uid+", "+record.id+", 'design', 'ngs_experiment_series', this)\">"+record.design+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'lab', 'ngs_experiment_series', this)\">"+record.lab+"</td>"+
					"<td onclick=\"editBox("+uid+", "+record.id+", 'organization', 'ngs_experiment_series', this)\">"+record.organization+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'grant', 'ngs_experiment_series', this)\">"+record.grant+"</td>"+
					"<td><input type=\"checkbox\" class=\"ngs_checkbox\" name=\""+record.id+"\" id=\"experiment_checkbox_"+record.id+"\" onClick=\"manageChecklists(this.name, 'experiment_checkbox')\">"+"</td><tr>";
			}else{
				return null;
			}
		}else{
			if (type == 'samples') {
				if (queryType == 'getSamples') {
					return "<tr><td>"+record.id+"</td><td>"+"<a href=\""+BASE_PATH+"/search/details/samples/"+record.id+'/'+theSearch+"\">"+sample_name+"</a>"+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'title', 'ngs_samples', this)\">"+record.title+
						"</td><td onclick=\"editBox("+uid+", "+record.id+", 'source', 'ngs_samples', this)\">"+record.source+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'organism', 'ngs_samples', this)\">"+record.organism+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'molecule', 'ngs_samples', this)\">"+record.molecule+"</td><td>"+
						""+initialRunWarning+"<input type=\"checkbox\" class=\"ngs_checkbox\" name=\""+record.id+"\" id=\"sample_checkbox_"+record.id+"\" onClick=\"manageChecklists(this.name, 'sample_checkbox')\">"+"</td></tr>";
				}else if (queryType == 'table_create') {
					return "<tr><td>"+record.id+"</td><td>"+"<a href=\""+BASE_PATH+"/search/details/samples/"+record.id+'/'+theSearch+"\">"+sample_name+"</a>"+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'title', 'ngs_samples', this)\">"+record.title+
						"</td><td onclick=\"editBox("+uid+", "+record.id+", 'source', 'ngs_samples', this)\">"+record.source+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'organism', 'ngs_samples', this)\">"+record.organism+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'molecule', 'ngs_samples', this)\">"+record.molecule+"</td><td>"+
						""+initialRunWarning+"<input type=\"checkbox\" class=\"ngs_checkbox\" name=\""+record.id+"\" id=\"sample_checkbox_"+record.id+"\" onClick=\"manageCreateChecklists(this.name, this)\" " + checked + ">"+"</td></tr>";
				}else{
					return "<tr><td>"+record.id+"</td><td>"+"<a href=\""+BASE_PATH+"/search/details/samples/"+record.id+'/'+theSearch+"\">"+sample_name+"</a>"+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'title', 'ngs_samples', this)\">"+record.title+
					"</td><td onclick=\"editBox("+uid+", "+record.id+", 'source', 'ngs_samples', this)\">"+record.source+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'organism', 'ngs_samples', this)\">"+record.organism+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'molecule', 'ngs_samples', this)\">"+record.molecule+"</td><tr>";
				}
			}else if (type == 'lanes') {
				return "<tr><td>"+record.id+"</td><td>"+"<a href=\""+BASE_PATH+"/search/details/experiments/"+record.id+'/'+theSearch+"\">"+record.name+"</a>"+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'facility', 'ngs_lanes', this)\">"+record.facility+
					"</td><td onclick=\"editBox("+uid+", "+record.id+", 'total_reads', 'ngs_lanes', this)\">"+record.total_reads+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'total_samples', 'ngs_lanes', this)\">"+record.total_samples+"</td><td>"+
					""+initialRunWarning+"<input type=\"checkbox\" class=\"ngs_checkbox\" name=\""+record.id+"\" id=\"lane_checkbox_"+record.id+"\" onClick=\"manageChecklists(this.name, 'lane_checkbox')\">"+"</td></tr>";
					
			}else if(type == 'experiments'){
				return "<tr><td>"+record.id+"</td><td>"+"<a href=\""+BASE_PATH+"/search/details/experiment_series/"+record.id+'/'+theSearch+"\">"+record.experiment_name+"</a>"+"</td><td onclick=\"editBox("+uid+", "+record.id+", 'summary', 'ngs_experiment_series', this)\">"+record.summary+
					"</td><td onclick=\"editBox("+uid+", "+record.id+", 'design', 'ngs_experiment_series', this)\">"+record.design+"</td>"+
					"<td><input type=\"checkbox\" class=\"ngs_checkbox\" name=\""+record.id+"\" id=\"experiment_checkbox_"+record.id+"\" onClick=\"manageChecklists(this.name, 'experiment_checkbox')\">"+"</td><tr>";
			}else{
				return null;
			}
		}
	};
	
	var callbacks = {
		pagination: function(summary){
			$('#'+type+'_summary').text( summary.from + ' to '+ summary.to +' of '+ summary.total +' entries');
			if (type == "samples") {
				checkCheckedList();
			}else if (type == 'lanes') {
				checkCheckedLanes();
			}else if (type == 'experiments') {
				checkCheckedExperiments();
			}
		},
		after_add: function(){
			//Only for example: Stop ajax streaming beacause from localfile data size never going to empty.
			if (this.data.length == queryData.length){
				this.stopStreaming();
			}
		}
	}
	
	var type_summary = createElement('div', ['id', 'class'], [type+'_summary', 'pull-left margin']);
	var the_table = document.getElementById('table_div_'+type);
	the_table.setAttribute('style','overflow:scroll');
	the_table.appendChild(type_summary);
	var table_rows = the_table.getElementsByTagName('tr');
	
	var st = StreamTable('#jsontable_'+type,
	  { view: view, 
		per_page: 10, 
		data_url: BASE_PATH + "/public/ajax/ngsquerydb.php?p="+queryType+"&q="+qvar+"&r="+rvar+"&seg="+seg+"&search="+theSearch+"&uid="+uid+"&gids="+gids,
		stream_after: 0.2,
		auto_sorting: true,  //Default is false
		fetch_data_limit: 100,
		callbacks: callbacks,
		pagination:{
			span: 5,                              
			next_text: 'Next &rarr;',              
			prev_text: '&larr; Previous',
			ul_class: type,
		},
	  },
	 data, type);
	
	var search = document.getElementById('st_search');
	search.id = 'st_search_' + type;
	search.setAttribute('class',"st_search margin pull-right");
	
	var table_element = document.getElementById('jsontable_'+type);
	var num_search = document.getElementById('st_num_search');
	num_search.id = 'st_num_search_' + type;
	
	var newlabel = createElement('label', ['class'], ['margin']);
	newlabel.setAttribute("for",'st_num_search_'+type);
	newlabel.innerHTML = "entries per page";
	document.getElementById('table_div_'+type).insertBefore(newlabel, table_element);
	
	num_search.setAttribute('class',"st_per_page margin pull-left input-sm");
	
	document.getElementById('st_pagination').id = 'st_pagination_' + type;
	var pagination = document.getElementById('st_pagination_'+type);
	pagination.setAttribute('class',"st_pagination_"+type+" margin");
	pagination.setAttribute('style',"text-align:right");
	
	type_summary = document.getElementById(type+'_summary');
	document.getElementById(type+'_summary').remove();
	the_table.insertBefore(type_summary, pagination);
}

function shiftColumns(id){
	if (id.childNodes[1].getAttribute('class') == 'pull-right fa') {
		id.childNodes[1].setAttribute('class', 'pull-right fa fa-sort-asc');
	}else if (id.childNodes[1].getAttribute('class') == 'pull-right fa fa-sort-asc') {
		id.childNodes[1].setAttribute('class','pull-right fa fa-sort-desc');
	}else{
		id.childNodes[1].setAttribute('class','pull-right fa fa-sort-asc');
	}
}

function expandTable(table){
	var toggle = tableToggle(table);
	location.reload();
}

function exportExcel(){
	if (checklist_lanes.length == 0 && checklist_samples.length == 0) {
		$('#deleteModal').modal({
			show: true
		});
		document.getElementById('myModalLabel').innerHTML = 'No Import/Sample Selection';
		document.getElementById('deleteLabel').innerHTML ='You must select at least one Import/Sample to export.';
		document.getElementById('deleteAreas').innerHTML = '';
			
		document.getElementById('cancelDeleteButton').innerHTML = "OK";
		document.getElementById('confirmDeleteButton').setAttribute('style', 'display:none');
	}else{
		$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/export_excel.php",
				data: { p: "checkExperimentSeries", samples: checklist_samples },
				async: false,
				success : function(s)
				{
					var ES = JSON.parse(s);
					console.log(ES);
					if (ES.length == 1) {
						var file_path;
						$.ajax({ type: "GET",
								url: BASE_PATH+"/public/ajax/export_excel.php",
								data: { p: "exportExcel", samples: checklist_samples },
								async: false,
								success : function(q)
								{
									console.log(q);
									window.open(BASE_PATH + "/public" + q, '_blank');
									file_path = q;
								}
						});
						
						$.ajax({ type: "GET",
								url: BASE_PATH+"/public/ajax/export_excel.php",
								data: { p: "deleteExcel", file: file_path },
								async: false,
								success : function(r)
								{
								}
						});
					}else{
						$('#deleteModal').modal({
							show: true
						});
						document.getElementById('myModalLabel').innerHTML = 'More than one Experiment Series';
						document.getElementById('deleteLabel').innerHTML ='You must select Imports/Samples within the same experiment series.';
						document.getElementById('deleteAreas').innerHTML = '';
							
						document.getElementById('cancelDeleteButton').innerHTML = "OK";
						document.getElementById('confirmDeleteButton').setAttribute('style', 'display:none');
					}
				}
		});
	}
}

$(function() {
	"use strict";
	
	//Rerun Check
	if (window.location.href.split("/").indexOf('selected') > -1 || window.location.href.split("/").indexOf('rerun') > -1) {
		rerunLoad();
	}
	
	//The Calender
	$("#calendar").datepicker();
	
	$('input').on('ifChanged', function(event){
		if (event.target.name.substring(0,6) == "common") {
			var array = event.target.id.split("_");
			
			if (array[1] == 'yes' && deseqList.indexOf(array[0]) == -1 && (array[0] == 'miRNA' || array[0] == 'tRNA')) {
				var selects = document.getElementsByTagName("select");
				for(var i = 0; i < selects.length; i++) {
					if(selects[i].id.indexOf('select_5_') == 0) {
						var opt = createElement('option', ['id', 'value'], [array[0], array[0]]);
						opt.innerHTML = array[0];
						selects[i].appendChild(opt);
					}
				}
				deseqList.push(array[0]);
			}else if (array[1] == 'no' && deseqList.indexOf(array[0]) > -1 ){
				deseqList.splice(deseqList.indexOf(array[0]), 1);
				var selects = document.getElementsByTagName("select");
				for(var i = 0; i < selects.length; i++) {
					if(selects[i].id.indexOf('select_5_') == 0) {
						var children = selects[i].childNodes;
						for (var y = 0; y < children.length; y++) {
							if (children[y].id.split("_")[0] == array[0]) {
								children[y].remove();
							}
						}
					}
				}
			}
		}
	});

	/*##### PAGE DETERMINER #####*/
	
	var qvar = "";
	var rvar = "";
	var segment = "";
	var theSearch = "";
	var uid = "";
	var gids = "";
	
	if (phpGrab) {
		var segment = phpGrab.theSegment;
		var theSearch = phpGrab.theSearch;
		uid = phpGrab.uid;
		gids = phpGrab.gids;
	}

	//gids
	if (gids == '') {
		gids = -1;
	}
	
	//Details values
	if (segment == "details") {
		if (phpGrab.theField == "experiment_series") {
			qvar = phpGrab.theValue;
		}
		else if (phpGrab.theField == "experiments") {
			rvar = phpGrab.theValue;
		}
	}

	//Browse values
	else if (segment == "browse") {
		qvar = phpGrab.theField;//field
		rvar = unescape(phpGrab.theValue);//value
	}

	if (phpGrab.theField == "samples") {
		reloadBasket();
	}
	
	if (phpGrab.theSegment == 'generated') {
		$.ajax({ type: "GET",
			url: BASE_PATH +"/public/ajax/tablegenerator.php?",
			data: { p: "getGeneratedTable"},
			async: false,
			success : function(s)
			{
				console.log(s);
				table_params = s;
			}
		});
		var json_obj;
		console.log(BASE_PATH +"/public/api/getsamplevals.php?" + table_params.parameters);
		$.ajax({ type: "GET",
			url: BASE_PATH +"/public/api/getsamplevals.php?" + table_params.parameters,
			async: false,
			success : function(s)
			{
				json_obj = JSON.parse(s);
				generateStreamTable('generated', json_obj, phpGrab.theSegment, qvar, rvar, segment, theSearch, uid, gids);
			}
		});
	}else if (phpGrab.theSegment != 'report' && phpGrab.theSegment != 'table_viewer') {
		var experiment_series_data = [];
		var lane_data = [];
		var sample_data = [];
	
		/*##### SAMPLES TABLE #####*/	
		//var samplesTable = $('#jsontable_samples').dataTable();
	
		var samplesType = "";
		if (segment == 'selected') {
			samplesType = "getSelectedSamples";
			if (window.location.href.split("/").indexOf('tablecreator') > -1) {
				theSearch = basket_info;
				qvar = "getTableSamples";
			}
		}else{
			samplesType = "getSamples";
		}
		console.log(basket_info);
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/ngs_tables.php",
			data: { p: samplesType, q: qvar, r: rvar, seg: segment, search: theSearch, uid: uid, gids: gids },
			async: false,
			success : function(s)
			{
				sample_data = s;
				var changeHTML = '';
				var hrefSplit = window.location.href.split("/");
				var typeLocSelected = $.inArray('selected', hrefSplit);
				var typeLocRerun = $.inArray('rerun', hrefSplit);
				if (typeLocSelected > 0 || typeLocRerun > 0) {
					theSearch = '';
				}
				var type = 'samples';
				if (samplesType == 'getSamples' && segment == 'table_create') {
					var samples_with_runs = [];
					var objects_with_runs = [];
					$.ajax({ type: "GET",
						url: BASE_PATH+"/public/ajax/tablegenerator.php",
						data: { p: "samplesWithRuns" },
						async: false,
						success : function(k)
						{
							for(var x = 0; x < k.length; x++){
								samples_with_runs.push(k[x].sample_id);
							}
						}
					});
					console.log(samples_with_runs);
					for(var z = 0; z < s.length; z++){
						if (samples_with_runs.indexOf(s[z].id) > -1) {
							objects_with_runs.push(s[z]);
						}
					}
					console.log(objects_with_runs);
					s = objects_with_runs;
					queryType = 'table_create';
				}else{
					var queryType = samplesType;
				}
				generateStreamTable(type, s, queryType, qvar, rvar, segment, theSearch, uid, gids);
			}
		});
	
		/*##### LANES TABLE #####*/
	
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/ngs_tables.php",
			data: { p: "getLanes", q: qvar, r: rvar, seg: segment, search: theSearch, uid: uid, gids: gids },
			async: false,
			success : function(s)
			{
				lane_data = s;
				var type = 'lanes';
				var queryType = "getLanes";
				if (window.location.href.split("/").indexOf('search') > -1) {
					generateStreamTable(type, s, queryType, qvar, rvar, segment, theSearch, uid, gids);
				}
			}
		});
	
		/*##### SERIES TABLE #####*/
		//var experiment_seriesTable = $('#jsontable_experiment_series').dataTable({responsive: true});
		
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/ngs_tables.php",
			data: { p: "getExperimentSeries", q: qvar, r: rvar, seg: segment, search: theSearch, uid: uid, gids: gids },
			async: false,
			success : function(s)
			{
				experiment_series_data = s;
				var type = 'experiments';
				var queryType = "getExperimentSeries";
				if (window.location.href.split("/").indexOf('search') > -1) {
					generateStreamTable(type, s, queryType, qvar, rvar, segment, theSearch, uid, gids);
				}
			}
		});
		
		if (segment == 'index' || segment == 'browse' || segment == 'details') {
			console.log(experiment_series_data);
			console.log(lane_data);
			console.log(sample_data);
			checkOffAllSamples();
			checkOffAllLanes();
			generateIDDictionary(experiment_series_data, lane_data, sample_data);
			reloadBasket();
		}
	}
	console.log(qvar);
	console.log(rvar);
	console.log(segment);
	console.log(theSearch);
});