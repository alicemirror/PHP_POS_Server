<?php
/**
 * receipts table fields. This table contains the header records of the
 * receipts that includes for every receipt header one or more receipt
 * item record.
 */

 // Table name
define ("DB_TABLE_RECEIPTS", "receipts");

// Table fields
define ("RECEIPT_ID", "id");
define ("POS_ID", "pos_id");
define ("RECEIPT_DATE", "date");
define ("RECEIPT_AMOUNT", "amount");
define ("RECEIPT_COUNTER", "counter");
define ("RECIPT_ACTIVE", "active");

// Field aliases
define ("RECPT_DEVICE_ID", "receipt_id");
define ("RECPT_POS", "receipt_pos");
define ("RECPT_DATE", "receipt_date");
define ("RECPT_AMOUNT", "receipt_amount");
define ("RECPT_COUNTER", "receipt_counter");
define ("RECPT_STATUS", "receipt_active");

?>