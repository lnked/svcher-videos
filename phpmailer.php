
// $mail = new PHPMailer;

// $mail->setLanguage("ru");

// $mail->SMTPDebug = false;

// $mail->isSMTP();

// $mail->Host = $this->settings['send_server'];

// $mail->SMTPAuth = true;

// $mail->Username = $this->settings['send_email'];
// $mail->Password = $this->settings['send_password'];

// $mail->SMTPSecure = "ssl";
// $mail->Port = $this->settings['send_port'];

// $mail->From = $this->settings['send_email'];
// $mail->FromName = $this->settings['send_name'];

// $mail->addAddress($user_email, $user_name);

// $mail->isHTML(true);

// $mail->addAttachment(PATH_ROOT.$selected->video, "video.mp4");
// // $mail->addAttachment(PATH_ROOT.$selected->poster, "poster.jpg");

// $mail->Subject = $this->settings['send_subject'];
// $mail->Body = $this->renderView($data);

// if(!$mail->send()) {
//     echo "Mailer Error: " . $mail->ErrorInfo;
// } else {
//     echo "Message has been sent successfully";
// }

// exit(__($this->post, $selected));