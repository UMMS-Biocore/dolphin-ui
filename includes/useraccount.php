<?php
  require_once("dbfuncs.php");
  $query=new dbfuncs();
  $line = preg_split("/[,\s\t]+/", $query->getName($_SESSION['user']));
  if(count($line) > 1){
    $name=$line[1]." ".$line[0];  
  }else{
    $name=$line[0];
  }
  
  $role=$query->getRole($_SESSION['user']);
  $membersince="June 2012";
  $avatar=BASE_PATH.$query->getPhotoLoc($_SESSION['user']);
  
?>

<!-- User Account: style can be found in dropdown.less -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="glyphicon glyphicon-user"></i>
                                <span><?php print $name;?><i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                <li class="user-header bg-light-blue">
                                    <img src="<?=$avatar?>" class="img-circle" alt="User Image" />
                                    <p>
                                        <?php print $name."-".$role; ?>
                                        <small>Member since <?php print $membersince;?></small>
                                    </p>
                                </li>
                                <!-- Menu Body -->
                                <li class="user-body">
                                    <div class="col-xs-4 text-center">
                                        <a href="<?php echo BASE_PATH?>/ngsimport">Import</a>
                                    </div>
                                    <div class="col-xs-4 text-center">
                                        <a href="<?php echo BASE_PATH?>/search">Browser</a>
                                    </div>
                                    <div class="col-xs-4 text-center">
                                        <a href="<?php echo BASE_PATH?>/stat/status">Status</a>
                                    </div>
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="<?php echo BASE_PATH?>/profile" class="btn btn-default btn-flat">Profile</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="<?php echo BASE_PATH?>/index.php?p=logout" class="btn btn-default btn-flat">Sign out</a>
                                    </div>
                                </li>
                            </ul>
                        </li>

