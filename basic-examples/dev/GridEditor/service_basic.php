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
	
	protected function is_bool($value) {
		$ok = false;
		if ($value === 1 || $value == "1" || $value === true ||
		    $value === 0 || $value == "0" || $value === false||
		    strtolower($value) == "true" || strtolower($value) == "false") {
			$ok = true;
		}
		return $ok;
	}
	
	protected function is_int($value) {
		if (strlen((int) $value) == strlen($value) &&
		   (int) $value . "" == $value) {
			return true;
		}
		return false;
	}
	
	protected function is_float($value) {
		if (strlen((float) $value) == strlen($value) && 
		   (float) $value . "" == $value) {
			return true;
		}
		return false;
	}
	
	protected function is_array($value) {
		return is_array($value);
	}
	
	protected function is_object($value) {
		return is_array($value);
	}
	
	public function validate($value) {
		switch($this->type) {
			case 1: // bool
				return $this->is_bool($value);

			case 2: // int
				return $this->is_int($value);
			
			case 3: // float
				return $this->is_float($value);
			
			case 4: // string
				return ($value && !$this->is_object($value) && !$this->is_array($value));

			case 5: // array
				return $this->is_array($value);
			
			case 6: // object
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
 *
 * TODO: Put this in a separate file together with the rest of basic classes.
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
		header("Content-type: application/javascript");
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
		if (!$api) {
			$s->error(3, "no api description"); // exit 
		}
		
		// validate parameters, sanitize input
		//
		// get metadata about this api call, check if all parameters are submitted 
		// and if their data type is apropriate
		$params = array();
		foreach($api->param as $ix => $p) {
			
			$value = NULL;
			if(isset($_GET[$p->name])) 
				$value = $_GET[$p->name];
			else if(isset($_GET[$p->name])) 
				$value = $_GET[$p->name];
			
			// parameter missing?
			if ($value === NULL) {
				$s->error(4, "Parameter ". $p->name ." missing!"); // exit 
			}
			
			// TODO: type checking
			//echo "$value : " . $p->type . " ";
			//var_dump((int) $value);
			//var_dump($value);
			$valid = $p->validate($value);
			//var_dump($valid);
			if (!$valid) 
				$s->error(5, "Parameter ". $p->name .", wrong type!"); // exit 

			$params[$p->name] = $value;
		}
		
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
