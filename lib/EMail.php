<?php

/**
 * A minimalistic Emailer class. Creates and sends HTML emails.
 *
 * @author Andreas Åkre Solberg, UNINETT AS. <andreas.solberg@uninett.no>
 * @package simpleSAMLphp
 * @version $Id$
 */
class Foodle_EMail {

	private $to = NULL;
	private $cc = NULL;
	private $body = NULL;
	private $from = NULL;
	private $replyto = NULL;
	private $subject = NULL;
	private $headers = array();
	
	protected $swift;
	
	/**
	 * Constructor
	 */
	function __construct($to, $subject, $from = NULL, $cc = NULL, $replyto = NULL) {
		$this->to = $to;
		$this->cc = $cc;
		$this->replyto = $replyto;
		$this->subject = $subject;
		
		$config = SimpleSAML_Configuration::getInstance('foodle');
		$this->from = $config->getValue('fromAddress', 'no-reply@foodl.org');
		
		require_once(dirname(dirname(__FILE__)) . '/lib-ext/swift/swift_required.php');
	}

	function setBody($body) {
		$this->body = $body;
	}
	
	public function getHTML($body = NULL) {
		if (empty($body)) $body = $this->body;
		
		$body = Data_Foodle::cleanMarkdownInput($body);
		
		return '<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
		<head xml:lang="en">

			<meta charset="utf-8" />	

		<style type="text/css">


		/* ------ Buttons  ----- */



		/* --- links --- */

		a, a:link, a:visited, a:active {
		    color: #633;
		    text-decoration: none;
		}
		a:hover{
		    text-decoration: underline;
		}
		a.lesmer {
		    float: right;
		    margin: 1em;
		}

		span.grey {
			color: #aaa;
		}


		html{
		    height: 100%;

		/*    font-family: Arial, Verdana, sans-serif;*/
		}
		body{
		    height: 100%;
		}
		p {
		    margin-top:     10px;
		    margin-bottom:  10px;
		}


		table {
			border-collapse:collapse;
			border-spacing:0;
			margin: .6em;
		}

		td {
			border: 1px solid #ccc;
		}
		td,th {
			border: 1px solid #aaa;
		/*	text-align: center; */
		}
		th {
			background: #dda;
			padding: .1em 1em .1em 1em;

		}

		dt {
			font-size: 105%;
			color: #600;
			font-weight: bold;
		}
		dd p {
			margin: 0px 1em .1em 0px;
		}


		/* --- General --- */


		body {
			margin: 0px;
			padding: 0px;
			font-family: Helvetica, Arial, sans-serif;
		}
		p {
		/*	margin: 0px;
			padding: 0px;*/
		}

		div#content {
			padding: 1em;
		}
		div#content h1 {
			margin-top: 0px;
		}
		
		hr {
			height: 0px; color: #ccc; 
		}


		/*  --- Header ---  */


		#header {
		    z-index: 0;
		    background-color: #f00;
		}

		#header #logo {
			color: #fff;
			font-family: "Verdana", "sans-serif";
			font-weight: bold;
			letter-spacing: -0.12em;
			text-shadow: 0px 2px 0px #900;
			font-size: 30px;
		/*	position: absolute;
			top: 2px; left: 2px; */
			z-index: 10;
		}
		#header #version {
			font-weight: normal;
			letter-spacing: 0.1em;
			font-size: x-small;
			text-shadow: 0px 1px 0px #900;
		}
		#header #logo #news, #header #logo #mailinglist {
			font-weight: normal;
			letter-spacing: 0em;
		}


		/* --- headerbar --- */

		#headerbar {
		/*	position: absolute;
			top: 42px;
			width: 100%;

			*/
			background: #eee;
			border-top: 1px solid #ccc;
			border-bottom: 1px solid #ccc;
			margin: 3px 0px 0px 0px; 
			padding: 0px 0px 0px 0px;
			z-index: 3;
		}
		#headerbar #breadcrumb {
			float: left; 
			margin: 9px 1em;

		}




		/*  --- Footer ---  */

		#footer {
		/*	width: 100%;*/
			clear: both;
			border-top: 1px solid #ccc;
			text-align: center;
			margin-top: 1em; 
			padding: 0px 0px 0px 0px;
			z-index: 1;
			color: #888;
		}




		</style>


			<title>' . $this->subject . '</title> 



		</head>
		<body>





		<!-- Grey header bar below -->
		<div id="headerbar" style="clear: both">
		<p id="breadcrumb">' . $this->subject . '</p>
		<p style="height: 0px; clear: both"></p>
		</div><!-- /#headerbar -->



		<div id="content">


				' . $body . '

		</div><!-- /#content -->

		<div id="footer">
			This mail was sent via <a href="https://foodl.org">foodl.org</a>. Please report misuse &mdash; <a href="https://foodl.org/support">Foodle Support</a>.
		</div><!-- /#footer -->


		</body>
		</html>';
	}
	
	function sendWithAttachment($attach) {
		if ($this->to == NULL) throw new Exception('EMail field [to] is required and not set.');
		if ($this->subject == NULL) throw new Exception('EMail field [subject] is required and not set.');
		if ($this->body == NULL) throw new Exception('EMail field [body] is required and not set.');
		
		$random_hash = SimpleSAML_Utilities::stringToHex(SimpleSAML_Utilities::generateRandomBytes(16));
		
		if (isset($this->from))
			$this->headers[]= 'From: ' . $this->from;
		if (isset($this->replyto))
			$this->headers[]= 'Reply-To: ' . $this->replyto;

		$this->headers[] = 'MIME-Version: 1.0';
		$this->headers[] = 'multipart/mixed; boundary=simplesamlphp-mixed-' . $random_hash . ''; 


		$message = '--simplesamlphp-mixed-' . $random_hash . '
Content-Type: text/plain; charset="utf-8" 
Content-Transfer-Encoding: 7bit

' . self::quoted_printable_encode('Dette er litt tekst. som skal være med i mailen....'). '

--simplesamlphp-mixed-' . $random_hash . '
Content-Disposition: attachment;
	filename=foodle-invitation-' . $random_hash . '.ics
Content-Type: text/calendar;
	name="foodle-invitation-' . $random_hash . '.ics"
Content-Transfer-Encoding: base64

' . base64_encode($attach) . '

--simplesamlphp-mixed-' . $random_hash . '
';

		
		$message = 'simplesamlphp-mixed-' . $random_hash . '
Content-Type: multipart/alternative; boundary="simplesamlphp-alt-' . $random_hash . '"

--simplesamlphp-alt-' . $random_hash . '
Content-Type: text/plain; charset="utf-8" 
Content-Transfer-Encoding: 8bit

' . strip_tags(html_entity_decode($this->body)) . '

--simplesamlphp-alt-' . $random_hash . '
Content-Type: text/html; charset="utf-8" 
Content-Transfer-Encoding: 8bit

' . $this->getHTML($this->body) . '

--simplesamlphp-alt-' . $random_hash . '

--simplesamlphp-mixed-' . $random_hash . '
Content-Type: text/calendar; name="foodle-invitation-' . $random_hash . '.ics"  
Content-Transfer-Encoding: base64  
Content-Disposition: attachment 

' . base64_encode($attach) . '

--simplesamlphp-mixed-' . $random_hash . '
';
		$headers = join("\r\n", $this->headers);

		$mail_sent = mail($this->to, $this->subject, $message, $headers);
		SimpleSAML_Logger::debug('Email: Sending e-mail to [' . $this->to . '] : ' . ($mail_sent ? 'OK' : 'Failed'));
		if (!$mail_sent) throw new Exception('Error when sending e-mail');
	}
	
	function send($attach = NULL) {
		if ($this->to == NULL) throw new Exception('EMail field [to] is required and not set.');
		if ($this->subject == NULL) throw new Exception('EMail field [subject] is required and not set.');
		if ($this->body == NULL) throw new Exception('EMail field [body] is required and not set.');

		
		$message = Swift_Message::newInstance();
		$message->setSubject($this->subject);
		error_log('From address is: ' . $this->from);
		$message->setFrom($this->from);
		$message->setTo($this->to);
		$message->setBody(strip_tags(html_entity_decode($this->body)));
		$message->addPart( $this->getHTML(), 'text/html');
		
		if (!empty($attach)) {
			foreach($attach AS $a) {
				$na = Swift_Attachment::newInstance($a['data'], $a['file'], $a['type']);
				$message->attach($na);
			}
		}
		
		
		$transport = Swift_MailTransport::newInstance();
		//Sendmail
		//$transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
		
		$mailer = Swift_Mailer::newInstance($transport);
		$mailer->send($message);
		
	}


	function sendOld() {
		if ($this->to == NULL) throw new Exception('EMail field [to] is required and not set.');
		if ($this->subject == NULL) throw new Exception('EMail field [subject] is required and not set.');
		if ($this->body == NULL) throw new Exception('EMail field [body] is required and not set.');
		
		$random_hash = SimpleSAML_Utilities::stringToHex(SimpleSAML_Utilities::generateRandomBytes(16));
		
		if (isset($this->from))
			$this->headers[]= 'From: ' . $this->from;
		if (isset($this->replyto))
			$this->headers[]= 'Reply-To: ' . $this->replyto;

		$this->headers[] = 'Content-Type: multipart/alternative; boundary="simplesamlphp-' . $random_hash . '"'; 
		
		$message = '

This is a message with multiple parts in MIME format.

--simplesamlphp-' . $random_hash . '
Content-Disposition: inline
Content-Type: text/plain; charset="utf-8" 
Content-Transfer-Encoding: 8bit

' . strip_tags(html_entity_decode($this->body)) . '

--simplesamlphp-' . $random_hash . '
Content-Type: text/html; charset="utf-8" 
Content-Transfer-Encoding: 8bit

' . $this->getHTML($this->body) . '

--simplesamlphp-' . $random_hash . '--
';
		$headers = join("\r\n", $this->headers);

		$mail_sent = mail($this->to, $this->subject, $message, $headers);
		SimpleSAML_Logger::debug('Email: Sending e-mail to [' . $this->to . '] : ' . ($mail_sent ? 'OK' : 'Failed'));
		if (!$mail_sent) throw new Exception('Error when sending e-mail');
	}

}
