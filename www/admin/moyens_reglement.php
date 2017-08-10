<?php
include "./includes/header.php";

$referencepage="moyens_reglement";
$pagetitle = "Moyens de reglement - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}
//************************************************ GESTION ACTION EDITION MOYEN PAIEMENT **********************************
if ($action == doedit_MP){
	if($admin_droit[$scriptcourant][ecriture]){
		$sites = $DB_site->query("SELECT * FROM site"); 
		while ($site = $DB_site->fetch_array($sites)){
			$test = $DB_site->query_first("SELECT * FROM moyenpaiement_site WHERE siteid = '$site[siteid]' AND moyenid='$moyenid'");
			if ($test[moyenid] != ""){
				$description = "description_".$site[siteid];
				$contenu = "contenu_".$site[siteid];
				$activev1 = 'activev1_'.$site[siteid];
				$activev2 = 'activev2_'.$site[siteid];
				$montantMinimum = 'minimumachat_'.$site[siteid];
				$montantMaximum = 'maximumachat_'.$site[siteid];
				$libelle = "libelleMP_".$site[siteid];
				$defaut = "defaut_".$site[siteid];
				$DB_site->query("UPDATE	moyenpaiement_site SET libelle = '".securiserSql(${$libelle})."',
																description = '".securiserSql(${$description}, "html")."',
																contenu = '".securiserSql(${$contenu}, "html")."',
																activeV1 = '${$activev1}',
																activeV2 = '${$activev2}',
																montantMinimum = '${$montantMinimum}',
																montantMaximum = '${$montantMaximum}'														
								WHERE siteid = '$site[siteid]' && moyenid = '$moyenid'");
				if (${$defaut} == 1){
					$DB_site->query("UPDATE moyenpaiement_site SET defaut = '0' WHERE defaut = '1' && siteid = '$site[siteid]'");
					$DB_site->query("UPDATE moyenpaiement_site SET defaut = '1' WHERE moyenid = '$moyenid'");
				}else{
					$DB_site->query("UPDATE moyenpaiement_site SET defaut = '0' WHERE moyenid = '$moyenid' && siteid = '$site[siteid]'");	
				}
			}else{
				$libelleMP = $DB_site->query_first("SELECT libelle FROM moyenpaiement_site WHERE moyenid ='$moyenid' AND siteid='1'");
				$description = "description_".$site[siteid];
				$contenu = "contenu_".$site[siteid];
				$activev1 = 'activev1_'.$site[siteid];
				$activev2 = 'activev2_'.$site[siteid];
				$montantMinimum = 'minimumachat_'.$site[siteid];
				$montantMaximum = 'maximumachat_'.$site[siteid];
				$libelle = "libelleMP_".$site[siteid];
				$DB_site->query("INSERT INTO moyenpaiement_site(moyenid, siteid, libelle, defaut, description, activeV1, activeV2, 
																activeV1M, activeV2M, montantMinimum, montantMaximum, contenu)
												VALUES	('$moyenid', '$site[siteid]', '".securiserSql(${$libelle})."', '${$defaut}', '".securiserSql(${$description}, "html")."', '${$activev1}', 
														'${$activev2}', '0', '0', '${$montantMinimum}', '${$montantMaximum}', '".securiserSql(${$contenu}, "html")."')");
				
				
				
				/*echo "INSERT INTO moyenpaiement_site(moyenid, siteid, libelle, description, activeV1, activeV2, 
																activeV1M, activeV2M, montantMinimum, montantMaximum, contenu)
												VALUES	('$moyenid', '$site[siteid]', '$libelleMP[libelle]', '${$description}', '${$activev1}', 
														'${$activev2}', '0', '0', '${$montantMinimum}', '${$montantMaximum}', '${$contenu}')";*/
			}
		}
		//$action="editMP";
		header("location: moyens_reglement.php?action=editMP&moyenid=$moyenid&alertSuccessMP=success");
	}else{
		header('location: moyens_reglement.php?erreurdroits=1');	
	}
}
//************************************************ SAVE LOGO ************************************************************
if ($action == 'doedit_logo'){
	if($admin_droit[$scriptcourant][ecriture]){
		//$DB_site->query("UPDATE moyenpaiement SET logo = '$logo' WHERE moyenid = '$moyenid'");
		//echo $moyenid;
		//var_dump( $_FILES['moyen_logo']['name']);
		if (!empty($_FILES['moyen_logo']['name'])) {		
			$listeTypesAutorise = array("image/pjpeg","image/jpeg","image/gif");
			erreurUpload("moyen_logo",$listeTypesAutorise,1048576);
			if (empty($erreur)){
				$type_fichier=define_extention($_FILES['moyen_logo']['name']);
				$DB_site->query("UPDATE moyenpaiement SET logo = '$type_fichier' WHERE moyenid = '$moyenid'");
				$nom_fic=$rootpath."configurations/$host/images/moyen_paiement/".$moyenid.".".$type_fichier;
				copier_image($nom_fic,'moyen_logo');
				$destination=$rootpath."configurations/$host/images/moyen_paiement/br/".$moyenid.".".$type_fichier;
				redimentionner_image($nom_fic,$destination,$moyenpaiement_largeur,$moyenpaiement_hauteur);	
			}
		}
		//$action = "editMP";
		header("location: moyens_reglement.php?action=editMP&moyenid=$moyenid&alertSuccesslogo=success");
	}else{
		header('location: moyens_reglement.php?erreurdroits=1');	
	}
}
//************************************************ GESTION EDIT MOYEN PAIEMENT ********************************************
if ($action == 'editMP'){
	$sites = $DB_site->query("SELECT * FROM site");
	$infosMP = $DB_site->query_first("SELECT * FROM moyenpaiement WHERE moyenid ='$moyenid'");
	
	if (!empty($infosMP[logo])) {
		$fichier = $moyenid.".".$infosMP[logo] ;
		$folder = $rootpath."configurations/$host/images/moyen_paiement/br";		
	}
	
	eval(charge_template($langue,$referencepage,"EditMP"));
	while($site = $DB_site->fetch_array($sites)){
		$infosMoyenpaiement = $DB_site->query_first("SELECT * FROM moyenpaiement_site WHERE siteid = '$site[siteid]' && moyenid = '$moyenid'");
		if($infosMoyenpaiement[activeV1]==1){
			$checkedv1 = "checked=\"checked\"";
		}else{
			$checkedv1 = "";
		}
		
		if($infosMoyenpaiement[activeV2]==1){
			$checkedv2 = "checked=\"checked\"";
		}else{
			$checkedv2 = "";
		}
		if ($infosMoyenpaiement[defaut]==1){
			$checkeddefaut = "checked=\"checked\"";
		}else{
			$checkeddefaut = "";	
		}
		
		if ($site[siteid] != '1'){
			$style = "style = \"display : none\"";
			$collapse = "expand";
		}else{
			$style = "";
			$collapse = "collapse";
		}
		eval(charge_template($langue,$referencepage,"EditDescription"));
	}
	$title = $DB_site->query_first("SELECT libelle FROM moyenpaiement_site WHERE moyenid = '$moyenid' && siteid= '1'");
	$libNavigSupp = "Edition du moyen de règlement <i><b>\"$title[libelle]\"</i></b>";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	if ($alertSuccessMP == "success"){
		$infosSuccess = $DB_site->query_first("SELECT * FROM moyenpaiement_site WHERE moyenid = '$moyenid'");
		$texteSuccessMP = $multilangue[le_moyen_de_paiement]." \"$infosSuccess[libelle]\" ".$multilangue[a_bien_ete_edite];
		eval(charge_template($langue,$referencepage,"SuccessMP"));
	}
	if ($alertSuccesslogo == "success"){
		$infosSuccess = $DB_site->query_first("SELECT * FROM moyenpaiement_site WHERE moyenid = '$moyenid'");
		$texteSuccessLogo = $multilangue[le_logo_du_moyen_de_paiement]." \"$infosSuccess[libelle]\" ".$multilangue[a_bien_ete_edite];
		eval(charge_template($langue,$referencepage,"SuccessLogo"));
	}
	eval(charge_template($langue,$referencepage,"EditForm"));
	
}
//************************************************ GESTION EDIT MAIL PAYPAL ********************************************
if ($action == edit_paypal){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE parametre SET valeur = '$paypal' WHERE parametre ='emailComptePaypal'");
		$action="";
		$texteSuccess2 = $multilangue[mail_paypal_enregistre];
		eval(charge_template($langue,$referencepage,"Success2"));
	}else{
		header('location: moyens_reglement.php?erreurdroits=1');	
	}
}
//************************************************ GESTION EDIT COORDONNEES BANCAIRES ********************************************
if ($action == 'paiement_virement'){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE parametre SET valeur = '$ribbanque' WHERE parametre ='RIBetab'");
		$DB_site->query("UPDATE parametre SET valeur = '$ribguichet' WHERE parametre ='RIBguichet'");
		$DB_site->query("UPDATE parametre SET valeur = '$ribcompte' WHERE parametre ='RIBcompte'");
		$DB_site->query("UPDATE parametre SET valeur = '$ribcle' WHERE parametre ='RIBrice'");
		$DB_site->query("UPDATE parametre SET valeur = '$iban' WHERE parametre ='IBAN'");
		$DB_site->query("UPDATE parametre SET valeur = '$bic' WHERE parametre ='BIC'");
		$action="";
		$texteSuccess1 = $multilangue[coordonees_banquaires_enregistrees];
		eval(charge_template($langue,$referencepage,"Success1"));
	}else{
		header('location: moyens_reglement.php?erreurdroits=1');	
	}
}
//************************************************ GESTION EDIT CONTRE REMBOURSEMENT *********************************************
if ($action == 'edit_contreremboursement'){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE parametre SET valeur = '$contre_remboursement' WHERE parametre = 'prix_contre_remboursement'");	
		$action="";
		$texteSuccess = $multilangue[prix_contre_remboursement_edite];
		eval(charge_template($langue,$referencepage,"Success"));
	}else{
		header('location: moyens_reglement.php?erreurdroits=1');	
	}
}
//************************************************ GESTION BTN RADIO DEFAUT *********************************************
if ($action2 == "infos") {
	if($admin_droit[$scriptcourant][ecriture]){
		if ($changedefaut == 1) {
			$DB_site->query("update moyenpaiement set defaut = '0'");
			$DB_site->query("update moyenpaiement set defaut = '1' where moyenid = '$defaut'");
		}
		if ($decrementer == 1) {
			foreach ($decrementation as $key => $value)
				$DB_site->query("update moyenpaiement set decrementer = '$value' where moyenid = '$key'");
		}
		if ($incrementerDelai == 1) {
			foreach ($delai as $key => $value){
				if (is_numeric($value) and $value >= 0)
					$DB_site->query("update moyenpaiement set incrementerDelai = '$value' where moyenid = '$key'");
			}
		}
	}else{
		header('location: moyens_reglement.php?erreurdroits=1');	
	}
}
//************************************************ GESTION AFFICHAGE INITIAL *********************************************
if ($action == editermoyenreglement) {
	if($admin_droit[$scriptcourant][ecriture]){
		$sites = $DB_site->query("SELECT * FROM site");
		$moyensreglement = $DB_site->query("SELECT * FROM moyenpaiement_site");
		while ($moyenreglement = $DB_site->fetch_array($moyensreglement)){
			$decrementation = "decrementation_".$moyenreglement[moyenid];
			$reincrementation = "reincrementation_".$moyenreglement[moyenid];
			$passage = "passage_".$moyenreglement[moyenid]; 
			$validation = "validation_".$moyenreglement[moyenid];
			$avertir = "avertir_".$moyenreglement[moyenid];	
			$moyenidtab = $moyenreglement[moyenid];
			
			if (${$passage} !=1) {
				${$passage} = 0;
			}
			if (${$validation} !=1) {
				${$validation} = 0;
			}
			if (${$avertir} !=1) {
				${$avertir} = 0;
			}	
			$DB_site->query("UPDATE moyenpaiement SET decrementer = '${$decrementation}', 
														incrementerDelai = '${$reincrementation}', 
														passage = '${$passage}',
														validation = '${$validation}',
														avertir = '${$avertir}'
							WHERE moyenid = '$moyenreglement[moyenid]'");		
		}
		
			
			$DB_site->query("UPDATE moyenpaiement SET defaut = '0'");
			$DB_site->query("UPDATE moyenpaiement_site SET defaut = '0'");
			//echo" Nouveau : $defaut";
						
			$DB_site->query("UPDATE moyenpaiement SET defaut = '1' WHERE moyenid = '$defaut'");
			$DB_site->query("UPDATE moyenpaiement_site SET defaut = '1' WHERE moyenid = '$defaut'");
		
		$action="";
		$texteSuccessMR1 = $multilangue[moyen_paiement_edite];
		eval(charge_template($langue,$referencepage,"SuccessMR1"));
	}else{
		header('location: moyens_reglement.php?erreurdroits=1');	
	}
}

//************************************************ GESTION MOYEN DE REGLEMENT ACTIF *********************************************

if ($action=="active"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($pastille == "vert") 
			$cacher = 0 ; 
		else 
			$cacher = 1 ;
			
		$sites = $DB_site->query("SELECT * FROM site");
		while($site = $DB_site->fetch_array($sites)){
			$moyen_reglement_par_defaut = $DB_site->query_first("SELECT defaut FROM moyenpaiement WHERE moyenid='$moyenid'");
			$existe = $DB_site->query("SELECT * FROM moyenpaiement_site WHERE siteid='$site[siteid]' AND moyenid='$moyenid'");
			if($DB_site->num_rows($existe)){
				$DB_site->query("UPDATE moyenpaiement_site SET activeV1 = '$cacher' WHERE moyenid = '$moyenid' AND siteid='$site[siteid]'");
			}else{
				$DB_site->query("INSERT INTO moyenpaiement_site (moyenid, siteid, defaut ,activeV1) VALUES ('$moyenid', '$site[siteid]', '$moyen_reglement_par_defaut[defaut]' , '$cacher')");	
			}
		}
		header("location: moyens_reglement.php");
	}else{
		header('location: moyens_reglement.php?erreurdroits=1');	
	}
}

if ($action=="active2"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($pastille2 == "vert")
			$cacher = 0 ;
		else
			$cacher = 1 ;
	
		$sites = $DB_site->query("SELECT * FROM site");
		while($site = $DB_site->fetch_array($sites)){
			$moyen_reglement_par_defaut = $DB_site->query_first("SELECT defaut FROM moyenpaiement WHERE moyenid='$moyenid'");
			$existe = $DB_site->query("SELECT * FROM moyenpaiement_site WHERE siteid='$site[siteid]' AND moyenid='$moyenid2'");
			if($DB_site->num_rows($existe)){
				$DB_site->query("UPDATE moyenpaiement_site SET activeV2 = '$cacher' WHERE moyenid = '$moyenid2' AND siteid='$site[siteid]'");
			}else{
				$DB_site->query("INSERT INTO moyenpaiement_site (moyenid, siteid, defaut, activeV2) VALUES ('$moyenid2', '$site[siteid]', '$moyen_reglement_par_defaut[defaut]' , '$cacher')");	
			}
		}
	
		header("location: moyens_reglement.php");
	}else{
		header('location: moyens_reglement.php?erreurdroits=1');	
	}
}

//************************************************ GESTION AFFICHAGE INITIAL *********************************************
if (!isset($action) || $action == ""){
	$sites = $DB_site->query("SELECT * FROM site");		
	$contreRemboursement = $DB_site->query_first("SELECT * FROM parametre WHERE parametre = 'prix_contre_remboursement'");
	$banque = $DB_site->query_first("SELECT * FROM parametre WHERE parametre = 'RIBetab'");
	$guichet = $DB_site->query_first("SELECT * FROM parametre WHERE parametre = 'RIBguichet'");
	$compte = $DB_site->query_first("SELECT * FROM parametre WHERE parametre = 'RIBcompte'");
	$cle = $DB_site->query_first("SELECT * FROM parametre WHERE parametre = 'RIBrice'");
	$iban = $DB_site->query_first("SELECT * FROM parametre WHERE parametre = 'IBAN'");
	$bic = $DB_site->query_first("SELECT * FROM parametre WHERE parametre = 'BIC'");
	$adressepaypal = $DB_site->query_first("SELECT * FROM parametre WHERE parametre ='emailComptePaypal'");
	$TemplateMoyens_reglementListePaysBit="";
	$moyens_reglement = $DB_site->query("SELECT * FROM moyenpaiement ORDER BY position");	
	while ($infosMoyenpaiement = $DB_site->fetch_array($moyens_reglement)){
		$moyen_reglement = $DB_site->query_first("SELECT * FROM moyenpaiement_site WHERE moyenid = '$infosMoyenpaiement[moyenid]' AND siteid='1'");
		
		//echo "libelle : $moyen_reglement[libelle]/ nb : $infosMoyenpaiement[decrementer]///";
		
		if($moyen_reglement[activeV1]==1){
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
		
		if($moyen_reglement[activeV2]==1){
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
		
		/*$ischecked0 = "checkeddecrementation".$infosMoyenpaiement[moyenid];
		${$ischecked0} = "checked";
		
		echo "${$ischecked0}/ $ischecked0/////";*/

		$checkeddecrementation0=$checkeddecrementation1=$checkeddecrementation2="";
		
		$varTemp = "checkeddecrementation".$infosMoyenpaiement[decrementer];
		${$varTemp} = "checked=\"checked\"";
		
	
		if ($infosMoyenpaiement[defaut] == 1) {
			$ischecked = "checked=\"checked\"";
		}else{
			$ischecked = "";
		}
		
		
		$checkedbox1=$checkedbox2=$checkedbox3="";
		$ifchecked1 = "checkedbox1";
		$ifchecked2 = "checkedbox2";
		$ifchecked3 = "checkedbox3";
			
		if ($infosMoyenpaiement[passage] == 1) {
			${$ifchecked1} = "checked=\"checked\"";
		}
		if ($infosMoyenpaiement[validation] == 1) {
			${$ifchecked2} = "checked=\"checked\"";;
		}
		if ($infosMoyenpaiement[avertir] == 1) {
			${$ifchecked3} = "checked=\"checked\"";;
		}
		
		$TemplateMoyens_reglementActiveBit="";
		$TemplateMoyens_reglementInactiveBit="";
		$TemplateMoyens_reglementEditActiveBit="";
		$TemplateMoyens_reglementEditInactiveBit="";
		if ($moyen_reglement[activeV1] == 1 || $moyen_reglement[activeV2] == 1 || $moyen_reglement[moyenid] == "3"){
			$TemplateMoyens_reglementDelai="";
			if	($varTemp == "checkeddecrementation0"){
				eval(charge_template($langue,$referencepage,"Delai"));
			}
			
			//if ($moyen_reglement[moyenid] == '1' || $moyen_reglement[moyenid] == '2' || $moyen_reglement[moyenid] == '7' || $moyen_reglement[moyenid] == '6' || $moyen_reglement[moyenid] == '5'){
				eval(charge_template($langue,$referencepage,"EditActiveBit"));
			/*}else{
				eval(charge_template($langue,$referencepage,"EditInactiveBit"));
			}*/
			eval(charge_template($langue,$referencepage,"ActiveBit"));
		}else{
			eval(charge_template($langue,$referencepage,"InactiveBit"));
			eval(charge_template($langue,$referencepage,"EditInactiveBit"));
		}
		
		eval(charge_template($langue,$referencepage,"ListePaysBit"));
	}
	eval(charge_template($langue,$referencepage,"ListePays"));
	eval(charge_template($langue,$referencepage,"Liste"));
	//eval(charge_template($langue,$referencepage,"Contreremboursement"));
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