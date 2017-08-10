<?php

// on essaie de reconnaitre l'extension pour que le téléchargement corresponde au type de fichier afin d'éviter les erreurs de corruptions
if ($_GET['file']!='')
	$file = $_GET['file'];
	
switch(strrchr(basename($file), ".")) {
	case ".gz": $type = "application/x-gzip"; break;
	case ".tgz": $type = "application/x-gzip"; break;
	case ".zip": $type = "application/zip"; break;
	case ".pdf": $type = "application/pdf"; break;
	case ".png": $type = "image/png"; break;
	case ".gif": $type = "image/gif"; break;
	case ".jpg": $type = "image/jpeg"; break;
	case ".txt": $type = "text/plain"; break;
	case ".htm": $type = "text/html"; break;
	case ".html": $type = "text/html"; break;
	case ".csv": $type = "text/csv"; break;
	default: $type = "application/octet-stream"; break;
}

header("Content-disposition: attachment; filename=$file");
header("Content-Type: application/force-download");
header("Content-Transfer-Encoding: $type\n"); // Surtout ne pas enlever le \n
header("Content-Length: ".filesize($chemin . $file));
header("Pragma: no-cache");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
header("Expires: 0");
readfile($chemin . $file);

?>