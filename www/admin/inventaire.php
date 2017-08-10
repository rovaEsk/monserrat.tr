<?
include "./includes/header.php";

$referencepage="inventaire";
$pagetitle = "Inventaire - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

//$mode = "test_modules";

if(!isset($action) || $action = ""){
	
	if (in_array("122", $modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"ModulePrixPro"));
		eval(charge_template($langue,$referencepage,"ModuleModifPrixPro"));
	}
	
	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
	while($site = $DB_site->fetch_array($sites)){
		eval(charge_template($langue,$referencepage,"ListeSiteBit"));
	}
	$fournisseurs = $DB_site->query("SELECT fournisseurid, libelle FROM fournisseur");
	if($DB_site->num_rows($fournisseurs) > 0){
		while($fournisseur = $DB_site->fetch_array($fournisseurs)){
			eval(charge_template($langue,$referencepage,"ListeFournisseur"));
		}
		eval(charge_template($langue,$referencepage,"Fournisseur"));
	}
	eval(charge_template($langue,$referencepage,"Liste"));
	
}


$TemplateIncludejavascript = eval(charge_template($langue,  $referencepage, "Includejavascript"));
eval(charge_template($langue, "commun", "header"));
eval(charge_template($langue, "commun", "footer"));
eval(charge_template($langue, $referencepage, "index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();
?>