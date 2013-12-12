<?php
/**
 * JSon service classes for ExtJS Data stores
 *
 * TODO: extend metacatalog to describe resultsets
 * TODO: add global PHP error handling
 */

error_reporting(E_ALL);

/**
 * all constants are used for enumeration
 */
class enum {
	private $constants = null; // cache reflection lookups for constants
	
	function __get($name) {
		if ($name == "ENUM") {
			if ($this->constants !== null)
				return $this->constants;
			
			$r = new ReflectionClass(get_class($this));
			$this->constants = $r->getConstants();
			return $this->constants;
		}
		
		return $this->$name;
	}
}

/**
 * result class
 * 
 * This class contains all properties for a unified result message
 * - metaProperty: might be used by ext for dynamic configurations
 * - record: single record result
 * - root: recordset data
 * - success: boolean, false if there was an error
 * - total: number of records in root
 * - type: service_data::TYPE_* (record/recordset)
 */
class service_result {
	public $metaData = null;
	public $record = null;
	public $root = null;
	public $success = null;
	public $total = null;
	public $type = null;
	
	function __construct($type) {
		$this->type = $type;
	} 
}

/**
 * Standard Error message for json data stores
 */
class service_error {
	public $code    = 0;
	public $message = "";
	
	function __construct($code, $message) {
		$this->code    = $code;
		$this->message = $message;
	}
}

/**
 * Standard Data storage for JSon stores
 */
class service_data extends enum {
	const TYPE_RECORDSET = 1;
	const TYPE_RECORD    = 2;
	const TYPE_SUCCESS   = 3;
	
	protected $data = array();
	protected $num  = null;
	protected $type = null;

	function __construct($type) {
		if($type == self::TYPE_RECORDSET) {
			$this->num = 0;
		}
		$this->type = $type;
	}
	
	/**
	 * add a single record to the result
	 * 
	 * used for multi record results
	 */
	public function add_record(array $rec) {
		if ($this->type == self::TYPE_RECORD || 
		    $this->type == self::TYPE_SUCCESS)
			return false;
		
		$this->data[] = $rec;
		$this->num++;
		return true;
	}
	
	/**
	 * replace result with this record
	 * 
	 * used for single record results
	 */
	public function set_record(array $rec) {
		if ($this->type == self::TYPE_RECORDSET || 
		    $this->type == self::TYPE_SUCCESS)
			return false;
		
		$this->data = $rec;
		return true;
	}
	
	/**
	 * replace result with these records
	 * 
	 * used for multiple record results
	 */
	public function set_records(array $recs) {
		if ($this->type == self::TYPE_RECORD || 
		    $this->type == self::TYPE_SUCCESS)
			return false;
		
		$this->data = $recs;
		$this->num = sizeof($recs);
		return true;
	}
	
	/**
	 * set success exit code
	 */
	public function set_success($code = 0) {
		if ($this->type == self::TYPE_RECORD || 
		    $this->type == self::TYPE_RECORDSET)
			return false;
		
		$this->data = $code;
		return true;
	}

}

/**
 * Service parameter description
 */
class service_parameter extends enum {
	const TYPE_BOOL   = 1;
	const TYPE_INT    = 2;
	const TYPE_FLOAT  = 3;
	const TYPE_STRING = 4;
	const TYPE_ARRAY  = 5;
	const TYPE_OBJECT = 6;
	
	public $name = "";
	public $type = NULL;
	public $required = false;
	
	/**
	 * constructor
	 * 
	 * $type must be of self::TYPE_* or the constructor will return null
	 */
	function __construct($name, $type, $required=true) {
		// check if we have a valid type
		if (!in_array($type, $this->ENUM))
			return null;
		
		$this->name = $name;
		$this->type = $type;
		$this->required = $required;
	}
	
	/**
	 * check if input value (get/post) is a boolean value
	 *
	 * 1/0/true/false (case insensitive) allowed
	 */
	protected function is_bool($value) {
		$ok = false;
		if ($value === 1 || $value == "1" || $value === true ||
		    $value === 0 || $value == "0" || $value === false||
		    strtolower($value) == "true" || strtolower($value) == "false" ||
		    strtolower($value) == "null") {
			$ok = true;
		}
		return $ok;
	}
	
	/**
	 * check if input value from get/post is integer
	 */
	protected function is_int($value) {
		if (strlen((int) $value) == strlen($value) &&
		   (int) $value . "" == $value) {
			return true;
		}
		return false;
	}
	
	/**
	 * check if input value from get/post is float
	 */
	protected function is_float($value) {
		return preg_match("/^[0-9\.]+$/", $value);
	}
	
	/**
	 * check if input value from get/post is php array
	 */
	protected function is_array($value) {
		return is_array($value);
	}
	
	/**
	 * check if input value from get/post is a json object
	 *
	 * parse if needed
	 */
	protected function is_object($value) {
		return is_object($value);
	}
	
	/**
	 * validate post/get value against this data type
	 */
	public function validate($value) {
		switch($this->type) {
			case 1: // bool
				return $this->is_bool($value);

			case 2: // int
				return $this->is_int($value);
			
			case 3: // float
				return $this->is_float($value);
			
			case 4: // string
				return (!$this->is_object($value) && !$this->is_array($value));

			case 5: // array
				return $this->is_array($value);
			
			case 6: // object
				return $this->is_object($value);
			
		}
		
		return false;
	}
	
	/**
	 * cast from POST/GET string to native data type
	 *
	 * Strings and arrays are not casted, they should be set up by php properly 
	 * already, whereis this might not be true for array elements.
	 *
	 * object should be posted via json and therefore should have proper types.
	 */
	public function cast($value) {
		switch($this->type) {
			case 1: // bool
				if ($value === 0 || $value == "0" || $value === false ||
						strtolower($value) == "false" || strtolower($value) == "null")
					return false;
				return true;

			case 2: // int
				return (int) $value;
			
			case 3: // float
				return (float) $value;
			
			case 4: // string
				return $value;

			case 5: // array
				return $value;
			
			case 6: // object
				if (is_string($value))
					return json_decode($value);
				if (is_object($value))
					return $value;
				
		}
		
		return null;
	}
}

/**
 * Service api item
 */
class service_api_item {
	public $fn = "";
	public $desc = "";
	public $param = null; // array of service_parameter
	public $return = null; // int, service_data::TYPE_*
	
	function __construct($fn, $param, $return, $desc) {
		$this->fn = $fn;
		$this->param = $param;
		$this->return = $return;
		$this->desc = $desc;
	}
}

/**
 * Basic service class for communicating with ExtJS
 */
class service_basic {
	protected $dbconn_active = null;
	protected $dbconn_pd = null;
	protected $dbconn_read = null;
			
	/**
	 * associative array of config values
	 */
	public $config  = array();
	
	/**
	 * defines the service catalog
	 *
	 * The catalog holds information about all callable methods, attributes 
	 * include:
	 * - name:        string
	 * - description: string
	 * - parameters:  service_parameter
	 * - type:        service_data::TYPE_*
	 */
	public $api = array();
	
	function __construct($config = array()) {
		foreach($config as $n => $v)
			$this->config[$n] = $v;
	}
	
	/**
	 * handle errors
	 * 
	 * This is a convenience function to handle errors. It will send a json 
	 * response, and abort the script with $code as exit code.
	 */
	protected function error($code, $message) {
		$r = new service_result(null);
		$r->error = new service_error($code, $message);
		$r->success = false;
		
		self::serve($r);
		exit($code);
	}
	
	/**
	 * shorthand for creating api descriptions from an array
	 */
	protected function add_api($meta) {
		if (!isset($meta["fn"]) || !isset($meta["return"]) || !isset($meta["desc"]))
			return null;
		
		/*
		$param = array();
		if (isset($meta["param"])) {
			foreach($meta["param"] as $n => $v) {
				$param[] = new service_parameter($n, $v);
			}
		}
		*/

		$a = new service_api_item($meta["fn"], $meta["param"], 
		                          $meta["return"], 
		                          $meta["desc"]);
		$this->api[] = $a;
		
		return $a;
	}
	
	/**
	 * usb dbconn 
	 * 
	 * there re 2 ways: read only, which is the fast method
	 * and read/write, which uses PD methods with logging. this method is 
	 * dog slow and not recommendable to populate datastores. 
	 */
	protected function get_dbconn($write=false) {
		if ($write) {
			// TODO: instantiate a PD dbconn without wrecking output 
			$this->dbconn_active = $this->dbconn_pd;
			die("TBD: not yet imlpemented");
		} else {
			
			if ($this->dbconn_read) {
				$this->dbconn_active = $this->dbconn_read;
				return $this->dbconn_read;
			}
			
			include_once("../../../../uhbs_config.php");
			global $ADODB_FETCH_MODE;
			$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
			$this->dbconn_read = isop_dbconn();
			$this->dbconn_read->Execute("alter session set nls_date_format='dd.mm.yyyy hh24:mi:ss'");
			$this->dbconn_active = $this->dbconn_read;
			return $this->dbconn_read;
			
		}
		return $this->dbconn_active;
	}
	
	/**
	 * serve result
	 */
	public static function serve(service_result $res) {
		header("Content-type: application/json");
		print(json_encode($res));
	}
	
	/**
	 * main method
	 * 
	 * This method should be called once the service is declared
	 */
	public static function main() {
		$s = new service();
		//var_dump($s);
		
		// we expect to get a post/get parameter named fn. This parameter defines
		// which api function should be called
		$fn = (isset($_GET["fn"])) ? $_GET["fn"] : null;
		if(!$fn)
			$fn = (isset($_POST["fn"])) ? $_POST["fn"] : null;
		if(!$fn) {
			// failed to retreive api function name. this is where we abort
			$s->error(1, "no such method"); // exit 
		}
		
		// if we have an fn parameter, check if a method in this class called 
		// "call_".fn exists. if not, throw error sensible message
		if (!method_exists($s, "call_" . $fn)) {
			$s->error(2, "method $fn does not exist"); // exit 
		}
		
		// get API
		$api = $s->find_api_call($fn);
		//var_dump($api);
		if (!$api) {
			$s->error(3, "no api description"); // exit 
		}
		
		// validate parameters, sanitize input
		//
		// get metadata about this api call, check if all parameters are submitted 
		// and if their data type is apropriate
		//
		// submitted parameters must match their type. empty values are not allowed 
		// for submitted parameters. name="" is illegal. Omit the parameter if empty
		// (works only for parameters which are not required).
		//
		$params = array();
		foreach($api->param as $ix => $p) {
			
			$value = NULL;
			$found = true;
			if(isset($_GET[$p->name])) 
				$value = $_GET[$p->name];
			else if(isset($_GET[$p->name])) 
				$value = $_GET[$p->name];
			else
				$found = false;
			
			// required?
			if (!$found && $p->required) {
				//print_r($p);
				$s->error(4, "Parameter ". $p->name ." missing!"); // exit 
			}
			
			// if empty and string, set NULL to "" again
			if($value === NULL && $p->type == service_parameter::TYPE_STRING)
				$value = "";
				
			// decode json objects
			if ($p->type == service_parameter::TYPE_OBJECT)
				$value = json_decode($value);
			
			// type checking
			if ($found) {
				$valid = $p->validate($value);
				if (!$valid) 
					$s->error(5, "Parameter ". $p->name .", wrong type!"); // exit 
			}
			
			// cast input values. all but object (already casted before validation)
			if ($p->type != service_parameter::TYPE_OBJECT)
				$value = $p->cast($value);
			
			// CAVE: if not provided, value is set to NULL
			$params[$p->name] = $value;
		}
		
		// execute function
		$ret = call_user_func_array(array($s, "call_".$fn), $params);
		if (!$ret) {
			$s->error(6, "Result empty"); // exit 
		}
		
		// handle result
		$result = new service_result($ret->type);
		
		if ($ret->type == service_data::TYPE_RECORDSET) {
			$result->root = $ret->data;
			$result->total = $ret->num;
		} else {
			$result->record = $ret->data;
		}
		$result->success = true;
		
		//print_r($ret);
		
		$s->serve($result);
	}
	
	/**
	 * lookup callable methods
	 */
	public function find_api_call($name) {
		foreach ($this->api as $a) {
			if ($a->fn == $name)
				return $a;
		}
		return null;
	} 
}

?>
