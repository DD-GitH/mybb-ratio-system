<?php

if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}

$plugins->add_hook("index_start", "count_ratio");
$plugins->add_hook("showthread_start", "count_ratio");
$plugins->add_hook("newreply_start", "check");
$plugins->add_hook("showthread_end", "quickreply");
$plugins->add_hook("newthread_do_newthread_end", "count_ratio");
$plugins->add_hook("newreply_do_newreply_end", "count_ratio");
$plugins->add_hook("member_profile_start", "declare_ratio");


function ratio_info()
{
    global $lang;
    $lang->load("ratio");
    return array(
        "name"          => $lang->title_plugin,
        "description"   => $lang->desc_plugin,
        "website"       => "http://www.electrikforums.fr",
        "author"        => "DarSider",
        "authorsite"    => "http://www.electrikforums.fr/member.php?action=profile&uid=1",
        "version"       => "1.0",
        "guid"          => "",
        "codename"      => "ratio_system",
        "compatibility" => "*"
    );
}

function ratio_install()
{
    global $db, $lang;
    $lang->load("ratio");
    $db->query("ALTER TABLE `".TABLE_PREFIX."users` ADD `ratio` DECIMAL(10,3) NOT NULL DEFAULT '0'");
    
    $setting_group = array(  
		'name'			=> 'ratio',
		'title'			=>  $lang->group_title,
		'description'	=>  $lang->group_desc,
		'disporder'		=> '1', // The order that the group is displayed on the settings module
		'isdefault'		=> 'no'
	);
        $db->insert_query('settinggroups', $setting_group);
	$gid = $db->insert_id();
    
    $ratio_setting = array(
		'name'			=> 'ratio_minimum',
		'title'			=> $lang->minimum_title,
		'description'	=> $lang->minimum_desc,
		'optionscode'	=> 'text', 
		'value'			=> '0.1', 
		'disporder'		=> '1', 
		'gid'			=> intval($gid)
	);
	$db->insert_query('settings', $ratio_setting);
    
    $ratio_setting2 = array(
		'name'			=> 'ratio_gids',
		'title'			=> $lang->avoidedgroups_title,
		'description'	=> $lang->avoidedgroups_desc,
		'optionscode'	=> 'text', 
		'value'			=> '', 
		'disporder'		=> '2', 
		'gid'			=> intval($gid)
	);
	$db->insert_query('settings', $ratio_setting2);
    
    $ratio_setting3 = array(
		'name'			=> 'ratio_fids',
		'title'			=> $lang->avoidedfids_title,
		'description'	=> $lang->avoidedfids_desc,
		'optionscode'	=> 'text',
		'value'			=> '', 
		'disporder'		=> '3', 
		'gid'			=> intval($gid)
	);
	$db->insert_query('settings', $ratio_setting3);
    rebuild_settings();
    send_request();
}

function ratio_is_installed()
{
    global $db;
    $table = TABLE_PREFIX.'users';
    $column = 'ratio';
    $query = "SHOW COLUMNS FROM $table LIKE '$column'";
    $result = $db->query($query) or die(mysql_error());
    if($num_rows = $db->num_rows($result) > 0) 
    {
	   return true;
    } 
    else 
    {
	   return false;
    }
}

function ratio_uninstall()
{
    global $db;
    $db->query("ALTER TABLE `".TABLE_PREFIX."users` DROP `ratio`");
    
    $db->write_query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN ('ratio_minimum','ratio_gids','ratio_fids')");

	$db->write_query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name = 'ratio'");
	rebuild_settings();
}

function ratio_activate()
{
    global $lang;
    $lang->load("ratio");
    global $db, $mybb;
    require MYBB_ROOT.'/inc/adminfunctions_templates.php';
    find_replace_templatesets(
        "member_profile",
        "#" . preg_quote('{$referrals}') . "#i",
        '<tr>
            <td class="trow1"><strong>Ratio:</strong></td>
            <td class="trow1">{$ratio}</td>
        </tr>
        {$referrals}'
    );
    
    $template = '<html>
<head>
<title>{$mybb->settings[bbname]}</title>
{$headerinclude}
</head>
<body>
{$header}
<br />
<!-- Content: Start -->
{$lang->error}</br>
<!-- Content: End -->
{$footer}
</body>
</html>';

$insert_array = array(
    'title' => 'nopermtoreply_template',
    'template' => $db->escape_string($template),
    'sid' => '-1',
    'version' => '',
    'dateline' => time()
);

$db->insert_query('templates', $insert_array);
$requette = $db->query("SELECT uid FROM ".TABLE_PREFIX."users");
while($mem = $db->fetch_array($requette))
{
    count_ratiofor($mem['uid']);
} 
}
function ratio_deactivate()
{
    global $db;
    require MYBB_ROOT.'/inc/adminfunctions_templates.php';
    find_replace_templatesets(
        "member_profile",
        "#" . preg_quote('<tr>
            <td class="trow1"><strong>Ratio:</strong></td>
            <td class="trow1">{$ratio}</td>
        </tr>
        {$referrals}') . "#i",
        '{$referrals}'
    );
    $db->delete_query("templates", "title = 'nopermtoreply_template'");
}

include "ratio/functions.php";