<?php
include "./includes/header.php";

$referencepage="emails_auto";
$pagetitle = "E-mails automatiques - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

//************************************************ GESTION ACTION TEST *************************************************************
if ($action == "envoyertest2"){
	if (isset($monmail) && !filter_var($monmail, FILTER_VALIDATE_EMAIL) === false){
		test_mail_perso($DB_site, $monmail, $emailid, $etat);
		$action="";
		$texteSuccess = $multilangue[mail_test];		
		eval(charge_template($langue, $referencepage, "Success"));
	}else{
		$action = "envoyertest" ;
		$texteErreur = $multilangue[mail_test_echec];
		eval(charge_template($langue, $referencepage, "Erreur"));
	}
}
//************************************************ GESTION TEST *************************************************************
/*
if ($action == envoyertest){
	eval(charge_template($langue,$referencepage,"Test"));
}*/
//************************************************ GESTION ACTION EDITION EMAIL *********************************************
if ($action == "doediter"){
	if($admin_droit[$scriptcourant][ecriture]){
		$sites = $DB_site->query("SELECT * FROM site");
		while($site = $DB_site->fetch_array($sites)){
			$sujet = "sujet_".$site[siteid];
			$contenu = "contenu_".$site[siteid];
			
			$test = $DB_site->query_first("SELECT * FROM mail_type_site WHERE siteid = '$site[siteid]' && emailid = '$mailid'");
			if ($test[emailid] != ""){
				$DB_site->query("UPDATE mail_type_site SET sujet = '".securiserSql(${$sujet})."', 
														contenu = '".securiserSql(${$contenu}, "html")."' 
									WHERE siteid = '$site[siteid]' 
									&& emailid = '$mailid'");
			}else{
				$DB_site->query("INSERT INTO mail_type_site(emailid, siteid, sujet, contenu) 
									VALUES('$mailid', '$site[siteid]', '".securiserSql(${$sujet})."', '".securiserSql(${$contenu}, "html")."')");	
			}
			//var_dump(${$sujet});
			//var_dump($siteid);
		}
		$texteSuccess = "Le mail automatique a bien été modifié";			
		header("location: emails_auto.php?mailid=$mailid&alertSuccess=success");
	}else{
		header('location: emails_auto.php?erreurdroits=1');	
	}
}
//************************************************ GESTION EDITION EMAIL ****************************************************
if ($action == "modif"){
	$sites = $DB_site->query("SELECT * FROM site");
	$sujetmail = $DB_site->query_first("SELECT * FROM mail_type_site WHERE emailid='$mailid'");
	while ($site = $DB_site->fetch_array($sites)){
		$langue = $DB_site->query_first("SELECT * FROM langue where langueid = '$site[langueid]'");
		$contentfck=$DB_site->query_first("select * from mail_type_site where emailid = '$mailid' && siteid = '$site[siteid]'");
		$contenumail = $DB_site->query_first("SELECT * FROM mail_type_site WHERE emailid ='$mailid' && siteid = '$site[siteid]'");
		if($site[siteid] != 1){
			$style = "style = \" display : none\"";
			$collapse = "expand";	
		}else{
			$style = "";
			$collapse = "collapse";	
		}
		eval(charge_template($langue,$referencepage,"EditBit"));		
	}
	eval(charge_template($langue,$referencepage,"Edit"));
	$libNavigSupp= $multilangue[modification_mail_automatique]."<i><b>\"$sujetmail[sujet]\"</b></i>";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}
//************************************************ GESTION AFFICHAGE PAR DEFAUT *********************************************
if (!isset($action) || ($action == "")){
	if (in_array(5818, $modules)){
		eval(charge_template($langue,$referencepage,"Module5818"));
	}
	if (in_array(5930, $modules)){
		eval(charge_template($langue,$referencepage,"Module5930"));
	}
	if (in_array(5958, $modules)){
		eval(charge_template($langue,$referencepage,"Module5958"));
	}
	if (in_array(5946, $modules)){
		eval(charge_template($langue,$referencepage,"Module5946"));
	}
	if (in_array(104, $modules)){
		eval(charge_template($langue,$referencepage,"Module104"));
	}
	if (in_array(5888, $modules)){
		eval(charge_template($langue,$referencepage,"Module5888"));
	}
	if (in_array(122, $modules)){
		eval(charge_template($langue,$referencepage,"Module122"));
	}
	if (in_array(5931, $modules)){
		eval(charge_template($langue,$referencepage,"Module5931"));
	}
		
	
	$paiements=$DB_site->query("SELECT DISTINCT(moyenid) FROM moyenpaiement_site AS mps WHERE activeV1 = 1 || activeV2 = 1");
	while ($paiement=$DB_site->fetch_array($paiements)) {
		$passid=$paiement[moyenid]+50;
		$valid=$paiement[moyenid]+100;
		$passage=$DB_site->query_first("SELECT * FROM mail_type where emailid='$passid' ");
		$validation=$DB_site->query_first("SELECT * FROM mail_type where emailid='$valid' ");
		eval(charge_template($langue,$referencepage,"Emailspaiement"));
	}
	
	
	$otherMails=$DB_site->query("SELECT * FROM mail_type WHERE emailid >= '250' ");
	while ($otherMail=$DB_site->fetch_array($otherMails)) {
		eval(charge_template($langue,$referencepage,"Othermail"));		
	}
	
	if ($alertSuccess == success){
		$emailauto = $DB_site->query_first("SELECT * FROM mail_type WHERE emailid = '$mailid'");
		$texteSuccess = $multilangue[l_email_auto]."\" $emailauto[libelle] \"".$multilangue[a_bien_ete_edite];	
		eval(charge_template($langue, $referencepage, "Success"));
	}
	eval(charge_template($langue,$referencepage,"Emailstype"));
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