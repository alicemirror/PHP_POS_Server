<?php

// Include the database tables definitions
require_once dirname(__DIR__) . "/include/database/customers.php";
require_once dirname(__DIR__) . "/include/database/customer_pos.php";
require_once dirname(__DIR__) . "/include/database/daily_items.php";
require_once dirname(__DIR__) . "/include/database/departments.php";
require_once dirname(__DIR__) . "/include/database/products.php";
require_once dirname(__DIR__) . "/include/database/receipts.php";
require_once dirname(__DIR__) . "/include/database/receipt_items.php";

// Methods to manage the POS database
Class PosManager {

	// Global preferences instance. Includes  the database 
	// instance opened by the Globals class constructor
	private $prefs;
	// Local replica of the database instance
	private $db;
	
	private $DEBUG = 0;

	// The vendors field names
	private $customersFieldList = array(
		CUSTOMER_ID,
		CUSTOMER_NAME,
		CUSTOMER_COMPANY,
		CUSTOMER_ADDRESS,
		CUSTOMER_TOWN,
		CUSTOMER_COUNTRY,
		CUSTOMER_ZIP,
		CUSTOMER_STATE,
		CUSTOMER_VAT,
		CUSTOMER_EMAIL,
		CUSTOMER_PHONE,
		CUSTOMER_SKYPE
		);
	
	// The products field names
	private $customer_posFieldList = array(
		CUSTOMER_ID,
		POS_ID,
		POS_NAME,
		POS_COMPANY,
		POS_ADDRESS1,
		POS_ADDRESS2,
		POS_TOWN,
		POS_COUNTRY,
		POS_ZIP,
		POS_STATE,
		POS_VAT,
		POS_EMAIL,
		POS_PHONE,
		POS_SKYPE
		);
	
	// The classes field names
	private $daily_itemsFieldList = array(
		DAILYITEM_DATE,
		POS_ID,
		ITEM_ID,
		DEPARTMENT_ID,
		DAILYITEM_VALUE,
		DAILYITEM_ACTIVE
		);
	
	// The devices field names
	private $departmentsFieldList = array(
		DEPARTMENT_ID,
		POS_ID,
		DEPARTMENT_DESCRIPTION,
		DEPARTMENT_ACTIVE,
		);
	
	// The printer profiles field names	
	private $productsFieldList = array(
		PRODUCT_ID,
		POS_ID,
		PRODUCT_DEPARTMENT,
		PRODUCT_DESCRIPTION,
		PRODUCT_IMAGE,
		PRODUCT_PRICE,
		PRODUCT_ACTIVE
		);
		
	// The printer profiles field names	
	private $receiptsFieldList = array(
		RECEIPT_ID,
		POS_ID,
		RECEIPT_DATE,
		RECEIPT_AMOUNT,
		RECEIPT_COUNTER,
		RECIPT_ACTIVE
		);
		
	private $receipt_itemsFieldList = array(
		RECPT_ITEM_ID,
		POS_ID,
		RECPT_ITEM_RECPT_ID,
		RECPT_ITEM_SELL_PRICE
		);
	
	// Class loader method to manage the autoload
	private function loader($className) { 
		require_once dirname(__FILE__) ."/". $className . '.php'; 
	}
	
	// When the class is instantiated it is possible to pass a already
	// loaded prefs instance of the Globals class. If it is null (the default)
	// the Globals class is instantiated locally.
	public function __construct(Globals $prefs=null){
		// Register the autoload function
		spl_autoload_register(array($this, 'loader'));
		
		// Check for the already existing Globals class
		if($prefs == null) {
			$prefs= new Globals;
		}
		
        // Assigns the local instance of the preferences and database
		$this->prefs = $prefs;
		$this->db = $this->prefs->db;
	} // constructor
	
	/* ------------------------------------------------------------------------
		Get the products list for the required POS ID.
		Only the active products are loaded in the list.
	   ------------------------------------------------------------------------ */
	public function getProductsList($keyWord) {

		// Search by code
		$whereClause = ' WHERE (' . POS_ID . '=\'' . $keyWord . '\''
						. ' AND ' . PRODUCT_ACTIVE . '=\'1\')'
		;

		$q = 'SELECT ' . PRODUCT_ID .
			',' . POS_ID . 
			',' . PRODUCT_DEPARTMENT . 
			',' . PRODUCT_DESCRIPTION . 
			',' . PRODUCT_IMAGE . 
			',' . PRODUCT_PRICE . 
			',' . PRODUCT_ACTIVE . 

			' FROM ' . DB_TABLE_PRODUCTS . $whereClause;
			
		if($this->DEBUG == 1)
			echo 'getProductsList() query = ' . $q . '<br>';
		
		$pdoStatement = $this->db->prepare($q);
		$pdoStatement->execute();

		$r = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);

		return $r;
	}
	/* ------------------------------------------------------------------------
		Get the departments list for the required POS ID.
		If the list is empty (no departments) the application ignore the
		department organization and all the products are shown as part of the
		department '0' 
		Only the active departments are loaded in the list.
	   ------------------------------------------------------------------------ */
	public function getDepartmentsList($keyWord) {

		// Search by code
		$whereClause = ' WHERE (' . POS_ID . '=\'' . $keyWord . '\''
						. ' AND ' . DEPARTMENT_ACTIVE . '=\'1\')'
		;

		$q = 'SELECT ' . DEPARTMENT_ID .
			',' . POS_ID . 
			',' . DEPARTMENT_DESCRIPTION . 
			',' . DEPARTMENT_ACTIVE . 

			' FROM ' . DB_TABLE_DEPARTMENTS . $whereClause;
			
		if($this->DEBUG == 1)
			echo 'getDepartmentsList() query = ' . $q . '<br>';
		
		$pdoStatement = $this->db->prepare($q);
		$pdoStatement->execute();

		$r = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);

		return $r;
	}

	/* ------------------------------------------------------------------------
		Search the POS Customer profile based on the POS ID.
		The POS Customer ID - also called POS ID - is the field present int all
		the tables representing the top grouping of a specific POS / Shop records.
		Every POS records group always refers to a single POS ID. The POS
		application "knows" what is its POS identification because of the POS_ID
		in the licenses Extras field.
	   ------------------------------------------------------------------------ */
	public function searchPosCustomerProfile($keyWord) {

		// Search by code
		$whereClause = ' WHERE ' . POS_ID . '=\'' . $keyWord . '\'';

		$q = 'SELECT ' . CUSTOMER_ID .
			',' . POS_ID . 
			',' . POS_NAME . 
			',' . POS_COMPANY . 
			',' . POS_ADDRESS1 . 
			',' . POS_ADDRESS2 . 
			',' . POS_TOWN . 
			',' . POS_COUNTRY . 
			',' . POS_ZIP . 
			',' . POS_STATE . 
			',' . POS_VAT . 
			',' . POS_EMAIL . 
			',' . POS_PHONE . 
			',' . POS_SKYPE . 

			' FROM ' . DB_TABLE_CUSTOMER_POS . $whereClause;
			
		if($this->DEBUG == 1)
			echo 'searchPosCustomerProfile() query = ' . $q . '<br>';
		
		$pdoStatement = $this->db->prepare($q);
		$pdoStatement->execute();

		// Organize the record fields to a list before returning to create a json
		// object instead of a json array.
		$r = null;

		// If the query returned the record
		if( $row = $pdoStatement->fetch(PDO::FETCH_ASSOC) ) {
			// Loop on th fields array
			foreach ($this->customer_posFieldList as $key) {
                $r["$key"] = $row["$key"];
			} // Loop on fields
		} // Record found
		
		$pdoStatement->closeCursor();
		
		if( $r == null ) {
			$r[CUSTOMER_ID] = null;
			}
		
		return $r;
	}

	
}
?>