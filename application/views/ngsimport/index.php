				<section class="content">
					<div class="row">
						<div class="col-md-6"><!-- left column -->
							<!-- general form elements -->
							<div class="box box-primary">
								<div class="box-header">
									<h3 class="box-title">Excel Import</h3>
									<?php echo $html->getInfoBox("excel_import");?>
								</div><!-- /.box-header -->
								<div class="box-body">
									<div>				
				<!-- form start -->
							<?php echo $html->sendJScript("excel import", "", "", "", $uid, $gids); ?>
								<form role="form" enctype="multipart/form-data" action="ngsimport/process" method="post">
									<div class="box-body">
										<div class="form-group">
											<label for="excelInputFile">Excel file input</label>
											<input type="file" name="excelFile" id="excelFile">
											<p class="help-block">Please choose excel file from your device.</p>
											<a href="<?php echo BASE_PATH?>/public/downloads/example_template.xls">Download example excel input for files in a single directory.</a>
											<br>
											<a href="<?php echo BASE_PATH?>/public/downloads/example_template_multi_dirs.xls">Download example excel input for files in multiple directories.</a>
										</div>
									</div><!-- /.box-body -->
									<div class="box-body">
										<div class="form-group">
											<label for="Group">Project Group</label>
											<?php
						echo $html->getMultipleSelectBox($groups, 'group_id', 'name', 'id');
						?>
											<p class="help-block">Please select a group you belong for this project</p>
										</div>
									</div><!-- /.box-body -->
					<div class="box-body">
										<div class="form-group">
											<label for="Group">Who can see?</label>
											<?php
												$radiofields=array(
														array('name' => 'only me', 'value' => '3', 'selected' => ''),
														array('name' => 'only my group', 'value' => '15', 'selected' => 'checked'),
														array('name' => 'everyone', 'value' => '32', 'selected' => ''));
												echo $html->getRadioBox($radiofields, 'security_id', 'name');
											?>
											<p class="help-block">Please select the security credentials for this import</p>
										</div>
										<div class="callout callout-info">
											<h4>Before Submission:</h4>
											<p>If re-submitting an old import with different data, make sure all the samples you wish to keep are still within the import.</p>
											<p>Any old sample information will be lost if you have removed that data from the excel spreadsheet.</p>
										</div>
									</div><!-- /.box-body -->
									<div class="box-footer">
										<button type="submit" class="btn btn-primary">Submit</button>
									</div>
								</form>
								<script src="<?php echo BASE_PATH?>/js/dolphin/ngs_help.js" type="text/javascript"></script>
								<script src='<?php echo BASE_PATH?>/js/dolphin/js_to_html_funcs.js' type="text/javascript"></script>
