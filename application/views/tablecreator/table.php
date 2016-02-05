<!-- Content Header (Page header) -->
				<section class="content-header">
					<h1>
						NGS Table Viewer
						<small>Custom table viewer</small>
					</h1>
					<ol class="breadcrumb">
						<li><a href="<?php echo BASE_PATH?>"><i class="fa fa-dashboard"></i> Home</a></li>
						<li><a href="<?php echo BASE_PATH."/search"?>">NGS Table Creator</a></li>
						<li class="active"><?php echo $field?></li
					</ol>
				</section>
				<!-- Main content -->
				<?php echo $html->sendJScript("generated", "", "", "", $uid, $gids); ?>
				<section class="content">
					<div class="row margin">
						<div class="col-md-2">
							<div id="downloadOptions" class="btn-group">
								<button id="generated_button" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Download Type  <span class="fa fa-caret-down"></span></button>
							</div>
						</div>
						<div class="col-md-8">
							<form class="form-horizontal">
								<div id="save_table" class="form-group">
									<label class='col-sm-2 control-label'>Save Table As:</label>
									<div class="col-sm-10">
										<input type="text" class="form-control" id="input_table_name">
									</div>
								</div>
							</form>
						</div>
						<div class="col-md-2">
							<button id="save_table_button" class="btn btn-primary pull-right" type="button" onclick="saveTable()">Save Table</button>
							<button class="btn btn-primary pull-right" type="button" onclick="toTableListing()">Tables List</button>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div id="generated_table" class="margin col-md-12">
								<?php
									$_SESSION['tablecreatorcheck'] = true;
									echo $html->getRespBoxTableStream("Table Generated", "generated", [], []);
									unset($_SESSION['tablecreatorcheck']);
								?>
							</div>
						</div>
					</div><!-- /.row -->
					<div class="row">
						<div class="col-md-12">
							<div id='permissions_group' class="margin col-md-12">
								<?php echo $html->getStaticSelectionBox("Group Selection", "groups", $html->groupSelectionOptions($groups), 6)?>
								<?php echo $html->getStaticSelectionBox("Permissions", "perms", "<option value='3'>only me</option>
																		<option value='15'>only my group</option>
																		<option value='32'>everyone</option>", 6)?>
							</div>
						</div>
					</div>
				</section><!-- /.content -->

