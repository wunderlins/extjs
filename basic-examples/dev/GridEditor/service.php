<?php

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
 * Standard Error message for json data stores
 */
class service_error {
	protected $code    = 0;
	protected $message = "";
	
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

	function __construct(int $type) {
		if($type == self::TYPE_RECORDSET) {
			$this->num = 0;
		}
		$this->type = $type;
	}
	
	public function add_record(array $rec) {
		if ($this->type == self::TYPE_RECORD)
			return false;
		
		$this->data[] = $rec;
		$this->num++;
		return true;
	}

	public function set_record(array $rec) {
		if ($this->type == self::TYPE_RECORDSET)
			return false;
		
		$this->data = $rec;
		$this->num = null;
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
	
	function __construct($name, $type) {
		$this->name = $name;
		$this->type = $type;
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
 * TODO: Put this in a separate class together with the rest of basic classes.
 */
class service_basic {
	
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
	 * compile std JSon response
	 * TODO: implementation
	 */
	protected function response(service_data $data, service_error $error) {;}
	
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
	 * run an api function
	 *
	 * TODO: implementation, check if method is in top down class, must b
	 */
	protected function call() {
		// validate result
		// send response
	}
	
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
			// TODO: serve JSon error to client
			die("unhandled error, no FN param");
		}
		//echo $fn;
		
		// if we have an fn parameter, check if a method in this class called 
		// "call_".fn exists. if not, throw error sensible message
		if (!method_exists($s, "call_" . $fn)) {
			// TODO: 
			die("unhandled error, method does not exist");
		}
		
		//echo "we got so far ...";
		
		// validate parameters, sanitize input
		//
		// get metadata aboutthis api call, check if all parameters are submitted 
		// and if their data type is apropriate
		// 
		// FIXME: ended here, check if the api call's parameters are valid.
		
		
		// execute function
	}
}

/**
 * The service
 *
 * every public method with a prefix "call_" in this class is callable as 
 * service method via http. The method parameters should be defined in it's 
 * own class.
 *
 * the fn GET/POST parameter defines the method to be called. "call_" is 
 * appended to fn for searching of an appropriate method.
 */
class service extends service_basic {
	function __construct($config = array()) {
		// describe the service
		$this->add_api(array(
			"fn" => "get_list",
			"param" => array(
				"sort"  => service_parameter::TYPE_STRING,
				"dir"   => service_parameter::TYPE_STRING,
				"start" => service_parameter::TYPE_INT,
				"count" => service_parameter::TYPE_INT
			),
			"return" => service_data::TYPE_RECORDSET,
			"desc" => "Returns a recordset of items"
		));
		
		parent::__construct($config);
	}
	
	/**
	 * api get list of items
	 */
	public function call_get_list($sort, $dir, $start, $count) {
		;
	}
}

service::main();

/*
class a extends enum {
	const C0 = 0;
	const C1 = 1;
	const C2 = 2;
	public $a = 0;
}

$b = new a();
var_dump($b->ENUM["C2"]);
var_dump($b->a);
*/

// $r = new ReflectionClass('service_parameter'); print_r($r->getConstants());

?>
