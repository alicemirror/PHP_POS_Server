	<?php
	
	// -----------------------------------------------------------------------
	//	Init and constants
	// -----------------------------------------------------------------------
	
	$DEBUG = 0;
	
	// Error handler intialization
	set_error_handler("customError");
	// Register the autoloader
	spl_autoload_register('classLoader');

	$JSON_HEADER = 'Content-type: application/json';
	
	// Parameter names
	$ACTION ='action';			// Action type
	$LICENSE_CODE = 'kcode';	// License code
	$PRODUCT_CODE = 'kproduct';	// Produce code
	$DEVICE_UUID = 'uuid';		// Device UUID
	$DEVICE_IMEI = 'imei';		// Device IMEI (if any)
	$DEVICE_LAN = 'wlan_mac';	// Device MAC Address
	$DEVICE_BT = 'bt_address';	// Device Bt address
	
	// Command actions
	// Keyword passed to the action name.
	$cmdGlobal = 'check';		// Check all the licenses for a user
	$cmdProduct = 'product';	// Check for the user/product license
	$cmdUser = 'user';			// Check the user profile
	$cmdLicenses = 'licenses';	// Check all the licenses for a product
	
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
		
		// Instantiates the license ckecking class
		$check = new CheckLicenses();
		
		// -----------------------------------------
		// Check for the requested action
		// -----------------------------------------
		
		// Check all the licenses for the user
		if( $command === $cmdGlobal ) {
			if( false == isset($_GET[$LICENSE_CODE]) )
				badRequest("License key (user) missing");

			$license = $_GET[$LICENSE_CODE];

			if( $DEBUG == 1 )
				echo 'execute: Check all the licenses for the user ' . $license . ' <br>';
			
			// Set the header
			header($JSON_HEADER);

			$jContent = $check->checkGlobal($license);
			print $jContent;

		}
		
		// Check the user license for a product
		elseif ( $command == $cmdProduct ) {
			if( false == isset($_GET[$LICENSE_CODE]) )
				badRequest("License key (user) missing");

			$license = $_GET[$LICENSE_CODE];

			if( false == isset($_GET[$PRODUCT_CODE]) )
				badRequest("Product key missing");

			$product = $_GET[$PRODUCT_CODE];
	
			if( false == isset($_GET[$DEVICE_UUID]) )
				badRequest("UUID missing");

			$uuid = $_GET[$DEVICE_UUID];

			if( false == isset($_GET[$DEVICE_IMEI]) )
				badRequest("IMEI missing");

			$imei = $_GET[$DEVICE_IMEI];

			if( false == isset($_GET[$DEVICE_LAN]) )
				badRequest("MAC Address missing");

			$mac = $_GET[$DEVICE_LAN];

			if( false == isset($_GET[$DEVICE_BT]) )
				badRequest("Bt Address missing");

			$bluetooth = $_GET[$DEVICE_BT];
	
			if( $DEBUG == 1 )
				echo 'execute: Check the user license ' 
				. $license . ' for the product ' . $product . ' <br>';
			
			// Set the header
			header($JSON_HEADER);

			// Check for the license
			$licenseContent = $check->checkProduct(	$license, $product );
			// Check for the user
			$userContent = $check->checkUser($license);
			// Check for the device
			$deviceContent = $check->checkDevice( $uuid, $imei, $mac, $bluetooth );

			$toJson = array ($licenseContent, $userContent, $deviceContent);
			
			print json_encode($toJson);
			// print $jContent;

		} 
		
		// Check che user profile
		elseif ( $command == $cmdUser ) {
			if( false == isset($_GET[$LICENSE_CODE]) ) {
				badRequest('License code missing');
				exit;
			}
			
			$license = $_GET[$LICENSE_CODE];
			
			// Set the header
			header($JSON_HEADER);

			$jContent = $check->checkUser($license);
			print $jContent;

		}
		
		// Check all the licenses for a specific product
		elseif ( $command == $cmdLicenses ) {
			if( false == isset($_GET[$PRODUCT_CODE]) )
				badRequest("Product key missing");

			$product = $_GET[$PRODUCT_CODE];

			if( $DEBUG == 1 )
				echo 'execute: Check all the licenses for the product ' 
				. $product . ' <br>';
			
			// Set the header
			header($JSON_HEADER);

			$jContent = $check->checkLicense($product);
			// If the content is null, return an error
			print $jContent;

		}
	} // Action processing
	
	// Class loadedr
	function classLoader($className) {
		require_once dirname(__FILE__) ."/Classes/". $className . '.php';
	}
	
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
	
?>

