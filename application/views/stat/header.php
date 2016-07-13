<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Biocore-Dolphin</title>
		<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
		<link href="<?php echo BASE_PATH?>/css/bootstrap/bootstrap.min.css" rel="stylesheet" type="text/css" />
		<link href="<?php echo BASE_PATH?>/css/font-awesome-4.6.1/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
		<link href="<?php echo BASE_PATH?>/css/datatables/jquery-ui.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo BASE_PATH?>/css/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo BASE_PATH?>/css/datatables/dataTables.tableTools.css" rel="stylesheet" type="text/css" />
		<link href="<?php echo BASE_PATH?>/css/datatables/dataTables.editor.bootstrap.css" rel="stylesheet" type="text/css" />
		<!-- Date Picker -->
		<link href="<?php echo BASE_PATH?>/css/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
		<!-- Daterange picker -->
		<link href="<?php echo BASE_PATH?>/css/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
		<!-- Ionicons -->
		<link href="<?php echo BASE_PATH?>/css/ionicons/ionicons.min.css" rel="stylesheet" type="text/css" />
		<!-- Morris charts -->
		<link href="<?php echo BASE_PATH?>/css/morris/morris.css" rel="stylesheet" type="text/css" />
		<!-- Theme style -->
		<link href="<?php echo BASE_PATH?>/css/AdminLTE.css" rel="stylesheet" type="text/css" />
		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		<script src="<?php echo BASE_PATH?>/js/plugins/html5shiv/3.7.0/html5shiv.min.js"></script>
		<script src="<?php echo BASE_PATH?>/js/plugins/respond/1.3.0/respond.min.js"></script>
		<![endif]-->
        <?php echo $html->getBasePath(BASE_PATH, API_PATH); ?>
		<style>
			.directory-col	{
				word-break: break-all;
				min-width:300px
			}
		</style>
	</head>
	<body class="skin-blue">
		<!-- header logo: style can be found in header.less -->
		<header class="header">
			<a href="<?php echo BASE_PATH;?>" class="logo">
				<!-- Add the class icon to your logo image or logo icon to add the margining -->
				Biocore - Dolphin
			</a>
			<!-- Header Navbar: style can be found in header.less -->
			<nav class="navbar navbar-static-top" role="navigation">
				<!-- Sidebar toggle button-->
				<a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
				<div class="navbar-right">
					<ul class="nav navbar-nav">
						<?php require_once("../includes/messages.php");?>
						<?php require_once("../includes/notifications.php");?>
						<?php require_once("../includes/tasks.php");?>
						<?php require_once("../includes/useraccount.php"); ?>
					</ul>
				</div>
			</nav>
		</header>
		<div class="wrapper row-offcanvas row-offcanvas-left">
			<!-- Left side column. contains the logo and sidebar -->
			<aside class="left-side sidebar-offcanvas">
				<!-- sidebar: style can be found in sidebar.less -->
				<section class="sidebar">
					<?php require_once("../includes/sidebaruserpanel.php");?>
					<?php require_once("../includes/search.php");?>
					<?php require_once("../includes/sidebarmenu.php");?>
			</aside>

			<!-- Right side column. Contains the navbar and content of the page -->
			<aside class="right-side">
