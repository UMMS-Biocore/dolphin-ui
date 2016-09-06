				<style>
					.warning {
						background-color: #F99 !important;
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
								</ul>
								<div class="tab-content margin">
									<div class="tab-pane active" id="samples_tab">
											<?php
												echo $html->getRespBoxTable_ng("Selected Samples", "selected_samples",
																		   "<th>id</th><th>Sample Name</th><th>Source</th><th>Organism</th><th>Molecule</th><th>Removal</th><th>Selected</th>");
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
											echo $html->getRespBoxTable_ng("Donors", "donors",
																		   "<th>Sample</th><th>Donor</th><th>Lab</th><th>Award</th><th>Organism</th><th>Life Stage</th><th>Age</th><th>Sex</th><th>Donor Acc</th><th>Donor UUID</th><th>Selected</th>");
										?>
										<input type="button" class="btn btn-primary margin" value="Change Selected" onClick="changeValuesEncode('selected','donor',this,event)"/>
										<input type="button" class="btn btn-primary margin" value="Change All" onClick="changeValuesEncode('all','donor',this,event)"/>
									</div>
									<div class="tab-pane" id="experiments_tab">
										<?php
											#Experiments
											echo $html->getRespBoxTable_ng("Experiments", "experiments",
																		   "<th>Sample</th><th>Assay Term Name</th><th>Source</th><th>Description</th><th>Experiment Acc</th><th>Experiment UUID</th><th>Selected</th>");
										?>
										<input type="button" class="btn btn-primary margin" value="Change Selected" onClick="changeValuesEncode('selected','experiment',this,event)"/>
										<input type="button" class="btn btn-primary margin" value="Change All" onClick="changeValuesEncode('all','experiment',this,event)"/>
									</div>
									<div class="tab-pane" id="treatments_tab">
										<?php
											#Treatments
											echo $html->getRespBoxTable_ng("Treatments", "treatments",
																		   "<th>Name</th><th>Treatment Term Name</th><th>Treatment Term Id</th><th>Treatment Type</th><th>Concentration</th><th>Concentration Units</th><th>Duration</th><th>Duration Units</th><th>Selected</th>");
										?>
										<input type="button" class="btn btn-primary margin" value="Change Selected" onClick="changeValuesEncode('selected','treatment',this,event)"/>
										<input type="button" class="btn btn-primary margin" value="Change All" onClick="changeValuesEncode('all','treatment',this,event)"/>
									</div>
									<div class="tab-pane" id="biosamples_tab">
										<?php
											#Biosamples
											echo $html->getRespBoxTable_ng("Biosamples", "biosamples",
																		   "<th>Sample</th><th>Treatment</th><th>Biosample Term Name</th><th>Biosample Term Id</th><th>Biosample Type</th><th>Date Submitted</th><th>Date Received</th><th>Biosample Acc</th><th>Biosample UUID</th><th>Selected</th>");
										?>
										<input type="button" class="btn btn-primary margin" value="Change Selected" onClick="changeValuesEncode('selected','biosample',this,event)"/>
										<input type="button" class="btn btn-primary margin" value="Change All" onClick="changeValuesEncode('all','biosample',this,event)"/>
									</div>
									<div class="tab-pane" id="libraries_tab">
										<?php
											#Libraries
											echo $html->getRespBoxTable_ng("Libraries", "libraries",
																		   "<th>Sample</th><th>Nucleic Acid Term Name</th><th>Crosslinking Method</th><th>Spike-ins Used</th><th>Extraction Method</th><th>Fragmentation Method</th><th>Size Range</th><th>Sequencing Platform</th><th>Library Acc</th><th>Library UUID</th><th>Selected</th>");
										?>
										<input type="button" class="btn btn-primary margin" value="Change Selected" onClick="changeValuesEncode('selected','library',this,event)"/>
										<input type="button" class="btn btn-primary margin" value="Change All" onClick="changeValuesEncode('all','library',this,event)"/>
									</div>
									<div class="tab-pane" id="antibodies_tab">
										<?php
											#Antibody Lots
											echo $html->getRespBoxTable_ng("Antibodies", "antibodies",
																		   "<th>Target</th><th>Source</th><th>Product Id</th><th>Lot Id</th><th>Host Organism</th><th>Clonality</th><th>Isotype</th><th>Purifications</th><th>URL</th><th>Antibody UUID</th><th>Selected</th>");
										?>
										<input type="button" class="btn btn-primary margin" value="Change Selected" onClick="changeValuesEncode('selected','antibody',this,event)"/>
										<input type="button" class="btn btn-primary margin" value="Change All" onClick="changeValuesEncode('all','antibody',this,event)"/>
									</div>
									<div class="tab-pane" id="replicates_tab">
										<?php
											#Replicates
											echo $html->getRespBoxTable_ng("Replicates", "replicates",
																		   "<th>Sample</th><th>Antibody</th><th>Biological Replicate Number</th><th>Technical Replicate Number</th><th>Replicate UUID</th><th>Selected</th>");
										?>
										<input type="button" class="btn btn-primary margin" value="Change Selected" onClick="changeValuesEncode('selected','replicate',this,event)"/>
										<input type="button" class="btn btn-primary margin" value="Change All" onClick="changeValuesEncode('all','replicate',this,event)"/>
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>
				<section>
					<div class="margin">
						<input type="button" id="submitMeta" class="btn btn-primary" name="pipeline_send_button" value="Submit Meta-data" onClick="checkForEncodeSubmission('metadata')"/>
						<input type="button" id="submitFiles" class="btn btn-primary" name="pipeline_send_button" value="Submit Files" onClick="checkForEncodeSubmission('files')"/>
						<input type="button" id="submitBoth" class="btn btn-primary" name="pipeline_send_button" value="Submit Both" onClick="checkForEncodeSubmission('both')"/>
						<input type="button" class="btn btn-default pull-right" value="View Encode Submissions" onClick="toEncodeSubmissions()"/>
					</div>
				</section>