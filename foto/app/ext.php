<?

include_once('../core/mail.php');
include_once('../core/form.php');
include_once('../core/fm.php');

//d:/VC/MxImg/app/projects
define('d_path_proj_int', '../projects');
define('d_path_data', '../app/@data');
define('d_path_params', '../app/@data/params.dat');
define('d_path_send_data', '../app/@data/stat.dat');
define('d_index_file', 'index.php');

define('d_mail_subject', 'Видео-файл');
define('d_mail_text', 'См. прикрепленный файл');
define('d_mail_subject_test', 'Тестирование почты');
define('d_mail_text_test', 'Тестовое сообщение');

define('d_pm_mail', 2);
define('d_pm_params', 3);
define('d_pm_login', 5);
define('d_pm_fm', 6);
define('d_pm_report', 7);
define('d_pm_report_create', 8);
define('d_pm_test_mail', 9);
define('d_pm_report_proj', 11);
define('d_pm_report_mail', 12);
define('d_user_admin_rights', '1000');
define('d_data_save_sign', 'data_save_sign');
define('d_email_sent_sign', 'email_sent_sign');

$_arr_changes = array();
$_arr_stat = array();
$_arr_proj = array();

$_af_params = array(

	array('name'=>'event', 'title'=>'Наименование мероприятия', 'type'=>'text', 'max'=>120, 'min'=>3),		
	array('name'=>'logo', 'title'=>'Логотип', 'type'=>'text', 'max'=>250),
	array('name'=>'dir_video', 'title'=>'Папка с готовыми проектами', 'type'=>'text', 'max'=>120, 'min'=>5),
	array('name'=>'dir_photo', 'title'=>'Папка с фото', 'type'=>'text', 'max'=>120),
	array('name'=>'mode', 'title'=>'Режим работы', 'type'=>'number', 'tag'=>'select', 'list'=>array(1=>'видео-файл', 2=>'gif-файл')),
	array('name'=>'pswd', 'title'=>'Пароль', 'type'=>'text', 'max'=>50, 'min'=>3),
	array('name'=>'period', 'title'=>'Время просмотра, сек', 'type'=>'number', 'max'=>300, 'min'=>1),
	array('name'=>'email_address', 'title'=>'Адрес отправителя', 'type'=>'email', 'max'=>120, 'min'=>6),
	array('name'=>'email_pswd', 'title'=>'Пароль эл. ящика', 'type'=>'text', 'max'=>50, 'min'=>3),
	array('name'=>'email_host', 'title'=>'SMTP-сервер', 'type'=>'text', 'max'=>250, 'min'=>5),
	array('name'=>'email_port', 'title'=>'Порт', 'type'=>'number', 'max'=>65000, 'min'=>1),
	array('name'=>'email_subject', 'title'=>'Тема письма', 'type'=>'text', 'max'=>120),
	array('name'=>'email_text', 'title'=>'Текст письма', 'type'=>'text', 'tag'=>'textarea'),
	array('name'=>'email_sign', 'title'=>'Подпись в письме', 'type'=>'text', 'max'=>250, 'tag'=>'textarea'),
	array('name'=>'email_address_test', 'title'=>'Адрес для тестирования', 'type'=>'email', 'max'=>120, 'min'=>6)

);

$_af_mail = array(
	
	array('name'=>'fam', 'title'=>'ФИО', 'type'=>'text', 'max'=>150, 'min'=>1),
	array('name'=>'name', 'title'=>'Имя', 'type'=>'text', 'max'=>120),
	array('name'=>'name_2', 'title'=>'Отчество', 'type'=>'text', 'max'=>120),
	array('name'=>'tel', 'title'=>'Телефон', 'type'=>'text', 'max'=>120, 'min'=>7),
	array('name'=>'email', 'title'=>'Эл. адрес', 'type'=>'email', 'max'=>120, 'min'=>7)

);

function main()
{
	$id = _rq(_id);
	
	switch($id)
	{
		case '1':
		
			break;
		
	}
	
	return 1;
}

function _init($mode_in = 3)
{
	if($mode_in && 1)
	{
		_add('pm.mail', d_pm_mail);
		_add('pm.params', d_pm_params);
		_add('pm.login', d_pm_login);
		_add('pm.fm', d_pm_fm);
		_add('pm.report', d_pm_report);
		_add('pm.report.create', d_pm_report_create);
		_add('pm.test.mail', d_pm_test_mail);
		_add('form.action', d_index_file);
	}
	
	if($mode_in && 2)
	{
		_add('logo.title', _ini('event'));
		_add('play.period', _ini('period'));
	}
	
	$logo_path = get_path('logo');
	
	if(file_exists($logo_path))
	{
		//$logo = '<img class="logo-sm" src="'.$logo_path.'" alt="<!---MAIN.NAME---> - small" />';
		//_add('MAIN.LOGO', $logo);
		_add('MAIN.LOGO.IMG', $logo_path);
	}
}

function ch($param_in)
{
	global $_arr_changes;
	return array_key_exists($param_in, $_arr_changes) ? $_arr_changes[$param_in] : '';
}

function init_page($step_in)
{
	$res = 0;
	global $_arr_changes;
	
	//ech("t = $step_in");
	
	$path = _ini('dir_video');
	$aps = _dir_tree($path, 6);
	$nav = '';
	$mode = _ini('mode');	
	$ext_mode = $mode == 2 ? 'gif' : 'mp4';
	
	foreach($aps as $cd)
	{
		$ap = _dir_tree($cd, 5);
		$ad = array();
		
		foreach($ap as $cf)
		{
			$ref = _path(d_path_proj_int, _dir(dirname($cf), -1, true), basename($cf));
			$title = trim(_dir(dirname($cf), -1, true), '\\/');
			$dst = _path(realpath(d_path_proj_int), $title, basename($cf));
			
			$ad[] = $dst;
			$ext = _ext($cf);
			
			if(in_array($ext, array('jpg', 'png')))
				$poster = $ref;
			else if($ext == $ext_mode) 
				$file = $ref;
		}
		
		$b = _copy($ap, $ad);
		$res += $b;
		
		if(!$step_in || $b)
		{
			$title = wrap_tag($title, 'div', 'title');
			$nav = wrap_tag('<img src="'.$poster.'" />'.$title, 'div', array('class'=>'video-unit', 'dp'=>$poster, 'df'=>$file)).$nav;
			
			$_arr_changes['video_panel'] = $nav;
			$_arr_changes['video_file'] = $file;
			$_arr_changes['video_poster'] = $poster;
		}
	}
	
	return $res;
}

function user_page()
{
	init_page(0);
}

function send(&$msg_res)
{
	$msg_res = '';
	
	$af = _rq('proj');	
	if(empty($af))
	{
		$msg_res = 'Не указан файл для отправки по эл. почте';
		return 0;
	}
	else if(!file_exists($af))
	{
		$msg_res = 'Файла '.$af.' не существует';
		return 0;
	}
	
	$ae = array('email_address', 'email_pswd', 'email_host', 'email_port', 'email_subject', 'email_text', 'email_sign');
	$av = _ini($ae);
	extract($av);
	
	$to = _rq('email');	
	
	$test = md5($to.'---'.$af);
	if($test == $_SESSION[d_email_sent_sign])
	{
		$msg_res = 'Письмо уже отправлено';
		return 2;
	}
	
	if(!empty($email_sign))
		$email_sign = "<br /><br />---<br />".$email_sign;
	
	$opt = array();	
	$opt['to'] = $to;
	$opt['from'] = $email_address;
	$opt['subject'] = _not_empty($email_subject, d_mail_subject);
	$opt['text'] = _not_empty($email_text.$email_sign, d_mail_text);
	$opt['dst_charset'] = 'UTF-8';
	
	$opt['arr_files'] = $af;
	$opt['username'] = $email_address;
	$opt['password'] = $email_pswd;
	$opt['host'] = $email_host;
	$opt['port'] = $email_port;
	
	$obj = new c_mail($opt);	
	//$result = true; //$obj->send();
	$result = $obj->send();
	
	if($result === true)
	{
		$msg_res = 'Письмо успешно отправлено';
		$_SESSION[d_email_sent_sign] = $test;
	}
	else
		$msg_res = 'Письмо не отправлено. Ошибка: '.$result;
	
	return $result === true ? 1 : 0;
}

function mail_page()
{
	$msg = '';
	$step = _rq(_step);
	
	global $_af_mail;
	$obj = new c_form($_af_mail);
	$ap = $obj->params('name');
	
	if($step <= 1)
	{
		$step = 2;
	}
	else if($step == 2)
	{
		$ap = _rq($ap);
		$obj->set($ap);
		
		if(($msg = $obj->test()) === true)
		{
			$res = send($msg);
			
			$ap[] = _rq('proj');
			$ap[] = $res ? 'ok' : 'error';
			
			if(!save_user_data($ap))
				$msg = _implode_not_empty('Не удалось сохранить данные. Обратитесь к администратору', $msg);
			
			/*
			if(!$r)
				$msg = 'Не удалось сохранить данные. Обратитесь к администратору';
			else if($r == 2)
				$msg = 'Письмо уже отправлено';
			else
				$msg = send();
			*/
		}
		
		foreach($ap as $k=>$v)
			_add('pm.'.$k.'.val', $v);
	}
	
	_add('proj.val', $_REQUEST['proj']);
	_add('pm.step.val', $step);
	
	_msg($msg);
	
	return '';
}

function _is_aa($app_in)
{
	if(!is_array($app_in))
		return false;
	
	reset($app_in);
	current($app_in);
	
	return !is_int(key($app_in));
}

function _ini($name_in, $val_in = null)
{
	$res = null;
	static $arr_params = array();
	$path = d_path_params;
	
	if(!count($arr_params) && file_exists($path))	
	{
		$buf = file_get_contents($path);
		$arr_params = s2a($buf, "=", "\n", " \t", "\r");
	}
	
	if($val_in !== null || _is_aa($name_in))
	{
		if(is_array($name_in))
		{
			foreach($name_in as $k=>$v)
			{
				$v = str_replace(array("\r", "\t"), '', $v);
				$v = str_replace("\n", '<br />', $v);
				$arr_params[$k] = $v;
			}
		}
		else
			$arr_params[$name_in] = $val_in;
		
		$buf = a2s($arr_params, "=", "\n");
		$res = file_put_contents($path, $buf);
	}
	else
	{
		if(is_array($name_in))
		{
			$res = array();
			foreach($name_in as $k)
				$res[$k] = $arr_params[$k];
		}
		else
			$res = $arr_params[$name_in];
	}
	
	return $res;
}

function _msg($str_in)
{
	_add('msg', empty($str_in)  ? '' : wrap_tag(ltrim($str_in, '!'), 'div', strpos($str_in, '!') === 0 ? 'err' : 'ok'));
}

function params_page()
{
	$msg = '';
	
	if($_SESSION[_g_user_rights] != d_user_admin_rights)
	{
		if(!login_test())
		{
			$ap[_id] = d_pm_login;
			$ref = '?'.http_build_query($ap);		
			_redir($ref);
			exit();
		}
	}
	
	global $_af_params;
	$step = _rq(_step);
	$set = false;
	
	$obj = new c_form($_af_params);	
	$ap = $obj->params('name');	
	
	if($step <= 1)
	{
		$step = 2;		
		$ap = _ini($ap);
		$obj->set($ap);
		$set = true;
	}
	else if($step == 2)
	{
		$ap = _rq($ap);
		$obj->set($ap);
		
		if(($msg = $obj->test()) === true)
		{
			_ini($ap);
			$msg = 'Настройки сохранены';
			
			prep_logo();
			
			_init(2);
		}
		
		$set = true;
	}
	
	if($set)
		foreach($ap as $k=>$v)
		{
			$at = $obj->get($k);			
			
			if($at['tag'] == 'textarea')
				$v = str_replace('<br />', "\n", $v);
			
			_add('pm.'.$k.'.val', $v);
		}

	_add('unit.mode', $obj->unit('mode'));
	_add('pm.step.val', $step);	

	_msg($msg);
	
	return '';
}

function save_user_data($arr_in)
{
	$test = md5(implode('---', $arr_in));
	if($test == $_SESSION[data_save_sign])
		return 2;
	
	$arr = $arr_in;
	array_unshift($arr, date('Y-m-d H:i:s'));
	$row = implode("\t", $arr)."\r\n";
	
	$res = file_put_contents(d_path_send_data, $row, FILE_APPEND);
	if($res)
		$_SESSION[data_save_sign] = $test;
	
	return $res ? 1 : 0;
}

function login_test()
{
	$p1 = _rq('p1');
	$p2 = _rq('p2');
	
	if($p1 == 'admin' && $p2 == _ini('pswd'))
	{
		$_SESSION[_g_user_rights] = d_user_admin_rights;
		return true;
	}
	
	return false;
}

function login_page()
{
	$msg = '';
	$step = _rq(_step);
	
	if($step == 2)
	{
		$_SESSION[_g_user_rights] = '';		
		$ref = './';		
		_redir($ref);
		exit();
	}
	
	if($_SESSION[_g_user_rights] == d_user_admin_rights || login_test())
	{
		$ap[_id] = d_pm_params;
		$ref = '?'.http_build_query($ap);		
		_redir($ref);
		exit();
	}
	
	$p1 = _rq('p1');
	$p2 = _rq('p2');
	
	_add('pm.p1.val', $p1);	
	_add('pm.p2.val', $p2);	

	if(!empty($p1))
		$msg = '!Неверное имя и/или пароль';

	_msg($msg);

	return '';
}

function fm()
{
	return _fm();
}

function get_path($key_in)
{
	$res = '';
	
	if($key_in == 'logo')
	{
		$path = _ini('logo');
		if(file_exists($path))
		{
			$name = basename($path);
			$res = _path(d_path_data, $name);
		}
	}
	
	return $res;
}

function prep_logo()
{
	$src = _ini('logo');
	$dst = '';
	
	if(file_exists($src))
	{
		$name = basename($src);
		$dst = _path(d_path_data, $name);
		
		if(!file_exists($dst) || $name !== basename($src))
			copy($src, $dst);
	}	
	
	return !empty($dst) && file_exists($dst);
}

function load_stat()
{
	global $_arr_stat;
	$path = d_path_send_data;
	
	$af = file($path);
	
	foreach($af as $row)
	{
		$ar = explode("\t", rtrim($row, "\r\n"));
		$_arr_stat[] = $ar;
	}
}

function stat_dates($name_in)
{
	$res = '';
	global $_arr_stat;
	$ar = array(0=>'---');
	
	if(!count($_arr_stat))
		load_stat();

	foreach($_arr_stat as $ac)
	{
		list($date, $time) = explode(' ', $ac[0]);
		$ar[$date] = $date;
	}
		
	$ar = array_unique($ar);
	foreach($ar as $k=>$v)
		$res .= wrap_tag($v, 'option', array('value'=>$k));
	
	$res = wrap_tag($res, 'select', array('name'=>$name_in));

	return $res;
}

function load_proj()
{
	$ar = array();
	global $_arr_proj;
	$path = d_path_proj_int;
	
	$af = _dir_tree(d_path_proj_int, 6);

	foreach($af as $cur)
	{
		$name = rtrim(_dir(rtrim($cur, '\\/'), -1, true), '\\/');
		//list($pd, $time) = explode('_', $name);
		//$ar[] = $pd;
		$ar[] = $name;
	}

	$_arr_proj = array_unique($ar);
}

function proj_dates($name_in)
{
	$res = '';
	global $_arr_proj;
	$ar = array(0=>'---');
	
	if(!count($_arr_proj))
		load_proj();
	
	foreach($_arr_proj as $v)
	{
		list($date, $time) = explode('_', $v);
		$ar[$date] = $date;
	}

	foreach($ar as $k=>$v)
		$res .= wrap_tag($v, 'option', array('value'=>$k));
	
	$res = wrap_tag($res, 'select', array('name'=>$name_in));

	return $res;
}

function is_sent_proj($id_in)
{
	$res = 0;
	global $_arr_stat;

	if(!count($_arr_stat))
		load_stat();
	
	foreach($_arr_stat as $ac)
	{
		if(strpos($ac[6], $id_in) !== false && $ac[7] == 'ok')
			$res ++;
	}
	
	return $res;
}

function report_mail_load($mode_in = 0)
{
	$res = '';	
	global $_arr_stat;	
	$date = _rq('date');
	
	if(!count($_arr_stat))
		load_stat();
	
	$num = 1;
	$ah = array('№№', 'ФИО', 'Телефон', 'email', 'Проект');
	
	foreach($_arr_stat as $ac)
	{
		list($dc, $time) = explode(' ', $ac[0]);
		
		if($dc == $date)
		{
			$at = array();
			$at[] = $num ++;
			$at[] = _implode_not_empty($ac[1], $ac[2], $ac[3]);
			$at[] = $ac[4]; 
			$at[] = $ac[5]; 
			$at[] = basename(_dir($ac[6], -1));
			
			$row = implode('', wrap_tag($at, 'td'));
			$row = wrap_tag($row, 'tr');
			
			$res .= $row;
		}
	}
	
	if(!empty($res))
	{
		$thead = implode('', wrap_tag($ah, 'td'));
		$thead = wrap_tag($thead, 'tr');
		$thead = wrap_tag($thead, 'thead');		
		$res = wrap_tag($thead.$res, 'table');
	}
	else if(empty($date))
		$res = 'Нужно указать дату отчета';
	else
		$res = 'Нет данных на дату '.$date;
	
	if($mode_in)
	{
		$head = wrap_tag('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />', 'head');
		$body = wrap_tag($res, 'body');
		$res = wrap_tag($head.$body, 'html');
	}
	
	return $res;
}

function report_mail($mode_in)
{
	$root_ref = _root_ref();
	$ref = _ref(array(_id=>d_pm_report_mail, 'date'=>_rq('date')));
	
	$nav .= '<button onclick="window.open(\''.$ref.'\'); return false;">Сохранить</button>';
	$nav .= '<button onclick="dlg_close(); return false;">Ok</button>';

	$at['code'] = report_mail_load();
	$at['nav'] = $nav;

	return json_encode($at);
}

function report_proj_load($mode_in = 0)
{
	$res = '';
	$date = _rq('date');
	global $_arr_proj;
	
	if(empty($date))
		$res = 'Нужно указать дату отчета';
	else if(empty($date))
		$res = 'Нет данных на дату '.$date;
	else
	{
		if(!count($_arr_proj))
			load_proj();
		
		$cnt = 0;
		$cnt_sent = 0;
		
		foreach($_arr_proj as $cur)
		{
			list($pd, $time) = explode('_', $cur);
			
			if($pd == $date)
			{
				$cnt ++;				
				$cnt_sent += is_sent_proj($cur);
			}
		}
		
		//$res = 'На дату '.$date.'<br />';
		$res .= 'Создано проектов: '.$cnt.'<br />';
		$res .= 'Отправлено проектов: '.$cnt_sent;
	}
	
	if($mode_in)
	{
		$head = wrap_tag('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />', 'head');
		$body = wrap_tag($res, 'body');
		$res = wrap_tag($head.$body, 'html');
	}
	
	return $res;
}

function report_proj()
{
	$root_ref = _root_ref();
	$ref = _ref(array(_id=>d_pm_report_proj, 'date'=>_rq('date')));
	
	$nav .= '<button onclick="window.open(\''.$ref.'\'); return false;">Сохранить</button>';
	$nav .= '<button onclick="dlg_close(); return false;">Ok</button>';

	$at = array();
	$at['code'] = report_proj_load();
	$at['nav'] = $nav;

	return json_encode($at);
}

function test_email()
{
	$res = '';
	
	$opt = array();		
	$ae = array('email_address', 'email_pswd', 'email_host', 'email_port', 'email_subject', 'email_text', 'email_address_test');
	$av = _ini($ae);
	extract($av);
	
	if(empty($email_address_test))
		return 'Нужно указать эл. адрес для тестирования почты';
	
	$opt['to'] = $email_address_test;
	$opt['from'] = $email_address;
	$opt['subject'] = _not_empty($email_subject, d_mail_subject_test);
	$opt['text'] = _not_empty($email_text, d_mail_text_test);
	$opt['dst_charset'] = 'UTF-8';
	
	$opt['username'] = $email_address;
	$opt['password'] = $email_pswd;
	$opt['host'] = $email_host;
	$opt['port'] = $email_port;
	
	$obj = new c_mail($opt);	
	$result = $obj->send();
	
	if($result === true)
		$res = 'Письмо успешно отправлено';
	else
		$res = 'Письмо не отправлено. Ошибка: '.$result;
	
	return $res;
}

function part_projects()
{
	$res = '';
	$step = _rq(_part_step);
	
	global $_arr_changes;	
	$cnt = init_page($step);
	
	$code = $_arr_changes['video_panel'];	
	$res = (!$step || $cnt) ? $code.wrap_tag('prep_units();', 'script') : '';
	
	return $res;
}

function mail_page_res()
{
	$msg = '';
	$step = _rq(_step);
	$nav = '';
	
	global $_af_mail;
	$obj = new c_form($_af_mail);
	$ap = $obj->params('name');
	
	$ap = _rq($ap);
	$obj->set($ap);
	
	if(($msg = $obj->test()) === true)
	{
		$res = send($msg);
		
		$ap[] = _rq('proj');
		$ap[] = $res ? 'ok' : 'error';
		
		if(!save_user_data($ap))
			$msg = _implode_not_empty('Не удалось сохранить данные. Обратитесь к администратору', $msg);
		else
		{
			$root_ref = _root_ref();
			$nav = '<button onclick="window.open(\''.$root_ref.'\', \'_self\'); return false;">Ok</button>';
		}
	}

	$at['code'] = $msg;
	$at['nav'] = $nav;

	return json_encode($at);
}

?>