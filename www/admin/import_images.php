<?php
include "./includes/header.php";

$referencepage="import_images";
$pagetitle = "Import d'images - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if (isset($action) && $action == "importer") {
	if($admin_droit[$scriptcourant][ecriture]){
		$nb_import = 0;
		$maxnbimport = 1;
		$repertoireImport = $rootpath . "configurations/$host/ftp/images";
		$images = dir($repertoireImport);
		$count = 0;
		$fd = opendir($repertoireImport);
		while($file = readdir($fd)){
			if ($file != "." && $file != ".." and !is_dir($repertoireImport . "/" . $file) ) {
				$count += 1;
			}
		}
		while($image = $images->read()) {
			if (substr($image, 0, 1) != "." and !is_dir($repertoireImport."/".$image) ){
				if ($nb_import < $maxnbimport){
					$chemin = $repertoireImport . "/" . $image;
					$tabImage = explode(".", $image);
					$intitule = $tabImage[0];
					$extension = strtolower($tabImage[1]);
					if ($type == "categorie") {
						$categorie = $DB_site->query_first("SELECT catid FROM categorie WHERE catid = '$intitule'");
						if ($categorie[catid]) {
							$catid = $categorie[catid];
							$nom_fichier = $rootpath."configurations/$host/images/categories/FR/" . $catid . "." . $extension;
							if (file_exists($nom_fichier)) {
								@unlink($nom_fichier);
							}
							copier_image_url($nom_fichier, $chemin, $extension);
							redimentionner_image_categorie($nom_fichier, "FR", $catid . "." . $extension);
							$DB_site->query("UPDATE categorie_site SET image = '$extension' WHERE catid = '$catid' AND siteid = '1'");
							@unlink($chemin);
							$nb_import++;
						} else {
							@rename($chemin, $repertoireImport . "/inexistant/" . $image);
						}
					} elseif ($type == "article") {
						$tabIntitule = explode("_", $intitule);
						$articles = $DB_site->query("SELECT artid FROM article WHERE $identifiant = '$tabIntitule[0]'");
						if ($DB_site->num_rows($articles)) {
							while ($article = $DB_site->fetch_array($articles)) {
								$artid = $article[artid];
								if ($tabIntitule[1]) {
									$lastPosition = $DB_site->query_first("SELECT MAX(position) FROM articlephoto WHERE artid = '$artid'");
									$position = $lastPosition[0] + 1;
									$DB_site->query("INSERT INTO articlephoto (artid, image, position) VALUES ('$artid', '$extension', '$position')");
									$articlephotoid = $DB_site->insert_id();
									$nom_fichier = $rootpath . "configurations/$host/images/produits/" . $artid . "_" . $articlephotoid . "." . $extension;
									if (file_exists($nom_fichier)) {
										@unlink($nom_fichier);
									}
									copier_image_url($nom_fichier, $chemin, $extension);
									redimentionner_image_complet($nom_fichier, $artid . "_" . $articlephotoid . "." . $extension);
									$nb_import++;
								} else {
									$nom_fichier = $rootpath . "configurations/$host/images/produits/" . $artid . "." . $extension;
									if (file_exists($nom_fichier)) {
										@unlink($nom_fichier);
									}
									copier_image_url($nom_fichier, $chemin, $extension);
									redimentionner_image_complet($nom_fichier, $artid . "." . $extension);
									$DB_site->query("UPDATE article_site SET image = '$extension' WHERE artid = '$artid'");
									$nb_import++;
								}
							}
							@unlink($chemin);
						} else {
							@rename($chemin, $repertoireImport . "/inexistant/" . $image);
						}
					}
				}
			}
		}
		$images->close();
		if ($count == 0)
			header('location: import_images.php');
		else
			header("location: import_images.php?action=importer&type=$type&identifiant=$identifiant");
	}else{
		header('location: import_images.php?erreurdroits=1');	
	}
}

if (!isset($action) or $action == ""){
	$texteInfo = $multilangue[infos_import_images];
	eval(charge_template($langue, $referencepage, "InfoFixe"));
	$repertoireImport = $rootpath . "configurations/$host/ftp/images";
	if (!is_dir($repertoireImport)) {
		mkdir($repertoireImport, 0777);
	}
	if (!is_dir($repertoireImport . "/inexistant")) {
		mkdir($repertoireImport . "/inexistant", 0777);
	}
	$count = 0;
	$fd = opendir($repertoireImport);
	while($file = readdir($fd)){
		if ($file != "." && $file != ".." and !is_dir($repertoireImport . "/" . $file) ) {
			$count += 1;
		}
	}
	if ($count > 0) {
		eval(charge_template($langue, $referencepage, "ListeImportArticle"));
		eval(charge_template($langue, $referencepage, "ListeImportCategorie"));		
	}
	eval(charge_template($langue, $referencepage, "Liste"));
}

$TemplateIncludejavascript = eval(charge_template($langue, $referencepage, "Includejavascript"));
eval(charge_template($langue, "commun", "header"));
eval(charge_template($langue, "commun", "footer"));
eval(charge_template($langue, $referencepage, "index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();

?>