<?php
include "./includes/header.php";

$referencepage="tva";
$pagetitle = "Taux de TVA - $host - Admin Arobases";



if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

//************************************************ GESTION MODIF MINIMUM ACHAT *********************************************
if ($action == modiftva){
	if($admin_droit[$scriptcourant][ecriture]){
		$valspays = $DB_site->query("SELECT * FROM site_pays");
		while ($valpays = $DB_site->fetch_array($valspays)){
			$txnormDyn = "txnorm_".$valpays[paysid];
			$txredDyn = "txred_".$valpays[paysid];
			$txinterDyn = "txinter_".$valpays[paysid];
			$tvasurDyn = "tvasur_".$valpays[paysid];
			$fraisport="fraisport_".$valpays[paysid];
			$valminachat = $DB_site->query_first("SELECT * FROM pays WHERE paysid = $valpays[paysid]");
			//if (${$txnormDyn} != ""){
			
			if(${$tvasurDyn}){
				$tvaSurFraisDePort=1;
			}else{
				$tvaSurFraisDePort=0;
			}
			
			$DB_site->query("UPDATE pays SET TVAtauxnormal = '${$txnormDyn}',
											TVAtauxreduit = '${$txredDyn}',
											TVAtauxintermediaire = '${$txinterDyn}',
											TVAporttauxnormal = '${$fraisport}',
											port = '$tvaSurFraisDePort'
											WHERE paysid = $valpays[paysid]");
			
		}
		//$action="";
		header("location: tva.php?alertSuccess1=success");
	}else{
		header('location: tva.php?erreurdroits=1');
	}

}

//************************************************ GESTION AFFICHAGE PAYS PAR SITES *********************************************

if (!isset($action) || $action == ""){
	$sites = $DB_site->query("SELECT DISTINCT siteid FROM site_pays");

	while ($site = $DB_site->fetch_array($sites)){
		$TemplateTvaSitePaysBit="";
		$paysSite = $DB_site->query("SELECT * FROM site_pays WHERE siteid = $site[siteid]");
		$infosSite = $DB_site->query_first("SELECT * FROM site WHERE siteid = $site[siteid]");
		while ($pays = $DB_site->fetch_array($paysSite)){
			$infosPays = $DB_site->query_first("SELECT * FROM pays WHERE paysid = $pays[paysid]");
			if ($infosPays[port] == 1){
				$isitchecked = checked;	
			}else{
				$isitchecked = "";				
			}
			$libelleTagPays = strtolower($infosPays['diminutif']);
			if ($infosPays[minimumachat] != -1){
				$afficheMin = $infosPays[minimumachat];
			}else{
				$afficheMin = "";
			}
			eval(charge_template($langue,$referencepage,"SitePaysBit"));
		}
		eval(charge_template($langue,$referencepage,"SiteBit"));
	}
	if ($alertSuccess1 == success){
		$texteSuccess = $multilangue[taux_tva_edites];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	eval(charge_template($langue,$referencepage,"FormBit"));
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