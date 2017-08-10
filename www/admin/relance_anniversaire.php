<?php
include "./includes/header.php";

$referencepage="relance_anniversaire";
$pagetitle = "Relance anniversaire - $host - Admin Arobases";



if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

//****************************************************** GESTION ACTION EDITION INSERTION EMAIL RELANCE *******************************************************************
if ($action == 'doeditrelance'){
	
}
//****************************************************** GESTION ACTION EDITION PARAMETRES *******************************************************************
if ($action == 'doeditparam'){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE parametre SET valeur = '$reduction' WHERE parametre = 'anniversaire_envoicadeau'");
		$DB_site->query("UPDATE parametre SET valeur = '$montant' WHERE parametre = 'anniversaire_montantcadeau'");
		$DB_site->query("UPDATE parametre SET valeur = '$remise' WHERE parametre = 'anniversaire_typecadeau'");
		$DB_site->query("UPDATE parametre SET valeur = '$valide' WHERE parametre = 'anniversaire_delaicadeau'");
		
		$sites = $DB_site->query("SELECT * FROM site");
		while ($site = $DB_site->fetch_array($sites)){
			$sujet = "sujet_".$site[siteid];
			$contenu = "contenu_".$site[siteid];
			$test = $DB_site->query_first("SELECT * FROM mail_type_site WHERE emailid = '15' && siteid = '$site[siteid]'");
			if ($test[emailid] != ""){
				$DB_site->query("UPDATE mail_type_site SET sujet = '".securiserSql(${$sujet})."',
															contenu = '".securiserSql(${$contenu}, "html")."'
																WHERE siteid = '$site[siteid]' && emailid = '15'");
			}else{
				$DB_site->query("INSERT INTO mail_type_site(emailid, siteid, sujet, contenu)
						VALUES('15', '$site[siteid]', '".securiserSql(${$sujet})."', '".securiserSql(${$contenu}, "html")."')");
			}
		}
		header("location: relance_anniversaire.php?alertSuccess1=success");
	}else{
		header('location: relance_anniversaire.php?erreurdroits=1');	
	}
}
//****************************************************** GESTION AFFICHAGE INITIAL *******************************************************************
if (!isset ($action) || $action ==""){
	$montantcadeau = $DB_site->query_first("SELECT * FROM parametre WHERE parametre = 'anniversaire_montantcadeau'");
	$validitecadeau = $DB_site->query_first("SELECT * FROM parametre WHERE parametre = 'anniversaire_delaicadeau'");
	$envoicodereduc = $DB_site->query_first("SELECT * FROM parametre WHERE parametre = 'anniversaire_envoicadeau'");
	$typecadeau = $DB_site->query_first("SELECT * FROM parametre WHERE parametre = 'anniversaire_typecadeau'");
	$sites = $DB_site->query("SELECT * FROM site");
	if ($alertSuccess == "success"){
		$texteSuccess = $multilangue[relance_anniversaire_edition];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	if ($alertSuccess1 == "success"){
		$texteSuccess = $multilangue[relance_anniversaire_edition_mail];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	if($envoicodereduc[valeur] == '1'){
		$checkedv1 = "checked = \"checked\"";	
	}
	if($typecadeau[valeur] == '1'){
		$checkedv2 = "checked = \"checked\"";
	}
	while ($site = $DB_site->fetch_array($sites)){
		$infosmailsite = $DB_site->query_first("SELECT * FROM mail_type_site WHERE siteid = '$site[siteid]' && emailid = '15'");
		eval(charge_template($langue,$referencepage,"EditrelancesiteBit"));
		
	}
	eval(charge_template($langue,$referencepage,"FormrelancesiteBit"));
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
