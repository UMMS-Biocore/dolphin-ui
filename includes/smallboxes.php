<?php
  $username=$_SESSION['user'];
  $totalruns=$query->getTotalGalaxyRuns($username);
  $totalruntext="Total Galaxy runs";
  $totaldolphinruns=$query->getTotalDolphinRuns($username);
  $totaldolphinruntext="Total Dolphin runs";
  $totalsamples=$query->getTotalSamples($username);
  $totalsamplestext="Total Samples";
  $totalclusterjobs=$query->getTotalJobs($username);
  $totalclusterjobstext="Total cluster submissions";
?>
		    <!-- Small boxes (Stat box) -->
                    <div class="row">
                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box bg-aqua">
                                <div class="inner">
                                    <h3>
                                        <?php print $totalruns;?>
                                    </h3>
                                    <p>
                                        <?php print $totalruntext;?>
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-stats-bars"></i>
                                </div>
                                <a href="galaxystats" class="small-box-footer">
                                    More info <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div><!-- ./col -->
                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h3>
                                        <?php print $totaldolphinruns;?>
                                    </h3>
                                    <p>
                                        <?php print $totaldolphinruntext;?>
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-stats-bars"></i>
                                </div>
                                <a href="dolphinstats" class="small-box-footer">
                                    More info <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div><!-- ./col -->
                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box bg-yellow">
                                <div class="inner">
                                    <h3>
                                        <?php print $totalsamples;?>
                                        
                                    </h3>
                                    <p>
                                        <?php print $totalsamplestext;?>
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-pie-graph"></i>
                                </div>
                                <a href="search" class="small-box-footer">
                                    More info <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div><!-- ./col -->
                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box bg-red">
                                <div class="inner">
                                    <h3>
                                        <?php print $totalclusterjobs;?>
                                    </h3>
                                    <p>
                                        <?php print $totalclusterjobstext;?>
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-pie-graph"></i>
                                </div>
                                <a href="#" class="small-box-footer">
                                    More info <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div><!-- ./col -->
                    </div><!-- /.row -->
