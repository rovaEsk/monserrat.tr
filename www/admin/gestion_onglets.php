<?php
include "./includes/header.php";

$referencepage="gestion_onglets";
$pagetitle = "Gestion des onglets - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

clearDir($GLOBALS[rootpath]."configurations/$GLOBALS[host]/cache/onglets/");

//$mode = "test_modules";

if(!isset($action) || $action = "" ){	
	if(!isset($idsite)){
		$idsite = '1';
	}
	$param_nb_onglets = $DB_site->query_first("SELECT valeur FROM parametre WHERE parametre='nb_onglets_max'");
	$nb_onglets = $param_nb_onglets[valeur];
	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
	while($site = $DB_site->fetch_array($sites)){
		$selected_site="";
		if($site[siteid] == $idsite){
			$selected_site="selected=\"selected\"";
		}		
		eval(charge_template($langue,$referencepage,"ListeSiteBit"));
	}
	$libNavigSupp = $multilangue[selection_onglets];
	eval(charge_template($langue, $referencepage, "NavigSupp"));
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