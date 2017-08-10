<?php
include "./includes/header.php";

$referencepage="tags";
$pagetitle = "Gestion des tags - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if (isset($action) and $action == "zero"){
	if($admin_droit[$scriptcourant][ecriture]){
		if (isset($tagsiteid))
			$DB_site->query("UPDATE tags SET hit = '0' WHERE tagid = '$tagid' AND siteid = '$siteid'");
		else
			$DB_site->query("UPDATE tags SET hit = '0' WHERE tagid = '$tagid'");
		header('location: tags.php');
	}else{
		header('location: tags.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "supprimer"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM tags WHERE tagid = '$tagid'");
		header('location: tags.php');
	}else{
		header('location: tags.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "modifier2"){
	if($admin_droit[$scriptcourant][ecriture]){
		$nouveautag = 0;
		if($tagid == ""){
			$DB_site->query("INSERT INTO tags(tagid, siteid) VALUES ('', '1')");
			$tagid = $DB_site->insert_id();
			$nouveautag = 1;
		}
		$DB_site->query("UPDATE tags SET tag = '" . securiserSql($_POST[tag]) . "',
						description = '" . securiserSql($_POST[description]) . "',
						ref_title = '" . securiserSql($_POST[ref_title]) . "',
						ref_description = '" . securiserSql($_POST[ref_description]) . "',
						ref_keywords = '" . securiserSql($_POST[ref_keywords]) . "'
						WHERE tagid = '$tagid' AND siteid = '1'");
		$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
		while ($site = $DB_site->fetch_array($sites)){
			$tagsite = $DB_site->query_first("SELECT * FROM tags WHERE tagid = '$tagid' AND siteid = '$site[siteid]'");
			if ($nouveautag || $tagsite[tagid] == "")
				$DB_site->query("INSERT INTO tags(tagid, siteid) VALUES ('$tagid', '$site[siteid]')");
			$DB_site->query("UPDATE tags SET tag = '" . securiserSql($_POST["tag$site[siteid]"]) . "',
							description = '" . securiserSql($_POST["description$site[siteid]"]) . "',
							ref_title = '" . securiserSql($_POST["ref_title$site[siteid]"]) . "',
							ref_description = '" . securiserSql($_POST["ref_description$site[siteid]"]) . "',
							ref_keywords = '" . securiserSql($_POST["ref_keywords$site[siteid]"]) . "'
							WHERE tagid = '$tagid' AND siteid = '$site[siteid]'");
		}
		header('location: tags.php');
	}else{
		header('location: tags.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "modifier"){
	$siteprincipal = $DB_site->query_first("SELECT * FROM site WHERE siteid = '1'");
	$texteInfo = "$multilangue[infos_balises_meta].<br><br>";
	$texteInfo .= "$multilangue[la_variable] <strong>[boutiquetitre]</strong> $multilangue[remplacement_boutiquetitre].<br>";
	$texteInfo .= "<span>$multilangue[exemple] : $multilangue[boutique] 4</span><br><br>";
	$texteInfo .= "$multilangue[la_variable] <strong>[tagtitre]</strong>".$multilangue[sera_remplacee]."<br>";
	$texteInfo .= "<span>$multilangue[exemple] : ".$multilangue[livre_cuisine]."</span>";
	if (isset($tagid)){
		$tags = $DB_site->query_first("SELECT * FROM tags WHERE tagid = '$tagid' AND siteid = '1'");
		$libNavigSupp = "$multilangue[modification] : <b>\"$tags[tag]\"</b>";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}else{
		$libNavigSupp = $multilangue[ajt_tag];
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}
	$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
	while ($site = $DB_site->fetch_array($sites)){
		$tagsite = $DB_site->query_first("SELECT * FROM tags WHERE tagid = '$tagid' AND siteid = '$site[siteid]'");
		eval(charge_template($langue, $referencepage, "ModificationSiteBit"));
	}
	eval(charge_template($langue, $referencepage, "Modification"));
}

if (!isset($action) or $action == ""){
	$tags = $DB_site->query("SELECT * FROM tags WHERE siteid = '1'");
	while ($tag = $DB_site->fetch_array($tags)){
		$tagcount = $DB_site->query_first("SELECT SUM(hit) hit FROM tags WHERE tagid = '$tag[tagid]'");
		$TemplateTagsListeDetail = "";
		$tagdetails = $DB_site->query("SELECT * FROM tags INNER JOIN site USING(siteid) WHERE tagid = '$tag[tagid]'");
		while ($tagdetail = $DB_site->fetch_array($tagdetails))
			eval(charge_template($langue,$referencepage,"ListeDetail"));
		eval(charge_template($langue,$referencepage,"ListeBit"));
	}
	$libNavigSupp = $multilangue[liste_tags];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	eval(charge_template($langue,$referencepage,"Liste"));
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