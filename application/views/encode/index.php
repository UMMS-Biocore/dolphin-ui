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
						echo $html->getRespBoxTable_ng("Donors", "donors", "<th>id</th><th>Donor</th><th>Award</th><th>Lab</th><th>Life Stage</th><th>Age</th><th>Sex</th><th>Donor Acc</th><th>Donor UUID</th>");
					?>
					<?php
						#Experiments
						echo $html->getRespBoxTable_ng("Experiments", "experiments", "<th>id</th><th>Assay Term Name</th><th>Assay Term Id</th><th>Biosample Term Name</th><th>Biosample Term Id</th><th>Description</th>");
					?>
					<?php
						#Treatments
						echo $html->getRespBoxTable_ng("Treatments", "treatments", "<th>id</th><th>Treatment Term Name</th><th>Treatment Term Id</th><th>Treatment Type</th><th>Concentration</th><th>Concentration Units</th><th>Duration</th><th>Duration Units</th>");
					?>
					<?php
						#Biosamples
						echo $html->getRespBoxTable_ng("Biosamples", "biosamples", "<th>id</th><th>Donor</th><th>Biosample Term Name</th><th>Biosample Term Id</th><th>Biosample Type</th><th>Source</th><th>Treatments</th><th>Date Obtained</th>");
					?>
					<?php
						#Libraries
						echo $html->getRespBoxTable_ng("Libraries", "libraries", "<th>id</th><th>Biosample</th><th>Nucleic Acid Term Name</th><th>Nucleic Acid Term Id</th><th>Extraction Method</th><th>Size Range</th>");
					?>
					<?php
						#Antibody Lots
						echo $html->getRespBoxTable_ng("Antibodies", "antibodies", "<th>id</th><th>Source</th><th>Product Id</th><th>Lot Id</th><th>Host Organism</th><th>Targets<th>");
					?>
					<?php
						#Replicates
						echo $html->getRespBoxTable_ng("Replicates", "replicates", "<th>Experiment</th><th>Biological Replicate Number</th><th>Technical Replicate Number</th><thLlibrary</th><th>Antibody</th>");
					?>
					</div>
				</section>
				<section>
					<div class="margin">
						<input type="button" id="submitMeta" class="btn btn-primary" name="pipeline_send_button" value="Submit Meta-data" onClick=""/>
						<input type="button" id="submitFiles" class="btn btn-primary" name="pipeline_send_button" value="Submit Files" onClick=""/>
						<input type="button" id="submitBoth" class="btn btn-primary" name="pipeline_send_button" value="Submit Both" onClick=""/>
					</div>
				</section>