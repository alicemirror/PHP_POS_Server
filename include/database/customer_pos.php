<?php
/**
 * customer POS table fields. Every POS installation has a single associated customer.
 * A physical customer can own more than one POS so the customer_pos table includes the
 * customer ID code.
 */

 // Table name
define ("DB_TABLE_CUSTOMER_POS", "customer_pos");

// Table fields
define ("CUSTOMER_ID", "customer_id");
define ("POS_ID", "pos_id");
define ("POS_NAME", "name");
define ("POS_COMPANY", "company");
define ("POS_ADDRESS1", "address1");
define ("POS_ADDRESS2", "address2");
define ("POS_TOWN", "town");
define ("POS_COUNTRY", "country");
define ("POS_ZIP", "zip");
define ("POS_STATE", "state");
define ("POS_VAT", "vat");
define ("POS_EMAIL", "email");
define ("POS_PHONE", "phone");
define ("POS_SKYPE", "skype");

?>