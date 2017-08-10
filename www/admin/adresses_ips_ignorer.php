<?php
include "./includes/header.php";

$referencepage="adresses_ips_ignorer";
$pagetitle = "Adresses IP à ignorer des statistiques - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if (isset($action) and $action == "supprimer"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM ipignorer WHERE ipid = '$ipid'");
		header('location: adresses_ips_ignorer.php');
	}else{
		header('location: adresses_ips_ignorer.php?erreurdroits=1');	
	}
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if(isset($erreur) and $erreur == 1){
	$texteErreur = $multilangue[ip_fin_superieure_ip_debut];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if (isset($action) and $action == "modifier"){
	if($admin_droit[$scriptcourant][ecriture]){
		$iponly = iptochaine($_POST[iponly]);
		$ipdeb = ($iponly != "0" ? $iponly : iptochaine($_POST[ipdeb]));
		$ipfin = ($iponly != "0" ? $iponly : iptochaine($_POST[ipfin]));
		if ($ipfin >= $ipdeb){
			$ips = $DB_site->query("SELECT * FROM ipignorer WHERE ipdeb >= '$ipdeb' and ipfin <= '$ipfin'");
			while ($ip = $DB_site->fetch_array($ips))
				$DB_site->query("DELETE FROM ipignorer WHERE ipid = '$ip[ipid]'");
			$DB_site->query("INSERT INTO ipignorer SET ipdeb = '$ipdeb', ipfin = '$ipfin'");
			header('location: adresses_ips_ignorer.php');
		}else{
			header('location: adresses_ips_ignorer.php?erreur=1');	
		}
	}else{
		header('location: adresses_ips_ignorer.php?erreurdroits=1');	
	}
}

if (!isset($action) or $action == ""){
	$texteSuccess = $multilangue[infos_ip_stats];
	eval(charge_template($langue, $referencepage, "SuccessFixe"));
	$ips = $DB_site->query("SELECT * FROM ipignorer ORDER BY ipid");
	while ($ip = $DB_site->fetch_array($ips)) {
		$ipdeb = chainetoip($ip[ipdeb]);
		$ipfin = chainetoip($ip[ipfin]);
		eval(charge_template($langue, $referencepage, "ListeBit"));
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