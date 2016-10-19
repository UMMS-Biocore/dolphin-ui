<!-- Content Header (Page header) -->
				<section class="content-header">
					<h1>
						GEO Import
						<small>GEO Fastq generation</small>
					</h1>
					<ol class="breadcrumb">
						<li><a href="<?php echo BASE_PATH?>"><i class="fa fa-dashboard"></i> Home</a></li>
						<li class="active"><?php echo $field?></li>
					</ol>
				</section>
				<?php echo $html->sendJScript("geoimport", "", "", "", $uid, $gids); ?>
				<section class="content">
					<div class="row">
						<div id="name_div" class="col-md-12">
							<?php echo $html->getStaticSelectionBox("Experiment Series Name", "series_name", "TEXT", 6)?>
							<?php echo $html->getStaticSelectionBox("Import Name", "lane_name", "TEXT", 6)?>
						</div>
						
						<div id="sra_search_term_div" class="col-md-12">
							<div class="col-md-12">
								<div class="box box-default">
									<div class="box-header with-border">
										<h3 class="box-title">GEO Accession Search</h3>
										<div class="navbar-right margin">
											<div class="box-tools nav">
												<li class="dropdown user-menu" id="geo_search_term-head" onclick="getInfoBoxData(this.id)">
													<a href="#" class="dropdown-toggle" data-toggle="dropdown">
														<i class="fa fa-info-circle"></i>
													</a>
													<ul class="dropdown-menu">
														<li class="header">
															<h3 style="text-align: center" class="margin">Info</h3>
														</li>
														<li>
															<div id="sra_serach_term_info" class="slimScrollDiv margin" style="position: relative; overflow: hidden; width: 400px; height: auto;">
															</div>
														</li>
														<li class="footer">
														</li>
													</ul>
												</li>
											</div>
										</div>
									</div><!-- /.box-header -->
									<div class="box-body">
										<div class="input-group margin col-md-12">
											<div class="col-md-10">
												<input type="text" class="form-control" id="geo_search_term">
											</div>
											<div class="col-md-2">
												<button class="btn btn-primary" onclick="searchGeoTerm()">Search GEO</button>
											</div>
										</div>
									</div><!-- /.box-body -->
								</div>
							</div>
						</div>
						<div id="searched_sra_div" class="col-md-12">
							<div id="searched_inner_div" class="col-md-12" hidden>
								<?php
								echo $html->getRespBoxTable_ng("Search GEO values", "geo_searched", "<th>GEO Accession</th><th>SRA Available</th><th>Select</th><th>Remove</th>");
								?>
							</div>
						</div>
						<div id="selected_sra_div" class="col-md-12">
							<div id="selected_inner_div" hidden>
								<?php
								echo $html->getRespBoxTable_ng("Selected GEO samples", "geo_selected", "<th>Sample Name</th><th>SRA File</th><th>Fastq File</th><th>Remove</th>");
								?>
							</div>
						</div>
						<div id="download_input_div" class="col-md-12">
							<?php echo $html->getStaticSelectionBox("Download SRA Directory", "download_sra_dir", "TEXT", 12)?>
						</div>
						<div id="import_process_dir_div" class="col-md-12">
							<?php echo $html->getStaticSelectionBox("Import Process Directory", "import_process_dir", "TEXT", 12)?>
						</div>
						
						<div id="perms_div" class="col-md-12">
						<?php echo $html->getStaticSelectionBox("Group Selection", "groups", $html->groupSelectionOptions($groups), 6); ?>
						<?php $radiofields=array(
								array('name' => 'only me', 'value' => '3', 'selected' => ''),
								array('name' => 'only my group', 'value' => '15', 'selected' => 'checked'),
								array('name' => 'everyone', 'value' => '32', 'selected' => '')); ?>
						<?php echo $html->getStaticPermissionsBox("Who has permissions to view?", "permissions", $html->getRadioBox($radiofields, 'security_id', 'name'), 6); ?>
						</div>
					</div><!-- /.row -->
					<div class="row">
						<div class="col-md-12">
							<div class="col-md-12">
								<input type="button" id="submit_geo" class="btn btn-primary pull-right" onclick="submitGeo()" value="Submit GEO Import"/>
							</div>
						</div>
					</div><!-- /.row -->
				</section><!-- /.content -->
				