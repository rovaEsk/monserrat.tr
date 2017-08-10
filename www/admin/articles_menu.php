<?php
include "./includes/header.php";

$referencepage="articles_menu";
$pagetitle = "Articles menu - $host - Admin Arobases";

$scriptcourant = "gestion_onglets.php";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

//$mode = "test_modules";

if(!isset($action) || $action = ""){
	if(!isset($idsite)){
		$idsite = "1";
	}
	
	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
	while($site = $DB_site->fetch_array($sites)){
		$selected_site = "";
		if($site[siteid] == $idsite){
			$selected_site="selected=\"selected\"";
		}
		eval(charge_template($langue,$referencepage,"ListeSiteBit"));
	}
	
	$ordre = "";
	$first = true;
	$articles = $DB_site->query("SELECT * FROM topmenu INNER JOIN article_site USING (artid) INNER JOIN article USING(artid) WHERE topmenu.siteid = '$idsite' AND topmenu.siteid = article_site.siteid ORDER BY position");
	while($article = $DB_site->fetch_array($articles)){
		if($first){
			$ordre.="$article[artid]";
			$first = false;
		} else {
			$ordre.="|$article[artid]";
		}
		eval(charge_template($langue,$referencepage,"ModificationArticle"));
	}
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