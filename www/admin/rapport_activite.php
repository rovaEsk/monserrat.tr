<?php
include "./includes/header.php";

$referencepage="rapport_activite";
$pagetitle = "Rapport d'activité - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

// DEVISES
if ($_POST['siteid']) {
	$siteid = $_POST['siteid'];
} else {
	$siteid = $siteid_defaut;
}

$site = $DB_site->query_first("SELECT * FROM site WHERE siteid = '$siteid'");
$devise = $DB_site->query_first("SELECT * FROM devise WHERE deviseid = '$site[deviseid]'");

if(!empty($devise['symbole'])){
	$symboleMonetaire = $devise['symbole'];
}else{
	$symboleMonetaire = $devise['devise'];
}

if($_POST['taxe']=='1'){
	$montant_total = "montanttotal_horsfraisport_ttc";
	$prix = "prix";
} else {
	$montant_total = "montanttotal_horsfraisport_ht";
	$prix = "prixht";
}
$lengendeDevise = "En ".$recupDevise['contenu'];



if(isset($_POST['debut']) and isset($_POST['fin']) and !empty($_POST['debut']) and !empty($_POST['fin']) and $_POST['debut']!=$_POST['fin'] and convertirChaineenDate($_POST['fin']) > convertirChaineenDate($_POST['debut'])){	
	$periodegraph=convertirChaineenDate($_POST['debut']); 
	$periodegraphbis=convertirChaineenDate($_POST['fin']);
	
	$perso = "oui";
	
	if($periodegraphbis > date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d"), date("Y")))) // Si la date de fin est plus grande que aujourd'hui 
		$periodegraphbis = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
		
	if($params[$siteid][valeur]!="")
		$dateDeMiseEnLigne = convertirChaineEnDate($recupMiseEnLigne[valeur]);
	
	if($periodegraph < $dateDeMiseEnLigne) // Si la date de début est inférieur à la date de mise en ligne de la boutique 
		$periodegraph = $dateDeMiseEnLigne;
		
	$periode1 = date("Y-m-d",mktime(0, 0, 0, date("m",strtotime($periodegraph))  , date("d",strtotime($periodegraph)), date("Y",strtotime($periodegraph))));
	$periode2 = date("Y-m-d",mktime(0, 0, 0, date("m",strtotime($periodegraphbis))  , date("d",strtotime($periodegraphbis)), date("Y",strtotime($periodegraphbis))));
	$periode1Graph = $periode1;
	$periode2Graph = $periode2;
	$periodeLib = "$multilangue[du] ".convertirDateEnChaine($periode1)." $multilangue[au] ".convertirDateEnChaine($periode2);
}else{
	$periodegraph = date("Y-m-d",mktime(0, 0, 0, date("m")  , 01, date("Y"))); // Debut du mois
	$periodegraphbis = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
	
	$perso = "non";
	
	$diff_date = round(mktime(0, 0, 0, date("m",strtotime($periodegraphbis))  , date("d",strtotime($periodegraphbis)), date("Y",strtotime($periodegraphbis))) - mktime(0, 0, 0, date("m",strtotime($periodegraph))  , date("d",strtotime($periodegraph)), date("Y",strtotime($periodegraph))))/(3600*24);

	if($periodegraph == $periodegraphbis)
		$periodegraph = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")-30, date("Y")));

	$periode2 = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
	$periode1 = date("Y-m-d",mktime(0, 0, 0, date("m")  , 01, date("Y")));
	$periode1Graph = $periode1;
	$periode2Graph = $periode2;

	$periodeLib = "$multilangue[du] ".convertirDateEnChaine($periode1)." $multilangue[au] ".convertirDateEnChaine($periode2);		
}
$periodegraphE = convertirDateEnChaine($periodegraph);
$periodegraphbisE = convertirDateEnChaine($periodegraphbis);

// Etats
if($etatSelected!= '0' and $etatSelected!= '1' and $etatSelected!= '2')  $etatSelected = $params[$siteid]['etat_calcul_marge'];
switch($etatSelected){
	case '0':
		$etatid='5';
		$etatSelected0 = "selected";
		break;
	case '1':
		$etatid='1';
		$etatSelected1 = "selected";
		break;
	case '2':
	default :
		$etatid='1,5';
		$etatSelected2 = "selected";
		break;
}
// Dates de filtre
$filtredate=empty($filtredate)?'dateexpedition':$filtredate;
switch($filtredate){
	case 'datefacture':
		$filtredate0 = "selected";
		break;
	case 'datevalidation':
		$filtredate1 = "selected";
		break;
	case 'dateexpedition':
	default :
		$filtredate2 = "selected";
		break;
}

// sites
$listeSites = $DB_site->query("SELECT * FROM site");
while($listeSite = $DB_site->fetch_array($listeSites)){
	$selectedSite = "";
	if ($selectedSite == $siteid) {
		$selectedSite = "selected";
	}
	eval(charge_template($langue, $referencepage, "FormulaireSiteBit"));
}
eval(charge_template($langue, $referencepage, "Formulaire"));	

if($perso == "oui")
	eval(charge_template($langue, $referencepage, "TitrePerso"));	
else
	eval(charge_template($langue, $referencepage, "TitreMoisEnCours"));	
		
/*echo "<pre>";
print_r($_POST);
echo "</pre>";*/

//////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////// DEBUT RAPPORT ACTIVITE PANIERS BOUTIQUE ///////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////

	
$moisEnCours = $DB_site->query("SELECT * FROM facture WHERE etatid IN ($etatid) AND deleted != '1' AND siteid=".$siteid." AND montanttotal_ttc > 0 AND $filtredate >= ('".$periode1."') AND $filtredate <= ('".$periode2."') ");
$panierMoyen = $DB_site->query_first("SELECT AVG(montanttotal_ttc) as moyenne FROM facture WHERE etatid IN ($etatid) AND deleted != '1' AND $filtredate >= ('".$periode1."') AND $filtredate <= ('".$periode2."') AND siteid=".$siteid." AND montanttotal_ttc > 0");
$nombreProd = $DB_site->query_first("SELECT SUM(l.qte) as nbrprod FROM lignefacture l INNER JOIN facture f ON l.factureid=f.factureid AND f.etatid IN ($etatid) AND f.deleted != '1' AND f.$filtredate >= ('".$periode1."') AND f.$filtredate <= ('".$periode2."') AND f.siteid=".$siteid." AND f.montanttotal_ttc > 0");

$commandeGraph = $DB_site->query("SELECT COUNT(*) AS result, $filtredate FROM facture WHERE etatid IN ($etatid)  AND siteid=".$siteid." AND montanttotal_ttc > 0 AND $filtredate >= ('".$periode1."') AND $filtredate <= ('".$periode2."') GROUP BY $filtredate ");

$mauvaisMeilleurJour = $DB_site->query("SELECT COUNT(*) as nbr, $filtredate FROM facture WHERE etatid IN ($etatid)  AND siteid=".$siteid." AND montanttotal_ttc > 0 AND $filtredate >= ('".$periode1."') AND $filtredate <= ('".$periode2."') GROUP BY $filtredate");

/* Meilleur et plus mauvais jour en nombres de commandes */ 
$cpt = 0;
$meilleurjour = 0;
$dateMeilleur = "";
$dateMauvais = "";
while($recupMauvaisMeilleurJour = $DB_site->fetch_array($mauvaisMeilleurJour)){
	
	if($cpt == 0){
		$mauvaisjour = $recupMauvaisMeilleurJour['nbr'];
		$dateMauvais = convertirDateenChaine($recupMauvaisMeilleurJour[$filtredate]);	
	}
	
	if($recupMauvaisMeilleurJour['nbr'] > $meilleurjour){
		$meilleurjour = $recupMauvaisMeilleurJour['nbr'];
		$dateMeilleur = convertirDateenChaine($recupMauvaisMeilleurJour[$filtredate]);
	}
		
	if($recupMauvaisMeilleurJour['nbr'] <= $mauvaisjour){
		$mauvaisjour = $recupMauvaisMeilleurJour['nbr'];
		$dateMauvais = convertirDateenChaine($recupMauvaisMeilleurJour[$filtredate]);
	}			
$cpt++;
}

$recupPanierMoyen = $panierMoyen['moyenne'];
$recupNombresCommande  = $DB_site->num_rows($moisEnCours);
$recupNombreProd = $nombreProd['nbrprod'];

if($recupNombresCommande!=0)
	$recupCommandeMoyenne = $recupNombreProd/$recupNombresCommande;
else	
	$recupCommandeMoyenne = 0;

if(empty($recupPanierMoyen))
	$recupPanierMoyen = "0,00";
	
if(empty($recupCommandeMoyenne))
	$recupCommandeMoyenne = "0";	
	
$recupPanierMoyenE = formaterPrix(round($recupPanierMoyen, 5));
$recupCommandeMoyenneE = round($recupCommandeMoyenne,2);	

// Graph
$joursouhaite = $periode1; // init
$jourencour = $periode1;
$contenu_stat = "";
$resultat = "non";
while($recupCommandeGraph = $DB_site->fetch_array($commandeGraph)){
	$jourencour = $recupCommandeGraph[$filtredate];
	if($jourencour > $joursouhaite){
		while($jourencour > $joursouhaite){
			$contenu_stat .= "['"; $contenu_stat .= convertirDateenChaine($joursouhaite); $contenu_stat .= "',0,0],";
			$joursouhaite=date("Y-m-d",mktime(0, 0, 0, date("m",strtotime($joursouhaite))  , date("d",strtotime($joursouhaite))+1, date("Y",strtotime($joursouhaite))));
		}
	}
	$nombreProd = $DB_site->query_first("SELECT SUM(l.qte) as nbrprod FROM lignefacture l INNER JOIN facture f ON l.factureid=f.factureid AND f.etatid IN ($etatid) AND f.$filtredate = '".$jourencour."' AND f.siteid=".$siteid." AND f.montanttotal_ttc > 0");
	
	$contenu_stat .= "['"; $contenu_stat .= convertirDateenChaine($jourencour); $contenu_stat .= "',$recupCommandeGraph[result],$nombreProd[nbrprod]],";	
	$joursouhaite=date("Y-m-d",mktime(0, 0, 0, date("m",strtotime($joursouhaite))  , date("d",strtotime($joursouhaite))+1, date("Y",strtotime($joursouhaite))));	
	$resultat = "oui";
}
// securité si il n'y a pas assez de données sur la periode 
if($jourencour < $periode2){

	if($resultat == "oui")
		$jourencour = date("Y-m-d",mktime(0, 0, 0, date("m",strtotime($jourencour))  , date("d",strtotime($jourencour))+1, date("Y",strtotime($jourencour))));
	else
		$jourencour = date("Y-m-d",mktime(0, 0, 0, date("m",strtotime($jourencour))  , date("d",strtotime($jourencour)), date("Y",strtotime($jourencour))));
	
		$periodegraphbisModif = date("Y-m-d",mktime(0, 0, 0, date("m",strtotime($periode2))  , date("d",strtotime($periode2))+1, date("Y",strtotime($periode2))));
	
	// On rajoute un jour a chaque periode 
	while($jourencour < $periodegraphbisModif){
		$contenu_stat .= "['"; $contenu_stat .= convertirDateenChaine($jourencour); $contenu_stat .= "',0,0],";
		$jourencour=date("Y-m-d",mktime(0, 0, 0, date("m",strtotime($jourencour))  , date("d",strtotime($jourencour))+1, date("Y",strtotime($jourencour))));
	}
}
$contenu_stat = substr_replace($contenu_stat,'',-1);
$periode1E = convertirDateEnChaine($periode1);
$periode2E = convertirDateEnChaine($periode2);
	
eval(charge_template($langue, $referencepage, "PaniersEnCours"));

#### PANIER SUR 12 MOIS ####
$periode2m = date("Y-m-d",mktime(0, 0, 0, date("m")-13  , 01, date("Y"))); // Dans 12 mois 
$periode1m = date("Y-m-d",mktime(0, 0, 0, date("m")-1  , 01, date("Y"))); // Mois derniers
	
$graphMois = array();
$graphMoisDonne = array();
$graphMoisDonneProd = array();
// Params meilleurs et mauvais jour 
$meilleurjour = 0;
$dateMeilleur = "";
$dateMauvais = "";
for($i=1;$i<14;$i++){
	$moisEnCours = date("m",mktime(0, 0, 0, date("m")-$i  , 01, date("Y")));
	$anneeEnCours = date("Y",mktime(0, 0, 0, date("m")-$i  , 01, date("Y")));
	
	$nbrJour = date("t",mktime(0, 0, 0, date("m")-$i  , 01, date("Y")));
	$periode1m = date("Y-m-d",mktime(0, 0, 0, date("m")-$i  , 01, date("Y"))); // premier jour du mois
	$periode2m = date("Y-m-d",mktime(0, 0, 0, date("m")-$i  , $nbrJour, date("Y"))); // dernier jour du mois
	
	$nbrCommandes = $DB_site->query("SELECT * FROM facture WHERE etatid IN ($etatid)  AND siteid=".$siteid." AND montanttotal_ttc > 0 AND $filtredate >= ('".$periode1m."') AND $filtredate <= ('".$periode2m."') ");
	$panierMoyen = $DB_site->query_first("SELECT AVG(montanttotal_ttc) as moyenne FROM facture WHERE etatid IN ($etatid) AND $filtredate >= ('".$periode1m."') AND $filtredate <= ('".$periode2m."') AND siteid=".$siteid." AND montanttotal_ttc > 0");
	$commandeMoyenne = $DB_site->query_first("SELECT AVG(count) AS moyenne FROM (SELECT l.qte as count FROM lignefacture l INNER JOIN facture f ON l.factureid=f.factureid AND f.etatid IN ($etatid) AND f.$filtredate >= ('".$periode1m."') AND f.$filtredate <= ('".$periode2m."') AND f.siteid=".$siteid." AND f.montanttotal_ttc > 0 GROUP BY l.factureid) AS t1");
	$nombreProd = $DB_site->query_first("SELECT SUM(l.qte) as nbrprod FROM lignefacture l INNER JOIN facture f ON l.factureid=f.factureid AND f.etatid IN ($etatid) AND f.$filtredate >= ('".$periode1m."') AND f.$filtredate <= ('".$periode2m."') AND f.siteid=".$siteid." AND f.montanttotal_ttc > 0");

	$recupPanierMoyen = $panierMoyen['moyenne'];
	$recupCommandeMoyenne = $commandeMoyenne['moyenne'];
	$recupNombresCommande  = $DB_site->num_rows($nbrCommandes);
	$recupNombreProd = $nombreProd['nbrprod'];

	if(empty($recupPanierMoyen))
		$recupPanierMoyen = "0,00";
		
	if(empty($recupCommandeMoyenne))
		$recupCommandeMoyenne = "0";
						
	if(empty($recupNombreProd))
		$recupNombreProd = "0";

	$moisEnCoursLib = retournerMoisFr($moisEnCours)." ".$anneeEnCours;
	$graphMois[] = $moisEnCoursLib;
	$graphMoisDonne[] = $recupNombresCommande;
	$graphMoisDonneProd[] = $recupNombreProd;
	
	$recupPanierMoyenE = formaterPrix($recupPanierMoyen);
	if($recupNombresCommande != 0) {
		$recupCommandeMoyenneE = round($recupNombreProd/$recupNombresCommande,2);
	} else {
		$recupCommandeMoyenneE = 0;
	}
	eval(charge_template($langue, $referencepage, "Paniers12moisBit"));
}

//Graph
$contenu_stat = "";
for($i=count($graphMois)-1;$i>=0;$i--){
	$contenu_stat .= "['".$graphMois[$i]."',".$graphMoisDonne[$i].",".$graphMoisDonneProd[$i]."],";
}
$contenu_stat = substr_replace($contenu_stat,'',-1);

eval(charge_template($langue, $referencepage, "Paniers12mois"));
//////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////// FIN RAPPORT ACTIVITE PANIERS BOUTIQUE ///////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////


//////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////// DEBUT RAPPORT ACTIVITE CHIFFRE AFFAIRES ///////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
$sql="SELECT * FROM facture WHERE etatid IN ($etatid) AND siteid=".$siteid." AND $filtredate >= ('".$periode1."') AND $filtredate <= ('".$periode2."') GROUP BY $filtredate ";
//echo $sql.'<br>';
$factures = $DB_site->query($sql);

$totalMarge = 0;
$sousTotalHt= 0;
$sousTotalTTC = 0;
$totalHT = 0;
$totalTTC = 0;
$montantTVA = 0; 
$sousTotalHtGraph = array();
$TVAGraph = array();
$jourGraph = array();
$margeGraph = array();
$i=1;
$cptDate = 0;
$test = 0;

while ($facture=$DB_site->fetch_array($factures)){
	$totalMargeTemp = 0;

	$factureParDate = $DB_site->query("SELECT * FROM facture WHERE etatid IN ($etatid) AND siteid=".$siteid." AND $filtredate = '".$facture[$filtredate]."' ORDER BY $filtredate");
	
	$cptDate++;
	$rapport_activite = "";
	while($recupFactureParDate = $DB_site->fetch_array($factureParDate)){
		$rapport_activite = calculerTotalFacture($DB_site, $recupFactureParDate['factureid']);
		$sousTotalHt += $rapport_activite['sousTotalHT'];
		$sousTotalHtGraph[$cptDate] += formaterPrix($rapport_activite['sousTotalHT'],3,'.','');
		$jourGraph[$cptDate] = $recupFactureParDate[$filtredate];
		$sousTotalTTC += $rapport_activite['sousTotalTTC'];
		$totalHT += $rapport_activite['totalHT'];
		$totalTTC += $rapport_activite['totalTTC'];
		$montantTVA += $rapport_activite['montantTVA'];
		$TVAGraph[$cptDate] += $rapport_activite['montantTVA'];
		$lignefactures=$DB_site->query("SELECT * FROM lignefacture WHERE factureid = '$recupFactureParDate[factureid]'");
		while ($lignefacture=$DB_site->fetch_array($lignefactures)){
			if ($lignefacture['prixachat'] == "0"){
				$nblignesanspa++;
			}else{
				$totalMarge += 	($lignefacture['prixht'] - $lignefacture['prixachat']) * $lignefacture['qte'];
				$totalMargeTemp += 	($lignefacture['prixht'] - $lignefacture['prixachat']) * $lignefacture['qte'];
			}	
		}
	}
	$margeGraph[$i] += $totalMargeTemp;
	$i++;
}
$sousTotalHtE = formaterPrix(round($sousTotalHt,2));
$sousTotalTTCE = formaterPrix(round($sousTotalTTC,2));
$totalHTE = formaterPrix(round($totalHT,2));
$totalTTCE = formaterPrix(round($totalTTC,2));
$totalMargeE = formaterPrix(round($totalMarge,2));
$montantTVAE = formaterPrix(round($montantTVA,2));

// Graph
$joursouhaite = $periode1; // init
$jourencour = $periode1;
$contenu_stat = "";
$resultat = "non";
foreach($sousTotalHtGraph as $key => $value){
	$jourencour = $jourGraph[$key];
	if($jourencour > $joursouhaite){
		while($jourencour > $joursouhaite){
			$contenu_stat .= "['"; $contenu_stat .= convertirDateenChaine($joursouhaite); $contenu_stat .= "',0,0,0],";
			$joursouhaite=date("Y-m-d",mktime(0, 0, 0, date("m",strtotime($joursouhaite))  , date("d",strtotime($joursouhaite))+1, date("Y",strtotime($joursouhaite))));
		}
	}
			
	$contenu_stat .= "['"; $contenu_stat .= convertirDateenChaine($jourencour); $contenu_stat .= "',".formaterPrix($sousTotalHtGraph[$key],2,'.','').",".formaterPrix($TVAGraph[$key],2,'.','').",".formaterPrix($margeGraph[$key],2,'.','')."],";	
	$joursouhaite=date("Y-m-d",mktime(0, 0, 0, date("m",strtotime($joursouhaite))  , date("d",strtotime($joursouhaite))+1, date("Y",strtotime($joursouhaite))));	
	$resultat = "oui";
}
// securité si il n'y a pas assez de données sur la periode 
if($jourencour < $periode2){

	if($resultat == "oui")
		$jourencour = date("Y-m-d",mktime(0, 0, 0, date("m",strtotime($jourencour))  , date("d",strtotime($jourencour))+1, date("Y",strtotime($jourencour))));
	else
		$jourencour = date("Y-m-d",mktime(0, 0, 0, date("m",strtotime($jourencour))  , date("d",strtotime($jourencour)), date("Y",strtotime($jourencour))));
	
		$periodegraphbisModif = date("Y-m-d",mktime(0, 0, 0, date("m",strtotime($periode2))  , date("d",strtotime($periode2))+1, date("Y",strtotime($periode2))));
	
	// On rajoute un jour a chaque periode 
	while($jourencour < $periodegraphbisModif){
		$contenu_stat .= "['"; $contenu_stat .= convertirDateenChaine($jourencour); $contenu_stat .= "',0,0,0],";
		$jourencour=date("Y-m-d",mktime(0, 0, 0, date("m",strtotime($jourencour))  , date("d",strtotime($jourencour))+1, date("Y",strtotime($jourencour))));
	}
}
$contenu_stat = substr_replace($contenu_stat,'',-1);
$periode1E = convertirDateEnChaine($periode1);
$periode2E = convertirDateEnChaine($periode2);

eval(charge_template($langue, $referencepage, "ChiffreAffairesEnCours"));	

#### CA SUR 12 MOIS ####
$totalTTCAnnee=0;
$sousTotalHtGraph = array();
$TVAGraph = array();
$jourGraph = array();
$margeGraph = array();
$graphMois = array();
for($j=1;$j<14;$j++){
	$moisEnCours = date("m",mktime(0, 0, 0, date("m")-$j  , 01, date("Y")));
	$anneeEnCours = date("Y",mktime(0, 0, 0, date("m")-$j  , 01, date("Y")));
	$nbrJour = date("t",mktime(0, 0, 0, date("m")-$j  , 01, date("Y")));
	$periode1m = date("Y-m-d",mktime(0, 0, 0, date("m")-$j  , 01, date("Y"))); // premier jour du mois
	$periode2m = date("Y-m-d",mktime(0, 0, 0, date("m")-$j  , $nbrJour, date("Y"))); // dernier jour du mois
	$moisEnCoursLib = retournerMoisFr($moisEnCours)." ".$anneeEnCours;
	$graphMois[$j] = $moisEnCoursLib;
	
	$sql="SELECT * FROM facture WHERE etatid IN ($etatid) AND siteid=".$siteid." AND $filtredate >= ('".$periode1m."') AND $filtredate <= ('".$periode2m."')";
	//echo $sql.'<br>';
	$facturesMois = $DB_site->query($sql);
	
	$totalMarge = 0;
	$sousTotalHt= 0;
	$sousTotalTTC = 0;
	$totalHT = 0;
	$totalTTC = 0;
	$montantTVA = 0; 
	$i=0;
	while ($facture=$DB_site->fetch_array($facturesMois)){
		$rapport_activite = calculerTotalFacture($DB_site, $facture['factureid']);
		$sousTotalHt += $rapport_activite['sousTotalHT'];
		$sousTotalTTC += $rapport_activite['sousTotalTTC'];
		$totalHT += $rapport_activite['totalHT'];
		$totalTTC += $rapport_activite['totalTTC'];
		$totalTTCAnnee += $rapport_activite['totalTTC'];
		$montantTVA += $rapport_activite['montantTVA'];
		
		$lignefactures=$DB_site->query("SELECT * FROM lignefacture WHERE factureid = '$facture[factureid]'");
		while ($lignefacture=$DB_site->fetch_array($lignefactures)){
			if ($lignefacture['prixachat'] == "0"){
				$nblignesanspa++;
			}else{
				$totalMarge += 	($lignefacture['prixht'] - $lignefacture['prixachat']) * $lignefacture['qte'];
				//$totalMargeTemp += 	($lignefacture['prixht'] - $lignefacture['prixachat']) * $lignefacture['qte'];
			}	
		}
		$i++;
	}
	
	$TVAGraph[$j] = $montantTVA;
	$sousTotalHtGraph[$j] = $sousTotalHt;
	
	if(!isset($totalMarge))
		$totalMarge=0;
		
	$margeGraph[$j] = $totalMarge;
	$moisEnCoursLib = retournerMoisFr($moisEnCours)." ".$anneeEnCours;
	$sousTotalHtE = formaterPrix(round($sousTotalHt,2));
	$sousTotalTTCE = formaterPrix(round($sousTotalTTC,2));
	$totalHTE = formaterPrix(round($totalHT,2));
	$totalTTCE = formaterPrix(round($totalTTC,2));
	$totalMargeE = formaterPrix(round($totalMarge,2));
	$montantTVAE = formaterPrix(round($montantTVA,2));
	eval(charge_template($langue, $referencepage, "ChiffreAffaires12moisBit"));
}

$contenu_stat = "";
for($i=count($graphMois);$i>0;$i--){
	$contenu_stat .= "['".$graphMois[$i]."',".formaterPrix($sousTotalHtGraph[$i],2,'.','').",".formaterPrix($TVAGraph[$i],2,'.','').",".formaterPrix($margeGraph[$i],2,'.','')."],";
}
$contenu_stat = substr_replace($contenu_stat,'',-1);

eval(charge_template($langue, $referencepage, "ChiffreAffaires12mois"));
//////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////// FIN RAPPORT ACTIVITE CHIFFRE AFFAIRES ///////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////// DEBUT RAPPORT ACTIVITE CHIFFRE AFFAIRES PAR MOYEN DE REGLEMENT ///////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$periodeLib = "$multilangue[du] ".convertirDateEnChaine($periode1)." $multilangue[au] ".convertirDateEnChaine($periode2);

$where = "";
if (in_array(5910, $modules) || in_array(5909, $modules)) {
	$where .= " OR neteven_marketplaceid != '0' ";
}
if (in_array(5983, $modules)) {
	$where .= " OR id_flux_lengow != '0' ";
}

$moyenPaiement = $DB_site->query("SELECT mpsite.* FROM moyenpaiement_site mpsite INNER JOIN moyenpaiement mp USING (moyenid) WHERE siteid = '$siteid' AND (activeV1 ='1' OR activeV2 ='1') $where");
$tabPaiement = array();
$tabPaiementId = array();
while($recupMoyenPaiement = $DB_site->fetch_array($moyenPaiement)){
	$tabPaiement[] = $recupMoyenPaiement[libelle];
	$tabPaiementId[] = $recupMoyenPaiement['moyenid'];
}
$tabNbrCommandes = array();
$tabMontant = array();
$totMontant = 0;
$listeMoyensPaiement = "";
$listeNbrCommandes = "";
$listeMontants = "";
$datasGraph = "";
foreach($tabPaiement as $key => $value){
	$recupNombresCommandes = $DB_site->query_first("SELECT COUNT(*) AS nbrCommandes FROM facture WHERE etatid IN ($etatid)  AND siteid=".$siteid." AND montanttotal_ttc > 0 AND $filtredate >= ('".$periode1."') AND $filtredate <= ('".$periode2."') AND moyenid=".$tabPaiementId[$key]);
	$recupMontant = $DB_site->query_first("SELECT SUM(montanttotal_horsfraisport_ht) AS montant FROM facture WHERE etatid IN ($etatid)  AND siteid=".$siteid." AND montanttotal_ttc > 0 AND $filtredate >= ('".$periode1."') AND $filtredate <= ('".$periode2."') AND moyenid=".$tabPaiementId[$key]);
	$tabMontant[$key] = $recupMontant['montant'];
	$totMontant += $recupMontant['montant'];
	$tabNbrCommandes[$key] = $recupNombresCommandes['nbrCommandes'];
	$listeMoyensPaiement .= $tabPaiement[$key]."<br>";	
	$listeNbrCommandes .= $tabNbrCommandes[$key]."<br>";
	$listeMontants .= formaterPrix($tabMontant[$key])." $symboleMonetaire<br>";	
	$datasGraph .= "data.setValue($key, 0, '".$tabPaiement[$key]."');";
    $datasGraph .= "data.setValue($key, 1, ".formaterPrix($tabMontant[$key],2,'.','').");";	
}
if($totMontant > 0){
	$nbRowsGraph = count($tabPaiement);
	eval(charge_template($langue, $referencepage, "ChiffreAffairesParReglementEnCoursGraph"));
}	

eval(charge_template($langue, $referencepage, "ChiffreAffairesParReglementEnCours"));

### 12 mois ###
$graphMois = array();
$graphMoisDonneTotal = array();
$graphMoisDonneProd = array();
for($k=1;$k<14;$k++){
	$moisEnCours = date("m",mktime(0, 0, 0, date("m")-$k , 01, date("Y")));
	$anneeEnCours = date("Y",mktime(0, 0, 0, date("m")-$k  , 01, date("Y")));

	$nbrJour = date("t",mktime(0, 0, 0, date("m")-$k  , 01, date("Y")));
	$periode1 = date("Y-m-d",mktime(0, 0, 0, date("m")-$k  , 01, date("Y"))); // premier jour du mois
	$periode2 = date("Y-m-d",mktime(0, 0, 0, date("m")-$k  , $nbrJour, date("Y"))); // dernier jour du mois
	$moisEnCoursLib = retournerMoisFr($moisEnCours)." ".$anneeEnCours;
	
	$tabNbrCommandes = array();
	$tabMontant = array();
	
	$listeMoyensPaiement = "";
	$listeNbrCommandes = "";
	$listeMontants = "";
	foreach($tabPaiement as $key => $value){
		$recupNombresCommandes = $DB_site->query_first("SELECT COUNT(*) AS nbrCommandes FROM facture WHERE etatid IN ($etatid)  AND siteid=".$siteid." AND montanttotal_ttc > 0 AND $filtredate >= ('".$periode1."') AND $filtredate <= ('".$periode2."') AND moyenid=".$tabPaiementId[$key]);
		$recupMontant = $DB_site->query_first("SELECT SUM(montanttotal_horsfraisport_ht) AS montant FROM facture WHERE etatid IN ($etatid)  AND siteid=".$siteid." AND montanttotal_ttc > 0 AND $filtredate >= ('".$periode1."') AND $filtredate <= ('".$periode2."') AND moyenid=".$tabPaiementId[$key]);
		$tabMontant[$key] = $recupMontant['montant'];
		$tabNbrCommandes[$key] = $recupNombresCommandes['nbrCommandes'];
		$listeMoyensPaiement .= $tabPaiement[$key]."<br>";	
		$listeNbrCommandes .= $tabNbrCommandes[$key]."<br>";
		$listeMontants .= formaterPrix($tabMontant[$key])." $symboleMonetaire<br>";	
	}
	eval(charge_template($langue, $referencepage, "ChiffreAffairesParReglement12moisBit"));
}	

// Graph
$periodeGraphMoyen2 = date("Y-m-d",mktime(0, 0, 0, date("m") , 00, date("Y")));
$periodeGraphMoyen1 = date("Y-m-d",mktime(0, 0, 0, date("m")-13  , 01, date("Y")));
$sizeTabPaiement=0;
$scriptContent='';
foreach($tabPaiement as $key => $value){
	$recupMontant = $DB_site->query_first("SELECT SUM(montanttotal_horsfraisport_ht) AS montant FROM facture WHERE etatid IN ($etatid)  AND siteid=".$siteid." AND montanttotal_ttc > 0 AND $filtredate >= ('".$periodeGraphMoyen1."') AND $filtredate <= ('".$periodeGraphMoyen2."') AND moyenid=".$tabPaiementId[$key]);
	if($recupMontant['montant'] >0 ) {
		$scriptContent .=  "data.setValue($sizeTabPaiement, 0, '".$tabPaiement[$key]."');\n";
		$scriptContent .= "data.setValue($sizeTabPaiement, 1, ".formaterPrix($recupMontant['montant'],2,'.','').");\n";
		$sizeTabPaiement++;
	}
}
$scriptContent = "data.addRows($sizeTabPaiement);\n".$scriptContent;

eval(charge_template($langue, $referencepage, "ChiffreAffairesParReglement12mois"));
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////// FIN RAPPORT ACTIVITE CHIFFRE AFFAIRES PAR MOYEN DE REGLEMENT ///////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


$TemplateIncludejavascript = eval(charge_template($langue, $referencepage, "Includejavascript"));
eval(charge_template($langue, "commun", "header"));
eval(charge_template($langue, "commun", "footer"));
eval(charge_template($langue, $referencepage, "index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();

?>