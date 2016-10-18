<!-- Content Header (Page header) -->
				<section class="content-header">
					<h1>
						NGS Fastlane
						<small>Fast database entry</small>
					</h1>
					<ol class="breadcrumb">
						<li><a href="<?php echo BASE_PATH?>"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="<?php echo BASE_PATH."/search"?>">NGS Pipeline</a></li>
			<li class="active"><?php echo $field?></li
					</ol>
				</section>
			<?php echo $html->sendJScript("fastlane", "", "", "", $uid, $gids); ?>
			
			<div class="modal fade" id="regexModal" tabindex="-1" role="dialog" aria-labelledby="regexModalLabel" aria-hidden="true">
				<div class="modal-dialog">
				  <div class="modal-content">
					<div class="modal-header">
					  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					  <h4 class="modal-title" id="regexModalLabel">File Select</h4>
					</div>
					<form name="regexForm" role="form" method="post">
						<div class="modal-body">
							<fieldset>
								<div class="form-group" style="overflow:scroll">
									<label id="regexLabel">Distinguish the Fastq Files</label>
									<br>
									<p id="regexArea">
										To help determine the correct fastq files listed in your selected directory, Please give the naming scheme for each type of read.
										To list every single-end fastq file, just leave the field blank.  A naming scheme is required for paired-end fastq files.
										<br>
										<br>
										Please refrain from using the '*' regex.
									</p>
									<div class="col-md-11">
										<label id="read_1_label">First Read</label>
										<input id="read_1_input" class="form-control margin" type="text" placeholder="_R1" value="_R1">
										<label id="read_2_label">Second Read</label>
										<input id="read_2_input" class="form-control margin" type="text" placeholder="_R2" value="_R2">
									</div>
								</div>
							</fieldset>   
						</div>
						<div class="modal-footer">
						  <button type="button" class="btn btn-default" data-dismiss="modal" onclick="queryDirectory()">OK</button>
						  <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
						</div>
					</form>
				  </div>
				</div>
			</div><!-- End Regex modal -->
			<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog">
				  <div class="modal-content">
					<div class="modal-header">
					  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					  <h4 class="modal-title" id="myModalLabel">Warning</h4>
					</div>
					<form name="editForm" role="form" method="post">
						<div class="modal-body">
							<fieldset>
								<div class="form-group" style="overflow:scroll">
									<label id="errorLabel"></label>
									<br>
									<p id="errorAreas"></p>
								</div>
							</fieldset>   
						</div>
						<div class="modal-footer">
						  <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
						</div>
					</form>
				  </div>
				</div>
			</div><!-- End Error modal -->
			<div class="modal fade" id="dircheckModal" tabindex="-1" role="dialog" aria-labelledby="myDircheckLabel" aria-hidden="true">
				<div class="modal-dialog">
				  <div class="modal-content">
					<div class="modal-header">
					  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					  <h4 class="modal-title" id="myDircheckLabel">Warning</h4>
					</div>
					<form name="dircheckForm" role="form" method="post">
						<div class="modal-body">
							<fieldset>
								<div class="form-group" style="overflow:scroll">
									<label id="dircheckLabel"></label>
									<br>
									<p id="dircheckAreas"></p>
								</div>
							</fieldset>   
						</div>
						<div class="modal-footer">
						  <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="realSubmit()">OK</button>
						  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						</div>
					</form>
				  </div>
				</div>
			</div><!-- End dircheck modal -->
			
			<form role="form" id="fastlane_form" enctype="multipart/form-data" action="fastlane/process" method="post">
				<section class="content">
					<div class="row">
						<div id="static_info_selection" class="col-md-12">
							<?php echo $html->getStaticSelectionBox("Barcode Seperation", "barcode_sep", "<option>no</option><option>yes</option>", 6)?>
							<script>
								document.getElementById('barcode_sep').setAttribute('onchange', 'expandBarcodeSep()');
							</script>
							<?php echo $html->getStaticSelectionBox("Mate-paired", "spaired", "<option>yes</option><option>no</option>", 6)?>
							<script>
								document.getElementById('spaired').setAttribute('onchange', 'resetSelection()');
							</script>
						</div>
						<div id="barcode_div" class="col-md-12" style="display: none">
							<?php echo $html->getStaticSelectionBox("Barcode Definitions", "Barcode Definitions", "TEXTBOX", 12)?>
						</div>
						<div id="barcode_opt_div" class="col-md-12" style="display: none">
							<?php echo $html->getStaticSelectionBox("Barcode Distance Options", "bar_distance", "<option>1</option>
																				<option>2</option>
																				<option>3</option>
																				<option>4</option>
																				<option>5</option>", 6)?>
							<?php echo $html->getStaticSelectionBox("Barcode Format Options", "bar_format", "<option>5 end read 1</option>
																	<option>3 end read 2 (or 3 end on single end)</option>
																	<option>barcode is in header (illumina casava)</option>
																	<option>no barcode on read 1 of a pair (read 2 must have on 5 end)</option>
																	<option>paired end both reads 5 end</option>", 6)?>
						</div>
						<div id="name_div" class="col-md-12">
							<?php echo $html->getStaticSelectionBox("Experiment Series Name", "series_name", "TEXT", 6)?>
							<?php echo $html->getStaticSelectionBox("Import Name", "lane_name", "TEXT", 6)?>
						</div>
						<div id="input_dir_div" class="col-md-12">
							<?php echo $html->getStaticSelectionBoxWithButton("Input Directory (Full path)", "input_dir", "TEXT", "searchDirectoryModal()", "Search Directory", 12)?>
						</div>
						<div id="input_files_div" class="col-md-12">
							<?php
							echo $html->fastlaneDirectoryFileInput();
							?>
						</div>
						<div id="output_files" class="col-md-12">
							<?php echo $html->getStaticSelectionBox("Process Directory (Full path)", "backup_dir", "TEXT", 12)?>
							<?php echo $html->getStaticSelectionBox("Amazon Bucket", "amazon_bucket", "TEXT", 12)?>
						</div>
					</div><!-- /.row -->
					<div>
						<hr>
					</div>
					<div>
						<?php echo $html->getStaticSelectionBox("Group Selection", "groups", $html->groupSelectionOptions($groups), 7); ?>
						<?php $radiofields=array(
								array('name' => 'only me', 'value' => '3', 'selected' => ''),
								array('name' => 'only my group', 'value' => '15', 'selected' => 'checked'),
								array('name' => 'everyone', 'value' => '32', 'selected' => '')); ?>
						<?php echo $html->getStaticPermissionsBox("Who has permissions to view?", "permissions", $html->getRadioBox($radiofields, 'security_id', 'name'), 7); ?>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="callout callout-info">
								<h4>Before Submission:</h4>
								<p>If submitting a completely new run, please make sure that the process directory does not match an already used process directory.</p>
							</div>
							<input type="button" name="submitted_fastlane" id="submit_fastlane" class="btn btn-primary" onclick="submitFastlaneButton()" value="Submit Fastlane"/>
							<input type="submit" id="hidden_submit_fastlane" class="btn btn-primary" style="display:none"/>
						</div>
					</div><!-- /.row -->
				</section><!-- /.content -->
			</form>
