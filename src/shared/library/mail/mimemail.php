<?php

sendmail(array("fromname" => "Ivar Clemens", "fromaddress" => "post@ivarclemens.nl", "toname" => "Kees Meijs", "toaddress" => "post@keesmeijs.nl", "subject" => "Test mail", "text" => "Dit is een mail voor kees.\nOf niet soms!", "attachment" => "/home/ivar/CT2006000.pdf"));

function sendmail($mail) {

	$header = "From: ".$mail['fromname']." <".$mail['fromaddress'].">\n";
//	$header .= "To: ".$mail['toname']." <".$mail['toaddress'].">\n";
	$header .= "Subject: ".$mail['subject']."\n";
	$header .= "MIME-Version: 1.0\n";
	$header .= "Content-Type: multipart/mixed; boundary=\"bound-12345\n";
	$header .= "Content-Transfer-Encoding: 7bit\n";
	$header .= "This part of the E-mail should never be seen. If\n";
	$header .= "you are reading this, consider upgrading your e-mail\n";
	$header .= "client to a MIME-compatible client.\n";
	$header .= "--bound-12345\n";
	
	$message = "Content-Type: text/plain; charset=\"iso-8859-15\"\n";
        $message .= "Content-Transfer-Encoding: 7bit\n";
        $message .= "\n".$mail['text']."\n";
	$message .= "--bound-12345\n";
	
	if($mail['attachment']) {
		if(is_array($mail['attachment'])) {
		$attachments = $mail['attachment'];
		// Een voor een toevoegen als attachment
		  for($i = 0; $i < count($attachments); $i++) {
		    $handle = fopen($attachments[$i],'rb');
		    $file_content = fread($handle,filesize($attachments[$i]));
		    fclose($handle);
		    $encoded = chunk_split(base64_encode($file_content)); 
		    
		    $message .= "Content-Type: application/octet-stream; name=\"".$attachments[$i]."\"\n";
		    $message .= "Content-Transfer-Encoding: base64\n";
		    $message .= "Content-Disposition: attachment; filename=\"".$attachments[$i]."\"\n";
		    $message .= "\n".$encoded;
		    if(($i + 1) == count($attachments)) {
		      $message .= "--bound-12345--\n"; 
		    } else {
		      $message .= "--bound-12345\n";
		    }
		  }
		} else {
		// Een attachment toevoegen
		   $handle = fopen($mail['attachment'],'rb');
		   $file_content = fread($handle,filesize($mail['attachment']));
		   fclose($handle);
		   $encoded = chunk_split(base64_encode($file_content));
		   
		   $message .= "Content-Type: application/octet-stream; name=\"".$mail['attachment']."\"\n";
		   $message .= "Content-Transfer-Encoding: base64\n";
		   $message .= "Content-Disposition: attachment; filename=\"".$mail['attachment']."\"\n";
		   $message .= "\n".$encoded;
		   $message .= "--bound-12345--\n";
		}
	}
	
mail('Kaas <' .$mail['toaddress'] . '>', $mail['subject'], $message, $header, '-f' . $mail['fromaddress']);
	
}

?>
