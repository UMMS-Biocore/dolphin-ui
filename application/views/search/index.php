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
						<div class="col-md-9">
							<?php if(!isset($_SESSION['ngs_experiments'])){
								echo $html->getRespBoxTableStream("Experiments", "experiments", ["id","Series Name","Summary","Design", "Selected"], ["id","experiment_name","summary","design", ""]);
							}else if($_SESSION['ngs_experiments'] == ''){
								echo $html->getRespBoxTableStream("Experiments", "experiments", ["id","Series Name","Summary","Design", "Selected"], ["id","experiment_name","summary","design", ""]);
							}else{
								echo $html->getRespBoxTableStream("Experiments", "experiments", ["id","Series Name","Summary","Design", "Lab","Organization","Grant", "Selected"], ["id","experiment_name","summary","design", "lab","organization","grant", ""]);
							}?>
							<?php if(!isset($_SESSION['ngs_lanes'])){
								echo $html->getRespBoxTableStream("Imports", "lanes", ["id","Import Name","Facility","Total Reads","Total Samples","Selected"], ["id","name","facility", "total_reads", "total_samples",""]);
							}else if($_SESSION['ngs_lanes'] == ''){
								echo $html->getRespBoxTableStream("Imports", "lanes", ["id","Import Name","Facility","Total Reads","Total Samples","Selected"], ["id","name","facility", "total_reads", "total_samples",""]);
							}else{
								echo $html->getRespBoxTableStream("Imports", "lanes", ["id","Import Name","Facility","Total Reads","Total Samples", "Cost", "Phix Requested", "Phix in Lane", "Notes", "Selected"],
																  ["id","name","facility", "total_reads", "total_samples", "cost", "phix_requested", "phix_in_lane", "notes", ""]);
							}?>
							<?php if(!isset($_SESSION['ngs_samples'])){
								echo $html->getRespBoxTableStream("Samples", "samples", ["id","Sample Name","Title","Source","Organism","Molecule","Backup","Selected"], ["id","name","title","source","organism","molecule","backup","total_reads"]);
							}else if($_SESSION['ngs_samples'] == ''){
								echo $html->getRespBoxTableStream("Samples", "samples", ["id","Sample Name","Title","Source","Organism","Molecule","Backup","Selected"], ["id","name","title","source","organism","molecule","backup","total_reads"]);
							}else{
								echo $html->getRespBoxTableStream("Samples", "samples", ["id","Sample Name","Title","Source","Organism","Molecule", "Barcode", "Backup", "Description", "Avg Insert Size", "Read Length",
																						"Concentration", "Time", "Biological Replica", "Technical Replica", "Spike-ins", "Adapter",
																						"Notebook Ref", "Notes", "Genotype", "Library Type", "Biosample Type", "Instrument Model", "Treatment Manufacturer","Selected"],
																						["id","name","title","source","organism","molecule","backup","total_reads", "barcode", "description", "avg_insert_size", "read_length",
																						"concentration", "time", "biological_replica", "technical_replica", "spike_ins", "adapter",
																						"notebook_ref", "notes", "genotype", "library_type", "biosample_type", "instrument_model", "treatment_manufacturer"]);
							}?>
						</div><!-- /.col (RIGHT) -->
					</div><!-- /.row -->
				</section><!-- /.content -->



