<!-- Content Header (Page header) -->
			<div class="modal fade" id="delModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog">
				  <div class="modal-content">
					<div class="modal-header">
					  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					  <h4 class="modal-title" id="myModalLabel">Delete Run</h4>
					</div>
					<form name="editForm" role="form" method="post">
						<div class="modal-body">
							<fieldset>
								<div class="form-group" style="overflow:scroll">
									<label>Run ID:</label>
									<label id="delRunId"></label>
									<br>
									<label>Are you sure you want to delete this run?</label>
								</div>
							</fieldset>
						</div>
						<div class="modal-footer">
						  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						  <button type="button" id="confirm_del_btn" class="btn btn-danger" data-dismiss="modal" onclick="confirmDeleteRunparams(this.value)">Delete</button>
						</div>
					</form>
				  </div>
				</div>
			</div><!-- End Delete modal -->
			<div class="modal fade" id="logModal" tabindex="-1" role="dialog" aria-labelledby="myModalLog" aria-hidden="true">
				<div class="modal-dialog" style="width:75%">
				  <div class="modal-content">
					<div class="modal-header">
					  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					  <h4 class="modal-title" id="myModalLog">Error Log:</h4>
					</div>
					<form name="logForm" role="form" method="post">
						<div class="modal-body" style="overflow:scroll">
							<fieldset>
								<div class="form-group">
									<label id="logRunId"></label>
									<br>
									<p id="logDetails"></p>
								</div>
							</fieldset>
						</div>
						<div class="modal-footer">
							<button type="button" id="modal_adv_status" class="btn btn-primary" data-dismiss="modal">Adv. Status</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
						</div>
					</form>
				  </div>
				</div>
			</div><!-- End logging modal -->
			<div class="modal fade" id="groupsModal" tabindex="-1" role="dialog" aria-labelledby="myModalGroups" aria-hidden="true">
				<div class="modal-dialog">
				  <div class="modal-content">
					<div class="modal-header">
					  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					  <h4 class="modal-title" id="myModalGroups"></h4>
					</div>
					<form name="logForm" role="form" method="post">
						<div class="modal-body" style="overflow:scroll">
							<fieldset>
								<div class="form-group">
									<label id="groupsLabel"></label>
									<br>
									<div id="groupsDiv">
									</div>
								</div>
							</fieldset>
						</div>
						<div class="modal-footer">
							<button type="button" id="confirmGroupsButton" class="btn btn-primary" data-dismiss="">Confirm</button>
							<button type="button" id="cancelGroupsButton" class="btn btn-default" data-dismiss="modal">Cancel</button>
						</div>
					</form>
				  </div>
				</div>
			</div><!-- End groups modal -->
			<div class="modal fade" id="permsModal" tabindex="-1" role="dialog" aria-labelledby="myModalPerms" aria-hidden="true">
				<div class="modal-dialog">
				  <div class="modal-content">
					<div class="modal-header">
					  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					  <h4 class="modal-title" id="myModalPerms"></h4>
					</div>
					<form name="logForm" role="form" method="post">
						<div class="modal-body" style="overflow:scroll">
							<fieldset>
									<div class="form-group">
										   <label id="editLabel"></label>
										   <br>
										   <div id="editDiv">
										   </div>
								   </div>
								   <div class="form-group">
											<label id="permsLabel"></label>
											<div class="form-group">
															<label for="Group">Who can see?</label>
											<div class="radio">
												<label for="only me">
													<input type="radio" name="security_id" id="only_me" value="3" style="position: absolute;">
													&nbsp;only me
												</label>
											</div>
											<div class="radio">
												<label for="only my group">
													<input type="radio" name="security_id" id="only_my_group" value="15" style="position: absolute;">
													&nbsp;only my group
												</label>
											</div>
											<div class="radio">
												<label for="everyone">
													<input type="radio" name="security_id" id="everyone" value="32" style="position: absolute;">
													&nbsp;everyone
												</label>
											</div>
											<p class="help-block">Please select the security credentials for this import</p>
										</div>
								</div>
							</fieldset>
						</div>
						<div class="modal-footer">
							<button type="button" id="confirmPermsButton" class="btn btn-primary" data-dismiss="modal">Confirm</button>
							<button type="button" id="cancelPermsButton" class="btn btn-default" data-dismiss="modal">Cancel</button>
						</div>
					</form>
				  </div>
				</div>
			</div><!-- End perms modal -->
			<div class="modal fade" id="runModal" tabindex="-1" role="dialog" aria-labelledby="myModalrun" aria-hidden="true">
				<div class="modal-dialog">
				  <div class="modal-content">
					<div class="modal-header">
					  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					  <h4 class="modal-title" id="myModalrun">Owner Required</h4>
					</div>
					<form name="runForm" role="form" method="post">
						<div class="modal-body" style="overflow:scroll">
							<fieldset>
								<div class="form-group">
									<label id="runLabel">You must be the owner of this run to perform that action.</label>
									<br>
									<div id="runDiv">
									</div>
								</div>
							</fieldset>
						</div>
						<div class="modal-footer">
							<button type="button" id="runButton" class="btn btn-default" data-dismiss="modal">OK</button>
						</div>
					</form>
				  </div>
				</div>
			</div><!-- run modal -->
				<section class="content-header">
					<h1>
						NGS Run Status
						<small>Status details</small>
					</h1>
					<ol class="breadcrumb">
						<li><a href="<?php echo BASE_PATH?>"><i class="fa fa-dashboard"></i> Home</a></li>
						<li><a href="<?php echo BASE_PATH."/search"?>">NGS Pipeline</a></li>
						<li class="active"><?php echo $field?></li
					</ol>
				</section>
				<!-- Main content -->
				<?php $title_list = ["ID", "Name", "Output Directory", "Description",
			      "Status", "Fastq Dir", "Owner", "Options"];
			      $fields_list = ["sample_id", "sample", "experiment", "lane", "file_name",
			      "backup_dir", "amazon_bucket", "backup_status", "selected"]; ?>

				<section class="">
					<div class="row">
						<div class="col-md-12 margin">
						<?php echo $html->sendJScript('status', "", "", "", $uid, $gids); ?>
						<?php echo $html->getRespBoxTable_ng("Current Run Status", "runparams", "<th>ID</th><th>Name</th><th>Output Directory</th><th>Description</th><th>Status</th><th>Owner</th><th>Options</th>"); ?>
						</div>
						<div class="col-md-12">
						<?php echo $html->getStaticSelectionBox("View which runs?", "run_types", "<option value=\"0\">All Runs</option>
																		<option value=\"1\">Initial Runs</option>
																		<option value=\"2\">Normal Runs</option>", 4)?>

						<div id="all_tracking_data_table" class="margin">
		          <?php echo $html->getRespBoxTableStreamNoExpand("Current Run Status",
		            "berk_yarak_kurek", ["ID","Run Name","Output Directory", "Description", "Status", "Owner", "Options"],
								["id","run_name","outdir", "run_description", "status", "owner", "options"]); ?>
		        </div>


						</div>
					</div><!-- /.row -->
				</section><!-- /.content -->
