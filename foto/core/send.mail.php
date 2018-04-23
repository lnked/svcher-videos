<?php
/**
* SendMailSmtpClass
* 
* Класс для отправки писем через SMTP с авторизацией
* Может работать через SSL протокол
* Тестировалось на почтовых серверах yandex.ru, mail.ru и gmail.com
* 
* @author Ipatov Evgeniy <admin@ipatov-soft.ru>
* @version 1.0
*/
class SendMailSmtpClass {

    /**
    * 
    * @var string $smtp_username - логин
    * @var string $smtp_password - пароль
    * @var string $smtp_host - хост
    * @var string $smtp_from - от кого
    * @var integer $smtp_port - порт
    * @var string $smtp_charset - кодировка
    *
    */   
    public $smtp_username;
    public $smtp_password;
    public $smtp_host;
    public $smtp_from;
    public $smtp_port;
    public $smtp_charset;
	private $attach_part;
	private $boundary;
    
    public function __construct($smtp_username, $smtp_password, $smtp_host, $smtp_from, $smtp_port = 25, $smtp_charset = "utf-8") {
        $this->smtp_username = $smtp_username;
        $this->smtp_password = $smtp_password;
        $this->smtp_host = $smtp_host;
        $this->smtp_from = $smtp_from;
        $this->smtp_port = $smtp_port;
        $this->smtp_charset = $smtp_charset;
    }
    
    /**
    * Отправка письма
    * 
    * @param string $mailTo - получатель письма
    * @param string $subject - тема письма
    * @param string $message - тело письма
    * @param string $headers - заголовки письма
    *
    * @return bool|string В случаи отправки вернет true, иначе текст ошибки    *
    */
    function send($mailTo, $subject, $message, $arr_files = null) //, $headers) 
	//function send($mailTo, $subject, $message, $headers) 
	{
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=utf-8\r\n"; // кодировка письма
		$headers .= 'From: '.$this->smtp_from.' <'.$this->smtp_username.">\r\n"; // от кого письмо	
		
		//ech($headers);
		//ech($headers2);
		
		//return;
		
		$this->boundary = '—-'.md5(uniqid(time()));
		$this->attach($arr_files);
		if(!empty($this->attach_part))
			$headers .= "Content-Type: multipart/mixed; boundary=\"".$this->boundary."\"\r\n";

		$contentMail = "Date: " . date("D, d M Y H:i:s") . " UT\r\n";
		$contentMail .= 'Subject: =?' . $this->smtp_charset . '?B?'  . base64_encode($subject) . "=?=\r\n";
		$contentMail .= $headers . "\r\n";
		$contentMail .= $message . "\r\n";		
		
		if(!empty($this->attach_part))
			$contentMail .= $this->attach_part;
		
		try {
            if(!$socket = @fsockopen($this->smtp_host, $this->smtp_port, $errorNumber, $errorDescription, 30)){
                throw new Exception($errorNumber.".".$errorDescription);
            }
            if (!$this->_parseServer($socket, "220")){
                throw new Exception('Connection error');
            }
			
			$server_name = $_SERVER["SERVER_NAME"];
            fputs($socket, "HELO $server_name\r\n");
            if (!$this->_parseServer($socket, "250")) {
                fclose($socket);
                throw new Exception('Error of command sending: HELO');
            }
            
            fputs($socket, "AUTH LOGIN\r\n");
            if (!$this->_parseServer($socket, "334")) {
                fclose($socket);
                throw new Exception('Autorization error');
            }
            
            fputs($socket, base64_encode($this->smtp_username) . "\r\n");
            if (!$this->_parseServer($socket, "334")) {
                fclose($socket);
                throw new Exception('Autorization error');
            }
            
            fputs($socket, base64_encode($this->smtp_password) . "\r\n");
            if (!$this->_parseServer($socket, "235")) {
                fclose($socket);
                throw new Exception('Autorization error');
            }
			
            fputs($socket, "MAIL FROM: <".$this->smtp_username.">\r\n");
            if (!$this->_parseServer($socket, "250")) {
                fclose($socket);
                throw new Exception('Error of command sending: MAIL FROM');
            }
            
			$mailTo = ltrim($mailTo, '<');
			$mailTo = rtrim($mailTo, '>');
            fputs($socket, "RCPT TO: <" . $mailTo . ">\r\n");     
            if (!$this->_parseServer($socket, "250")) {
                fclose($socket);
                throw new Exception('Error of command sending: RCPT TO');
            }
            
            fputs($socket, "DATA\r\n");     
            if (!$this->_parseServer($socket, "354")) {
                fclose($socket);
                throw new Exception('Error of command sending: DATA');
            }
            
            fputs($socket, $contentMail."\r\n.\r\n");
            if (!$this->_parseServer($socket, "250")) {
                fclose($socket);
                throw new Exception("E-mail didn't sent");
            }
            
            fputs($socket, "QUIT\r\n");
            fclose($socket);
        } catch (Exception $e) {
            return  $e->getMessage();
        }
        return true;
    }
    
    private function _parseServer($socket, $response) {
        while (@substr($responseServer, 3, 1) != ' ') {
            if (!($responseServer = fgets($socket, 256))) {
                return false;
            }
        }
        if (!(substr($responseServer, 0, 3) == $response)) {
            return false;
        }
        return true;
        
    }
	
	function content_type($file) 
	{
		return _mime(basename($file));
	}
	
	function attach($arr_files) 
	{
        $res = 0;
		$attach_part = '';
		$af = is_array($arr_files) ? $arr_files : (empty($arr_files) ? null : array($arr_files));

		if(is_array($af))
			foreach($af as $path)
			{
				if(!file_exists($path))
					continue;
				
				$fp = fopen($path, 'r');
				
				if(!$fp)
					continue;
				
				$file = fread($fp, filesize($path));
				fclose($fp);
				
				$filename = basename($path);				
				$attach_part .= "\r\n—".$this->boundary."\r\n";
				$attach_part .= "Content-Type: "._mime(basename($filename))."; name=\"".$filename."\"\r\n";
				$attach_part .= "Content-Disposition: attachment\r\n";
				$attach_part .= "Content-Transfer-Encoding: base64\r\n";
				$attach_part .= "\r\n";
				$attach_part .= chunk_split(base64_encode($file));

				$res ++;
			}
			
		if(!empty($attach_part))
			$attach_part .= "\r\n—".$this->boundary."—\r\n";
		
		$this->attach_part = $attach_part;
		
		ech($this->attach_part);
		
        return $res;
    }
}