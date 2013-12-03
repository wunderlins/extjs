<?php
/**
 * JSon service classes for ExtJS Data stores
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
		if ($this->type == self::TYPE_RECORD)
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
		if ($this->type == self::TYPE_RECORDSET)
			return false;
		
		$this->data = $rec;
		$this->num = null;
		return true;
	}
	
	/**
	 * replace result with this record
	 * 
	 * used for single record results
	 */
	public function set_records(array $recs) {
		if ($this->type == self::TYPE_RECORD)
			return false;
		
		$this->data = $recs;
		$this->num = sizeof($recs);
		return true;
	}
}

/**
 * Service parameter description
 *
 * This class is used to describe input parameters of a service method. It will 
 * describe the name, the value and makes available input validation methods. 
 *
 * The methods validate an string input value to check if there is the 
 * appropriate type in the string
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
	
	/**
	 * constructor
	 * 
	 * $type must be of self::TYPE_* or the constructor will return null
	 */
	function __construct($name, $type) {
		// check if we have a valid type
		if (!in_array($type, $this->ENUM))
			return null;
		
		$this->name = $name;
		$this->type = $type;
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
		    strtolower($value) == "true" || strtolower($value) == "false") {
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
		if (preg_match("/[\.0-9]+/", $value))
			return true;
		return false;
	}
	
	/**
	 * check if input value from get/post is php array
	 */
	protected function is_array($value) {
		return is_array($value);
	}
	
	/**
	 * check if input value from get/post is php array
	 */
	protected function is_object($value) {
		return is_object($value);
	}
	
	/**
	 * validate post/get value against this data type
	 */
	public function validate($value) {
		switch($this->type) {
			case service_parameter::TYPE_BOOL: // bool
				return $this->is_bool($value);

			case service_parameter::TYPE_INT: // int
				return $this->is_int($value);
			
			case service_parameter::TYPE_FLOAT: // float
				return $this->is_float($value);
			
			case service_parameter::TYPE_STRING: // string
				return ($value && !$this->is_object($value) && !$this->is_array($value));

			case service_parameter::TYPE_ARRAY: // array
				return $this->is_array($value);
			
			case service_parameter::TYPE_OBJECT: // object
				return $this->is_object($value);
		}
		
		return false;
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
		
		$this->serve($r);
		exit($code);
	}
	
	/**
	 * shorthand for creating api descriptions from an array
	 */
	protected function add_api($meta) {
		if (!isset($meta["fn"]) || !isset($meta["return"]) || !isset($meta["desc"]))
			return null;
		
		$param = array();
		if (isset($meta["param"])) {
			foreach($meta["param"] as $n => $v) {
				$param[] = new service_parameter($n, $v);
			}
		}

		$a = new service_api_item($meta["fn"], $param, 
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
		} else {
		
			include_once("../../../uhbs_config.php");
			$this->dbconn_read = isop_dbconn();
			$this->dbconn_active = $this->dbconn_read;
			return isop_dbconn();
			
		}
		return $this->dbconn_active;
	}
	
	/**
	 * serve result
	 */
	public function serve(service_result $res) {
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
		
		//echo "we got so far ...";
		
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
		$params = array();
		foreach($api->param as $ix => $p) {
			
			// fetch parameters, GET always wins over POST
			$value = NULL;
			if(isset($_POST[$p->name])) 
				$value = $_POST[$p->name];
			else if(isset($_GET[$p->name])) 
				$value = $_GET[$p->name];
				
			// parameter missing?
			if ($value === NULL) {
				$s->error(4, "Parameter ". $p->name ." missing!"); // exit 
			}
			
			// if this is an object, we need to decode it first
			if ($p->type == service_parameter::TYPE_OBJECT) {
				$value = json_decode($value);
				var_dump($value);
			}
			
			// type checking
			$valid = $p->validate($value);
			if (!$valid) 
				$s->error(5, "Parameter ". $p->name .", wrong type!"); // exit 
			
			// type casts of input values
			$params[$p->name] = $value;
			switch ($p->type) {
				case service_parameter::TYPE_BOOL:
					$params[$p->name] = (strtolower($value) == "true" || $value == 1) ? 
						true : false;
					break;
				case service_parameter::TYPE_INT:
					$params[$p->name] = (int) $value;
					break;
				case service_parameter::TYPE_FLOAT:
					$params[$p->name] = (float) $value;
					break;
			}
		}
		//var_dump($params);
		
		// execute function
		$ret = call_user_func_array(array($s, "call_".$fn), $params);
		
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

/*
class service_model {
	protected $fields = null;
	protected $validations = null;
}

class service_model_field {

}
*/

?>
