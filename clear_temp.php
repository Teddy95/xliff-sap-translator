<?php
/**
 * Author: Andre Sieverding
 * Date: 05.09.2018
 */

$files = glob('./temp/*.txt');

foreach ($files as $file) {
	unlink($file);
}
