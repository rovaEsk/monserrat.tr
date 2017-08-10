<?php
include "./includes/header.php";

$referencepage="instit";
$pagetitle = "$multilangue[pages_instit] - $host - Admin Arobases";

// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


/******************* GESTION SUPPRESSION PAGE ********************************/
if ($action == "supprimer"){
	if($admin_droit[$scriptcourant][suppression]){
		$asupprimer=$DB_site->query_first("select * from institutionnel where institutionnelid = '$institutionnelid'");
		$institus=$DB_site->query("select * from institutionnel where menu = '$asupprimer[menu]' and position > '$asupprimer[position]'");
		while ($institu=$DB_site->fetch_array($institus))
			$DB_site->query("update institutionnel set position = position - 1 where institutionnelid = '$institu[institutionnelid]'");
		$DB_site->query("delete from institutionnel where institutionnelid = '$institutionnelid'");
		$DB_site->query("delete from institutionnel_site where institutionnelid = '$institutionnelid'");
		//echo "marche pas";
		//var_dump($institutionnelid);
		//$action="";
		header("location: instit.php?alertSuccess1=success");
	}else{
		header('location: instit.php?erreurdroits=1');	
	}
}
/******************* GESTION ACTION MONTEE/DESCENTE ********************************/
if (isset($action) && $action == "ordrePages"){
	if($admin_droit[$scriptcourant][ecriture]){
		$var_temp_positions="ordrepositions".$menuid;
		if(${$var_temp_positions}!=""){		
			$position = 0 ;
			$liste = explode(";", ${$var_temp_positions});
			foreach ($liste as $key => $value) {
				$position++ ;
				$DB_site->query("UPDATE institutionnel SET position = '$position' 
								WHERE institutionnelid = '$value'
								AND menu='$menuid'");
			}
			
		}
		header("location: instit.php?alertSuccess0=success");
	}else{
		header('location: instit.php?erreurdroits=1');	
	}
}


/******************* GESTION ACTION AJOUT PAGE ********************************/
if($action == "doajoutpage"){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("INSERT INTO institutionnel (menu, position, formulaireid, positionformulaire)
							VALUES ('".securiserSql($menuid)."', '".securiserSql($ordre)."', '".securiserSql($formulaireid)."', '".securiserSql($positionform)."')");
		
		$recupid = $DB_site->insert_id();
		
		$DB_site->query("INSERT INTO institutionnel_site (institutionnelid, siteid, libelle, contenu, active, pagetitle, metadescription, metakeywords)
							VALUES ('".securiserSql($recupid)."', '".securiserSql('1')."', '".securiserSql($nomform_1)."',
									'".securiserSql($contenuform_1)."', '".securiserSql($visible_1)."', '".securiserSql($modiftitle_1)."',
									'".securiserSql($modifdescription_1)."', '".securiserSql($modifkeywords_1)."')");
		
		$nbSites = $DB_site->query("SELECT * FROM site WHERE siteid!=1");
	
		while ($reqAjout = $DB_site->fetch_array($nbSites)){
			$contenuFormDyn = "contenuform_".$reqAjout[siteid];
			$nomFormDyn = "nom_".$reqAjout[siteid];
			$modiftitleFormDyn = "modiftitle_".$reqAjout[siteid];
			$modifdescriptionFormDyn = "modifdescription_".$reqAjout[siteid];
			$modifkeywordsDyn = "modifkeywords_".$reqAjout[siteid];
			$visibleDyn = "visible_".$reqAjout[siteid];
			
			$DB_site->query("INSERT INTO institutionnel_site (institutionnelid, siteid, libelle, contenu, active, pagetitle, metadescription, metakeywords)
							VALUES ('".securiserSql($recupid)."', '".securiserSql($reqAjout[siteid])."', '".securiserSql(${$nomFormDyn})."',
									'".securiserSql(${$contenuFormDyn})."', '".securiserSql(${$visible})."', '".securiserSql(${$modiftitleFormDyn})."', 
									'".securiserSql(${$modifdescriptionFormDyn})."', '".securiserSql(${$modifkeywordsDyn})."')");
	
			$DB_site->query("UPDATE institutionnel SET positionformulaire = '$positionform', formulaireid='$formulaireid' WHERE institutionnelid='$recupid'");
			
		}
		
		header("location: instit.php?alertSuccess2=success");
	}else{
		header('location: instit.php?erreurdroits=1');	
	}
}

/******************* GESTION AJOUT PAGE ********************************/
if($action == "ajoutPage"){
	
	$selectForms = $DB_site->query("SELECT * FROM formulaire_site ORDER BY formulaireid");
	while ($selectForm = $DB_site->fetch_array($selectForms)){
		eval(charge_template($langue,$referencepage,"SelectBit"));
	}
	
	$siteRef = $DB_site->query_first("SELECT * FROM site WHERE siteid='1'");
	$sites = $DB_site->query("SELECT * FROM site WHERE siteid!='1'");
	while ($site = $DB_site->fetch_array($sites)){
		$siteid2 = $site[siteid];
		$langueEditPage = $DB_site->query_first("SELECT * FROM langue l
												WHERE l.langueid = '$site[langueid]'");
		
		
		eval(charge_template($langue, $referencepage, "AjoutPageBit"));	
	}
	 
	eval(charge_template($langue, $referencepage, "AjoutPage1"));
	eval(charge_template($langue, $referencepage, "AjoutPage"));
	
	$libNavigSupp="Ajout d'une page institutionnelle";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}

/******************* GESTION ACTION EDITION PAGE ********************************/
if($action == "doeditpage"){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE institutionnel_site SET libelle = '".securiserSql($nomform_1)."',
														contenu = '".securiserSql($contenuform_1, "html")."',
														pagetitle = '".securiserSql($modiftitle_1)."',
														metadescription = '".securiserSql($modifdescription_1)."',
														metakeywords = '".securiserSql($modifkeywords_1)."'
														WHERE institutionnelid = '$institutionnelid' && siteid = 1 ");
	
	
		$DB_site->query("UPDATE institutionnel SET positionformulaire = '$positionform', formulaireid='$formulaireid' WHERE institutionnelid = '$institutionnelid' ");
	
		$nbSites = $DB_site->query("SELECT * FROM site WHERE siteid!=1");
	
		while ($reqEdit = $DB_site->fetch_array($nbSites)){
			$test = $DB_site->query_first("SELECT * FROM institutionnel_site WHERE siteid = '$reqEdit[siteid]' && institutionnelid = '$institutionnelid'");
			
			$contenuDyn = "contenuform_".$reqEdit[siteid];
			$libelleDyn = "nom_".$reqEdit[siteid];
			$modiftitleFormDyn = "modiftitle_".$reqEdit[siteid];
			$modifdescriptionFormDyn = "modifdescription_".$reqEdit[siteid];
			$modifkeywordsDyn = "modifkeywords_".$reqEdit[siteid];
			$visibleDyn = "visible_".$reqEdit[siteid];
			
			if ($test[institutionnelid] != ""){	
				$DB_site->query("UPDATE institutionnel_site SET libelle = '".securiserSql(${$libelleDyn})."', 
															contenu = '".securiserSql(${$contenuDyn}, "html")."',
															pagetitle = '".securiserSql(${$modiftitleFormDyn})."',
															metadescription = '".securiserSql(${$modifdescriptionFormDyn})."',
															metakeywords = '".securiserSql(${$modifkeywordsDyn})."'
							WHERE institutionnelid = '$institutionnelid' && siteid = '$reqEdit[siteid]' "); 
			
			}else{		
				$DB_site->query("INSERT INTO institutionnel_site(institutionnelid, siteid, libelle, contenu, pagetitle, metadescription, metakeywords) 	
								VALUES('$institutionnelid', '$reqEdit[siteid]', '".securiserSql(${$libelleDyn})."', '".securiserSql(${$contenuDyn}, "html")."', 
										'".securiserSql(${$modiftitleFormDyn})."', '".securiserSql(${$modifdescriptionFormDyn})."', '".securiserSql(${$modifkeywordsDyn})."')");
								
			}
		
		
		}
		header("location: instit.php?alertSuccess3=success");
	}else{
		header('location: instit.php?erreurdroits=1');	
	}
}

/******************* GESTION EDITION PAGE ********************************/
if ($action == "editpage"){
	
	$infosPageEdit = $DB_site->query_first("SELECT * FROM institutionnel
											INNER JOIN institutionnel_site ON institutionnel.institutionnelid=institutionnel_site.institutionnelid AND institutionnel_site.siteid = 1
											 WHERE institutionnel.institutionnelid = '$institutionnelid'");
	
	$siteRef = $DB_site->query_first("SELECT * FROM site WHERE siteid='1'");
	
	$selectForms = $DB_site->query("SELECT * FROM formulaire_site WHERE siteid='1' ORDER BY formulaireid");
	while ($selectForm = $DB_site->fetch_array($selectForms)){
		$selected_formulaire="";
		if($infosPageEdit[formulaireid] == $selectForm[formulaireid]){
			$selected_formulaire="selected=\"selected\"";
		}
		eval(charge_template($langue,$referencepage,"SelectBit"));
	}
	
	
	$sites = $DB_site->query("SELECT * FROM site WHERE siteid!='1'");
	while ($site = $DB_site->fetch_array($sites)){
		$siteid2 = $site[siteid];
		$infosSiteBit = $DB_site-> query_first("SELECT * FROM institutionnel_site 
												WHERE siteid = $siteid2 && institutionnelid = '$institutionnelid'");
		$langueEditPage = $DB_site->query_first("SELECT * FROM langue l
												WHERE l.langueid = '$site[langueid]'");
		eval(charge_template($langue, $referencepage, "EditpageBit"));	
	}
	
	eval(charge_template($langue,$referencepage,"Editpage1"));
	eval(charge_template($langue,$referencepage,"Editpage"));
	
	$libNavigSupp="Modification de la page <i><b>\"$infosPageEdit[libelle]\"</b></i>";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}

/******************* TEMPORAIRE : GESTION PASTILLE REFERENCE/VISIBLE ********************************/

if ($action=="ref"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($pastille == "vert")
			$cacher = 0 ;
		else
			$cacher = 1 ;
	
		$DB_site->query("UPDATE institutionnel SET nofollow = '$cacher' WHERE institutionnelid = '$institutionnelid'");
	
		header("location: instit.php");
	}else{
		header('location: instit.php?erreurdroits=1');	
	}
}

if ($action=="visible_article"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($pastille == "vert")
			$cacher = 0 ;
		else
			$cacher = 1 ;
	
		$DB_site->query("UPDATE institutionnel_site SET active = '$cacher' WHERE institutionnelid = '$institutionnelid'");
	
		header("location: instit.php");
	}else{
		header('location: instit.php?erreurdroits=1');	
	}
}
/******************* GESTION EDITION TITRE MENU ********************************/
if ($action == "editmenu"){
	if($admin_droit[$scriptcourant][ecriture]){
		$sites = $DB_site->query("SELECT * FROM site");
		while($site = $DB_site->fetch_array($sites)){
			$menu_existe = $DB_site->query("SELECT * FROM menu_site WHERE menuid = $menuid AND siteid = '$site[siteid]'");
			if($DB_site->num_rows($menu_existe)>0){
				$DB_site->query("UPDATE menu_site SET libelle = '${"libellemenu".$site[siteid]}' WHERE menuid = $menuid AND siteid = '$site[siteid]'");
			}else{
				$DB_site->query("INSERT INTO menu_site (menuid, siteid, libelle) VALUES ('$menuid','$site[siteid]','${"libellemenu".$site[siteid]}')");
			}
		}
				
		
		header("location: instit.php?alertSuccess11=success");
	}else{
		header('location: instit.php?erreurdroits=1');	
	}
}
/******************* GESTION AFFICHAGE MENUS PAGES PERSO ********************************/
if (!isset ($action) || $action == ""){
	$afficheMenus = $DB_site->query("SELECT DISTINCT menu FROM institutionnel");
	
	while($afficheMenu = $DB_site->fetch_array($afficheMenus)){
		$affichePages = $DB_site->query("SELECT * FROM institutionnel
											WHERE menu = $afficheMenu[menu]
											ORDER BY position");
		$TemplateInstitMenuPPBit="";
		$TemplateInstitFormPPBit="";
		$TemplateInstitFormEditTitreSiteBit="";
		
		if($afficheMenu[menu] == 0){
			$titreMenu = "Pages obligatoires";
			$displayBouton="style=\"display : none;\"";
		}else{
			$displayBouton="style=\"display : inline;\"";
				
			$sites = $DB_site->query("SELECT * FROM site");
			while($site = $DB_site->fetch_array($sites)){
				$libelleMenuSite = $DB_site->query_first("SELECT libelle FROM menu_site WHERE menuid = '$afficheMenu[menu]' AND siteid = '$site[siteid]'");
				eval(charge_template($langue,$referencepage,"FormEditTitreSiteBit"));
			}
			eval(charge_template($langue,$referencepage,"FormEditTitre"));
			$rqtitreMenu = $DB_site->query_first("SELECT * FROM menu_site
					WHERE menuid = $afficheMenu[menu]");
			$titreMenu = $rqtitreMenu[libelle];
		}
		
		
		while ($affichePage = $DB_site->fetch_array($affichePages)){
			$infosPage = $DB_site->query_first("SELECT * FROM institutionnel_site 
													WHERE institutionnelid = '$affichePage[institutionnelid]'");
				
			
			$urlPage="http://$host/V2/".$regleurlrewrite[1][institutionnel]."-".url_rewrite($infosPage[libelle])."-$infosPage[institutionnelid].htm";
			
			if ($affichePage[nofollow] == 1){
				$color_follow = "vert";
				$color2_follow = "green";
				$ico_follow = "fa-check-square-o";
				$tooltip_reference=$multilangue[passer_nonreference];
			}else{
				$color_follow = "rouge";
				$color2_follow = "red";
				$ico_follow = "fa-square-o";
				$tooltip_reference=$multilangue[passer_reference];
			}
			
			
			if($infosPage[active]==1){
				$color_aff = "vert";
				$color2_aff = "green";
				$ico_aff = "fa-check-square-o";
				$tooltip_visible=$multilangue[passer_invisible];
			}else{
				$color_aff = "rouge";
				$color2_aff = "red";
				$ico_aff = "fa-square-o";
				$tooltip_visible=$multilangue[passer_visible];
			}
			
			//echo $infosPage[institutionnelid];
			eval(charge_template($langue,$referencepage,"MenuPPBit"));
		}
		
		eval(charge_template($langue,$referencepage,"FormPPBit"));
				
		
		eval(charge_template($langue,$referencepage,"MenuPP"));	
	}	
	if ($alertSuccess1 == 'success'){
		$texteSuccess = $multilangue[la_page]." ".$multilangue[a_bien_ete_supprimee];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	if ($alertSuccess0 == 'success'){
		$texteSuccess = $multilangue[l_ordre_d_affichage]." ".$multilangue[a_bien_ete_enregistre];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	if ($alertSuccess2 == 'success'){
		$texteSuccess = $multilangue[la_page]." ".$multilangue[a_bien_ete_ajoutee];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	if ($alertSuccess3 == 'success'){
		$texteSuccess = $multilangue[la_page]." ".$multilangue[a_bien_ete_editee];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	if ($alertSuccess11 == 'success'){
		$texteSuccess = $multilangue[le_titre_du_menu]." ".$multilangue[a_bien_ete_edite];
		eval(charge_template($langue,$referencepage,"Success"));
	}
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