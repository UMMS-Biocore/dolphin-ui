<?php
 
class NgsimportController extends VanillaController {
	private $username;
	function beforeAction() {

	}
 
	function index() {
		$this->username=$_SESSION['user'];
		$this->set('title','NGS Excel Import');
		$this->set('groups',$this->Ngsimport->getGroups($this->username));
        
        $this->set('uid', $_SESSION['uid']);
        $gids = $this->Ngsimport->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
	}
	function check() {
		$this->username=$_SESSION['user'];
		$this->set('title','NGS Excel Import');
		$this->set('groups',$this->Ngsimport->getGroups($this->username));
        
        $this->set('uid', $_SESSION['uid']);
        $gids = $this->Ngsimport->getGroup($_SESSION['user']);
        $this->set('gids', $gids);
		
		/** Include path **/
		set_include_path('../includes/excel/Classes/');

		/** PHPExcel_IOFactory */
		include 'PHPExcel/IOFactory.php';
		require_once '../includes/dbfuncs.php';

		$target_file ='../tmp/files/'.$_SESSION['user']."_".date('Y-m-d-H-i-s').".xls";
		$_SESSION['file_upload'] = $target_file;
		$_SESSION['file_upload_size'] = $_FILES["excelFile"]["size"];
		$_SESSION['group_id'] = $_POST['group_id'];
		$_SESSION['security_id'] = $_POST['security_id'];
		$text = "Files size: ".$_FILES["excelFile"]["size"]."</br>";
		if($_FILES["excelFile"]["size"] > 0 && $_FILES["excelFile"]["size"]<500000){
			move_uploaded_file($_FILES["excelFile"]["tmp_name"], $target_file);
			
			$inputFileType = 'Excel5';
			$inputFileName = $target_file;
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$worksheetData = $objReader->listWorksheetInfo($inputFileName);
			$objPHPExcel = $objReader->load($inputFileName);
			
			$newCheck = "";
			$text = "<h3>Processing your spreadsheet...</h3>";
			$directory = "";
			$ngs=new Ngsimport();
			$objPHPExcel->setActiveSheetIndexByName("METADATA");
			$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			for ($i=1;$i<=$worksheetData[0]['totalRows'];$i++){
				if($sheetData[$i]["A"]=="backup directory"){
					$directory = $sheetData[$i]["B"];
				}else if ($sheetData[$i]["A"]=="processed directory"){
					$directory = $sheetData[$i]["B"];
				}else if ($sheetData[$i]["A"]=="process directory"){
					$directory = $sheetData[$i]["B"];
				}
			}
			$newCheck = $ngs->checkNewRun($directory);
			if($newCheck != 'pass'){
				$text="<h3>This run directory already exists, do you wish to continue?</h3>";
			}
		}else{
			$text = "<h3>The file size is either too large to import or no file was selected.</h3>";
		}
		$this->set('mytext', $text);
		
	}
	function process() {
		$this->username=$_SESSION['user'];
		$this->set('title','NGS Excel Import');
		$this->set('groups',$this->Ngsimport->getGroups($this->username));
		$this->set('uid', $_SESSION['uid']);
		$gids = $this->Ngsimport->getGroup($_SESSION['user']);
		$this->set('gids', $gids);
		$text="";
		$inputFileName=$_SESSION['file_upload'];
		$fileSize = $_SESSION['file_upload_size'];
		if($fileSize > 0 && $fileSize<500000){
			/** Include path **/
			set_include_path('../includes/excel/Classes/');

			/** PHPExcel_IOFactory */
			include 'PHPExcel/IOFactory.php';
			require_once '../includes/dbfuncs.php';

			$inputFileType = 'Excel5';
			$text.='Loading excel file <br />';
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);

			$text.='<hr />';

			$worksheetData = $objReader->listWorksheetInfo($inputFileName);
			$objPHPExcel = $objReader->load($inputFileName);

			$text.='<h3>Worksheet Information</h3>';
			$text.= '<h5>Checking for proper excel formatting:</h5><BR>';
			$text.='<ol>';
			
			$passed_final_check = true;
			
			$ngs=new Ngsimport();
			foreach ($worksheetData as $worksheet) {
				$objPHPExcel->setActiveSheetIndexByName($worksheet['worksheetName']);
				$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
				$parseArray=$ngs->parseExcel($_SESSION["group_id"], $_SESSION["security_id"], $worksheet, $sheetData, $passed_final_check);
				$passed_final_check = $parseArray[0];
				$text.=$parseArray[1];
			}
			$text.= '</ol>';
			
			if($passed_final_check){
				$text.= '<h5>Updating/Inserting the database</h5><BR>';
				$text.= '<ol>';
				foreach ($worksheetData as $worksheet) {
					$objPHPExcel->setActiveSheetIndexByName($worksheet['worksheetName']);
					$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
					$text.=$ngs->finalizeExcel($worksheet, $sheetData);
				}
				$text.='</ol>';
				
				$text.='<div class="callout callout-info lead"><h4>We are currently processing your samples to obtain read counts and additional information.<br><br>
						You can check the status of these initial runs on your NGS Status page.</h4></div>';
				$text.= '<div>
						<input type="button" class="btn btn-primary" value="Go to Status" onclick="sendToStatus()">
						</div>';
			}else{
				$text.=$ngs->errorText("<BR><BR>Excel import aborted due to errors, see above<BR>");
				$text.= $ngs->errorText("If you're not a galaxy group member, visit <a href='http://umassmed.edu/biocore/resources/galaxy-group/'>here</a> for instructions on how to become a member.<br>");
				$text.= $ngs->errorText("Please visit our <a href='http://dolphin.readthedocs.org/en/master/dolphin-ui/excelimport.html'>documentation</a> for additional help.");
			}
		}
		unset($_SESSION['file_upload']);
		unset($_SESSION['file_upload_size']);
		unset($_SESSION['group_id']);
		unset($_SESSION['security_id']);
		$this->set('mytext', $text);
	}

	function afterAction() {

	}

}
