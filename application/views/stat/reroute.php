				<section class="content-header">
					<h1>NGS Run Status<small>Admin Reroute</small></h1>
					<ol class="breadcrumb">
						<li><a href="<?php echo BASE_PATH?>"><i class="fa fa-dashboard"></i> Home</a></li>
						<li><a href="<?php echo BASE_PATH."/search"?>">NGS Pipeline</a></li>
						<li class="active"><?php echo $field?></li>
					</ol>
				</section>
				<!-- Main content -->
				<?php echo $html->sendJScript('reroute', "", "", "", $uid, $gids); ?>
				<section class="content">
					<div class="row">
						<div class="col-md-12">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 style="word-break: break-all;"><b>Rerouting...</b></h4>
									<?php $_SESSION['adv_status_id'] = $run_id ?>
								</div>
							</div>
						</div>
					</div><!-- /.row -->
				</section><!-- /.content -->
