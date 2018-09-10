<?php
/**
 * Author: Andre Sieverding
 * Date: 05.09.2018
 */

if (file_exists('./temp/' . basename($_POST['file']))) {
	echo (int)file_get_contents('./temp/' . basename($_POST['file']));
} else {
	echo 0;
}
