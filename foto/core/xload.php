<?

/*
include_once("settings/fin.set");
define("g_path_book", home_dir."docs/download2/");
define("g_path_rel_tmp", "tmp/");
define("g_path_tmp", home_dir."docs/".g_path_rel_tmp);
define("g_file_finec", "FinecInstall.exe");
define("g_path_finec", home_dir."docs/download/".g_file_finec);
define("g_file_read_me", "read_me.txt");
define("g_path_read_me", home_dir."docs/download/".g_file_read_me);
define("g_file_finec_online", "FinecOnline.htm");
define("g_path_finec_online", home_dir."docs/download/".g_file_finec_online);
define("g_time_delete_old", 600);
define('g_download_id', 'id');
*/

define('g_download_debug', true);

define('g_download_period', 600);
define('g_download_id', 'id');
define('g_download_src', g_path_app.'plugins/@files/books/');
define('g_download_dst', g_path_web_tmp);
define('g_download_url', g_url_web_tmp);
define('g_download_ext', g_path_docs.'download/FinecInstall.exe;'.g_path_docs.'download/read_me.txt;'.g_path_docs.'download/FinecOnline.htm');

class c_download
{
	function main($mode_in = 0)
	{
		$id = _rq(g_download_id);
		
		if(empty($id))
		{
			echo('Error 1');
			exit;
		}
		
		$this->init();
		self::delete_old();
		
		if(!$this->create_archive($id))
		{
			echo('Error 2');
			exit;
		}
		
		$url 	= self::get_path($id, 'url');
		$dst	= self::get_path($id, 'dst');
		
		if(file_exists($dst))
			$this->redir($url);
		else
		{
			echo('Error 3');
			exit;
		}
	}
		
	function redir($path_in)
	{
		$domain = self::get_domain();
		header('Location: http://'.$domain.'/'.$path_in);
		exit;
	}
	
	function delete_old()
	{
		$dir = $this->m_dst;
		
		if(strlen($dir) && is_dir($dir)) 
		{
			if($dh = opendir($dir)) 
			{
				$cur = time();
				$dif = max(60, g_download_period);
				
				while(($file = readdir($dh)) !== false) 
				{
					$path = _path($dir, $file);
					
					if(is_file($path) && file_exists($path) && ($cur - filemtime($path)) > $dif && strpos($path, g_dir_web_tmp) > 0)
						unlink($path);
				}
				
				closedir($dh);
			}
		}
	}
	
	function create_archive($id_in)
	{
		$res = false;
		
		$src 	= self::get_path($id_in, 'src');
		$dest 	= self::get_path($id_in, 'dst');
		$ext 	= self::get_path('', 'ext');
		
		if(file_exists($dest))
			return true;
			
		if(!copy($src, $dest))
			return $res;
			
		$zip = new ZipArchive;
		
		if($zip->open($dest) === TRUE) 
		{
			foreach($ext as $cur)
				$zip->addFile($cur, basename($cur));

			$zip->close();
			$res = true;
		}
		
		return $res;
	}
	
	function init()
	{
		$this->m_url = g_download_url;
		$this->m_dst = g_download_dst;
		$this->m_src = g_download_src;		
	}
	
	function file_path($id_in)
	{
		$pa = explode(';', $this->m_src);
		
		foreach($ap as $dir)
		{
			$path = _path($dir, $id_in);
			if(file_exists($path))
				return $path;
		}
		
		return null;
	}
	
	function get_path($id_in, $mode_in)
	{
		$path = '';
		
		if($mode_in == 'dst')
			$path = $this->m_dst;
		else if($mode_in == 'src')
			$path = $this->m_src;
		else if($mode_in == 'url')
			$path = $this->m_url;
		else if($mode_in == 'ext')
		{
			$ar = array();
			$ap = explode(';', g_download_ext);
			foreach($ap as $cur)
				if(file_exists($cur))
					$ar[] = $cur;

			return $ar;
		}

		return _path($path, $id_in);
	}
	
	//////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Получить имя домена для переадресации
	*
	*	@param	string $key_in - ключ для переадресации
	*
	*	@return	string - имя домена
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////

	static function get_domain($key_in = '')
	{
		$res = $_SERVER['SERVER_NAME'];
		$res = trim($res, '\\/ ');
		
		if($res == 'localhost')
		{
			if(g_download_debug === true)
				$res = _path($res, _get_dir($_SERVER['SCRIPT_NAME'], -2));
		}

		/*
		$arr = explode('.', $res);
		while(count($arr) > 2)
			array_shift($arr);
		$res = implode('.', $arr);
		
		$res = trim($res, '\\/');
		*/

		return $res;
	}
}

?>