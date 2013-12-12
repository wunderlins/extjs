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
				new service_parameter("string", service_parameter::TYPE_STRING),
				new service_parameter("float",  service_parameter::TYPE_FLOAT),
				new service_parameter("bool",   service_parameter::TYPE_BOOL),
				new service_parameter("int",    service_parameter::TYPE_INT)
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
		
		
		// FIXME: implement better return description. Describe all 
		//        columns: name/type/format
		$this->add_api(array(
			"fn" => "get_op",
			"param" => array(),
			"return" => service_data::TYPE_RECORDSET,
			"desc" => "Returns a list of patients"
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
	
	public function call_get_op() {
		$data = new service_data(service_data::TYPE_RECORDSET);
		$sql ="
		SELECT 
		  CASE 
		    WHEN (A.KN_GCS=1 OR A.KN_AWAREN=1 OR A.KN_STROKE=1 OR A.KN_EXITUS=1 OR A.KA_MASK=1 OR A.KA_ITN=1 OR KA_PNEUMOTHO=1 OR A.KA_HYPOXIE=1 OR A.KA_ASPIRAT=1 OR KA_BRONCHO=1 OR A.KK_VASO=1 OR A.KK_ISCHAM=1 OR KK_BDSYS=1 OR A.KK_REAEL=1 OR A.KR_PARAST =1 OR A.KR_LOKAL=1 OR A.KR_ALLGEM=1 OR KI_HOSPI=1 OR A.KR_DURA=1 OR KR_LATOX=1 OR A.KI_ZAHN=1 OR A.KI_LASION=1 OR A.KI_HYPOT=1 OR a.KI_REINTUB=1 OR a.KI_IMCIPS=1 OR KK_VERZEXTUB=1 OR KK_INCIDENT=1 OR a.KI_QC=1 OR LENGTH(a.K_POSTKOMPL_TEXT) >= 1 OR LENGTH(a.KI_ANDERE) > 1 ) THEN 1 
		    ELSE 0 
		  END AS KOMPLIK, 
		  o.ID, q.ID as QID, q.STATUS, TO_DATE(o.OPDATUM, 'YYYYMMDD') OPDATUM, 
		  o.STMNAME, o.STMVORNAME, o.STMSEX, 
		  TO_DATE(o.STMGEBDAT, 'YYYYMMDD') STMGEBDAT, 
		  o.ABTLIEGTZIM, o.HOSKLASSE, 
		  c.NAME AS CHIR1 , o.ANA1, o.FACHDISZI, o.OPTEXT, o.FALLNR 
		FROM dato_op o LEFT OUTER JOIN data_qualiana q ON o.ID = q.IDOP 
		               LEFT OUTER JOIN dato_anacode a ON o.ID = a.IDOP 
		               LEFT outer JOIN conf_personal c ON o.CHIR1 = c.CODE 
		               LEFT outer JOIN conf_bereich b ON o.FACHDISZI = b.CODE 
		WHERE o.OPDATUM >= '20130302' AND o.OPDATUM < '20130322' AND (ALI_LAMONITOR = '0' OR ALI_LAMONITOR IS NULL) AND ( q.STATUS IS NULL OR q.STATUS = '0' OR q.STATUS = '1' ) 
		  AND o.FACHDISZI NOT IN ('AU', 'AP') AND o.ABTABT NOT IN ('C53') AND (o.DEL IS NULL OR o.DEL <> 'J') 
		ORDER BY o.STMNAME";
		
		$conn = $this->get_dbconn(false);
		$ret = $conn->Execute($sql);
		
		$r = $ret->getAssoc();
		$data->set_records($r);
		//print_r($r);
		//print(implode(",", array_keys($r[0])));
		return $data;
	}
}

// run the service's main method.
service::main();

// test validation


?>
