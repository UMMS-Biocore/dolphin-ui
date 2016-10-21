                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        Dashboard
                        <small>Control panel</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Dashboard</li>
                    </ol>
                </section>
                <!-- Main content -->
                <section class="content">
                    <?php
                        require_once("../includes/globalmessage.php");
                        require_once("../includes/smallboxes.php");
                    ?>

                    <!-- Main row -->
                    <div class="row">
                        <!-- Left col -->
                        <section class="col-lg-7 connectedSortable">
                            <?php require_once("../includes/bar_chart.php");?>
                            <?php require_once("../includes/charts.php");?>
                      
                        </section><!-- /.Left col -->
                        <!-- right col (We are only adding the ID to make the widgets sortable)-->
                        <section class="col-lg-5 connectedSortable"> 
                            <?php require_once("../includes/totalruns.php");?>
                            <?php require_once("../includes/quickemail.php");?>
                        </section>
                </section><!-- /.content -->

