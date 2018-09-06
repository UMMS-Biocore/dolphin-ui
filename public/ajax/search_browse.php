<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');


function getRespBoxTableStream($title, $table, $fields, $tableKeys){
  $html = '';
  $html.= '<div class="box">
          <div class="box-header">
              <h3 class="box-title">'.$title.'</h3>';
  // $html.= $this->getInfoBox($table);
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



require_once("../../library/html.class.php");
require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
require_once("../api/funcs.php");

if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$query = new dbfuncs();
$funcs = new funcs();

$p = '';


if (isset($_GET['p'])){$p = $_GET['p'];}

if($p == 'getSearchSamples')
{
//var_dump(get_defined_functions());
  if(!isset($_SESSION['ngs_samples']) || ($_SESSION['ngs_samples'] == '') ){
    echo getRespBoxTableStream("Samples", "samples", ["id","Sample Name","Title","Source","Organism","Molecule","Backup","Selected"], ["id","name","title","source","organism","molecule","backup","total_reads"]);
  } else{
    echo getRespBoxTableStream("Samples", "samples", ["id","Sample Name","Title","Source","Organism","Molecule", "Barcode", "Backup", "Description", "Avg Insert Size", "Read Length",
    														"Concentration", "Time", "Biological Replica", "Technical Replica", "Spike-ins", "Adapter",
    														"Notebook Ref", "Notes", "Genotype", "Library Type", "Biosample Type", "Instrument Model", "Treatment Manufacturer","Selected"],
    														["id","name","title","source","organism","molecule","backup","total_reads", "barcode", "description", "avg_insert_size", "read_length",
    														"concentration", "time", "biological_replica", "technical_replica", "spike_ins", "adapter",
    														"notebook_ref", "notes", "genotype", "library_type", "biosample_type", "instrument_model", "treatment_manufacturer"]);
  }
}
