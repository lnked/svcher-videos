<?

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Корректировать url-картинок в html-файле 
*
*	Картинки копируются в новую папку $dir_dst_in
*
*	Функция позволяет исключать правку кода внутри тега textarea
*
*	Если исходной картинки не существует, 
*	то путь к ней в исходной html-странице не меняется
*
*	@param	string $str_code 		- код станицы
*	@param	string $dir_src_in 		- путь к исходному файлу с картинками
*	@param	string $dir_src_in 		- путь директории назначения, куда будут копироваться картинки
*	@param	string $dir_web_in 		- относительный путь к файлу картинок в url
*	@param	number file_name_mode_in	- принимает значения:
*										1 - имя файла картинки будет сформировано из имени директории
*											исходного файла с картинками и имени самой картинки. 
*											Пример: a101-img001.jpg
*										2 - имя файла картинки будет полностью сохранено. 
*											Он будет помещен в директорию как в сиходной папке.
*											Пример: tmp/images/img001.jpg
*										0 - Имя файла картинки будет представлять собой хеш полного
*											пути исходного файла
*											Пример: 083774f1cdd7e4a963d06db271ce8db3.jpg
*
*	@return	ничего
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _correct_image_url(&$code_res, $dir_src_in, $dir_dst_in, $dir_web_in, $file_name_mode_in = 0)
{
	$arr = array();
	$arr_ta = array();	
	
	$dir_src 	= $dir_src_in;			// путь к директории с исходным файлом
	$dir_dst	= $dir_dst_in;			// путь к директории назначения
	$dir_web	= $dir_web_in;			// относительный путь от корневой веб-папки до папки картинки
	$name_mode	= $file_name_mode_in;	// как формировать имя картинки
	
	if(empty($dir_src) || empty($dir_dst))
	{
		_debug('Ошибка параметров');
		return;
	}
		
	// Найти все теги TEXTAREA
	$tmp = '/<textarea.*<\/textarea>/isU';
	if(preg_match_all($tmp, $code_res, $arr))
	{
		// Заменить все теги TEXTAREA на уникальные строки
		foreach ($arr[0] as $code)
		{
			$key = uniqid();
			$arr_ta[$key] = $code;
			$code_res = str_replace($code, $key, $code_res);
		}
	}

	$preg_what = '/<img.*?src[\s]*=[\s\"\']+([^\s\"\'>]*)[\s\"\']*.*?>/is';

	$str_res = '';
	$str_buf = $code_res;
	$as = $ad = array();
	
	if(preg_match_all($preg_what, $str_buf, $ao))
		for($i = 0, $cnt = count($ao[0]); $i < $cnt; $i ++)
		{
			$src = $ao[1][$i];			
			$copy = strpos($src, '.') !== 0;
			$exist = true;
			
			if($copy)
			{
				$dir_page = _get_dir($dir_src, -1, true);
				
				if($name_mode == 1)
					$path = str_replace('/', '-', trim($dir_page.basename($src), '/'));
				else if($name_mode == 2)
					$path = _path($dir_page, $src);
				else
				{
					$path = _path($dir_src, $src);
					$pi = pathinfo($path);
					$path = md5($path).'.'.$pi['extension'];
				}
				
				// Если исходной картинки не существует, 
				// то путь к ней в исходной html-странице не меняется
				if($exist = file_exists(_path($dir_src, $src)))
				{
					if(!in_array($src, $as))
					{
						$as[] = $src;
						$ad[] = $path;
					}
					
					$dst = _path(array($dir_web, $path));
				}
				
			}
			// Убрать слеш и точку в начале пути к картинке
			else
				$dst = ltrim($src, '\\/.');
			
			if($exist)
			{
				$what = $ao[0][$i];
				$with = str_replace($src, $dst, $what);
				$str_buf = str_replace($what, $with, $str_buf);
			}
		}
		
	$code_res = $str_buf;
	
	// Заменить все уникальные строки на теги TEXTAREA
	foreach ($arr_ta as $key=>$code)
		$code_res = str_replace($key, $code, $code_res);

	_copy($as, $ad, $dir_src, $dir_dst);
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Архивировать директорию
*
*	@param	string $dir_in - исходная директория
*	@param	object $zip_in - объект архива
*	@param	string $sub_dir_in - вложенная фдиректория
*
*	@return ничего
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _dir_2_zip($dir_in, &$zip_in, $sub_dir_in = null) 
{
	// no resource given, exit
	if($zip_in == null)
		return false;
	
	// we check if $dir_in has a slash at its end, if not, we append one
	$dir_in .= end(str_split($dir_in)) == '/' ? '' : '/';
	$sub_dir_in .= end(str_split($sub_dir_in)) == '/' ? '' : '/';
	
	// we start by going through all files in $dir_in
	$handle = opendir($dir_in);
	while($f = readdir($handle)) 
	{
		if($f != '.' && $f != '..') 
		{
			if(is_file($dir_in.$f)) 
			{
				// if we find a file, store it
				// if we have a subfolder, store it there
				if ($sub_dir_in != null)
					$zip_in->addFile($dir_in.$f, $sub_dir_in.$f);
				else
					$zip_in->addFile($dir_in.$f);
			} 
			else if(is_dir($dir_in.$f)) 
			{
				// if we find a folder, create a folder in the zip 
				$zip_in->addEmptyDir($f);
				// and call the function again
				_dir_2_zip($dir_in.$f, $zip_in, $f);
			}
		}
	}
	
	if($handle)
		closedir($handle);

}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить значение переменной сессии
*
*	@param	string	$name_in 			- имя переменной
*	@param	object	$default_value_in	- значение по умолчанию, если переменная
*									  	  неопределена
*
*	@return	string/number - значение переменной
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////
	
function _get_sess_val($name_in, $default_value_in = null)
{
	$res	= $default_value_in;
	$name 	= strtolower($name_in);
	
	if(isset($_SESSION[$name]))
		$res = $_SESSION[$name];
	
	return $res;
}

//////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Установит значение переменной сессии
*
*	@param	string	$name_in	- имя переменной сессии
*	@param	object	$value_in	- значение переменной
*
*	@return	ничего
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////
	
function _set_sess_val($name_in, $value_in)
{
	$name 	= strtolower($name_in);
	$_SESSION[$name] = $value_in;
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Сгенерировать уникальную поддиректорию в директории
*
*	@param	string $dir_in - корневая директория
*
*	@return string - уникальная директория в корневой директории или null
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _uniq_dir($dir_in)
{
	$max = 100;
	$dir = rtrim($dir_in, '\\/').'/';

	while($i ++ < $max)
	{	
		$tmp = _uniq_id();
		$path = $dir.$tmp;

		if(!file_exists($path))
			return $path.'/';
	}

	return null;
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Сделать первю букву заглавной
*
*	@param	string/array $obj_in	- входная строка или массив строк
*	@param	string $charset_in 		- кодировка строки
*
*	@return	string - итоговая строка
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _ucfirst($obj_in, $charset_in = 'UTF-8') 
{ 
	/*
	if(is_array($str_in))
	{
		foreach($str_in as $cur)
			$res[] = _ucfirst($cur, $charset_in);
			
		return $res;
	}
	
	return mb_strtoupper(mb_substr($str_in, 0, 1, $charset_in), $charset_in).mb_substr($str_in, 1, mb_strlen($str_in, $charset_in) - 1, $charset_in); 
	*/
	
	return _xcfirst($obj_in, 1, $charset_in);
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Сделать первую букву строки маленькой или большой
*
*	@param	string/array $obj_in	- входная строка или массив строк
*	@param	number $mode_in			- мода первой буквы
*	@param	string $charset_in		- кодировка строки
*
*	@return	string - итоговая строка
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _xcfirst($obj_in, $mode_in = 0, $charset_in = 'UTF-8') 
{ 
	if(is_array($obj_in))
	{
		$mode = $mode_in;
		
		foreach($obj_in as $cur)
		{
			$res[] = _xcfirst($cur, $mode, $charset_in);
			$mode -= ($mode_in > 1 ? 2 : 0);
		}
			
		return $res;
	}

	$fun = 'mb_strto'.($mode_in > 0 ? 'upper' : 'lower');

	return $fun(mb_substr($obj_in, 0, 1, $charset_in), $charset_in).mb_substr($obj_in, 1, mb_strlen($obj_in, $charset_in) - 1, $charset_in); 
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить имя файла без расширения
*
*	@param	string $path_in - входной путь
*
*	@return string - имя файла
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _name($path_in)
{
	$pi = pathinfo($path_in);
	
	$res = $pi['basename'];
	$ext = $pi['extension'];
		
	$pos = strrpos($res, '.'.$ext);
	if($pos !== false)
		$res = substr($res, 0, $pos);
	
	return $res;
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить расширение файла без точки
*
*	@param	string $path_in - входной путь
*
*	@return string - расширение файла
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _ext($path_in)
{
	$pi = pathinfo($path_in);	
	return $pi['extension'];
}

//////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Копировать массив файлов
*
*	@param	string/array $src_in	- исходные файлы
*	@param	string/array $dst_in	- файлы назначения
*	@param	string $dir_dst_in		- исходная директория
*	@param	string $dir_dst_in		- директория назначения
*	@param	array $opt_res			- массив опций
*
*	@return	bool - true, если все файлы скопированы
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _copy_ex($src_in, $dst_in, $dir_src_in, $dir_dst_in, &$opt_res)
{
	$cnt = 0;
	$as = is_array($src_in) ? $src_in : array($src_in);
	$ad = is_array($dst_in) ? $dst_in : array($dst_in);

	$ts = _get_opt_val('time_save', $opt_res, true);
	$dc = _get_opt_val('dir_deep', $opt_res, 3);
	$fun_copy = _get_opt_val('fun_copy', $opt_res, 'copy');
	$arr_res = &$opt_res;
	$err = 0;
	
	for($i = 0, $c = max(count($as), count($ad)); $i < $c; $i ++)
	{
		$src = _path($dir_src_in, $as[$i]);
		$dst = _path($dir_dst_in, $ad[$i]);
		
		if(file_exists($src) && (file_exists($dst) ? filemtime($src) > filemtime($dst) : true))
		{
			// !!! is_dir($src)
			$dir = is_dir($src) ? $dst : dirname($dst);

			if(!file_exists($dir))
			{
				for($j = $dc; $j >= 0; $j --)
				{
					$cur = _get_dir($dir, -$j);
					if(!file_exists($cur))
						if(!mkdir($cur))
							if(is_array($arr_res))
								$arr_res['err-list'][] = 'mkdir -> '.$cur.' - err';
				}
			}

			$r = (is_dir($src) || $fun_copy($src, $dst)) ? 1 : 0;
			if(!$r)
			{
				$err ++;
				if(is_array($arr_res))
					$arr_res['err-list'][] = $src.' -> '.$dst.' - err';
			}
			else
			{
				if(is_array($arr_res))
					$arr_res['ok-list'][] = $src.' -> '."$dst($dir)".' - ok';
				
				if($ts && !is_dir($src))
				{
					$at = fileatime($src);
					$mt = filemtime($src);
					touch($dst, $mt, $at);
				}
			}
			
			$cnt += $r;
		}
	}
	
	$all = count($src_in);
	if(is_array($arr_res))
	{
		$arr_res['err'] = $err;
		$arr_res['coped'] = $cnt.'/'.$all.' - ok';
	}
	
	return $cnt == $all;
}

//////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Копировать массив файлов
*
*	@param	string/array $src_in	- исходные файлы
*	@param	string/array $dst_in	- файлы назначения
*	@param	string $dir_dst_in		- исходная директория
*	@param	string $dir_dst_in		- директория назначения
*
*	@return	bool - true, если все файлы скопированы
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _copy($src_in, $dst_in, $dir_src_in = '', $dir_dst_in = '')
{
	$at = array();
	return _copy_ex($src_in, $dst_in, $dir_src_in, $dir_dst_in, $at);
}

function _file_list($file_in)
{
	$arr_res = array();
	$arr = is_array($file_in) ? $file_in : array($file_in);
	
	foreach($arr as $cur)
	{
		$path = _path(array($cur, '*.*'));
		$at = glob($path);			
		$arr_res = array_merge($arr_res, $at);
	}
	
	return $arr_res;
}

/////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Преобразовать строку в ассоциированный массив
*
*	Свойства-ключи массива задаются в заголовке и в отдельных частях (строках).
*	Заголовок представляет собой первую строку, начинающуюся с символа @ и
*	разделенную символами \t.
*	Если свойство зарается в отдельной части, то разделитель =>
*
*	@param	string $code_in - исходная строка
*	@param	string $su_in - разделитель элементов строки
*	@param	string $sp_in - разделитель частей
*	@param	string $wu_in - символы, которые нужно обрезать в эл. строки
*	@param	string $wp_in - символы, которые нужно обрезать в частях
*
*	@return	array - ассоциированны массив
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _s2aa(&$code_in, $su_in = "\t", $sp_in = "\n", $wu_in = '', $wp_in = '', $split_in='=>')
{
	$res = '';

	$buf = str_replace("\r", '', $code_in);
	$at = explode($sp_in, $buf);
	$cnt = 0;
	$ap = array();

	foreach($at as $cur)
	{
		$cur = trim(rtrim($cur, $sp_in), $wp_in);
		
		if(!$cnt ++ && strpos($cur, '@') === 0)
			$ap = array_diff(explode("\t", ltrim($cur, '@')), array(''));
		
		if(($p = strpos($cur, "\t//")) !== false)
			$cur = substr($cur, 0, $p);

		if(strpos($cur, '@') === 0 || empty($cur))
			continue;

		$av = !empty($su_in) ? explode($su_in, rtrim($cur), count($ap) + 1) : array($cur);
		$ext = count($av) > count($ap) ? array_pop($av) : '';

		$ae = s2a($ext, $split_in, $su_in);

		if(count($ap))
		{
			$av = array_pad($av, count($ap), '');
			$at = array_combine($ap, $av);
		}
		else
			$at = array();
		
		$ar = $at + $ae;
		$an = array();
		foreach($ar as $k=>$v)
			$an[trim($k, $wu_in)] = trim($v, $wu_in);
		
		$res[] = $an;
		
	}

	return $res;	
}

//////////////////////////////////////////////////////////////////////////////
//===========================================================================
/**
*	Получить дерево каталога
*
*	@param	string $src_dir_in	- корневая директория
*	@param	number $mode_in		- битовая маска
*								  	1 - только файлы
*								  	2 - только директории
*								  	4 - только текущая директория
*
*	@return	array - массив файлов
*/
//===========================================================================
//////////////////////////////////////////////////////////////////////////////

function _dir_tree($src_dir_in, $mode_in = 0)
{
	$arr_res = array();
	$srcdir = $src_dir_in;
	$m = !$mode_in ? 3 : $mode_in;
	$tmpl = '';
	
	if(!is_dir($srcdir))
	{
		$srcdir = str_replace('\\', '/', $srcdir);
		if(($pos = strrpos($srcdir, '/')) !== false)
		{
			$tmpl = substr($srcdir, $pos + 1);
			$srcdir = substr($srcdir, 0, $pos);
		}
	}
	
	if(!is_dir($srcdir))
		return $arr_res;
	
	if(!empty($tmpl))
	{
		$tmp = _uniq_id();	
		$as = array('.*', '*', '|', '(', ')');
		$ad = array($tmp.'_1', $tmp.'_1', $tmp.'_2', $tmp.'_3', $tmp.'_4');
		
		$tmpl_src = $tmpl;
		$tmpl = str_replace($as, $ad, $tmpl);
		$tmpl = '/'.preg_quote($tmpl).'$/iuS';
		$tmpl = str_replace($ad, $as, $tmpl);
	}
	
	// открываем исходный каталог
	if($curdir = @opendir($srcdir)) 
	{
		// последовательно считываем все
		// имена файлов и каталогов
		while($file = readdir($curdir)) 
		{
			// пропускаем указатель на текущий и
			// предыдущий каталоги
			if($file != '.' && $file != '..') 
			{
				$srcfile = rtrim($srcdir, '/').'/'.$file;
				
				$b = is_dir($srcfile);
				
				// если текущий элемент - файл
				if(($m & 1 && !$b) || ($m & 2 && $b))
					if(empty($tmpl) || preg_match($tmpl, $file))
						array_push($arr_res, $srcfile);

				// если текущий элемент - директория
				if($b && !($m & 4))
				{
					// получить содержимое внутреннего каталога
					$arr_new = _dir_tree(empty($tmpl_src) ? $srcfile : _path($srcfile, $tmpl_src), $m);
					if(is_array($arr_new))
						$arr_res = array_merge($arr_res, $arr_new);
				}	
			}
		}
		
		// закрываем ранее открытый каталог
		closedir($curdir);
	}
	
	return $arr_res;
}

function _mime($filename)
{
	if(function_exists('mime_content_type'))
		return mime_content_type($filename);

	$mime_types = array(

		'txt' => 'text/plain',
		'htm' => 'text/html',
		'html' => 'text/html',
		'php' => 'text/html',
		'css' => 'text/css',
		'js' => 'application/javascript',
		'json' => 'application/json',
		'xml' => 'application/xml',
		'swf' => 'application/x-shockwave-flash',
		'flv' => 'video/x-flv',

		// images
		'png' => 'image/png',
		'jpe' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'gif' => 'image/gif',
		'bmp' => 'image/bmp',
		'ico' => 'image/vnd.microsoft.icon',
		'tiff' => 'image/tiff',
		'tif' => 'image/tiff',
		'svg' => 'image/svg+xml',
		'svgz' => 'image/svg+xml',

		// archives
		'zip' => 'application/zip',
		'rar' => 'application/x-rar-compressed',
		'exe' => 'application/x-msdownload',
		'msi' => 'application/x-msdownload',
		'cab' => 'application/vnd.ms-cab-compressed',

		// audio/video
		'mp3' => 'audio/mpeg',
		'qt' => 'video/quicktime',
		'mov' => 'video/quicktime',

		// adobe
		'pdf' => 'application/pdf',
		'psd' => 'image/vnd.adobe.photoshop',
		'ai' => 'application/postscript',
		'eps' => 'application/postscript',
		'ps' => 'application/postscript',

		// ms office
		'doc' => 'application/msword',
		'rtf' => 'application/rtf',
		'xls' => 'application/vnd.ms-excel',
		'ppt' => 'application/vnd.ms-powerpoint',

		// open office
		'odt' => 'application/vnd.oasis.opendocument.text',
		'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
	);

	$ext = strtolower(array_pop(explode('.',$filename)));
	
	if(array_key_exists($ext, $mime_types))
		return $mime_types[$ext];
	else if(function_exists('finfo_open')) 
	{
		$finfo = finfo_open(FILEINFO_MIME);
		$mimetype = finfo_file($finfo, $filename);
		finfo_close($finfo);
		return $mimetype;
	}
	else
		return 'application/octet-stream';
}

?>