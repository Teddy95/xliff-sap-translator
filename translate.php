<?php
/**
 * Author: Andre Sieverding
 * Date: 05.09.2018
 */

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

// Read files from source file directory
$files = glob('./src/*.xlf');

foreach ($files as $file) {
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
			}
		}
	}

	// Save new xml structure into file
	$output = $xmlSource->asXML();
	$newFile = './target/' . basename($file);
	$fileHandle = fopen($newFile, 'w');
	fwrite($fileHandle, $output);
	fclose($fileHandle);
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Translate</title>
		<link rel="stylesheet" type="text/css" href="./assets/bootstrap.min.css" />
	</head>
	<body>
		<div class="container">
			<br />
			<a class="btn btn-primary" href="./view.php">Ergebnisse anzeigen...</a>
		</div>
	</body>
</html>
