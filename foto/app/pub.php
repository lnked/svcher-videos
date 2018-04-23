<?

include_once('../app/set/sys.php');
include_once('../app/set/set.php');
include_once('../core/com.php');
include_once('../core/com.ext.php');
include_once('../core/page.php');
include_once('../app/ext.php');

class c_pub extends c_page
{
	function __construct()
	{
		parent::__construct();
		
		$this->init();
	}
	
	function init()
	{
		$this->init_changes(1);
		$this->add_style('css/com.css');
		$this->add_script('js/jquery.js;js/main.js');
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
		$res = null;
		$key = strtolower($key_in);
		$mode = 0;
		
		//ech($key_in);
		
		switch($key)
		{
			case 'pm.id':
			case 'page.id':
				$res = g_pm_id;
				break;
				
			case 'pm.val':
			case 'page.val':
				$res = _rq(g_pm_id);
				break;
				
			case 'pm.act':
				$res = _act;
				break;
				
			case 'pm.step':
				$res = _step;
				break;
				
			/*
			case 'pm.act.val':
				$res = _rq(_act);
				break;
				
			case 'pm.step.val':
				$res = _rq(_step);
				break;
			*/
			
			case 'include':
				$res = $this->file_contents($param_in);
				break;
				
			default:
				$res = method_exists($this, $key) ? $this->$key($param_in) : (function_exists($key) ? $key($param_in) : null);
				break;
		}
		
		return $res;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	//===========================================================================
	/**
	*	Установть замены
	*
	*	@param	number $step_in - номер шага установки
	*
	*	@return	ничего
	*/
	//===========================================================================
	//////////////////////////////////////////////////////////////////////////////
	
	function init_changes($step_in = 1)
	{
		if($step_in <= 1)
		{
			// Антиспам
			$asc = _get_sess_val('asc');
			if(empty($asc))
			{
				$asc = uniqid(rand(), true);
				_set_sess_val('asc', $asc);
			}
			
			$main_name = $this->val('g_app_name');
			$alt = empty($main_name) ? '' : ' alt="'.($main_name.' - small').'"';
			$img = './images/app/app.png';
			$img_sm = './images/app/app.sm.png';

			$arr = array(
			
				'main.title' 		=> $this->val('g_app_title'),
				'main.keywords' 	=> '',
				'main.description'	=> '',
				'main.style' 		=> '',
				'main.script' 		=> '',
				'main.copyright' 	=> $this->val('g_app_copyright'),
				'main.copyright.year' => $this->val('g_app_copyright_year'),
				'main.tagline' 		=> $this->val('g_app_tagline'),
				'main.name' 		=> $main_name,				
				'main.logo.img' 	=> $img,
				'main.logo.img.sm' 	=> $img_sm,
				'main.logo' 		=> 1 ? '<img class="logo-img" src="<!---MAIN.LOGO.IMG--->"'.$alt.'" />' : $this->val('g_app_name'),
				'main.logo.sm' 		=> g_app_logo_img_sm === true ? '<img class="logo-sm" src="<!---MAIN.LOGO.IMG.SM--->"'.$alt.' />' : '',
				'main.counter' 		=> $this->file_contents('ext/counter.txt'),
				'domain'			=> $this->get_domain(),				
				'tel'				=> g_tel,
				'tel_2'				=> g_tel_2,
				'fax'				=> g_fax,
				'email'				=> g_email,
				///'main.search'		=> $this->srch_form(),
				'asc'				=> $asc,
				
				'com.name'				=> g_com_name,
				'com.address'			=> g_com_address,
				'com.gps.center'		=> g_com_gps_center,
				'com.gps.mark'			=> g_com_gps_mark,
				
				//'root.id'				=> _ref(array(_id=>1)),
				//'root.id'				=> _ref(array(_id=>$this->get_ref_param('log', '*', 'id'))),
				
			);
			
			///$this->m_pub_changes = $this->load_changes(g_path_changes);			
			///$arr = array_merge($arr, $this->m_pub_changes);
		}
		else
		{
			$arr = array(
			
				//'menu.main'	=> $this->menu('app/menu.main.htm'),
				'menu.main'	=> $this->menu('app/menu.main.txt'),
				'menu.top'	=> $this->menu('app/menu.top.txt'),
				'root.id'	=> _ref(array(_id=>$this->get_ref_param('log', '*', 'id')))
			);
			
			//$t = $this->get_ref_param('log', '*', 'id');
		}
			
			//print_r($this->m_refs);
			//$t = $this->get_ref_param_2('log', '*', 'id');
			//print_r($this->m_ref);
			//ech($t);
		
		$this->add($arr);
	}
	
	function main()
	{
		// MAIN -> MAIN.CONTENT -> CONTENT
		
		// Обработка запросов для отображения частей страницы 
		// Используется приставка: part_
		$res = $this->part();
		if($res !== false)
		{
			$at = array();
			$at['code'] = $res;
			$code = json_encode($at);
			$this->out($code);
			return;
		}
		
		//$this->route();
		
		$code = '<!---MAIN--->';
		$main = $this->file_contents('main.htm');
		
		// Получить массив данных текущей страницы
		$ar = $this->content();
		
		//$this->add('menu.main', $this->menu('app/menu.main.txt'));
		$this->init_changes(2);

		///$this->process();

		// В плагинах страница может отсутствовать
		if($this->m_status == 404)
			$ar = $this->content(404);
		
		// Первоначальная установка меток для надстройки ext
		if(function_exists('_init'))
			_init();

		$content = _av($ar, 'code');
		//$this->init_changes(2);
		// Внимание! Был конфликт с добавлением $content позже
		//$this->proc_marks($content);	// обработать метки		
		///$this->int_links($content);		// добавить внутренние ссылки
		
		$status = _av($this->m_ref, 'status');	// статус страницы		
		// Добавление контента в страницу main.htm
		$rt = intval(_rq('rt'));
		//$b_main_page = $status != 280 && !$rt;
		$b_main_page = !($status >= 280 && $status < 300) && !$rt;

		$this->add('main', $b_main_page ? $main : '<!---MAIN.CONTENT--->');
		$this->add('main.content', $rt == 2 ? '<!---CONTENT--->' : $content);
		$this->add('main.inline.styles', $ar['stl']);
		$this->add('main.styles', $this->gen_tag('styles', null, 0, 0));
		$this->add('main.scripts', $this->gen_tag('scripts', null, 0, 0));		
		///$this->add('main.log', $this->page_log_path());

		// Замены на основе регулярных выражений. 
		// Дополнения для перелинковки
		///if($b_main_page)
		///	$this->prepare_content();
		
		//$this->init_changes(2);
		
		$this->changes($code);
		$this->prepare_refs($code);
		$this->clear_marks($code);		// убрать все оставшиеся метки
		
		// ajax
		if($rt)
		{
			$at = array();
			
			$at[$rt == 2 ? 'msg' : 'code'] = $code;			
			
			$stl = $ar['stl'];			
			if(!empty($stl))
				$at['stl'] = $stl;

			$code = json_encode($at);
		}
		
		$this->out($code);
	}
	
	function part()
	{
		$res = false;
		$part = _rq(_part);
		
		if(empty($part))
			return $res;
		
		$fun = 'part_'.$part;
		if(function_exists($fun))
			$res = $fun();
		
		return $res;
		
	}
}

$obj = new c_pub;
$obj->main();

?>