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
	
	public function getHTML($body) {
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


static function quoted_printable_encode($input, $line_max = 75) { 
   $hex = array('0','1','2','3','4','5','6','7', 
                          '8','9','A','B','C','D','E','F'); 
   $lines = preg_split("/(?:\r\n|\r|\n)/", $input); 
   $linebreak = "=0D=0A=\r\n"; 
   /* the linebreak also counts as characters in the mime_qp_long_line 
    * rule of spam-assassin */ 
   $line_max = $line_max - strlen($linebreak); 
   $escape = "="; 
   $output = ""; 
   $cur_conv_line = ""; 
   $length = 0; 
   $whitespace_pos = 0; 
   $addtl_chars = 0; 

   // iterate lines 
   for ($j=0; $j<count($lines); $j++) { 
     $line = $lines[$j]; 
     $linlen = strlen($line); 

     // iterate chars 
     for ($i = 0; $i < $linlen; $i++) { 
       $c = substr($line, $i, 1); 
       $dec = ord($c); 

       $length++; 

       if ($dec == 32) { 
          // space occurring at end of line, need to encode 
          if (($i == ($linlen - 1))) { 
             $c = "=20"; 
             $length += 2; 
          } 

          $addtl_chars = 0; 
          $whitespace_pos = $i; 
       } elseif ( ($dec == 61) || ($dec < 32 ) || ($dec > 126) ) { 
          $h2 = floor($dec/16); $h1 = floor($dec%16); 
          $c = $escape . $hex["$h2"] . $hex["$h1"]; 
          $length += 2; 
          $addtl_chars += 2; 
       } 

       // length for wordwrap exceeded, get a newline into the text 
       if ($length >= $line_max) { 
         $cur_conv_line .= $c; 

         // read only up to the whitespace for the current line 
         $whitesp_diff = $i - $whitespace_pos + $addtl_chars; 

        /* the text after the whitespace will have to be read 
         * again ( + any additional characters that came into 
         * existence as a result of the encoding process after the whitespace) 
         * 
         * Also, do not start at 0, if there was *no* whitespace in 
         * the whole line */ 
         if (($i + $addtl_chars) > $whitesp_diff) { 
            $output .= substr($cur_conv_line, 0, (strlen($cur_conv_line) - 
                           $whitesp_diff)) . $linebreak; 
            $i =  $i - $whitesp_diff + $addtl_chars; 
          } else { 
            $output .= $cur_conv_line . $linebreak; 
          } 

        $cur_conv_line = ""; 
        $length = 0; 
        $whitespace_pos = 0; 
      } else { 
        // length for wordwrap not reached, continue reading 
        $cur_conv_line .= $c; 
      } 
    } // end of for 

    $length = 0; 
    $whitespace_pos = 0; 
    $output .= $cur_conv_line; 
    $cur_conv_line = ""; 

    if ($j<=count($lines)-1) { 
      $output .= $linebreak; 
    } 
  } // end for 

  return trim($output); 
} // end quoted_printable_encode 

}

?>