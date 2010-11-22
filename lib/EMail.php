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
	
	/**
	 * Constructor
	 */
	function __construct($to, $subject, $from = NULL, $cc = NULL, $replyto = NULL) {
		$this->to = $to;
		$this->cc = $cc;
		$this->from = $from;
		$this->replyto = $replyto;
		$this->subject = $subject;
	}

	function setBody($body) {
		$this->body = $body;
	}
	
	private function getHTML($body) {
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



		<!-- Red logo header -->
		<div id="header">	
			<div id="logo">Foodle <span id="version">mail</span> 

			</div><!-- end #logo -->

		</div><!-- end #header -->


		<!-- Grey header bar below -->
		<div id="headerbar" style="clear: both">
		<p id="breadcrumb">' . $this->subject . '</p>
		<p style="height: 0px; clear: both"></p>
		</div><!-- /#headerbar -->



		<div id="content">


				' . $body . '

		</div><!-- /#content -->

		<div id="footer">
			This mail was sent to you because you did use the Foodle service at foodl.org.
		</div><!-- /#footer -->


		</body>
		</html>';
	}

	function send() {
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
--simplesamlphp-' . $random_hash . '
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

?>