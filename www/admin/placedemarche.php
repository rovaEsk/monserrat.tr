<?php
include "./includes/header.php";

$referencepage="placedemarche";
$pagetitle = "Places de marché - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


if (!isset($action) or $action == ""){
	$texteInfo = $multilangue[infos_places_marche];
	eval(charge_template($langue,$referencepage,"InfoFixe"));
	$placesdemarche = $DB_site->query("SELECT * FROM placedemarche WHERE active = '1'");
	while($placedemarche = $DB_site->fetch_array($placesdemarche)){
		$infosmarche = $DB_site->query_first("SELECT * FROM placedemarche_langue WHERE placedemarcheid = '$placedemarche[placedemarcheid]'");
		if ($placedemarche[fichier] != ""){
			$lien = "";
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