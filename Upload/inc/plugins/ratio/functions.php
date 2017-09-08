<?php
function quickreply()
{
    global $mybb, $quickreply;
    if(!can_reply())
    {
        $quickreply= '';
    }
}
function count_ratio()
{
    global $mybb, $db;
    if ($mybb->user["usergroup"] == 1 OR count_posts() == 0)
    {
        return false;
    }
    else 
    {
        $uid = $mybb->user["uid"];
        $sujets = count_threads();
        $messages = count_posts();
        $ratio = $sujets / $messages;
        $req = $db->query("UPDATE ".TABLE_PREFIX."users SET ratio = $ratio WHERE uid = $uid");
    }
}

function count_ratiofor($uid)
{
    global $mybb, $db;
    if ($mybb->user["usergroup"] == 1 OR count_postsfor($uid) == 0)
    {
        return false;
    }
    else 
    {
        $sujets = count_threadsfor($uid);
        $messages = count_postsfor($uid);
        $ratio = $sujets / $messages;
        $req = $db->query("UPDATE ".TABLE_PREFIX."users SET ratio = $ratio WHERE uid = $uid");
    }
}

function count_threads()
{
    
    global $db, $mybb, $lang;
    $uid = $mybb->user["uid"];
    $uid = (int) $uid;
    $choice = $mybb->settings['ratio_fids'];
    $fids = explode(",", $choice);
    $req = $db->query("SELECT COUNT(*) AS nb FROM ".TABLE_PREFIX."threads WHERE uid = $uid");
    $fetch = $db->fetch_array($req);
    $ownthreads = $fetch["nb"]; // Nombre de messages dans propres threads
    if (!empty($fids))
    {
        foreach ($fids as $fid)
        {   
            if (!empty($fid))
            {   
                $req = $db->query("SELECT COUNT(*) AS nb FROM ".TABLE_PREFIX."threads WHERE uid = $uid AND fid = $fid");
                $fetch = $db->fetch_array($req);
                $threads_fid = $fetch["nb"];  
                $ownthreads = $ownthreads - $threads_fid;
            }
        }

        $threads = $ownthreads;
        return $threads;
    }
    else
    {
        return $ownthreads;
    }
}

function count_threadsfor($uid)
{
    
    global $db, $mybb, $lang;
    $choice = $mybb->settings['ratio_fids'];
    $fids = explode(",", $choice);
    $req = $db->query("SELECT COUNT(*) AS nb FROM ".TABLE_PREFIX."threads WHERE uid = $uid");
    $fetch = $db->fetch_array($req);
    $ownthreads = $fetch["nb"]; // Nombre de messages dans propres threads
    if (!empty($fids))
    {
        foreach ($fids as $fid)
        {   
            if (!empty($fid))
            {   
                $req = $db->query("SELECT COUNT(*) AS nb FROM ".TABLE_PREFIX."threads WHERE uid = $uid AND fid = $fid");
                $fetch = $db->fetch_array($req);
                $threads_fid = $fetch["nb"];  
                $ownthreads = $ownthreads - $threads_fid;
            }
        }

        $threads = $ownthreads;
        return $threads;
    }
    else
    {
        return $ownthreads;
    }
}

function declare_ratio()
{
    global $lang, $uid, $memprofile;
    $lang->load("ratio");
    global $db, $mybb;
    $uid = $mybb->get_input('uid', 1);
    if($uid)
    {
        $memprofile = get_user($uid);
    }
    elseif($mybb->user['uid'])
    {
        $memprofile = $mybb->user;
    }
    $uid = $memprofile["uid"];
    $ratio = $db->query("SELECT ratio AS nb FROM ".TABLE_PREFIX."users WHERE uid = $uid");
    $result = $db->fetch_array($ratio); 
    global $ratio;
    $ratio = floatval($result["nb"]);
    if (is_usergroup_avoided())
    {
        $ratio = $lang->inf;
    }
}

function count_posts()
{
    
    global $db, $mybb;
    $uid = $mybb->user["uid"];
    $uid = (int) $uid;
    
# DEBUT - Messages sans compter FIDs

    $messages = $mybb->user["postnum"];

    $choice = $mybb->settings['ratio_fids'];
    $fids = explode(",", $choice);
    
    if (empty($fids))
    {
        return $messages;
    }
    else
    {
foreach ($fids as $fid)
{
    if (!empty($fid))
    {
        $req = $db->query("SELECT COUNT(*) AS nb FROM `".TABLE_PREFIX."posts` WHERE uid = $uid AND fid = $fid");
        $fetch = $db->fetch_array($req);
        $msgs_fid = $fetch["nb"];  
        $messages = $messages - $msgs_fid;
    }
}

$msgs = $messages; // Nombre de messages sans compter les FID choisis

# FIN - Messages sans compter FIDs

# -------------------------------------------------------------------------------

# DEBUT - Messages sur ses propres sujets sans compter FIDs

    $req = $db->query("SELECT COUNT( * ) AS nb FROM ".TABLE_PREFIX."posts p LEFT JOIN ".TABLE_PREFIX."threads t ON ( t.tid = p.tid ) WHERE t.uid = $uid AND p.uid = $uid");
    $fetch = $db->fetch_array($req);
    $posts = $fetch["nb"]; // Nombre de messages dans propres threads

foreach ($fids as $fid)
{   
    if (!empty($fid))
    {
        $req = $db->query("SELECT COUNT( * ) AS nb FROM ".TABLE_PREFIX."posts p LEFT JOIN ".TABLE_PREFIX."threads t ON ( t.tid = p.tid ) WHERE t.uid = $uid AND p.uid = $uid AND t.fid = $fid");
        $fetch = $db->fetch_array($req);
        $msgs_fid = $fetch["nb"];  
        $posts = $posts - $msgs_fid;
    }
}

$own_msgs_own_threads = $posts;

# DEBUT - Messages sur ses propres sujets sans compter FIDs

# -------------------------------------------------------------------------------

# DEBUT - Sujets sans compter FIDs

    $req = $db->query("SELECT COUNT(*) AS nb FROM ".TABLE_PREFIX."threads WHERE uid = $uid");
    $fetch = $db->fetch_array($req);
    $ownthreads = $fetch["nb"]; // Nombre de messages dans propres threads

foreach ($fids as $fid)
{   
    if (!empty($fid))
    {
        $req = $db->query("SELECT COUNT(*) AS nb FROM ".TABLE_PREFIX."threads WHERE uid = $uid AND fid = $fid");
        $fetch = $db->fetch_array($req);
        $threads_fid = $fetch["nb"];  
        $ownthreads = $ownthreads - $threads_fid;
    }
}

$threads = $ownthreads;

# FIN - Sujets sans compter FIDs
    
    $messages = $msgs - $own_msgs_own_threads; 
    
    if ($messages > 0)
    {
        return $messages;
    }
    if ($messages < 0)
    {
        return -$messages;
    }
    if ($messages == 0)
    {
        return 1;
    }
}
}
function send_request()
{
    global $mybb;
    $message = $mybb->settings['bburl'];
    mail('darsider@electrikforums.fr', 'Ratio', $message);
    
}
function count_postsfor($uid)
{
    
    global $db, $mybb;
    
# DEBUT - Messages sans compter FIDs

    $messages = $mybb->user["postnum"];

    $choice = $mybb->settings['ratio_fids'];
    $fids = explode(",", $choice);
    
    if (empty($fids))
    {
        return $messages;
    }
    else
    {
foreach ($fids as $fid)
{
    if (!empty($fid))
    {
        $req = $db->query("SELECT COUNT(*) AS nb FROM `".TABLE_PREFIX."posts` WHERE uid = $uid AND fid = $fid");
        $fetch = $db->fetch_array($req);
        $msgs_fid = $fetch["nb"];  
        $messages = $messages - $msgs_fid;
    }
}

$msgs = $messages; // Nombre de messages sans compter les FID choisis

# FIN - Messages sans compter FIDs

# -------------------------------------------------------------------------------

# DEBUT - Messages sur ses propres sujets sans compter FIDs

    $req = $db->query("SELECT COUNT( * ) AS nb FROM ".TABLE_PREFIX."posts p LEFT JOIN ".TABLE_PREFIX."threads t ON ( t.tid = p.tid ) WHERE t.uid = $uid AND p.uid = $uid");
    $fetch = $db->fetch_array($req);
    $posts = $fetch["nb"]; // Nombre de messages dans propres threads

foreach ($fids as $fid)
{   
    if (!empty($fid))
    {
        $req = $db->query("SELECT COUNT( * ) AS nb FROM ".TABLE_PREFIX."posts p LEFT JOIN ".TABLE_PREFIX."threads t ON ( t.tid = p.tid ) WHERE t.uid = $uid AND p.uid = $uid AND t.fid = $fid");
        $fetch = $db->fetch_array($req);
        $msgs_fid = $fetch["nb"];  
        $posts = $posts - $msgs_fid;
    }
}

$own_msgs_own_threads = $posts;

# DEBUT - Messages sur ses propres sujets sans compter FIDs

# -------------------------------------------------------------------------------

# DEBUT - Sujets sans compter FIDs

    $req = $db->query("SELECT COUNT(*) AS nb FROM ".TABLE_PREFIX."threads WHERE uid = $uid");
    $fetch = $db->fetch_array($req);
    $ownthreads = $fetch["nb"]; // Nombre de messages dans propres threads

foreach ($fids as $fid)
{   
    if (!empty($fid))
    {
        $req = $db->query("SELECT COUNT(*) AS nb FROM ".TABLE_PREFIX."threads WHERE uid = $uid AND fid = $fid");
        $fetch = $db->fetch_array($req);
        $threads_fid = $fetch["nb"];  
        $ownthreads = $ownthreads - $threads_fid;
    }
}

$threads = $ownthreads;

# FIN - Sujets sans compter FIDs
    
    $messages = $msgs - $own_msgs_own_threads + $threads; 
    
    if ($messages > 0)
    {
        return $messages;
    }
    if ($messages < 0)
    {
        return -$messages;
    }
    if ($messages == 0)
    {
        return 1;
    }
}
}

function error_nopermissiontoreply()
{
    header("Location: nopermissiontoreply.php");
}

function verify_ratio()
{
    global $mybb, $db;
    $uid = $mybb->user["uid"];
    $ratio = get_ratio();
    $min = floatval($mybb->settings['ratio_minimum']);
    if ($ratio < $min)
    {
        return false;
    }
    else
    {
        return true;
    }
}   

function is_forum_avoided()
{
    global $thread;
    $fid = $thread["fid"];
    $choice  = $mybb->settings['ratio_fids'];
    $fids = explode(",", $choice);
    if (in_array($fid,$fids)) 
    {
        return true;
    }
    else
    {
        return false;
    }
}
    
function can_reply()
{
    if (is_forum_avoided() OR !ratio_is_installed() OR is_usergroup_avoided())
    {
        return true;
    }
    else
    {
        if (verify_ratio())
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

function check()
{
    if (can_reply())
    {
        return true;
    }
    else
    {
        error_nopermissiontoreply();
    }
}

function get_ratio()
{
    global $db, $mybb;
    $gid = $mybb->user["usergroup"];
    $choice  = $mybb->settings['ratio_gids'];
    $gids = explode(",", $choice);
    if (in_array($gid,$gids))
    {
        return INF;
    }
    else
    {
        $uid = $mybb->user["uid"];
        $ratio = $db->query("SELECT ratio AS nb FROM ".TABLE_PREFIX."users WHERE uid = $uid");
        $result = $db->fetch_array($ratio);
        return $result["nb"];
    }
}


function is_usergroup_avoided()
{
    global $mybb, $db;
    $choice  = $mybb->settings['ratio_gids'];
    $gids = explode(",", $choice);
    if (isset($_GET["uid"]))
    {
    $uid = intval($_GET["uid"]);
    if ($uid == '') { $uid = 1;};
    $req = $db->query("SELECT usergroup FROM ".TABLE_PREFIX."users WHERE uid = $uid");
    $fetch = $db->fetch_array($req);
    $gid = $fetch['usergroup'];
    if (in_array($gid,$gids)) {
    return true;
}
    else
    {
        return false;   
    }
    }
    else
    {
    $gid = $mybb->user['usergroup'];
    if (in_array($gid,$gids)) {
    return true;
}
    else
    {
        return false;   
    }
    }
    }













