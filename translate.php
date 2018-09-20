<?php
/**
 * Author: Andre Sieverding
 * Date: 19.09.2018
 */

// Deactivate error reporting
error_reporting(0);
ini_set('display_errors', 0);

// Ignoring user abort, so the script can be executed completly
ignore_user_abort(true);

// Deactivate timeout
set_time_limit(0);

// Set HTTP header to UTF-8
header('Content-Type: text/html; charset=utf-8');

// Function for translating source text using DeepL Pro API
function translate ($sourceText, $sourceLanguage, $targetLanguage, $maxWidth) {
	$langCodes = array(
		'de' => 'DE',
		'deDE' => 'DE',
		'de-DE' => 'DE',
		'en' => 'EN',
		'enUS' => 'EN',
		'en-US' => 'EN',
		'fr' => 'FR',
		'frFR' => 'FR',
		'fr-FR' => 'FR',
		'es' => 'ES',
		'esES' => 'ES',
		'es-ES' => 'ES',
		'it' => 'IT',
		'itIT' => 'IT',
		'it-IT' => 'IT',
		'nl' => 'NL',
		'nlNL' => 'NL',
		'nl-NL' => 'NL',
		'pl' => 'PL',
		'plPL' => 'PL',
		'pl-PL' => 'PL'
	);

	$apiLink = "https://api.deepl.com/v1/translate?auth_key=" . $_POST['apikey'] . "&text=" . urlencode($sourceText) . "&source_lang=" . $langCodes[$sourceLanguage] . "&target_lang=" . $langCodes[$targetLanguage];
	//$apiLink = "http://localhost/xliff/sandbox_api.php?string=" . urlencode($sourceText); # API-Link for sandbox mode
	$apiCallback = file_get_contents($apiLink);
	
	if ($apiCallback != '') {
		$apiObject = json_decode($apiCallback);
		$target = $apiObject->translations[0]->text;

		if (mb_strlen($target, 'utf-8') > $maxWidth) {
			$target = substr($target, 0, $maxWidth);
		}

		return $target;
	} else {
		return false;
	}
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

				if ($target === false) {
					echo 0;
					die();
				}

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
