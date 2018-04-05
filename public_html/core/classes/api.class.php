<?php declare(strict_types=1);

class Api
{
    public $data = [];

    private $post = [];
    private $action = '';
    private $status = false;
    private $settings = [];

    public function __construct()
    {
        $this->post = $_POST;

        if (isset($_GET['_d'])) {
            $this->action = $this->prepare($_GET['_d']);
        }
    }

    public function getData()
    {
        $this->_settings();

        $this->data = [];
        $this->data['videos'] = _scandir(PATH_ROOT.DS.'files', $this->settings['mode']);
        $this->status = true;

        $this->response();
    }

    public function removeLogo()
    {
        $logo = Q("SELECT `value` FROM `settings` WHERE `system` LIKE ?s LIMIT 1", [
            'logo'
        ])->row('value');

        unlink(PATH_ROOT.$logo);

        Q("DELETE FROM `settings` WHERE `system` LIKE ?s LIMIT 1", [
            'logo'
        ]);

        $this->status = true;
    }

    private function render($data = [])
    {
        extract($data);

        ob_start();

        require PATH_TEMPLATE.DS.'message.phtml';

        return ob_get_clean();
    }

    private function addStatistics($data)
    {
        Q("INSERT INTO `statistics` SET `datetime`=?i, `session`=?s, `name`=?s, `email`=?s, `phone`=?s", [
            $data['datetime'],
            $data['session'],
            $data['name'],
            $data['email'],
            $data['phone'],
        ]);
    }

    public function sendMessage()
    {
        $this->_settings();

        $errors = [];

        $required = [
            'name',
            'phone',
            'email',
            'session'
        ];

        // $this->post = [
        //     'name' => 'ed',
        //     'phone' => '+7 988 666 77 66',
        //     'email' => 'qwdf@23e.er',
        //     'session' => 'session_2018_04_01_13_43_21',
        // ];

        foreach ($required as $name)
        {
            if (empty($this->post[$name]))
            {
                $errors[$name] = true;
            }
        }

        if (!empty($this->post['email']) && !is_email($this->post['email'])) {
            $errors['email'] = true;
        }

        if (!empty($errors))
        {
            $this->status = false;

            $this->data = [
                'errors' => $errors
            ];

            return;
        }

        $session = $this->post['session'];

        $selected = _scannode(PATH_FILES, $session, $this->settings['mode'], $this->settings['send_photo_number']);

        $send_photo = false;

        $user_name = $this->post['name'];
        $user_email = $this->post['email'];
        $user_phone = $this->post['phone'];

        if (isset($this->post['photo']) && $this->post['photo'] == 1) {
            $send_photo = true;
        }

        $this->addStatistics([
            'datetime'  => time(),
            'session'   => $session,
            'name'      => $user_name,
            'email'     => $user_email,
            'phone'     => $user_phone
        ]);

        $item = [
            'image' => $selected->poster,
            'video' => $selected->video
        ];

        $data = [
            'item' => $item,
            'settings' => $this->settings
        ];

        $emails[$user_email] = $user_name;

        try {
            // Create the Transport
            $transport = (new Swift_SmtpTransport($this->settings['send_server'], $this->settings['send_port'], 'ssl'))
                ->setUsername($this->settings['send_email'])
                ->setPassword($this->settings['send_password'])
            ;

            // Create the Mailer using your created Transport
            $mailer = new Swift_Mailer($transport);

            // Create a message
            $message = (new Swift_Message())
                ->setSubject($this->settings['send_subject'])
                ->setFrom([
                    $this->settings['send_email'] => $this->settings['send_name']
                ])
                ->setTo($emails)
            ;

            $data['logo'] = $message->embed(Swift_Image::fromPath(PATH_ROOT.$this->settings['logo']));
            $data['video'] = $message->embed(Swift_Image::fromPath(PATH_ROOT.$item['video']));

            $data['send_photo'] = $send_photo;

            if ($send_photo) {
                $data['image'] = $message->embed(Swift_Image::fromPath(PATH_ROOT.$item['image']));
            }

            $html = $this->render($data);

            $message->setBody($html, 'text/html');

            $request = $mailer->send($message, $failures);

            if ($request) {
                $this->status = true;

                $this->data = [
                    'title' => 'Сообщение отправлено',
                    'message' => 'Видео отправлено на адрес: ' . $this->post['email']
                ];
            } else {
                if (!empty($failures)) {
                    $this->data = [
                        'title' => 'Сообщение не отправлено',
                        'message' => 'Не верный адрес почты <span color="red">' . $failures[0] . '</span>'
                    ];
                }

                $this->status = false;
            }

            $mailer->getTransport()->stop();

        } catch (\Exception $e) {
            $this->status = false;
        }

        $this->response();
    }

    private function _settings()
    {
        $data = [];
        $result = Q("SELECT * FROM `settings`")->all();

        if (!empty($result)) {
            foreach ($result as $item) {
                $data[$item['system']] = $item['value'];
            }
        }

        $this->settings = $data;
    }

    public function prepare($action)
    {
        $action = mb_ucwords($action, " \t\r\n\f\v\-");
        $action = str_replace('-', '', $action);
        $action = lcfirst($action);

        return $action;
    }

    public function response()
    {
        header('Content-Type: application/json');

        $this->data['status'] = $this->status;

        exit(json_encode($this->data, 64 | 256));
    }

    public function handleRequest()
    {
        $this->{$this->action}();

        $this->response();
    }
}
