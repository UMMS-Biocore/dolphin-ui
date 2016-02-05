<!-- sidebar menu: : style can be found in sidebar.less -->
<?php
$parentMenus = $query->getParentSideBar();

$menuhtml='                    <ul class="sidebar-menu">
';

foreach ($parentMenus as $parentitem):
    $menuhtml.=($parentitem->{'treeview'})?'<li class="treeview">':'<li>';
    if($parentitem->{'link'} == 'readthedocs'){
        $menuhtml.='<a href="http://dolphin.readthedocs.org"><i class="fa '.$parentitem->{'iconname'}.'"></i> <span>'.$parentitem->{'name'}.'</span>';
    }else{
        $menuhtml.='<a href="'.BASE_PATH.$parentitem->{'link'}.'"><i class="fa '.$parentitem->{'iconname'}.'"></i> <span>'.$parentitem->{'name'}.'</span>';
    }
    
    if ($parentitem->{'treeview'}){
        $menuhtml.='<i class="fa fa-angle-left pull-right"></i></a>';
        $menuhtml.='<ul class="treeview-menu">';
        $menuhtml.=$html->getSideMenuItem($query->getSubMenuFromSideBar($parentitem->{'name'}));
        #$menuhtml.=$html->getSideMenuItem($query->getSubMenuFromDataTables($parentitem->{'name'}));
        $menuhtml.='</ul>';
    }
    else{
        $menuhtml.='</a>';
    }
    $menuhtml.='</li>';

endforeach;
    
$menuhtml.='                    <ul>';
echo $menuhtml;

?> 
<!-- /.sidebar -->
