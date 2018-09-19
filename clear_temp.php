<?php
/**
 * Author: Andre Sieverding
 * Date: 19.09.2018
 */

$files = glob('./temp/*.txt');

foreach ($files as $file) {
	unlink($file);
}
