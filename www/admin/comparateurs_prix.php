<?php
include "./includes/header.php";
$referencepage="comparateurs_prix";
$pagetitle = "Comparateurs de prix - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if (isset($action) && $action == "exporter"){
	$comparateur = $DB_site->query_first("SELECT * FROM comparateur WHERE comparateurid = '" . $comparateurid . "'");
	$parametres = $DB_site->query("SELECT * FROM parametre");
	while($parametre = $DB_site->fetch_array($parametres)){
		$tab[$parametre[parametre]] = $parametre[valeur];
	}
	$sites = $DB_site->query("SELECT * FROM site");
	while ($site = $DB_site->fetch_array($sites)){
		$comparateursite = $DB_site->query_first("SELECT * FROM comparateur_site WHERE comparateurid = '$comparateur[comparateurid]' AND siteid = '$site[siteid]'");
		if ($comparateursite[comparateurid]){
			$provenance = "";
			if ($comparateur[fichier]){
				ob_start();
				include ($comparateur[fichier]);
				ob_end_clean();
			}
			if ($comparateur[fichier] && $selection > 0 && in_array("130", $modules)){
				$provenance = "selection";
				ob_start();
				include ($comparateur[fichier]);
				ob_end_clean();
			}
			if ($comparateur[fichiersoldes] && in_array("5805", $modules)){
				ob_start();
				include ($comparateur[fichiersoldes]);
				ob_end_clean();
			}
		}
	}
	header('location: comparateurs_prix.php');
}

if (isset($action) && $action == "selection2"){
	$DB_site->query("DELETE FROM article_comparateur WHERE comparateurid = '$comparateurid' AND siteid = '$comparateursiteid'");
	$articles = explode(",", $_POST[articles]);
	foreach($articles as $value) {
		if ($value != "")
			$DB_site->query("INSERT INTO article_comparateur(artid, comparateurid, siteid) VALUES ('$value', '$comparateurid', '$comparateursiteid')");
	}
	header('location: comparateurs_prix.php');
}

if (isset($action) && $action == "selection"){	
	$and = " AND siteid='1'";
	$niveaux0 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '0' $and ORDER BY position");
	//echo ("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '0' $and ORDER BY position");
	
	$TemplateComparateurs_prixSelectionNiveau0 = "";
	while ($niveau0 = $DB_site->fetch_array($niveaux0)) {
		$TemplateComparateurs_prixSelectionArticleNiveau0 = "";
		$articlesniveau0 = $DB_site->query("SELECT DISTINCT(artid), article.*, article_site.* FROM article INNER JOIN article_site USING(artid) WHERE article.catid = '$niveau0[catid]'  AND siteid = '$comparateursiteid' ORDER BY libelle");
		while ($articleniveau0 = $DB_site->fetch_array($articlesniveau0)) {
			$articlecomparateur = $DB_site->query_first("SELECT * FROM article_comparateur WHERE artid = '$articleniveau0[artid]' AND comparateurid = '$comparateurid' AND siteid = '$comparateursiteid'");
			$checked = ($articlecomparateur[artid] != "" ? ', "checked" : true' : "");
			eval(charge_template($langue,$referencepage,"SelectionArticleNiveau0"));
		}
		$TemplateComparateurs_prixSelectionNiveau1 = "";
		$TemplateComparateurs_prixSelectionArticleNiveau1 = "";
		$niveaux1 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau0[catid]' $and ORDER BY position");
		while ($niveau1 = $DB_site->fetch_array($niveaux1)) {
			$TemplateComparateurs_prixSelectionArticleNiveau1 = "";
			$articlesniveau1 = $DB_site->query("SELECT DISTINCT(artid), article.*, article_site.* FROM article INNER JOIN article_site USING(artid) WHERE article.catid = '$niveau1[catid]' AND siteid = '$comparateursiteid' ORDER BY libelle");
			while ($articleniveau1 = $DB_site->fetch_array($articlesniveau1)) {
				$articlecomparateur = $DB_site->query_first("SELECT * FROM article_comparateur WHERE artid = '$articleniveau1[artid]' AND comparateurid = '$comparateurid' AND siteid = '$comparateursiteid'");
				$checked = ($articlecomparateur[artid] != "" ? ', "checked" : true' : "");
				eval(charge_template($langue,$referencepage,"SelectionArticleNiveau1"));
			}
			$TemplateComparateurs_prixSelectionNiveau2 = "";
			$TemplateComparateurs_prixSelectionArticleNiveau2 = "";
			$niveaux2 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau1[catid]' $and ORDER BY position");
			while ($niveau2 = $DB_site->fetch_array($niveaux2)) {
				$TemplateComparateurs_prixSelectionArticleNiveau2 = "";
				$articlesniveau2 = $DB_site->query("SELECT DISTINCT(artid), article.*, article_site.* FROM article INNER JOIN article_site USING(artid) WHERE article.catid = '$niveau2[catid]' AND siteid = '$comparateursiteid' ORDER BY libelle");
				while ($articleniveau2 = $DB_site->fetch_array($articlesniveau2)) {
					$articlecomparateur = $DB_site->query_first("SELECT * FROM article_comparateur WHERE artid = '$articleniveau2[artid]' AND comparateurid = '$comparateurid' AND siteid = '$comparateursiteid'");
					$checked = ($articlecomparateur[artid] != "" ? ', "checked" : true' : "");
					eval(charge_template($langue,$referencepage,"SelectionArticleNiveau2"));
				}
				$TemplateComparateurs_prixSelectionNiveau3 = "";
				$TemplateComparateurs_prixSelectionArticleNiveau3 = "";
				$niveaux3 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau2[catid]' $and ORDER BY position");
				while ($niveau3 = $DB_site->fetch_array($niveaux3)) {
					$TemplateComparateurs_prixSelectionArticleNiveau3 = "";
					$articlesniveau3 = $DB_site->query("SELECT DISTINCT(artid), article.*, article_site.* FROM article INNER JOIN article_site USING(artid) WHERE article.catid = '$niveau3[catid]' AND siteid = '$comparateursiteid' ORDER BY libelle");
					while ($articleniveau3 = $DB_site->fetch_array($articlesniveau3)) {
						$articlecomparateur = $DB_site->query_first("SELECT * FROM article_comparateur WHERE artid = '$articleniveau3[artid]' AND comparateurid = '$comparateurid' AND siteid = '$comparateursiteid'");
						$checked = ($articlecomparateur[artid] != "" ? ', "checked" : true' : "");
						eval(charge_template($langue,$referencepage,"SelectionArticleNiveau3"));
					}
					$TemplateComparateurs_prixSelectionNiveau4 = "";
					$TemplateComparateurs_prixSelectionArticleNiveau4 = "";
					$niveaux4 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau3[catid]' $and ORDER BY position");
					while ($niveau4 = $DB_site->fetch_array($niveaux4)) {
						$TemplateComparateurs_prixSelectionArticleNiveau4 = "";
						$articlesniveau4 = $DB_site->query("SELECT DISTINCT(artid), article.*, article_site.* FROM article INNER JOIN article_site USING(artid) WHERE article.catid = '$niveau4[catid]' AND siteid = '$comparateursiteid' ORDER BY libelle");
						while ($articleniveau4 = $DB_site->fetch_array($articlesniveau4)) {
							$articlecomparateur = $DB_site->query_first("SELECT * FROM article_comparateur WHERE artid = '$articleniveau4[artid]' AND comparateurid = '$comparateurid' AND siteid = '$comparateursiteid'");
							$checked = ($articlecomparateur[artid] != "" ? ', "checked" : true' : "");
							eval(charge_template($langue,$referencepage,"SelectionArticleNiveau4"));
						}
						eval(charge_template($langue,$referencepage,"SelectionNiveau4"));
					}
					eval(charge_template($langue,$referencepage,"SelectionNiveau3"));
				}
				eval(charge_template($langue,$referencepage,"SelectionNiveau2"));
			}
			eval(charge_template($langue,$referencepage,"SelectionNiveau1"));
		}
		eval(charge_template($langue,$referencepage,"SelectionNiveau0"));
	}
	eval(charge_template($langue, $referencepage, "Selection"));
}

if (!isset($action) or $action == ""){
	$texteInfo = $multilangue[infos_comparateurs];
	eval(charge_template($langue,$referencepage,"InfoFixe"));
	$parametres = $DB_site->query("SELECT * FROM parametre");
	while($parametre = $DB_site->fetch_array($parametres)){
		$tab[$parametre[parametre]] = $parametre[valeur];
	}
	$checkedExporterNonVisibles = ($tab[exporter_non_visibles] ? "checked" : "");
	$checkedExporterCatsNonVisibles = ($tab[exporter_cats_non_visibles] ? "checked" : "");
	$checkedExporterNonCommandables = ($tab[exporter_non_commandables] ? "checked" : "");
	$checkedExporterStockEpuise = ($tab[exporter_stock_epuise] ? "checked" : "");
	$checkedExporterPrixpublic = ($tab[exporter_prixpublic] ? "checked" : "");
	if (in_array("130", $modules))
		eval(charge_template($langue, $referencepage, "ListeSelectionArticleTitre"));
	$comparateurs = $DB_site->query("SELECT * FROM comparateur INNER JOIN comparateur_langue USING(comparateurid) WHERE active = '1' AND langueid='$admin_langueid_defaut' ORDER BY comparateurid");
	while ($comparateur = $DB_site->fetch_array($comparateurs)){
		$TemplateComparateurs_prixListeSelectionArticle = "";
		$TemplateComparateurs_prixListeSelectionArticleSite = "";
		$TemplateComparateurs_prixListeGenerer = "";
		$TemplateComparateurs_prixListeBitAuto = "";
		$TemplateComparateurs_prixListeBitAutoSel = "";
		$TemplateComparateurs_prixListeBitExport = "";
		$TemplateComparateurs_prixListeBitExportSelection = "";
		$TemplateComparateurs_prixListeBitExportSoldes = "";
		$sites = $DB_site->query("SELECT * FROM site");
		while ($site = $DB_site->fetch_array($sites)){	

			$comparateur_site = $DB_site->query_first("SELECT * FROM comparateur_site WHERE siteid = '$site[siteid]' AND comparateurid='$comparateur[comparateurid]'");
			
			$articles = $DB_site->query_first("SELECT count(*) count FROM article AS a INNER JOIN article_site AS asite ON asite.artid = a.artid AND siteid='$site[siteid]' WHERE prix > 0");
			$count = $DB_site->query_first("SELECT count(*) count FROM article_comparateur WHERE comparateurid = '$comparateur[comparateurid]' AND siteid = '$site[siteid]'");
			
			eval(charge_template($langue, $referencepage, "ListeSelectionArticleSite"));
			if ($articles[count]){
				if ($comparateur_site[auto] == 1){
					$color_aff = "vert";
					$color2_aff = "green";
					$ico_aff = "fa-check-square-o";
					$tooltip_visible = $multilangue[desactiver];
				}else{
					$color_aff = "rouge";
					$color2_aff = "red";
					$ico_aff = "fa-square-o";
					$tooltip_visible = $multilangue[activer];
				}
				eval(charge_template($langue, $referencepage, "ListeBitAuto"));
			}
			if ($count[count]){
				if ($comparateur_site[auto_sel] == 1){
					$color_aff = "vert";
					$color2_aff = "green";
					$ico_aff = "fa-check-square-o";
					$tooltip_visible = $multilangue[desactiver];
				}else{
					$color_aff = "rouge";
					$color2_aff = "red";
					$ico_aff = "fa-square-o";
					$tooltip_visible = $multilangue[activer];
				}
				eval(charge_template($langue, $referencepage, "ListeBitAutoSel"));
			}
			if (file_exists($rootpath . "configurations/$host/exports/export_comparateur_$comparateur[nomcomparateur]$site[siteid].$comparateur[type_export]")){
				$date = str_replace(":","h", date("d/m/Y H:i", filemtime($rootpath . "configurations/$host/exports/export_comparateur_$comparateur[nomcomparateur]$site[siteid].$comparateur[type_export]")));
				eval(charge_template($langue, $referencepage, "ListeBitExport"));
			}
			if (in_array("130", $modules) && file_exists($rootpath . "configurations/$host/exports/export_comparateur_$comparateur[nomcomparateur]_selection$site[siteid].$comparateur[type_export]")){
				$date = str_replace(":","h", date("d/m/Y H:i", filemtime($rootpath . "configurations/$host/exports/export_comparateur_$comparateur[nomcomparateur]_selection$site[siteid].$comparateur[type_export]")));
				eval(charge_template($langue, $referencepage, "ListeBitExportSelection"));
			}
			if (in_array("5805", $modules) && file_exists($rootpath . "configurations/$host/exports/export_comparateur_$comparateur[nomcomparateur]_soldes$site[siteid].$comparateur[type_export]")){
				$date = str_replace(":","h", date("d/m/Y H:i", filemtime($rootpath . "configurations/$host/exports/export_comparateur_$comparateur[nomcomparateur]_soldes$site[siteid].$comparateur[type_export]")));
				eval(charge_template($langue, $referencepage, "ListeBitExportSoldes"));
			}			
		}
		if (in_array("130", $modules))
			eval(charge_template($langue, $referencepage, "ListeSelectionArticle"));
		if ($comparateur[fichier] != "")
			eval(charge_template($langue, $referencepage, "ListeGenerer"));
		eval(charge_template($langue, $referencepage, "ListeBit"));
	}
	$libNavigSupp = $multilangue[liste_comparateurs];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
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