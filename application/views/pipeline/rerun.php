<!-- Content Header (Page header) -->
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
								<div class="form-group">
									<label id="errorLabel"></label>
									<br>
									<p id="errorAreas"></p>
								</div>
							</fieldset>   
						</div>
						<div class="modal-footer">
						  <button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
						</div>
					</form>
				  </div>
				</div>
			</div><!-- End Error modal -->	
				<section class="content-header">
					<h1>
						NGS Pipeline
						<small>Workflow creation</small>
					</h1>
					<ol class="breadcrumb">
						<li><a href="<?php echo BASE_PATH?>"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="<?php echo BASE_PATH."/search"?>">NGS Pipeline</a></li>
			<li class="active"><?php echo $field?></li
					</ol>
				</section>
				<!-- Main content -->
				<section class="content">
					<div class="row">
						<div class="col-md-12">
							<?php echo $html->sendJScript("selected", "", "", $selection, $uid, $gids); ?>
							<?php if(!isset($_SESSION['ngs_samples'])){
								echo $html->getRespBoxTableStream("Samples", "samples", ["id","Sample Name","Title","Source","Organism","Molecule","Selected"], ["id","name","title","source","organism","molecule"]);
							}else if($_SESSION['ngs_samples'] == ''){
								echo $html->getRespBoxTableStream("Samples", "samples", ["id","Sample Name","Title","Source","Organism","Molecule","Selected"], ["id","name","title","source","organism","molecule"]);
							}else{
								echo $html->getRespBoxTableStream("Samples", "samples", ["id","Sample Name","Title","Source","Organism","Molecule", "Barcode", "Description", "Avg Insert Size", "Read Length",
																						"Concentration", "Time", "Biological Replica", "Technical Replica", "Spike-ins", "Adapter",
																						"Notebook Ref", "Notes", "Genotype", "Library Type", "Biosample Type", "Instrument Model", "Treatment Manufacturer"],
																						["id","name","title","source","organism","molecule", "barcode", "description", "avg_insert_size", "read_length",
																						"concentration", "time", "biological_replica", "technical_replica", "spike_ins", "adapter",
																						"notebook_ref", "notes", "genotype", "library_type", "biosample_type", "instrument_model", "treatment_manufacturer"]);
							}?>
						</div><!-- /.col (RIGHT) -->
					</div><!-- /.row -->
					<div class="row">
						<?php echo $html->getStaticSelectionBox("Name the Run", "run_name", "TEXT", 4)?>
						<?php echo $html->getStaticSelectionBox("Run Description", "run_description", "TEXT", 8)?>
						<?php echo $html->getStaticSelectionBox("Genome Build", "genomebuild", "<option>human,hg19</option>
																				<option>hamster,cho-k1</option>
																				<option>rat,rn5</option>
																				<option>zebrafish,danRer7</option>
																				<option>mouse,mm10</option>
																				<option>mousetest,mm10</option>
																				<option>s_cerevisiae,sacCer3</option>
																				<option>c_elegans,ce10</option>
																				<option>cow,bosTau7</option>
																				<option>d_melanogaster,dm3</option>", 4)?>
						<?php echo $html->getStaticSelectionBox("Mate-paired", "spaired", "<option>yes</option>
																				<option>no</option>", 4)?>
						<?php echo $html->getStaticSelectionBox("Resume Run", "resume", "<option>Fresh</option>
																				<option>Resume</option>", 4)?>
						<?php echo $html->getStaticSelectionBox("Output Directory", "outdir", "TEXT", 8)?>
						<?php echo $html->getStaticSelectionBox("FastQC", "fastqc", "<option>yes</option>
																			<option>no</option>", 4)?>
						<?php echo $html->getStaticSelectionBox("Permissions", "perms", "<option value='3'>only me</option>
																			<option value='15'>only my group</option>
																			<option value='32'>everyone</option>", 4)?>
						<?php echo $html->getStaticSelectionBox("Group Selection", "groups", $html->groupSelectionOptions($groups), 4)?>
						<?php echo $html->getStaticSelectionBox("Submission", "submission", "<option value='0'>None</option>
																			<option value='1'>Encode</option>
																			<option value='2'>Geo</option>", 4)?>
			<?php echo $html->startExpandingSelectionBox(6)?>
						<?php echo $html->getExpandingSelectionBox("Split FastQ", "split", 1, 12, ["number of reads per file"], [["TEXT","5000000"]])?>
						<?php echo $html->getExpandingSelectionBox("Adapter Removal", "adapters", 1, 12, ["adapters"], [["TEXTBOX"]])?>
						<?php echo $html->getExpandingSelectionbOX("Custom Sequence Set", "custom", 1, 12, ["Add new Custom Sequence Set"], [["BUTTON"]])?>
						<?php echo $html->getExpandingSelectionBox("Additional Pipelines", "pipeline", 1, 12, ["Add a Pipeline"], [["BUTTON"]])?>
			<?php echo $html->endExpandingSelectionBox()?>

			<?php echo $html->startExpandingSelectionBox(6)?>
						<?php echo $html->getExpandingSelectionBox("Quality Filtering", "quality", 5, 12, ["window size","required quality","leading","trailing","minlen"],
																	[["TEXT","10"],["TEXT","15"],["TEXT","5"],["TEXT","5"],["TEXT","36"]])?>
						<?php echo $html->getExpandingSelectionBox("Trimming", "trim", 3, 12, ["single or paired-end", "5 length 1", "3 length 1"],
																	[["ONCHANGE", "single-end", "paired-end"],["TEXT","0"],["TEXT","0"]])?>
						<?php echo $html->getExpandingCommonRNABox("Common RNAs", "commonind", 8, 12, ["ercc","rRNA","miRNA","tRNA","piRNA","snRNA","rmsk","genome"],
																	[["no","yes"],["no","yes"],["no","yes"],["no","yes"],["no","yes"],["no","yes"],["no","yes"],["no","yes"]])?>
			<?php echo $html->endExpandingSelectionBox()?>

			<div class="col-md-12">
				<input type="button" id="submitPipeline" class="btn btn-primary" name="pipeline_send_button" value="Submit Pipeline" onClick="submitPipeline('selected');"/>
						</div>
					</div><!-- /.row -->
				</section><!-- /.content -->

