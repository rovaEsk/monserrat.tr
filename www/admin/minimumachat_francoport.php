<?php
include "./includes/header.php";

$referencepage="minimumachat_francoport";
$pagetitle = "Minimums d'achat et francos de port - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


if($_GET[alert] == 1){
	$texteSuccess= $multilangue[modif_enregistrees];
	eval(charge_template($langue,$referencepage,"Success"));
}

// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

//************************************************ GESTION MODIF MINIMUM ACHAT *********************************************
if ($action == modifier){	
	if($admin_droit[$scriptcourant][ecriture]){
		$valsmodelivraison = $DB_site-> query("SELECT * FROM mode_livraison_site");	
		if(in_array(5868, $modules)){
			foreach($gratuit as $paysid => $value){
				foreach($gratuit[$paysid] as $modelivraisonid => $franco){
					//echo "Pays : $paysid / $modelivraisonid / $franco";
					if($franco == "" || $franco < 0){
						$franco=-1;
					}				
					$testval = $DB_site->query_first("SELECT * FROM francoports_pays_moyen WHERE paysid = '$paysid' AND modelivraisonid = '$modelivraisonid'");
					if ($testval[montantgratuit] != ""){
						$DB_site->query("UPDATE francoports_pays_moyen SET montantgratuit = '$franco' WHERE paysid = '$paysid' AND modelivraisonid = '$modelivraisonid'");
					}else{
						$DB_site->query("INSERT INTO francoports_pays_moyen (paysid, modelivraisonid, montantgratuit)
								VALUES('$paysid','$modelivraisonid', '$franco' )");
					}				
				}
			}
		}else{
			foreach($gratuit as $paysid => $franco){
				if($franco == "" || $franco < 0){
					$franco=-1;
				}
				$DB_site->query("UPDATE pays SET montantgratuit='$franco' WHERE paysid='$paysid'");
			}
		}	
		
		foreach($minimum as $paysid => $minimum_achat){	
			if ($minimum_achat == "" || $minimum_achat < 1){
				$minimum_achat=-1;
			}
			$DB_site->query("UPDATE pays SET minimumachat = '$minimum_achat' WHERE paysid = '$paysid'");		
		}
		header("location: minimumachat_francoport.php?alert=1");
	}else{
		header('location: minimumachat_francoport.php?erreurdroits=1');	
	}
}
//************************************************ GESTION AFFICHAGE PAYS PAR SITES *********************************************

if (!isset($action) || $action == ""){
	$sites = $DB_site->query("SELECT DISTINCT siteid FROM site_pays");

	if (in_array(5868, $modules)){
		while ($site = $DB_site->fetch_array($sites)){
			$TemplateMinimumachat_francoportMLSitePaysBit="";
			
			$devise_actuel = $tabsites[$site[siteid]][devise_complete];
			
			$paysSite = $DB_site->query("SELECT * FROM site_pays WHERE siteid = '$site[siteid]'");
			$infosSite = $DB_site->query_first("SELECT * FROM site WHERE siteid = '$site[siteid]'");
			while ($pays = $DB_site->fetch_array($paysSite)){
				$TemplateMinimumachat_francoportMLSitePaysLivraisonBit="";
				$TemplateMinimumachat_francoportMLSitePaysLibelleLivraisonBit="";
				$infosPays = $DB_site->query_first("SELECT * FROM pays WHERE paysid = $pays[paysid]");
				$libelleTagPays = strtolower($infosPays['diminutif']);
				if ($infosPays[montantgratuit] == -1){
					$infosPays[montantgratuit]="";
				}

				if ($infosPays[minimumachat] == -1){
					$infosPays[minimumachat]="";
				}
				
				$modeslivraison = $DB_site-> query("SELECT * FROM mode_livraison_site
													WHERE (activeV1 = '1' || activeV2 = '1') AND siteid='$site[siteid]' ORDER BY modelivraisonid");
				while($modelivraison = $DB_site->fetch_array($modeslivraison)){
						
					$infosModelivraison = $DB_site->query_first("SELECT * FROM mode_livraison_site
							WHERE modelivraisonid = $modelivraison[modelivraisonid]");
					$montantgratuitmodelivraison = $DB_site->query_first("SELECT * FROM francoports_pays_moyen
							WHERE paysid = $pays[paysid]
							&& modelivraisonid = $modelivraison[modelivraisonid]");
					
					if($montantgratuitmodelivraison[montantgratuit] == -1){
						$montantgratuitmodelivraison[montantgratuit]="";
					}
					
					//echo $montantgratuitmodelivraison[montantgratuit];
					eval(charge_template($langue,$referencepage,"MLSitePaysLibelleLivraisonBit"));
					eval(charge_template($langue,$referencepage,"MLSitePaysLivraisonBit"));
				}
				eval(charge_template($langue,$referencepage,"MLSitePaysBit"));
			}
			eval(charge_template($langue,$referencepage,"MLSiteBit"));
		}
		eval(charge_template($langue,$referencepage,"MLBit"));
	}else {
		while ($site = $DB_site->fetch_array($sites)){
			$TemplateMinimumachat_francoportSitePaysBit1="";
			
			$devise_actuel = $tabsites[$site[siteid]][devise_complete];
			
			$paysSite = $DB_site->query("SELECT * FROM site_pays WHERE siteid = $site[siteid]");
			$infosSite = $DB_site->query_first("SELECT * FROM site WHERE siteid = $site[siteid]");
			while ($pays = $DB_site->fetch_array($paysSite)){
				$infosPays = $DB_site->query_first("SELECT * FROM pays WHERE paysid = $pays[paysid]");
				$libelleTagPays = strtolower($infosPays['diminutif']);
				if ($infosPays[montantgratuit] != -1){
					$montantgratuit = $infosPays[montantgratuit];
				}else{
					$montantgratuit = "";
				}
				
				if ($infosPays[minimumachat] == -1){
					$infosPays[minimumachat]="";
				}

				eval(charge_template($langue,$referencepage,"SitePaysBit1"));
			}
			eval(charge_template($langue,$referencepage,"SiteBit1"));
		}
		eval(charge_template($langue,$referencepage,"FormBit1"));
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