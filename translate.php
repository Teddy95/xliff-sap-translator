<?php
/**
 * Author: Andre Sieverding
 * Date: 05.09.2018
 */

// Deactivate error reporting
/*error_reporting(0);
ini_set('display_errors', 0);*/

// Ignoring user abort, so the script can be executed completly
//ignore_user_abort(true);

// Deactivate timeout
set_time_limit(0);

// Function for translating source text using Google Translate API / DeepL API
function translate ($sourceText, $sourceLanguage, $targetLanguage, $maxWidth) {
	$origEncoding = mb_detect_encoding($sourceText);
	$sourceText = mb_convert_encoding($sourceText, 'UTF-8');

	/************************************************************************/
	/******** BEI AKTIVIERUNG SCHLEIFE AUF EINEN DURCHLAUF SETZEN!!! ********/
	/*$apiLink = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=" . $sourceLanguage . "&tl=" . $targetLanguage . "&dt=t&q=" . urlencode($sourceText);
	$apiObject = json_decode(file_get_contents($apiLink));
	$target = $apiObject[0][0][0];*/
	/************************************************************************/
	$target = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.';
	$target = mb_convert_encoding($target, $origEncoding);

	if (strlen($target) > $maxWidth) {
		$target = substr($target, 0, $maxWidth);
	}

	return $target;
}

if (isset($_POST['file']) && !empty($_POST['file']) && isset($_POST['apikey']) && !empty($_POST['apikey']) && file_exists($_POST['file'])) {
	// Read files from source file directory
	$file = $_POST['file'];

	// Get xml source from file
	$xmlSource = new SimpleXMLElement(file_get_contents($file));

	for ($i = 0, $j = count($xmlSource->file); $i < $j; $i++) {
		$srcLang = (string)$xmlSource->file[$i]->attributes()->{'source-language'};
		$targetLang = (string)$xmlSource->file[$i]->attributes()->{'target-language'};

		for ($n = 0, $m = count($xmlSource->file[$i]->body->{'trans-unit'}); $n < $m; $n++) {
			// Extract max char width, source text and target text from xlm structure
			$maxWidth = (int)$xmlSource->file[$i]->body->{'trans-unit'}[$n]->attributes()->maxwidth;
			$source = $xmlSource->file[$i]->body->{'trans-unit'}[$n]->source;
			$target = trim($xmlSource->file[$i]->body->{'trans-unit'}[$n]->target);

			/*if (isset($xmlSource->file[$i]->body->{'trans-unit'}[$n]->target)) {
				$xmlSource->file[$i]->body->{'trans-unit'}[$n]->target->addChild('target');
			}*/

			// Translate source
			if (empty($target)) {
				$target = translate($source, $srcLang, $targetLang, $maxWidth);

				// Write down new translated text back into xml structure
				$xmlSource->file[$i]->body->{'trans-unit'}[$n]->target = $target;
				$xmlSource->file[$i]->body->{'trans-unit'}[$n]->target->addAttribute('state', 'needs-review-translation');

				// Write progessstatus into text file in /temp directory
				if (file_exists('./temp/all.txt')) {
					$progstatAll = (int)file_get_contents('./temp/all.txt');
				} else {
					$progstatAll = 0;
				}

				if (file_exists('./temp/' . explode('.', basename($file))[0] . '.txt')) {
					$progstat = (int)file_get_contents('./temp/' . explode('.', basename($file))[0] . '.txt');
				} else {
					$progstat = 0;
				}

				$progstatAll++;
				$progstat++;
				$progFileAll = './temp/all.txt';
				$progFileHandleAll = fopen($progFileAll, 'w');
				fwrite($progFileHandleAll, (string)$progstatAll);
				fclose($progFileHandleAll);
				$progFile = './temp/' . explode('.', basename($file))[0] . '.txt';
				$progFileHandle = fopen($progFile, 'w');
				fwrite($progFileHandle, (string)$progstat);
				fclose($progFileHandle);
			}
		}
	}

	// Save new xml structure into file
	$output = $xmlSource->asXML();
	$newFile = './target/' . basename($file);
	$fileHandle = fopen($newFile, 'w');
	fwrite($fileHandle, $output);
	fclose($fileHandle);

	echo 1;
} else {
	echo 0;
}
