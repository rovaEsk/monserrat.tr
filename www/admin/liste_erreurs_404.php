<?php
include "./includes/header.php";

$referencepage="liste_erreurs_404";
$pagetitle = "Liste des erreurs 404 - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if (isset($action) and $action == "purger"){
	if($admin_droit[$scriptcourant][suppression]){
		$dateline = date("Y-m-d", strtotime(str_replace('/', '-', $_POST[dateline])));
		$DB_site->query("DELETE FROM erreur404 WHERE dateline < '$dateline'");
		header('location: liste_erreurs_404.php');
	}else{
		header('location: liste_erreurs_404.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "supprimer"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM erreur404 WHERE erreurid = '$erreurid'");
	}else{
		header('location: liste_erreurs_404.php?erreurdroits=1');	
	}
}

if (!isset($action) or $action == ""){
	$dateline = date("01/m/Y");
	$erreurs = $DB_site->query("SELECT * FROM erreur404");
	while ($erreur = $DB_site->fetch_array($erreurs)){
		$erreur[dateline] = date("d/m/Y", strtotime($erreur[dateline]));
		$redirection = $DB_site->query_first("SELECT * FROM redirections WHERE old_url = '$erreur[url]'");
		if($redirection){
			$erreur[redirection] = $redirection[new_url];
		}else{
			$erreur[redirection] = "<a href='gestion_redirections.php?old_url=".substr($erreur[url], 1)."' class='btn blue'>$multilangue[creer]</a>";
		}
		eval(charge_template($langue, $referencepage, "ListeBit"));
	}
	
	
	$libNavigSupp = $multilangue[liste_erreurs_404];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	eval(charge_template($langue, $referencepage, "Liste"));
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