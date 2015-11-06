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
									<div class="form-group">
										<label id="deleteLabel"></label>
										<br>
										<p id="deleteAreas"></p>
									</div>
								</fieldset>   
							</div>
							<div class="modal-footer">
							  <button type="button" id="confirmDeleteButton" class="btn btn-danger" data-dismiss="modal" onclick="confirmDeletePressed()">Confirm</button>
							  <button type="button" id="cancelDeleteButton" class="btn btn-default" data-dismiss="modal" onclick="cancelDeletePressed()">Cancel</button>
							</div>
						</form>
					  </div>
					</div>
				</div><!-- End Delete modal -->
				<section class="content-header">
					<h1>
						NGS Browser
						<small>Project and experiment search</small>
					</h1>
					<ol class="breadcrumb">
						<li><a href="<?php echo BASE_PATH?>"><i class="fa fa-dashboard"></i> Home</a></li>
						<li><a href="<?php echo BASE_PATH."/search"?>">NGS Browser</a></li>
			<li class="active"><?php echo $field?></li>
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
					<?php echo $html->sendJScript($segment, $table, $value, $search, $uid, $gids); ?>
					<?php echo $html->getAccordion("Assay", $assay, $search)?>
					<?php echo $html->getAccordion("Organism", $organism, $search)?>
					<?php echo $html->getAccordion("Molecule", $molecule, $search)?>
					<?php echo $html->getAccordion("Source", $source, $search)?>
					<?php echo $html->getAccordion("Genotype", $genotype, $search)?>
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
								echo $html->getRespBoxTableStream("Samples", "samples", ["id","Sample Name","Title","Source","Organism","Molecule","Selected"], ["id","name","title","source","organism","molecule","total_reads"]);
							}else if($_SESSION['ngs_samples'] == ''){
								echo $html->getRespBoxTableStream("Samples", "samples", ["id","Sample Name","Title","Source","Organism","Molecule","Selected"], ["id","name","title","source","organism","molecule","total_reads"]);
							}else{
								echo $html->getRespBoxTableStream("Samples", "samples", ["id","Sample Name","Title","Source","Organism","Molecule", "Barcode", "Description", "Avg Insert Size", "Read Length",
																						"Concentration", "Time", "Biological Replica", "Technical Replica", "Spike-ins", "Adapter",
																						"Notebook Ref", "Notes", "Genotype", "Library Type", "Biosample Type", "Instrument Model", "Treatment Manufacturer","Selected"],
																						["id","name","title","source","organism","molecule","total_reads", "barcode", "description", "avg_insert_size", "read_length",
																						"concentration", "time", "biological_replica", "technical_replica", "spike_ins", "adapter",
																						"notebook_ref", "notes", "genotype", "library_type", "biosample_type", "instrument_model", "treatment_manufacturer"]);
							}?>
						</div><!-- /.col (RIGHT) -->
					</div><!-- /.row -->
				</section><!-- /.content -->



