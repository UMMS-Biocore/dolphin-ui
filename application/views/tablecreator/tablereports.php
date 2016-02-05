<!-- Content Header (Page header) -->
				<div class="modal fade" id="permsModal" tabindex="-1" role="dialog" aria-labelledby="myPermsModal" aria-hidden="true">
					<div class="modal-dialog">
					  <div class="modal-content">
						<div class="modal-header">
						  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						  <h4 class="modal-title" id="myPermsModal">Change the Tables's Permissions</h4>
						</div>
						<form name="editForm" role="form" method="post">
							<div class="modal-body">
								<fieldset>
									<div class="form-group">
										<label id="permsOwnerLabel"></label>
										<select id="permsOwnerSelect" class="form-control"></select>
										<br>
										<label id="permsGroupLabel"></label>
										<select id="permsGroupSelect" class="form-control"></select>
										<br>
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
									</div>
								</fieldset>   
							</div>
							<div class="modal-footer">
							  <button type="button" id="confirmTablePermsButton" class="btn btn-primary" onclick="confirmTablePermsPressed()" data-dismiss="modal">Confirm</button>
							  <button type="button" id="cancelTablePermsButton" class="btn btn-default" data-dismiss="modal">Cancel</button>
							</div>
						</form>
					  </div>
					</div>
				</div><!-- End Perms modal -->
				<div class="modal fade" id="permsConfirmModal" tabindex="-1" role="dialog" aria-labelledby="myPermsConfirmModal" aria-hidden="true">
					<div class="modal-dialog">
					  <div class="modal-content">
						<div class="modal-header">
						  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						  <h4 class="modal-title" id="myPermsConfirmModal">Change Results</h4>
						</div>
						<form name="editForm" role="form" method="post">
							<div class="modal-body">
								<fieldset>
									<div class="form-group">
										<label id="permsConfirmLabel">Table's permissions have been changed!</label>
										<br>
									</div>
								</fieldset>   
							</div>
							<div class="modal-footer">
							  <button type="button" id="cancelTablePermsButton" class="btn btn-default" data-dismiss="modal">OK</button>
							</div>
						</form>
					  </div>
					</div>
				</div><!-- End Perms modal -->
				<section class="content-header">
					<h1>
						NGS Table List
						<small>Custom table list</small>
					</h1>
					<ol class="breadcrumb">
						<li><a href="<?php echo BASE_PATH?>"><i class="fa fa-dashboard"></i> Home</a></li>
						<li><a href="<?php echo BASE_PATH."/search"?>">NGS Table List</a></li>
						<li class="active"><?php echo $field?></li
					</ol>
				</section>
				<!-- Main content -->
				<?php echo $html->sendJScript("table_viewer", "", "", "", $uid, $gids); ?>
				<section class="content">
					<div class="row">
						<div class="col-md-12">
							<div id="generated_table" class="margin col-md-12">
								<?php echo $html->getRespBoxTable_ng("Tables Created", "table_viewer", "<th>ID</th><th>Name</th><th>Samples/Runs</th><th>Files</th><th>Options</th>"); ?>
							</div>
						</div>
						<div class="col-md-12">
							<div id="generated_table" class="margin col-md-12">
								<button class="btn btn-box-tool btn-primary margin pull-right" onclick="backToTableIndex()">Create a Table</button>
								<button class="btn btn-box-tool btn-primary margin pull-right" onclick="toBrowserPage()">Back to Browser</button>
							</div>
						</div>
					</div><!-- /.row -->
				</section><!-- /.content -->

