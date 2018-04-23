<?

class c_mail
{
	function __construct($params_in = null)
	{
		$this->init($params_in, true);
	}
	
	function init($params_in, $start_in = false)
	{
		if(!is_array($params_in))
			$params_in = array();
		
		$this->m_to 			= array_key_exists("to", $params_in)			? $params_in["to"] 			: ($start_in ? "" : $this->m_to);
		$this->m_from 			= array_key_exists("from", $params_in)			? $params_in["from"] 		: ($start_in ? "" : $this->m_from);
		
		$this->m_subject 		= array_key_exists("subject", $params_in)		? $params_in["subject"] 	: ($start_in ? "" : $this->m_subject);

		$this->m_src_charset	= array_key_exists("src_charset", $params_in)	? $params_in["src_charset"] : ($start_in ? "UTF-8"	: $this->m_src_charset);
		$this->m_dst_charset	= array_key_exists("dst_charset", $params_in)	? $params_in["dst_charset"] : ($start_in ? "KOI8-R"	: $this->m_dst_charset);
		
		$this->m_text 			= array_key_exists("text", $params_in)			? $params_in["text"] 		: ($start_in ? "" : $this->m_text);
		$this->m_replay_to 		= array_key_exists("replay_to", $params_in)		? $params_in["replay_to"] 	: ($start_in ? "" : $this->m_replay_to);
		
		$this->m_content_type	= array_key_exists("content_type", $params_in)	? $params_in["content_type"] : ($start_in ? "text/html"	: $this->m_content_type);
		
		$this->m_arr_files		= array_key_exists("arr_files", $params_in)		? $params_in["arr_files"] 	: ($start_in ? array()	: $this->m_arr_files);
		
		$this->m_cc 			= array_key_exists("cc", $params_in)			? $params_in["cc"] 			: ($start_in ? "" : $this->m_cc);
		$this->m_bcc 			= array_key_exists("bcc", $params_in)			? $params_in["bcc"] 		: ($start_in ? "" : $this->m_bcc);
		
		$this->m_arr_file_names	= array_key_exists("arr_file_names", $params_in) ? $params_in["arr_file_names"]  : ($start_in ? array()	: $this->m_arr_file_names);
		
		$this->m_smtp_username = array_key_exists("username", $params_in)		? $params_in["username"] 	: ($start_in ? "" : $this->m_smtp_username);		
		$this->m_smtp_password = array_key_exists("password", $params_in)		? $params_in["password"] 	: ($start_in ? "" : $this->m_smtp_password);
		$this->m_smtp_host = array_key_exists("host", $params_in)				? $params_in["host"] 		: ($start_in ? "" : $this->m_smtp_host);
		$this->m_smtp_port = array_key_exists("port", $params_in)				? $params_in["port"] 		: ($start_in ? "" : $this->m_smtp_port);
		
		if(empty($this->m_from))
			$this->m_from = $this->m_smtp_username;
		
		if(!is_array($this->m_arr_files))
			$this->m_arr_files = array($this->m_arr_files);
	}
	
	function send($params_in = null)
	{
		$this->init($params_in);
		
		$data_charset = $this->m_src_charset;
		$send_charset = $this->m_dst_charset;
		
		$body 		= '';
		$to 		= $this->m_to;
		$from 		= $this->m_from;
		$replay_to 	= $this->m_replay_to;		
		$text		= $this->m_text;
		$subject	= $this->m_subject;
		$content_type = $this->m_content_type;
		
		$cc 		= $this->m_cc;
		$bcc 		= $this->m_bcc;
		
		$arr_files = $this->m_arr_files;
		
		if($data_charset != $send_charset) 
		{
			$text		= iconv($data_charset, $send_charset."//TRANSLIT", $text);
			$subject	= iconv($data_charset, $send_charset."//TRANSLIT", $subject);
		}
		
		$subject 	= '=?'.$send_charset.'?B?'.base64_encode($subject).'?=';
		//$contentMail .= 'Subject: =?' .$this->m_dst_charset.'?B?'. base64_encode($subject)."=?=\r\n";
		
		$host_ext = !empty($this->m_smtp_username);
		
		$header 	= $host_ext ? "To: $to\r\n" : "";
		$header 	.= "From: $from\r\n";
		
		if(!empty($cc))
			$header		.= "Cc: $cc"."\r\n";		
		if(!empty($bcc))
			$header		.= "Bcc: $bcc"."\r\n";
		if(!empty($replay_to))
			$header .= "Reply-To: $replay_to\r\n";
			
		$header	.= "Mime-Version: 1.0"."\r\n"; 	
			
		//if(!is_array($arr_files))
		//	$arr_files = array();	
			
		if(!is_array($arr_files) || !count($arr_files))
		{
			$header	.= "Content-Type: $content_type; charset=$send_charset"."\r\n";
			$body	.= $text;
		}
		else
		{	
			//*
			$bound = rand(100000, 200000);
			
			$header	.= "Content-Type: multipart/mixed; boundary=".$bound."\r\n"; 
			
			$body	= "\r\n\r\n--".$bound."\r\n"; 
			$body	.= "Content-Type: $content_type; charset=$send_charset"."\r\n"; 
			$body	.= "Content-Transfer-Encoding: quoted-printable\r\n\r\n"; 
			$body	.= $text;
				
			$count = 0;	
	
			foreach ($arr_files as $file_name)
			{
				if(!file_exists($file_name))
					continue;
				
				$file	= fopen($file_name, "rb");
				$content = fread($file, filesize($file_name));
				fclose($file);
				
				//$content .= '     ';
				
				// замена имени файла
				$basename = basename($file_name);
				$pos = array_search($basename, $this->m_arr_file_names);
				if($pos)
				{
					$basename = $pos;
					if($data_charset != $send_charset) 
						$basename = iconv($data_charset, $send_charset."//TRANSLIT", $basename);
				}

				$body	.= "\r\n\r\n--".$bound."\r\n"; 
				//$body	.= "Content-Type: application/octet-stream; name=\"".$basename."\"\r\n";
				$body	.= "Content-Type: "._mime($basename)."; name=\"".$basename."\"\r\n";
				$body	.= "Content-Transfer-Encoding: base64\r\n"; 
				$body	.= "Content-Disposition: attachment; filename=\"".$basename."\"\r\n\r\n"; 
				$body	.= chunk_split(base64_encode($content))."\r\n";
				
				$count ++;
			}
			
			if($count > 0)
				$body	.= "--".$bound."--\r\n\r\n";
		}
		
		return $host_ext ? $this->mail($to, $subject, $body, $header) : @mail($to, $subject, $body, $header);
	}
	
	function parse_server($socket, $response) 
	{
		while (@substr($responseServer, 3, 1) != ' ') 
		{
			if (!($responseServer = fgets($socket, 256)))
				return false;
		}

		if (!(substr($responseServer, 0, 3) == $response)) 
			return false;

		return true;
    }
	
	function mail($to, $subject, $message, $headers)
	{
		$content = 'Date: '.date('r')."\r\n";//"Date: " . date("D, d M Y H:i:s") . " +0400\r\n";
		$content .= 'Subject: '.$subject."\r\n"; //'Subject: =?' .$this->m_dst_charset.'?B?'. base64_encode($subject)."=?=\r\n";
		$content .= $headers."\r\n";
		$content .= $message."\r\n";		
		
		try 
		{
			if(!$socket = @fsockopen($this->m_smtp_host, $this->m_smtp_port, $errorNumber, $errorDescription, 30))
				throw new Exception($errorNumber.".".$errorDescription);
			if (!$this->parse_server($socket, "220"))
				throw new Exception('Connection error');

			$server_name = $_SERVER["SERVER_NAME"];
			fputs($socket, "HELO $server_name\r\n");
			if (!$this->parse_server($socket, "250")) 
			{
				fclose($socket);
				throw new Exception('Error of command sending: HELO');
			}

			fputs($socket, "AUTH LOGIN\r\n");
			if (!$this->parse_server($socket, "334")) 
			{
				fclose($socket);
				throw new Exception('Autorization error');
			}

			fputs($socket, base64_encode($this->m_smtp_username) . "\r\n");
			if (!$this->parse_server($socket, "334")) 
			{
				fclose($socket);
				throw new Exception('Autorization error');
			}

			fputs($socket, base64_encode($this->m_smtp_password) . "\r\n");
			if (!$this->parse_server($socket, "235")) 
			{
				fclose($socket);
				throw new Exception('Autorization error');
			}

			fputs($socket, "MAIL FROM: ".$this->m_smtp_username."\r\n");
			if (!$this->parse_server($socket, "250")) 
			{
				fclose($socket);
				throw new Exception('Error of command sending: MAIL FROM');
			}

			$to = ltrim($to, '<');
			$to = rtrim($to, '>');
			fputs($socket, "RCPT TO: ".$to."\r\n");     
			if (!$this->parse_server($socket, "250")) 
			{
				fclose($socket);
				throw new Exception('Error of command sending: RCPT TO');
			}

			fputs($socket, "DATA\r\n");     
			if (!$this->parse_server($socket, "354")) 
			{
				fclose($socket);
				throw new Exception('Error of command sending: DATA');
			}

			fputs($socket, $content."\r\n.\r\n");
			if (!$this->parse_server($socket, "250")) 
			{
				fclose($socket);
				throw new Exception("E-mail didn't sent");
			}

			fputs($socket, "QUIT\r\n");
			fclose($socket);
        } 
		catch (Exception $e)
		{
			return  $e->getMessage();
		}

        return true;
    }
}

?>