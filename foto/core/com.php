<?

$_ach = array();

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Сгенерировать уникальный идентификатор
*
*	@param	number $len_in - длина идентикикатора
*
*	@return string - уникальный идентификатор
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _uniq_id($len_in = 10)
{
	$res = '';
	$len = $len_in;
	
	while(strlen($res) < $len)
		$res .= rand(1000, 9000);

	return substr($res, 0, $len);
}

function ech($str_in)
{
	echo($str_in.'<br />'."\n");
}

function a2s($arr_in, $su_in, $sp_in, $wu_in = '', $wp_in = '')
{
	$at = array();

	foreach($arr_in as $k=>$v)
		$at[] = wrap($k, $wu_in, $wu_in).($v === null ? '' : $su_in.wrap($v, $wp_in, $wp_in));
		
	return implode($sp_in, $at);
}	

function s2a($str_in, $su_in, $sp_in, $wu_in = '', $wp_in = '')
{
	$ar = array();
	
	if(!empty($str_in))
	{
		$at = !empty($sp_in) ? explode($sp_in, $str_in) : array($str_in);
		foreach($at as $row)
		{
			if(!strlen(trim($row, $wu_in)))
				continue;

			list($k, $v) = strpos($row, $su_in) ? _explode($su_in, $row, 2, 2) : array($row, null);				
			$ar[trim($k, $wu_in)] = $v === null ? $v : trim($v, $wu_in);
		}
	}

	return $ar;
}

function wrap($obj_in, $start_in, $end_in)
{
	if(!is_array($obj_in))
		$res = $start_in.$obj_in.$end_in;
	else
	{
		$res = array();
		foreach($obj_in as $cur)
			$res[] = wrap($cur, $start_in, $end_in);
	}

	return $res;
}

function wrap_tag($obj_in, $tag_in, $prop_in = null)
{
	if(!is_array($obj_in))		
	{
		$prop = is_array($prop_in) ? $prop_in : ($prop_in === null ? array() : array('class'=>$prop_in));

		$ap = (is_array($prop) && array_key_exists(0, $prop)) ? $prop : array($prop);
		$at = is_array($tag_in) ? $tag_in : array($tag_in);
		$ats = $ate = array();
		
		foreach($at as $k=>$t)
		{
			$apc = _av($ap, $k);
			$apc = is_array($apc) ? $apc : ($apc === null ? array() : array('class'=>$apc));			
			//$sp = is_array($apc) ? ' '.a2s($apc, '=', ' ', '', '\'') : '';
			$sp = (is_array($apc) && count($apc)) ? ' '.a2s($apc, '=', ' ', '', '"') : '';

			$ats[] = $t.$sp;
			array_unshift($ate, $t);
		}

		$s = implode('', wrap($ats, '<', '>'));
		$e = implode('', wrap($ate, '</', '>'));

		$res = $s.$obj_in.$e;
	}
	else
	{
		$res = array();
		foreach($obj_in as $cur)
			$res[] = wrap_tag($cur, $tag_in, $prop_in);
	}

	return $res;
}

function _btn($params_in)
{
	$res = '';	
	$params = is_array(_av($params_in, 0)) ? $params_in : array($params_in);
	
	foreach($params as $unit)
	{
		$title = $unit['title'];
		$fp = $unit['params'];
		unset($unit['title'], $unit['params']);
		$unit['onclick'] = 'form_click({'.a2s($fp, ':', ',', '\'', '\'').'}, event);';
		
		$res .= wrap_tag($title, 'button', $unit);
	}
	
	return $res;
}

function _dlg($body_in, $menu_in, $title_in = '')
{
	$title = empty($title_in) ? 'Сообщение' : $title_in;
	$server = $_SERVER['SERVER_NAME'];	
	if((!defined('g_chpu') || !g_chpu) && ($server == 'localhost'))
	{
		$path = $_SERVER['PHP_SELF'];
		$action = substr($path, strrpos($path, '/') + 1);
	}
	else
		$action = '';

	$res = '<form action="'.$action.'" method="post" enctype="application/x-www-form-urlencoded">';
	$res .= '<div class="dlg-frame"><div class="dlg"><div class="title">'.$title.'</div><div class="body">'.$body_in.'</div><div class="bottom">'.$menu_in.'</div></div></div>';
	$res .= '</form>';
	
	return $res;
}

function _implode_not_empty()
{
	$a = func_get_args();		
	return trim(implode(' ', $a));
}

function _implode_ne_split()
{
	return _implode_not_empty_split(func_get_args());
}

function _array_one($a_in)
{
	$ar = array();
	$a = _array($a_in);

	foreach($a as $k=>$v)
		$ar = array_merge($ar, is_array($v) ? _array_one($v) : array($k=>$v));

	return $ar;
}

function _implode_not_empty_split()
{
	$a = _array_one(func_get_args());
	
	$split = array_shift($a);
	$b = array();
	
	foreach($a as $c)
		if(!empty($c))
			$b[] = is_array($c) ? trim(implode($split, $c)) : $c;

	return trim(implode($split, $b));
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Подключить необходимые файлы
*
*	@param	array $files_in	- массив файлов для подключения
*	@param	string $dir_in	- корневая директория
*
*	@return	bool - true, если все файлы удалось подключить
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _include_files($files_in, $dir_in)
{
	$res = true;
	$arr_files = is_array($files_in) ? $files_in : array($files_in);

	foreach($arr_files as $cur)
	{
		$path = _path($dir_in, $cur);
		$res &= include_once($path);
	}
	
	return $res;
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить директорию по ключу
*
*	@param	string $dir_in 		- директория, относительно которой 
*							 	  будет осуществляться поиск 
*	@param	string $key_dir_in	- ключ к директории:
*									отрицательное число	- указывает смещение по дереву каталогов	 
*								  	имя директории 		- по которой будет обрезан путь
*	@param	bool $param_in		- выполняет разные функии при строковом и числовом значении $key_dir_in:
*									при строковом значении - исключить имя директории $key_dir_in из пути
*									при числовом значении - получить правую часть пути
*
*	@return	string - путь директории
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _dir($path_in, $key_dir_in, $param_in = false)
{
	$res = false;
	$key = $key_dir_in;
	
	$arr = explode('/', rtrim(str_replace('\\', '/', $path_in), '/'));
	
	if(is_numeric($key))
	{
		$an = array();
		while($key ++ < 0)
			$an[] = array_pop($arr);
		if($param_in)
			$arr = array_reverse($an);
	}
	else
	{
		while(count($arr) && strcmp(array_pop($arr), $key));
		
		if(!$param_in)
			$arr[] = $key;
	}
	
	$res = implode('/', $arr).(strpos('.', end($arr) || '') === false ? '/' : '');
	
	return $res;
}

function _get_dir($path_in, $key_dir_in, $param_in = false)
{
	return _dir($path_in, $key_dir_in, $param_in);
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить путь из частей
*
*	@param	array/string $param_in - массив частей пути или путь
*
*	@return string - итоговый путь
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _path()
{
	$ar = func_get_args();
	
	if(count($ar) == 1)
	{
		$arr = is_array($ar[0]) ? $ar[0] : array($ar[0]);
		$path = str_replace('\\', '/', implode('/', $arr));		
		
		if(strpos($path, ':') !== false)
			$path = ltrim($path, '/');		
		
		return preg_replace("/([\/]{2,})/i", '/', $path);
	}
	
	return _path($ar);
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить значение опции из массива
*
*	@param	string $param_in - имя опции
*	@param	array $arr_opt_in - массив опций
*	@param	string $def_val_in - значение по умолчанию
*
*	@return	string/number - значение из массива или значенине по умолчанию
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _get_opt_val($param_in, $arr_opt_in, $def_val_in = null)
{
	return (is_array($arr_opt_in) && array_key_exists($param_in, $arr_opt_in)) ? $arr_opt_in[$param_in] : $def_val_in;
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Очистить директорию от файлов и поддиректорий
*
*	@param	string $path_in - входной путь
*	@param	number $period_in - период, в течение которго директория 
*								будет сохранятся
*	@param	number $del_cur_in - удалить текущую директорию
*
*	@return bool - true, если удалилось все
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _clear($dir_in, $period_in = 60, $del_cur_in = false)
{
	$res = true;
	$dir = rtrim($dir_in, '\\/ ');
	
	if(empty($dir))
		return false;
	
	$arr_ext_exc = array('php', 'css', 'js');		
	$dir .= '/';

	if($h = opendir($dir)) 
	{
        while(($f = readdir($h)) !== false) 
		{
            if($f == '.' || $f == '..')
				continue;
			
			$f = $dir.$f;
			$b_new = $period_in > 0 && ((time() - filemtime($f)) < $period_in);
			
			if(is_file($f))
			{
				$pi = pathinfo($f);
				$res &= in_array($pi['extension'], $arr_ext_exc) ? false : ($b_new ? false : unlink($f));
			}
			else if(is_dir($f))
				$res &= ($b_new ? false : (_clear($f) && rmdir($f)));
        }

        closedir($h);
		
		if($res && $del_cur_in)
			$res &= rmdir($dir);
    }

	return $res;
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить отформатированое содержимое документа
*
*	@param	string $path_in - путь к документу
*	@param	string $id_in - идентификатор документа
*	@param	string $dir_tmp_in - путь к временной директории для распаковки
*	@param	number $offset_in - смещение внутри файла
*	@param	number $maxlen_in - максимальная длина прочитанных данных
*
*	@return	string - код документа
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _get_content($path_in, $id_in = '', $dir_tmp_in = '', $offset_in = 0, $maxlen_in = 0)
{
	$path = $path_in;
	$pi = pathinfo($path);
	$ext = $pi['extension'];
	$src = $pi['dirname'];
	$name = empty($id_in) ? basename($pi['basename'], '.'.$ext) : $id_in;
	$b_zip = false;

	$dir_tmp = empty($dir_tmp_in) ? sys_get_temp_dir() : $dir_tmp_in;
	$tmp = _path(array($dir_tmp, $name));

	if(!strcasecmp($ext, 'zip'))
	{
		if(!file_exists($tmp))
		{
			//$dst = _uniq_dir($this->m_dir_tmp);
			$dst = $tmp;
			$zip = new ZipArchive;

			if($zip->open($path) === true) 
			{
				$zip->extractTo($dst);
				$zip->close();
			}

			$src = $dst;
		}
		else
			$src = $tmp;

		$at = glob(_path(array($src, $name)).'.*');
		$path = $at ? $at[0] : null;
		
		$b_zip = true;
	}

	if($path && file_exists($path))
	{
		if($maxlen_in > 0)
			$code = file_get_contents($path, false, null, $offset_in, $maxlen_in);
		else
			$code = file_get_contents($path);
	}
		
	if($b_zip)
	{
		$period = $maxlen_in ? 7200 : 0;
		@_clear($tmp, $period, 1);
	}
	
	return $code;
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Вывод обекта на экран или файл
*
*	@param	array/string $obj_in - строка или массив
*	@param	string $path_in - путь к файлу
*	@param	number $size_in - максимальный размер файла
*
*	@return ничего
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _log($obj_in, $path_in = 0, $size_in = 100000)
{
	$file_path = $path_in;
	$file_size = max(1000, $size_in);
	
	$buf = is_array($obj_in) ? implode(', ', $obj_in) : $obj_in;
	$str = implode("\t", array(date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR'], $buf))."\r\n";
	$flags = FILE_APPEND;

	$full = file_exists($file_path) ? filesize($file_path) : 0;
	
	if($file_size > 0 && file_exists($file_path) && ($full = filesize($file_path)) > $file_size)
	{
		$at = file($file_path);
		$dif = $full - $file_size - strlen($str);
		$i = 0;
		$c = 0;
		
		while($c < $dif && count($at))
			$c += strlen(array_shift($at));

		$at[] = $str;
		$str = implode('', $at);
		
		$flags = 0;
	}		

	if(is_writable($file_path)) 
		file_put_contents($file_path, $str, $flags);
}

function _get_call_point()
{
	$res = "";
	
	if(!function_exists("debug_backtrace"))
		return $res;
	
	$arr_data = debug_backtrace();
	$c = count($arr_data);
	
	for($i = $c - 1; $i >= 0; $i --)
	{
		$fun	= $arr_data[$i]["function"];
		$file	= $arr_data[$i]["file"];
		
		if($fun == "_debug")
		{
			$file = basename($arr_data[$i]["file"]);
			$line = $arr_data[$i]["line"];				
			
			$res =  "$file ($line)";
			
			break;
		}
	}
	
	return $res;
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Вывод обекта на экран или файл
*
*	@param	array/string $obj_in - строка или массив
*	@param	number/string $param_1_in - мода или путь к файлу
*							0 - режим заданный _g_debug_mode
*							1 - отображение на странице
*							2 - вывод в файл
*	@param	number $param_2_in - максимальный размер файла
*
*	@return ничего
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _debug($obj_in, $param_1_in = 0, $param_2_in = 100000)
{
	$p_1 = $param_1_in;
	$p_2 = $param_2_in;
	
	$mode = $p_1 == 0 ? _g_debug_mode : (is_numeric($p_1) ? $p_1 : 2);
	
	$from = _get_call_point();
	$str = (empty($from) ? '' : $from.': ').$obj_in;
	
	if($mode & 2)
	{
		$path = is_numeric($p_1) ? _g_debug_file : $p_1;
		$size = max(1000, $p_2);
		
		_log($str, $path, $size);
	}
	
	if($mode & 1)	
		ech($str);
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Сгенерировать ссылку
*
*	@param	array/string $params_in - список параметров
*
*	@return string - ссылка
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _ref($params_in)
{
	$ap = is_array($params_in) ? $params_in : _parse_str($params_in);
	return intval(_av($ap, _act)) > 0 ? '?'.http_build_query($ap) : wrap(http_build_query($ap), '<!---REF(', ')--->');
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить значение парамета из строки
*
*	@param	string $name_in			- имя параметра
*	@param	string/array $cont_in	- контейтер с различными параметрами
*
*	@return string - значение параметра
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _param($name_in, $cont_in)
{
	$ap = is_array($cont_in) ? $cont_in : _parse_str($cont_in);	
	return _av($ap, $name_in);
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить параметр запроса
*
*	@param	string $name_in			- имя параметра
*
*	@return string - значение параметра
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _rq($name_in)
{
	if(is_array($name_in))
	{
		$res = array();
		foreach($name_in as $cur)
			$res[$cur] = _rq($cur);
	}
	else 
		$res = isset($_REQUEST[$name_in]) ? $_REQUEST[$name_in] : null;
	
	return $res;
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Преодразовать строку запроса в ассоциированный массив
*
*	@param	string $str_in - входная строка
*
*	@return string - значение параметра
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _parse_str($str_in)
{
	parse_str($str_in, $ap);
	return $ap;
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Сделать замены
*
*	@param	string $code_res	- исходный код
*	@param	array $arr_in		- массив замен
*
*	@return	number - количество сделанных замен
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _changes(&$code_res, $arr_in)
{
	$r = 0;
	
	if(!is_array($arr_in))
		return $r;
	
	$cnt = 0;
	$arr = $arr_in;
	$exit = false;
	
	while($cnt < 5)
	{
		$p = 0;
		foreach($arr as $k=>$v)
		{
			$k1 = '<!---'.$k.'--->';
			$k2 = '<!--'.$k.'-->';
			$k3 = '%'.$k.'%';			
			$ak = array($k1, strtoupper($k1), $k2, strtoupper($k2), $k3, strtoupper($k3));

			$code_res = str_replace($ak, $v, $code_res, $c);
			$p += $c;
			
			// для ускорения
			if(strpos($code_res, '<!--') === false && strpos($code_res, '%') === false)
			{
				$exit = true;
				break;
			}
		}

		$r += $p;
		$cnt ++;

		if(!$p || $exit)
			break;
	}
	
	return $r;
}

function _array($val_in)
{
	return is_array($val_in) ? $val_in : array($val_in);
}

function _array_values_by_keys($arr_in, $keys_in)
{
	$ar = array();
	$ak = _array($keys_in);
	
	foreach($ak as $k)
		if(array_key_exists($k, $arr_in))
			$ar[] = $arr_in[$k];
	
	return $ar;
}

function _not_empty()
{
	$a = _array_one(func_get_args());
	
	foreach($a as $c)
	{
		$c = is_array($c) ? _not_empty($c) : $c;		
		if(strlen($c))
			return $c;
	}
	
	return null;
}

function _html_tree_($arr_in, $tag_frame_in = 'ul', $tag_unit_in = 'li', $select_id = null)
{
	$res = '';
	list($start, $end) = _explode('!', wrap_tag('!', $tag_frame_in, 'ext-frame'), 2, 2);
	$vp = '';
	
	//_line2tree($arr_in);
	//return _html_tree_2($arr_in);

	foreach($arr_in as $k=>$v)
	{
		$cc = substr_count($k, '.');

		$res .= empty($vp) ? '' : wrap_tag($vp, $tag_unit_in, $cc > $cp ? _implode_not_empty('ext-title', $sp ? 'ext-sel' : '') : null);
		$res .= implode('', array_pad(array(), abs($cp - $cc), $cc > $cp ? $start : $end));			
		
		$vp = $v;
		$sp = $k == $select_id;
		
		$cp = $cc;
	}

	if(!empty($res))
		$res = $start.$res.wrap_tag($vp, $tag_unit_in).$end;

	return $res;
}

function _arr_tree($arr_in)
{
	$ar = array();
	
	foreach($arr_in as $k=>$v)
	{
		$ak = explode('.', $k);
		
		$ac = &$ar;
		for($i = 0, $cnt = count($ak); $i < $cnt; $i ++)
		{
			$cur = $ak[$i];
			if(!array_key_exists($cur, $ac))
			{
				if($i == ($cnt - 1))
				{
					$ac[$cur] = $v;
					continue;
				}
				else
					$ac[$cur] = array();
			}
			else if(!is_array($ac[$cur]))
				$ac[$cur] = array($ac[$cur]);

			$ac = &$ac[$cur];
		}
	}
	
	//print_r($ar);
	
	return $ar;
}

function _html_tree($arr_in, $tag_frame_in = 'ul', $tag_unit_in = 'li', $select_id_in = null, $step_in = 0)
{
	$res = '';
	$sel = 0;
	$as = is_array($select_id_in) ? $select_id_in : explode('.', $select_id_in);
	$first = array_shift($as);

	$step = $step_in ++;
	$arr = !$step ? _arr_tree($arr_in) : $arr_in;
	$cnt = 0;

	foreach($arr as $k=>$v)
	{
		$select = null;
		if($k == $first)
			$select = $as;
		
		$bt = false;
		
		$unit = is_array($v) ? wrap_tag(array_shift($v), 'span')._html_tree($v, $tag_frame_in, $tag_unit_in, $select, $step) : $v;
		
		$sel = $first && $k == $first && !count($select);		
		$class = _implode_not_empty((is_array($v) ? 'ext-title' : ''), $sel ? 'ext-sel' : '');
		$res .= wrap_tag($unit, $tag_unit_in, empty($class) ? null : $class);
		$cnt ++;
	}

	if(!empty($res))
		$res = wrap_tag($res, $tag_frame_in, 'ext-frame');
	
	//print_r($res);

	return $res;
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить путь к файлу
*
*	@param	string $file_in -  имя файла или оьносительный путь к файлу
*
*	@return string - путь к найденному файлу
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _file_path($file_in)
{
	static $arr_dir;
	
	$arr_dir = array_merge(array(g_path_nodes, g_path_app), is_array(g_dir_files) ? g_dir_files : explode(';', g_dir_files));
	
	foreach($arr_dir as $cur)
	{
		$path = _path($cur, $file_in);
		if(file_exists($path))
			return $path;
	}
	
	return null;
}

//////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить имя домена для переадресации
*
*	@param	нет
*
*	@return	string - имя домена
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _domain()
{
	$res = $_SERVER['SERVER_NAME'];
	
	if($res == 'localhost')
		$res = _path($res, _get_dir($_SERVER['SCRIPT_NAME'], g_dir_docs));
	else
		$res = implode('.', array_slice(explode('.', $res), -2));
	
	$res = trim($res, '\\/');

	return $res;
}

//////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Отправить электронное письмо
*
*	@param	array $params_in -  ассив свойств
*
*	@return	bool - true, если письмо отправлено
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _mail($params_in)
{
	include_once('mail.php');	
	$obj = new c_mail;
	return $obj->send($params_in);
}

//////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Протестировать адрес электронной почты
*
*	@param	string $email_in - строка адреса
*
*	@return	bool - true, если адрес правильный
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _test_email($email_in)
{
	return preg_match("/[a-z0-9_\.-]+@[a-z0-9\.-]{2,}\.[a-z]{2,10}/i", $email_in);
}

//////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить значение массива по ключу
*
*	@param	array $arr_in			- массив
*	@param	string $key_in			- ключ
*	@param	any type $def_val_in	- значение по умолчанию
*
*	@return	any type - значение массива или null
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _av($arr_in, $key_in, $def_val_in = null)
{
	return isset($arr_in[$key_in]) ? $arr_in[$key_in] : $def_val_in;
}

//////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Разделить строку на составные части
*
*	@param	string $sep_in			- разделитель
*	@param	string $str_in			- строка
*	@param	number $limit_in 		- максимальное количество частей
*	@param	number $limit_min_in	- минимальное количество частей
*	@param	string $pad_val_in		- значеия, которыми будут заполняться новые элементы маасива
*
*	@return	array - массив строк
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _explode($sep_in, $str_in, $limit_in = PHP_INT_MAX, $limit_min_in = 0, $pad_val_in = null)
{
	$arr = explode($sep_in, $str_in, $limit_in);
	
	if($limit_min_in)
		$arr = array_pad($arr, $limit_min_in, $pad_val_in);
	
	return $arr;
}

//////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Найти путь к файлу
*
*	@param	string $path_tmpl_in	- шаблон пути
*
*	@return	string/bool - путь к найденному файлу или false
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _file_find($path_tmpl_in)
{
	if(empty($path_tmpl_in))
		return false;
	
	if(file_exists($path_tmpl_in))
		return $path_tmpl_in;
	
	$af = glob($path_tmpl_in);
	foreach($af as $cur)
		if(file_exists($cur))
			return $cur;
	
	return false;
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить все ссылки ко страницы
*
*	@param	string $code_in - код страницы
*
*	@return	array - массив ссылок
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _page_refs(&$code_in)
{
	$arr_res = array();
	
	$apt = array(	
		'id' => '/<!--[-]*REF\.(.*)[-]*-->/iusU',
		'cid' => '/<!---PID\.([^-)]*)--->/iusU',
		'ps' => '/<!--[-]*REF\(([^\)]*)\)[-]*-->/iusU'		
	);
	
	foreach($apt as $k=>$pattern)
	{
		if(preg_match_all($pattern, $code_in, $arr_tmp))
			for($i = 0, $c = count($arr_tmp[0]); $i < $c; $i ++)
				$arr_res[] = array($arr_tmp[0][$i], trim($arr_tmp[1][$i]));
	}

	return $arr_res;
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить метку поиска для страницы
*
*	@param	string $code_in - код страницы
*
*	@return	array - массив ссылок
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _srch_mark($str_in, &$code_in)
{
	$res = '';
	
	if(!empty($str_in))
	{
		$arr_refs = array();

		$at = _page_refs($code_in);
		foreach($at as $ac)
			if(count($ac) >= 2)
				$arr_refs[] = strpos($ac[1], _id) !== 0 ? _id.'='.$ac[1] : $ac[1];
		
		$res = '<!---SRCH('.$str_in.(count($arr_refs) ? ';'.implode(';', $arr_refs) : '').')--->';
		//$res = '<!---SRCH('.$str_in.')--->';
	}
	
	return $res;
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Добавить элемент в массив глобальных замен
*
*	@param	string $key_in - ключ
*	@param	string $val_in - значение
*
*	@return ничего
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _add($key_in, $val_in)
{
	global $_ach;	
	$_ach[$key_in] = $val_in;
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить значение из массива замен
*
*	@param	string $key_in - ключ
*
*	@return значение или null
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _ach($key_in)
{
	global $_ach;	
	return $_ach[$key_in];
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Перенаправление запроса
*
*	@param	string $url_in - url новой траницы
*
*	@return ничего
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _redir($url_in)
{
	if(!empty($url_in))
	{
		header('Location: '.$url_in);
		exit();
	}
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить ссылку в корень сайта
*
*	@param	нет
*
*	@return string - строка ссылка
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _root_ref()
{
	$res = '.';
	$chpu = defined('g_chpu') && g_chpu === true;
	$index = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? basename($_SERVER['PHP_SELF']) : '';
	return $chpu ? '/' : './'.$index; 
}

?>