<?php
include "./includes/header.php";

$referencepage="marqueurs_google";
$pagetitle = "Code google analytics - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

//GESTION EDITION CODE GOOGLE ANALYTICS *********************************************************
if ($action == 'doeditcodegoogle'){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($googleanalytics != ""){
			$DB_site->query("UPDATE parametre SET valeur = '".addslashes($googleanalytics)."' WHERE parametre = 'codegoogle'");
			header("location: marqueurs_google.php?alertSuccess=success");
		}else{
			header("location: marqueurs_google.php?alertErreur=erreur");
		}
	}else{
		header('location: marqueurs_google.php?erreurdroits=1');	
	}
}
//GESTION AFFICHAGE INITIAL *********************************************************************
if (!isset($action) || $action == ""){
	$infosga = $DB_site->query_first("SELECT * FROM parametre where parametre = 'codegoogle'");
	if ($alertSuccess == "success"){
		$texteSuccess = $multilangue[code_ga_enregistre];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	if ($alertErreur == "erreur"){
		$texteErreur = $multilangue[all_champs_obligatoires];;
		eval(charge_template($langue,$referencepage,"Erreur"));
	}
}



$TemplateIncludejavascript = eval(charge_template($langue, $referencepage,"Includejavascript"));
eval(charge_template($langue,"commun","header"));
eval(charge_template($langue,"commun","footer"));
eval(charge_template($langue,$referencepage,"index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};
var_dump($pathpic);

$DB_site->close();
flush();

?>