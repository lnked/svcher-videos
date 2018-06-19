<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Api
{
    public $data = array();

    private $post = array();
    private $action = '';
    private $element = null;
    private $status = false;
    private $settings = array();

    public function __construct()
    {
        $this->post = $_POST;

        if (isset($_GET['_d'])) {
            $path = preg_split('/\/+/', $_GET['_d'], -1, PREG_SPLIT_NO_EMPTY);

            if (isset($path[1])) {
                $this->element = $path[1];
            }

            $this->action = $this->prepare($path[0]);
        }
    }

    public function getData()
    {
        $this->_settings();

        $this->data = array();
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

    public function removeStatistic()
    {
        if ($this->element && $this->element !== null) {
            Q("DELETE FROM `statistics` WHERE `id`=?i LIMIT 1", array(
                $this->element
            ));
        }
    }

    private function renderView($data = array())
    {
        extract($data);

        ob_start();

        require PATH_TEMPLATE.DS.'message.phtml';

        return ob_get_clean();
    }

    private function addStatistics($data)
    {
        Q("INSERT INTO `statistics` SET `datetime`=?i, `session`=?s, `name`=?s, `email`=?s, `phone`=?s", array(
            $data['datetime'],
            $data['session'],
            $data['name'],
            $data['email'],
            $data['phone'],
        ));
    }

    public function sendMessage()
    {
        $this->_settings();

        $errors = array();

        $required = array(
            'name',
            'phone',
            'email',
            'session'
        );

        // $this->post = array(
        //     'name' => 'ed',
        //     'phone' => '+7 988 666 77 66',
        //     'email' => 'ed.proff@gmail.com',
        //     'session' => 'session_2018_04_01_13_43_21',
        //     'photo' => true
        // );

        foreach ($required as $name)
        {
            if (empty($this->post[$name]))
            {
                $errors[$name] = true;
            }
        }

        if (!empty($this->post['email']) && !is_email($this->post['email'])) {
            $errors['email'] = true;
        } elseif (!checkThisEmail($this->post['email'])) {
            $errors['email'] = true;
        }

        if (!empty($errors))
        {
            $this->status = false;

            $this->data = array(
                'errors' => $errors
            );

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

        $item = array(
            'image' => $selected->poster,
            'video' => $selected->video
        );

        $data = array(
            'item' => $item,
            'settings' => $this->settings,
            'send_photo' => $send_photo
        );

        $emails[$user_email] = $user_name;

        // Create the Transport
        $transport = (new Swift_SmtpTransport($this->settings['send_server'], $this->settings['send_port'], 'ssl'))
            ->setUsername($this->settings['send_email'])
            ->setPassword($this->settings['send_password'])
        ;

        // Create the Mailer using your created Transport
        $mailer = new Swift_Mailer($transport);

        $message = (new Swift_Message())
            ->setSubject($this->settings['send_subject'])
            ->setFrom([$this->settings['send_email'] => $this->settings['send_name']])
            ->setTo($emails)
        ;

        // $attachment_video = \Swift_Attachment::fromPath(
        //     PATH_ROOT.$item['video'], 'video/mp4'
        // );

        // $attachment_image = \Swift_Attachment::fromPath(
        //     PATH_ROOT.$item['image'], 'image/jpg'
        // );

        // $message
        //     ->attach($attachment_video)
        //     ->attach($attachment_image)
        // ;

        $data['logo'] = $message->embed(Swift_Image::fromPath(PATH_ROOT.$this->settings['logo']));
        $data['video'] = $message->embed(Swift_Image::fromPath(PATH_ROOT.$item['video']));

        if ($send_photo) {
            $data['image'] = $message->embed(Swift_Image::fromPath(PATH_ROOT.$item['image']));
        }

        $message->setBody($this->renderView($data), 'text/html');

        if ($mailer->send($message, $failures)) {

            $this->addStatistics(array(
                'datetime'  => time(),
                'session'   => $session,
                'name'      => $user_name,
                'email'     => $user_email,
                'phone'     => $user_phone
            ));

            $this->status = true;

            $this->data = array(
                'title' => 'Сообщение отправлено',
                'message' => 'Видео отправлено на адрес: ' . $this->post['email']
            );
        } else {
            if (!empty($failures)) {
                $this->data = array(
                    'title' => 'Сообщение не отправлено',
                    'message' => 'Не верный адрес почты <span color="red">' . $failures[0] . '</span>'
                );
            }

            $this->status = false;
        }

        $mailer->getTransport()->stop();

        $this->response();
    }

    private function _settings()
    {
        $data = array();
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
