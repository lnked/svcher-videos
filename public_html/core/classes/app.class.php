<?php declare(strict_types = 1);

class App
{
    public $path = [];
    public $data = [];

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

        if (isset($this->path[0]) && $this->path[0] == 'admin')
        {
        	$this->is_admin = true;
        }
	}

	private function render()
	{
		// if ($this->page['id'] == 0) {
		//     header('HTTP/1.1 404 Not Found', true, 404);
		// }

		// $this->viewer->display($this->pattern);

		$template = $this->is_admin ? 'admin' : 'base';

		require PATH_TEMPLATE.DS.$template.'.phtml';
	}

	public function terminate()
	{
		// $target = 'uploads.php';
		// $link = 'uploads';
		// symlink(dirname(PATH_ROOT).DS.'input', PATH_ROOT.DS.'files');
		// echo readlink(PATH_ROOT.DS.'files');

		$this->data = _scandir(PATH_ROOT.DS.'files');

		$this->render();
	}
}