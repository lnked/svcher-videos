<?php declare(strict_types = 1);

class App
{
    public $page = '';
    public $path = array();
    public $data = array();
    public $params = array();
    public $userData = array();
    public $statistics = array();

    protected $url = null;
    protected $query = null;
    protected $domain = null;
    protected $request = null;
    protected $backuri = null;
    protected $is_admin = false;

	public function __construct()
	{
        $this->domain = $_SERVER['HTTP_HOST'];
        $this->query = $_SERVER['QUERY_STRING'];
        $this->request = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $this->backuri = base64_encode($this->request);
        $this->url = current(explode('?', $this->request));
        $this->path = preg_split('/\/+/', $this->url, -1, PREG_SPLIT_NO_EMPTY);

        if (isset($this->path[0]) && $this->path[0] == 'admin') {
        	$this->is_admin = true;

        	if (isset($this->path[1])) {
        		$this->page = $this->path[1];
        	}
        }

        if ($this->is_admin && count($_POST)) {
        	if (isset($_POST['action']) && $_POST['action'] === 'auth-form') {
        		$this->authForm($_POST);
        	}
        	elseif (isset($_POST['action']) && $_POST['action'] === 'extract-data') {
        		$this->extractData($_POST);
        	} else {
        		$this->updateData($_POST, $_FILES);
        	}
        }

        if ($this->is_admin && isset($this->path[1]) && $this->path[1] === 'logout')
        {
        	unset($_SESSION['userData']);
        	redirect('/', 301);
        }

        if (!empty($_SESSION['userData'])) {
    		$this->userData = $_SESSION['userData'];

    		if ($this->is_admin && !isset($this->path[1])) {
    			redirect('/admin/settings', 301);
        	}
    	}
	}

	private function extractData($data = array())
	{
		if (!empty($data['change']))
		{
			$dataArray = Q("SELECT `name`, `email`, `phone`, `session` FROM `statistics` WHERE `id` IN (?li)", [
				$data['change']
			])->all();

			array_unshift($dataArray, array(
				'name' => 'Имя',
				'email' => 'Электронная почта',
				'phone' => 'Номер телефона',
				'session' => 'Сессия',
			));

			// create php excel object
			$doc = new PHPExcel();

			// set active sheet
			$doc->setActiveSheetIndex(0);

			// read data to active sheet
			$doc->getActiveSheet()->fromArray($dataArray);

			//save our workbook as this file name
			$filename = sprintf('Выгрузка от %s.xls', date('d.m.Y H:i'));

			//mime type
			header('Content-Type: application/vnd.ms-excel');
			//tell browser what's the file name
			header('Content-Disposition: attachment;filename="' . $filename . '"');

			header('Cache-Control: max-age=0'); //no cache
			//save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
			//if you want to save it as .XLSX Excel 2007 format

			$objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');

			//force user to download the Excel file without writing it to server's HD
			$objWriter->save('php://output');
		}
	}

	private function authForm($data = array())
	{
		if (isset($data['login']) && $data['password']) {
			$user = Q("SELECT `l`.`value` as `login`, `p`.`value` as `password` FROM `settings` as `l`
				LEFT JOIN `settings` as `p` ON (`p`.`system`='password' AND `p`.`value` LIKE '153426rhfy')
				WHERE (`l`.`system`='login' AND `l`.`value` LIKE 'admin')"
			)->row();

			if (!empty($user['login']) && !empty($user['password'])) {
				$_SESSION['userData'] = $user;
			}
		}

		redirect('/admin', 301);
	}

	private function loadData()
	{
		if ($this->page == 'statistic') {
			$this->statistics = Q("SELECT * FROM `statistics`")->all();
		}

		$sample = array(
			'event_name',
			'logo',
			'mode',
			'login',
			'password',
			'time',
			'send_button',
			'send_email',
			'send_password',
			'send_server',
			'send_port',
			'send_name',
			'send_subject',
			'send_signature',
			'send_photo_number',
			'style_body_bg',
			'style_body_text',
			'style_sidebar_bg',
			'style_sidebar_text',
			'style_button_bg',
			'style_button_text',
		);

		$data = array();

		$result = Q("SELECT * FROM `settings`")->all();

		if (!empty($result)) {
			foreach ($result as $item) {
				$data[$item['system']] = $item['value'];
			}
		}

		$pure = array_fill_keys(array_values($sample), '');

		$data = array_merge($data, array_diff_key($pure, $data));

		$this->params = $data;
	}

	private function updateData($post = array(), $files = array())
	{
		if (!empty($files)) {
			$dir = PATH_ROOT.DS.'cache';

			$ext = pathinfo($files['logo']['name'], PATHINFO_EXTENSION);

			$filename = sprintf('/cache/logo.%s', $ext);
			$uploadFile = sprintf('%s/logo.%s', $dir, $ext);

			if (move_uploaded_file($files['logo']['tmp_name'], $uploadFile)) {
				$post['logo'] = $filename;
			}
		}

		if (!empty($post)) {
			foreach ($post as $name => $value) {
				$count = Q("SELECT COUNT(`id`) as `count` FROM `settings` WHERE `system` LIKE ?s LIMIT 1", array(
					$name
				))->row('count');

				if ($count) {
					Q("UPDATE `settings` SET `value`=?s WHERE `system` LIKE ?s LIMIT 1", array(
						$value, $name
					));
				} else {
					Q("INSERT INTO `settings` SET `system`=?s, `value`=?s", array(
						$name, $value
					));
				}

				if ($name == 'pathname' && is_dir($value)) {
					symlink($value, PATH_ROOT.DS.'files');
				}
			}
		}

		redirect('/admin', 301);
	}

	private function render()
	{
		$template = $this->is_admin ? 'admin' : 'base';

		require PATH_TEMPLATE.DS.$template.'.phtml';
	}

	public function terminate()
	{
		// $api = new Api();
		// $api->sendMessage();
		// exit;

		$this->loadData();
		$this->render();
	}
}