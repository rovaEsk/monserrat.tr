<?php


include "includes/header.php";

$referencepage="gestion_modules_html5";
$pagetitle = "Gestion des modules html5 - $host - Admin Arobases";

$titrepage=$multilangue[gestion_modules_html5];
$lienpagebase="gestion_modules_html5.php";
$niveaunavigsup="";

$class_menu_gestion_modules_html5_active = "active";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
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
