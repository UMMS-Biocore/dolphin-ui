<!-- Content Header (Page header) -->
				<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
					  <div class="modal-content">
						<div class="modal-header">
						  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						  <h4 class="modal-title" id="myModalLabel">Error</h4>
						</div>
						<form name="editForm" role="form" method="post">
							<div class="modal-body">
								<fieldset>
									<div class="form-group" style="overflow:scroll">
										<label id="errorLabel"></label>
										<br>
										<p id="errorAreas"></p>
									</div>
								</fieldset>   
							</div>
							<div class="modal-footer">
							  <button type="button" id="" class="btn btn-default" data-dismiss="modal">OK</button>
							</div>
						</form>
					  </div>
					</div>
				</div><!-- End Delete modal -->
				<section class="content-header">
					<h1>
						NGS Table Creator
						<small>Custom table creation</small>
					</h1>
					<ol class="breadcrumb">
						<li><a href="<?php echo BASE_PATH?>"><i class="fa fa-dashboard"></i> Home</a></li>
						<li><a href="<?php echo BASE_PATH."/search"?>">NGS Table Creator</a></li>
						<li class="active"><?php echo $field?></li
					</ol>
				</section>
				<!-- Main content -->
				<?php echo $html->sendJScript("table_create", "", "", "", $uid, $gids); ?>
				<section class="content">
					<div class="row">
						<div class="col-md-12">
							<div class="col-md-8">
								<?php 
									echo $html->getRespBoxTable_ng("Samples Selected", "selected_samples", "<th>id</th><th>Sample Name</th><th>Run ID</th><th>Delete</th>");
								?>
							</div>
							<div class="col-md-4">
								<div class="col-md-12">
									<div class="box">
										<div class="box-header">
											<h3 class="box-title">Report Selection
											</h3>
											<?php echo $html->getInfoBox('report_selected') ?>
										</div>
										<div class= "box-body">
											<select id="report_multi_box" class="form-control" size="31" multiple>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-12">
								<?php
									$_SESSION['tablecreatorcheck'] = true;
									echo $html->getExpandingAnalysisBox('Additional Sample Selection', "table_create", false);
									unset($_SESSION['tablecreatorcheck']);
								?>
							</div>
							<div class="margin col-md-12">
								<button class="btn btn-primary pull-left" onclick="">Select Like Runs</button>
								<button class="btn btn-box-tool btn-primary margin pull-right" onclick="toBrowserPage()">Back to Browser</button>
								<button class="btn btn-box-tool btn-primary margin pull-right" onclick="tableCreatorPage()">Generate Table</button>
								<button class="btn btn-box-tool btn-primary margin pull-right" onclick="toTableListing()">To Created Tables</button>
							</div>
						</div><!-- /.col (LEFT) -->
					</div><!-- /.row -->
				</section><!-- /.content -->

