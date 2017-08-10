<?php
/*
 * Tous les calculs sont faux, j'ai viré le lien dans l'admin qui amene vers cette page (benjamin)
 * 
 * 
 * */
 
include "includes/admin_global.php";

ini_set("memory_limit","20M");

$contenu="$multilangue[periode];$multilangue[nb_commandes];$multilangue[ca] $multilangue[ht] 5.5%;$multilangue[ca] $multilangue[ht] 19.6%;$multilangue[frais_port];$multilangue[tva] 5.5%;$multilangue[tva] 19.6%;$multilangue[tva_frais_port];$multilangue[total_tva] 19.6%";

if($year=="")
	$year=2009;

for ($nbmois=1;$nbmois<13;$nbmois++){
	$nbjourInmois =  date("t", mktime( 0, 0, 0, $nbmois, 1, $year));
	$dated = date("Y-m-d", mktime(0, 0, 0, $nbmois, "1", $year));
	$datef = date("Y-m-d", mktime(0, 0, 0, $nbmois, $nbjourInmois, $year));
	
	switch ($params[etat_calcul_marge]){
		case "0":
			$where = "WHERE etatid = '5' AND dateexpedition >= '$dated' and dateexpedition <= '$datef'"; // commandes expediees
			break;
		case "1":
			$where = "WHERE etatid = '1' AND datefacture >= '$dated' and datefacture <= '$datef'"; // commandes validees
			break;
		case "2":
			$where = "WHERE etatid IN (1,5) AND (datefacture >= '$dated' and datefacture <= '$datef')"; // commandes validees ou expediees			
			break;
	}
	
	$nb_facture = 0;
	$caht1=0;
	$caht2=0;
	$fp=0;
	
	//$factures=$DB_site->query("SELECT * FROM facture $where ");
	
	$factures=$DB_site->query("SELECT * FROM facture WHERE etatid = '5' AND dateexpedition >= '2011-06-01' and dateexpedition <= '2011-06-30'");
	
	
	
	$nb_facture = $DB_site->num_rows($factures);
	while ($facture=$DB_site->fetch_array($factures)){
		//print_r($facture);
		
		$fp+=$facture[montantport];
		$lfacs=$DB_site->query("SELECT * FROM lignefacture WHERE factureid='$facture[factureid]' ");
		while ($lfac=$DB_site->fetch_array($lfacs)){
			if($lfac[tva]=="5.5")
				$caht1+=($lfac[prix]/1.055);
			elseif($lfac[tva]=="19.6")
				$caht2+=($lfac[prix]/1.196);
		}
	}
	
	$caht1 = formaterPrix($caht1);
	$caht2 = formaterPrix($caht2);
	$fp = formaterPrix($fp);
	$tva1 = formaterPrix($caht1*0.055);
	$tva2 = formaterPrix($caht2*0.196);
	$tvafp = formaterPrix($fp*0.196);
	$tva22 = formaterPrix(($caht2*0.196)+($fp*0.196));
	
	if($alt==2)
		$alt=1;
	else
		$alt++;
	
	switch($nbmois){
		case "1":
			$mois = $multilangue[janvier];
			break;
		case "2":
			$mois = $multilangue[fevrier];
			break;
		case "3":
			$mois = $multilangue[mars];
			break;
		case "4":
			$mois = $multilangue[avril];
			break;
		case "5":
			$mois = $multilangue[mai];
			break;
		case "6":
			$mois = $multilangue[juin];
			break;
		case "7":
			$mois = $multilangue[juillet];
			break;
		case "8":
			$mois = $multilangue[aout];
			break;
		case "9":
			$mois = $multilangue[septembre];
			break;
		case "10":
			$mois = $multilangue[octobre];
			break;
		case "11":
			$mois = $multilangue[novembre];
			break;
		case "12":
			$mois = $multilangue[decembre];
			break;
	}
	$contenu.="\n";
	$contenu.="$mois $year";
	$contenu.=";";
	$contenu.=$nb_facture;
	$contenu.=" ;";
	$contenu.=$caht1;
	$contenu.=" ;";
	$contenu.=$caht2;
	$contenu.=" ;";
	$contenu.=$fp;
	$contenu.=" ;";
	$contenu.=$tva1;
	$contenu.=" ;";
	$contenu.=$tva2;
	$contenu.=" ;";
	$contenu.=$tvafp;
	$contenu.=" ;";
	$contenu.=$tva22;
	$contenu.=" ;";
}

$filename = './csv/rapport_tva.csv';
if (is_writable($filename))
	{
	if (!$handle = fopen($filename, 'w'))
		{
		echo "$multilangue[erreur_ouverture_fichier] ($filename)";
		exit;
		}
	if (fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE)
		{
		echo "$multilangue[erreur_ecriture_fichier] ($filename)";
		exit;
		}
	fclose($handle);

	$file = $rootpath."admin/".$filename;
	header('Content-Description: File Transfer');
	header('Content-Type: application/force-download');
	header('Content-Length: ' . filesize($file));
	header('Content-Disposition: attachment; filename=' . basename($file));
	readfile($file);
	exit;
	}
else
	{
	echo "$multilangue[erreur_accessibilite_ecriture_fichier] ($filename).";
	}
?>
