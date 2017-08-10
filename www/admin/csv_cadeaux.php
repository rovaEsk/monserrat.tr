<?php
/**
* Export personnalisé par sélection bons de réduction
* @Benjamin & Adeline
* @Enjoy
*/
	
require "includes/admin_global.php";

//construction des titres des colonnes
$contenu="";

if ($exporterTitre == 1){
	
	//Code bon réduction
	if ($cadeauCode == 'on') $contenu.="$multilangue[code_bon_reduction];";
	
	//Date commande
	if ($cadeauDateCommande == 'on') $contenu.="$multilangue[date_de_commande];";
	
	//Num commande
	if ($cadeauFactureid == 'on') $contenu.="$multilangue[numero_commande];";
	
	//Etat commande
	if ($cadeauEtat == 'on') $contenu.="$multilangue[etat_commande];";
	
	//Client
	if ($cadeauClient == 'on') $contenu.="$multilangue[client];";
	
	//Montant commande
	if ($cadeauMontanttotal_ttc == 'on') $contenu.="$multilangue[montant_total] $multilangue[ttc];";
	
	//Date commande
	if ($cadeauMontantCadeau == 'on') $contenu.="$multilangue[montant_reduction] $multilangue[ttc];";
	
	$contenu.="\n";
}

$filename = './csv/cadeaux.csv';
if (is_writable($filename)) {
	if (!$handle = fopen($filename, 'w')) {
		echo "$multilangue[erreur_ouverture_fichier] ($filename)";
		exit;
	}
	if (fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE) {
		echo "$multilangue[erreur_ecriture_fichier] ($filename)";
		exit;
	}
}else {
	echo "$multilangue[erreur_accessibilite_ecriture_fichier] ($filename)";
	exit();
}

$handle = fopen($filename, 'a');

//requete en fonction des critères sélectionnés
$where = "";
$or = "";
$innerJoin = "";

switch ($type) {
	case "cadeau":
		//Dates facture
		if ($critereDateDebutFacture != "" && $critereDateFinFacture != ""){
			$where.=" AND f.datefacture > '".convertirChaineEnDate($critereDateDebutFacture)."' AND f.datefacture < '".convertirChaineEnDate($critereDateFinFacture)."'";
		}
		
		//Critere selon etatid
		if (is_array($critereEtatid)){
			$tabEtatFacture=array();
			foreach ($critereEtatid as $key => $value){
				array_push($tabEtatFacture,$value);
			}
			$where.=" AND f.etatid IN (".implode(',',$tabEtatFacture).")";
		}
		
		$sql = "SELECT DISTINCT(f.cadeauid), c.code FROM facture f INNER JOIN cadeau c ON (c.cadeauid = f.cadeauid) WHERE f.cadeauid > 0 $where ORDER BY c.code";
		
		$cadeaux=$DB_site->query($sql);
		
		while($cadeau=$DB_site->fetch_array($cadeaux)) {
			$nbUtilisations = 0;
			$montantCadeau = 0;
			$listeClients = "";
			
			$sql = "SELECT f.* FROM facture f WHERE f.cadeauid = $cadeau[cadeauid] $where ORDER BY f.factureid";
			$factures=$DB_site->query($sql);
			while($facture=$DB_site->fetch_array($factures)) {
				$nbUtilisations++;
				$montantCadeau += $facture[montantcadeau];
				$listeClients .= secureChaineExport($facture[nom]." ".$facture[prenom])." ($multilangue[num_commande] $facture[factureid]) | ";
			}			
			
			$contenu="";
			
			//Code bon réduction
			if ($cadeauCode == 'on') {
				$contenu .= $cadeau[code].";";
			}
			
			//Utilisations
			if ($cadeauUtilisations == 'on') {
				$contenu .= $nbUtilisations.";";
			}
			
			//Montant cadeau
			if ($cadeauMontantCadeau == 'on'){
				$contenu .= formaterPrix($montantCadeau)." €;";
			}
			
			// Liste cliens
			if ($cadeauListeClients == 'on') {
				$contenu .= substr($listeClients, 0, -2).";";
			}
			
			$contenu.="\n";
			fwrite($handle, stripslashes(html_entity_decode($contenu)));			
			
		}
	
	break;
	case "facture":
		//Dates facture
		if ($critereDateDebutFacture != "" && $critereDateFinFacture != ""){
			$where.=" AND f.datefacture > '".convertirChaineEnDate($critereDateDebutFacture)."' AND f.datefacture < '".convertirChaineEnDate($critereDateFinFacture)."'";
		}
		
		//Critere selon etatid
		if (is_array($critereEtatid)){
			$tabEtatFacture=array();
			foreach ($critereEtatid as $key => $value){
				array_push($tabEtatFacture,$value);
			}
			$where.=" AND f.etatid IN (".implode(',',$tabEtatFacture).")";
		}
		
		$sql = "SELECT f.*, c.code FROM facture f INNER JOIN cadeau c ON (c.cadeauid = f.cadeauid) WHERE f.cadeauid > 0 $where ORDER BY f.factureid";
		
		$factures=$DB_site->query($sql);
		
		while($facture=$DB_site->fetch_array($factures)) {
			
			$contenu="";
			
			//Code bon réduction
			if ($cadeauCode == 'on') {
				$contenu .= $facture[code].";";
			}
			
			//Date commande
			if ($cadeauDateCommande == 'on') {
				$contenu .= convertirDateEnChaine($facture[datefacture]).";";
			}
			
			//Num commande
			if ($cadeauFactureid == 'on') {
				$contenu .= $facture[factureid].";";
			}
			
			//Etat de la commande
			if ($cadeauEtat == 'on'){
				$contenu .= secureChaineExport(retournerLibelleEtatFacture($DB_site, $facture[etatid])).";";
			}
			
			//Client
			if ($cadeauClient == 'on'){
				$contenu .= secureChaineExport($facture[nom]." ".$facture[prenom]).";";	
			}
			
			//Montant commande TTC
			if ($cadeauMontanttotal_ttc == 'on'){
				$contenu .= formaterPrix($facture[montanttotal_ttc])." €;";
			}
			
			//Montant réduction TTC
			if ($cadeauMontantCadeau == 'on'){
				$contenu .= formaterPrix($facture[montantcadeau])." €;";
			}
			
			$contenu.="\n";
			fwrite($handle, stripslashes(html_entity_decode($contenu)));
		}
	
	break;
}

fclose($handle);
$file = realpath(".")."/csv/cadeaux.csv";  
header('Content-Description: File Transfer'); 
header('Content-Type: application/force-download'); 
header('Content-Length: ' . filesize($file)); 
header('Content-Disposition: attachment; filename=' . basename($file)); 
readfile($file); 
exit;

?>
