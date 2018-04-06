<?php

/*	Create a mail message and send it to the destinations with some predefined parameters
	e.g. blind cc, predefined text etc.

	NOTE: The mail parameters are created to use the PHP / Linux sendmail (with exim4 under
	Debian systems).
*/
Class Mailer {

	private $DEBUG = 0;

	// Predefined mail compnents
	private $MAIL_BCC = "ibiza.techconsulting@gmail.com";
	private $NAME_BCC = "Webmaster";
	private $MAIL_FROM = "pos_manager@techinside.es";
	private $NAME_FROM = "POS Manager";
	private $MAIL_REPLY = "ibiza.techconsulting@gmail.com";
	private $SUBJECT = "Message from the POS Server";

	// Constructor.
	public function __construct(){

	}
	
	/*
		Mail sending function. Send the string (i.e. a list or one or more user license(s)
		to the destination address. The method returns 
	*/
	public function mailSend($toAddress, $subjectDetails, $message) {
		// Create and array that will contain all the mail headers.
		$headers   = array();
		// Create the complete mail subject.
		$emailSubject = $this->SUBJECT . " " . $subjectDetails;
		
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-type: text/html; charset=iso-8859-1";
		$headers[] = "From: " . $this->NAME_FROM . " <" . $this->MAIL_FROM . ">";
		$headers[] = "Bcc: " . $this->NAME_BCC . " <" . $this->MAIL_BCC . ">";
		$headers[] = "Reply-To: POS Manager <" . $this->MAIL_REPLY . ">";
		$headers[] = "Subject: {" . $this->SUBJECT . "}";
		$headers[] = "X-Mailer: PHP/".phpversion();
		
		$mailResult = mail($toAddress, $emailSubject, $message, implode("\r\n", $headers), $additionalParameters);
		
		return $mailResult;
	}
	
}

?>