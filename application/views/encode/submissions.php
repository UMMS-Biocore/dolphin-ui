				<section class="content-header">
					<h1>
						NGS Encode Submissions Table
						<small>Encode Submissions List</small>
					</h1>
					<ol class="breadcrumb">
						<li><a href="<?php echo BASE_PATH?>"><i class="fa fa-dashboard"></i> Home</a></li>
						<li><a href="<?php echo BASE_PATH."/search"?>">NGS Browser</a></li>
						<li class="active"><?php echo $field?></li
					</ol>
				</section>
				<!-- Main content -->
				<?php echo $html->sendJScript("encode_submissions", "", "", "", $uid, $gids); ?>
				<div class="modal fade" id="logModal" tabindex="-1" role="dialog" aria-labelledby="myLogModalLabel" aria-hidden="true">
					<div class="modal-dialog" style="width:75%">
					  <div class="modal-content">
						<div class="modal-header">
						  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						  <h4 class="modal-title" id="myLogModalLabel">View Log</h4>
						</div>
						<form name="editForm" role="form" method="post">
							<div class="modal-body">
								<fieldset>
									<div id="log_content" class="form-group" style="overflow:scroll">
									</div>
								</fieldset>   
							</div>
							<div class="modal-footer">
							  <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
							</div>
						</form>
					  </div>
					</div>
				</div><!-- End experiment modal -->
				<section class="content">
					<div class="row">
						<div class="col-md-12">
							<div id="sub_table" class="margin col-md-12">
								<?php echo $html->getRespBoxTable_ng("Encode Batch Submissions", "submissions_batch_table", "<th>ID</th><th>Samples</th><th>Submission Status</th><th>Log File</th><th>Resubmission</th>"); ?>
							</div>
						</div>
						<div class="col-md-12">
							<div id="sub_table" class="margin col-md-12">
								<?php echo $html->getRespBoxTable_ng("Encode Sample Submissions", "submissions_table", "<th>Sample ID</th><th>Samples</th><th>Submission Status</th><th>Log File</th>"); ?>
							</div>
						</div>
						<div class="col-md-12">
							<div id="generated_table" class="margin col-md-12">
								<button class="btn btn-box-tool btn-primary margin pull-right" onclick="toSubmitEncode()">Submit to Encode</button>
								<button class="btn btn-box-tool btn-primary margin pull-right" onclick="backToBrowser()">Back to Browser</button>
							</div>
						</div>
					</div><!-- /.row -->
				</section><!-- /.content -->