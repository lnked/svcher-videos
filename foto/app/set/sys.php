<?

session_start();

define('g_charset', 'UTF-8');

// Директории приложения
define('g_dir_app',		'app');
define('g_dir_core', 	'core');
define('g_dir_docs', 	'docs');
define('g_dir_nodes', 	'nodes');
define('g_dir_img_tmp', 'img');
define('g_dir_web_tmp', 'tmp');

define('g_path_base', substr(__FILE__, 0, strrpos(str_replace('\\', '/', __FILE__), '/'.g_dir_app.'/')).'/');

define('g_path_app', 		g_path_base.g_dir_app.'/');
define('g_path_nodes',		g_path_app.g_dir_nodes.'/');
define('g_path_docs', 		g_path_base.g_dir_docs.'/');
define('g_path_web_tmp',	g_path_base.g_dir_docs.'/'.g_dir_web_tmp.'/');
define('g_path_plugins',	g_path_app.'plugins/');
define('g_path_cache',		g_path_app.'cache/');
define('g_url_web_tmp',		g_dir_web_tmp.'/');
define('g_path_params',		g_path_app.'set/params.dat');

define('g_path_refs',		'set/refs.txt');
define('g_path_changes',	'app/_def.txt');
define('g_path_reg_changes','app/_def.reg.txt');
define('g_path_int_links',	'app/_def.links.txt');

define('g_reg_changes_step',	1000);
define('g_reg_changes_max',		3);

// Параметры запроса
define('_id',	'id');
define('_act',	'act');
define('_step',	'step');
define('_rt',	'rt');
define('_part',	'p');
define('_part_step', 'ps');

// Параметры сессии
define('_g_user_stat',		'user_stat');
define('_g_user_rights',	'user_rights');
define('_g_user_logout_id',	'user_logout_id');

define('_g_debug_mode',	3);
define('_g_debug_file',	g_path_app.'log/main.txt');


?>