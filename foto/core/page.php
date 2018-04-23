<?

class c_page
{
	protected $m_props;
	protected $m_dbg;
	protected $m_changes;
	protected $m_refs;
	protected $m_ref;
	protected $m_root_id;
	protected $m_dirs;
	protected $m_charset;
	protected $m_status;
	
	function __construct()
	{
		self::init();
	}
	
	function init()
	{
		$this->m_dbg = 0;
		$this->m_props = array();
		$this->m_changes = array();		
		$this->m_paths = array();
		$this->m_refs = array();		
		$this->m_scripts = array();
		$this->m_styles = array();
		$this->m_ref = array();
		$this->m_root_id = null;
		$this->m_dir_files = array();
		$this->m_charset = g_charset;
		$this->m_status = 0;
		
		$this->load_refs();
	}
	
	function get($prop_in, $type_in = '', $def_in = null)
	{
		if($def_in !== null)
			$def = $def_in;
		else if(in_array($type_in, array('array', 'arr')))
			$def = array();
		else if(in_array($type_in, array('object', 'obj')))
			$def = null;
		else if(in_array($type_in, array('number', 'num')))
			$def = 0;
		else
			$def = '';
		
		if(array_key_exists($prop_in, $this->m_props))
			$res = $this->m_props[$prop_in];
		else
		{
			$this->dbg('Property not found: '.$prop_in);
			$res = $def;
		}

		return $res;
	}
	
	function set($prop_in, $val_in)
	{
		$this->m_props[$prop_in] = $val_in;
	}
	
	function dbg($obj_in)
	{
		if($this->m_dbg & 1)
		{
			if(is_string($obj_in))
				echo($obj_in);
			else
				print_r($obj_in);
		}
	}
	
	function changes(&$code_res, $arr_in = null)
	{
		global $_ach;
		return _changes($code_res, is_array($arr_in) ? $arr_in : array_merge($this->m_changes, $_ach));
		///return _changes($code_res, is_array($arr_in) ? $arr_in : $this->m_changes);
	}
	
	function arr_param(&$arr_res, $p_in, $v_in = '')
	{
		if(is_array($p_in))
		{
			foreach($p_in as $k => $v)
				$arr_res[$k] = $v;
		}
		else
			$arr_res[$p_in] = $v_in;
	}
	
	function add($p_in, $v_in = '')
	{
		$ar = is_array($p_in) ? $p_in : array($p_in=>$v_in);		
		$ap = array();
		
		foreach($ar as $k=>$v)
		{
			$k = strpos($k, '(') === false ? strtoupper($k) : $k;
			$ap[$k] = $v;
		}
		//$ap = $ar;
		
		$this->arr_param($this->m_changes, $ap);

		if(strlen($v_in))
		{
			$ac = $this->find_marks($v_in);
			$an = $this->add_marks($ac);
			if(count($an))
				$this->add($an);
		}
	}

	function arr_val(&$arr_res, $p_in, $uniq_in = false)
	{
		$arr = is_array($p_in) ? $p_in : explode(';', $p_in);

		foreach($arr as $v)
			if(!$uniq_in || !in_array($v, $arr_res))
				array_push($arr_res, $v);
	}

	function add_style($p_in)
	{
		$this->arr_val($this->m_styles, $p_in, true);
	}
	
	function add_script($p_in)
	{
		$this->arr_val($this->m_scripts, $p_in, true);
	}
	
	function val($v_in, $v_def_in = '')
	{
		return defined($v_in) ? CONSTANT($v_in) : $v_def_in;
	}
	
	function file_path($id_in)
	{
		/*
		$arr_dir = array_merge(array(g_path_nodes, g_path_app), $this->get('dir_files', 'array'));
		
		foreach($arr_dir as $cur)
		{
			$path = $cur.$id_in;
			if(file_exists($path))
				return $path;
		}
		
		return null;
		*/
		
		return _file_path($id_in);
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Получить содержимое файла целиком или часть по метке
	*
	*	Части задается в id_in через запятую. Пример: 'a1/a1.htm, part1'
	*
	*	Метки частей:
	*		<!---PART.ABC.START--->	- начало части
	*		<!---PART.ABC.END--->	- конец части
	*
	*	@param	string $id_in - относительный путь к файлу.
	*							Состоих из относительного пути к файлу 
	*							и через запятую может указываться имя части
	*							внутри этого файла
	*
	*	@return string - извлеченный код
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function file_contents($id_in, $id_parent_in = null)
	{
		$res = '';
		$path = '';
		
		list($id, $part) = _explode(',', str_replace(' ', '', $id_in), 2, 2);

		if(!empty($id_parent_in))
		{
			$path_parent = $this->file_path($id_parent_in);
			$dir = dirname($path_parent);
			$path = _path($dir, $id);
		}
		
		if(!file_exists($path))
			$path = $this->file_path($id);

		$code = file_exists($path) ? file_get_contents($path) : '';
		
		// Извлечь нужную часть кода
		if(!empty($code) && !empty($part)) 
		{
			$start = '<!---PART.'.strtoupper($part).'.START--->';
			$end = '<!---PART.'.strtoupper($part).'.END--->';
			
			$ps = 0;
			while(1)
			{
				$ps = strpos($code, $start, $ps);
				$pe = strpos($code, $end, $ps);
				
				if($ps !== false && $pe !== false)
				{
					$ps += strlen($start);
					$res .= substr($code, $ps, $pe - $ps)."\n";
					$ps += strlen($end);
				}
				else
					break;
			}
		}
		else
			$res = $code;
		
		return $res;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Получить содержимое страницы по идентификатору
	*
	*	@param	string $id_in		- идентификатор страницы в $this->m_refs
	*	@param	bool $not_base_in	- не основная страница. 
	*								  При false устанавливается массив $this->m_ref,
	*								  а также добавляются мета-поля
	*
	*	@return array - массив, содержащий основной код, стили и php-код
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function page_contents($id_in = '', $not_base_in = false)
	{
		if(!$not_base_in)
			$path = $this->init_page($id_in);
		else
			$path = (array_key_exists($id_in, $this->m_refs) ? $this->m_refs[$id_in]['path'] : null);

		$code = _av($this->m_ref, 'content');		
		if(empty($code))
			$code = $this->file_contents($path);
		//$code = $this->file_contents($path);

		$dir_src = dirname($this->file_path($path));
		$dir_dst = g_path_docs.g_dir_img_tmp;		
		_correct_image_url($code, $dir_src, $dir_dst, g_dir_img_tmp.'/', 1);
		
		$ar = $this->correct_code($code, $not_base_in);

		return $ar;
	}
	
	function load_refs()
	{
		$buf = $this->file_contents(g_path_refs);
		$af = explode("\n", empty($buf) ? "id=>1\tcid=>\tlog=>1\tpath=>index.htm\nid=>2\tpid=>logout\tlog=>1\tpath=>index.htm" : $buf);
		$at = null;
		
		unset($buf);
		
		foreach($af as $row)
		{
			$row = trim($row);
			
			if(empty($row))
				continue;
			
			if(strpos($row, '@') === 0)
			{
				if(!$at)
					$at = explode("\t", ltrim(preg_replace('/[\t]{2,}/', "\t", $row), '@'));
				
				continue;
			}
			else
				$ar = explode("\t", $row);

			$ap = array();
			$i = 0;
			foreach($ar as $str)
			{
				if(strpos($str, '=>'))
					list($p, $v) = _explode('=>', $str, 2, 2);
				else
				{
					$v = $str;
					$p = ($at && count($at) > $i) ? $at[$i] : $i;
					$i ++;
				}

				$ap[$p] = $v;
			}
			
			$id = $ap['id'];
			if(!strlen($id))
				continue;
			
			$title = $ap['title'];
			if(strpos($title, '[') !== false)
				$ap['title'] = str_replace(array('[', ']'), '', $title);
			if(!array_key_exists('title-short', $ap))	
				$ap['title-short'] = preg_replace('/\[.*\]/U', '', $title);

			$this->m_refs[$id] = $ap;
		}

		if(count($this->m_refs))
		{
			reset($this->m_refs);
			$at = current($this->m_refs);
			$this->m_root_id = $at['id'];
		}
		else
			$this->m_root_id = null;
		
		//print_r($this->m_refs);
	}
	
	function get_ref_param($key_in, $val_in, $param_in = null)
	{
		//ech("$key_in, $val_in, $param_in");
		
		foreach($this->m_refs as $at)
		{
			if(_av($at, $key_in) == $val_in)
			{
				if($param_in !== null)
				{
					$res = array();
					$ap = is_array($param_in) ? $param_in : array($param_in);

					foreach($ap as $cur)
					{
						$k = strlen($cur) ? $cur : count($res);
						$res[$k] = array_key_exists($cur, $at) ? $at[$cur] : null;
					}
					
					$res = is_array($param_in) ? $res : (count($res) ? array_shift($res) : null);
				}
				else
					$res = $at;
				
				//ech($res);
				
				return $res;
			}
		}
		
		return null;
	}
	
	function get_ref_param_2($key_in, $val_in, $param_in = null)
	{
		//ech("$key_in, $val_in, $param_in");
		
		foreach($this->m_refs as $at)
		{
			if(_av($at, $key_in) == $val_in)
			{
				if($param_in !== null)
				{
					$res = array();
					$ap = is_array($param_in) ? $param_in : array($param_in);

					foreach($ap as $cur)
					{
						$k = strlen($cur) ? $cur : count($res);
						$res[$k] = array_key_exists($cur, $at) ? $at[$cur] : null;
					}
					
					$res = is_array($param_in) ? $res : (count($res) ? array_shift($res) : null);
				}
				else
					$res = $at;
				
				//ech($res);
				
				return $res;
			}
		}
		
		return null;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Получить реальную ссылку по набору параметров
	*
	*	@param	array/string $ap_in - массив параметров или строка, 
	*								  которая может сождержать query-строку 
	*								  вида p1=v1&p2=v2 или id-страницы
	*	@param	number $mode_in 	- мода создания ссылки
	*									0 - стандарт
	*								 	1 - id является логическим путем страницы
	*
	*	@return	string - строка ссылки
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function get_ref($ap_in, $mode_in = 0)
	{
		$res = '';
		$ap = array();
		$root = '.';
		
		// строка
		if(is_string($ap_in))
		{
			// query
			if(strpos($ap_in, '='))
				$ap = _parse_str($ap_in);
			// id
			else
				$ap[_id] = $ap_in;
		}
		// массив
		else if(is_array($ap_in))
			$ap = $ap_in;

		// Добавить текущий id, если его нет в массиве
		if(!array_key_exists(_id, $ap))
		{
			$id = $this->page_id();
			if($id)
				$ap[_id] = $id;
		}
		
		// 967-3 -> 967
		//print_r($ap_in);
		$tmp = 'this';
		if(strpos(strtolower($ap[_id]), 'this') === 0)
		{
			$id = $this->page_id();
			if(($p = strpos($id, '-')) !== false)
				$id = substr($id, 0, $p);			
			$id .= substr($ap[_id], strlen($tmp));
			
			if($id)
				$ap[_id] = $id;
		}
		
		$id = $ap[_id];
		// Если ссылка является идентификатором логическлго пути к странице
		if(strpos($id, '.') !== false || $mode_in == 1)
		{
			$id = $this->get_ref_param('log', $id, 'id');
			$ap[_id] = $id;
		}
		
		$at = $this->get_ref_param('id', $id);
		$title = $at['title'];
		$this->changes($title);
		
		/*
		if($id !== $this->m_root_id && mb_strlen($title, $this->m_charset) <= 30 && !array_key_exists('str', $ap))
			$ap['str'] = $title;
		*/
		
		$chpu = defined('g_chpu') && g_chpu === true;
		
		// ЧПУ
		if(!_av($ap, _act) && $chpu)
		{
			//$id = $ap[_id];			
			$obj = $at['obj'];
			$ps = $at['pm']; 
			
			if($ap[_id] === $this->m_root_id)
				$ap[_id] = $root;
			else if(!empty($title))
				$ap[_id] = $title;
			
			if(!empty($obj))
			{
				$obj = $this->get_obj($obj);
				if(method_exists($obj, 'params'))
				{
					$title = $obj->params('title', $ap);				
					$ap[$obj->m_pm_self] = $title;
				}
			}
			
			$apm = array_merge(array(_id), empty($ps) ? array() : explode(',', $ps));
			
			$af = array();
			foreach($apm as $k)
			{
				if(array_key_exists($k, $ap))
				{
					$af[] = urlencode($ap[$k]);
					unset($ap[$k]);
				}
			}

			$res = implode('/', $af);
		}
		
		// Корневая страница
		if(count($ap) == 1 && array_key_exists(_id, $ap) && $ap[_id] == $this->m_root_id)
			$res = $root;
		else
		{
			$ext = http_build_query($ap);
			$res .= (empty($ext) ? '' : '?').$ext;
		}
		
		// Править путь к главной странице с учетом, что сервер может работать локально
		if(strpos($res, $root) === 0)
		{
			$index = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? basename($_SERVER['PHP_SELF']) : '';
			//$res = preg_replace('/^'.preg_quote($root).'/', ($_SERVER['HTTP_HOST'] == 'localhost' ? './'.$index : '/'), $res);
			$res = preg_replace('/^'.preg_quote($root).'/', ($chpu ? '/' : './'.$index), $res);
		}

		return $res;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Сформировать реальные ссылки на странице
	*
	*	@param	string $code_res	- код страницы
	*
	*	@return	ничего
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////

	function prepare_refs(&$code_res)
	{
		$at = _page_refs($code_res);
		
		foreach($at as $ac)	
		{
			$ap = array();
			$what = $ac[0];
			$pm = trim($ac[1]);
			
			$b_ref = true;
			
			//if($k == 'id')
			if(strpos($what, '-REF.'))	
			{
				// REF.312&t=123
				if(strpos($pm, '=') > 0)
				{
					$pm = _id.'='.$pm;
					$ap = _parse_str($pm);
				}
				// REF.312
				else
					$ap[_id] = $pm;
			}
			// PID.logout
			//else if($k == 'cid')
			else if(strpos($what, '-PID.'))	
			{
				$pm = $this->get_ref_param('cid', $pm, 'id');
				$ap[_id] = $pm;
			}
			// REF(id=312&t=123)
			//else if($k == 'ps')
			else if(strpos($what, '-REF('))	
			{
				$ap = _parse_str($pm);
				
				// REF(cid=logout&t=123)
				$kp = 'cid';
				if(array_key_exists($kp, $ap))
				{
					$vp = $ap[$kp];
					$pm = $this->get_ref_param('cid', $vp, 'id');
					$ap[_id] = $pm;
					unset($ap[$kp]);
				}
				
				// REF(cv=logout)
				$kp = 'cv';
				if(array_key_exists($kp, $ap))
				{
					$b_ref = false;
					
					$vp = $ap[$kp];
					$pm = $this->get_ref_param('cid', $vp, 'id');
					$ap[_id] = $pm;
					unset($ap[$kp]);
				}
			}
			
			$with = $b_ref ? $this->get_ref($ap) : $ap[_id];

			$code_res = str_replace($what, $with, $code_res);
		}
	}	
	
	function init_page($id_in)
	{
		if(!count($this->m_refs))
			return null;
		
		$first = current($this->m_refs);
		$id = strlen($id_in) ? $id_in : $first['id'];

		//$this->m_ref = (array_key_exists($id, $this->m_refs) ? $this->m_refs[$id] : array());
		//$path = (array_key_exists('path', $this->m_ref) ? $this->m_ref['path'] : '');
		
		if(!array_key_exists($id, $this->m_refs))
		{
			$id = $this->get_ref_param('cid', 'not_found', 'id');
			if(empty($id))
				_debug('Не найдна страница 404');
		}
		
		$this->m_ref = (array_key_exists($id, $this->m_refs) ? $this->m_refs[$id] : array());
		$path = array_key_exists('path', $this->m_ref) ? $this->m_ref['path'] : '';

		return $path;
	}
	
	///////////////////////////////////////////////////////////////////////////////////
	//================================================================================
	/**
	*	Получить часть кода строки
	*
	*	@param	string $code_res 	- строка
	*	@param	string $start_in 	- первый разделитель
	*	@param	string $end_in 		- второй разделитель
	*
	*	@return	string - выделенный код 
	*/
	//================================================================================
	///////////////////////////////////////////////////////////////////////////////////
	
	static function get_part_code(&$code_res, $start_in, $end_in)
	{
		$res = "";
		
		while(1)
		{
			$what_start = $start_in;
			$pos_start = strpos($code_res, $what_start);
			$what_end = $end_in;
			$pos_end = strpos($code_res, $what_end);
			
			if($pos_start >= 0 && $pos_end > $pos_start)
			{
				$res .= substr($code_res, $pos_start + strlen($what_start), $pos_end - ($pos_start + strlen($what_start)))."\r\n\r\n";
				$code_res = substr($code_res, 0, $pos_start).substr($code_res, $pos_end + strlen($what_end));
			}
			else
				break;
		}
		
		return $res;
	}
	
	//////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Изменить код страницы
	*
	*	@param	string $code_res	- код страницы для изменения
	*	@param	array $arr_res 		- результат изменения кода. Здесь представлены
	*								  различные части кода страницы: php-скрипт и др.
	*	@param	string $mode_in		- мода обработки кода страницы
	*
	*	@return	number - количество извлеченных из кода частей, совпадающее 
	*					 с размером $arr_res
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function prepare_code(&$code_res, &$arr_res, $mode_in = 0)
	{
		$php_code 	= '';
		$stl_code 	= '';
		$stl_regexp	= '/<style(.*)style>/sU';
		
		// php-код страницы
		$php_code = self::get_part_code($code_res, '<?', '?>');

		// Код стилей страницы
		if(preg_match_all($stl_regexp, $code_res, $arr_tmp))
			for($i = 0, $c = count($arr_tmp[0]); $i < $c; $i ++)
			{
				$buf = $arr_tmp[0][$i];

				// Добавить тип тега
				$pos = mb_strpos($buf, '>');
				if($pos > 0)
					//$buf = '<style type="text/css">'.mb_substr($buf, $pos + 1);
					$buf = '<style>'.mb_substr($buf, $pos + 1);

				$stl_code .= $buf."\r\n";
			}

		$php_code = rtrim($php_code);
		$stl_code = rtrim($stl_code);
		
		// Удалить код
		if($mode_in == 0)
			$code_res = preg_replace($stl_regexp, '', $code_res);
		
		$arr_res['php'] = $php_code;
		$arr_res['stl'] = $stl_code;
		$code_res = ltrim($code_res);
		
		return count($arr_res);
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Откорректировать код страницы
	*
	*	@param	string $str_code 	- код станицы
	*	@param	bool $not_base_in	- не основная страница. 
	*								  При false устанавливается массив $this->m_ref,
	*								  а также добавляются мета-поля
	*
	*	@return	array - массив с основным кодом и стилями
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function correct_code(&$code_res, $not_base_in = false)
	{
		$keys = array('title', 'keywords', 'description');
		$res = 0;
		$arr = null;
		
		$this->prepare_code($code_res, $arr_res, 0);
		
		// Извлечь служебные поля
		foreach ($keys as $cur)
		{
			$val = null;
			//$res = preg_match('/(<!--[-]*[\s]*'.$cur.'[\s]*=)(.*)([-]*-->)/i', $code_res, $arr);
			$res = preg_match('/<!--[-\s]*'.$cur.'[\s]*=(.*)-->/i', $code_res, $arr);
			if($res > 0)
			{
				$code_res = str_replace($arr[0], '', $code_res);
				
				$val = trim($arr[1], "-\t\r\n ");
				$this->changes($val);
				
				// Иногда в мета-теги проникают теги
				$val = strip_tags($val);
			}

			if(!$not_base_in)
			{
				if(!empty($this->m_ref[$cur]))
					$val = $this->m_ref[$cur];
				
				if(empty($val))
					$val = $this->m_ref['title'];
				
				if(!empty($val))
					$this->add('main.'.$cur, $this->gen_tag($cur, $val, 0, 0));
			}
		}

		$arr_res['code'] = $code_res;

		return $arr_res;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Сгенерировать тег
	*
	*	@param	string $tag_in 		- имя тега
	*	@param	string $param_in 	- значение параметра
	*			number $num_tab		- число табуляций перед тегом
	*			number $num_endl	- число новых строк после тега
	*
	*	@return	string - код тега
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function gen_tag($tag_in, $param_in = null, $num_tab = 1, $num_endl = 1)
	{
		if(empty($tag_in))
			return '';
		
		$str_res 	= '';
		$tag	 	= strtolower($tag_in);
		$param		= null;
		$res 		= '';
		$multi		= false;
		$media		= '';
		
		if(is_array($param_in))
		{
			$param = @$param_in['value'];
			$media = @$param_in['media'];
		}
		else
			$param = $param_in;
				
		if($tag == "keywords")
			$str_res = "<meta name=\"keywords\" content=\"$param\" />";
		else if($tag == "description")
			$str_res = "<meta name=\"description\" content=\"$param\" />";
		else if($tag == "style")
		{
			if(!empty($media))
				$media = " media=\"$media\"";
				
			//$str_res = "<link href=\"$param\" type=\"text/css\" rel=\"stylesheet\"$media />";
			$str_res = "<link href=\"$param\" rel=\"stylesheet\"$media />";
		}
		else if($tag == "script")
			//$str_res = "<script src=\"$param\" type=\"text/javascript\"></script>";
		$str_res = "<script src=\"$param\"></script>";
		else if($tag == "styles")
		{
			$multi = true;
			
			foreach ($this->m_styles as $cur)
			{
				if(!empty($cur))
				{
					$buf = $this->gen_tag("style", $cur, $num_tab, $num_endl);
					$str_res .= $buf;
				}
			}
		}
		else if($tag == "scripts")
		{
			$multi = true;
			
			foreach ($this->m_scripts as $cur)
			{
				if(is_array($cur))
				{
					foreach ($cur as $cur2)
					{
						if(!empty($cur2))
						{
							$buf = $this->gen_tag("script", $cur2, $num_tab, $num_endl);
							$str_res .= $buf;
						}
					}		
				}	
				else if(!empty($cur))
				{					
					$buf = $this->gen_tag("script", $cur, $num_tab, $num_endl);
					$str_res .= $buf;
				}
			}
		}
		else
			$str_res =  $param_in;
			
		if(!$multi)
			$str_res = str_repeat("\t", $num_tab).$str_res.str_repeat("\r\n", $num_endl);
			
		return $str_res;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Установить значения мета-тегов
	*
	*	@param	array $arr_in - ассоцированный массив тегов
	*
	*	@return	ничего
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function set_meta_tag($arr_in)
	{
		if(is_array($arr_in))
			foreach($arr_in as $cur=>$val)
				if(!empty($val))
					$this->add('main.'.$cur, $this->gen_tag($cur, $val, 0, 0));
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Оочистить код от меток
	*
	*	@param	string $code_res - исходный код
	*
	*	@return	ничего
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function clear_marks(&$code_res)
	{
		$reg = '/<!--(.*)-->/U';		
		$code_res = preg_replace($reg, '', $code_res);
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Найти все метки в коде
	*
	*	@param	string $code_res - исходный код
	*
	*	@return	array - массив всех меток
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function find_marks_(&$code_res)
	{
		$res = array();
		$reg = '/<!--[-]*([^-]+)[-]*-->/isuU';
		
		if(preg_match_all($reg, $code_res, $arr_tmp))
			for($i = 0, $c = count($arr_tmp[0]); $i < $c; $i ++)
				$res[] = $arr_tmp[1][$i];

		$res = array_unique($res);
		
		print_r($res);

		return $res;
	}
	
	function find_marks(&$code_res)
	{
		$res = array();
		$reg = '/<!--(.*)-->/isuU';
		
		if(preg_match_all($reg, $code_res, $arr_tmp))
			for($i = 0, $c = count($arr_tmp[0]); $i < $c; $i ++)
				$res[] = trim($arr_tmp[1][$i], '-');

		$res = array_unique($res);
		
		//print_r($res);

		return $res;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Получить строку по ключу
	*
	*	@param	string $key_in - ключ
	*	@param	string $param_in - параметр
	*
	*	@return	string - значение строки
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function get_str($key_in, $param_in = '')
	{
		return $key_in;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Добавить метки для замен
	*
	*	@param	array $arr_in - массим меток
	*
	*	@return	array - массив замен
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function add_marks($arr_in)
	{
		$an = array();
		
		foreach($arr_in as $cur)
		{
			if(
				preg_match('/([^\(]+)\(([^\)]*)\)/', $cur, $ap)
				||
				preg_match('/([^= ]+)[ ]*=[ ]*(.*)/', $cur, $ap)
				||
				preg_match('/(INS)\.(.*)/', $cur, $ap)
			)
			{
				$k = trim($ap[1]);
				$p = trim($ap[2]);
				
				$v = $this->get_str($k, $p);
			}
			else
				$v = $this->get_str($cur);
			
			if($v !== null)
				$an[$cur] = $v;
		}
		
		return $an;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Добавить метки в код
	*
	*	@param	string $code_res - исходный код
	*
	*	@return	number - количество добавленных меток
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function proc_marks(&$code_res)
	{
		$ac = $this->find_marks($code_res);
		$an = $this->add_marks($ac);
		$this->changes($code_res, $an);
		
		return count($an);
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Добавить метки в код
	*
	*	@param	string $code_res - исходный код
	*
	*	@return	number - количество добавленных меток
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function correct_image(&$code_res)
	{
		$ac = $this->find_marks($code_res);
		$an = $this->add_marks($ac);
		$this->changes($code_res, $an);
		
		return count($an);
	}
	
	function page_id()
	{
		return _rq('id');
	}
	
	function content($id_in = null)
	{
		$ar = $this->page_contents($id_in ? $id_in : $this->page_id());
		
		$status = _av($this->m_ref, 'status');
		if($status != 280 && !preg_match('/<!--[- ]*ALSO/i', $ar['code'])) // может быть <!---ALSO()--->
			$ar['code'] .= '<!---ALSO--->';
			
		return $ar;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Сгенерировать меню 
	*
	*	@param	string $key_in - относительный путь к файлу меню
	*
	*	@return	string - код меню
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function menu($key_in, $type_in = '')
	{
		$res = '';
		$type = in_array($type_in, array('table', 'tbl'));
		
		$path = $this->file_path($key_in);
		
		if(!file_exists($path))
			return $res;
		
		$buf = file_get_contents($path);
		
		if(_ext($path) == 'htm')
			return $buf;
		
		$as = _s2aa($buf);
		
		$list = '';		
		$wr = $type ? array('tr', 'td') : 'li';
		$wo = $type ? 'table' : 'ul';
		$root = current(explode('.', $this->m_ref['log']));
		
		$am  = array();
		$n = 0;
		$sel_id = null;
		foreach($as as $row)
		{
			$key = array_key_exists('id', $row) ? 'id' : 'log';
			$id = $row[$key];
			$title = _av($row, 'title');
			$class = _av($row, 'class');
			$redir = _av($row, 'redir');
			$layer = _av($row, 'layer');
			
			if($id == $root)
				$sel_id = $id;

			$ref = empty($redir) ? $this->get_ref($id, $key == 'log' ? 1 : 0) : $redir;
			$ap = array('href'=>$ref);
			if(!empty($class))
				$ap['class'] = $class;
			$a = wrap_tag($title, 'a', $ap);
			
			$id = !strlen($layer) ? $row['log'] : 'z'.(++ $n).str_pad('', $layer, '.');
			
			$am[$id] = $a;
		}
		
		$res = _html_tree($am, 'ul', 'li', $sel_id);
		$res = wrap_tag($res, 'nav', 'menu');

		return $res;

	}
	
	//////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Получить имя текущего домена
	*
	*	@param	ничего
	*
	*	@return	string - имя домена
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////

	function get_domain()
	{
		$res = $_SERVER['SERVER_NAME'];
		if($res == 'localhost')
			$res = g_domain_def;
		$arr = explode('.', $res);

		return trim(implode('.', array_slice($arr, -2)), '\\/');
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Оптравить заголовок ответа веб-сервера
	*
	*	@param	нет
	*
	*	@return	ничего
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function header()
	{
		$status = _av($this->m_ref, 'status', 0);
		$redir = _av($this->m_ref, 'redir');
		
		if(!empty($redir))
		{
			// Внешние ссылки
			if($redir == 'r')
			{
				$url = _rq('r');
				$k = strtoupper('redir.'.$url);				
				$url = $this->m_changes[$k];				
				
				if(!empty($url))
				{
					header('Location: '.$url);
					exit();
				}
				
				$status = 404;
			}
			else
				$status = 301;
		}
		
		$redir_url = '';
		
		if($this->m_status)
		{
			if(is_numeric($this->m_status))
				$status = $this->m_status;
			else if(is_array($this->m_status))
			{
				$status = _av($this->m_status, 'status');
				$redir_url = _av($this->m_status, 'ref');
				$this->prepare_refs($redir_url);
				//ech($redir_url);
			}
		}
		
		if(empty($status))
			return;

		$http = $_SERVER['SERVER_PROTOCOL'];

		if($status == 404)
		{
			header($http.' 404 Not Found');
			header('Status: 404 Not Found');
		}
		else if($status == 301)
		{
			$url = $redir_url ? $redir_url : $this->redir_url();
			header($http.' 301 Moved Permanently');
			header('Location: '.$url);
			exit();
		}
		else if($status == 281)
		{
			$path = _av($this->m_ref, 'dst_path'); 
			$name = empty($path) ? 'file.txt' : basename($path);
			
			header('Content-Type: '._mime($name));
			header('Content-Disposition: attachment; filename="'.$name.'"');
			header('Content-Transfer-Encoding: binary');
		}
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Вывести ответ на запрос
	*
	*	@param	string - $code_in
	*
	*	@return	ничего
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function out(&$code_in)
	{
		$this->header();
		echo($code_in);
	}
}

?>