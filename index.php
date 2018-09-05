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
			<a class="btn btn-primary" href="./translate.php">Übersetzung beginnen...</a>
			<a class="btn btn-secondary" href="./view.php">Ergebnisse anzeigen...</a>
			<br />
			<br />
			<?php
			$files = glob('./src/*.xlf');
			$chars = 0;
			$texts = 0;

			foreach ($files as $file) {
				// Get xml source from file
				$xmlSource = new SimpleXMLElement(file_get_contents($file));

				for ($i = 0, $j = count($xmlSource->file); $i < $j; $i++) {
					for ($n = 0, $m = count($xmlSource->file[$i]->body->{'trans-unit'}); $n < $m; $n++) {
						$source = $xmlSource->file[$i]->body->{'trans-unit'}[$n]->source;
						$target = trim($xmlSource->file[$i]->body->{'trans-unit'}[$n]->target);

						if (empty($target)) {
							$texts++;
							$chars += strlen($source);
						}
					}
				}
			}

			$costs = ($chars / 1000000) * 20;
			?>
			<div class="row">
				<div class="col">
					<h3>Zu übersetzende Dateien:</h3>
					<ul>
						<?php
						foreach ($files as $file) {
							echo "<li>" . $file . "</li>";
						}
						?>
					</ul>
				</div>
				<div class="col">
					<h3>Übersetzungsinfo:</h3>
					<p>
						Anzahl Texte: <b><?=number_format($texts, 0, ',', '.');?></b><br />
						Anzahl Zeichen: <b><?=number_format($chars, 0, ',', '.');?></b>
					</p>
					<p>
						Kosten ca.: <b><?=number_format($costs, 2, ',', '.');?></b> EUR (20,00 € / 1 Mio. Zeichen)
					</p>
				</div>
			</div>
		</div>
	</body>
</html>
