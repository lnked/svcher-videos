<?php declare(strict_types = 1);

require 'init.php';

$app = new App;
$app->terminate($_SERVER);

// $body = '<p>С уважением,<br>Администрация мероприятия</p>';

// $emails = [
//     'ed.proff@gmail.com' => 'ED CELEBRO'
// ];

// // Create the Transport
// $transport = (new Swift_SmtpTransport('smtp.softkor.ru', 465, 'ssl'))
//     ->setUsername('time@softkor.ru')
//     ->setPassword('1234567890')
// ;

// // Create the Mailer using your created Transport
// $mailer = new Swift_Mailer($transport);

// // Create a message
// $message = (new Swift_Message('subject'))
//     ->setFrom(['time@softkor.ru' => 'Софткор'])
//     ->setTo($emails)
//     ->setBody($body, 'text/html')
// ;

// $message->attach(
// 	Swift_Attachment::fromPath('/files/2018-03-28_15-42-10/video_1080p_2018_03_29_16_10_14.mp4')->setFilename('video.mp4')
// );

// $mailer->send($message);