<?php
include "./includes/header.php";

$referencepage="parametrage_messagerie_outlook";
$pagetitle = "Parametrage messagerie outlook et autres - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


if (!isset($action) or $action == "")
	eval(charge_template($langue, $referencepage, "Liste"));

$TemplateIncludejavascript = eval(charge_template($langue, $referencepage, "Includejavascript"));
eval(charge_template($langue, "commun", "header"));
eval(charge_template($langue, "commun", "footer"));
eval(charge_template($langue, $referencepage, "index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();

?>