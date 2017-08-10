<?php
include "./includes/header.php";

$referencepage="gestion_site";
$pagetitle = "Gestion des sites - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if ($succes == "2"){
	$nomSiteSuccess = $DB_site->query_first("SELECT * FROM site WHERE siteid = '$idSite'");
	$texteSuccess = $multilangue[le_site] ." <strong>\"".$nomSiteSuccess[libelle]."\"</strong> ".$multilangue[a_bien_ete_cre] ; 
	eval(charge_template($langue,$referencepage,"Success"));
}
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}
//GESTION EDIT SITE
if(isset($action) && $action == "doediter"){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE site 
							SET libelle = '".addslashes($libellesite)."'
							WHERE siteid = '$idSite'");
		if(isset($color)){
			$DB_site->query("UPDATE site
							SET classcolor = '$color'
							WHERE siteid = '$idSite'");
		}
		$DB_site->query("UPDATE site 
							SET langueid = '$langueSite'
							WHERE siteid = '$idSite'");
		$DB_site->query("DELETE FROM site_pays WHERE siteid='$idSite'");
		if(sizeof($chk_pays) > 0){		
			foreach ($chk_pays as $key => $value){			
				$DB_site->query("INSERT INTO site_pays (siteid,paysid) VALUES ('$idSite','$value')");
				$DB_site->query("UPDATE pays SET siteid = $idSite WHERE paysid = $value");
			}
		}
		$nomSiteSuccess = $DB_site->query_first("SELECT * FROM site WHERE siteid = '$idSite'");
		header("location: gestion_site.php?action=editer&idSite=$idSite&succes=1");
	}else{
		header('location: gestion_site.php?erreurdroits=1');	
	}
}

//************************************************** GESTION EDITION SITE *********************************************************************************
if(isset($action) && $action == "editer"){
	$infoSite2 = $DB_site->query_first("SELECT s.*, l.libelle AS liblangue, l.langueid AS langueid
										FROM site AS s
										INNER JOIN langue AS l USING(langueid)
										 WHERE siteid = $idSite");
	$var_active="active_".str_replace("-","",$infoSite2[classcolor]);
	${$var_active}="active";
	$var_checked="checked_".str_replace("-","",$infoSite2[classcolor]);
	${$var_checked}="checked=\"checked\"";
	//GESTION FLAGS CHECKBOXES
	$reqFlagCheck = $DB_site->query("SELECT * FROM pays 
										WHERE paysid NOT IN(
											SELECT paysid 
											FROM site_pays 
											WHERE siteid != '$idSite'
										)
										ORDER BY libelle");
	while ($flagCheckBoxes = $DB_site->fetch_array($reqFlagCheck)){
		$payssite = $DB_site->query_first("SELECT * FROM site_pays 
											WHERE siteid='$idSite' 
											AND paysid='$flagCheckBoxes[paysid]'");
		$input_hidden_pays_defaut="";
		$checked="";
		if($payssite[paysid]){		
			$checked="checked=\"checked\"";
			if($flagCheckBoxes[paysid] == $infoSite2[paysid]){
				$checked.=" disabled=\"disabled\"";
				$input_hidden_pays_defaut="<input type=\"hidden\" name=\"chk_pays[$infoSite2[paysid]]\" value=\"$infoSite2[paysid]\">";
			}			
		}		
		$libelleTagPays = strtolower($flagCheckBoxes['diminutif']);
		eval(charge_template($langue, $referencepage, "ModifSiteBit"));
	}
	$liste_langues = $DB_site->query("SELECT * FROM langue ORDER BY libelle");
	$langue_selected = $DB_site->query_first("SELECT langueid FROM site WHERE siteid='$idSite'");
	while($langue = $DB_site->fetch_array($liste_langues)){
		if($langue[langueid] == $langue_selected[langueid]){
			$select = 'selected="selected"';	
		} else {
			$select = "";	
		}
		eval(charge_template($langue,$referencepage,"ListeLangueBit"));
	}
	eval(charge_template($langue, $referencepage, "ModifSite"));
	if ($succes == "1"){
		$nomSiteSuccess = $DB_site->query_first("SELECT * FROM site WHERE siteid = '$idSite'");
		$texteSuccess = $multilangue[le_site] ." <strong>\"$nomSiteSuccess[libelle]\"</strong> ".$multilangue[a_bien_ete_modifie];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	$libNavigSupp=$multilangue[modification_du]." <i><b>\"$infoSite2[libelle]\"</b></i>";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}

//GESTION AJOUT SITE
if (isset($action) && ($action== "doajout")){
	if($admin_droit[$scriptcourant][ecriture]){
		$execInsertNewSite = $DB_site->query("INSERT INTO  site(libelle, deviseid, langueid, paysid, classcolor)
		values('".securiserSql($_POST[libellesite])."', '".securiserSql($_POST[deviseSite])."', '".securiserSql($_POST[langueSite])."', '".securiserSql($_POST[pays])."', '".securiserSql($_POST[color])."')");
		$idSite = $DB_site->insert_id();
		if(sizeof($chk_pays) > 0){
			foreach ($chk_pays as $key => $value){
				$DB_site->query("INSERT INTO site_pays (siteid,paysid) VALUES ('".securiserSql($idSite)."','".securiserSql($value)."')");
			}
		}
		$nomSiteSuccess=$_POST[libellesite];
		header("location: gestion_site.php?idSite=$idSite&succes=2");
	}else{
		header('location: gestion_site.php?erreurdroits=1');	
	}
}
	
if(isset($action) && $action == "ajout"){
	$optionsPays=retournerListePays($DB_site, 57);
	$idSite = 0;
	$infoSite2 = $DB_site->query_first("SELECT s.*, l.libelle AS liblangue
										FROM site AS s
										INNER JOIN langue AS l USING(langueid)
										WHERE siteid = $idSite");
	$var_active="active_".str_replace("-","",$infoSite2[classcolor]);
	${$var_active}="active";
	//GESTION FLAGS CHECKBOXES
	$reqFlagCheck = $DB_site->query("SELECT * FROM pays
										WHERE paysid NOT IN(
										SELECT paysid
										FROM site_pays
										WHERE siteid != '$idSite')		
										ORDER BY libelle");
	while ($flagCheckBoxes = $DB_site->fetch_array($reqFlagCheck)){
		$payssite = $DB_site->query_first("SELECT * FROM site_pays
		WHERE siteid='$idSite'
		AND paysid='$flagCheckBoxes[paysid]'");
		$checked="";
		if($payssite[paysid]){
		$checked="checked=\"checked\"";
		}
		$libelleTagPays = strtolower($flagCheckBoxes['diminutif']);
		eval(charge_template($langue, $referencepage, "AjoutSiteBit"));
	}
	$devises = $DB_site->query("SELECT * FROM devise WHERE deviseid != '1' ORDER BY contenu");
	while($devise = $DB_site->fetch_array($devises)){
		eval(charge_template($langue,$referencepage,"ListeDeviseBit"));
	}
	$langues = $DB_site->query("SELECT * FROM langue ORDER BY libelle");
	while($langue = $DB_site->fetch_array($langues)){
		eval(charge_template($langue,$referencepage,"ListeLangueBit"));
	}
	eval(charge_template($langue, $referencepage, "AjoutSite"));
}	

//GESTION AFFICHAGE LISTE SITES
if (!isset($action) or ($action == "")) {
	
	$listeSites = $DB_site->query("SELECT * FROM site");
	while ($listeSite = $DB_site->fetch_array($listeSites)){
		$TemplateGestion_sitePaysLivreBit="";
		$langueSite = $DB_site->query_first("SELECT libelle
												FROM langue l
												WHERE l.langueid = $listeSite[langueid]");
		
		$siteDevise = $DB_site->query_first("SELECT contenu
												FROM devise d
												WHERE d.deviseid = $listeSite[deviseid]");
		
		$reqFlagCheck = $DB_site->query("SELECT * FROM site_pays AS sp INNER JOIN pays AS p ON sp.paysid = p.paysid WHERE sp.siteid = '$listeSite[siteid]' ORDER BY libelle");

			
			while ($flagCheckBoxes = $DB_site->fetch_array($reqFlagCheck)){
				if($flagCheckBoxes[paysid]==$listeSite[paysid]){
					$libelle_pays_livre = "<b>$flagCheckBoxes[libelle]</b><br>";
				} else {
					$libelle_pays_livre = "$flagCheckBoxes[libelle]<br>";
				}
				eval(charge_template($langue, $referencepage, "PaysLivreBit"));
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
