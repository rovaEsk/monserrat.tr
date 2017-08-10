<?php
include "./includes/header.php";

$referencepage="avoirs";
$pagetitle = "Avoirs - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


$mode = "test_modules";

if (isset($action) && $action == "docreerAvoir") {
	$rs_numerofacture = $DB_site->query_first("SELECT MAX(numerofacture) AS numerofacture FROM facture");
	$num_facture = $rs_numerofacture[numerofacture] + 1;
	$DB_site->query("UPDATE facture SET deleted = '0', datevalidation = '$datedujour', dateexpedition = '$datedujour', etatid = '5', numerofacture = '$num_facture', moyenid = '$moyenid' WHERE factureid='$newFactureid'");
	// Commentaire
	if ($commentaire) {
		$DB_site->query("INSERT INTO facture_commentaires (factureid, commentaire, date) VALUES ('$newFactureid', '".securiserSql($commentaire)."', NOW())");
	}
	// Réincrémentaion Stocks
	if ($reincrementationStocks && $reincrementationStocks == "1") {
		$DB_site->query("UPDATE facture SET decrementation = '0', dateincrementation = '$datedujour' WHERE factureid = '$factureid'");
		$articles = $DB_site->query("SELECT lf.artid, lf.qte, lf.lignefactureid, a.typearticle from lignefacture lf INNER JOIN article a ON (a.artid = lf.artid) WHERE lf.factureid = '$newFactureid'");
		while ($article = $DB_site->fetch_array($articles)) {
			if ($article[typearticle] == "1") {
				$caractvals = array() ;
				$artcaracts = $DB_site->query("SELECT caractvalid FROM lignefacturecaracteristique WHERE lignefactureid = '$article[lignefactureid]'");
				while ($artcaract = $DB_site->fetch_array($artcaracts)) {
					array_push($caractvals, $artcaract[caractvalid]) ;
				}
				decrementerStock($DB_site, $article[artid], retournerStockid($DB_site, $article[artid], $caractvals), $article[qte]);
			}
		}
	}
	supprimerPdfCommande($factureid);
	supprimerPdfCommande($newFactureid);
	header("location: commandes.php?action=modifier&factureid=$newFactureid");
}

if (isset($action) && $action == "creerAvoir") {
	$libNavigSupp = $multilangue[creer_avoir];
	eval(charge_template($langue, $referencepage, "LibNavigSupp"));
	
	if(in_array(4, $modules) || $mode == "test_modules"){
		eval(charge_template($langue, $referencepage, "AjoutStocks"));
	}
	
	$avoirsExistants = $DB_site->query("SELECT datefacture, factureid FROM facture WHERE avoir_parentid = '$factureid' AND deleted = '0' ORDER BY factureid");
	while($avoirExistant = $DB_site->fetch_array($avoirsExistants)){
		eval(charge_template($langue, $referencepage, "AjoutExistantBit"));
	}
	
	$facture = $DB_site->query_first("SELECT * FROM facture WHERE factureid = '$factureid'");
	$qtesExpediees = $DB_site->query_first("SELECT SUM(cl.qte) AS total FROM colis_lignefacture cl INNER JOIN colis c USING (colisid) WHERE c.factureid = '$factureid'");
	
	$devise = $tabsites[$facture[siteid]][devise_complete];
	
	$dateFacture = convertirDateEnChaine($facture[datefacture]);
	$montantTotalTTC = formaterPrix($facture[montanttotal_ttc]);
	
	$moyen_paiement_facture = $DB_site->query_first("SELECT libelle FROM moyenpaiement_site WHERE moyenid = '$facture[moyenid]' AND siteid = '1'");
	
	$moyens_paiement = $DB_site->query("SELECT * FROM moyenpaiement_site WHERE siteid = '1'");		
	while($moyen_paiement = $DB_site->fetch_array($moyens_paiement)){
		if($moyen_paiement[moyenid] == $facture[moyenid]){
			$selectedMoyen = "selected = 'selected'";
		}else{
			$selectedMoyen = "";
		}
		eval(charge_template($langue, $referencepage, "AjoutReglementBit"));
	}
	
	$tabQtesExpediees = array();
	$tabQtesAutresAvoirs = array();
	$lignesfacture = $DB_site->query("SELECT lignefactureid, qte FROM colis_lignefacture cl INNER JOIN colis c USING (colisid) WHERE c.factureid = '$factureid'");
	while($lignefacture = $DB_site->fetch_array($lignesfacture)) {
		$tabQtesExpediees[$lignefacture[lignefactureid]] += $lignefacture[qte];
		$sslignesfacture = $DB_site->query("SELECT qte FROM lignefacture lf INNER JOIN facture f USING (factureid) WHERE f.etatid = '5' AND lf.avoir_lignefactureidparent = '$lignefacture[lignefactureid]'");
		while($sslignefacture = $DB_site->fetch_array($sslignesfacture)) {
			$tabQtesAutresAvoirs[$lignefacture[lignefactureid]] += $sslignefacture[qte];
		}
	}
	foreach ($tabQtesExpediees as $lignefactureid => $qtesExp) {
		if (!$tabQtesAutresAvoirs[$lignefactureid]) {
			$tabQtesAutresAvoirs[$lignefactureid] = 0;
		}
		$qtesRestantes = $qtesExp - $tabQtesAutresAvoirs[$lignefactureid];
		if ($qtesRestantes) {
			$lignefacture = $DB_site->query_first("SELECT * FROM lignefacture WHERE lignefactureid = '$lignefactureid'");
			eval(charge_template($langue, $referencepage, "AjoutArticleListe1Bit"));
		}
	}
	
	if($facture[supplement_livraison]) {
		$facture[supplement_livraisonE] = formaterPrix($facture[supplement_livraison]) ;
		eval(charge_template($langue, $referencepage, "AjoutSupplementLivraison"));
	}
			
	eval(charge_template($langue, $referencepage, "Ajout"));
}


if(!isset($action) || $action == ""){
	
}


$TemplateIncludejavascript = eval(charge_template($langue,  $referencepage, "Includejavascript"));
eval(charge_template($langue, "commun", "header"));
eval(charge_template($langue, "commun", "footer"));
eval(charge_template($langue, $referencepage, "index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();

?>