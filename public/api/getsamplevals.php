<?php
error_reporting(E_ALL);
ini_set('report_errors','on');
require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
require_once("class.csv-to-api.php");
$query = new dbfuncs();

#
# Sample Definitions:
# samples=sample_id1,sample_id2;run_id1:sample_id3,sample_id4;run_id3
# Examples:
# 1. Summary Example:
# API_PATH/api/getsamplevals.php?samples=8,10,11;6:13,14;7&file=counts/rRNA.summary.tsv&type=summary&format=html
#
#
# 2. Count Example:
# API_PATH/api/getsamplevals.php?samples=8,10,11;6:13,14;7&file=counts/rRNA.counts.tsv&common=id,len&key=id&format=html
#
#
# 3. RSEM Example:
# a. API_PATH/api/getsamplevals.php?samples=8,10,11;6:13,14;7&file=rsem/isoforms_expression_tpm.tsv&common=gene,transcript&key=transcript&format=html
# b. API_PATH/api/getsamplevals.php?samples=control_rep1,c_rep1,e_rep1&file=rsem/genes_expression_expected_count.tsv&common=gene,transcript&key=gene&type=rsem&format=html
# 
#
# 4. DESeq EXample:
# a.API_PATH/api/getsamplevals.php?samples=8,10,11;6:13,14;7&file=DESeq2RSEM1/alldetected_genes.tsv&common=name,&keepcols=padj,log2FoldChange&key=name&type=rsem&format=html
# b.API_PATH/api/getsamplevals.php?samples=8,10,11;6:13,14;7&file=DESeq2RSEM1/alldetected_genes.tsv&common=name,&keepcols=padj,log2FoldChange&key=name&type=rsem&format=html&filter=Fgf21,0610005C13Rik,Crebbp
#

$samples= $_GET['samples']; # Ex: samples=1:10,13:8

$format=$_GET['format']; # Ex: format:html [html|json|json2]
$file=$_GET['file']; # Ex: DESeq2RSEM1/alldetected_genes.tsv
$type=$_GET['type'];#summary or others
$commonfields=$_GET['common']; # Common fields that will hold the values from the first file
$keyfield=$_GET['key']; # index field specific to the table
$keepcols=$_GET['keepcols']; # This will keep the columns even they are common in the other files
$filter=$_GET['filter']; # This will be used to filter the values in the key field and report

$keepcols_array=explode(',', $keepcols);
$filter_array=explode(',', $filter);
#print $samplenames;

$data=$query->getSampleQuery($samples);


#header('Cache-Control: no-cache, must-revalidate');
#header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
#header('Content-type: application/json');

$dat=json_decode($data);
$sample_array=array();
$multi_run_array = array();
for ($i=0; $i<sizeof($dat); $i++)
{
    $c[$dat[$i]->wkey] = $dat[$i]->id;
    $b[$dat[$i]->wkey].=$dat[$i]->samplename . "_run" . $dat[$i]->id.",";
    if(strpos($file,"RSeQC") > -1){
        $a[$dat[$i]->wkey].=$dat[$i]->samplename.".bam,";
        array_push($sample_array, $dat[$i]->samplename.".bam");
    }else{
        $a[$dat[$i]->wkey].=$dat[$i]->samplename.",";
        array_push($sample_array, $dat[$i]->samplename);
    }
}
$new_data=array();
$title=array();
foreach ($a as $i => $row){
    if ($type!="summary"){
        $fields="&fields=";
        if($commonfields != ""){
            $fields.="$commonfields,".str_replace("-", "_", $row);
        }
        if($keepcols != ""){
            $fields.="$keepcols,".str_replace("-", "_", $row);
        }
    }
        $sample_breakdown = explode(",",$b[$i]);
    array_pop($sample_breakdown);
    $url=API_PATH."/public/api/?source=".API_PATH."/public/pub/".$i."/$file&$fields&$format";
    $json = file_get_contents($url);
    $dat1 = json_decode($json, $array=TRUE);
    foreach ($dat1[0] as $key => $value){
        $new_key = array_search($key."_run".$c[$i], $sample_breakdown);
        if($new_key > -1){
                $key = $sample_breakdown[$new_key];
        }
        if (!in_array($key, $title) || in_array($key, $keepcols_array)){
            array_push($title, $key);
        }
    }
    if($format == 'json2'){
        $new_data["title"]=$title;
    }
    for ($j=0; $j<sizeof($dat1); $j++){
        $row = $dat1[$j];
        if ($type=="summary"){
            if(in_array($row["File"], $sample_array)){
                $new_data[$row["File"]]=$row;
            }elseif(in_array($row["Sample"], $sample_array)){
                $new_data[$row["Sample"]]=$row;
            }
        }else{
            if ((isset($filter) && in_array($row[$keyfield], $filter_array)) || !isset($filter)){
                if(!isset($new_data[$row[$keyfield]])){
                   $oc[$row[$keyfield]]=array();
                   $new_data[$row[$keyfield]]=array();
                }
                foreach ($row as $key => $value){
                        $new_key = array_search($key."_run".$c[$i], $sample_breakdown);
                        if($new_key > -1){
                                $key = $sample_breakdown[$new_key];
                        }
                    if (!in_array($key, $oc[$row[$keyfield]]) || in_array($key, $keepcols_array) ){
                      array_push($new_data[$row[$keyfield]], $value);
                      array_push($oc[$row[$keyfield]] , $key);
                    }
                }
            }
        }
    }
}

$final_array=array();
foreach ($new_data as $nd_key => $nd_value){
    $new_object=array();
    foreach($nd_value as $v_key => $v_value){
        if ($type=="summary"){
            $new_object[$v_key] = $v_value;
        }else{
            $new_object[$oc[$nd_key][$v_key]] = $v_value;
        }
    }
    array_push($final_array, $new_object);
}
if($format == 'json2' || $format == 'JSON2'){
    $format = 'json2_5';
}
$function = 'object_to_' . $format;
$api = new CSV_To_API();
if($format == 'json2_5'){
    echo $api->$function($new_data);
}else{
    echo $api->$function($final_array);    
}
exit;

