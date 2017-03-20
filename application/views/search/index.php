		<!-- Content Header (Page header) -->
				<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
					  <div class="modal-content">
						<div class="modal-header">
						  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						  <h4 class="modal-title" id="myModalLabel">Delete Selected</h4>
						</div>
						<form name="editForm" role="form" method="post">
							<div class="modal-body">
								<fieldset>
									<div class="form-group" style="overflow:scroll">
										<label id="deleteLabel"></label>
										<br>
										<p id="deleteAreas"></p>
									</div>
								</fieldset>
							</div>
							<div class="modal-footer">
							  <button type="button" id="confirmPatchButton" class="btn btn-success" data-dismiss="modal" onclick="" style="display:none">Confirm</button>
							  <button type="button" id="confirmDeleteButton" class="btn btn-danger" data-dismiss="modal" onclick="confirmDeletePressed()">Confirm</button>
							  <button type="button" id="cancelDeleteButton" class="btn btn-default" data-dismiss="modal" onclick="cancelDeletePressed()">Cancel</button>
							</div>
						</form>
					  </div>
					</div>
				</div><!-- End Delete modal -->
				<div class="modal fade" id="permsModal" tabindex="-1" role="dialog" aria-labelledby="myPermsModal" aria-hidden="true">
					<div class="modal-dialog">
					  <div class="modal-content">
						<div class="modal-header">
						  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						  <h4 class="modal-title" id="myPermsModal">Change the Data's Group</h4>
						</div>
						<form name="editForm" role="form" method="post">
							<div class="modal-body">
								<fieldset>
									<div class="form-group" style="overflow:scroll">
										<label id="permsLabel"></label>
										<br>
										<div id="permsDiv">

										</div>
									</div>
								</fieldset>
							</div>
							<div class="modal-footer">
							  <button type="button" id="confirmPermsButton" class="btn btn-primary" onclick="confirmPermsPressed()">Confirm</button>
							  <button type="button" id="cancelPermsButton" class="btn btn-default" data-dismiss="modal">Cancel</button>
							</div>
						</form>
					  </div>
					</div>
				</div><!-- End Perms modal -->
				<section class="content-header">
					<h1>
						NGS Browser
						<small>Project and experiment search</small>
					</h1>
					<ol class="breadcrumb">
						<li><a href="<?php echo BASE_PATH?>"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="<?php echo BASE_PATH."/search"?>">NGS Browser</a></li>
			<li class="active"><?php echo $field?></li
					</ol>
				</section>
				<!-- Main content -->
				<section class="content">
					<div class="row">
					<div class="toggle toggle-modern pull-right" style="margin:25px;"></div>
						<div class="col-md-3">

			<div class="box box-solid">
				<div class="box-header with-border">
				<h3 class="box-title">Browse</h3>
			<div class="panel-tools pull-right">
			<button class="btn btn-primary" onclick="returnToIndex();">
				<i class="fa fa-fast-backward"></i>
			</button>
			</div>
				</div><!-- /.box-header -->
				<div class="box-body">
				<div class="box-group" id="accordion">
					<!-- we are adding the .panel class so bootstrap.js collapse plugin detects it -->
					<?php echo $html->sendJScript("index", "", "", "", $uid, $gids); ?>
					<?php echo $html->getAccordion("Assay", $assay, "")?>
					<?php echo $html->getAccordion("Organism", $organism, "")?>
					<?php echo $html->getAccordion("Molecule", $molecule, "")?>
					<?php echo $html->getAccordion("Source", $source, "")?>
					<?php echo $html->getAccordion("Genotype", $genotype, "")?>
				</div>
				</div><!-- /.box-body -->
		</div><!-- /.box -->
		<div>
			<?php echo $html->getDolphinBasket()?>
		</div>
		<!-- END ACCORDION & CAROUSEL-->
			</div><!-- /.col (LEFT) -->
			<?php echo $html->getSubmitBrowserButton()?>

						<div id="top_search_section" class="col-md-9">
							<ul id="tabList" class="nav nav-tabs">
					          <li class="active">
					            <a href="#browse_experiments" data-toggle="tab" aria-expanded="true">Experiments</a>
					          </li>
					          <li class>
					            <a href="#browse_imports" data-toggle="tab" aria-expanded="true">Imports</a>
					          </li>
					          <li class>
					            <a href="#browse_samples" onclick="fillSampleTable();" data-toggle="tab" aria-expanded="true">Samples</a>
					          </li>
					        </ul>
							<div class="tab-content">
								<div class="tab-pane active" id="browse_experiments">
									<div id="browse_experiment_data_table" class="margin">
										<?php if(!isset($_SESSION['ngs_experiments']) || ($_SESSION['ngs_experiments'] == '') ){
											echo $html->getRespBoxTableStream("Experiments", "experiments", ["id","Series Name","Summary","Design", "Selected"], ["id","experiment_name","summary","design", ""]);
										} else{
											echo $html->getRespBoxTableStream("Experiments", "experiments", ["id","Series Name","Summary","Design", "Lab","Organization","Grant", "Selected"], ["id","experiment_name","summary","design", "lab","organization","grant", ""]);
										}?>
									</div>
								</div>
								<div class="tab-pane" id="browse_imports">
									<div id="browse_import_data_table" class="margin">
										<?php 
											$title_all_import = "Imports";
											$table_all_import = "lanes";
											$table_filtered_import = "lanes_filtered";
											$title_filtered_import = "Filtered Imports";
											$fields_basic_import = ["id","Import Name","Facility","Total Reads","Total Samples","Selected"];
											$tableKeys_basic_import = ["id","name","facility", "total_reads", "total_samples",""];
											$fields_extended_import = ["id","Import Name","Facility","Total Reads","Total Samples", "Cost", "Phix Requested", "Phix in Lane", "Notes", "Selected"];
											$tableKeys_extended_import = ["id","name","facility", "total_reads", "total_samples", "cost", "phix_requested", "phix_in_lane", "notes", ""];

										if(!isset($_SESSION['ngs_lanes']) || ($_SESSION['ngs_lanes'] == '') ){
											echo $html->getRespBoxTableStream($title_all_import, $table_all_import, $fields_basic_import, $tableKeys_basic_import);
										} else{
											echo $html->getRespBoxTableStream($title_all_import, $table_all_import, $fields_extended_import, $tableKeys_extended_import);
										}
										?>
									</div>
									<div id="imports_filtered_by_selection" class="margin">
										<?php 
										if(!isset($_SESSION['ngs_lanes']) || ($_SESSION['ngs_lanes'] == '') ){
											echo $html->getRespBoxTableStreamNoExpand($title_filtered_import, $table_filtered_import, $fields_basic_import, $tableKeys_basic_import);
										} else{
											echo $html->getRespBoxTableStreamNoExpand($title_filtered_import, $table_filtered_import, $fields_extended_import, $tableKeys_extended_import);
										}
										 ?>
									</div>
								</div>
								<div class="tab-pane" id="browse_samples">
									<div id="browse_sample_data_table" class="margin">
										<?php 
											$title_all_sample = "Samples";
											$table_all_sample = "samples";
											$table_filtered_sample = "samples_filtered";
											$title_filtered_sample = "Filtered Samples";
											$fields_basic_sample = ["id","Sample Name","Title","Source","Organism","Molecule","Backup","Selected"];
											$tableKeys_basic_sample = ["id","name","title","source","organism","molecule","backup","total_reads"];
											$fields_extended_sample = ["id","Sample Name","Title","Source","Organism","Molecule", "Barcode", 
												"Backup", "Description", "Avg Insert Size", "Read Length", "Concentration", "Time", 
												"Biological Replica", "Technical Replica", "Spike-ins", "Adapter", "Notebook Ref", "Notes", 
												"Genotype", "Library Type", "Biosample Type", "Instrument Model", "Treatment Manufacturer","Selected"];
											$tableKeys_extended_sample = ["id","name","title","source","organism","molecule","backup","total_reads",
												"barcode", "description", "avg_insert_size", "read_length", "concentration", "time", 
												"biological_replica", "technical_replica", "spike_ins", "adapter", "notebook_ref", "notes",
												"genotype", "library_type", "biosample_type", "instrument_model", "treatment_manufacturer"];

											if(!isset($_SESSION['ngs_samples']) || ($_SESSION['ngs_samples'] == '') ){
												echo $html->getRespBoxTableStream($title_all_sample, $table_all_sample, $fields_basic_sample, $tableKeys_basic_sample);
											} else{
												echo $html->getRespBoxTableStream($title_all_sample, $table_all_sample, $fields_extended_sample, $tableKeys_extended_sample);
											}
										?>
									</div>
									<div id="samples_filtered_by_selection" class="margin">
										<?php 
										if(!isset($_SESSION['ngs_samples']) || ($_SESSION['ngs_samples'] == '') ){
											echo $html->getRespBoxTableStreamNoExpand($title_filtered_sample, $table_filtered_sample, $fields_basic_sample, $tableKeys_basic_sample);
										} else{
											echo $html->getRespBoxTableStreamNoExpand($title_filtered_sample, $table_filtered_sample, $fields_extended_sample, $tableKeys_extended_sample);
										}
										 ?>										
									</div>
								</div>
							</div>
							<input type="button" class="btn btn-success margin pull-right" value="Edit Selected" onClick="editMultipleSamples()"/>
							<div class="modal fade" id="editMultipleSamplesModal" tabindex="-1" role="dialog" aria-labelledby="editMultipleSamplesModalLabel" aria-hidden="true">
								<div class="modal-dialog">
								  <div class="modal-content">
									<div class="modal-header">
									  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									  <h4 class="modal-title" id="editMultipleSamplesModalLabel">Edit Multiple Samples</h4>
									</div>
									<form name="editForm" role="form" method="post">
										<div class="modal-body">
											<fieldset>
												<div class="form-group" style="overflow:scroll">

													<div id="selectedSamplesList">
														<label>Selected Samples:</label>
													</div>
													<div id="selectFieldsToModify"></div>
													<div id="editMultipleSamplesAdd">

													</div>
												</div>
											</fieldset>
										</div>
										<div class="modal-footer">
										  <button type="button" id="editSamplesConfirm" class="btn btn-primary" data-dismiss="modal" onclick="updateSelectedSamples();">Update</button>
										  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
										</div>
									</form>
								  </div>
								</div>
							</div><!-- End Edit Selected modal -->
						</div><!-- /.col (RIGHT) -->
						<div id="outer_e" class="col-md-9 pull-right"><div id="e_details" class="col-md-9"></div></div>
						<div id="outer_i" class="col-md-9 pull-right"><div id="i_details" class="col-md-9"></div></div>
						<div id="outer_s" class="col-md-9 pull-right"><div id="s_details" class="col-md-9"></div></div>
						<div id="outer_top" class="col-md-9 pull-right" style="margin-top: 50px;">
							<div id="back_to_top" class="col-md-9">
								<a href="#">Back to top</a>
							</div>
						</div>
					</div><!-- /.row -->
				</section><!-- /.content -->