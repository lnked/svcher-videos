<?php declare(strict_types=1);

class Api
{
    public $data = [];

    private $post = [];
    private $action = '';
    private $status = false;

    public function __construct()
    {
        $this->post = $_POST;
        $this->action = $this->prepare($_GET['_d']);
    }

    public function sendMessage()
    {
        $errors = [];

        $required = [
            'name',
            'phone',
            'email'
        ];

        foreach ($required as $name)
        {
            if (empty($this->post[$name]))
            {
                $errors[$name] = true;
            }
        }

        if (!empty($errors))
        {
            $this->status = false;

            $this->data = [
                'errors' => $errors
            ];

            return;
        }

        $subject = "Новое сообщение на сайте";

        $body  = '';

        $body .= '<p>Здравствуйте,</p>';
        $body .= '<p>Новое сообщение, на сайте ' . $domen . '</p>';
        $body .= '<p>--------------------</p>';

        if (!empty($this->post['name']))
        {
            $body .= '<p>Контактное лицо: <strong>'. $this->post['name'] .'</strong></p>';
        }

        if (!empty($this->post['phone']))
        {
            $body .= '<p>Телефон: <strong>'. $this->post['phone'] .'</strong></p>';
        }

        if (!empty($this->post['email']))
        {
            $body .= '<p>E-mail: <strong>'. $this->post['email'] .'</strong></p>';
        }

        $body .= '<p>--------------------</p>';
        $body .= '<p>Дата отправки: '. date('d.m.Y H:i:s') .'</p>';

        $body .= '<p>С уважением,<br>Администрация мероприятия</p>';

        $emails = [
            'ed.proff@gmail.com' => 'ED CELEBRO'
        ];

        // // Create the message
        // $message = (new Swift_Message())

        // // Give the message a subject
        // ->setSubject('Your subject')

        // // Set the From address with an associative array
        // ->setFrom(['john@doe.com' => 'John Doe'])

        // // Set the To addresses with an associative array (setTo/setCc/setBcc)
        // ->setTo(['receiver@domain.org', 'other@domain.org' => 'A name'])

        // // Give it a body
        // ->setBody('Here is the message itself')

        // // And optionally an alternative body
        // ->addPart('<q>Here is the message itself</q>', 'text/html')

        // // Optionally add any attachments
        // ->attach(Swift_Attachment::fromPath('my-document.pdf'))
        // ;

        // $message->attach(
        //     Swift_Attachment::fromPath('/files/2018-03-28_15-42-10/video_1080p_2018_03_29_16_10_14.mp4')->setFilename('video.mp4')
        // );

        // if ($mailer->send($message)) {
        //     $this->status = true;
        // } else {
        //     $this->status = false;
        // }

        // $body = '<p>С уважением,<br>Администрация мероприятия</p>';

        // $emails = [
        //     'svcher@bk.ru' => 'Sergey Cher',
        //     'ed.proff@gmail.com' => 'ED CELEBRO',
        // ];

        // // Create the Transport
        // $transport = (new Swift_SmtpTransport('smtp.mail.ru', 465, 'ssl'))
        //     ->setUsername('timefreeze23@softkor.ru')
        //     ->setPassword('153426rhfy')
        // ;

        // // Create the Mailer using your created Transport
        // $mailer = new Swift_Mailer($transport);

        // // Create a message
        // $message = (new Swift_Message('subject'))
        //     ->setFrom(['timefreeze23@softkor.ru' => 'Софткор'])
        //     ->setTo($emails)
        //     ->setBody($body, 'text/html')
        // ;

        // $message->attach(
        //     Swift_Attachment::fromPath(PATH_ROOT.DS.'files/2018-03-28_15-42-10/video_1080p_2018_03_29_16_10_14.mp4')->setFilename('video.mp4')
        // );

        // $mailer->send($message);
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

        echo json_encode($this->data, 64 | 256);
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
