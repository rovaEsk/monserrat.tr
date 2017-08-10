<?php
include "./includes/header.php";

$referencepage="frais_port";
$pagetitle = "Frais de port - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if (isset($action) && $action == "modifiermodeszone") {
	if ($params[type_tranches_port] == 0) {
		$unite = "$multilangue[gramme_diminutif]";
		$type_tranches_port = "poids";
	}else{
		$unite = "&euro;";
		$type_tranches_port = "prix";
	}
	$texteInfo = "$multilangue[infos_frais_port_etape2] $multilangue[poids] $multilangue[de_debut] <= $multilangue[poids] ".strtolower($multilangue[total])." <= $multilangue[poids] $multilangue[de_fin].<br>$multilangue[infos_frais_port_montant_defaut]";
	eval(charge_template($langue,$referencepage,"InfoFixe"));
	$zone = $DB_site->query_first("SELECT * FROM zone WHERE zoneid = '$zoneid'");
	$modes = $DB_site->query("SELECT * FROM mode_livraison
							INNER JOIN mode_livraison_site USING(modelivraisonid)
							WHERE (nom = 'Colissimo Expert International' OR nom = 'Colissimo Expert Outre-Mer' OR nom = 'Colissimo Access Outre-Mer')
							AND (activeV1 = '1' OR activeV2 = '1' OR activeV1M = '1' OR activeV2M = '1') ORDER BY position");
	while ($mode = $DB_site->fetch_array($modes)){
		$i = 0;
		$TemplateFrais_portListeModificationZoneFraisPort = "";
		$TemplateFrais_portListeModificationZoneInfoFraisPort = "";
		$TemplateFrais_portListeModificationZoneFraisPortDefaut = "";
		$fraisportdefaut = $DB_site->query_first("SELECT * FROM fraisport
												 INNER JOIN pays USING(paysid)
												 INNER JOIN zone_pays USING(paysid)
												 GROUP BY zoneid, modelivraisonid, debut, fin, prix 
												 HAVING zoneid = '$zone[zoneid]' AND modelivraisonid = '$mode[modelivraisonid]'
												 AND fin = '0' AND debut = '0'");
		$fraisportdefaut[prix] = (empty($fraisportdefaut[prix]) ? 0 : $fraisportdefaut[prix]);
		eval(charge_template($langue,$referencepage,"ListeModificationZoneFraisPortDefaut"));
		$fraisports = $DB_site->query("SELECT * FROM fraisport
									  INNER JOIN zone_pays USING(paysid)
									  GROUP BY zoneid, modelivraisonid, debut, fin, prix 
									  HAVING zoneid = '$zone[zoneid]'
									  AND modelivraisonid = '$mode[modelivraisonid]'
									  AND fin > '0' ORDER BY debut");
		while ($fraisport = $DB_site->fetch_array($fraisports)){
			++$i;
			eval(charge_template($langue,$referencepage,"ListeModificationZoneFraisPort"));
		}
		if ($i < 1) {
			eval(charge_template($langue,$referencepage,"ListeModificationZoneInfoFraisPort"));
		}
		eval(charge_template($langue,$referencepage,"ListeModificationZoneBit"));
	}
	eval(charge_template($langue,$referencepage,"ListeModificationZone"));
	$libNavigSupp = "$multilangue[configuration_frais_port_zone_colissimo] : $zone[libelle]";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}

if (isset($action) && $action == "modifierzone2") {
	if($admin_droit[$scriptcourant][ecriture]){
		if($zoneid == ""){
			$DB_site->query("INSERT INTO zone(zoneid) VALUES ('')");
			$zoneid = $DB_site->insert_id();
			$nouvellezone = 1;
		}
		$DB_site->query("UPDATE zone SET libelle = '" . securiserSql($_POST[libelle]) . "'WHERE zoneid = '$zoneid'");
		$DB_site->query("DELETE FROM zone_pays WHERE zoneid = '$zoneid'");
		if(sizeof($pays) > 0){
			foreach ($pays as $key => $value){
				$DB_site->query("INSERT INTO zone_pays (zoneid, paysid) VALUES ('$zoneid', '$value')");
			}
		}
		header('location: frais_port.php?action=zone');
	}else{
		header('location: balises_meta.php?erreurdroits=1');	
	}
}

if (isset($action) && $action == "supprimerzone") {
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE fraisport FROM fraisport INNER JOIN zone_pays USING(paysid) WHERE zoneid = '$zoneid'");
		$DB_site->query("DELETE FROM zone WHERE zoneid = '$zoneid'");
		$DB_site->query("DELETE FROM zone_pays WHERE zoneid = '$zoneid'");
		header('location: frais_port.php?action=zone');
	}else{
		header('location: balises_meta.php?erreurdroits=1');	
	}
}

if (isset($action) && $action == "modifierzone") {
	$zone = $DB_site->query_first("SELECT * FROM zone WHERE zoneid = '$zoneid'");
	if (isset($zoneid))
		$texte_entete = "$multilangue[modification] : $zone[libelle]";
	else
		$texte_entete = $multilangue[ajt_zone];
	$flags = $DB_site->query("SELECT * FROM pays LEFT JOIN zone_pays USING(paysid)
							WHERE paysid NOT IN (SELECT paysid FROM zone_pays WHERE zoneid != '$zoneid') ORDER BY libelle");
	while ($flag = $DB_site->fetch_array($flags)){
		$img = "assets/img/flags/" . strtolower($flag[diminutif]) . ".png";
		$check = $DB_site->query_first("SELECT * FROM zone_pays WHERE zoneid = '$flag[zoneid]'");
		if ($check[zoneid] != ""){
			$textsuccess = "text-success";
			$checked = "checked";
		}else{
			$textsuccess = "";
			$checked = "";
		}
		++$i;
		eval(charge_template($langue,$referencepage,"ModificationZoneFlagBit"));
	}
	if ($i)
		eval(charge_template($langue,$referencepage,"ModificationZoneFlag"));
	eval(charge_template($langue,$referencepage,"ModificationZone"));
	$libNavigSupp = $multilangue[configuration_frais_port_zone_colissimo];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}

if (isset($action) && $action == "zone") {
	$zones = $DB_site->query("SELECT * FROM zone ORDER BY libelle");
	while ($zone = $DB_site->fetch_array($zones)){
		eval(charge_template($langue,$referencepage,"ListeZoneBit"));
	}
	eval(charge_template($langue,$referencepage,"ListeZone"));
	$libNavigSupp = $multilangue[configuration_frais_port_zone_colissimo];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}

if (!isset($action) or $action == ""){
	if ($params[type_tranches_port] == 0) {
		$unite = "$multilangue[gramme_diminutif]";
		$type_tranches_port = "poids";
	}else{
		$unite = "&euro;";
		$type_tranches_port = "prix";
	}
	$texteInfo = "$multilangue[infos_frais_port_etape2] $multilangue[poids] $multilangue[de_debut] <= $multilangue[poids] ".strtolower($multilangue[total])." <= $multilangue[poids] $multilangue[de_fin].<br>$multilangue[infos_frais_port_montant_defaut]";
	eval(charge_template($langue,$referencepage,"InfoFixe"));
	$sites_pays = $DB_site->query("SELECT * FROM site_pays");
	while ($site_pays = $DB_site->fetch_array($sites_pays)){
		$pays =  $DB_site->query_first("SELECT * FROM pays WHERE paysid = '$site_pays[paysid]'");
		$modes = $DB_site->query("SELECT * FROM mode_livraison INNER JOIN mode_livraison_site USING(modelivraisonid) WHERE activeV1 = '1' OR activeV2 = '1' OR activeV1M = '1' OR activeV2M = '1' ORDER BY position");
		while ($mode = $DB_site->fetch_array($modes)) {
			$fraisportdefaut = $DB_site->query_first("SELECT * FROM fraisport WHERE paysid = '$pays[paysid]' AND modelivraisonid = '$mode[modelivraisonid]' AND debut = '0' AND fin = '0'");
			if (!$fraisportdefaut[fraisportid])
				$DB_site->query("INSERT INTO fraisport (paysid, modelivraisonid, debut, fin) values ('$pays[paysid]', '$mode[modelivraisonid]', '0', '0')");
		}
	}
	$sites_pays = $DB_site->query("SELECT * FROM site_pays");
	while ($site_pays = $DB_site->fetch_array($sites_pays)){
		$devise_actuelle = $tabsites[$site_pays[siteid]][devise_complete];
		$pays =  $DB_site->query_first("SELECT * FROM pays WHERE paysid = '$site_pays[paysid]'");
		$src = "assets/img/flags/" . strtolower($pays[diminutif]) . ".png";
		$modes = $DB_site->query("SELECT * FROM mode_livraison
								INNER JOIN mode_livraison_site USING(modelivraisonid)
								INNER JOIN site_pays USING(siteid)
								INNER JOIN pays USING(paysid)
								WHERE pays.paysid = '$pays[paysid]' AND (activeV1 = '1' OR activeV2 = '1' OR activeV1M = '1' OR activeV2M = '1')");
		
		$TemplateFrais_portListeMode = "";
		if ($DB_site->num_rows($modes) > 0){
			while ($mode = $DB_site->fetch_array($modes)){
				$i = 0;
				$fraisports = $DB_site->query("SELECT * FROM fraisport INNER JOIN pays USING(paysid) 
											WHERE paysid = '$mode[paysid]' AND modelivraisonid = '$mode[modelivraisonid]' ORDER BY debut");
				$TemplateFrais_portListeFraisPort = "";
				$TemplateFrais_portListeInfoFraisPort = "";
				$TemplateFrais_portListeFraisPortDefaut = "";
				while ($fraisport = $DB_site->fetch_array($fraisports)){
					++$i;
					if ($fraisport[debut] == "0" && $fraisport[fin] == "0")
						eval(charge_template($langue,$referencepage,"ListeFraisPortDefaut"));
					else
						eval(charge_template($langue,$referencepage,"ListeFraisPort"));
				}
				if ($i < 2) {
					eval(charge_template($langue,$referencepage,"ListeInfoFraisPort"));
				}
				eval(charge_template($langue,$referencepage,"ListeMode"));
			}
			eval(charge_template($langue,$referencepage,"ListeBit"));
		}
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