<?php
include "./includes/header.php";

$referencepage="faq";
$pagetitle = "$multilangue[faq] - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

/******************* GESTION SUPPRESSION MENU ********************************/
if ($action == "supprimerMenu"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM faq_menu where menuid = '$menuid'");
		$DB_site->query("DELETE FROM faq_menu_site where menuid = '$menuid'");
		$DB_site->query("DELETE FROM faq_site WHERE faqid IN(SELECT faqid FROM faq WHERE menuid = '$menuid')");
		$DB_site->query("DELETE FROM faq WHERE menuid = '$menuid'");
		header("location: faq.php?Success=2");
	}else{
		header('location: faq.php?erreurdroits=1');	
	}
}

/******************* GESTION SUPPRESSION QUESTION ********************************/
if ($action == "supprimerFaq"){	
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("delete from faq where faqid = '$faqid'");
		$DB_site->query("delete from faq_site where faqid = '$faqid'");	
		header("location: faq.php?Success=1");
	}else{
		header('location: faq.php?erreurdroits=1');	
	}
}

/******************* GESTION EDITION QUESTION ********************************/
if ($action == "doeditFaq"){	
	if($admin_droit[$scriptcourant][ecriture]){
		if($faqid == ""){			
			$max_pos = $DB_site->query_first("SELECT MAX(position) FROM faq");
			$new_pos=$max_pos[0]+1;
		
			$DB_site->query("INSERT INTO faq (menuid,position) VALUES('$menuid','$new_pos')");
			$faqid = $DB_site->insert_id();
		
			$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid ASC");
			while ($site = $DB_site->fetch_array($sites)){
				$question_temp = "question_".$site[siteid];
				$question_site = ${$question_temp};
				$reponse_temp = "reponse_".$site[siteid];
				$reponse_site = ${$reponse_temp};
				$DB_site->query("INSERT INTO faq_site (faqid,siteid,question, reponse) 
									VALUES('$faqid','$site[siteid]','".securiserSql($question_site)."','".securiserSql($reponse_site,"html")."')");
			}
		}else{
			$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid ASC");
			while ($site = $DB_site->fetch_array($sites)){
				$question_temp = "question_".$site[siteid];
				$question_site = ${$question_temp};
				$reponse_temp = "reponse_".$site[siteid];
				$reponse_site = ${$reponse_temp};
				
				$existe_faq_site = $DB_site->query_first("SELECT * FROM faq_site WHERE faqid='$faqid' AND siteid='$site[siteid]'");
				if($existe_faq_site[siteid] == ""){
					$DB_site->query("INSERT INTO faq_site (faqid,siteid,question,reponse) 
										VALUES ('$faqid','$site[siteid]','".securiserSql($question_site)."',
										'".securiserSql($reponse_site,"html")."')");
				}else{
					$DB_site->query("UPDATE faq_site SET
									question = '".securiserSql($question_site)."',
									reponse = '".securiserSql($reponse_site,"html")."'
							WHERE faqid='$faqid' AND siteid='$site[siteid]'");
				}
				
			}
		}
		header("location: faq.php?Success=3");
	}else{
		header('location: faq.php?erreurdroits=1');	
	}
}


if ($action == "editFaq"){
	if($faqid == ""){
		$titre = $multilangue[ajt_faq];
		$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid ASC");
		while ($site = $DB_site->fetch_array($sites)){
			if($site[siteid] == 1){
				$class_portlet="collapse";
				$style_portlet="style=\"\"";
			}else{
				$class_portlet="expand";
				$style_portlet="style=\"display:none;\"";
			}
			eval(charge_template($langue,$referencepage,"EditQuestionSiteBit"));
		}				
	}else{
		$faq1 = $DB_site->query_first("SELECT * FROM faq_site WHERE faqid = '$faqid' AND siteid='1'");
		$titre = $multilangue[modif_faq]." : $faq1[libelle]";
		
		$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid ASC");
		while ($site = $DB_site->fetch_array($sites)){
			$faq_site = $DB_site->query_first("SELECT * FROM faq_site WHERE faqid = '$faqid' AND siteid='$site[siteid]'");
			if($site[siteid] == 1){
				$class_portlet="collapse";
				$style_portlet="style=\"\"";
			}else{
				$class_portlet="expand";
				$style_portlet="style=\"display:none;\"";
			}
			eval(charge_template($langue,$referencepage,"EditQuestionSiteBit"));
		}
	}		
	$libNavigSupp=$titre;
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	eval(charge_template($langue,$referencepage,"EditQuestion"));
}

/******************* GESTION EDITION MENU ********************************/
if ($action == "doeditMenu"){	
	if($admin_droit[$scriptcourant][ecriture]){
		if($menuid == ""){		
			$max_pos = $DB_site->query_first("SELECT MAX(position) FROM faq_menu");
			$new_pos=$max_pos[0]+1;
		
			$DB_site->query("INSERT INTO faq_menu (position) VALUES('$new_pos')");
			$menuid = $DB_site->insert_id();
		
			$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid ASC");
			while ($site = $DB_site->fetch_array($sites)){
				$lib_temp = "libelle_".$site[siteid];
				$lib_site = ${$lib_temp};
				$DB_site->query("INSERT INTO faq_menu_site (menuid,siteid,libelle) VALUES('$menuid','$site[siteid]','".securiserSql($lib_site)."')");
			}
		}else{
			$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid ASC");
			while ($site = $DB_site->fetch_array($sites)){
				$lib_temp = "libelle_".$site[siteid];
				$lib_site = ${$lib_temp};
				$DB_site->query("UPDATE faq_menu_site SET libelle = '".securiserSql($lib_site)."' WHERE menuid='$menuid' AND siteid='$site[siteid]'");
			}
		}

		$DB_site->query("UPDATE menu_site SET libelle = '$libellemenu'
				WHERE menuid = $menuid");

		header("location: faq.php?alertSuccess11=success");
	}else{
		header('location: faq.php?erreurdroits=1');	
	}
}


if ($action == "editMenu"){
	if($menuid == ""){
		$titre = $multilangue[ajt_menu];
		$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid ASC");
		while ($site = $DB_site->fetch_array($sites)){
			if($site[siteid] == 1){
				$class_portlet="collapse";
				$style_portlet="style=\"\"";
			}else{
				$class_portlet="expand";
				$style_portlet="style=\"display:none;\"";
			}
			eval(charge_template($langue,$referencepage,"EditMenuSiteBit"));
		}				
	}else{
		$menu1 = $DB_site->query_first("SELECT * FROM faq_menu_site WHERE menuid = '$menuid' AND siteid='1'");
		$titre = $multilangue[modif_menu]." : $menu1[libelle]";
		
		$menu_sites = $DB_site->query("SELECT * FROM faq_menu_site WHERE menuid = '$menuid'");
		while($menu_site = $DB_site->fetch_array($menu_sites)){
			$site = $DB_site->query_first("SELECT * FROM site WHERE siteid = '$menu_site[siteid]'");
			if($site[siteid] == 1){
				$class_portlet="collapse";
				$style_portlet="style=\"\"";
			}else{
				$class_portlet="expand";
				$style_portlet="style=\"display:none;\"";
			}
			eval(charge_template($langue,$referencepage,"EditMenuSiteBit"));
		}
	}		
	$libNavigSupp=$titre;
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	
	eval(charge_template($langue,$referencepage,"EditMenu"));
}

/******************* GESTION AFFICHAGE MENUS PAGES PERSO ********************************/
if (!isset ($action) || $action == ""){	
	// Liste des menus		
	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid ASC");
	while ($site = $DB_site->fetch_array($sites)){	
		eval(charge_template($langue,$referencepage,"MenuListeSiteBit"));
	}

	$afficheMenus = $DB_site->query("SELECT DISTINCT fm.menuid, fms.* FROM faq_menu AS fm
										INNER JOIN faq_menu_site AS fms ON(fm.menuid = fms.menuid) AND siteid='1'
										ORDER BY fm.position ASC");
	$nb_menus=0;
	while($afficheMenu = $DB_site->fetch_array($afficheMenus)){
		$TemplateFaqMenuListeBitSite = "";
		$nb_menus++;
		$menu_sites = $DB_site->query("SELECT * FROM faq_menu_site 
										WHERE menuid='$afficheMenu[menuid]' 
										ORDER BY siteid ASC");
		while ($menu_site = $DB_site->fetch_array($menu_sites)){
			if($menu_site[siteid] == 1){
				$lib_menu_site1 = $menu_site[libelle];
			}			
			eval(charge_template($langue,$referencepage,"MenuListeBitSite"));
		}		
		eval(charge_template($langue,$referencepage,"MenuListeBit"));
	}
	if($nb_menus){
		eval(charge_template($langue,$referencepage,"MenuListeTableau"));
	}
	eval(charge_template($langue,$referencepage,"MenuListe"));
	
	
	
	
	// Liste des questions / réponses sans menu	
	$afficheFaqs = $DB_site->query("SELECT * FROM faq AS f
			INNER JOIN faq_site AS fs ON(f.faqid = fs.faqid) AND siteid='1'
			WHERE f.menuid = '0'
			ORDER BY f.position");

	while($afficheFaq = $DB_site->fetch_array($afficheFaqs)){
		$afficheFaq[reponse] = nl2br($afficheFaq[reponse]);
		eval(charge_template($langue,$referencepage,"MenuBitFaqBit"));
	}
	$afficheMenu[menuid] = 0;
	$afficheMenu[libelle] = "Questions / réponses sans menu";
	eval(charge_template($langue,$referencepage,"MenuBit"));
	
	
	
	// Liste des questions / réponses dans leur menu
	$afficheMenus = $DB_site->query("SELECT DISTINCT fm.menuid, fms.* FROM faq_menu AS fm
										INNER JOIN faq_menu_site AS fms ON(fm.menuid = fms.menuid) AND siteid='1'
										ORDER BY fm.position ASC");
	while($afficheMenu = $DB_site->fetch_array($afficheMenus)){	
		$TemplateFaqMenuBitFaqBit="";
		
		$afficheFaqs = $DB_site->query("SELECT * FROM faq AS f
											INNER JOIN faq_site AS fs ON(f.faqid = fs.faqid) AND siteid='1'
											WHERE f.menuid = '$afficheMenu[menuid]'
											ORDER BY f.position");

		while($afficheFaq = $DB_site->fetch_array($afficheFaqs)){	
		$afficheFaq[reponse] = nl2br($afficheFaq[reponse]);
			eval(charge_template($langue,$referencepage,"MenuBitFaqBit"));
		}
		eval(charge_template($langue,$referencepage,"MenuBit"));	
	}	
	
	
	if ($Success == '1'){
		$texteSuccess = $multilangue[la_faq]." ".$multilangue[a_bien_ete_supprimee];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	
	if ($Success == '2'){
		$texteSuccess = $multilangue[le_menu]." ".$multilangue[a_bien_ete_supprime];
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