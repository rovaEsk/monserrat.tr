<?php
include "./includes/header.php";

$referencepage="categorie";
$pagetitle = "Catégories - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

clearDir($GLOBALS[rootpath]."configurations/$GLOBALS[host]/cache/onglets/");

$il_y_a_dix_minutes = time()-600;
if($params[date_comptage_produits] < $il_y_a_dix_minutes){
	$DB_site->query("UPDATE parametre SET valeur = '".time()."' WHERE parametre = 'date_comptage_produits'");
	compter_produits($DB_site);
	compter_produits_actifs($DB_site);
}

//$mode="test_modules";

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if (isset($action) and $action == "supprimerarticlemultiple"){
	if($admin_droit[$scriptcourant][suppression]){
		foreach ($_POST[artid] as $key => $artid)
			supprimer_article($DB_site, $artid);
		$tabcatidsEnfants = array();
		compter_produits($DB_site,$catid);
		compter_produits_actifs($DB_site,$catid);
		header("location: categorie.php?catid=$catid");
	}else{
		header('location: categorie.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "supprimerarticle"){
	if($admin_droit[$scriptcourant][suppression]){
		supprimer_article($DB_site, $artid);
		$tabcatidsEnfants = array();
		compter_produits($DB_site,$catid);
		compter_produits_actifs($DB_site,$catid);
		header("location: categorie.php?catid=$catid");
	}else{
		header('location: categorie.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "deplacerarticle2"){
	if($admin_droit[$scriptcourant][ecriture]){
		if (isset($_GET[artid])){
			$DB_site->query("UPDATE article SET catid = '$catid' WHERE artid = '$artid'");
			$DB_site->query("DELETE FROM position WHERE artid = '$artid' AND catid = '$anciencatid'");
			$article = $DB_site->query_first("SELECT * FROM position WHERE artid = '$artid' AND catid = '$catid'");
			if (!$article[artid])
				$DB_site->query("INSERT INTO position (artid, catid) VALUES ('$artid', '$catid')");
		}else{
			foreach ($_POST[artid] as $key => $artid){
				$DB_site->query("UPDATE article SET catid = '$catid' WHERE artid = '$artid'");
				$DB_site->query("DELETE FROM position WHERE artid = '$artid' AND catid = '$anciencatid'");
				$article = $DB_site->query_first("SELECT * FROM position WHERE artid = '$artid' AND catid = '$catid'");
				if (!$article[artid])
					$DB_site->query("INSERT INTO position (artid, catid) VALUES ('$artid', '$catid')");
			}
		}
		$tabcatidsEnfants = array();
		compter_produits($DB_site);
		compter_produits_actifs($DB_site);
		header("location: categorie.php?catid=$catid");
	}else{
		header('location: categorie.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "deplacerarticle"){
	if (!isset($_POST[artid])){
		$article = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$artid' AND siteid = '1'");
		$libNavigSupp = "$multilangue[deplacer_l_article] : $article[libelle] - $article[artcode]";
		eval(charge_template($langue, $referencepage, "DeplacerArticleArtid"));
	}else{
		$libNavigSupp = $multilangue[deplacer];
		foreach ($_POST[artid] as $key => $artid)
			eval(charge_template($langue, $referencepage, "DeplacerArticleArtid"));
	}
	$article = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$artid' AND siteid = '1'");
	$TemplateCategorieDeplacerArticleJstreeNiveau0 = "";
	$niveaux0 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '0' AND siteid = '1' ORDER BY position");
	while ($niveau0 = $DB_site->fetch_array($niveaux0)) {
		$TemplateCategorieDeplacerArticleJstreeNiveau1 = "";
		$niveaux1 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau0[catid]' AND siteid = '1' ORDER BY position");
		while ($niveau1 = $DB_site->fetch_array($niveaux1)) {
			$TemplateCategorieDeplacerArticleJstreeNiveau2 = "";
			$niveaux2 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau1[catid]' AND siteid = '1' ORDER BY position");
			while ($niveau2 = $DB_site->fetch_array($niveaux2)) {
				$TemplateCategorieDeplacerArticleJstreeNiveau3 = "";
				$niveaux3 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau2[catid]' AND siteid = '1' ORDER BY position");
				while ($niveau3 = $DB_site->fetch_array($niveaux3)) {
					$TemplateCategorieDeplacerArticleJstreeNiveau4 = "";
					$niveaux4 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau3[catid]' AND siteid = '1' ORDER BY position");
					while ($niveau4 = $DB_site->fetch_array($niveaux4)) {
						eval(charge_template($langue,$referencepage,"DeplacerArticleJstreeNiveau4"));
					}
					eval(charge_template($langue,$referencepage,"DeplacerArticleJstreeNiveau3"));
				}
				eval(charge_template($langue,$referencepage,"DeplacerArticleJstreeNiveau2"));
			}
			eval(charge_template($langue,$referencepage,"DeplacerArticleJstreeNiveau1"));
		}
		eval(charge_template($langue,$referencepage,"DeplacerArticleJstreeNiveau0"));
	}
	eval(charge_template($langue,$referencepage,"DeplacerArticleJstree"));
	eval(charge_template($langue, $referencepage, "DeplacerArticle"));
	eval(charge_template($langue, $referencepage, "NavigSupp"));
}

if (isset($action) and $action == "copierarticle2"){
	if($admin_droit[$scriptcourant][ecriture]){
		foreach ($_POST[artid] as $key => $artid)
			copier_article($DB_site, $artid, $copcatid);
		$tabcatidsEnfants = array();
		compter_produits($DB_site,$catid);
		compter_produits_actifs($DB_site,$catid);
		header("location: categorie.php?catid=$copcatid");
	}else{
		header('location: categorie.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "copierarticle"){
	if (!isset($_POST[artid])){
		$article = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$artid' AND siteid = '1'");
		$libNavigSupp = "$multilangue[copier_article] : $article[libelle] - $article[artcode]";
		eval(charge_template($langue, $referencepage, "CopierArticleArtid"));
	}else{
		$libNavigSupp = "$multilangue[copier]";
		foreach ($_POST[artid] as $key => $artid)
			eval(charge_template($langue, $referencepage, "CopierArticleArtid"));
	}
	$TemplateCategorieCopierArticleJstreeNiveau0 = "";
	$niveaux0 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '0' AND siteid = '1' ORDER BY position");
	while ($niveau0 = $DB_site->fetch_array($niveaux0)) {
		$TemplateCategorieCopierArticleJstreeNiveau1 = "";
		$niveaux1 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau0[catid]' AND siteid = '1' ORDER BY position");
		while ($niveau1 = $DB_site->fetch_array($niveaux1)) {
			$TemplateCategorieCopierArticleJstreeNiveau2 = "";
			$niveaux2 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau1[catid]' AND siteid = '1' ORDER BY position");
			while ($niveau2 = $DB_site->fetch_array($niveaux2)) {
				$TemplateCategorieCopierArticleJstreeNiveau3 = "";
				$niveaux3 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau2[catid]' AND siteid = '1' ORDER BY position");
				while ($niveau3 = $DB_site->fetch_array($niveaux3)) {
					$TemplateCategorieCopierArticleJstreeNiveau4 = "";
					$niveaux4 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau3[catid]' AND siteid = '1' ORDER BY position");
					while ($niveau4 = $DB_site->fetch_array($niveaux4)) {
						eval(charge_template($langue,$referencepage,"CopierArticleJstreeNiveau4"));
					}
					eval(charge_template($langue,$referencepage,"CopierArticleJstreeNiveau3"));
				}
				eval(charge_template($langue,$referencepage,"CopierArticleJstreeNiveau2"));
			}
			eval(charge_template($langue,$referencepage,"CopierArticleJstreeNiveau1"));
		}
		eval(charge_template($langue,$referencepage,"CopierArticleJstreeNiveau0"));
	}
	eval(charge_template($langue,$referencepage,"CopierArticleJstree"));
	eval(charge_template($langue, $referencepage, "CopierArticle"));
	eval(charge_template($langue, $referencepage, "NavigSupp"));
}

if (isset($action) and $action == "supprimer"){
	if($admin_droit[$scriptcourant][suppression]){
		$catids = lister_catid($DB_site, $catid);
		$catids = substr($catids, 0, strlen($catids) - 1);
		$articles = $DB_site->query("SELECT * FROM article WHERE catid IN ($catids)");
		while ($article = $DB_site->fetch_array($articles))
			supprimer_article($DB_site, $article[artid]);
		if (in_array("3", $modules) || $mode == "test_modules"){
			$remises = $DB_site->query("SELECT * FROM remisecategorie WHERE catid IN ($catids)");
			while ($remise = $DB_site->fetch_array($remises)){
				$DB_site->query("DELETE FROM remise WHERE remiseid = '$remise[remiseid]'");
				$DB_site->query("DELETE FROM remise_site WHERE remiseid = '$remise[remiseid]'");
			}
			$DB_site->query("DELETE FROM remisecategorie WHERE catid IN ($catids)");
			$remises = $DB_site->query("SELECT * FROM remiseprocategorie WHERE catid IN ($catids)");
			while ($remise = $DB_site->fetch_array($remises)){
				$DB_site->query("DELETE FROM remisepro WHERE remiseid = '$remise[remiseid]'");
				$DB_site->query("DELETE FROM remisepro_site WHERE remiseid = '$remise[remiseid]'");
			}
			$DB_site->query("DELETE FROM remiseprocategorie WHERE catid IN ($catids)");
		}
		$sites = $DB_site->query("SELECT * FROM site");
		while ($site = $DB_site->fetch_array($sites)){
			$categorie = $DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$catid' AND siteid = '$site[siteid]'");
			@unlink($rootpath . "configurations/$host/images/categories/$site[siteid]/".$catid.".".$categorie[image]);
			@unlink($rootpath . "configurations/$host/images/categories/$site[siteid]/".$catid."_2.".$categorie[image2]);
			@unlink($rootpath . "configurations/$host/images/categories/$site[siteid]/".$catid."_3.".$categorie[image3]);
		}
		$DB_site->query("DELETE FROM categorie WHERE catid IN ($catids)");
		$DB_site->query("DELETE FROM categorie_site WHERE catid IN ($catids)");
		$tabcatidsEnfants = array();
		compter_produits($DB_site);
		compter_produits_actifs($DB_site);
		header('location: categorie.php');
	}else{
		header('location: categorie.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "deplacer2"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($parentid != $catid)
			$DB_site->query("UPDATE categorie SET parentid = '$parentid' WHERE catid = '$catid'");
		$tabcatidsEnfants = array();
		compter_produits($DB_site);
		compter_produits_actifs($DB_site);
		header('location: categorie.php');
	}else{
		header('location: categorie.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "deplacer"){
	$categorie = $DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$catid' AND siteid = '1'");
	$TemplateCategorieDeplacerJstreeNiveau0 = "";
	$niveaux0 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '0' AND siteid = '1' ORDER BY position");
	while ($niveau0 = $DB_site->fetch_array($niveaux0)) {
		$TemplateCategorieDeplacerJstreeNiveau1 = "";
		$niveaux1 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau0[catid]' AND siteid = '1' ORDER BY position");
		while ($niveau1 = $DB_site->fetch_array($niveaux1)) {
			$TemplateCategorieDeplacerJstreeNiveau2 = "";
			$niveaux2 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau1[catid]' AND siteid = '1' ORDER BY position");
			while ($niveau2 = $DB_site->fetch_array($niveaux2)) {
				$TemplateCategorieDeplacerJstreeNiveau3 = "";
				$niveaux3 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau2[catid]' AND siteid = '1' ORDER BY position");
				while ($niveau3 = $DB_site->fetch_array($niveaux3)) {
					$TemplateCategorieDeplacerJstreeNiveau4 = "";
					$niveaux4 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau3[catid]' AND siteid = '1' ORDER BY position");
					while ($niveau4 = $DB_site->fetch_array($niveaux4)) {
						eval(charge_template($langue,$referencepage,"DeplacerJstreeNiveau4"));
					}
					eval(charge_template($langue,$referencepage,"DeplacerJstreeNiveau3"));
				}
				eval(charge_template($langue,$referencepage,"DeplacerJstreeNiveau2"));
			}
			eval(charge_template($langue,$referencepage,"DeplacerJstreeNiveau1"));
		}
		eval(charge_template($langue,$referencepage,"DeplacerJstreeNiveau0"));
	}
	eval(charge_template($langue,$referencepage,"DeplacerJstree"));
	eval(charge_template($langue, $referencepage, "Deplacer"));
	$libNavigSupp = "$multilangue[deplacer_le_rayon] : $categorie[libelle]";
	eval(charge_template($langue, $referencepage, "NavigSupp"));
}

if (isset($action) and $action == "copier2"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($copcatid && $copcatid != $catid)
			copier_rayon($DB_site, $catid, $copcatid);
		$tabcatidsEnfants = array();
		compter_produits($DB_site);
		compter_produits_actifs($DB_site);
		header('location: categorie.php');
	}else{
		header('location: categorie.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "copier"){
	$categorie = $DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$catid' AND siteid = '1'");
	$TemplateCategorieCopierJstreeNiveau0 = "";
	$niveaux0 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '0' AND siteid = '1' ORDER BY position");
	while ($niveau0 = $DB_site->fetch_array($niveaux0)) {
		$TemplateCategorieCopierJstreeNiveau1 = "";
		$niveaux1 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau0[catid]' AND siteid = '1' ORDER BY position");
		while ($niveau1 = $DB_site->fetch_array($niveaux1)) {
			$TemplateCategorieCopierJstreeNiveau2 = "";
			$niveaux2 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau1[catid]' AND siteid = '1' ORDER BY position");
			while ($niveau2 = $DB_site->fetch_array($niveaux2)) {
				$TemplateCategorieCopierJstreeNiveau3 = "";
				$niveaux3 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau2[catid]' AND siteid = '1' ORDER BY position");
				while ($niveau3 = $DB_site->fetch_array($niveaux3)) {
					$TemplateCategorieCopierJstreeNiveau4 = "";
					$niveaux4 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau3[catid]' AND siteid = '1' ORDER BY position");
					while ($niveau4 = $DB_site->fetch_array($niveaux4)) {
						eval(charge_template($langue,$referencepage,"CopierJstreeNiveau4"));
					}
					eval(charge_template($langue,$referencepage,"CopierJstreeNiveau3"));
				}
				eval(charge_template($langue,$referencepage,"CopierJstreeNiveau2"));
			}
			eval(charge_template($langue,$referencepage,"CopierJstreeNiveau1"));
		}
		eval(charge_template($langue,$referencepage,"CopierJstreeNiveau0"));
	}
	eval(charge_template($langue,$referencepage,"CopierJstree"));
	eval(charge_template($langue, $referencepage, "Copier"));
	$libNavigSupp = "$multilangue[copier_rayon] : $categorie[libelle]";
	eval(charge_template($langue, $referencepage, "NavigSupp"));
}

if (isset($action) and $action == "modifier2"){
	if($admin_droit[$scriptcourant][ecriture]){
		$erreur = "";
		if ($catid == ""){
			$DB_site->query("INSERT INTO categorie(catid, parentid) VALUES ('', '$_POST[parentid]')");
			$catid = $DB_site->insert_id();
			$nouvellecategorie = 1;
		}
		$sites = $DB_site->query("SELECT * FROM site");
		while($site = $DB_site->fetch_array($sites)){
			if ($nouvellecategorie)
				$DB_site->query("INSERT INTO categorie_site (catid, siteid) VALUES ('$catid', '$site[siteid]')");
			$existe_site_categorie = $DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$catid' AND siteid='$site[siteid]'");
			if ($existe_site_categorie[catid] == "")
				$DB_site->query("INSERT INTO categorie_site (catid, siteid) VALUES ('$catid', '$site[siteid]')");
			$DB_site->query("UPDATE categorie INNER JOIN categorie_site USING(catid)
							SET libelle = '" . securiserSql($_POST["libelle$site[siteid]"]) . "',
							color = '" . substr(securiserSql($_POST["color$site[siteid]"]), 1) . "',
							color_back = '" . substr(securiserSql($_POST["color_back$site[siteid]"]), 1) . "',
							color_survol = '" . substr(securiserSql($_POST["color_survol$site[siteid]"]), 1) . "',
							description = '" . securiserSql($_POST["description$site[siteid]"]) . "',
							collection = '" . ($_POST["collection$site[siteid]"] ? '1' : '0') . "',
							visible_treeviewV1 = '" . ($_POST["visible_treeviewV1$site[siteid]"] ? '1' : '0') . "',
							visible_treeviewV2 = '" . ($_POST["visible_treeviewV2$site[siteid]"] ? '1' : '0') . "',
							ref_title = '" . securiserSql($_POST["ref_title$site[siteid]"]) . "',
							ref_description = '" . securiserSql($_POST["ref_description$site[siteid]"]) . "',
							ref_keywords = '" . securiserSql($_POST["ref_keywords$site[siteid]"]) . "'
							WHERE catid = '$catid' AND siteid = '$site[siteid]'");
			if (!is_dir($rootpath."configurations/$host/images/categories/$site[siteid]"))
				mkdir($rootpath."configurations/$host/images/categories/$site[siteid]", 0777);
			if (!empty($_FILES["image_$site[siteid]"]["name"])) {
				$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif", "image/png");
				erreurUpload("image_$site[siteid]", $listeTypesAutorise, 1048576);
			}
			if ($erreur == "" && !empty($_FILES["image_$site[siteid]"]["name"])) {			
				$type_fichier = define_extention($_FILES["image_$site[siteid]"]["name"]);
				$DB_site->query("UPDATE categorie_site SET image = '$type_fichier' WHERE catid = '$catid' AND siteid = '$site[siteid]'");
				$nom_fichier = $rootpath."configurations/$host/images/categories/$site[siteid]/".$catid.".".$type_fichier;
				copier_image($nom_fichier, "image_$site[siteid]");			
				redimentionner_image_categorie($nom_fichier, $site[siteid], $catid.".".$type_fichier);
			}elseif (!empty($_FILES["image_$site[siteid]"]["name"])){
				$texteErreur = "$multilangue[erreur_chargement_fichier]";
				eval(charge_template($langue, $referencepage, "Erreur"));
				$action = "modifier";
			}
			if (!empty($_FILES["image2_$site[siteid]"]["name"])) {
				$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif", "image/png");
				erreurUpload("image2_$site[siteid]", $listeTypesAutorise, 1048576);
			}
			if ($erreur == "" && !empty($_FILES["image2_$site[siteid]"]["name"])) {
				$type_fichier = define_extention($_FILES["image2_$site[siteid]"]["name"]);
				$DB_site->query("UPDATE categorie_site SET image2 = '$type_fichier' WHERE catid = '$catid' AND siteid = '$site[siteid]'");
				$nom_fichier = $rootpath."configurations/$host/images/categories/$site[siteid]/".$catid."_2.".$type_fichier;
				copier_image($nom_fichier, "image2_$site[siteid]");		
				redimentionner_image_categorie($nom_fichier, $site[siteid], $catid."_2.".$type_fichier,2);
			}elseif (!empty($_FILES["image2_$site[siteid]"]["name"])){
				$texteErreur = "$multilangue[erreur_chargement_fichier]";
				eval(charge_template($langue, $referencepage, "Erreur"));
				$action = "modifier";
			}
			if (!empty($_FILES["image3_$site[siteid]"]["name"])) {
				$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif", "image/png");
				erreurUpload("image3_$site[siteid]", $listeTypesAutorise, 1048576);
			}
			if ($erreur == "" && !empty($_FILES["image3_$site[siteid]"]["name"])) {
				$type_fichier = define_extention($_FILES["image3_$site[siteid]"]["name"]);
				$DB_site->query("UPDATE categorie_site SET image3 = '$type_fichier' WHERE catid = '$catid' AND siteid = '$site[siteid]'");
				$nom_fichier = $rootpath."configurations/$host/images/categories/$site[siteid]/".$catid."_3.".$type_fichier;
				copier_image($nom_fichier, "image3_$site[siteid]");
				redimentionner_image_categorie($nom_fichier, $site[siteid], $catid."_3.".$type_fichier,3);
			}elseif (!empty($_FILES["image3_$site[siteid]"]["name"])){
				$texteErreur = "$multilangue[erreur_chargement_fichier]";
				eval(charge_template($langue, $referencepage, "Erreur"));
				$action = "modifier";
			}
			
		}
		$DB_site->query("UPDATE categorie INNER JOIN categorie_site USING(catid)
						SET libelle = '" . securiserSql($_POST[libelle]) . "',
						color = '" . substr(securiserSql($_POST[color]), 1) . "',
						color_back = '" . substr(securiserSql($_POST[color_back]), 1) . "',
						color_survol = '" . substr(securiserSql($_POST[color_survol]), 1) . "',
						description = '".securiserSql($_POST[description])."',
						collection = '" . ($_POST[collection] ? '1' : '0') . "',
						visible_treeviewV1 = '" . ($_POST[visible_treeviewV1] ? '1' : '0') . "',
						visible_treeviewV2 = '" . ($_POST[visible_treeviewV2] ? '1' : '0') . "',
						ref_title = '" . securiserSql($_POST[ref_title]) . "',
						ref_description = '" . securiserSql($_POST[ref_description]) . "',
						ref_keywords = '" . securiserSql($_POST[ref_keywords]) . "'
						WHERE catid = '$catid' AND siteid = '1'");


		if($_POST[rayon_cliquable] == "1"){
			$rayon_cliquable = '1';
		}else{
			$rayon_cliquable = '0';
		}
		$DB_site->query("UPDATE categorie INNER JOIN categorie_site USING(catid)
		SET num_colonne = '" . securiserSql($_POST[num_colonne]) . "',
		rayon_cliquable = '$rayon_cliquable' 							
		WHERE catid = '$catid' AND siteid = '1'");

		if (!is_dir($rootpath."configurations/$host/images/categories/1"))
			mkdir($rootpath."configurations/$host/exports/images/categories/1", 0777);
		if (!empty($_FILES['image']['name'])) {
			$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif", "image/png");
			erreurUpload("image", $listeTypesAutorise, 1048576);
		}
		if ($erreur == "" && !empty($_FILES['image']['name'])) {
			$type_fichier = define_extention($_FILES['image']['name']);
			$DB_site->query("UPDATE categorie_site SET image = '$type_fichier' WHERE catid = '$catid' AND siteid = '1'");
			$nom_fichier = $rootpath."configurations/$host/images/categories/1/".$catid.".".$type_fichier;
			copier_image($nom_fichier, 'image');
			redimentionner_image_categorie($nom_fichier, 1, $catid.".".$type_fichier);
		}elseif (!empty($_FILES['image']['name'])){
			$texteErreur = "$multilangue[erreur_chargement_fichier]";
			eval(charge_template($langue, $referencepage, "Erreur"));
			$action = "modifier";
		}
		if (!empty($_FILES['image2']['name'])) {
			$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif", "image/png");
			erreurUpload("image2", $listeTypesAutorise, 1048576);
		}
		if ($erreur == "" && !empty($_FILES['image2']['name'])) {
			$type_fichier = define_extention($_FILES['image2']['name']);
			$DB_site->query("UPDATE categorie_site SET image2 = '$type_fichier' WHERE catid = '$catid' AND siteid = '1'");
			$nom_fichier = $rootpath."configurations/$host/images/categories/1/".$catid."_2.".$type_fichier;
			copier_image($nom_fichier, 'image2');
			redimentionner_image_categorie($nom_fichier, 1, $catid."_2.".$type_fichier,2);
		}elseif (!empty($_FILES['image2']['name'])){
			$texteErreur = "$multilangue[erreur_chargement_fichier]";
			eval(charge_template($langue, $referencepage, "Erreur"));
			$action = "modifier";
		}
		if (!empty($_FILES['image3']['name'])) {
			$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif", "image/png");
			erreurUpload("image3", $listeTypesAutorise, 1048576);
		}
		if ($erreur == "" && !empty($_FILES['image3']['name'])) {
			$type_fichier = define_extention($_FILES['image3']['name']);
			$DB_site->query("UPDATE categorie_site SET image3 = '$type_fichier' WHERE catid = '$catid' AND siteid = '1'");
			$nom_fichier = $rootpath."configurations/$host/images/categories/1/".$catid."_3.".$type_fichier;
			copier_image($nom_fichier, 'image3');
			redimentionner_image_categorie($nom_fichier, 1, $catid."_3.".$type_fichier,3);
		}elseif (!empty($_FILES['image3']['name'])){
			$texteErreur = "$multilangue[erreur_chargement_fichier]";
			eval(charge_template($langue, $referencepage, "Erreur"));
			$action = "modifier";
		}
		if ($nouvellecategorie)
			$texteSuccess = "$multilangue[le_rayon] <strong>$_POST[libelle]</strong> $multilangue[a_bien_ete_cre]";
		else
			$texteSuccess = "$multilangue[le_rayon] <strong>$_POST[libelle]</strong> $multilangue[a_bien_ete_modifie]";
		
		if ($action != "modifier"){
			eval(charge_template($langue, $referencepage, "Success"));
			header('location: categorie.php');
		}
	}else{
		header('location: categorie.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "modifier") {
	// Site principal
	$siteprincipal = $DB_site->query_first("SELECT * FROM site WHERE siteid = '1'");
	if (isset($catid)){
		$categorie = $DB_site->query_first("SELECT * from categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$catid' AND siteid = '1'");
		if (in_array("5836", $modules) || $mode == "test_modules"){
			$color = ($categorie[color] ? "#$categorie[color]" : "");
			eval(charge_template($langue,$referencepage,"ModificationCouleur"));
		}
		if (in_array("5913", $modules) || $mode == "test_modules"){
			$color_back = ($categorie[color_back] ? "#$categorie[color_back]" : "");
			eval(charge_template($langue,$referencepage,"ModificationCouleurBack"));
		}
		if (in_array("5927", $modules) || $mode == "test_modules"){
			$color_survol = ($categorie[color_survol] ? "#$categorie[color_survol]" : "");
			eval(charge_template($langue,$referencepage,"ModificationCouleurSurvol"));
		}
		
		// Spé stickers folies
		if($categorie[collection] == "1"){
			$checkedCollection = "checked='checked'";
		}else{
			$checkedCollection = "";
		}
		if($categorie[rayon_cliquable] == "1"){
			$checkedCliquable = "checked='checked'";
		}else{
			$checkedCliquable = "";
		}
		eval(charge_template($langue,$referencepage,"ModificationNumColonne"));
		
		
		$img = ($categorie[image] ? "../configurations/$host/images/categories/$siteprincipal[siteid]/" . $categorie[catid] . "." . $categorie[image] : "");
		eval(charge_template($langue,$referencepage,"ModificationImage"));
		if (in_array("5813", $modules) || $mode == "test_modules"){
			$img2 = ($categorie[image2] ? "../configurations/$host/images/categories/$siteprincipal[siteid]/" . $categorie[catid] . "_2." . $categorie[image2] : "");
			eval(charge_template($langue,$referencepage,"ModificationImage2"));
		}
		if (in_array("5927", $modules) || $mode == "test_modules"){
			$img3 = ($categorie[image3] ? "../configurations/$host/images/categories/$siteprincipal[siteid]/" . $categorie[catid] . "_3." . $categorie[image3] : "");
			eval(charge_template($langue,$referencepage,"ModificationImage3"));
		}
		$checkedV1 = ($categorie[visible_treeviewV1] ? "checked" : "");
		$checkedV2 = ($categorie[visible_treeviewV2] ? "checked" : "");
		$libNavigSupp = "$multilangue[modif_rayon] : $categorie[libelle]";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}else{
		if (in_array("5836", $modules) || $mode == "test_modules"){
			$color = ($categorie[color] ? "#$categorie[color]" : "");
			eval(charge_template($langue,$referencepage,"ModificationCouleur"));
		}
		if (in_array("5913", $modules) || $mode == "test_modules"){
			$color_back = ($categorie[color_back] ? "#$categorie[color_back]" : "");
			eval(charge_template($langue,$referencepage,"ModificationCouleurBack"));
		}
		if (in_array("5927", $modules) || $mode == "test_modules"){
			$color_survol = ($categorie[color_survol] ? "#$categorie[color_survol]" : "");
			eval(charge_template($langue,$referencepage,"ModificationCouleurSurvol"));
		}
		eval(charge_template($langue,$referencepage,"ModificationImage"));
		if (in_array("5813", $modules) || $mode == "test_modules"){
			eval(charge_template($langue,$referencepage,"ModificationImage2"));
		}
		if (in_array("5927", $modules) || $mode == "test_modules"){
			eval(charge_template($langue,$referencepage,"ModificationImage3"));
		}
		$checkedV1 = "checked";
		$checkedV2 = "checked";
		$libNavigSupp = $multilangue[ajt_rayon];
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}
	
	// Autres sites
	$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
	while ($site = $DB_site->fetch_array($sites)){
		$TemplateCategorieModificationSiteBitImage = "";
		$TemplateCategorieModificationSiteBitImage2 = "";
		$TemplateCategorieModificationSiteBitImage3 = "";		
		$categoriesite = $DB_site->query_first("SELECT * from categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$catid' AND siteid = '$site[siteid]'");
		/*if (in_array("5836", $modules) || $mode == "test_modules"){
			$color = ($categorie[color] ? "#$categoriesite[color]" : "");
			eval(charge_template($langue,$referencepage,"ModificationSiteBitCouleur"));
		}
		if (in_array("5913", $modules) || $mode == "test_modules"){
			$color_back = ($categorie[color_back] ? "#$categoriesite[color_back]" : "");
			eval(charge_template($langue,$referencepage,"ModificationSiteBitCouleurBack"));
		}
		if (in_array("5927", $modules) || $mode == "test_modules"){
			$color_survol = ($categorie[color_survol] ? "#$categoriesite[color_survol]" : "");
			eval(charge_template($langue,$referencepage,"ModificationSiteBitCouleurSurvol"));
		}*/
		$imgsite = ($categoriesite[image] ? "../configurations/$host/images/categories/$site[siteid]/" . $categorie[catid] . "." . $categorie[image] : "");
		eval(charge_template($langue,$referencepage,"ModificationSiteBitImage"));
		if (in_array("5813", $modules) || $mode == "test_modules"){
			$imgsite2 = ($categoriesite[image2] ? "../configurations/$host/images/categories/$site[siteid]/" . $categorie[catid] . "_2." . $categorie[image2] : "");
			eval(charge_template($langue,$referencepage,"ModificationSiteBitImage2"));
		}
		if (in_array("5927", $modules) || $mode == "test_modules"){
			$imgsite3 = ($categoriesite[image3] ? "../configurations/$host/images/categories/$site[siteid]/" . $categorie[catid] . "_3." . $categorie[image3] : "");
			eval(charge_template($langue,$referencepage,"ModificationSiteBitImage3"));
		}
		$checkedsiteCollection = ($categoriesite[collection] ? "checked" : "");
		$checkedsiteV1 = ($categoriesite[visible_treeviewV1] || $categoriesite[visible_treeviewV1] == "" ? "checked" : "");
		$checkedsiteV2 = ($categoriesite[visible_treeviewV2] || $categoriesite[visible_treeviewV1] == "" ? "checked" : "");
		eval(charge_template($langue,$referencepage,"ModificationSiteBit"));
	}

	eval(charge_template($langue,$referencepage,"Modification"));
}

if (!isset($action) or $action == ""){
	$countV1 = 0;
	$countV2 = 0;
	$counttotal = 0;
	$catid = (isset($catid) ? $catid : 0);
	$count = $DB_site->query_first("SELECT COUNT(*) count FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$catid' AND siteid = '1'");
	if ($count[count]){
		if ($catid){
			$categorie = $DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$catid'");
			eval(charge_template($langue, $referencepage, "ListeRetour"));
			if ((in_array("3", $modules) || $mode == "test_modules")){
				eval(charge_template($langue, $referencepage, "RemiseGros"));
			}
		}	
		
		if($catid == 0){
			$count_categorie_racine = $DB_site->query_first("SELECT SUM(nb_articles) AS total FROM categorie AS c
																INNER JOIN categorie_site AS cs ON cs.catid=c.catid AND cs.siteid='1' 
																WHERE c.parentid='0' AND cs.visible_treeviewV1='1' AND cs.siteid='1'");
			$categorie[nb_articles] = $count_categorie_racine[total];
			
			
			$enfants = $catid;
			catid_enfants_stricte($DB_site, $catid);
			//$enfants=str_replace("$catid,","",$enfants);
			
			$count_categorieV1 = $DB_site->query_first("SELECT SUM(articles_actifsV1) AS totalV1 FROM categorie_site WHERE catid IN ($enfants)
															AND visible_treeviewV1='1' AND siteid='1'");
			$count_categorieV2 = $DB_site->query_first("SELECT SUM(articles_actifsV2) AS totalV2 FROM categorie_site WHERE catid IN ($enfants)
															AND visible_treeviewV2='1' AND siteid='1'");
			
			$categorie[articles_actifsV1]=$count_categorieV1[totalV1];
			$categorie[articles_actifsV2]=$count_categorieV2[totalV2];	
		}
		
		
		
		
		
		
		
		
		eval(charge_template($langue, $referencepage, "ListePleine"));
	}else{
		$texteInfo = $multilangue[aucun_rayon];
		if ($catid){
			$categorie = $DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$catid'");
			eval(charge_template($langue, $referencepage, "ListeRetour"));
		}
		eval(charge_template($langue, $referencepage, "ListeVide"));
	}
	

	$nb_articles = $DB_site->query_first("SELECT COUNT(*) FROM position WHERE catid = '$catid'");
	if($nb_articles[0] > 0){
		eval(charge_template($langue, $referencepage, "OrdreAffichage"));
	}
	
	/*$articles = $DB_site->query("SELECT *, a.catid AS catidart FROM position LEFT JOIN article AS a USING(artid) INNER JOIN article_site USING(artid) WHERE position.catid = '$catid' ORDER BY position");
	while ($article = $DB_site->fetch_array($articles)){
		$TemplateCategorieListeArticleBitSimple = "";
		$TemplateCategorieListeArticleBitMultiple = "";
		$TemplateCategorieListeArticleBitCheckbox = "";
		if ($article[catidart] == $catid){
			eval(charge_template($langue, $referencepage, "ListeArticleBitCheckbox"));
			eval(charge_template($langue, $referencepage, "ListeArticleBitSimple"));
		}else{
			eval(charge_template($langue, $referencepage, "ListeArticleBitMultiple"));
		}
		eval(charge_template($langue, $referencepage, "ListeArticleBit"));
	}*/
// 	if ($DB_site->num_rows($articles) > 0)
// 		eval(charge_template($langue, $referencepage, "ListeArticle"));
// 	else
// 		eval(charge_template($langue, $referencepage, "ListeArticleVide"));
	eval(charge_template($langue, $referencepage, "Liste"));
	if ($catid)
		$libNavigSupp = chemincategorie($DB_site, $catid);
	else
		$libNavigSupp = '<a href="categorie.php">' . $multilangue[racine_du_catalogue] . '</a>';
	eval(charge_template($langue, $referencepage, "NavigSupp"));
}

$TemplateIncludejavascript = eval(charge_template($langue,  $referencepage, "Includejavascript"));
eval(charge_template($langue, "commun", "header"));
eval(charge_template($langue, "commun", "footer"));
eval(charge_template($langue, $referencepage, "index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();

?>