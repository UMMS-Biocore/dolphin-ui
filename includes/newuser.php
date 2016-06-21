<!DOCTYPE html>
<html class="bg-black">
    <head>
        <meta charset="UTF-8">
        <title>Biocore - Dolphin | New User</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="css/AdminLTE.css" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="bg-black">
        <div class="form-box" id="login-box">
            <div class="header">New User</div>
            <form action="<?php echo BASE_PATH?>/" method="post">
                <div class="body bg-gray">
					<div class="text-center form-group">
                        <input type="text" name="firstname" class="form-control" placeholder="First name" value="<?php echo $firstname_val ?>"/>
						<?php echo $err_firstname ?>
                    </div>
					<div class="text-center form-group">
                        <input type="text" name="lastname" class="form-control" placeholder="Last name" value="<?php echo $lastname_val ?>"/>
						<?php echo $err_lastname ?>
                    </div>
                    <div class="text-center form-group">
                        <input type="text" name="username" class="form-control" placeholder="UMASS email username" value="<?php echo $username_val ?>"/>
						<?php echo $err_username ?>
                    </div>
					<div class="text-center form-group">
                        <input type="text" name="clustername" class="form-control" placeholder="GHPCC clustername" value="<?php echo $clustername_val ?>"/>
						<?php echo $err_clustername ?>
                    </div>
					<div class="text-center form-group">
                        <input type="text" name="institute" class="form-control" placeholder="Institute" value="<?php echo $institute_val ?>"/>
						<?php echo $err_institute ?>
                    </div>
					<div class="text-center form-group">
                        <input type="text" name="lab" class="form-control" placeholder="Lab" value="<?php echo $lab_val ?>"/>
						<?php echo $err_lab ?>
                    </div>
					<div class="text-center form-group">
                        <input type="text" name="email" class="form-control" placeholder="Email" value="<?php echo $email_val ?>"/>
						<?php echo $err_email ?>
                    </div>
					<div class="text-center form-group">
                        <input type="password" name="password" class="form-control password" placeholder="Password" value="<?php echo $password_val ?>"/>
						<?php echo $err_password ?>
                    </div>
					<div class="text-center form-group">
                        <input type="password" name="verifypassword" class="form-control password" placeholder="Verify Password"/>
						<?php echo $err_verifypassword ?>
                    </div>
                </div>
                <div class="footer">
                    <button type="submit" name="request" class="btn bg-olive btn-block">Submit Request</button>
					<button type="submit" name="ok" class="btn bg-olive btn-block">Back</button>
                </div>
            </form>
        </div>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>
    </body>
</html>

