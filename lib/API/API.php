<?php

abstract class API_API {

	protected $config;	
	protected $parameters;
	
	protected $fdb;

	function __construct($config, $parameters) {
		$this->config = $config;
		$this->parameters = $parameters;
		
		$this->fdb = new FoodleDBConnector($this->config);
	}
	
	protected function prepare() {
		throw new Exception('API not implemented');
	}
	
	public function show() {
	
		$returnobj = array('status' => 'ok');

	
		try {			
			$returnobj['data'] = $this->prepare();			

		} catch(Exception $e) {
			
			$returnobj['status'] = 'error';
			$returnobj['message'] = $e->getMessage();
			
			// error_log('API returning error: ' . $e->getMessage());
			
		}

		if(!empty($_REQUEST['debug'])) {
			header('Content-type: text/plain; charset=utf-8');
			print_r($returnobj); 
			exit;
		}

		
		header('Content-type: application/json; charset=utf-8');
		
		
		echo json_encode($returnobj);
		exit;
	}
	
	
	/**
	 * Format an associative array as a json string.
	 *
	 * @param mixed $data  The data that should be json encoded.
	 * @param string $indentation  The current indentation level. Optional.
	 * @return string  The json encoded data.
	 */
	public static function json_format($data, $indentation = '') {
		assert('is_string($indentation)');
	
		if (!is_array($data)) {
			return json_encode($data);
		}
	
		$ret = "{";
		$first = TRUE;
		foreach ($data as $k => $v) {
			$k = json_encode((string)$k);
			$v = self::json_format($v, $indentation . "\t");
	
			if ($first) {
				$ret .= "\n";
				$first = FALSE;
			} else {
				$ret .= ",\n";
			}
	
			$ret .= $indentation . "\t" . $k . ': ' . $v;
		}
		$ret .= "\n" . $indentation . '}';
	
		return $ret;
	}


	
}

