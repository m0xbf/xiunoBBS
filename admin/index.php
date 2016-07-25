<?php

define('DEBUG', 1); 				// 发布的时候改为 0 

ob_start('ob_gzhandler');

$conf = (@include '../conf/conf.php') OR exit(header('Location: install/'));
$conf['log_path'] = '../'.$conf['log_path'];
$conf['tmp_path'] = '../'.$conf['log_path'];
$conf['upload_path'] = '../'.$conf['log_path'];

if(DEBUG) {
	include '../xiunophp/xiunophp.php';
} else {
	include '../xiunophp/xiunophp.min.php';
}

// 后台路径
$admin = 'admin';

include '../model.inc.php';
include "./admin.func.php";


// 测试数据库连接
db_connect() OR message(-1, $errstr);

$sid = sess_start();


// 语言包
$lang = include('../lang/zh-cn.php');

// 用户
$uid = _SESSION('uid');
$user = user_read($uid);

// 用户组
$gid = empty($user) ? 0 : $user['gid'];
$grouplist = group_list_cache();
$group = isset($grouplist[$gid]) ? $grouplist[$gid] : array();

// 版块
$fid = 0;
$forumlist = forum_list_cache();
$forumlist_show = forum_list_access_filter($forumlist, $gid);	// 有权限查看的板块

// 头部 header.inc.htm 
$header = array(
	'title'=>$conf['sitename'],
	'keywords'=>'',
	'description'=>'',
	'navs'=>array(),
);

// 运行时数据
$runtime = runtime_init();

$menu = include './menu.conf.php';

// 检测浏览器， 不支持 IE8
$browser = get__browser();
check_browser($browser);

// 检测站点运行级别
check_runlevel();

// 检测 IP 封锁，可以作为自带插件
//check_banip($ip);

// 记录 POST 数据
//DEBUG AND xn_log_post_data();

// 全站的设置数据，站点名称，描述，关键词，页脚代码等，应该存入 kv
$setting = kv_get('setting');

//DEBUG AND ($method == 'POST' || $ajax) AND sleep(1);

$route = param(0, 'index');

// 只允许管理员登陆后台
// 对于越权访问，可以默认为黑客企图，不用友好提示。
$gid != 1 AND http_403();

// 管理员令牌检查
admin_token_check();

// todo: HHVM 不支持动态 include $filename
switch ($route) {
	case 'index':		include './route/index.php'; 		break;
	case 'setting': 	include './route/setting.php'; 		break;
	case 'forum': 		include './route/forum.php'; 		break;
	case 'friendlink': 	include './route/friendlink.php'; 	break;
	case 'group': 		include './route/group.php'; 		break;
	case 'user':		include './route/user.php'; 		break;
	default: http_404();
}

?>