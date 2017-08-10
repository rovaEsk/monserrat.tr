<?php
include "./includes/header.php";

$referencepage="purges_donnees";
$pagetitle = "Purge des données - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if (isset($action) and $action == "purger"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($admin_historique || $newsletter || $traffic || $erreur404){
			$texteSuccess = "";
			if ($admin_historique == 1){
				$DB_site->query("TRUNCATE TABLE `admin_historique`");
				$texteSuccess .= ($texteSuccess == "" ? "" : "<br>") . $multilangue[historique_logs_purges];
			}
			if ($newsletter == 1){
				$DB_site->query("TRUNCATE TABLE `newsletter`");
				$texteSuccess .= ($texteSuccess == "" ? "" : "<br>") . $multilangue[newsletters_purges];
			}
			if ($traffic == 1){
				$DB_site->query("TRUNCATE TABLE `traffic`");
				$texteSuccess .= ($texteSuccess == "" ? "" : "<br>") . $multilangue[trafic_purges];
			}
			if ($erreur404 == 1){
				$DB_site->query("TRUNCATE TABLE `erreur404`");
				$texteSuccess .= ($texteSuccess == "" ? "" : "<br>") . $multilangue[erreurs404_purges];
			}
			header("location: purges_donnees.php?texteSuccess=$texteSuccess");
		}else{
			header('location: purges_donnees.php');
		}
	}else{
		header('location: purges_donnees.php?erreurdroits=1');	
	}
}
	
if (!isset($action) or $action == ""){
	if ($texteSuccess != "")
		eval(charge_template($langue,$referencepage,"Success"));
	$texteErreur = "<strong>$multilangue[attention_purge]</strong>";
	eval(charge_template($langue,$referencepage,"ErreurFixe"));
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