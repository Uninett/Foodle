<?php

class API_Download extends API_Authenticated {


	protected $parameters;
	
	protected $groupid;
	protected $filename;
	protected $fileinfo;
	

	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		if (count($parameters) < 1) {
			throw new Exception('Missing group parameter');
		}
		$this->auth();
		$this->filename = $parameters[0];
		
		$this->fileinfo = $this->fdb->getFileinfo($this->filename);
		
		#echo '<pre>'; print_r($this->fileinfo); exit;
		
		$this->groupid = $this->fileinfo['groupid'];
		$this->requireMembership();
		
	}
	
	protected function requireMembership() {
		if (!$this->fdb->isMemberOfContactlist($this->user, $this->groupid))
			throw new Exception('Access denied. You are not member of the group specified.');
	}

	
	protected function requireUserToken() {	
	}

	
	function checkMimetype($mimetype) {
	
		$splitted = explode('/', $mimetype);
		
		if ($splitted[0] === 'image') return TRUE;
		if ($splitted[0] === 'video') return TRUE;
	
		if (!in_array($mimetype, array(
            'application/msword',
            'application/rtf',
            'application/vnd.ms-excel',
            'application/vnd.ms-powerpoint',

            // open office
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.spreadsheet',
            
            'application/octet-stream',
		))) {
			throw new Exception('Mime type ' . htmlspecialchars($mimetype) . ' is not allowed, because of a security restriction on allowed mime types. Contact andreas.solberg@uninett.no if you think this MIME type should be allowed.');
		}
	}
	
	function prepare() {

		$fileName = $this->fileinfo['filename'];
		$contentLength = NULL;
		
		$fpath = $this->config->resolvePath('files');

		$mimetype = $this->get_mime_content_type($fileName);
		
		
		$storef = $fpath . '/' . $this->filename;

		$this->checkMimetype($mimetype);
		
		
		header('Content-disposition: attachment; filename=' . htmlspecialchars($this->fileinfo['filename'] ));
		header('Content-type: ' . $this->fileinfo['mimetype']);
		echo(file_get_contents($storef)); 
		exit;
		error_log('File: ' . $storef);
		error_log('File tag asked for: ' . $this->filename);
		
		
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
            'docx' => 'application/msword',
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

