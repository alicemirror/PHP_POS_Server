<?php

	/*
	 Retrieve the POS Customer profile based on the ID
	 
	 Usage: pos_profile?action=search&pos_id=nnn where nnn is the customer pos ID.
	*/
	
	// Class loadedr
	function classLoader($className) {
		require_once dirname(__FILE__) ."/Classes/". $className . '.php';
	}
	
	spl_autoload_register('classLoader');
	
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
	function positiveResponse($string){
		header("HTTP/1.0 202 Accepted");
		print "OK: [$string]";
		exit;
	}
	
	$DEBUG = 0;

	// The json header for posting data sent to the application client
	$JSON_HEADER = 'Content-type: application/json';
	
	// Parameter names
	$ACTION ='action';			// Action type
	$POS_ID = 'pos_id';			// POS ID

	// Command action(s)
	$cmdGet = 'get';		// Get the unqiue device definition, if any

	// -----------------------------------------------------------------------
	// Page execution
	// -----------------------------------------------------------------------
	
	if( false == isset($_GET[$ACTION])){ 
		badRequest("No action specified");
	}
	else {
		// -----------------------------------------
		// Go ahead processing the requested action
		// -----------------------------------------
		$command = $_GET[$ACTION];
		
		if( $DEBUG == 1 )
			echo 'Requested action: ' . $command . '<br>';
		
		// Check for the command parameters and retrieves the search keys
		if($command == $cmdGet) {
			// Pos ID
			if( false == isset($_GET[$POS_ID]))
				badRequest("POS ID not specified");
			else 
				$pos_id = $_GET[$POS_ID];
				
			// Instantiates the device manager class
			$manager = new PosManager();
			
			$toJson = $manager->searchPosCustomerProfile($pos_id);
			
			// Set the header
			header($JSON_HEADER);
			print json_encode($toJson);
	
		} // Command = search for POS ID
		
	} // Else executes command

?>