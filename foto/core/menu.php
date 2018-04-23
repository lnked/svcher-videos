<?

class c_menu
{
	function __construct()
	{
	}
	
	//////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Сгенерировать меню
	*
	*	@param	string $path_in - путь к файлу меню
	*	@param	string $mode_in - мода генерации меню
	*
	*	@return	string - код страницы
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function gen($path_in, $mode_in = "table", $active_id_in = "")
	{
		$res = "";
		$mode = $mode_in;
		
		$code = file_get_contents($path_in);		
		$arr_menus = array();
		$arr_parts = explode("****", $code);
		
		foreach ($arr_parts as $part)
		{
			$arr_part = explode("\n", trim($part));
			$arr_menu = array();
			
			foreach ($arr_part as $row)
			{
				// Пропустить пустые строки и комментарии
				$row = trim($row);
				if(empty($row) || strpos($row, "@") === 0)
					continue;
				
				$arr_row = explode("\t", trim($row));
				array_push($arr_menu, $arr_row);
			}
			
			array_push($arr_menus, $arr_menu);
		}
		
		foreach ($arr_menus as $arr_menu)
		{
			$menu 			= "";
			$menu_colunm 	= "";
			$main_name 		= "";
			
			$num = 1;
			foreach ($arr_menu as $arr_row)
			{
				$arr_tmp = array();				
				foreach ($arr_row as $str)
				{
					$ae = explode("=>", $str);
					
					if(count($ae) == 2)
						$arr_tmp[$ae[0]] = $ae[1];
				}
				
				$name	= @$arr_tmp["name"];
				$ref	= @$arr_tmp["ref"];
				$title	= @$arr_tmp["title"];
				$class	= @$arr_tmp["class"];
				$type	= @$arr_tmp["type"];
				
				if(
					empty($active_id_in) && $num == 1
					|| !empty($active_id_in) && preg_match("/\.$active_id_in-/", $ref)
					)
				{
					$class .= " active";
					$class = ltrim($class);
				}
				
				$num ++;
				
				if(!empty($class))
					$class = " class=\"$class\"";
				
				if($type == "unit")
				{
					if(!empty($main_name))
					{
						$menu_colunm .= "<ul>$main_name$menu</ul>\r\n";
						$menu = "";
					}
					
					$main_name = "<li$class>$name</li>\r\n";
					
					continue;
				}
				
				if(!empty($ref))
					$name = "<a href=\"$ref\" title=\"$title\">$name</a>";

				$menu .= "<li$class>$name</li>\r\n";
			}
			
			$tag = "";
			if($mode == "table")
				$tag = "td";
			
			$menu_colunm .= "<ul>$main_name$menu</ul>\r\n";
			
			if(!empty($tag))
				$res .= "<$tag>$menu_colunm</$tag>\r\n";
			else
				$res .= "$menu_colunm\r\n";
		}
		
		if($mode == "table")
			$res = "<table><tr>$res</tr></table>\r\n";
		
		
		return $res;
	}

}

?>