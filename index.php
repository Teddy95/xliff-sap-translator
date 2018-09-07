<?php
/**
 * Author: Andre Sieverding
 * Date: 05.09.2018
 */
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Translate</title>
		<link rel="stylesheet" type="text/css" href="./assets/bootstrap.min.css" />
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous" />
		<link rel="stylesheet" type="text/css" href="./assets/fileicons.css" />
		<script type="text/javascript" language="javascript" src="./assets/jquery-3.3.1.min.js"></script>
		<meta name="robots" content="noindex,nofollow" />
	</head>
	<body>
		<div class="container">
			<?php
			$files = glob('./src/*.xlf');
			$chars = 0;
			$texts = 0;

			if (count($files) > 0) {
				foreach ($files as $file) {
					// Get xml source from file
					$xmlSource = new SimpleXMLElement(file_get_contents($file));

					for ($i = 0, $j = count($xmlSource->file); $i < $j; $i++) {
						$srcLang = (string)$xmlSource->file[$i]->attributes()->{'source-language'};
						$targetLang = (string)$xmlSource->file[$i]->attributes()->{'target-language'};

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

				$costs = ($chars / 500) * 0.01;
				?>
				<div class="row">
					<div class="col-12">
						<br />
						<h1>XLIFF SAP Translator</h1>
						<button type="button" id="startTranslating" class="btn btn-primary"><i class="fa fa-language"></i> Übersetzung beginnen</button>
						<a class="btn btn-secondary" href="./view.php" target="_blank"><i class="fa fa-eye"></i> Ergebnisse anzeigen</a>
						<br />
						<br />
					</div>
					<div class="col">
						<h3>Zu übersetzende Dateien:</h3>
						<ul>
							<?php
							foreach ($files as $file) {
								echo "<li data-file='" . $file . "'>" . basename($file) . " <i class='fa fa-spinner file-wait'></i></li>";
							}
							?>
						</ul>
					</div>
					<div class="col">
						<h3>Übersetzungsinfo:</h3>
						<p>
							<?php
							$langs = array(
								'de' => 'Deutsch',
								'DE' => 'Deutsch',
								'deDE' => 'Deutsch',
								'de-DE' => 'Deutsch',
								'en' => 'Englisch',
								'EN' => 'Englisch',
								'enUS' => 'Englisch',
								'en-US' => 'Englisch',
								'fr' => 'Französisch',
								'FR' => 'Französisch',
								'frFR' => 'Französisch',
								'fr-FR' => 'Französisch'
							);
							?>
							Quellsprache: <b><?=$langs[$srcLang];?></b><br />
							Zielsprache: <b><?=$langs[$targetLang];?></b>
						</p>
						<p>
							Anzahl Texte: <b><?=number_format($texts, 0, ',', '.');?></b><br />
							Anzahl Zeichen: <b><?=number_format($chars, 0, ',', '.');?></b>
						</p>
						<p>
							Kosten ca.: <b><?=number_format($costs, 2, ',', '.');?></b> EUR (20,00 € / 1 Mio. Zeichen)
						</p>
						<hr />
						<p>
							<form>
								<div class="form-group">
									<label for="exampleInputEmail1"><b>DeepL Pro API Schlüssel</b></label>
									<input type="text" class="form-control" id="apikey" />
									<small class="form-text text-muted">Der API Schlüssel wird benötigt, um auf den Service von DeepL zuzugreifen!</small>
								</div>
								<div class="form-group form-check">
									<input type="checkbox" class="form-check-input" id="emptyTarget" checked="checked" />
									<label class="form-check-label" for="emptyTarget"><code>/target</code>-Verzeichnis vor dem Übersetzen leeren</label>
								</div>
							</form>
						</p>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="row">
					<div class="col-12">
						<br />
						<h1>XLIFF SAP Translator</h1>
						<p class="alert alert-info">
							Keine Dateien zum Übersetzen vorhanden!<br />
							Bitte <code>.xlf</code>-Dateien in das <code>/src</code> Verzeichnis legen.
						</p>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<script type="text/javascript" language="javascript">
			$(document).ready(function () {
				var files = [<?php
					for ($a = 0, $b = count($files); $a < $b; $a++) {
						$item = "'" . $files[$a] . "'";

						if ($a + 1 < $b) {
							$item .= ',';
						}

						echo $item;
					}
				?>];

				$('#startTranslating').off();
				$('#startTranslating').click(function () {
					if (confirm("Übersetzung starten? Dieser Vorgang kann nicht abgerochen werden und wird einige Zeit zur Ausführung benötigen!")) {
						if ($('#emptyTarget').prop('checked')) {
							$.ajax({
								url: './unlink_target_files.php',
								type: 'POST'
							});
						}

						files.forEach(function (element) {
							$('li[data-file="' + element + '"]').children('i').removeClass('fa-check file-ok fa-times file-error');
							$('li[data-file="' + element + '"]').children('i').addClass('fa-spinner file-wait');
						});

						execTranslations(files);
					}

					return false;
				});
			});

			function execTranslations (files) {
				var fileCount = 0;

				function execTranslation () {
					$('li[data-file="' + files[fileCount] + '"]').children('i').removeClass('file-wait');
					$('li[data-file="' + files[fileCount] + '"]').children('i').addClass('file-load fa-spin');

					$.ajax({
						url: './translate.php',
						timeout: 0,
						type: 'POST',
						data: {
							file: files[fileCount],
							apikey: $('#apikey').val()
						},
						success: function (data) {
							if (data == 1) {
								$('li[data-file="' + files[fileCount] + '"]').children('i').removeClass('fa-spin fa-spinner file-load');
								$('li[data-file="' + files[fileCount] + '"]').children('i').addClass('fa-check file-ok');
							} else {
								$('li[data-file="' + files[fileCount] + '"]').children('i').removeClass('fa-spin fa-spinner file-load');
								$('li[data-file="' + files[fileCount] + '"]').children('i').addClass('fa-times file-error');
							}
						},
						error: function (data) {
							$('li[data-file="' + files[fileCount] + '"]').children('i').removeClass('fa-spin fa-spinner file-load');
							$('li[data-file="' + files[fileCount] + '"]').children('i').addClass('fa-times file-error');
						},
						complete: function (data) {
							if (fileCount < files.length) {
								fileCount++;
								execTranslation();
							} else {
								alert("Übersetzung abgeschlossen!")
							}
						}
					});
				}

				execTranslation();
			}
		</script>
	</body>
</html>
