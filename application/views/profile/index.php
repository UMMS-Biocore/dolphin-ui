		<!-- Content Header (Page header) -->
				<div class="modal fade" id="groupModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
					  <div class="modal-content">
						<div class="modal-header">
						  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						  <h4 class="modal-title" id="myModalLabel">Groups</h4>
						</div>
						<form name="editForm" role="form" method="post">
							<div class="modal-body" style="overflow:scroll">
								<fieldset>
									<label id="groupLabel"></label>
									<div id="groupModalDiv" class="form-group"></div>
								</fieldset>   
							</div>
							<div class="modal-footer">
							  <button type="button" id="confirmGroupButton" class="btn btn-primary" data-dismiss="modal" onclick="">OK</button>
							  <button type="button" id="cancelGroupButton" class="btn btn-default" data-dismiss="modal" onclick="">Cancel</button>
							</div>
						</form>
					  </div>
					</div>
				</div><!-- End Group modal -->
				<div class="modal fade" id="awsModal" tabindex="-1" role="dialog" aria-labelledby="myAWSModalLabel" aria-hidden="true">
					<div class="modal-dialog">
					  <div class="modal-content">
						<div class="modal-header">
						  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						  <h4 class="modal-title" id="myAWSModalLabel">Add New Amazon Keys</h4>
						</div>
						<form name="editAWSForm" role="form" method="post">
							<div class="modal-body" style="overflow:scroll">
								<fieldset>
									<label id="awsLabel"></label>
									<div id="awsModalDiv" class="form-group"></div>
								</fieldset>   
							</div>
							<div class="modal-footer">
							  <button type="button" id="confirmAWSButton" class="btn btn-primary" data-dismiss="modal" onclick="awsButton()">Submit</button>
							  <button type="button" id="cancelAWSButton" class="btn btn-default" data-dismiss="modal">Cancel</button>
							</div>
						</form>
					  </div>
					</div>
				</div><!-- End aws modal -->
				<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="myPasswordModalLabel" aria-hidden="true">
					<div class="modal-dialog">
					  <div class="modal-content">
						<div class="modal-header">
						  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						  <h4 class="modal-title" id="myPasswordModalLabel">Change Password Results:</h4>
						</div>
						<form name="editPasswordForm" role="form" method="post">
							<div class="modal-body" style="overflow:scroll">
								<fieldset>
									<label id="passwordLabel"></label>
								</fieldset>   
							</div>
							<div class="modal-footer">
							  <button id="passwordButton" type="button" class="btn btn-default" data-dismiss="modal">OK</button>
							</div>
						</form>
					  </div>
					</div>
				</div><!-- End password modal -->
				<div class="modal fade" id="submitModal" tabindex="-1" role="dialog" aria-labelledby="mySubmitModalLabel" aria-hidden="true">
					<div class="modal-dialog">
					  <div class="modal-content">
						<div class="modal-header">
						  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						  <h4 class="modal-title" id="mySubmitModalLabel">Submission:</h4>
						</div>
						<form name="editAWSForm" role="form" method="post">
							<div class="modal-body" style="overflow:scroll">
								<fieldset>
									<label id="submitLabel"></label>
								</fieldset>   
							</div>
							<div class="modal-footer">
							  <button id="finalAWSButton" type="button" class="btn btn-default" data-dismiss="modal" onclick="window.location.reload()">OK</button>
							</div>
						</form>
					  </div>
					</div>
				</div><!-- End submit modal -->
				<section class="content-header">
					<h1>
						Profile
						<small>Personal/Group information</small>
					</h1>
					<ol class="breadcrumb">
						<li><a href="<?php echo BASE_PATH?>"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="<?php echo BASE_PATH."/search"?>">Profile</a></li>
					</ol>
				</section>
				<!-- Main content -->
				<section class="content">
					<div class="row">
						<div class="col-md-12">
							<!-- general form elements -->
							<div class="nav-tabs-custom">
								<ul id="tabList" class="nav nav-tabs">
									<li class="active">
										<a href="#profile" data-toggle="tab" aria-expanded="true">Profile Information</a>
									</li>
									<li class>
										<a href="#groups" data-toggle="tab" aria-expanded="true">Groups</a>
									</li>
									<li class>
										<a href="#photo" data-toggle="tab" aria-expanded="true">Photo</a>
									</li>
									<li class>
										<a href="#email" data-toggle="tab" aria-expanded="true">Email</a>
									</li>
									<li class>
										<a href="#password" data-toggle="tab" aria-expanded="true">Password</a>
									</li>
									<li class>
										<a href="#bucket" data-toggle="tab" aria-expanded="true">Amazon Information</a>
									</li>
								</ul>
								<div class="tab-content">
									<div class="tab-pane active" id="profile">
										<div id="profileInformation" class="margin">
											<?php echo $html->getRespBoxTableStreamNoExpand("Profile Information", "user_profile", [], []); ?>
										</div>
									</div>
									<div class="tab-pane" id="groups">
										<div id="user_groups" class="margin">
											<?php echo $html->getRespBoxTableStreamNoExpand("Group Information", "profile_groups", ['ID', 'Group Name', 'Date Created', 'Options'], ['id','name','date_created','options']); ?>
										</div>
										<div>
											<button type="button" id="requestgroup" class="btn btn-primary" onclick="requestNewGroup()">Create a New Group</button>
											<button type="button" id="requestgroup" class="btn btn-primary" onclick="requestJoinGroup()">Join a Group</button>
										</div>
									</div>
									<div class="tab-pane" id="photo">
										<form role="form" enctype="multipart/form-data" action="profile" method="post">
											<div class="margin">
												<div class="form-group" ">
													<div class="margin">
														<label><h3>Update your profile picture</h3></label>
													</div>
													<div class="margin">
														<img src="<?=BASE_PATH.'/public/img/avatar.png'?>" height="100px" width="100px" name="avatar_sel" class="img-circle" alt="av1" onclick="selectAvatar(this.alt)"/>
														<input id="av1" type="radio" name="avatar_select" value="/public/img/avatar.png">
														<img src="<?=BASE_PATH.'/public/img/avatar2.png'?>" height="100px" width="100px" name="avatar_sel" class="img-circle" alt="av2" onclick="selectAvatar(this.alt)" />
														<input id="av2" type="radio" name="avatar_select" value="/public/img/avatar2.png">
														<img src="<?=BASE_PATH.'/public/img/avatar3.png'?>" height="100px" width="100px" name="avatar_sel" class="img-circle" alt="av3" onclick="selectAvatar(this.alt)"/>
														<input id="av3" type="radio" name="avatar_select" value="/public/img/avatar3.png">
														<img src="<?=BASE_PATH.'/public/img/avatar5.png'?>" height="100px" width="100px" name="avatar_sel" class="img-circle" alt="av4" onclick="selectAvatar(this.alt)"/>
														<input id="av4" type="radio" name="avatar_select" value="/public/img/avatar5.png">
													</div>
												</div>
											</div><!-- /.box-body -->
											<br><br>
											<div class="box-footer margin">
												<button type="button" id="changeAv" class="btn btn-primary" onclick="updateProfile()">Update</button>
											</div>
										</form>
									</div>
									<div class="tab-pane" id="email">
										<div id="email_box" class="margin">
											<div class="form-group" ">
												<div class="margin">
													<label><h3>Update your email settings</h3></label>
												</div>
												<div class="margin">
													<?php echo $html->getStaticSelectionBox("Email address", "email_address", "TEXT", 6)?>
													<?php echo $html->getStaticSelectionBox("Send email for runs?", "email_check", "<option value='0'>No</option>
																			<option value='1'>Yes</option>", 6)?>
												</div>
											</div>
										</div>
										<div class="box-footer margin">
											<button type="button" id="changeEmail" class="btn btn-primary" onclick="updateEmail()">Update</button>
										</div>
									</div>
									<div class="tab-pane" id="password">
										<div id="password_box" class="margin">
											<div class="form-group" ">
												<div class="margin">
													<label><h3>Change your Password</h3></label>
												</div>
												<div class="margin">
													<label>Old password:</label>
													<input type="password" class="form-control" id="old_password" password>
													<label>New password:</label>
													<input type="password" class="form-control" id="new_password" password>
													<label>Confirm new password:</label>
													<input type="password" class="form-control" id="pass_confirm" password>
												</div>
											</div>
										</div>
										<div class="box-footer margin">
											<button type="button" id="changeEmail" class="btn btn-primary" onclick="updatePassword()">Update</button>
										</div>
									</div>
									<div class="tab-pane" id="bucket">
										<div id="amazon_keys" class="margin">
											<div id="loadingKeysHeader">
												<h3>Loading...</h3>
											</div>
											<div id="loadingKeys" style="display: none">
												<?php echo $html->getRespBoxTableStreamNoExpand("Update your Amazon Buckets", "amazon", ['Group','Access Key', 'Secret Key'], ['group','access_key','secret_key']); ?>
												<div class="box-footer margin">
													<button type="button" id="changeAv" class="btn btn-primary" onclick="updateProfile()">Submit</button>
													<button type="button" id="addAWS" class="btn btn-primary" onclick="addAWSButton()">Add Amazon</button>
												</div>
											</div>
										</div>
									</div>
								</div>