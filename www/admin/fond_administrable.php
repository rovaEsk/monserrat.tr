<?php
include "./includes/header.php";

$referencepage="fond_administrable";
$pagetitle = "Fond administrable - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if (isset($action) && $action=="actifsite"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($plateforme == "V1"){
			if ($pastille == "vert")
				$actif = 0 ;
			else
				$actif = 1 ;
			$DB_site->query("UPDATE background_site SET activeV1 = '$actif' WHERE backgroundid = '$backgroundid' AND siteid = '$actifsiteid'");
		}elseif ($plateforme == "V2"){
			if ($pastille == "vert")
				$actif = 0 ;
			else
				$actif = 1 ;
			$DB_site->query("UPDATE background_site SET activeV2 = '$actif' WHERE backgroundid = '$backgroundid' AND siteid = '$actifsiteid'");
		}
		header("location: fond_administrable.php?action=modifier&backgroundid=$backgroundid");
	}else{
		header('location: fond_administrable.php?erreurdroits=1');	
	}
}

if (isset($action) && $action=="actif"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($plateforme == "V1"){
			if ($pastille == "vert")
				$actif = 0 ;
			else
				$actif = 1 ;
			$DB_site->query("UPDATE background_site SET activeV1 = '$actif' WHERE backgroundid = '$backgroundid'");
		}elseif ($plateforme == "V2"){
			if ($pastille == "vert")
				$actif = 0 ;
			else
				$actif = 1 ;
			$DB_site->query("UPDATE background_site SET activeV2 = '$actif' WHERE backgroundid = '$backgroundid'");
		}
		header('location: fond_administrable.php');
	}else{
		header('location: fond_administrable.php?erreurdroits=1');	
	}
}

// MODIFIER UN BACKGROUND (Enregistrement BDD)
if (isset($action) and $action == "modifier2"){
	if($admin_droit[$scriptcourant][ecriture]){
		$erreur = "";
		
		if($backgroundid == ""){
			$DB_site->query("INSERT INTO background(backgroundid)VALUES ('')");
			$backgroundid = $DB_site->insert_id();
			$nouveaubackground=1;
		}

		$sites = $DB_site->query("SELECT * FROM site");
		while($site = $DB_site->fetch_array($sites)){

			if($nouveaubackground){
				$DB_site->query("INSERT INTO background_site (backgroundid, siteid) VALUES ('$backgroundid', '$site[siteid]')");
			}

			$existe_site_background = $DB_site->query_first("SELECT * FROM background
					INNER JOIN background_site USING(backgroundid)
					WHERE backgroundid = '$backgroundid'
					AND siteid='$site[siteid]'");

			if($existe_site_background[backgroundid] == ""){
				$DB_site->query("INSERT INTO background_site (backgroundid, siteid) VALUES ('$backgroundid', '$site[siteid]')");
			}
		}
		
		if ($_POST['fixe'] == "1")
			$fixe = 1;
		else
			$fixe = 0;
		
		$sql = "UPDATE background SET libelle = '" . securiserSql($_POST['libelle']) . "',
				couleur = '" . securiserSql($_POST['couleur']) . "',
				fixe = '" . $fixe . "'
				WHERE backgroundid = '$backgroundid'";

		$DB_site->query($sql);

		if (!empty($_FILES['image']['name'])) {
			$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif", "image/png");
			erreurUpload("image", $listeTypesAutorise, 1048576);
		}
		if ($erreur == "" && !empty($_FILES['image']['name'])) {
			$type_fichier = define_extention($_FILES['image']['name']);
			$DB_site->query("UPDATE background SET image = '$type_fichier' WHERE backgroundid = '$backgroundid'");
			$nom_fichier = $rootpath."configurations/$host/images/background/".$backgroundid.".".$type_fichier;
			copier_image($nom_fichier, 'image');
		}elseif (!empty($_FILES['image']['name'])){
			$texteErreur = "$multilangue[erreur_chargement_fichier]";
			eval(charge_template($langue, $referencepage, "Erreur"));
			$action = "modifier";
		}

		if($nouveaubackground){
			$texteSuccess = $multilangue[le_background]." <strong>$_POST[libelle]</strong> ".$multilangue[a_bien_ete_cre];
		}else{
			$texteSuccess = $multilangue[le_background]." <strong>$_POST[libelle]</strong> ".$multilangue[a_bien_ete_modifie];
		}

		if ($action != "modifier"){
		eval(charge_template($langue, $referencepage, "Success"));
		header('location: fond_administrable.php');
	}
	}else{
		header('location: fond_administrable.php?erreurdroits=1');	
	}
}

// AJOUTER OU MODIFIER UN BACKGROUND
if (isset($action) and $action == "modifier"){
	if(isset($backgroundid)){
		$background = $DB_site->query_first("SELECT * FROM background_site
				INNER JOIN background
				ON background_site.backgroundid = background.backgroundid
				WHERE siteid = '1' AND background.backgroundid = '$backgroundid'");
		
		$libNavigSupp = "$multilangue[modif_background] : $background[libelle]";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
		
		if ($background[fixe] == 1)
			$checked = "checked";
		else
			$checked = "";
		
		if ($background[image] != "")
			$img = "../configurations/$host/images/background/" . $background[backgroundid] . "." . $background[image];
		else
			$img = "";
		
		if ($background[activeV1] == 1){
			$color_affV1 = "vert";
			$color2_affV1 = "green";
			$ico_affV1 = "fa-check-square-o";
			$tooltip_visibleV1 = $multilangue[desactiver];
		}else{
			$color_affV1 = "rouge";
			$color2_affV1 = "red";
			$ico_affV1 = "fa-square-o";
			$tooltip_visibleV1 = $multilangue[activer];
		}
		
		if ($background[activeV2] == 1){
			$color_affV2 = "vert";
			$color2_affV2 = "green";
			$ico_affV2 = "fa-check-square-o";
			$tooltip_visibleV2 = $multilangue[desactiver];
		}else{
			$color_affV2 = "rouge";
			$color2_affV2 = "red";
			$ico_affV2 = "fa-square-o";
			$tooltip_visibleV2 = $multilangue[activer];
		}
		
		$display = (isset($display) ? "block" : "none");
		$expand = (isset($display) ? "expand" : "collapse");
		$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
		while($site = $DB_site->fetch_array($sites)){
			$backgroundsite = $DB_site->query_first("SELECT * FROM background
					INNER JOIN background_site USING(backgroundid)
					WHERE backgroundid = '$backgroundid'
					AND siteid = '$site[siteid]'");
			
			if ($backgroundsite[activeV1] == 1){
				$color_affV1 = "vert";
				$color2_affV1 = "green";
				$ico_affV1 = "fa-check-square-o";
				$tooltip_visibleV1 = $multilangue[desactiver];
			}else{
				$color_affV1 = "rouge";
				$color2_affV1 = "red";
				$ico_affV1 = "fa-square-o";
				$tooltip_visibleV1 = $multilangue[activer];
			}
			
			if ($backgroundsite[activeV2] == 1){
				$color_affV2 = "vert";
				$color2_affV2 = "green";
				$ico_affV2 = "fa-check-square-o";
				$tooltip_visibleV2 = $multilangue[desactiver];
			}else{
				$color_affV2 = "rouge";
				$color2_affV2 = "red";
				$ico_affV2 = "fa-square-o";
				$tooltip_visibleV2 = $multilangue[activer];
			}
			eval(charge_template($langue,$referencepage,"ModificationSiteBit"));
		}
		$backgroundactif = $DB_site->query_first("SELECT * FROM background
				INNER JOIN background_site USING(backgroundid)
				WHERE backgroundid = '$backgroundid'
				AND siteid = '1'");
		if ($backgroundactif[activeV1] == 1){
			$color_affV1 = "vert";
			$color2_affV1 = "green";
			$ico_affV1 = "fa-check-square-o";
			$tooltip_visibleV1 = $multilangue[desactiver];
		}else{
			$color_affV1 = "rouge";
			$color2_affV1 = "red";
			$ico_affV1 = "fa-square-o";
			$tooltip_visibleV1 = $multilangue[activer];
		}
		if ($backgroundactif[activeV2] == 1){
			$color_affV2 = "vert";
			$color2_affV2 = "green";
			$ico_affV2 = "fa-check-square-o";
			$tooltip_visibleV2 = $multilangue[desactiver];
		}else{
			$color_affV2 = "rouge";
			$color2_affV2 = "red";
			$ico_affV2 = "fa-square-o";
			$tooltip_visibleV2 = $multilangue[activer];
		}
		eval(charge_template($langue,$referencepage,"ModificationDefautBitActif"));
		eval(charge_template($langue,$referencepage,"ModificationDefautBit"));
	}else{		
		$libNavigSupp = "$multilangue[ajt_background]";
		eval(charge_template($langue,$referencepage,"NavigSupp"));		
		eval(charge_template($langue,$referencepage,"ModificationDefautBit"));
	}
	eval(charge_template($langue, $referencepage, "Modification"));
}

// SUPPRIMER UN BACKGROUND
if (isset($action) and $action == "supprimer") {
	if($admin_droit[$scriptcourant][suppression]){
		$background = $DB_site->query_first("SELECT * FROM background INNER JOIN background_site USING(backgroundid)
				WHERE backgroundid = '$backgroundid'");
		if ($background[backgroundid] != ""){
			$extension = $DB_site->query_first("SELECT image FROM background WHERE backgroundid = '$backgroundid'") ;
			$path = $rootpath . "configurations/$host/images/background";
			$dossier = opendir($path);
			while ($fichier = readdir($dossier)) {
				if ($fichier == "$backgroundid.$extension[0]"){
					unlink($path."/".$fichier);
				}
			}
			$DB_site->query("DELETE FROM background WHERE backgroundid = '$backgroundid'");
			$DB_site->query("DELETE FROM background_site WHERE backgroundid = '$backgroundid'");
			$texteSuccess = $multilangue[le_background]." <strong>$background[libelle]</strong> ".$multilangue[a_bien_ete_supprime];
			eval(charge_template($langue, $referencepage, "Success"));
		}else{
			$texteErreur = $multilangue[le_background_n_existe_plus];
			eval(charge_template($langue, $referencepage, "Erreur"));
		}
		header('location: fond_administrable.php');
	}else{
		header('location: fond_administrable.php?erreurdroits=1');	
	}
}

if (!isset($action) or $action == ""){
	$texteInfo = "$multilangue[infos_backgrounds]";
	eval(charge_template($langue, $referencepage, "Info"));
	$backgrounds = $DB_site->query("SELECT * FROM background_site
										INNER JOIN background
										ON background_site.backgroundid = background.backgroundid
										WHERE siteid = '1' ORDER BY background.libelle");
	while ($background = $DB_site->fetch_array($backgrounds)){
		if ($background[image] != "")
			$img = "../configurations/$host/images/background/" . $background[backgroundid] . "." . $background[image];
		else
			$img = "";
		if ($background[activeV1] == 1){
			$color_affV1 = "vert";
			$color2_affV1 = "green";
			$ico_affV1 = "fa-check-square-o";
			$tooltip_visibleV1 = $multilangue[desactiver];
		}else{
			$color_affV1 = "rouge";
			$color2_affV1 = "red";
			$ico_affV1 = "fa-square-o";
			$tooltip_visibleV1 = $multilangue[activer];
		}
		if ($background[activeV2] == 1){
			$color_affV2 = "vert";
			$color2_affV2 = "green";
			$ico_affV2 = "fa-check-square-o";
			$tooltip_visibleV2 = $multilangue[desactiver];
		}else{
			$color_affV2 = "rouge";
			$color2_affV2 = "red";
			$ico_affV2 = "fa-square-o";
			$tooltip_visibleV2 = $multilangue[activer];
		}
		eval(charge_template($langue, $referencepage, "ListeBit"));
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