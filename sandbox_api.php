<?php
/**
 * Author: Andre Sieverding
 * Date: 19.09.2018
 */

// Deactivate error reporting
error_reporting(0);
ini_set('display_errors', 0);

// Set HTTP header to UTF-8
header('Content-Type: text/html; charset=utf-8');

$JSON->translations = array();
$JSON->translations[0]->text = $_GET['string'];
echo json_encode($JSON);
