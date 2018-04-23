<?

define('fm_id_path', 'path');
define('fm_id_filter', 'filter');

class c_fm
{
	function c_fm()
	{		
	}
	
	function main()
	{
		$path = _rq(fm_id_path);
		$filter = _rq(fm_id_filter);
		
		return $this->tbl($path, $filter);
	}

	function disks()
	{
		$ar = array();
		$s = ':/';
		
		for($i = 'a'; $i <= 'z'; $i ++)
			if(@scandir($i.$s))
				$ar[] = $i.$s;

		return $ar;
	}

	function prep_dir($path_in)
	{
		if(strlen($path_in) <= 1)
			return '';

		$res = str_replace('\\', '/', is_file($path_in) ? dirname($path_in) : $path_in);
		
		while(strpos($res, '/') !== false && !file_exists($res))
			$res = dirname($res);
		
		return $res;
	}

	function tbl($path_in, $filter_in = 0)
	{
		$res = '';
		
		$mode = (is_numeric($filter_in) && $filter_in > 0) ? $filter_in : 7;
		$mode |= 4;
		
		$dir = $this->prep_dir($path_in);
		$def = htmlspecialchars($path_in);
		if(strlen($dir) > 1)
		{
			$arr = _dir_tree($dir, $mode);
			array_unshift($arr, '..');
		}
		else
			$arr = $this->disks();
		
		$fm_unit = 'fm-unit';
		$fm_select = 'fm-select';
		
		//print_r($arr);
		
		foreach($arr as $cur)
		{
			$path = $cur;
			$class = $fm_unit;
			
			if($cur == '..')
				$path = _dir($dir, -1);
			//else if($cur == '.')
			//	$class = _implode_not_empty(array($class, $fm_select, 'hide'));
			
			$path = htmlspecialchars($path);

			$cur = wrap_tag($cur, 'div', array('fm_path'=>$path, 'class'=>$class, 'onclick'=>'$(\'.fm-select\').removeClass(\'fm-select\'); $(this).addClass(\'fm-select\');', 'ondblclick'=>'$(this).closest(\'form\').find(\'input[name='.fm_id_path.']\').val(\''.$path.'\'); $(this).closest(\'form\').submit();'));
			$res .= wrap_tag($cur, array('tr', 'td'));
		}
		
		if(!empty($res))
			$res = wrap_tag($res, 'table');

		$ext = '<input name="'._id.'" type="hidden" value="'._rq(_id).'" />';
		$ext .= '<input name="rt" type="hidden" value="1" />';
		$ext .= '<input name="'.fm_id_path.'" type="hidden" value="'.$def.'" />';
		if(!empty($filter_in))
			$ext .= '<input name="'.fm_id_filter.'" type="hidden" value="'.$filter_in.'" />';
		
		$res = wrap_tag($ext.$res, 'form', array('method'=>'post', 'enctype'=>'application/x-www-form-urlencoded'));
		$res = wrap_tag($res, 'div', 'fm');	
		$res .= wrap_tag('prep_ajax();', 'script');
		
		//$res .= "!!! $path_in, $filter_in, $mode";
		//ech($res);
		//$res = 1234;
		
		return $res;
	}
}

function _fm()
{
	$fm = new c_fm();
	return $fm->main();
}

?>