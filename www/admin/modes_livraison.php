<?php
include "./includes/header.php";

$referencepage="modes_livraison";
$pagetitle = "Modes de livraison - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

// Copie des valeurs site par défaut pour les autres sites.
/*$modes_sites = $DB_site->query("SELECT * FROM mode_livraison_site WHERE siteid = '1'");
 while ($mode_site = $DB_site->fetch_array($modes_sites)){
 $sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
 while ($site = $DB_site->fetch_array($sites)){
 $DB_site->query("INSERT IGNORE INTO mode_livraison_site (modelivraisonid,siteid, nom, description, activeV1, activeV2, activeV1M, activeV2M)
 VALUES($mode_site[modelivraisonid], '$site[siteid]','".securiserSql($mode_site[nom])."',
 '".securiserSql($mode_site[description], "html")."','$mode_site[activeV1]','$mode_site[activeV2]',
 '$mode_site[activeV1M]','$mode_site[activeV2M]')");
 }
 }*/

// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

//************************************************ ACTION EDITION MODE LIVRAISON ************************************************************
if ($action == doedit_ML){
	if($admin_droit[$scriptcourant][ecriture]){
		$sites = $DB_site->query("SELECT * FROM site");
		while($site = $DB_site->fetch_array($sites)){
			$test = $DB_site->query_first("SELECT * FROM mode_livraison_site WHERE siteid = '$site[siteid]' AND modelivraisonid='$modelivraisonid'");		
			if ($test[modelivraisonid] != ""){
				$libelleDyn = "libelle_".$site[siteid];
				$descriptionDyn = "description_".$site[siteid];
				$activeV1Dyn = "activev1_".$site[siteid];
				$activeV2Dyn = "activev2_".$site[siteid];
				$DB_site->query("UPDATE mode_livraison_site SET nom = '".securiserSql(${$libelleDyn})."',
																description = '".securiserSql(${$descriptionDyn}, "html")."',
																activeV1 = '${$activeV1Dyn}',
																activeV2 = '${$activeV2Dyn}'
								WHERE siteid= '$site[siteid]' && modelivraisonid = '$modelivraisonid'");	
			}else{
				$libelleDyn = "libelle_".$site[siteid];
				$descriptionDyn = "description_".$site[siteid];
				$activeV1Dyn = "activev1_".$site[siteid];
				$activeV2Dyn = "activev2_".$site[siteid];
				if (${$libelleDyn} != ""){
					$DB_site-> query("INSERT INTO mode_livraison_site(modelivraisonid, siteid, nom, description, activeV1, activeV2, activeV1M, activeV2M) 
						VALUES ('$modelivraisonid', '$site[siteid]', '".securiserSql(${$libelleDyn})."', '".securiserSql(${$descriptionDyn}, "html")."', '${$activeV1Dyn}', '${$activeV2Dyn}', '0', '0')");
				}
				/*echo "INSERT INTO mode_livraison_site(modelivraisonid, siteid, nom, description, activeV1, activeV2, activeV1M, activeV2M) 
					VALUES ('$modelivraisonid', '$site[siteid]', '${$libelleDyn}', '${$descriptionDyn}', '${$activeV1Dyn}', '${$activeV2Dyn}', '0', '0')";	*/
			}	
			
			$file = "mode_logo$site[siteid]";
			$erreur = "";
			if (!empty($_FILES[$file]['name'])) {
				$listeTypesAutorise = array("image/pjpeg","image/jpeg","image/gif");
				erreurUpload($file,$listeTypesAutorise,1048576);
				if (!$erreur){
					$type_fichier=pathinfo($_FILES[$file][name], PATHINFO_EXTENSION);
					$DB_site->query("UPDATE mode_livraison_site SET logo = '$type_fichier' WHERE modelivraisonid = '$modelivraisonid' AND siteid = '$site[siteid]'");
					$nom_fic=$rootpath."configurations/$host/images/modes_livraison/".$modelivraisonid."_".$site[siteid].".".$type_fichier;
					copier_image($nom_fic,$file);
					$destination=$rootpath."configurations/$host/images/modes_livraison/br/".$modelivraisonid."_".$site[siteid].".".$type_fichier;
					redimentionner_image($nom_fic,$destination,$modelivraison_largeur,$modelivraison_hauteur);
					//@unlink($nom_fic);
				}
			}
			
		}
		//$action = "editML";
		header("location: modes_livraison.php?action=editML&modelivraisonid=$modelivraisonid&alertSuccess=success");
	}else{
		header('location: modes_livraison.php?erreurdroits=1');	
	}
}

//************************************************ GESTION POSITION MODES ************************************************************
if (isset($action) && $action == "positions"){
	if($admin_droit[$scriptcourant][ecriture]){
		if($ordre!=""){
			$position = 0 ;
			$liste = explode(";", $ordre) ;
			foreach ($liste as $key => $value) {
				$position++ ;
				$DB_site->query("UPDATE mode_livraison SET position = '$position' WHERE modelivraisonid = '$value'");
				//var_dump($value);
			}
			$texteSuccess = $multilangue[l_ordre_d_affichage]." ".$multilangue[a_bien_ete_enregistre];
			eval(charge_template($langue,$referencepage,"Success"));
		}
		$action = "";
	}else{
		header('location: modes_livraison.php?erreurdroits=1');	
	}
}
//************************************************ SAVE LOGO ************************************************************
/*if ($action == 'doedit_logo'){
	if (!empty($_FILES['mode_logo']['name'])) {
		$listeTypesAutorise = array("image/pjpeg","image/jpeg","image/gif");
		erreurUpload("mode_logo",$listeTypesAutorise,1048576);
		if (empty($erreur)){
			$type_fichier=define_extention($_FILES['mode_logo']['name']);
			$DB_site->query("UPDATE mode_livraison SET logo = '$type_fichier' WHERE modelivraisonid = '$modelivraisonid'");
			$nom_fic=$rootpath."configurations/$host/images/modes_livraison/".$modelivraisonid.".".$type_fichier;
			copier_image($nom_fic,'mode_logo');
			$destination=$rootpath."configurations/$host/images/modes_livraison/br/".$modelivraisonid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$modelivraison_largeur,$modelivraison_hauteur);
			@unlink($nom_fic);
		}
	}
	//$action = "editML";
	header("location: modes_livraison.php?action=editML&modelivraisonid=$modelivraisonid&alertSuccess1=success");
}*/
//************************************************ GESTION EDITION MODE LIVRAISON *********************************************
if ($action == editML){
	$sites = $DB_site->query("SELECT * FROM site");
	
	//eval(charge_template($langue,$referencepage,"EditLogo"));
	while ($site = $DB_site->fetch_array($sites)){	
		$infoslogo = $DB_site->query_first("SELECT * FROM mode_livraison_site WHERE modelivraisonid = '$modelivraisonid' AND siteid = '$site[siteid]'");
		if (!empty($infoslogo[logo])) {
			$fichier = $modelivraisonid."_".$site[siteid].".".$infoslogo[logo] ;
			$folder = $rootpath."configurations/$host/images/moyen_paiement/br";
			$image = "http://$host/configurations/$host/images/modes_livraison/br/".$modelivraisonid."_".$site[siteid].".".$infoslogo[logo]."?date=".time();
		}else{
			$image = "";	
		}
		
		$infosML = $DB_site-> query_first("SELECT * FROM mode_livraison_site WHERE modelivraisonid = '$modelivraisonid' && siteid = '$site[siteid]'");
		
		$langue = $DB_site->query_first("SELECT * FROM langue WHERE langueid = '$site[langueid]'");
		
		
		if ($site[siteid] == '1'){
			$collapse = "collapse";
			$style = "";	
		}else{
			$collapse = "expand";
			$style = "style = \"display : none;\"";	
		}
		
		if($infosML[activeV1]==1){
			$checkedv1 = "checked=\"checked\"";
		}else{
			$checkedv1 = "";
		}
		
		if($infosML[activeV2]==1){
			$checkedv2 = "checked=\"checked\"";
		}else{
			$checkedv2 = "";
		}
		
		
			
		eval(charge_template($langue,$referencepage,"EditML"));
	}
	$lib = $DB_site->query_first("SELECT * FROM mode_livraison_site WHERE modelivraisonid = '$modelivraisonid'");
	$libNavigSupp = $lib[nom];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	if ($alertSuccess == "success"){
		$infosSuccess = $DB_site->query_first("SELECT * FROM mode_livraison_site WHERE modelivraisonid = '$modelivraisonid'");
		$texteSuccess = "Le mode de livraison \"$infosSuccess[nom]\"a bien été édité";
		eval(charge_template($langue,$referencepage,"Success"));
	}
	if ($alertSuccess1 == "success"){
		$infosSuccess = $DB_site->query_first("SELECT * FROM mode_livraison_site WHERE modelivraisonid = '$modelivraisonid'");
		$texteSuccess1 = "Le logo du mode de livraison \"$infosSuccess[nom]\"a bien été édité";
		eval(charge_template($langue,$referencepage,"Success"));
	}
	eval(charge_template($langue,$referencepage,"EditMLForm"));
}
//************************************************ GESTION MODE DE LIVRAISON ACTIF *********************************************

if ($action=="active"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($pastille == "vert")
			$cacher = 0;
		else
			$cacher = 1;
	
		$DB_site->query("UPDATE mode_livraison_site SET activeV1 = '$cacher' WHERE modelivraisonid = '$modelivraisonid' && siteid = '1'");
	
		$action = "";
	}else{
		header('location: modes_livraison.php?erreurdroits=1');	
	}
}

if ($action=="active2"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($pastille2 == "vert")
			$cacher = 0 ;
		else
			$cacher = 1 ;
	
		$DB_site->query("UPDATE mode_livraison_site SET activeV2 = '$cacher' WHERE modelivraisonid = '$modelivraisonid' && siteid = '1'");
	
		$action = "" ;
	}else{
		header('location: modes_livraison.php?erreurdroits=1');	
	}
}

/******************* GESTION AFFICHAGE DE BASE ********************************/
if (!isset ($action) || $action == ""){
	$modeslivraison = $DB_site->query("SELECT * FROM mode_livraison ORDER BY position");	
	while ($modelivraison = $DB_site-> fetch_array ($modeslivraison)){
		$infosModelivraison = $DB_site->query_first("SELECT * FROM mode_livraison_site WHERE modelivraisonid = '$modelivraison[modelivraisonid]'");
		
		if($infosModelivraison[activeV1]==1){
			$color_aff = "vert";
			$color2_aff = "green";
			$ico_aff = "fa-check-square-o";
			$tooltip_visible="Désactiver";
		}else{
			$color_aff = "rouge";
			$color2_aff = "red";
			$ico_aff = "fa-square-o";
			$tooltip_visible="Activer";
		}
		
		if($infosModelivraison[activeV2]==1){
			$color_aff2 = "vert";
			$color2_aff2 = "green";
			$ico_aff2 = "fa-check-square-o";
			$tooltip_visible2="Désactiver";
		}else{
			$color_aff2 = "rouge";
			$color2_aff2 = "red";
			$ico_aff2 = "fa-square-o";
			$tooltip_visible2="Activer";
		}
		
		
		eval(charge_template($langue,$referencepage,"ListeBit"));
	}
	eval(charge_template($langue,$referencepage,"Liste"));
}



$TemplateIncludejavascript = eval(charge_template($langue, $referencepage,"Includejavascript"));
eval(charge_template($langue,"commun","header"));
eval(charge_template($langue,"commun","footer"));
eval(charge_template($langue,$referencepage,"index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};
$DB_site->close();
flush();

?>