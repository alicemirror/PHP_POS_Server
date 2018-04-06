	<?php
	
	// -----------------------------------------------------------------------
	//	Init and constants
	// -----------------------------------------------------------------------
	
	// Class loadedr
	function classLoader($className) {
		require_once dirname(__FILE__) ."/Classes/". $className . '.php';
	}
	spl_autoload_register('classLoader');

	$DEBUG = 0;
	
	// Error handler intialization
	set_error_handler("customError");
	// Register the autoloader
	spl_autoload_register("classLoader");

	// The sent json data header name
	$JSON_HEADER = "jsonData";
	// Error message
	$ERROR_NO_ERROR = "OK";
	$ERROR_WRONG_ACTION = "No action specified";
	$ERROR_WRONG_COMMAND = "Incorrect command";
	$ERROR_DATA = "Malformed json string. Impossible to decode";
	$ERROR_EMPTY = "Empty json string";
	$ERROR_MISSING_HEADER = "The data header is missing";
	
	// License types (must reflect the ids in the db table license_types
	// WARNING! If types changes shouldb e updated also the types table and the
	// CreateLicenses class.
	$LICENSE_TYPE_UNLIMITED = 1;
	$LICENSE_TYPE_TEMPORARY = 2;
	$LICENSE_TYPE_INCREMENTAL = 3;
	$LICENSE_TYPE_FOLLOWUP = 4;
	
	// Parameters	
	$ACTION = "action";			// Action command

	// Command actions
	$cmdReg = "register";		// Register a product license
	$cmdUnreg = "unregister";	// Unregister a product license
	$cmdUpdate = "update";	// Unregister a product license
	
	// Data array sections from client section definitions (item numbers)
	$DATA_ARRAY_S1 = 0;
	$DATA_ARRAY_S2 = 1;
	
	// -----------------------------------------------------------------------
	// Page execution
	// -----------------------------------------------------------------------
	
	if( false == isset($_GET[$ACTION])){ 
		badRequest($ERROR_WRONG_ACTION);
	}
	else {
		// -----------------------------------------
		// Go ahead processing the requested action
		// -----------------------------------------
		$command = $_GET[$ACTION];
		$headers = apache_request_headers();
		$dataFromClient = array();
		$json = "";
		// Create an instance of the UpdateLicenses class	
		$updater = new UpdateLicenses();
	
		// Exract data from the header	
		foreach ($headers as $header => $value) {
			// Extract the json string only
			if( $header == $JSON_HEADER ) {
				$json = $value;
			} // Data extraction
		} // Headers loop
		
		// Convert json data to array if it is not empty, else
		// return error.
		if($json !== "") {
			$dataFromClient = json_decode($json, true);
			if($dataFromClient == NULL) {
				badRequest($ERROR_DATA);
			} // malformed json
			elseif($dataFromClient == FALSE) {
				badRequest($ERROR_EMPTY);
			} // empty json
		}
		else {
			badRequest($ERROR_MISSING_HEADER);
		} // Heaer is missing
		
		IF($DEBUG == 1) {
			print "== JSON Decoded string START ==";
			print var_dump($dataFromClient);
			print "== JSON Decoded string END ==";
		}
		
	/* ...........................................................................
	_NOTE_ At this point, the $dataFromClient data array contains two fields
		where [0] are the data for the license record update and [1] are the
		data for the associated device update. The following is and example
		of the two array and their respective field names as are stored in the 
		$dataFromClient array.
		
	   [0]=>
	   array(9) {
		 ["licenseID"]=>string(3) "108"
		 ["userID"]=>string(3) "138"
		 ["updateDate"]=>string(10) "2013-01-05"
		 ["active_licenses"]=>string(1) "1"
		 ["suspend"]=>string(10) "0002-11-30"
		 ["is_active"]=>string(1) "1"
		 ["expire"]=>string(10) "2013-01-03"
		 ["productID"]=>string(1) "1"
		 ["licenseCode"]=>string(1) "4"
	   }
	   
	   [1]=>
	   array(19) {
		 ["deviceID"]=>string(2) "-1"
		 ["license_id"]=>string(3) "108"
		 ["license_number"]=>string(1) "1"
		 ["user_id"]=>string(3) "138"
		 ["updateDate"]=>string(28) "Sat Jan 05 14:43:59 CET 2013"
		 ["UUID"]=>string(36) "aad6b5ef-cace-3760-b61e-18ef61345cef"
		 ["IMEI"]=>string(15) "359220039818266"
		 ["WLANMAC"]=>string(17) "b0:ee:45:08:b4:ff"
		 ["BTADDRESS"]=>NULL
		 ["SDK"]=>string(2) "15"
		 ["ANDROID"]=>string(5) "4.0.3"
		 ["BOARD"]=>NULL
		 ["BOOTLOADER"]=>NULL
		 ["BRAND"]=>string(4) "alps"
		 ["DEVICE"]=>string(24) "e1809c_v75_gq1008_ov5647"
		 ["FINGERPRINT"]=>string(20) "8934076100122865180f"
		 ["MANUFACTURER"]=>string(4) "alps"
		 ["MODEL"]=>string(24) "e1809c_v75_gq1008_ov5647"
		 ["PRODUCT"]=>string(24) "e1809c_v75_gq1008_ov5647"
	   }
	 }
	........................................................................... */

		// -----------------------------------------
		// REGISTER NEW LICENSE
		// -----------------------------------------
		if( $command === $cmdReg ) {		
			// If the license type is follow-up, there is only one license
			// and the new registred device may need to replace a different one
			// already existing on the database, associated to the current license
			// and user data.
			if(intval($dataFromClient[$DATA_ARRAY_S1][$updater->DATA_S1_FLD_TYPE_CODE]) === $LICENSE_TYPE_FOLLOWUP) {
			// --------------------- FOLLOW-UP --------------------
				// Search the device ID unique record if it exists
				$devId = $updater->searchDeviceByLicense(
				$dataFromClient[$DATA_ARRAY_S2][$updater->DATA_S2_FLD_LICENSE_ID], 
				$dataFromClient[$DATA_ARRAY_S2][$updater->DATA_S2_FLD_LICENSE_NUM], 
				$dataFromClient[$DATA_ARRAY_S2][$updater->DATA_S2_FLD_USER_ID] );

				// Follow-up licenses accept only one registered device per-license so check if a
				// device with the license and users credentials still exist in the database.
				if($devId >= 0) {
					// Already exists a registered device for this license. The record will be
					// overwritten with the retrieved ID updating the device record data 
					$dataFromClient[$DATA_ARRAY_S2][$updater->DATA_S2_FLD_UPDATE] = Date("Y-m-d H:i:s", time());
					// Assign the retrieved device id (maybe already set but we overwrite anyway)
					$dataFromClient[$DATA_ARRAY_S2][$updater->DATA_S2_FLD_DEVICE_ID] = $devId;
					// Create the new device record
					$updater->updateDevice($dataFromClient[$DATA_ARRAY_S2]);
				} // already existing device
			else {
				// New device registration for this license ID.
				$dataFromClient[$DATA_ARRAY_S2][$updater->DATA_S2_FLD_UPDATE] = Date("Y-m-d H:i:s", time());
				$updater->createDevice($dataFromClient[$DATA_ARRAY_S2]);
				} // Device record is not found and should be created
			} // Follow-up
			else {
			// --------------------- OTHER LICENSES --------------------
				// Check if the device still exist
				if(intval($dataFromClient[$DATA_ARRAY_S2][$updater->DATA_S2_FLD_DEVICE_ID]) < 0) {
					// The device should be created
					$dataFromClient[$DATA_ARRAY_S2][$updater->DATA_S2_FLD_UPDATE] = Date("Y-m-d H:i:s", time());
					$updater->createDevice($dataFromClient[$DATA_ARRAY_S2]);
				} // Device create
				else {
					// The device registration / change date is updated on the still existing record.
					// The only changed element will be the update date field and some device parameters
					// that amay change (e.g. sim code).
					$updater->updateDevice($dataFromClient[$DATA_ARRAY_S2]);
				} // Device update
			} // Other licenses
		} // Register
		// -----------------------------------------
		// UNREGISTER & UPDATE A LICENSE
		// -----------------------------------------
//		elseif ( ($command === $cmdUnreg) || ($command === $cmdUpdate) ) {
		elseif ( $command === $cmdUpdate ) {
			// It is assumed that in this case the device is already present
			// for sure, so it will be updated with the last information from
			// the client
			$updater->updateDevice($dataFromClient[$DATA_ARRAY_S2]);
		} 
		// -----------------------------------------
		// WRONG OR EMPTY COMMAND. Do nothing.
		// -----------------------------------------
		else {
			// Returns error and exit
			badRequest($ERROR_WRONG_COMMAND);
		} // Wrong command

		// -----------------------------------------
		// Update the license record.
		// -----------------------------------------
		$updater->updateLicense($dataFromClient[$DATA_ARRAY_S1]);
		
		// Ending page
		positiveResponse();
				
	} // Action processing
	
	// ===================================== RESPONSE FUNCTIONS
	// Error manager method
	function customError($errno, $errstr){
		header("HTTP/1.0 500 Internal Server Error");
		print "Error: [$errno] $errstr";
		exit;
	}
	
	// Bad request header
	function badRequest($string){
		header("HTTP/1.0 400 Bad Request");
		print "Error: [$string]";
		exit;
	}
	
	// Correct response header    
	function positiveResponse(){
		header("HTTP/1.0 202 Accepted");
		print "OK";
		exit;
	}
	
?>

