<?php
include "./includes/header.php";

// TITRE ET NOM DE LA PAGE
$referencepage="balises_meta";
$pagetitle = "Balises META par défaut - $host - Admin Arobases";

// CHARGEMENT TEMPLATE
if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

$tab = array(1 => $multilangue[page_accueil],
		2 => $multilangue[page_rayon],
		3 => $multilangue[page_article],
		4 => $multilangue[page_instit],
		5 => $multilangue[page_recherche],
		6 => $multilangue[page_client],
		7 => $multilangue[page_recup_mdp],
		8 => $multilangue[page_cde1],
		9 => $multilangue[page_cde2],
		10 => $multilangue[page_cde3],
		11 => $multilangue[page_contact],
		12 => $multilangue[page_recherche_marque],
		13 => $multilangue[page_tops],
		14 => $multilangue[page_plan_site],
		15 => $multilangue[page_opti],
		16 => $multilangue[marques],
		17 => $multilangue[page_tag]);


// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if(isset($succes) and $succes == 1){	
	$texteSuccess = $multilangue[meta_defaut_save];
	eval(charge_template($langue, $referencepage, "Success"));
}

// ENREGISTREMENT MODIFICATION
if (isset($action) and $action == "modifier2"){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE meta_site SET title = '" . securiserSql($_POST[title]) . "',
						description = '" . securiserSql($_POST[description]) . "',
						keywords = '" . securiserSql($_POST[keywords]) . "'
						WHERE metaid = '$metaid' AND siteid = '1'");
		$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
		while($site = $DB_site->fetch_array($sites)){
			$balisemeta = $DB_site->query_first("SELECT * FROM meta_site WHERE metaid = '$metaid' AND siteid = '$site[siteid]'");
			if($balisemeta[metaid] == ""){
				$DB_site->query("INSERT INTO meta_site (metaid, siteid) VALUES ('$metaid','$site[siteid]')");
			}
			$titlesite = "title$site[siteid]";
			$descriptionsite = "description$site[siteid]";
			$keywordssite = "keywords$site[siteid]";
			$DB_site->query("UPDATE meta_site SET title = '" . securiserSql($_POST[$titlesite]) . "',
							description = '" . securiserSql($_POST[$descriptionsite]) . "',
							keywords = '" . securiserSql($_POST[$keywordssite]) . "'
							WHERE metaid = '$metaid' AND siteid = '$site[siteid]'");
		}
		header("location: balises_meta.php?succes=1&metaid=$metaid");
	}else{
		header('location: balises_meta.php?erreurdroits=1');	
	}
}

// MODIFICATION
if (isset($action) and $action == "modifier") {	
	$siteprincipal = $DB_site->query_first("SELECT * FROM site WHERE siteid = '1'");
	$balisemeta = $DB_site->query_first("SELECT * FROM meta INNER JOIN meta_site USING(metaid) WHERE metaid = '$metaid'");
	if (in_array(5, $modules) || in_array(17, $modules) || in_array(19, $modules) || in_array(21, $modules) || in_array(5872, $modules))
		eval(charge_template($langue,$referencepage,"ModificationListearticlestop"));
	elseif (in_array(123, $modules))
		eval(charge_template($langue,$referencepage,"ModificationOptititre"));
	elseif (in_array(134, $modules))
		eval(charge_template($langue,$referencepage,"ModificationTag"));
	$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
	while ($site = $DB_site->fetch_array($sites)){
		$balisemetasite = $DB_site->query_first("SELECT * FROM meta INNER JOIN meta_site USING(metaid) WHERE metaid = '$metaid' AND siteid = '$site[siteid]'");
		eval(charge_template($langue,$referencepage,"ModificationSiteBit"));
	}
	$libNavigSupp = "$multilangue[modification] : " . $tab[$balisemeta[metaid]];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	eval(charge_template($langue,$referencepage,"Modification"));
}

// PAGE DE DEPART
if (!isset($action) or $action == ""){
	$balisesmeta = $DB_site->query("SELECT * FROM meta");
	while ($balisemeta = $DB_site->fetch_array($balisesmeta)){
		$libelle = $tab[$balisemeta[metaid]];
		if ($balisemeta[metaid] == 15 && in_array(123, $modules) || $balisemeta[metaid] == 17 && in_array(134, $modules))
			eval(charge_template($langue,$referencepage,"ListeBit"));
		elseif ($balisemeta[metaid] != 15 && $balisemeta[metaid] != 17)
			eval(charge_template($langue,$referencepage,"ListeBit"));
	}
	eval(charge_template($langue,$referencepage,"Liste"));
}

// AFFICHAGE PAGE
$TemplateIncludejavascript = eval(charge_template($langue, $referencepage, "Includejavascript"));
eval(charge_template($langue, "commun", "header"));
eval(charge_template($langue, "commun", "footer"));
eval(charge_template($langue, $referencepage, "index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();

?>