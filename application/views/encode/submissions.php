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
				<section class="content">
					<div class="row">
						<div class="col-md-12">
							<div id="sub_table" class="margin col-md-12">
								<?php echo $html->getRespBoxTable_ng("Encode Submissions", "submissions_table", "<th>ID</th><th>File</th><th>Submission Status</th><th>Options</th>"); ?>
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