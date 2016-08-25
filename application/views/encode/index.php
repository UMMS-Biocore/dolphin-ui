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
				<section>
					<?php echo $html->sendJScript("encode", "", "", "", $uid, $gids); ?>
					<div class="margin">
					<?php
						#Samples
						echo $html->getRespBoxTableStreamNoExpand("Samples", "samples", ["id","Sample Name","Title","Source","Organism","Molecule","Backup","Selected"], ["id","name","title","source","organism","molecule","backup","total_reads"]);
					?>
					<?php
						#Donors
						echo $html->getRespBoxTable_ng("Donors", "donors", "<th>Donor</th><th>Lab</th><th>Award</th><th>Organism</th><th>Life Stage</th><th>Age</th><th>Sex</th><th>Donor Acc</th><th>Donor UUID</th>");
					?>
					<?php
						#Experiments
						echo $html->getRespBoxTable_ng("Experiments", "experiments", "<th>Sample</th><th>Assay Term Name</th><th>Source</th><th>Description</th><th>Experiment Acc</th><th>Experiment UUID</th>");
					?>
					<?php
						#Treatments
						echo $html->getRespBoxTable_ng("Treatments", "treatments", "<th>Name</th><th>Treatment Term Name</th><th>Treatment Term Id</th><th>Treatment Type</th><th>Concentration</th><th>Concentration Units</th><th>Duration</th><th>Duration Units</th>");
					?>
					<?php
						#Biosamples
						echo $html->getRespBoxTable_ng("Biosamples", "biosamples", "<th>Sample</th><th>Donor</th><th>Treatment</th><th>Biosample Term Name</th><th>Biosample Term Id</th><th>Biosample Type</th><th>Date Submitted</th><th>Date Received</th><th>Biosample Acc</th><th>Biosample UUID</th>");
					?>
					<?php
						#Libraries
						echo $html->getRespBoxTable_ng("Libraries", "libraries", "<th>Sample</th><th>Nucleic Acid Term Name</th><th>Crosslinking Method</th><th>Spike-ins Used</th><th>Extraction Method</th><th>Fragmentation Method</th><th>Size Range</th><th>Sequencing Platform</th><th>Library Acc</th><th>Library UUID</th>");
					?>
					<?php
						#Antibody Lots
						echo $html->getRespBoxTable_ng("Antibodies", "antibodies", "<th>Target</th><th>Source</th><th>Product Id</th><th>Lot Id</th><th>Host Organism</th><th>Clonality</th><th>Isotype</th><th>Purifications</th><th>URL</th><th>Antibody UUID</th>");
					?>
					<?php
						#Replicates
						echo $html->getRespBoxTable_ng("Replicates", "replicates", "<th>Sample</th><th>Antibody</th><th>Biological Replicate Number</th><th>Technical Replicate Number</th><th>Replicate UUID</th>");
					?>
					</div>
				</section>
				<section>
					<div class="margin">
						<input type="button" id="submitMeta" class="btn btn-primary" name="pipeline_send_button" value="Submit Meta-data" onClick="checkForEncodeSubmission('metadata')"/>
						<input type="button" id="submitFiles" class="btn btn-primary" name="pipeline_send_button" value="Submit Files" onClick="checkForEncodeSubmission('files')"/>
						<input type="button" id="submitBoth" class="btn btn-primary" name="pipeline_send_button" value="Submit Both" onClick="checkForEncodeSubmission('both')"/>
					</div>
				</section>