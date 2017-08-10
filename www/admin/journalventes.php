<?php
include "./includes/header.php";

$referencepage="journalventes";
$pagetitle = "Journal des ventes - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


if ($action == "afficher"){
	$defaut = date('01/01/Y');
	$search_date_from = empty($search_date_from) ? date('01/m/Y') : $search_date_from;
	$search_date_to = empty($search_date_to) ? date('d/m/Y') : $search_date_to;
	$filtredate = empty($filtredate) ? 'dateexpedition' : $filtredate;
	$filtremoyen = empty($filtremoyen) ? 0 : $filtremoyen;
	$trierpar = ($trierpar ==  "1" ? 'panierid' : 'factureid');
	$distinctmoyenids = $DB_site->query("SELECT DISTINCT moyenid FROM facture");
	while ($distinctmoyenid = $DB_site->fetch_array($distinctmoyenids)){
		$moyenpaiement = $DB_site->	query_first("SELECT * FROM moyenpaiement_site WHERE moyenid = '$distinctmoyenid[moyenid]'");
		if ($moyenpaiement[moyenid] == '1')
			$selected = "selected";
		else
			$selected = "";
		eval(charge_template($langue,$referencepage,"ListeMoyenPaiement"));
	}
	$fichier = fopen('./export/journal_ventes.csv', 'w');
	$tabtaux = array();
	$totalM_0 = 0;
	$totalF_0 = 0;
	$total_TVA = 0;
	$total_TTC = 0;
	$montantstaux = $DB_site->query("SELECT tvaport FROM facture f WHERE factureid > 0 AND numerofacture>0 AND deleted = '0'
							  	 	AND $filtredate BETWEEN '" . date("Y-m-d", strtotime(str_replace('/', '-', $search_date_from))) . "' AND '" . date("Y-m-d", strtotime(str_replace('/', '-', $search_date_to))) . "'");
	while ($montanttaux = $DB_site->fetch_array($montantstaux)){
		if (!in_array($montanttaux[tvaport],$tabtaux) && $montanttaux[tvaport] > 0){
			$tabtaux[] = $montanttaux[tvaport];
			${'totalM_'.$montanttaux[tvaport]}=0;
			${'totalF_'.$montanttaux[tvaport]}=0;
		}
	}
	$montantstaux = $DB_site->query("SELECT lf.tva FROM lignefacture lf INNER JOIN facture f USING (factureid) WHERE f.factureid > 0 AND f.numerofacture>0 AND f.deleted = '0'
									AND $filtredate BETWEEN '" . date("Y-m-d", strtotime(str_replace('/', '-', $search_date_from))) . "' AND '" . date("Y-m-d", strtotime(str_replace('/', '-', $search_date_to))) . "'");
	while ($montanttaux = $DB_site->fetch_array($montantstaux)){
		if (!in_array($montanttaux[tva],$tabtaux) && $montanttaux[tva] > 0){
			$tabtaux[] = $montanttaux[tva];
			${'totalM_'.$montanttaux[tva]}=0;
			${'totalF_'.$montanttaux[tva]}=0;
		}
	}
	$contenu = "$multilangue[num_commande];$multilangue[date_de_commande];$multilangue[client];$multilangue[num_facture];$multilangue[date_de_facture];$multilangue[montant] $multilangue[marchandise] $multilangue[exonere] $multilangue[ht];$multilangue[montant_frais_port] $multilangue[exonere] $multilangue[ht];";
	foreach($tabtaux as $value){
		$contenu .= "$multilangue[montant] $multilangue[marchandise] $multilangue[ht] $multilangue[france] $value%;$multilangue[marchandise] $multilangue[ht] $multilangue[intracom] $value%;";
		$contenu .= "$multilangue[montant] $multilangue[montant_frais_port] $multilangue[ht] $multilangue[france] $value%;$multilangue[montant_frais_port] $multilangue[ht] $multilangue[intracom] $value%;";
	}
	$contenu .= "$multilangue[montant] $multilangue[tva] $multilangue[france];$multilangue[montant] $multilangue[tva] $multilangue[intracom];";
	$contenu .= "$multilangue[montant] $multilangue[ttc] $multilangue[france];$multilangue[montant] $multilangue[ttc] $multilangue[intracom];$multilangue[moyen_de_reglement]\n";
	fputs($fichier, $contenu);
	foreach($tabtaux as $value){
		eval(charge_template($langue,$referencepage,"AfficherValueTitre"));
	}
	$factures = $DB_site->query("SELECT * FROM facture f INNER JOIN moyenpaiement_site mps USING(moyenid) INNER JOIN devise USING(deviseid) WHERE factureid > 0 AND numerofacture > 0 AND deleted = '0' " . ($filtremoyen ? "AND f.moyenid = '$filtremoyen' " : '') . "
								AND $filtredate BETWEEN '" . date("Y-m-d", strtotime(str_replace('/', '-', $search_date_from))) . "' AND '" . date("Y-m-d", strtotime(str_replace('/', '-', $search_date_to))) . "' AND f.siteid = '1' AND mps.siteid = '1' ORDER BY f.$trierpar");
	while ($facture = $DB_site->fetch_array($factures)){
		echo "$devise[symbole]\n";
		$totauxFacture = calculerTotalFacture($DB_site, $facture[factureid]);
		$totauxFacture['montantPortHT'] += $totauxFacture['supplementLivraisonHT'];
		$totauxFacture['montantPortTTC'] += $totauxFacture['supplementLivraisonTTC'];
		$totalArticlesHT = 0;
		$totalArticlesTTC = 0;
		$lignesfacture = $DB_site->query("SELECT * FROM lignefacture WHERE factureid = '$facture[factureid]'");
		while ($lignefacture = $DB_site->fetch_array($lignesfacture)){
			$totalArticlesHT += ($lignefacture[$prixht] + $lignefacture[$prixperso]) * $lignefacture[qte];
			$totalArticlesTTC += ($lignefacture[$prix] + $lignefacture[$prixperso]) * $lignefacture[qte];
		}
		$lignesfacture = $DB_site->query("SELECT * FROM lignefacture WHERE factureid = '$facture[factureid]'");
		$typetva = ($facture[lpaysid] == 57 ? 'france' : 'intracom');
		$sousTotalM = array();
		$sousTotalM['0'][$typetva] = 0;
		foreach($tabtaux as $value)
			$sousTotalM[$value][$typetva] = 0;
		$sousTotalF = $sousTotalM;
		$montantHtTemp = 0;
		$montantHtTotal = 0;
		$sousTotalF[$facture['tvaport']][$typetva] += $totauxFacture['montantPortHT'];
		$tauxnormalTmp = 0 ;
		while ($lignefacture = $DB_site->fetch_array($lignesfacture)){
			$montantHtTemp = $lignefacture[$prixht];
			$montantHtLigneTemp = (($montantHtTemp + $lignefacture[$prix_perso]) * $lignefacture['qte']);
			$montantHtTotal += $montantHtLigneTemp;
			$sousTotalM[$lignefacture['tva']][$typetva] += $montantHtLigneTemp;
			if($totauxFacture[$montantCadeauHT] != 0){
				$pourcentage = $totalArticlesHT > 0 ? $montantHtLigneTemp * 100 / $totalArticlesHT : 100;
				$prorataReduction = $totauxFacture[$montantCadeauHT] * ($pourcentage / 100);
				$sousTotalM[$lignefacture['tva']][$typetva] -= $prorataReduction;
			}
			if ($tauxnormalTmp < $lignefacture['tva'])
				$tauxnormalTmp=$lignefacture['tva'];
		}
		if ($facture[avoir_parentid]){
			$lignesFactureOrigine = $DB_site->query("SELECT * FROM lignefacture WHERE factureid = '$facture[avoir_parentid]'");
			while ($lignefacture = $DB_site->fetch_array($lignesFactureOrigine)){
				if($tauxnormalTmp < $lignefacture['tva'])
					$tauxnormalTmp = $lignefacture['tva'];
			}
		}
		if($totauxFacture['montantMaxichequeHT']==$totauxFacture['montantMaxichequeTTC']) 
			$sousTotalM[0][$typetva]-=$totauxFacture['montantMaxichequeHT'];
		else 
			$sousTotalM[$tauxnormalTmp][$typetva]-=$totauxFacture['montantMaxichequeHT'];
		if($totauxFacture['montantFideliteHT']==$totauxFacture['montantFideliteTTC']) 
			$sousTotalM[0][$typetva]-=$totauxFacture['montantFideliteHT'];
		else 
			$sousTotalM[$tauxnormalTmp][$typetva]-=$totauxFacture['montantFideliteHT'];
		if($totauxFacture['montantBonAchatHT']==$totauxFacture['montantBonAchatTTC']) 
			$sousTotalM[0][$typetva]-=$totauxFacture['montantBonAchatHT'];
		else 
			$sousTotalM[$tauxnormalTmp][$typetva]-=$totauxFacture['montantBonAchatHT'];
		if($totauxFacture['montantRemiseCommercialeHT']==$totauxFacture['montantRemiseCommercialeTTC']) 
			$sousTotalM[0][$typetva]-=$totauxFacture['montantRemiseCommercialeHT'];
		else 
			$sousTotalM[$tauxnormalTmp][$typetva]-=$totauxFacture['montantRemiseCommercialeHT'];
		if($totauxFacture['montantOperationsHT']==$totauxFacture['montantOperationsTTC']) 
			$sousTotalM[0][$typetva]-=$totauxFacture['montantOperationsHT'];
		else 
			$sousTotalM[$tauxnormalTmp][$typetva]-=$totauxFacture['montantOperationsHT'];
		$totauxFacture[$typetva][montantTVA] = $totauxFacture[montantTVA];
		$totauxFacture[$typetva][totalTTC] = $totauxFacture[totalTTC];
		$contenu="$facture[factureid];" . date("d/m/Y", strtotime($facture[datefacture])) . ";$facture[nom] $facture[prenom];$facture[numerofacture];" . date("d/m/Y", strtotime($facture[dateexpedition])) . ";".formaterPrix($sousTotalM['0'][$typetva]).';'.formaterPrix($sousTotalF['0'][$typetva]).';';
		foreach($tabtaux as $value){
			$contenu.=formaterPrix($sousTotalM[$value]['france']).';'.formaterPrix($sousTotalM[$value]['intracom']).';';
			$contenu.=formaterPrix($sousTotalF[$value]['france']).';'.formaterPrix($sousTotalF[$value]['intracom']).';';
		}
		$contenu .= formaterPrix($totauxFacture['france'][montantTVA]).';'.formaterPrix($totauxFacture['intracom'][montantTVA]).";";
		$contenu .= formaterPrix($totauxFacture['france'][totalTTC]).';'.formaterPrix($totauxFacture['intracom'][totalTTC]).";$facture[libelle]\n";
		fputs($fichier, $contenu);
		reset($sousTotalM);
		reset($sousTotalF);
		$totalF_0 += $sousTotalF['0'][$typetva];
		$totalM_0 += $sousTotalM['0'][$typetva];
		foreach($tabtaux as $value){
			${'totalF_'.$value.'_'.$typetva} += $sousTotalF[$value][$typetva];
			${'totalM_'.$value.'_'.$typetva} += $sousTotalM[$value][$typetva];
		}
		$total_TVA_france += $totauxFacture['france'][montantTVA];
		$total_TVA_intracom += $totauxFacture['intracom'][montantTVA];
		$total_TTC_france += $totauxFacture['france'][totalTTC];
		$total_TTC_intracom += $totauxFacture['intracom'][totalTTC];
		$facture[datefacture] =  date("d/m/Y", strtotime($facture[datefacture]));
		$facture[dateexpedition] =  date("d/m/Y", strtotime($facture[dateexpedition]));
		$sousTotalMTypeTVA = formaterPrix($sousTotalM['0'][$typetva]);
		$sousTotalFTypeTVA = formaterPrix($sousTotalF['0'][$typetva]);
		$TemplateJournalventesAfficherValue = "";
		foreach($tabtaux as $value){
 			$sousTotalMFrance = formaterPrix($sousTotalM[$value]['france']);
			$sousTotalMIntracom = formaterPrix($sousTotalM[$value]['intracom']);
			$sousTotalFFrance = formaterPrix($sousTotalF[$value]['france']);
			$sousTotalFIntracom = formaterPrix($sousTotalF[$value]['intracom']);
			eval(charge_template($langue,$referencepage,"AfficherValue"));
		}
		$totauxFactureFranceMontantTVA = formaterPrix($totauxFacture['france'][montantTVA]);
		$totauxFactureIntracomMontantTVA = formaterPrix($totauxFacture['intracom'][montantTVA]);
		$totauxFactureFranceTotalTTC = formaterPrix($totauxFacture['france'][totalTTC]);
		$totauxFactureIntracomTotalTTC = formaterPrix($totauxFacture['intracom'][totalTTC]);
		eval(charge_template($langue,$referencepage,"AfficherBit"));
	}		
	$contenu = "\n;;;;;$multilangue[montant] $multilangue[marchandise] $multilangue[exonere] $multilangue[ht];$multilangue[montant_frais_port] $multilangue[exonere] $multilangue[ht];";
	foreach($tabtaux as $value){
		$contenu.="$multilangue[montant] $multilangue[marchandise] $multilangue[ht] $multilangue[france] $value %;$multilangue[marchandise] $multilangue[ht] $multilangue[intracom] $value %;";
		$contenu.="$multilangue[montant] $multilangue[montant_frais_port] $multilangue[ht] $multilangue[france] $value %;$multilangue[montant_frais_port] $multilangue[ht] $multilangue[intracom] $value %;";
	}
	$contenu .= "$multilangue[montant] $multilangue[tva] $multilangue[france] ;$multilangue[montant] $multilangue[tva] $multilangue[intracom];";
	$contenu .= "$multilangue[montant] $multilangue[ttc] $multilangue[france] ;$multilangue[montant] $multilangue[ttc] $multilangue[intracom]";
	fputs($fichier, $contenu);
	$contenu="\n;;;;;".formaterPrix($totalM_0).';'.formaterPrix($totalF_0).';';
	foreach($tabtaux as $value){
		$contenu .= formaterPrix(${'totalM_'.$value.'_france'}).';'.formaterPrix(${'totalM_'.$value.'_intracom'}).';';
		$contenu .= formaterPrix(${'totalF_'.$value.'_france'}).';'.formaterPrix(${'totalF_'.$value.'_intracom'}).';';
	}
	$contenu .= formaterPrix($total_TVA_france).';'.formaterPrix($total_TVA_intracom).';';
	$contenu .= formaterPrix($total_TTC_france).';'.formaterPrix($total_TTC_intracom);
	fputs($fichier, $contenu);
	fclose($fichier);
	foreach($tabtaux as $value)
		eval(charge_template($langue,$referencepage,"TotauxValueTitre"));
	$facture = $DB_site->query_first("SELECT * FROM facture f INNER JOIN moyenpaiement_site mps USING(moyenid) INNER JOIN devise USING(deviseid) WHERE factureid > 0 AND numerofacture > 0 AND deleted = '0' " . ($filtremoyen ? "AND f.moyenid = '$filtremoyen' " : '') . "
									 AND $filtredate BETWEEN '" . date("Y-m-d", strtotime(str_replace('/', '-', $search_date_from))) . "' AND '" . date("Y-m-d", strtotime(str_replace('/', '-', $search_date_to))) . "' AND f.siteid = '1' AND mps.siteid = '1' ORDER BY f.$trierpar");
	$totalM0 = formaterPrix($totalM_0);
	$totalF0 = formaterPrix($totalF_0);
	foreach($tabtaux as $value){
		$totalMFrance = formaterPrix(${'totalM_'.$value.'_france'});
		$totalMIntracom = formaterPrix(${'totalM_'.$value.'_intracom'});
		$totalFFrance = formaterPrix(${'totalF_'.$value.'_france'});
		$totalFIntracom = formaterPrix(${'totalF_'.$value.'_intracom'});
		eval(charge_template($langue,$referencepage,"TotauxValue"));
	}
	$totalTVAFrance = formaterPrix($total_TVA_france);
	$totalTVAIntracom = formaterPrix($total_TVA_intracom);
	$totalTTCFrance = formaterPrix($total_TTC_france);
	$totalTTCIntracom = formaterPrix($total_TTC_intracom);
	eval(charge_template($langue,$referencepage,"Liste"));
	eval(charge_template($langue,$referencepage,"Afficher"));
	eval(charge_template($langue,$referencepage,"Totaux"));
	$libNavigSupp = $multilangue[afficher_journal_ventes];
	eval(charge_template($langue,  $referencepage, "NavigSupp"));
}

if (!isset($action) || $action == ""){
	$search_date_from = empty($search_date_from) ? date('01/m/Y') : $search_date_from;
	$search_date_to = empty($search_date_to) ? date('d/m/Y') : $search_date_to;
	$distinctmoyenids = $DB_site->query("SELECT DISTINCT moyenid FROM facture");
	while ($distinctmoyenid = $DB_site->fetch_array($distinctmoyenids)){
		$moyenpaiement = $DB_site->	query_first("SELECT * FROM moyenpaiement_site WHERE moyenid = '$distinctmoyenid[moyenid]'");
		if ($moyenpaiement[moyenid] == '1')
			$selected = "selected";	
		else
			$selected = "";
		eval(charge_template($langue,$referencepage,"ListeMoyenPaiement"));
	}
	eval(charge_template($langue,$referencepage,"Liste"));
	$libNavigSupp = $multilangue[afficher_journal_ventes];
	eval(charge_template($langue,  $referencepage, "NavigSupp"));
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