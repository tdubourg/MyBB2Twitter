<?php

function MyBB2Twitter_update_option($opt, $val) {
	global $db;
	$opt=$db->escape_string($opt);
	if(is_array($val)) {
		$val = serialize($val);
	}
	$val=$db->escape_string($val);
	return $db->query("UPDATE `".TABLE_PREFIX."settings` SET value = '{$val}' WHERE name = '{$opt}'");
}

function MyBB2Twitter_get_option($opt) {
	global $db;
	$opt=$db->escape_string($opt);
	$res = $db->query("SELECT value FROM `".TABLE_PREFIX."settings` WHERE name = '{$opt}'");
	$tmp = $db->fetch_field($res, 'value');
	return (is_serialized($tmp)) ? unserialize($tmp) : $tmp;
}

function MyBB2Twitter_add_option($opt, $val) {
	global $db;
	$opt=$db->escape_string($opt);
	$val=$db->escape_string($val);
	$res = $db->query("INSERT INTO `".TABLE_PREFIX."settings` (`sid`, `name`, `title`, `description`, `optionscode`, `value`, `disporder`, `gid`) VALUES (NULL, '{$opt}', '{$opt}', '', 'text', '', 0, (SELECT gid FROM ".TABLE_PREFIX."settinggroups WHERE name = 'mybb2twitter'))");
	return $db->insert_id();
	// return update_option($opt, $val);
}

function MyBB2Twitter_delete_option($opt) {
	global $db;
	$opt=$db->escape_string($opt);
	return $db->query("DELETE FROM `".TABLE_PREFIX."settings` WHERE name = '{$opt}'");
	// return @unlink(MYBB_ROOT.'/inc/plugins/MyBB2Twitter/'.$opt.'.conf');
}
?>