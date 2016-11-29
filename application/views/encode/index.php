				<style>
					.warning {
						background-color: #F99 !important;
					}
					div.combobox	{position: relative;}
					div.combobox	.cb_identifier		{height: 34px;}
					div.combobox	div.dropdownlist	{display: none;width: 200px;
						border: solid 1px #000;background-color: #fff;
						height: 200px;overflow: auto;position: absolute;}
					div.combobox	.dropdownlist	a	{display: block;text-decoration: none;
						color: #000;padding: 1px;height: auto;cursor: default}
					div.combobox	.dropdownlist	a.light	{color: #fff;
						background-color: #007}
					.dataTable {
						width: 100% !Important;
					}
				</style>
				<!-- Content Header (Page header) -->
				<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
					  <div class="modal-content">
						<div class="modal-header">
						  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						  <h4 class="modal-title" id="myModalLabel">Encode Submission</h4>
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
				</div><!-- End modal -->
				<div class="modal fade" id="addTreatmentModal" tabindex="-1" role="dialog" aria-labelledby="myTreatmentModalLabel" aria-hidden="true">
					<div class="modal-dialog">
					  <div class="modal-content">
						<div class="modal-header">
						  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						  <h4 class="modal-title" id="myTreatmentModalLabel">Add Treatment</h4>
						</div>
						<form name="editForm" role="form" method="post">
							<div class="modal-body">
								<fieldset>
									<div class="form-group" style="overflow:scroll">
										<label id="addSampleTreatmentLabel">Select Samples to add a Treatment to:</label>
										<br>
										<select id="addSampleTreatment" type="select-multiple" multiple size="10" style="width:100%"></select>
										<br>
										<label id="addNameTreatmentLabel">Name the Treatment:</label>
										<br>
										<input id="addNameTreatment" type="text" class="form-control">
									</div>
								</fieldset>   
							</div>
							<div class="modal-footer">
							  <button type="button" id="treatmentConfirm" class="btn btn-primary" data-dismiss="modal" onclick="createNewData('Treatment')">Add</button>
							  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
							</div>
						</form>
					  </div>
					</div>
				</div><!-- End Treatment modal -->
				<div class="modal fade" id="addAntibodyModal" tabindex="-1" role="dialog" aria-labelledby="myAntibodyModalLabel" aria-hidden="true">
					<div class="modal-dialog">
					  <div class="modal-content">
						<div class="modal-header">
						  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						  <h4 class="modal-title" id="myAntibodyModalLabel">Add Antibody</h4>
						</div>
						<form name="editForm" role="form" method="post">
							<div class="modal-body">
								<fieldset>
									<div class="form-group" style="overflow:scroll">
										<label id="addSampleAntibodyLabel">Select Samples to add a Antibody to:</label>
										<br>
										<select id="addSampleAntibody" class="form-control" type="select-multiple" multiple size="10" style="width:100%"></select>
										<br>
										<label id="addNameAntibodyLabel">Name the Antibody:</label>
										<br>
										<input id="addNameAntibody" class="form-control" type="text" class="form-control">
									</div>
								</fieldset>   
							</div>
							<div class="modal-footer">
							  <button type="button" id="antibodyConfirm" class="btn btn-primary" data-dismiss="modal" onclick="createNewData('Antibody')">Add</button>
							  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
							</div>
						</form>
					  </div>
					</div>
				</div><!-- End Antibody modal -->
				<section class="content-header">
					<h1>
						Encode Viewing/Submission
						<small>Projects and experiments submitted to Encode</small>
					</h1>
					<ol class="breadcrumb">
						<li><a href="<?php echo BASE_PATH?>"><i class="fa fa-dashboard"></i> Home</a></li>
						<li><a href="<?php echo BASE_PATH."/search"?>">NGS Browser</a></li>
						<li class="active"><?php echo $field?></li>
					</ol>
				</section>
				<?php echo $html->sendJScript("encode", "", "", "", $uid, $gids); ?>
				<section class="content">
					<div class="row">
						<div class="col-md-12">
							<!-- general form elements -->
							<div class="nav-tabs-custom">
								<ul id="tabList" class="nav nav-tabs">
									<li class="active">
										<a href="#samples_tab" data-toggle="tab" aria-expanded="true">Sample Selection</a>
									</li>
									<li class>
										<a href="#donors_tab" data-toggle="tab" aria-expanded="true">Donors</a>
									</li>
									<li class>
										<a href="#experiments_tab" data-toggle="tab" aria-expanded="true">Experiments</a>
									</li>
									<li class>
										<a href="#treatments_tab" data-toggle="tab" aria-expanded="true">Treatments</a>
									</li>
									<li class>
										<a href="#biosamples_tab" data-toggle="tab" aria-expanded="true">Biosamples</a>
									</li>
									<li class>
										<a href="#libraries_tab" data-toggle="tab" aria-expanded="true">Libraries</a>
									</li>
									<li class>
										<a href="#antibodies_tab" data-toggle="tab" aria-expanded="true">Antibodies</a>
									</li>
									<li class>
										<a href="#replicates_tab" data-toggle="tab" aria-expanded="true">Replicates</a>
									</li>
									<li class>
										<a href="#files_tab" data-toggle="tab" aria-expanded="true">Files</a>
									</li>
								</ul>
								<div class="tab-content margin">
									<div class="tab-pane active" id="samples_tab">
											<?php
												echo $html->getRespBoxTable_ng("Selected Samples", "encode_selected_samples",
																		   "<th>id</th><th>Sample Name</th><th>Donor</th><th>Source</th><th>Organism</th><th>Molecule</th><th>Lab</th><th>Grant</th><th>Removal</th><th>Selected</th>");
											?>
												<input type="button" class="btn btn-primary margin" value="Change Selected" onClick="changeValuesEncode('selected','sample',this,event)"/>
												<input type="button" class="btn btn-primary margin" value="Change All" onClick="changeValuesEncode('all','sample',this,event)"/>
											<?php	
												#Samples
												echo $html->getRespBoxTableStreamNoExpand("Samples", "samples",
																						  ["id","Sample Name","Title","Source","Organism","Molecule","Backup","Selected"],
																						  ["id","name","title","source","organism","molecule","backup","total_reads"]);
											?>
									</div>
									<div class="tab-pane" id="donors_tab">
										<?php
											#Donors
											echo $html->getRespBoxTable_ng("Donors", "encode_donors",
																		   "<th>Donor</th><th>Life Stage</th><th>Age</th><th>Sex</th><th>Donor Acc</th><th>Donor UUID</th><th>Selected</th>");
										?>
										<input type="button" class="btn btn-primary margin" value="Change Selected" onClick="changeValuesEncode('selected','donor',this,event)"/>
										<input type="button" class="btn btn-primary margin" value="Change All" onClick="changeValuesEncode('all','donor',this,event)"/>
									</div>
									<div class="tab-pane" id="experiments_tab">
										<?php
											#Experiments
											echo $html->getRespBoxTable_ng("Experiments", "encode_experiments",
																		   "<th>Sample</th><th>Assay Term Name</th><th>Source</th><th>Description</th><th>Experiment Acc</th><th>Experiment UUID</th><th>Selected</th>");
										?>
										<input type="button" class="btn btn-primary margin" value="Change Selected" onClick="changeValuesEncode('selected','experiment',this,event)"/>
										<input type="button" class="btn btn-primary margin" value="Change All" onClick="changeValuesEncode('all','experiment',this,event)"/>
									</div>
									<div class="tab-pane" id="treatments_tab">
										<?php
											#Treatments
											echo $html->getRespBoxTable_ng("Treatments", "encode_treatments",
																		   "<th>Name</th><th>Treatment Term Name</th><th>Treatment Term Id</th><th>Treatment Type</th><th>Concentration</th><th>Concentration Units</th><th>Duration</th><th>Duration Units</th><th>Selected</th>");
										?>
										<input type="button" class="btn btn-primary margin" value="Change Selected" onClick="changeValuesEncode('selected','treatment',this,event)"/>
										<input type="button" class="btn btn-primary margin" value="Change All" onClick="changeValuesEncode('all','treatment',this,event)"/>
										<input type="button" class="btn btn-primary margin pull-right" value="Add Treatment" onClick="addTreatment()"/>
									</div>
									<div class="tab-pane" id="biosamples_tab">
										<?php
											#Biosamples
											echo $html->getRespBoxTable_ng("Biosamples", "encode_biosamples",
																		   "<th>Sample</th><th>Treatment</th><th>Biosample Term Name</th><th>Biosample Term Id</th><th>Biosample Type</th><th>Date Submitted</th><th>Date Received</th><th>Biosample Acc</th><th>Biosample UUID</th><th>Selected</th>");
										?>
										<input type="button" class="btn btn-primary margin" value="Change Selected" onClick="changeValuesEncode('selected','biosample',this,event)"/>
										<input type="button" class="btn btn-primary margin" value="Change All" onClick="changeValuesEncode('all','biosample',this,event)"/>
									</div>
									<div class="tab-pane" id="libraries_tab">
										<?php
											#Libraries
											echo $html->getRespBoxTable_ng("Libraries", "encode_libraries",
																		   "<th>Sample</th><th>Nucleic Acid Term Name</th><th>Crosslinking Method</th><th>Spike-ins Used</th><th>Extraction Method</th><th>Fragmentation Method</th><th>Size Range</th><th>Read Size</th><th>Sequencing Platform</th><th>Machine Name</th><th>Flowcell</th><th>Flowcell Lane</th><th>Library Acc</th><th>Library UUID</th><th>Selected</th>");
										?>
										<input type="button" class="btn btn-primary margin" value="Change Selected" onClick="changeValuesEncode('selected','library',this,event)"/>
										<input type="button" class="btn btn-primary margin" value="Change All" onClick="changeValuesEncode('all','library',this,event)"/>
									</div>
									<div class="tab-pane" id="antibodies_tab">
										<?php
											#Antibody Lots
											echo $html->getRespBoxTable_ng("Antibodies", "encode_antibodies",
																		   "<th>Target</th><th>Source</th><th>Product Id</th><th>Lot Id</th><th>Host Organism</th><th>Clonality</th><th>Isotype</th><th>Purifications</th><th>URL</th><th>Antibody UUID</th><th>Selected</th>");
										?>
										<input type="button" class="btn btn-primary margin" value="Change Selected" onClick="changeValuesEncode('selected','antibody',this,event)"/>
										<input type="button" class="btn btn-primary margin" value="Change All" onClick="changeValuesEncode('all','antibody',this,event)"/>
										<input type="button" class="btn btn-primary margin pull-right" value="Add Antibody" onClick="addAntibody()"/>
									</div>
									<div class="tab-pane" id="replicates_tab">
										<?php
											#Replicates
											echo $html->getRespBoxTable_ng("Replicates", "encode_replicates",
																		   "<th>Sample</th><th>Antibody</th><th>Biological Replicate Number</th><th>Technical Replicate Number</th><th>Replicate UUID</th><th>Selected</th>");
										?>
										<input type="button" class="btn btn-primary margin" value="Change Selected" onClick="changeValuesEncode('selected','replicate',this,event)"/>
										<input type="button" class="btn btn-primary margin" value="Change All" onClick="changeValuesEncode('all','replicate',this,event)"/>
									</div>
									<div class="tab-pane" id="files_tab">
										<div class="row margin">
											<?php
												#Replicates
												echo $html->getRespBoxTable_ng("Run Selection", "encode_runs",
																			   "<th>Sample</th><th>Run Selection</th><th>Run Directory</th><th>Run Description</th>");
											?>
										</div>
										<div class="row">
											<div class="col-md-3">
												<div class="box">
													<div class="box-header">
														<h3 class="box-title">File Selection</h3>
													</div>
													<div class="box-body">
														<select id="addSampleFiles" class="form-control" type="select-multiple" multiple size="10" style="width:100%"></select>
													</div>
												</div>
											</div>
											<div class="col-md-9">
												<?php
													#Replicates
													echo $html->getRespBoxTable_ng("Submission Order", "encode_file_order",
																				   "<th>File</th><th>Parent File</th><th>File Type</th>");
												?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>
				<section>
					<div class="margin">
						<input type="button" id="submitMeta" class="btn btn-primary" name="pipeline_send_button" value="Submit Meta-data" onClick="checkForEncodeSubmission('metadata')"/>
						<input type="button" id="submitBoth" class="btn btn-primary" name="pipeline_send_button" value="Submit With Files" onClick="checkForEncodeSubmission('both')"/>
						<input type="button" class="btn btn-primary pull-right" value="View Encode Submissions" onClick="toEncodeSubmissions()"/>
					</div>
				</section>
				