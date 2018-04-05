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

        $selected = _scannode(PATH_FILES, $session, $this->settings['mode']);

        $user_name = $this->post['name'];
        $user_email = $this->post['email'];
        $user_phone = $this->post['phone'];

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
            // ->attach(
            //     Swift_Attachment::fromPath(PATH_ROOT.$item['video'])->setFilename('video.mp4')
            // )
        ;

        $data['logo'] = $message->embed(Swift_Image::fromPath(PATH_ROOT.$this->settings['logo']));
        $data['image'] = $message->embed(Swift_Image::fromPath(PATH_ROOT.$item['image']));
        $data['video'] = $message->embed(Swift_Image::fromPath(PATH_ROOT.$item['video']));

        $html = $this->render($data);

        $message->setBody($html, 'text/html');

        if ($mailer->send($message)) {
            $this->status = true;

            $this->data = [
                'title' => 'Сообщение',
                'message' => 'Видео файл отправлен'
            ];
        } else {
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

// https://habrahabr.ru/post/351890/

// GET: этот метод является безопасным и идемпотентным. Обычно используется для извлечения информации и не имеет побочных эффектов.
// POST: этот метод не является ни безопасным, ни идемпотентным. Этот метод наиболее широко используется для создания ресурсов.
// PUT: этот метод является идемпотентным. Вот почему лучше использовать этот метод вместо POST для обновления ресурсов. Избегайте использования POST для обновления ресурсов.
// DELETE: как следует из названия, этот метод используется для удаления ресурсов. Но этот метод не является идемпотентным для всех запросов.
// OPTIONS: этот метод не используется для каких-либо манипуляций с ресурсами. Но он полезен, когда клиент не знает других методов, поддерживаемых для ресурса, и используя этот метод, клиент может получить различное представление ресурса.
// HEAD: этот метод используется для запроса ресурса c сервера. Он очень похож на метод GET, но HEAD должен отправлять запрос и получать ответ только в заголовке. Согласно спецификации HTTP, этот метод не должен использовать тело для запроса и ответа.

// 200 OK — это ответ на успешные GET, PUT, PATCH или DELETE. Этот код также используется для POST, который не приводит к созданию.
// 201 Created — этот код состояния является ответом на POST, который приводит к созданию.
// 204 Нет содержимого. Это ответ на успешный запрос, который не будет возвращать тело (например, запрос DELETE)
// 304 Not Modified — используйте этот код состояния, когда заголовки HTTP-кеширования находятся в работе
// 400 Bad Request — этот код состояния указывает, что запрос искажен, например, если тело не может быть проанализировано
// 401 Unauthorized — Если не указаны или недействительны данные аутентификации. Также полезно активировать всплывающее окно auth, если приложение используется из браузера
// 403 Forbidden — когда аутентификация прошла успешно, но аутентифицированный пользователь не имеет доступа к ресурсу
// 404 Not found — если запрашивается несуществующий ресурс
// 405 Method Not Allowed — когда запрашивается HTTP-метод, который не разрешен для аутентифицированного пользователя
// 410 Gone — этот код состояния указывает, что ресурс в этой конечной точке больше не доступен. Полезно в качестве защитного ответа для старых версий API
// 415 Unsupported Media Type. Если в качестве части запроса был указан неправильный тип содержимого
// 422 Unprocessable Entity — используется для проверки ошибок
// 429 Too Many Requests — когда запрос отклоняется из-за ограничения скорости
