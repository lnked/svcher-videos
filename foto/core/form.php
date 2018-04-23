<?

class c_form
{
	protected $m_struct;
	protected $m_data;
	
	function __construct($struct_in = null, $data_in = null)
	{
		$this->init($struct_in);
		$this->set($data_in);
	}
	
	function init($struct_in)
	{
		$this->m_struct = array();
		
		if(!is_array($struct_in))
			return;
		
		foreach($struct_in as $ac)
		{
			$name = $ac['name'];
			$this->m_struct[$name] = $ac;
		}
	}
	
	function set($data_in)
	{
		$this->m_data = array();
		
		if(!is_array($data_in))
			return;
		
		foreach($data_in as $k=>$v)
			$this->m_data[$k] = $v;
	}
	
	function test()
	{
		$res = false;
		
		foreach($this->m_data as $k=>$v)
		{
			if(!array_key_exists($k, $this->m_struct))
				continue;
			
			$as = $this->m_struct[$k];
			
			$title = wrap_tag($as['title'], 'span', 'b');
			$type = $as['type'];
			$min = intval($as['min']);
			$max = intval($as['max']);
			$len = mb_strlen($v);
			
			if(empty($type) || in_array($type, array('string', 'str', 'text')))
			{
				if($max > 0 && $len > $max)
					$res = sprintf('Длина поля %s должна быть не больше %d символов', $title, $max);
				else if($min >= 0)
				{
					if($min > 0 && !$len)
						$res = sprintf('Нужно указать значение поля %s', $title);
					else if($len < $min)
						$res = sprintf('Длина поля %s должна быть не меньше %d символов', $title, $min);
				}
			}
			else if(in_array($type, array('number', 'num')))
			{
				if($max > 0 && $v > $max)
					$res = sprintf('Значение поля %s должна быть не больше %d', $title, $max);
				else if($min >= 0)
				{
					if(!$len)
						$res = sprintf('Нужно указать значение поля %s', $title);
					else if($v < $min)
						$res = sprintf('Значение поля %s должна быть не меньше %d', $title, $min);
				}
			}			
			else if(in_array($type, array('email')))
			{
				if(!preg_match('/[a-z0-9_\.-]{1,}@[a-z0-9-\.]{2,}\.[a-z]{2,}/i', $v))
					$res = sprintf('Неправильно указано значение поля %s', $title);
			}
			
			if($res)
				return $res;
		}
		
		return true;
	}
	
	function unit($name_in)
	{
		$res = '';
		$as = $this->m_struct[$name_in];
		
		if(is_array($as))
		{
			$title = $as['title'];
			$name = $as['name'];
			$class = _av($as, 'class', empty($name) ? '' : 'c-'.$name);
			$type = _av($as, 'type', 'text');
			$tag = _av($as, 'tag', 'text');
			$min = intval($as['min']);
			$max = intval($as['max']);
			$val = _av($this->m_data, $name);
			
			$str_name = empty($name) ? '' : ' name="'.$name.'"';
			$str_class = empty($class) ? '' : ' class="'.$class.'"';
			$str_max = $max <= 0 ? '' : ' maxlength="'.$max.'"';
			$str_val = ' value="'.$val.'"';
			
			if($tag == 'text')
				$res = '<input type="text"'.$str_name.$str_class.$str_max.$str_val.' />';
			else if(in_array($tag, array('hidden', 'password')))
				$res = '<input type="'.$tag.'"'.$str_name.$str_val.' />';
			else if(in_array($tag, array('select')))
			{
				$list = _av($as, 'list', array());
				$buf = '';
				foreach($list as $k=>$v)
				{
					$at = array('value'=>$k);
					if($k == $val)
						$at['selected'] = null;
					
					$buf .= wrap_tag($v, 'option', $at);
				}
					
				if(!empty($buf))
					$res = wrap_tag($buf, 'select', array('name'=>$name, 'class'=>$class));
			}
		}
		
		return $res;
	}
	
	function html($step_in = 0)
	{
		$res = '';
		
		foreach($this->m_struct as $as)
		{
			$row = $this->unit($as['name']);			
			$res .= wrap_tag(wrap_tag($title, 'td', 'c-title').wrap_tag($row, 'td', 'c-val'), 'tr')."\r\n";
		}
		
		if(!empty($res))
			$res = wrap_tag($res, 'table');
	
		return $res;
	}
	
	function params($name_in)
	{
		$res = array();
		
		foreach($this->m_struct as $as)
			$res[] = $as[$name_in];
			
		return $res;
	}
	
	function get($name_in)
	{
		return $this->m_struct[$name_in];
	}
	
}

?>