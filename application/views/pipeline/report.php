<!-- Content Header (Page header) -->
				<section class="content-header">
					<h1>
						NGS Pipeline
						<small>Workflow creation</small>
					</h1>
					<ol class="breadcrumb">
						<li><a href="<?php echo BASE_PATH?>"><i class="fa fa-dashboard"></i> Home</a></li>
			<li><a href="<?php echo BASE_PATH."/search"?>">NGS Pipeline</a></li>
			<li class="active"><?php echo $field?></li
					</ol>
				</section>
				<!-- Main content -->
				<section class="content">
					<?php echo $html->sendJScript("report", "", "", "", $uid, $gids); ?>
					<div class="row">
						<div class="col-md-12">
						<div class="panel panel-default">
				<div class="panel-heading">
				<h4>Analysis Results <small>Comprehensive Analysis</small></h4>
				</div>
				<div class="panel-body">
					<div id='empty_div'>
					</div>
					<?php echo $html->getExpandingAnalysisBox('Initial Mapping Results', "initial_mapping", true); ?>
					<?php echo $html->getExpandingAnalysisBox('FastQC Summary', "summary", false); ?>
					<?php echo $html->getExpandingAnalysisBox('Detailed FastQC Results', "details", false); ?>
					<?php echo $html->getExpandingAnalysisBox('RSEM Results', "RSEM", false); ?>
					<?php echo $html->getExpandingAnalysisBox('DESeq Results', "DESEQ", false); ?>
					<?php echo $html->getExpandingAnalysisBox('Picard Metrics', "picard", false); ?>
					<?php echo $html->getExpandingAnalysisBox('RSeQC', "rseqc", false); ?>
				</div>
				<div class="input margin">
					<button id="back_to_status" class="btn btn-primary" onclick="sendToStatus()">Return to Status</button>
					<button id="send_to_plots" class="btn btn-primary" onclick="sendToPlots()">Go to Plots</button>
				</div>
			</div>
						</div><!-- /.col (RIGHT) -->
					</div><!-- /.row -->
				</section><!-- /.content -->
