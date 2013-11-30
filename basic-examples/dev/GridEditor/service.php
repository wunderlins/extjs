<?php

include(dirname(__FILE__) . "/service_basic.php");

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
		
		$this->add_api(array(
			"fn" => "get_item",
			"param" => array(),
			"return" => service_data::TYPE_RECORD,
			"desc" => "Returns one record"
		));
		
		parent::__construct($config);
	}
	
	/**
	 * api get list of items
	 */
	public function call_get_list($sort, $dir, $start, $count) {
		$data = new service_data(service_data::TYPE_RECORDSET);
	
		$data->set_records(array(
			array("name" => "name1", "value" => "value1"),
			array("name" => "name2", "value" => "value2"),
			array("name" => "name3", "value" => "value3"),
			array("name" => "name4", "value" => "value4")
		));
		
		return $data;
	}
	
	public function call_get_item() {
		$data = new service_data(service_data::TYPE_RECORD);
		$data->set_record(array("name" => "name1", "value" => "value1"));
		
		return $data;
	}
}

// run the service's main method.
service::main();

// test validation


?>
