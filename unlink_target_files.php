<?php
/**
 * Author: Andre Sieverding
 * Date: 19.09.2018
 */

$files = glob('./target/*.xlf');

foreach ($files as $file) {
	unlink($file);
}
