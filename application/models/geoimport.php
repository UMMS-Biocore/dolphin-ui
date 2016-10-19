<?php
 
class Geoimport extends Model {
	function getGroup($username) {
        $groups = json_decode($this->query("select g.id, g.name from user_group ug, users u, groups g where ug.u_id=u.id and ug.g_id=g.id and username='$username'"), true);
        $group_str='';
        foreach ($groups as $group):
            $group_str.=$group['id'].",";
        endforeach;
        return rtrim($group_str, ",");
    }
}
