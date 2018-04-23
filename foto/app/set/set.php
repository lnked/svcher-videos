<?

define('g_timezone',	'Europe/Moscow');
date_default_timezone_set(g_timezone);

define('g_domain_def',		'');
define('g_app_name',		'');
define('g_app_tagline',		'');
define('g_app_title',		'');
define('g_app_copyright',	'');
define('g_app_copyright_year', date('Y'));

define('g_email',			'');				// электронная почта
define('g_tel',				'+7(800) 123-45-78');			// основной телефон
define('g_tel_2',			'');				// дополнительный телефон
define('g_tel_ex',			'');			// дополнительный телефон
define('g_fax',				'');			// факс

define('g_email_to',		'info@abc.ru');				// эл. почта для всех запросов через формы
define('g_email_account',	'');							// эл. почта для выставления счета
define('g_email_from',		'robot@abc.ru');				// эл. почта, с которой будут отправляться запросы через формы

define('g_chpu',			false);
//define('g_chpu',			true);
define('g_app_logo_img', 	false);
define('g_app_logo_img_sm', false);

define('g_pm_id',			_id);
define('g_pm_pgn',			'pg');
//define('g_pm_srch',			'w');
define('g_pm_srch',			'txt');
define('g_pm_ht_pos',		'ht');
define('g_pm_ht_what',		'w');

define('g_pv_srch',			501);
define('g_share',			7);

define('g_dir_files',		g_path_nodes.'base/');	// дополнительные директории для поиска html-страниц и др. файлов. 

define('g_also_ext',		'mode=>direct, ref=>');

define('g_com_name', 		'');
define('g_com_address', 	'г. Краснодар, ул. Красная 124, оф. 15');
define('g_com_gps_center', 	'38.97251, 45.014447');
define('g_com_gps_mark', 	'38.97251, 45.014447');

?>