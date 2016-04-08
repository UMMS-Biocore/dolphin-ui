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
			<form role="form" enctype="multipart/form-data" action="fastlane/process" method="post">
				<section class="content">
					<div class="row">
						<div id="static_info_selection" class="col-md-12">
							<?php echo $html->getStaticSelectionBox("Barcode Seperation", "barcode_sep", "<option>no</option>
																				<option>yes</option>", 6)?>
							<script>
								document.getElementById('barcode_sep').setAttribute('onchange', 'expandBarcodeSep()');
							</script>
							<?php echo $html->getStaticSelectionBox("Mate-paired", "spaired", "<option>yes</option><option>no</option>", 6)?>
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
							<?php echo $html->getStaticSelectionBoxWithButton("Input Directory (Full path)", "input_dir", "TEXT", "queryDirectory()", "Search Directory", 12)?>
						</div>
						<div id="input_files_div" class="col-md-12">
							<?php
							$manual = $html->fastlaneManualFileInput();
							$directory = $html->fastlaneDirectoryFileInput();
							echo $html->getStaticTabbedSelectionBox("Input Files", "input_files", ['Manual', 'Directory'], [$manual, $directory], 12);
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
							<input type="submit" name="submitted_fastlane" id="submit_fastlane" class="btn btn-primary" onclick="submitFastlaneButton()" innerHTML="Submit Fastlane"/>
						</div>
					</div><!-- /.row -->
				</section><!-- /.content -->
			</form>
