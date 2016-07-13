			<div class="modal fade" id="joboutData" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog" style="width:75%">
				  <div class="modal-content">
					<div class="modal-header">
					  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					  <h4 class="modal-title" id="myModalLabel">Job Output</h4>
					</div>
					<form name="editForm" role="form" method="post">
						<div class="modal-body">
							<fieldset>
								<div class="form-group" style="overflow:scroll">
									<label>Job Name: </label>
									<label id="job_modal_jobname"></label>
									<br>
									<p id="job_modal_text"></p>
								</div>
							</fieldset>   
						</div>
						<div class="modal-footer">
						  <button type="button" class="btn btn-default" data-dismiss="modal">Exit</button>
						</div>
					</form>
				  </div>
				</div>
			</div><!-- End Delete modal -->
				<section class="content-header">
					<h1>NGS Run Status<small>Advanced status details</small></h1>
					<ol class="breadcrumb">
						<li><a href="<?php echo BASE_PATH?>"><i class="fa fa-dashboard"></i> Home</a></li>
						<li><a href="<?php echo BASE_PATH."/search"?>">NGS Pipeline</a></li>
						<li class="active"><?php echo $field?></li>
					</ol>
				</section>
				<!-- Main content -->
				<section class="content">
					<div class="row">
						<div class="col-md-12">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 style="word-break: break-all;"><b>Run:</b> <?php echo $run_id?> <b>Directory:</b> <?php echo $outdir?></h4>
								</div>
								<div class="panel-body">
									<?php echo $html->sendJScript('advstatus', "", "", "", $uid, $gids); ?>
									<?php echo $html->getRespBoxTable_ng("Services", "services", "<th>Name</th><th>Duration</th><th style=\"width: 75px\">% Complete</th><th>Progress</th><th>Start</th><th>Finish</th><th>Select</th>"); ?>
									<div id="service_jobs" style="display:none">
										<?php echo $html->getRespBoxTable_ng("Jobs", "jobs", "<th>Name</th><th>Duration</th><th>Result</th><th>Job #</th><th>Submission Time</th><th>Start</th><th>Finish</th><th>Select</th>"); ?>
									</div>
								</div>
								<div class="input margin">
									<button id="back_to_status" class="btn btn-primary" onclick="backToStatus('<?php echo BASE_PATH?>')">Run Status</button>
									<button id="send_to_reports" class="btn btn-primary" onclick="reportSelected(this.name, this.name)">Reports</button>
								</div>
							</div>
						</div>
					</div><!-- /.row -->
				</section><!-- /.content -->
