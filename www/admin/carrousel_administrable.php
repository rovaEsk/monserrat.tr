<?php
include "./includes/header.php";

$referencepage="carrousel_administrable";
$pagetitle = "Carrousel administrable - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if(isset($succes)){
	switch ($succes){
		case 1:
			$diapo = $DB_site->query_first("SELECT * FROM diapositive WHERE diapositiveid = '$diapositiveid'");
			$texteSuccess = "$multilangue[le_diapositive] <strong>$diapo[nomdiapo]</strong> $multilangue[a_bien_ete_modifie]";
		break;
	}
	eval(charge_template($langue, $referencepage, "Success"));
}

if(isset($erreur)){
	switch ($erreur){
		case 1:
			$texteErreur = "$multilangue[erreur_chargement_fichier]";
		break;
		case 2:
			$texteErreur = "$multilangue[selection_maximum] <strong>$params[nombre_article_diapositive]</strong> $multilangue[articles]";
		break;	
	}
	eval(charge_template($langue, $referencepage, "Erreur"));
}

// VIDER UNE DIAPOSITIVE
if (isset($action) and $action == "vider"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("UPDATE diapositive set image = '', lien = '', nomdiapo = '', background = '' WHERE diapositiveid = '$diapositiveid' AND siteid ='$diapositivesiteid'");
		header('location: carrousel_administrable.php');
	}else{
		header('location: carrousel_administrable.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "supprimerarticle"){
	$DB_site->query("DELETE FROM diapositive_article WHERE artid = '$artid'");
	header("location: carrousel_administrable.php?action=modifier&diapositiveid=$diapositiveid&diapositivesiteid=$diapositivesiteid");
}

// MODIFIER UNE DIAPOSITIVE (Enregistrement BDD)
if (isset($action) and $action == "modifier2"){
	if($admin_droit[$scriptcourant][ecriture]){
		$erreur = "";
		if($diapositiveid == ""){
			$DB_site->query("INSERT INTO diapositive(diapositiveid, siteid)VALUES ('', $diapositivesiteid)");
			$diapositiveid = $DB_site->insert_id();
		}
		if ($_POST['blank'] == "1")
			$blank = 1;
		else
			$blank = 0;
		
		if ($_POST['background_defaut'] == "1")
			$background_defaut = 1;
		else
			$background_defaut = 0;
		
		if(!empty($_FILES['imagebg']['name'])){
			$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif", "image/png");
			erreurUpload("imagebg", $listeTypesAutorise, 5048576);
			if ($erreur == "" && !empty($_FILES['imagebg']['name'])){
				$type_fichier = define_extention($_FILES['imagebg']['name']);
				$DB_site->query("UPDATE diapositive SET background = '$type_fichier' WHERE diapositiveid = '$diapositiveid'");
				$nom_fichier = $rootpath."configurations/$host/images/background_carrousel/".$diapositiveid.".".$type_fichier;
				copier_image($nom_fichier, 'imagebg');
			}
		}
		
		
		$sql = "UPDATE diapositive SET nomdiapo = '" . securiserSql($_POST['nomdiapo']) . "',
				type = '".securiserSql($_POST['type_diapo'])."',
				lien = '" . securiserSql($_POST['lien']) . "',
				backgroundParDefaut = '" . securiserSql($background_defaut) . "',
				blank = '" . $blank . "'
				WHERE diapositiveid = '$diapositiveid'";
		$DB_site->query($sql);
		

		if ($_POST['type_diapo'] == "1"){
			$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif", "image/png");
			erreurUpload("image", $listeTypesAutorise, 1048576);
			if ($erreur == "" && !empty($_FILES['image']['name'])){
				$type_fichier = define_extention($_FILES['image']['name']);
				$DB_site->query("UPDATE diapositive SET image = '$type_fichier' WHERE diapositiveid = '$diapositiveid'");
				$nom_fichier = $rootpath."configurations/$host/images/carrousel/".$diapositiveid.".".$type_fichier;
				copier_image($nom_fichier, 'image');
			}elseif(!empty($_FILES['image']['name'])){			
				header("location: carrousel_administrable.php?action=modifier&diapositiveid=$diapositiveid&diapositivesiteid=$diapositivesiteid&erreur=1");
			}
		}elseif ($_POST['type_diapo'] == "0"){
			$DB_site->query("UPDATE diapositive SET image = 'NULL' WHERE diapositiveid = '$diapositiveid'");
			$DB_site->query("DELETE FROM diapositive_article WHERE diapositiveid = '$diapositiveid'");
			$pos = 0 ;
			if (is_array($articleTab) && count($articleTab) > 0) {
				foreach ($articleTab as $key => $value){				
					++$pos;
					if ($pos <= $params[nombre_article_diapositive]){
						$DB_site->query("INSERT INTO diapositive_article (diapositiveid, artid, position) VALUES ('$diapositiveid', '$key', '$pos')");
					}else{						
						header("location: carrousel_administrable.php?action=modifier&diapositiveid=$diapositiveid&diapositivesiteid=$diapositivesiteid&erreur=2");
					}				
				}
			}
		}else{
			$DB_site->query("UPDATE diapositive SET image = 'NULL', description = '".securiserSql($_POST['texte_html'], 'html')."' WHERE diapositiveid = '$diapositiveid'");	
		}
		header("location: carrousel_administrable.php?succes=1&diapositiveid=$diapositiveid");	
	}else{
		header('location: carrousel_administrable.php?erreurdroits=1');	
	}
}

// MODIFIER UNE DIAPOSITIVE
if (isset($action) and $action == "modifier"){
	$diapositive = $DB_site->query_first("SELECT * FROM diapositive
										WHERE siteid = '$diapositivesiteid' AND diapositiveid = '$diapositiveid'");	
	
	$site_modif = $DB_site->query_first("SELECT * FROM site WHERE siteid = '$diapositivesiteid'");
	
	$texte_entete = "$multilangue[modif_diapositive] N°$diapositive[position]";
	$width = ($carrousel_image_largeur / 2) . "px";
	$height = ($carrousel_image_hauteur / 2) . "px";
	
	$widthbg = "500px";
	$heightbg = "300px";
	
	if ($diapositive[background] != "" && $diapositive[background] != "NULL")
		$imgbg = "../configurations/$host/images/background_carrousel/" . $diapositive[diapositiveid] . "." . $diapositive[background];
		
	if ($diapositive[blank] == 1)
		$checked = "checked";
	else
		$checked = "";
	
	if ($diapositive[backgroundParDefaut] == 1)
		$checked_background_defaut = "checked";
	else
		$checked_background_defaut = "";
	
	$typeactiveArticle = "";
	$typeactiveImage = "";
	$typeactiveHtml = "";
	$typecheckedArticle = "";
	$typecheckedImage = "";
	$typecheckedHtml = "";
	$disabled = "";
	switch($diapositive[type]){
		case "0":
			$typeactiveArticle = "active";
			$typecheckedArticle = "checked";
			$displayHtml = "none";
			$displayImage = "none";
			$displayArticles = "block";
			break;
		case "1":
			$typeactiveImage = "active";
			$typecheckedImage = "checked";
			$displayHtml = "none";
			$displayImage = "block";
			$displayArticles = "none";
			break;
		case "2":
			if($user_infos[userid] == "1"){
				$disabled = "disabled"; 
			}
			$typeactiveHtml = "active";
			$typecheckedHtml = "checked";
			$displayHtml = "block";
			$displayImage = "none";
			$displayArticles = "none";
			break;
	}

	if ($diapositive[image] != "" && $diapositive[image] != "NULL")
		$img = "../configurations/$host/images/carrousel/" . $diapositive[diapositiveid] . "." . $diapositive[image];	
	else
		$img = "";
	
	$lien = $diapositive[lien];
	
	$choixarticle = "";
	$articles = $DB_site->query("SELECT a.artid, a.artcode, asite.libelle, da.position FROM article AS a 
								INNER JOIN article_site AS asite USING(artid)
								inner join diapositive_article AS da using(artid) 
								WHERE diapositiveid = '$diapositiveid' 
								AND asite.siteid='1'
								ORDER BY da.position");
	$count = $DB_site->query_first("SELECT COUNT(artid) FROM diapositive_article WHERE diapositiveid = '$diapositiveid'");
	if ($count[0] > 0){
		while ($article = $DB_site->fetch_array($articles)) {
			eval(charge_template($langue,$referencepage,"ModificationDefautBitPositionArticlesBit"));
		}
		eval(charge_template($langue,$referencepage,"ModificationDefautBitPositionArticles"));
	}
	eval(charge_template($langue,$referencepage,"ModificationDefautBit"));
	$libNavigSupp = "$multilangue[modif_diapositive] : $diapositive[nomdiapo]";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	eval(charge_template($langue, $referencepage, "Modification"));
}

if (!isset($action) or $action == ""){
	$texteInfo = "$multilangue[carrousel_diapositive_maximum] <strong>$params[nombre_diapositive]</strong> $multilangue[diapositives].";
	eval(charge_template($langue, $referencepage, "Info"));
	$i = 1;
	$width = ($carrousel_image_largeur / 2) . "px";
	$height = ($carrousel_image_hauteur / 2) . "px";
	$diapositives = $DB_site->query("SELECT * FROM diapositive
									WHERE siteid = '1' ORDER BY position");
	while (($diapositive = $DB_site->fetch_array($diapositives)) || $i <= $params[nombre_diapositive]){
		$TemplateCarrousel_administrableListeBitImage = "";
		$TemplateCarrousel_administrableListeBitArticles = "";
		$TemplateCarrousel_administrableListeBitDescription = "";
		switch($diapositive[type]){
			case "0":
				eval(charge_template($langue, $referencepage, "ListeBitArticles"));
				break;
			case "1":
				if ($diapositive[image] != "")
					$img = "../configurations/$host/images/carrousel/$diapositive[diapositiveid].$diapositive[image]";
				else
					$img = "";
				eval(charge_template($langue, $referencepage, "ListeBitImage"));
				break;
			case "2":
				eval(charge_template($langue, $referencepage, "ListeBitDescription"));
				break;
		}
		eval(charge_template($langue, $referencepage, "ListeBit"));
		++$i;
	}
	$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
	while($site = $DB_site->fetch_array($sites)){
		$i = 1;
		$diapositives = $DB_site->query("SELECT * FROM diapositive WHERE siteid = '$site[siteid]' ORDER BY position");
		$TemplateCarrousel_administrableListeSiteBit = "";
		while (($diapositive = $DB_site->fetch_array($diapositives)) || $i <= $params[nombre_diapositive]){
			$TemplateCarrousel_administrableListeSiteBitImage = "";
			$TemplateCarrousel_administrableListeSiteBitArticles = "";
			$TemplateCarrousel_administrableListeBitSiteDescription = "";
			switch($diapositive[type]){
				case "0":
					eval(charge_template($langue, $referencepage, "ListeSiteBitArticles"));
					break;
				case "1":
					if ($diapositive[image] != "")
					$img = "../configurations/$host/images/carrousel/$diapositive[diapositiveid].$diapositive[image]";
				else
					$img = "";
				eval(charge_template($langue, $referencepage, "ListeSiteBitImage"));
					break;
				case "2":
					eval(charge_template($langue, $referencepage, "ListeBitSiteDescription"));
					break;
			}
			eval(charge_template($langue, $referencepage, "ListeSiteBit"));
			++$i;
		}
		eval(charge_template($langue, $referencepage, "ListeSite"));
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