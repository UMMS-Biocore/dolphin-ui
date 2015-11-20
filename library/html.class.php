<?php

class HTML {
	private $js = array();

	function shortenUrls($data) {
		$data = preg_replace_callback('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', array(get_class($this), '_fetchTinyUrl'), $data);
		return $data;
	}

	private function _fetchTinyUrl($url) { 
		$ch = curl_init(); 
		$timeout = 5; 
		curl_setopt($ch,CURLOPT_URL,'http://tinyurl.com/api-create.php?url='.$url[0]); 
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout); 
		$data = curl_exec($ch); 
		curl_close($ch); 
		return '<a href="'.$data.'" target = "_blank" >'.$data.'</a>'; 
	}

	function sanitize($data) {
		return mysql_real_escape_string($data);
	}

	function link($text,$path,$prompt = null,$confirmMessage = "Are you sure?") {
		$path = str_replace(' ','-',$path);
		if ($prompt) {
			$data = '<a href="javascript:void(0);" onclick="javascript:jumpTo(\''.BASE_PATH.'/'.$path.'\',\''.$confirmMessage.'\')">'.$text.'</a>';
		} else {
			$data = '<a href="'.BASE_PATH.'/'.$path.'">'.$text.'</a>';	
		}
		return $data;
	}

	function includeJs($fileName) {
		$data = '<script src="'.BASE_PATH.'/js/'.$fileName.'.js"></script>';
		return $data;
	}

	function includeCss($fileName) {
		$data = '<style href="'.BASE_PATH.'/css/'.$fileName.'.css"></script>';
		return $data;
	}

	function getContentHeader($name, $parent_name, $parent_link)
	{
	$html='	<!-- Content Header (Page header) -->
				<section class="content-header">
					<h1>
						'.$name.'
						<small>'.$parent_name.'</small>
					</h1>
					<ol class="breadcrumb">
						<li><a href="'.BASE_PATH.'"><i class="fa fa-dashboard"></i> Home</a></li>
						<li><a href="'.BASE_PATH.'/'.$parent_link.'">'.$parent_name.'</a></li>
						<li class="active">'.$name.'</li>
					</ol>
				</section>';
	return $html;
	}
	
	function getDataTableFooterContent($fields, $table)
	{
	 $html='</aside><!-- /.right-side -->
		</div><!-- ./wrapper -->


		<script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script> 
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>
	<script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
		<script src="'.BASE_PATH.'/js/dataTables/jquery.dataTables.min.js"></script>
		<script src="'.BASE_PATH.'/js/dataTables/dataTables.bootstrap.js"></script>
		<script src="'.BASE_PATH.'/js/dataTables/dataTables.tableTools.min.js"></script>
		
		<!-- AdminLTE App -->
		<script src="'.BASE_PATH.'/js/AdminLTE/app.js" type="text/javascript"></script>

		<script type="text/javascript" language="javascript" src="'.BASE_PATH.'/js/dataTables/dataTables.editor.js"></script>
		<script type="text/javascript" language="javascript" src="'.BASE_PATH.'/js/dataTables/resources/syntax/shCore.js"></script>
		<script type="text/javascript" language="javascript" class="init">

	var editor; // use a global for the submit and return data rendering in the examples
	
	function toggleTable() {
		var lTable = document.getElementById("descTable");
		lTable.style.display = (lTable.style.display == "table") ? "none" : "table";
	}
	
	$(document).ready(function() {
		editor = new $.fn.dataTable.Editor( {
			"ajax": "'.BASE_PATH.'/public/php/ajax/ajax.php?t='.$table[0]['tablename'].'",
			"display": "lightbox",
			"table": "#'.$table[0]['tablename'].'",
			"fields": [';
	$usetablename = ($table[0]['joined']) ? $table[0]['tablename'].'.' : '';
			foreach ($fields as $field):
			$html.='{
				"label": "'.$field['title'].':",
				"name": "'.$usetablename.$field['fieldname'].'",
				';
			$html.=($field['options']!='' ) ? $field['options']:'';
			$html.=($field['joinedtablename']!="")? '"type": "select"':"";
			$html.=($field['len']>128)? '"type": "textarea"':"";
			$html.='},';
			endforeach;
	$html.=']
		} );
	
		$("#'.$table[0]['tablename'].'").DataTable( {
			dom: "Tfrtip",
			ajax: "'.BASE_PATH.'/public/php/ajax/ajax.php?t='.$table[0]['tablename'].'",
			columns: [';
			foreach ($fields as $field):
			$datafield=($field['joinedtablename']!="")? $field['joinedtablename'].'.'.$field['joinedtargetfield']:$usetablename.$field['fieldname'];
			
			$render = ( $field['render']!='' ) ? ",".$field['render']:'';
			$html.='	{ data: "'.$datafield.'"'.$render.' },
			';
			endforeach;
	$html.='],
			tableTools: {
				sRowSelect: "os",
				aButtons: [
					{ sExtends: "editor_create", editor: editor },
					{ sExtends: "editor_edit",	editor: editor },
					{ sExtends: "editor_remove", editor: editor }
				]
			},
			';
			if ($table[0]['joined']=='1'){
			$html.='initComplete: function ( settings, json ) {
			';
				foreach ($fields as $field):
					if ($field['joinedtablename']!="") {
					$html.='editor.field( "'.$table[0]['tablename'].'.'.$field['fieldname'].'" ).update( json.'.$field['joinedtablename'].' );
					';
					}
				endforeach;
			
			$html.='}';
			}
	$html.='	} );';
			
	$html.='} );
	
		</script>
		</body>
	</html>
	';
	return $html;
	}
	
	function getSideMenuItem($obj )
	{
	$html="";
	foreach ($obj as $item):
		$html.='<li><a href="'.BASE_PATH.'/'.$item->{'link'}.'"><i class="fa fa-angle-double-right"></i>'.$item->{'name'}.'</a></li>';
	endforeach;
	return $html;
	}
	
	function getDataTableContent($fields, $tablename)
	{
	$html='	<div class="container">
				<!-- Main content -->
				<section class="content">
					<div class="row">
						<div class="info">
								<a id="descLink" onclick="toggleTable();" href="#">Click </a> to see the description of each field in the table.<br>
								<table id="descTable" class="display" style="display:none" cellspacing="0" width="100%">
								<thead>
									<tr>
									 <th></th>
									 <th>Summary</th>
								</thead>
								<tbody>';
	foreach ($fields as $field):
		$html.="<tr><th>".$field['title']."</th><td>".$field['summary']."</td></tr>";
	endforeach;
	$html.='			</table>
				</p>
						</div>

						<table id="'.$tablename.'" class="display" cellspacing="0" width="100%">
								<thead>
										<tr>';
	foreach ($fields as $field):

				$html.="<th>".$field['title']."</th>";
	endforeach;
		 $html.='								</tr>
								</thead>
						</table>
			
					</div><!-- /.row -->
				</section><!-- /.content -->
		</div>';
	return $html;
	}
	
 function getRespBoxTable_ng($title, $table, $fields)
	{
	$html='				<div class="box">
				<div class="box-header">
					<h3 class="box-title">'.$title.'</h3>';
		$html.= $this->getInfoBox($table);
		$html.= '</div>
				<div class="box-body table-responsive">
				<table id="jsontable_'.$table.'" class="table table-hover table-striped table-condensed">
		<thead>
					<tr>
			'.$fields.'
					</tr>
					</thead>
				</table>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
				
				
	';
	return $html;
	}

	function getRespBoxTableStreamNoExpand($title, $table, $fields, $tableKeys){
		$html = '';
		$html.= '<div class="box">
						<div class="box-header">
								<h3 class="box-title">'.$title.'</h3>';
		$html.= $this->getInfoBox($table);
		$html.=			'</div><!-- /.box-head -->
						<div id="table_div_'.$table.'" class="box-body table-responsive">
								<table id="jsontable_'.$table.'" class="table table-hover table-striped table-condensed table-scrollable">
										<thead>
										<tr>';
										for($x = 0; $x < count($tableKeys); $x++){
												if($tableKeys[$x] == "id" || $tableKeys[$x] == "total_reads" || $tableKeys[$x] == "total_samples"){
		$html.=									'<th data-sort="'.$tableKeys[$x].'::number" onclick="shiftColumns(this)">'
												.$fields[$x].'<i id="'.$tableKeys[$x].'" class="pull-right fa fa-unsorted"></i></th>';
												}else{
		$html.=									'<th data-sort="'.$tableKeys[$x].'::string" onclick="shiftColumns(this)">'
												.$fields[$x].'<i id="'.$tableKeys[$x].'" class="pull-right fa fa-unsorted"></i></th>';
												}
										}
		$html.=							'</tr>
										</thead>
										<tbody>
										</tbody>
								</table>
						</div><!-- /.box-body -->
				</div><!-- /.box -->';
		
		return $html;
	}
	
	function getRespBoxTableStream($title, $table, $fields, $tableKeys){
		$html = '';
		$html.= '<div class="box">
						<div class="box-header">
								<h3 class="box-title">'.$title.'</h3>';
		$html.= $this->getInfoBox($table);
		if(!isset($_SESSION['tablecreatorcheck'])){
			$html.=				'<div class="pull-right">
										<button class="btn btn-default margin" value="'.$table.'" onclick="expandTable(this.value)">
										<span class="fa fa-arrows-h"></span>
										</button>
								</div>';
		}						
		$html.=			'</div><!-- /.box-head -->
						<div id="table_div_'.$table.'" class="box-body table-responsive">
								<table id="jsontable_'.$table.'" class="table table-hover table-striped table-condensed table-scrollable">
										<thead>
										<tr>';
										for($x = 0; $x < count($tableKeys); $x++){
												if($tableKeys[$x] == "id" || $tableKeys[$x] == "total_reads" || $tableKeys[$x] == "total_samples"){
		$html.=									'<th data-sort="'.$tableKeys[$x].'::number" onclick="shiftColumns(this)">'
												.$fields[$x].'<i id="'.$tableKeys[$x].'" class="pull-right fa fa-unsorted"></i></th>';
												}else{
		$html.=									'<th data-sort="'.$tableKeys[$x].'::string" onclick="shiftColumns(this)">'
												.$fields[$x].'<i id="'.$tableKeys[$x].'" class="pull-right fa fa-unsorted"></i></th>';
												}
										}
		$html.=							'</tr>
										</thead>
										<tbody>
										</tbody>
								</table>
						</div><!-- /.box-body -->
				</div><!-- /.box -->';
		
		return $html;
	}
	
	function getBoxTable_ng($title, $table, $fields)
	{
	$html='				<style>
				 
				 .table {margin:0 auto; border-collapse:separate;}
				 .table thead {}
				 
				 .table tbody {height:300px;overflow-y:scroll;}

				</style>
			
							<div class="box">
								<div class="box-header">
									<h3 class="box-title">'.$title.' Table</h3>
									<div class="pull-right box-tools">
									<button class="btn btn-primary btn-sm pull-right" data-widget="collapse" data-toggle="tooltip" title="Col
lapse" style="margin-right: 5px;"><i class="fa fa-minus"></i></button>
									<button class="btn btn-primary btn-sm daterange_'.$table.' pull-right" data-toggle="tooltip" title="Date 
range"><i class="fa fa-calendar"></i></button>
									</div><!-- /. tools -->
								</div><!-- /.box-header -->
								<div class="box-body table-responsive">
								<table id="jsontable_'.$table.'" class="table table-bordered table-striped table-condensed table-scrollable" cellspacing="0" width="100%">
									<thead>
									<tr>
										'.$fields.'
									</tr>
								</thead>

								 </table>
							</div><!-- /.box-body -->
							</div><!-- /.box -->
				
				
	';
	return $html;
	}


	function getBoxTable_stat($userlab, $galaxydolphin, $fields)
	{
	$html='
							<div class="box">
								<div class="box-header">
									<h3 class="box-title">'.$galaxydolphin.' '.$userlab.' Table</h3>
									<div class="pull-right box-tools">
									<button class="btn btn-primary btn-sm pull-right" data-widget="collapse" data-toggle="tooltip" title="Col
lapse" style="margin-right: 5px;"><i class="fa fa-minus"></i></button>
									<button class="btn btn-primary btn-sm daterange_'.$userlab.' pull-right" data-toggle="tooltip" title="Dat
e range"><i class="fa fa-calendar"></i></button>
									</div><!-- /. tools -->
								</div><!-- /.box-header -->
								<div class="box-body table-responsive">
								<table id="jsontable_'.$userlab.'" class="table table-bordered table-striped" cellspacing="0" width="100%">
									<thead>
									<tr>
										'.$fields.'
									</tr>
								</thead>
								<tfoot>
									 <tr>
										'.$fields.'
									 </tr>
								 </tfoot>
								 </table>
							</div><!-- /.box-body -->
							</div><!-- /.box -->
	';
	return $html;
	}
	function getAccordion($name, $object, $search)
	{
	 $html='	<div class="panel box box-primary">
					<div class="box-header with-border">
						<h4 class="box-title">
						<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$name.'">
							'.$name.'
						</a>
						</h4>
					</div>
					<div id="collapse'.$name.'" class="panel-collapse collapse in">
						<div class="box-body">
				<ul>
				';
				if($name == "Assay"){ $adjName = "library_type"; }
				else{ $adjName = $name; }
				if($search == ""){
				foreach ($object as $obj):
					$html.='<li><a href="'.BASE_PATH.'/search/browse/'
					.$name."/".$obj['name']."/".strtolower($adjName)."=".$obj['name']."".
					'">'.$obj['name'].' ('.$obj['count'].')</a></li>';
				endforeach;
				}
				else
				{
				$selectChk = explode('$', $search);
				
				foreach ($object as $obj):
					$modSearch = strtolower($adjName)."=".$obj['name'];
					if(in_array($modSearch, $selectChk))
					{
						$tmpSelectCheck = $selectChk;
						$key = array_search($modSearch, $tmpSelectCheck);
						unset($tmpSelectCheck[$key]);
						if(empty($tmpSelectCheck)){
							$modSearch = implode('$',$tmpSelectCheck);
							$html.='<li><a href="'.BASE_PATH.'/search/'
								."index".'">'.$obj['name'].' ('.$obj['count'].') +</a></li>';
						}
						else
						{
							$modSearch = implode('$',$tmpSelectCheck);
							$html.='<li><a href="'.BASE_PATH.'/search/browse/'
								.$name."/".$obj['name']."/".$modSearch.
								'">'.$obj['name'].' ('.$obj['count'].') +</a></li>';	
						}
						
					}
					else{
						$html.='<li><a href="'.BASE_PATH.'/search/browse/'
							.$name."/".$obj['name']."/".$search. "$" .$modSearch.
							'">'.$obj['name'].' ('.$obj['count'].')</a></li>';
					}
				endforeach;
				}
	$html.='
				</ul>
			</div>
					</div>
			</div>
	';
	return $html;
	}
	function getExperimentSeriesPanel($objects)
	{
	foreach ($objects as $obj):
	$html='<div class="panel panel-default">
		<div class="panel-heading">
		<h4 class="panel-title">Experiment Series</h3>
		</div>
		<div class="panel-body">
		<h4>
			'.$obj['experiment_name'].'
		</h4>
		</div>
		<div class="box-body">
		<dl class="dl-horizontal">
		<dt>Summary</dt>
		<dd>'.$obj['summary'].'</dd>
		<dt>Design</dt>
		<dd>'.$obj['design'].'</dd>
		</dl>
		</div> 
		</div>';
	endforeach;
	return $html;
	}
	function getBrowserPanel($objects, $fields, $header ,$name)
	{
	foreach ($objects as $obj):
	$html='<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">'.$header.'</h3>
			<div class="panel-tools pull-right">
				<button class="btn btn-panel-tool" onclick="backFromDetails(\''.$header.'\')">
					<i class="fa fa-arrow-left"></i>
				</button>
			</div>
		</div>
		<div class="panel-body">
		<h4>
			'.$obj[$name].'
		</h4>
		</div>
		<div class="box-body">
		<dl class="dl-horizontal">
		';
		foreach ($fields as $field):
		if ($field['fieldname']!=$name && $obj[$field['fieldname']]!=""){
					 $html.='	<dt>'.$field['title'].'</dt>';
			 $html.='<dd>'.$obj[$field['fieldname']].'</dd>';
		}
		endforeach;
	$html.=	'</dl>
		</div> 
		</div>';
	endforeach;
	return $html;
	}
	
	function getBrowserPanelMore($objects, $fields, $header ,$name, $files, $fastq_files)
	{
	foreach ($objects as $obj):
	$html='<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">'.$header.'</h3>
			<div class="panel-tools pull-right">
				<button class="btn btn-panel-tool" onclick="backFromDetails(\''.$header.'\')">
					<i class="fa fa-arrow-left"></i>
				</button>
			</div>
		</div>
		<div class="panel-body">
		<h4>
			'.$obj[$name].'
		</h4>
		
		<div class="box-body col-md-4" style="overflow:scroll">
		<dl class="dl-horizontal">
		';
		foreach ($fields as $field):
		if ($field['fieldname']!=$name && $obj[$field['fieldname']]!=""){
					 $html.='	<dt>'.$field['title'].'</dt>';
			 $html.='<dd>'.$obj[$field['fieldname']].'</dd>';
		}
		endforeach;
		$html.= '</dl>
				</div>
				<div class="box-body col-md-8">';
		if($files != null){
				$html .= '<div class="box-body col-md-12" style="overflow:scroll">
				<table class="table table-hover table-striped table-condensed">';
				$html.='<thead><tr><th>Input File(s) Directory:</th></tr></thead>
						<tbody>';
				$html.='<tr><td onclick="editBox( '.$_SESSION['uid'].', '. $files[0]['dir_id'].', \'fastq_dir\', \'ngs_dirs\', this)">'.$files[0]['fastq_dir'].'</td></tr>
						</tbody>';
				$html.='<thead><tr><th>Input File(s):</th></tr></thead>
						<tbody>';
				foreach ($files as $f){
						if($fastq_files == 'lanes'){
								$html.='<tr><td onclick="editBox( '.$_SESSION['uid'].', '. $f['id'].', \'file_name\', \'ngs_temp_lane_files\', this)">'.$f['file_name'].'</td></tr>';
						}else{
								$html.='<tr><td onclick="editBox( '.$_SESSION['uid'].', '. $f['id'].', \'file_name\', \'ngs_temp_sample_files\', this)">'.$f['file_name'].'</td></tr>';	
						}
				}
				$html .= '</tbody></table>
						</div>';
		}
		if($fastq_files != null && $fastq_files != 'lanes'){
				$html .= '<div class="box-body col-md-12" style="overflow:scroll">
				<table class="table table-hover table-striped table-condensed">';
				$html.='<thead><tr><th>Processed File(s) Directory:</th></tr></thead>
						<tbody>';
				$html.='<tr><td onclick="editBox( '.$_SESSION['uid'].', '. $fastq_files[0]['dir_id'].', \'fastq_dir\', \'ngs_dirs\', this)">'.$fastq_files[0]['fastq_dir'].'</td></tr>
						</tbody>';
				$html.='<thead><tr><th>Processed File(s):</th></tr></thead>
						<tbody>';
				foreach ($fastq_files as $ff){
						$html.='<tr><td onclick="editBox( '.$_SESSION['uid'].', '. $ff['id'].', \'file_name\', \'ngs_fastq_files\', this)">'.$ff['file_name'].'</td></tr>';
				}
				$html .= '</tbody></table>
						</div>';
		}
		$html.=	'</div>
			</div>
			</div>';
	endforeach;
	return $html;
	}
	
	function getDolphinBasket(){
	$html = '';
	$html.= '<div class="small box">
			<div class="small box-header">
				<h4 class="box-title">Selected Samples</h3>
			</div>
			<div class="small box-body table-responsive non-padding">
				<table id="dolphin_basket" class="table table-hover">
				<thead>
					<tr>
						<th>ID</th>
						<th>Sample Name</th>
						<th><button id="clear_basket" class="btn btn-primary btn-xs pull-right" disabled="true" onclick="clearBasket()">Clear</button></th>
					</tr>
					</thead>
				<tbody id="dolphin_basket_body">
				</tbody>
				</table>
			</div>
		</div>';
	return $html;
	}
	
	function getQCPanel()
	{
	$html='
	<div class="panel panel-default">
		<div class="panel-heading">
		<h4>Analysis Results <small>Comprehensive Analysis</small></h4>
		</div>
		<div class="panel-body">
	 <iframe src="'.BASE_PATH.'/bs.html" seamless frameborder=0 onload="this.width=855;this.height=600;"></iframe>
	</div>
	</div>
	';
	return $html;
	}
	
	function getMultipleSelectBox($options, $id, $field, $idfield)
	{
	$html='<select class="form-control" id="'.$id.'" name="'.$id.'">';
	foreach ($options as $option):
				$html.="<option value='".$option[$idfield]."'>".$option[$field]."</option>";
	endforeach;
	$html.='</select>';
	return $html;
	}
	function getRadioBox($options, $id, $field)
	{
	$html='';
	foreach ($options as $option):
			$html.='<div class="radio"><label>';
				$html.='<input type="radio" name="'.$id.'" id="'.$option['name'].'" value="'.$option['value'].'" '.$option['selected'].'>&nbsp;'.$option['name'];
		$html.='</label></div>';
	endforeach;
	return $html;
	}
	function getSubmitBrowserButton()
	{
	$html = '';
	$html.= '<div class="col-md-9">
				<div class="margin pull-left btn-group">
					<button id="dso_menu" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Data Selection Options <span class="fa fa-caret-down"></span></button>
					<ul class="dropdown-menu" role="menu" aria-labelledby="dso_menu">
						<li><a name="pipeline_button" onClick="submitSelected()">Send to Pipeline</a></li>
						<li><a name="send_to_status_button" onClick="sendToStatus()">Pipeline Status</a></li>
						<li class="divider"></li>
						<li><a name="export_excel_button" onClick="exportExcel()">Export to Excel</a></li>';
	if($_SESSION['uid'] == 1){
							#<li><a name="send_to_NCBI_button" onClick="" disabled>Send to NCBI</a></li>
		$html.= 			'<li><a name="send_to_ENCODE_button" onClick="checkForEncodeSubmission()" disabled>Send to ENCODE</a></li>';
	}
	$html.=				'<li class="divider"></li>
						<li><a name="change_group" onClick="changeDataGroup(this.name)" disabled>Change Experiment Group</a></li>
						<li><a name="change_owner" onClick="changeDataGroup(this.name)" disabled>Change Experiment Owner</a></li>
						<li class="divider"></li>
						<li><a name="table_generation_button" onClick="generateTableLink()">Generate Tables</a></li>';
	$html.= 		'</ul>';
	$html.= '	</div>
				<div class="margin pull-right">
					<input type="button" class="btn btn-danger" name="delete_Selected" value="Delete Selected" onclick="deleteButton('.$_SESSION['uid'].')"/>
				</div>
			</div>';
	return $html;
	}
	function getSelectionBox($title, $selection){
	$html = '';
	if($selection[0] == "TEXTBOX"){
		$html.= 	'<div class="form-group">
				<label>' .$title. '</label>
				<textarea id="'.$title.'_val" type="text" class="form-control" rows="5" placeholder="..."></textarea>
				</div>';
	}
	else if($selection[0] == "TEXT")
	{
		$html.= 	'<div class="col-md-6">
				<label>' .$title. '</label>
				<input id="'.$title.'_val" type="text" class="form-control" value="'.$selection[1].'" rows="5">
				</div>';
	}
	else
	{
		
		$html.= '<div class="col-md-12">';
		
		$html.=		'<label>' .$title. '</label>
					<select id="'.$title.'_val" class="form-control"';
		if($selection[0] == "ONCHANGE"){
		$html.= 								' onchange="selectTrimming(this.id, 0, 0)">';
		}else{
		$html .=								'>';
		}
		foreach($selection as $sel){
			if ($sel != "ONCHANGE"){
		$html.=			'<option>'.$sel.'</option>';
			}
		}
		$html.=			'</select>
				</div>';
	}
	return $html;
	}
	function getStaticSelectionBox($title, $id, $selection, $width){
	$html = "";
	$html = '<div class="col-md-'.$width.'">
			<div class="box box-default">
				<div class="box-header with-border">
				<h3 class="box-title">'.$title.'</h3>';
		$html.= $this->getInfoBox($id);
		$html.= '</div><!-- /.box-header -->
				<div class="box-body">
					<div class="input-group margin col-md-11">
						<form role="form">';
	if ($selection == "TEXT"){
		$html.= 						'<input type="text" class="form-control" id="'.$id.'">';
	}else if($selection == "TEXTBOX"){
		if($id == 'input_files'){
				$placeholder = "Paired End Example:\nlibrary_name_rep1 lib_rep1_R1.fastq.gz lib_rep1_R2.fastq.gz\nSingle End Example:\nlibrary_name_rep1 lib_rep1.fastq.gz";
		}else if($id == 'Barcode Definitions'){
				$placeholder = "lib_rep1 GATACA\nlib_rep2 CATATC";
		}else{
				$placeholder = "...";
		}
		$html.=							'<textarea id="'.$id.'" type="text" class="form-control" rows="5" placeholder="'.$placeholder.'"></textarea>';
	}else{
		$html.=							'<select class="form-control" id="'.$id.'">
									'.$selection.'
								</select>';
	}	
	$html.= 						'</form>
					</div>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col -->';
	return $html;
	}
	function startExpandingSelectionBox($width){
	$html = '';
	$html.= '<div class="col-md-'.$width.'">';
	return $html;
	}
	function endExpandingSelectionBox(){
	$html = '';
	$html.= '</div><!-- /.col -->';
	return $html;
	}
	function getExpandingSelectionBox($title, $id, $numFields, $width, $fieldTitles, $selection){
	$html = "";
	$html = '<div class="col-md-'.$width.'">
			<div id="'.$id.'_exp" class="box box-default collapsed-box">
				<div class="box-header with-border">
					<h3 class="box-title">'.$title.'</h3>
					<div class="box-tools margin pull-right">
					<button class="btn btn-box-tool btn-primary" data-widget="collapse"><i id="'.$id.'_exp_btn" class="fa fa-plus"></i></button>
					</div><!-- /.box-tools -->';
		$html.= $this->getInfoBox($id);
		$html.= '</div><!-- /.box-header -->';
	if ($fieldTitles[0] == "Add a Pipeline")
	{
		$html.= $this->getPipelinesButton();
	}
	else if ($fieldTitles[0] == "Add new Custom Sequence Set")
	{
		$html.= $this->getCustomButton();	
	}
	else
	{
		$html.= 	'<div id="'.$id.'_exp_body" class="box-body" style="display: none;" onchange="">
				<form role="form">
				<div class="margin">
						yes
					<input id="'.$id.'_yes" type="radio" name="'.$id.'" value="yes"></input>
					<input id="'.$id.'_no" type="radio" name="'.$id.'" value="no" checked></input>
						no
				</div>';
		$html.= 		'<div class="input-group margin col-md-11">
					';
		for($y = 0; $y < $numFields; $y++){
			$html.= $this->getSelectionBox($fieldTitles[$y], $selection[$y]);
		}
		$html.= 		'</div>';
	}
	$html.= 		'</form>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col -->';
	return $html;
	}
	function getExpandingCommonRNABox($title, $id, $numFields, $width, $fieldTitles, $selection){
	$html = "";
	$html = '<div class="col-md-'.$width.'">
			<div id="'.$id.'_exp" class="box box-default collapsed-box">
				<div class="box-header with-border">
					<h3 class="box-title">'.$title.'</h3>
					<div class="box-tools pull-right margin">
					<button class="btn btn-box-tool btn-primary" data-widget="collapse"><i id="'.$id.'_exp_btn" class="fa fa-plus"></i></button>
					</div><!-- /.box-tools -->';
		$html.= $this->getInfoBox($id);
		$html.= '</div><!-- /.box-header -->
				<div id="'.$id.'_exp_body" class="box-body compact" style="display: none;" onchange="">';
	
	$html.= 			'<div class="input-group col-md-12">
						<form role="form">';
	for($y = 0; $y < $numFields; $y++){
	$html.=					'<div class ="text-center btn-group-vertical margin">
							<label value="'.$fieldTitles[$y].'">'.$fieldTitles[$y].'</label>
							<div class="text-center">
								yes
								<input id="'.$fieldTitles[$y].'_yes" name="common_'.$fieldTitles[$y].'" type="radio" value="yes" />
								<input id="'.$fieldTitles[$y].'_no" name="common_'.$fieldTitles[$y].'" type="radio" value="no" checked/>
								no
							</div>
						</div>';
	}
	$html.=					'<div class="input-group margin">
							<div class="input-group margin">
								<div id="change_params_outer"></div>
								<input id="change_params_btn" class="btn btn-primary" type="button" value="Change Parameters" onclick="changeRNAParamsBtn()"/> 
							</div>
						</div>
						</form>
					</div>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col -->';
	return $html;
	}
	function getExpandingAnalysisBox($title, $id, $box_open){
	$html = "";
	if($box_open){
		$exp = 'box box-default';
		$exp_btn = 'fa fa-minus';
		$exp_body = 'display: block';
	}else{
		$exp = 'box box-default collapsed-box';
		$exp_btn = 'fa fa-plus';
		$exp_body = 'display: none';
	}
	
	$html = '<div class="col-md-12">
			<div id="'.$id.'_exp" class="'.$exp.' box">
				<div class="box-header with-border">
					<h3 class="box-title">'.$title.'</h3>
					<div class="box-tools pull-right">
						<button class="btn btn-box-tool btn-primary margin pull-right" data-widget="collapse"><i id="'.$id.'_exp_btn" class="'.$exp_btn.'"></i></button>';
		$html.= $this->getInfoBox($id);
		$html.= '	</div><!-- /.box-tools -->
				</div><!-- /.box-header -->
				<div id="'.$id.'_exp_body" class="box-solid box-body" style="'.$exp_body.'">';
				
				if($id == "initial_mapping"){
					$html.= $this->getInitialMappingTable();
				}else if ($id == 'table_create'){
					if(!isset($_SESSION['ngs_samples'])){	
						$html .= $this->getRespBoxTableStream("Samples", "samples", ["id","Sample Name","Title","Source","Organism","Molecule","Selected"], ["id","name","title","source","organism","molecule","total_reads"]);
					}else if($_SESSION['ngs_samples'] == ''){
						$html .= $this->getRespBoxTableStream("Samples", "samples", ["id","Sample Name","Title","Source","Organism","Molecule","Selected"], ["id","name","title","source","organism","molecule","total_reads"]);
					}else{
						$html .= $this->getRespBoxTableStream("Samples", "samples", ["id","Sample Name","Title","Source","Organism","Molecule", "Barcode", "Description", "Avg Insert Size", "Read Length",
																				"Concentration", "Time", "Biological Replica", "Technical Replica", "Spike-ins", "Adapter",
																				"Notebook Ref", "Notes", "Genotype", "Library Type", "Biosample Type", "Instrument Model", "Treatment Manufacturer","Selected"],
																				["id","name","title","source","organism","molecule","total_reads", "barcode", "description", "avg_insert_size", "read_length",
																				"concentration", "time", "biological_replica", "technical_replica", "spike_ins", "adapter",
																				"notebook_ref", "notes", "genotype", "library_type", "biosample_type", "instrument_model", "treatment_manufacturer"]);
					}
				}
				
	$html.= 		'</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col -->';
	return $html;
	}
	function getInitialMappingTable(){
	$html = '';
	$html.= '<table id="jsontable_initial_mapping" class="table table-hover compact">
			<thead>
			<tr id="tablerow">
				<th>Libname</th>
				<th>Total Reads</th>
			</tr>
			</thead>
		</table>';
		
	return $html;
	}
	function getPipelinesButton(){
	$html = '';
	$num = 0;
	$html.=	'<div id= "pipeline_exp_body" class="box-body" style="display: none;">
			<div class="input-group margin col-md-11">
				<form role="form">
					<div id="masterPipeline">
					</div>
					<input id="addPipe_'.$num.'" type="button" class="btn btn-primary" value="Add Pipeline" onClick="additionalPipes()"/>
				</form>
			</div>';
	return $html;
	}
	function getCustomButton(){
	$html = '';
	$html.= '<div id= "custom_exp_body" class="box-body" style="display: none;">
			<div class="input-group margin col-md-11">
				<form role="form">
					<div id="custom_seq_outer">
					</div>
					<input id="sequence_sets_btn" class="btn btn-primary" type="button" value="Add a Custom Sequence Set" onclick="sequenceSetsBtn()"/>
				</form>
			</div>';
	return $html;
	}
	function sendJScript($segment, $field, $value, $search, $uid, $gids){
	$html="";
	$jsData['theSegment'] = $segment;
		$jsData['theField'] = $field;
		$jsData['theValue'] = $value;
	$jsData['theSearch'] = $search;
	$jsData['uid'] = $uid;
	$jsData['gids'] = $gids;
	$jsData = json_encode($jsData);
	if (!empty($jsData)) {
		$html.="<script type='text/javascript'>\n";
		$html.="var phpGrab = " . $jsData . "\n";
		$html.="</script>\n";
	}
	return $html;
	}
	function getBasePath($BASE_PATH, $API_PATH){
		$html = "";
		$html.="<script type='text/javascript'>\n";
		$html.="var BASE_PATH = '" . $BASE_PATH . "'\n";
		$html.="var API_PATH = '" . $API_PATH . "'\n";
		$html.="</script>\n";
		return $html;
	}
	function groupSelectionOptions($groups){
		$html = "";
		foreach($groups as $g){
				$html.="<option value=".$g['id'].">".$g['name']."</option>";
		}
		return $html;
	}
	function getStaticPermissionsBox($title, $id, $selection, $width){
		$html = "";
		$html = '<div class="col-md-'.$width.'">
				<div class="box box-default">
					<div class="box-header with-border">
					<h3 class="box-title">'.$title.'</h3>';
		$html.= $this->getInfoBox($id);
		$html.= '</div><!-- /.box-header -->
				<div class="box-body">
					<div class="input-group margin col-md-11">
						<form role="form">';
		$html.= $selection;
		$html.= 		'</form>
						</div>
					</div><!-- /.box-body -->
				</div><!-- /.box -->
			</div><!-- /.col -->';
		return $html;
	}
	function getInfoBox($id){
		$left_help_boxes = ['plot_control_panel', 'run_name', 'genomebuild', 'adapter', 'split', 'custom', 'pipeline'];
		if(in_array($id, $left_help_boxes)){
				$width = '200px';
		}else{
				$width = '400px';
		}
		$html = '';
		$html.= '<div class="navbar-right margin">
						<div class="box-tools nav">
						<li class="dropdown user-menu" id="'.$id.'-head" onclick="getInfoBoxData(this.id)">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-info-circle"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="header">
										<h4 style="text-align: center" class="margin">Info</h3>
								</li>
                                <li>
										<div id="'.$id.'_info" class="slimScrollDiv margin" style="position: relative; overflow: hidden; width: '.$width.'; height: auto;">
										</div>
                                </li>
                                <li class="footer">
                                </li>
                            </ul>
                        </li>
					</div>
				</div>';
		return $html;
	}
}
