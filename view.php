<?php
/**
 * Author: Andre Sieverding
 * Date: 05.09.2018
 */

// Read files from source file directory
$files = glob('./target/*.xlf');
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Translate</title>
		<link rel="stylesheet" type="text/css" href="./assets/bootstrap.min.css" />
	</head>
	<body>
		<table class="table table-striped table-dark">
			<thead>
				<tr>
					<th>Quellsprache</th>
					<th>Zielsprache</th>
					<th>Max. Länge</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$fileCount = 1;
				foreach ($files as $file) {
					?>
					<tr>
						<td colspan="3"><b>File <?=$fileCount++;?> - <?=substr($file, 8);?></b></td>
					</tr>
					<?php
					// Get xml source from file
					$xmlSource = new SimpleXMLElement(file_get_contents($file));

					for ($i = 0, $j = count($xmlSource->file); $i < $j; $i++) {
						$srcLang = $xmlSource->file[$i]->attributes()->{'source-language'};
						$targetLang = $xmlSource->file[$i]->attributes()->{'target-language'};

						for ($n = 0, $m = count($xmlSource->file[$i]->body->{'trans-unit'}); $n < $m; $n++) {
							// Extract max char width, source text and target text from xlm structure
							$maxWidth = $xmlSource->file[$i]->body->{'trans-unit'}[$n]->attributes()->maxwidth;
							$source = $xmlSource->file[$i]->body->{'trans-unit'}[$n]->source;
							$target = trim($xmlSource->file[$i]->body->{'trans-unit'}[$n]->target);
							?>
							<tr>
								<td><?=$source;?></td>
								<td><?=$target;?></td>
								<td><?=$maxWidth;?></td>
							</tr>
							<?php
						}
					}
				}
				?>
			</tbody>
		</table>
	</body>
</html>
