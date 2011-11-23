<?php

class API_Upload extends API_Authenticated {


	protected $parameters;
	
	protected $groupid;
	

	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		if (count($parameters) < 1) {
			throw new Exception('Missing group parameter');
		}
		$this->auth();
		$this->groupid = $parameters[0];
		$this->requireMembership();
		
	}
	
	protected function requireMembership() {
		if (!$this->fdb->isMemberOfContactlist($this->user, $this->groupid))
			throw new Exception('Access denied. You are not member of the group specified.');
	}

	
	protected function requireUserToken() {	
	}

	
	function prepare() {
		parent::prepare();
		
		$fileName = NULL;
		$contentLength = NULL;
		
 		// error_log(var_export($_SERVER, TRUE));
		
		
		if (array_key_exists('HTTP_X_FILENAME', $_SERVER) && array_key_exists('CONTENT_LENGTH', $_SERVER)) {
			$fileName = $_SERVER['HTTP_X_FILENAME'];
			$contentLength = $_SERVER['CONTENT_LENGTH'];
		} else throw new Exception("Error retrieving headers when receiving uploaded file.");

		if (!$contentLength > 0) {
			throw new Exception('No file uploaded!');
		}
		
		$fpath = $this->config->resolvePath('files');
		$storefilename = SimpleSAML_Utilities::stringToHex(SimpleSAML_Utilities::generateRandomBytes(21));
		
		$mimetype = $this->get_mime_content_type($fileName);
		
		
		$storef = $fpath . '/' . $storefilename;
		file_put_contents($storef, file_get_contents("php://input"));
		
		$this->fdb->addFile($this->user, $this->groupid, $storefilename, $fileName, $mimetype);
		
		// error_log('Storing file: ' . $storef);
		
// 		error_log('Receiving a file');
// 		error_log(var_export($_FILES, TRUE));
		
	}

    function get_mime_content_type($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }
	
}

