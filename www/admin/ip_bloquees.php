<?php
include "./includes/header.php";

$referencepage="ip_bloquees";
$pagetitle = "IP bloquées - $host - Admin Arobases";

// Correction booléen Afrique non renseigné
$count_afrique = $DB_site->query_first("SELECT count(*) FROM pays WHERE afrique = '1'");
if($count_afrique[0] < 53){
	$DB_site->query("UPDATE pays SET afrique='1' WHERE paysid IN
		(3,5, 19,23,27,28,30,32,33,37,38,39,41,46,50,52,54,61,62,65,
		70,71,88,94,95,96,100,101,104,108,113,114,115,121,122,138,142,144,145,
		146,151,152,155,161,163,166,179,180,181,192,193,194,232);");
}


// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


if($erreur==1){
	$texteErreur = $multilangue[ip_fin_superieure_ip_debut];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if($success==1){
	$texteSuccess = $multilangue[adresse_bloquee];
	eval(charge_template($langue, $referencepage, "Success"));
}

if (isset($action) and $action == "supprimer"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM ipbloquer WHERE ipid = '$ipid'");
		header('location: ip_bloquees.php');
	}else{
		header('location: ip_bloquees.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "modifier"){
	if($admin_droit[$scriptcourant][ecriture]){
		$iponly = iptochaine($_POST[iponly]);
		$ipdeb = ($iponly != "0" ? $iponly : iptochaine($_POST[ipdeb]));
		$ipfin = ($iponly != "0" ? $iponly : iptochaine($_POST[ipfin]));
		if ($ipfin >= $ipdeb){
			$ips = $DB_site->query("SELECT * FROM ipbloquer WHERE ipdeb >= '$ipdeb' and ipfin <= '$ipfin'");
			while ($ip = $DB_site->fetch_array($ips))
				$DB_site->query("DELETE FROM ipbloquer WHERE ipid = '$ip[ipid]'");
			$DB_site->query("INSERT INTO ipbloquer SET ipdeb = '$ipdeb', ipfin = '$ipfin'");
			header('location: ip_bloquees.php?succes=1');
		}else{
			$texteErreur = $multilangue[ip_fin_superieure_ip_debut];
			eval(charge_template($langue, $referencepage, "Erreur"));
			header("location: ip_bloquees.php?erreur=1");
		}
	}else{
		header('location: ip_bloquees.php?erreurdroits=1');	
	}
}

if (!isset($action) or $action == ""){
	$texteErreur = $multilangue[infos_ip_bloquees];
	eval(charge_template($langue, $referencepage, "ErreurFixe"));
	$ips = $DB_site->query("SELECT * FROM ipbloquer ORDER BY ipid");
	while ($ip = $DB_site->fetch_array($ips)) {
		$ipdeb = chainetoip($ip[ipdeb]);
		$ipfin = chainetoip($ip[ipfin]);
		$pays = $DB_site->query_first("SELECT * FROM iptocountry INNER JOIN pays USING(paysid) WHERE ipstart <= '$ip[ipdeb]' AND ipend >= '$ip[ipdeb]'");
		eval(charge_template($langue, $referencepage, "ListeBit"));
	}
	$payss = $DB_site->query("SELECT * FROM pays ORDER BY libelle");
	while ($pays = $DB_site->fetch_array($payss)){
		$img = "assets/img/flags/" . strtolower($pays[diminutif]) . ".png";
		$check = $DB_site->query_first("SELECT * FROM pays_bloque WHERE paysid = '$pays[paysid]'");
		if ($check[paysid] != ""){
			$text = "text-danger";
			$checked = "checked";
		}else{
			$text = "";
			$checked = "";
		}
		eval(charge_template($langue,$referencepage,"ListePaysBit"));
	}
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