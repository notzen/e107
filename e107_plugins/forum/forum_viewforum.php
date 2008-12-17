<?php
/*
* e107 website system
*
* Copyright ( c ) 2001-2008 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* View specific forums
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/forum/forum_viewforum.php,v $
* $Revision: 1.11 $
* $Date: 2008-12-17 04:22:37 $
* $Author: mcfly_e107 $
*
*/

require_once('../../class2.php');
include_lan(e_PLUGIN.'forum/languages/English/lan_forum_viewforum.php');

if (isset($_POST['fjsubmit']))
{
	header('location:'.$e107->url->getUrl('forum', 'forum', array('func' => 'view', 'id'=>$_POST['forumjump'])));
	exit;
}

if (!e_QUERY)
{
	header('Location:'.$e107->url->getUrl('forum', 'forum', array('func' => 'main')));
	exit;
}

//$view = 25;
//echo "pref = {$pref['forum_threadspage']} <br />";
$view = (varset($pref['forum_threadspage']) ? $pref['forum_threadspage'] : 25);
//echo "view = $view <br />";
$page = (varset($_GET['p']) ? $_GET['p'] : 0);
$threadFrom = $page * $view;

require_once(e_PLUGIN.'forum/forum_class.php');
$forum = new e107forum;

$STARTERTITLE = LAN_54;
$THREADTITLE = LAN_53;
$REPLYTITLE = LAN_55;
$LASTPOSTITLE = LAN_57;
$VIEWTITLE = LAN_56;

global $forum_info, $FORUM_CRUMB;

$forumId = (int)$_REQUEST['id'];

if (!$forum->checkPerm($forumId, 'view'))
{
	header('Location:'.$e107->url->getUrl('forum', 'forum', array('func' => 'main')));
	exit;
}

$forumInfo = $forum->forum_get($forumId);
$threadsViewed = $forum->threadGetUserViewed();


//var_dump($forumInfo);

if (!$FORUM_VIEW_START)
{
	if (file_exists(THEME.'forum_viewforum_template.php'))
	{
		require_once(THEME.'forum_viewforum_template.php');
	}
	elseif (file_exists(THEME.'forum_template.php'))
	{
		require_once(THEME.'forum_template.php');
	}
	else
	{
		require_once(e_PLUGIN.'forum/templates/forum_viewforum_template.php');
	}
}

$forumInfo['forum_name'] = $e107->tp->toHTML($forumInfo['forum_name'], true, 'no_hook, emotes_off');
$forumInfo['forum_description'] = $e107->tp->toHTML($forumInfo['forum_description'], true, 'no_hook');

$_forum_name = (substr($forumInfo['forum_name'], 0, 1) == '*' ? substr($forumInfo['forum_name'], 1) : $forumInfo['forum_name']);
define('e_PAGETITLE', LAN_01.' / '.$_forum_name);
//define('MODERATOR', $forum_info['forum_moderators'] != '' && check_class($forum_info['forum_moderators']));
//$modArray = $forum->forum_getmods($forum_info['forum_moderators']);

$modArray = $forum->forumGetMods($thread->forum_info['forum_moderators']);
define('MODERATOR', (USER && is_array($modArray) && in_array(USERID, array_keys($modArray))));

$message = '';
if (MODERATOR)
{
	if ($_POST)
	{
		require_once(e_PLUGIN.'forum/forum_mod.php');
		$message = forum_thread_moderate($_POST);
	}
}

if(varset($pref['track_online']))
{
	$member_users = $sql->db_Count('online', '(*)', "WHERE online_location REGEXP('viewforum.php.id=$forumId\$') AND online_user_id != 0");
	$guest_users = $sql->db_Count('online', '(*)', "WHERE online_location REGEXP('viewforum.php.id=$forumId\$') AND online_user_id = 0");
	$users = $member_users+$guest_users;
}

require_once(HEADERF);
$text='';
if ($message)
{
	$ns->tablerender('', $message, array('forum_viewforum', 'msg'));
}

$threadCount = $forumInfo['forum_threads'];

if ($threadCount > $view)
{
	$pages = ceil($threadCount/$view);
}
else
{
	$pages = false;
}

if ($pages)
{
	if(strpos($FORUM_VIEW_START, 'THREADPAGES') !== false || strpos($FORUM_VIEW_END, 'THREADPAGES') !== false)
	{
		$parms = "{$pages},1,{$page},url::forum::forum::func=view&id={$forumId}&page=[FROM], off";
		$THREADPAGES = $e107->tp->parseTemplate("{NEXTPREV={$parms}}");
	}
}

if($forum->checkPerm($forumId, 'post'))
{
	$NEWTHREADBUTTON = "<a href='".$e107->url->getUrl('forum', 'thread', array('func' => 'nt', 'id' => $forumId))."'>".IMAGE_newthread.'</a>';
}

if(substr($forumInfo['forum_name'], 0, 1) == '*')
{
	$forum_info['forum_name'] = substr($forum_info['forum_name'], 1);
	$container_only = true;
}
else
{
	$container_only = false;
}

if(substr($forum_info['sub_parent'], 0, 1) == '*')
{
	$forum_info['sub_parent'] = substr($forum_info['sub_parent'], 1);
}

$forum->set_crumb(); // set $BREADCRUMB (and $BACKLINK)

$FORUMTITLE = $forumInfo['forum_name'];
$MODERATORS = LAN_404.': '.implode(', ', $modArray);
$BROWSERS = '';
if(varset($pref['track_online']))
{
	$BROWSERS = $users.' '.($users == 1 ? LAN_405 : LAN_406).' ('.$member_users.' '.($member_users == 1 ? LAN_407 : LAN_409).", ".$guest_users." ".($guest_users == 1 ? LAN_408 : LAN_410).')';
}


$ICONKEY = "
	<table style='width:100%'>
	<tr>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_new_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_79."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_nonew_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_80."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_sticky_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_202."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_announce_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_396."</td>
	</tr>
	<tr>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_new_popular_small."</td>
	<td style='width:2%' class='smallblacktext'>".LAN_79." ".LAN_395."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_nonew_popular_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_80." ".LAN_395."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_stickyclosed_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_203."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_closed_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_81."</td>
	</tr>
	</table>";

$SEARCH = "
	<form method='get' action='".e_BASE."search.php'>
	<p>
	<input class='tbox' type='text' name='q' size='20' value='' maxlength='50' />
	<input type='hidden' name='r' value='0' />
	<input type='hidden' name='ref' value='forum' />
	<input class='button' type='submit' name='s' value='".LAN_180."' />
	</p>
	</form>";

if($forum->checkPerm($forumId, 'post'))
{
	$PERMS = LAN_204.' - '.LAN_206.' - '.LAN_208;
}
else
{
	$PERMS = LAN_205.' - '.LAN_207.' - '.LAN_209;
}

$sticky_threads = 0;
$stuck = false;
$reg_threads = 0;
$unstuck = false;

$threadList = $forum->forumGetThreads($forumId, $threadFrom, $view);
$subList = $forum->forumGetSubs($forum_id);
$gen = new convert;

$SUBFORUMS = '';
if(is_array($subList) && isset($subList[$forumInfo['forum_parent']][$forumId]))
{
	$newflag_list = $forum->forumGetUnreadForums();
	$sub_info = '';
	foreach($subList[$forumInfo['forum_parent']][$forumId] as $sub)
	{
		$sub_info .= parse_sub($sub);
	}
	$SUBFORUMS = $FORUM_VIEW_SUB_START.$sub_info.$FORUM_VIEW_SUB_END;
}

if (count($threadList) )
{
	foreach($threadList as $thread_info)
	{
		if($thread_info['thread_options'])
		{
			$thread_info['thread_options'] = unserialize($thread_info['thread_options']);
		}
		else
		{
			$thread_info['thread_options'] = array();
		}
		if ($thread_info['thread_sticky'])
		{
			$sticky_threads ++;
		}
		if ($sticky_threads > 0 && !$stuck && $pref['forum_hilightsticky'])
		{
			if($FORUM_IMPORTANT_ROW)
			{
				$forum_view_forum .= $FORUM_IMPORTANT_ROW;
			}
			else
			{
				$forum_view_forum .= "<tr><td class='forumheader'>&nbsp;</td><td colspan='5'  class='forumheader'><span class='mediumtext'><b>".LAN_411."</b></span></td></tr>";
			}
			$stuck = true;
		}
		if (!$thread_info['thread_sticky'])
		{
			$reg_threads ++;
		}
		if ($reg_threads == '1' && !$unstuck && $stuck)
		{
			if($FORUM_NORMAL_ROW)
			{
				$forum_view_forum .= $FORUM_NORMAL_ROW;
			}
			else
			{
				$forum_view_forum .= "<tr><td class='forumheader'>&nbsp;</td><td colspan='5'  class='forumheader'><span class='mediumtext'><b>".LAN_412."</b></span></td></tr>";
			}
			$unstuck = true;
		}
		$forum_view_forum .= parse_thread($thread_info);
	}
}
else
{
	$forum_view_forum .= "<tr><td class='forumheader' colspan='6'>".LAN_58."</td></tr>";
}

$FORUMJUMP = forumjump();
$TOPLINK = "<a href='".e_SELF.'?'.e_QUERY."#top' onclick=\"window.scrollTo(0,0);\">".LAN_02.'</a>';

if($container_only)
{
	$FORUM_VIEW_START = ($FORUM_VIEW_START_CONTAINER ? $FORUM_VIEW_START_CONTAINER : $FORUM_VIEW_START);
	$FORUM_VIEW_END = ($FORUM_VIEW_END_CONTAINER ? $FORUM_VIEW_END_CONTAINER : $FORUM_VIEW_END);
	$forum_view_forum = '';
}

$forum_view_start = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_VIEW_START);
$forum_view_end = preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_VIEW_END);


if ($pref['forum_enclose'])
{
	$ns->tablerender($pref['forum_title'], $forum_view_start.$forum_view_subs.$forum_view_forum.$forum_view_end, array('forum_viewforum', 'main1'));
}
else
{
	echo $forum_view_start.$forum_view_forum.$forum_view_end;
}

echo "<script type=\"text/javascript\">
	function confirm_(thread_id)
	{
		return confirm(\"".$tp->toJS(LAN_434)."\");
	}
	</script>";

require_once(FOOTERF);


function parse_thread($thread_info)
{
	global $forum, $FORUM_VIEW_FORUM, $FORUM_VIEW_FORUM_STICKY, $FORUM_VIEW_FORUM_ANNOUNCE, $gen, $pref, $menu_pref, $threadsViewed;
	$e107 = e107::getInstance();
	$text = '';

	$threadId = $thread_info['thread_id'];
	$forumId = $thread_info['thread_forum_id'];

	$VIEWS = $thread_info['thread_views'];
	$REPLIES = $thread_info['thread_total_replies'];

	if ($REPLIES)
	{
		$lastpost_datestamp = $gen->convert_date($thread_info['thread_lastpost'], 'forum');
		if($thread_info['lastpost_username'])
		{
			$url = $e107->url->getUrl('core:user', 'main', "func=profile&id={$thread_info['thread_lastuser']}");
			$LASTPOST = "<a href='{$url}'>".$thread_info['lastpost_username']."</a>";
		}
		else
		{
			if(!$thread_info['thread_lastuser'])
			{
				$LASTPOST = $e107->tp->toHTML($thread_info['thread_lastuser_anon']);
			}
			else
			{
				$LASTPOST = FORLAN_19;
			}
		}
		$LASTPOST .= '<br />'.$lastpost_datestamp;
	}

	$newflag = (USER && $thread_info['thread_lastpost'] > USERLV && !in_array($thread_info['thread_id'], $threadsViewed));

	$THREADDATE = $gen->convert_date($thread_info['thread_datestamp'], 'forum');
	$ICON = ($newflag ? IMAGE_new : IMAGE_nonew);
	if ($REPLIES >= $pref['forum_popular'])
	{
	  $ICON = ($newflag ? IMAGE_new_popular : IMAGE_nonew_popular);
	}

	$THREADTYPE = '';
	if ($thread_info['thread_sticky'] == 1)
	{
		$ICON = ($thread_info['thread_active'] ? IMAGE_sticky : IMAGE_stickyclosed);
		$THREADTYPE = '['.LAN_202.']<br />';
	}
	elseif($thread_info['thread_sticky'] == 2)
	{
		$ICON = IMAGE_announce;
		$THREADTYPE = '['.LAN_396.']<br />';
	}
	elseif(!$thread_info['thread_active'])
	{
		$ICON = IMAGE_closed;
	}


	$thread_name = strip_tags($e107->tp->toHTML($thread_info['thread_name'], false, 'no_hook, emotes_off'));
	if(isset($thread_info['thread_options']['poll']))
	{
		$thread_name = '['.FORLAN_23.'] ' . $thread_name;
	}
//	if (strtoupper($THREADTYPE) == strtoupper(substr($thread_name, 0, strlen($THREADTYPE))))
//	{
//		$thread_name = substr($thread_name, strlen($THREADTYPE));
//	}
	if ($pref['forum_tooltip'])
	{
		$thread_thread = strip_tags($tp->toHTML($thread_info['thread_thread'], true, 'no_hook'));
		$tip_length = ($pref['forum_tiplength'] ? $pref['forum_tiplength'] : 400);
		if (strlen($thread_thread) > $tip_length)
		{
			$thread_thread = substr($thread_thread, 0, $tip_length).' '.$menu_pref['newforumposts_postfix'];
		}
		$thread_thread = str_replace("'", '&#39;', $thread_thread);
		$title = "title='".$thread_thread."'";
	}
	else
	{
		$title = '';
	}
	$THREADNAME = "<a {$title} href='".$e107->url->getUrl('forum', 'thread', "func=view&id={$threadId}")."'>{$thread_name}</a>";

	$pages = ceil(($REPLIES+1)/$pref['forum_postspage']);
	if ($pages > 1)
	{
		if($pages > 6)
		{
			for($a = 0; $a <= 2; $a++)
			{
				$PAGES .= $PAGES ? ' ' : '';
				$url = $e107->url->getUrl('forum', 'thread', "func=view&id={$thread_info['thread_id']}&page={$a}");
				$PAGES .= "<a href='{$url}'>".($a+1).'</a>';
			}
			$PAGES .= ' ... ';
			for($a = $pages-3; $a <= $pages-1; $a++)
			{
				$PAGES .= $PAGES ? ' ' : '';
				$url = $e107->url->getUrl('forum', 'thread', "func=view&id={$thread_info['thread_id']}&page={$a}");
				$PAGES .= "<a href='{$url}'>".($a+1).'</a>';
			}
		}
		else
		{
			for($a = 0; $a <= ($pages-1); $a++)
			{
				$PAGES .= $PAGES ? ' ' : '';
				$url = $e107->url->getUrl('forum', 'thread', "func=view&id={$thread_info['thread_id']}&page={$a}");
				$PAGES .= "<a href='{$url}'>".($a+1).'</a>';
			}
		}
		$PAGES = LAN_316.' [&nbsp;'.$PAGES.'&nbsp;]';
	}
	else
	{
		$PAGES = '';
	}

	if (MODERATOR)
	{
		$ADMIN_ICONS = "
		<form method='post' action='".$e107->url->getUrl('forum', 'forum', "func=view&id={$thread_info['thread_forum_id']}")."' id='frmMod_{$forumId}_{$threadId}' style='margin:0;'><div>
		<input type='image' ".IMAGE_admin_delete." name='deleteThread_{$threadId}' value='thread_action' onclick=\"return confirm_({$threadId})\" />
		".($thread_info['thread_sticky'] == 1 ? "<input type='image' ".IMAGE_admin_unstick." name='unstick_{$threadId}' value='thread_action' /> " : "<input type='image' ".IMAGE_admin_stick." name='stick_{$threadId}' value='thread_action' /> ")."
		".($thread_info['thread_active'] ? "<input type='image' ".IMAGE_admin_lock." name='lock_{$threadId}' value='thread_action' /> " : "<input type='image' ".IMAGE_admin_unlock." name='unlock_{$threadId}' value='thread_action' /> "). "
		<a href='".$e107->url->getUrl('forum', 'thread', "func=move&id={$threadId}")."'>".IMAGE_admin_move.'</a>
		</div></form>
		';
	}

	$text .= "</td>
		<td style='vertical-align:top; text-align:center; width:20%' class='forumheader3'>".$THREADDATE.'<br />';
//	$tmp = explode('.', $thread_info['thread_user'], 2);

	if($thread_info['user_name'])
	{
		$POSTER = "<a href='".$e107->url->getUrl('core:user', 'main', "func=profile&id={$thread_info['thread_user']}")."'>".$thread_info['user_name']."</a>";
	}
	else
	{
		if($thread_info['thread_user_anon'])
		{
			$POSTER = $e107->tp->toHTML($thread_info['thread_user_anon']);
		}
		else
		{
			$POSTER = FORLAN_19;
		}
	}

	if ($thread_info['thread_sticky'] == 1 && $FORUM_VIEW_FORUM_STICKY)
	{
		return(preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_VIEW_FORUM_STICKY));
	}

	if ($thread_info['thread_sticky'] == 2 && $FORUM_VIEW_FORUM_ANNOUNCE)
	{
		return(preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_VIEW_FORUM_ANNOUNCE));
	}

	if (!$REPLIES)
	{
		$REPLIES = LAN_317;		// 'None'
		$LASTPOST = ' - ';
	}

	return(preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_VIEW_FORUM));
}

function parse_sub($subInfo)
{
	global $FORUM_VIEW_SUB, $gen, $newflag_list;
	$e107 = e107::getInstance();
	$forumName = $e107->tp->toHTML($subInfo['forum_name'], true);
	$SUB_FORUMTITLE = "<a href='".$e107->url->getUrl('forum', 'forum', "func=view&id={$subInfo['forum_id']}")."'>{$forumName}</a>";
	$SUB_DESCRIPTION = $e107->tp->toHTML($subInfo['forum_description'], false, 'no_hook');
	$SUB_THREADS = $subInfo['forum_threads'];
	$SUB_REPLIES = $subInfo['forum_replies'];
	if(USER && is_array($newflag_list) && in_array($subInfo['forum_id'], $newflag_list))
	{
		
		$NEWFLAG = "<a href='".$e107->url->getUrl('forum','forum', 'func=mfar&id='.$subInfo['forum_id'])."'>".IMAGE_new.'</a>';
	}
	else
	{
		$NEWFLAG = IMAGE_nonew;
	}

	if($subInfo['forum_lastpost_info'])
	{
		$tmp = explode('.', $subInfo['forum_lastpost_info']);
		$lp_thread = "<a href='".$e107->url->getUrl('forum', 'thread', array('func' => 'last', 'id' => $tmp[1]))."'>".IMAGE_post2.'</a>';
		$lp_date = $gen->convert_date($tmp[0], 'forum');

		if($subInfo['user_name'])
		{
			$lp_name = "<a href='".$e107->url->getUrl('core:user', 'main', "func=profile&id={$subInfo['forum_lastpost_user']}")."'>{$subInfo['user_name']}</a>";
		}
		else
		{
			$lp_name = $subInfo['forum_lastpost_user_anon'];
		}
		$SUB_LASTPOST = $lp_date.'<br />'.$lp_name.' '.$lp_thread;
	}
	else
	{
		$SUB_LASTPOST = '-';
	}
	return  (preg_replace("/\{(.*?)\}/e", '$\1', $FORUM_VIEW_SUB));
}

function forumjump()
{
	global $forum;
	$jumpList = $forum->forum_get_allowed();
	$text = "<form method='post' action='".e_SELF."'><p>".LAN_403.": <select name='forumjump' class='tbox'>";
	foreach($jumpList as $key => $val)
	{
		$text .= "\n<option value='".$key."'>".$val."</option>";
	}
	$text .= "</select> <input class='button' type='submit' name='fjsubmit' value='".LAN_03."' />&nbsp;&nbsp;&nbsp;&nbsp;<a href='".e_SELF."?".$_SERVER['QUERY_STRING']."#top'>".LAN_02."</a></p></form>";
	return $text;
}

?>