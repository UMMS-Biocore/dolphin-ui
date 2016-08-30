		<section class="content">
			<?php echo $html->sendJScript("excel import", "", "", "", $uid, $gids); ?>
					<div class="row">
						<!-- left column -->
						<div class="col-md-6">
							<!-- general form elements -->
							<div class="box box-primary">
								<div class="box-header">
									<h3 class="box-title">Excel Import</h3>
								</div><!-- /.box-header -->
									<div class="box-body">
										<div id="mytext">
											<?php echo $mytext; ?>
										</div>
									</div><!-- /.box-body -->
									<div class="box-footer">
										<form role="form" enctype="multipart/form-data" action="process" method="post">
											<button type="submit" class="btn btn-primary margin" id="sub_button">Submit</button>
											<button type="button" class="btn margin" id="cancel_button" onclick="importBack()">Cancel</button>
											<script>
												function importBack() {
													window.history.go(-1);
												}
											</script>
											<script>
												if (document.getElementById('mytext').innerHTML.trim() == '<h3>The file size is either too large to import or no file was selected.</h3>'){
													document.getElementById('sub_button').style="display:none";
												}else if (document.getElementById('mytext').innerHTML.trim() == '<h3>Processing your spreadsheet...</h3>') {
													document.getElementById('sub_button').style="display:none";
													document.getElementById('cancel_button').style="display:none";
													window.location.href = '<?php echo BASE_PATH ?>' + '/ngsimport/process';
													
												}
											</script>
										</form>
									</div>
								</div><!-- /.box -->
							</div><!-- /.col -->
						</div>   <!-- /.row -->
				</section><!-- /.content -->