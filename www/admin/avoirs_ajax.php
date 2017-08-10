<?php

require ("includes/admin_global.php");

if($action == "calculerAvoir"){	
	if(empty($newFactureid)){
		$DB_site->query("INSERT INTO facture (factureid, datefacture, timestamp, deleted) VALUES (DEFAULT, '$datedujour', '".date('Y-m-d H:i:s')."','1')");
		$newFactureid = $DB_site->insert_id() ;
	}		
	$facture = $DB_site->query_first("SELECT * FROM facture WHERE factureid='$factureid'");
			
	$montants = calculerTotalFacture($DB_site, $factureid) ;
	if(!empty($lignesfacture) || !empty($remiseCommerciale) || !empty($supplementLivraison)){
		if ($lignesfacture) {
			$articlesLignesFacture=implode(',',$lignesfacture);
		}
		$update = '';
		foreach($facture as $key=>$value){
			if($key!='factureid' && $key != 'dateexpedition' && $key != 'numerofacture' && $key!='avoir_parentid' && $key!='etatid' && $key!='datefacture' && $key != 'deleted' && $key != 'timestamp' && !preg_match('/^([0-9]+)$/',$key)){
				$update.="$key = '".securiserSql($value)."', ";
			}
		}
		$DB_site->query("UPDATE facture SET $update avoir_parentid='$factureid' WHERE factureid = '$newFactureid'");
		//On réinitialise les lignes facture de la newFacture
		$DB_site->query("DELETE FROM lignefacturechamp WHERE lignefactureid IN (SELECT lignefactureid FROM lignefacture WHERE factureid = '$newFactureid')");
		$DB_site->query("DELETE FROM lignefacturecaracteristique WHERE lignefactureid IN (SELECT lignefactureid FROM lignefacture WHERE factureid = '$newFactureid')");
		$DB_site->query("DELETE FROM lignefacture WHERE factureid = '$newFactureid'");
			
		// Si la qte de la ligne à déplacer est égale à celle de la ligne de facture : changer juste le factureid de la ligne.
		// Sinon créer une nouvelle ligne de facture avec le $newFactureid et la qte + soustraire la qte
		if (!empty($lignesfacture))	{
			$checkQteLignes = $DB_site->query("SELECT lignefactureid,qte FROM lignefacture WHERE lignefactureid IN (".$articlesLignesFacture.")");
			while($checkQteLigne = $DB_site->fetch_array($checkQteLignes)){			
				$newlignefactureid = copier_lignefacture ($DB_site, $checkQteLigne[lignefactureid], '');
				$DB_site->query("UPDATE lignefacture SET factureid='$newFactureid', qte = '".$qte[$checkQteLigne['lignefactureid']]."', prix = prix * -1, prixachat = prixachat * -1, prixht = prixht * -1, prixbrut = prixbrut * -1, avoir_lignefactureidparent = '$checkQteLigne[lignefactureid]' WHERE lignefactureid = '$newlignefactureid'");					
			}			
		}

		// Frais de port		
		$totauxpoids = $DB_site->query_first("SELECT SUM(poids*qte) AS poids FROM lignefacture WHERE factureid = '$factureid'");
		$totauxpoidsNew = $DB_site->query_first("SELECT SUM(poids*qte) AS poids FROM lignefacture WHERE factureid = '$newFactureid'");
		
		// Remise commerciale
		$DB_site->query("UPDATE facture SET montantRemiseCommerciale = '$remiseCommerciale',supplement_livraison ='-$supplementLivraison' WHERE factureid = '$newFactureid'");
		
		$newMontants = calculerTotalFacture($DB_site, $newFactureid) ;
		$ratioPrix = ($newMontants[sousTotalTTC] + $newMontants[montantCadeauTTC]) / ($montants[sousTotalTTC] + $montants[montantCadeauTTC]);
		
		//Verifier le type de cadeau :
		if(!empty($facture['cadeauid'])){				
			$promotionSurPremier=$promotionSurDeuxieme=false;
			$articles1[artid]=$articles1[catid]=$articles2[artid]=$articles2[catid]=array();
			
			$articles1_tmp=$DB_site->query("SELECT catid,artid FROM lignefacture WHERE factureid = '$factureid' ");
			while($article1_tmp = $DB_site->fetch_array($articles1_tmp)){
				array_push($articles1[artid],$article1_tmp[artid]);
				array_push($articles1[catid],$article1_tmp[catid]);
			}			
			
			$articles2_tmp=$DB_site->query("SELECT catid,artid FROM lignefacture WHERE factureid = '$newFactureid' ");
			while($article2_tmp = $DB_site->fetch_array($articles2_tmp)){
				array_push($articles2[artid],$article2_tmp[artid]);
				array_push($articles2[catid],$article2_tmp[catid]);
			}		
		
			$cadeauRayon=$DB_site->query_first("SELECT catid, cadeauid FROM categorie_cadeau WHERE cadeauid = '$facture[cadeauid]'");
			$cadeauArticle=$DB_site->query_first("SELECT artid, cadeauid FROM article_cadeau WHERE cadeauid = '$facture[cadeauid]'");
			$lignesfacture = $DB_site->query("SELECT lf.artid, lf.catid FROM lignefacture lf WHERE lf.factureid = '$factureid' OR factureid = '$newFactureid' ORDER BY prix$devise DESC") ;
			// cadeau sur un rayon
			if ($cadeauRayon[cadeauid] == $facture[cadeauid]){
				$cats_enfant = array();
				$catEnCours = $cadeauRayon[catid];
				catid_enfants($DB_site, $catEnCours);
				$cats_enfant[$catEnCours] = $catEnCours;
				while ($lignefacture=$DB_site->fetch_array($lignesfacture)){
					if (in_array ($lignefacture[catid], $cats_enfant)){						
						if(in_array($listeRayonPanier[catid],$articles1[catid])) $promotionSurPremier = true;
						if(in_array($listeRayonPanier[catid],$articles2[catid])) $promotionSurDeuxieme = true;															
					}
				}				
			}
			if ($cadeauArticle[cadeauid] == $facture[cadeauid] && !($promotionSurPremier && $promotionSurDeuxieme)){// cadeau sur un article				
				/* Liste des articles associés au cadeau */
				$tabartids = array();
				$cadeauArticlesSecu=$DB_site->query("SELECT artid FROM article_cadeau WHERE cadeauid = '$facture[cadeauid]'");				
				while ($cadeauArticleSecu=$DB_site->fetch_array($cadeauArticlesSecu)){					
					$tabartids[$cadeauArticleSecu[artid]] = $cadeauArticleSecu[artid] ;
				}					
				/* Parmis tous les articles des factures, vérifier que l'articles appartient aux articles du cadeau */
				while ($listeArticleFacture=$DB_site->fetch_array($lignesfacture)){
					if (in_array ($listeArticleFacture[artid], $tabartids)){														
						if(in_array($listeArticleFacture[artid],$articles1[artid])) $promotionSurPremier = true;
						if(in_array($listeArticleFacture[artid],$articles2[artid])) $promotionSurDeuxieme = true;
						if($promotionSurPremier or $promotionSurDeuxieme) {
							break;
						}							
					}
				}
					
			}
			if(!($promotionSurPremier && $promotionSurDeuxieme)){
				$promotionSurPremier=true;
				$promotionSurDeuxieme=true;
			}	
			/**
			 * Si promotionSurPremier et non promotionSurDeuxieme la promotion ne s'applique que sur le factureid
			 * Si promotionSurDeuxieme et non promotionSurPremier la promotion ne s'applique que sur le newfactureid
			 * Si promotionSurPremier et promotionSurDeuxieme la promotion s'applique sur les deux. Dans ce cas on utilise ratioPrix
			 **/
			if($promotionSurPremier && !$promotionSurDeuxieme) {
				$montantCadeau = 0;	
			} elseif(!$promotionSurPremier && $promotionSurDeuxieme) {
				$montantCadeau = $facture['montantcadeau'.$devise];
			} elseif($promotionSurPremier && $promotionSurDeuxieme) {
				// je passe là
				$montantCadeau = ($facture['montantcadeau'.$devise]*$ratioPrix);			
			}
					
			
		}
		$montantCadeau = formaterPrix($montantCadeau, 2,'.','');
		
		$ratioMontantPort = $totauxpoidsNew[poids] / $totauxpoids[poids];
		$newMontantPort = $fraisportOk?$facture[montantport] * $ratioMontantPort:0;

		$ratioPrix = $ratioPrix * (-1);
		$DB_site->query("UPDATE facture SET
		montantport = '-$newMontantPort',
		montantcadeau = '".$montantCadeau."',
		prix_contre_remboursement = '".($facture['prix_contre_remboursement']*$ratioPrix)."',
		montantreductionfidelite = '".($facture['montantreductionfidelite']*$ratioPrix)."',
		montantBonAchat = '".($facture['montantBonAchat']*$ratioPrix)."',
		montantmaxicheque = '".($facture['montantmaxicheque']*$ratioPrix)."'
		WHERE factureid='$newFactureid'");

		
		// Totaux 
		$newMontants = calculerTotalFacture($DB_site, $newFactureid) ;
		if (!empty($lignesfacture))	{
			//$DB_site->query("UPDATE lignefacture SET factureid = '$factureid' WHERE lignefactureid IN (".$articlesLignesFacture.") ");
			
		}
		$DB_site->query("UPDATE facture SET montanttotal_ttc = '$newMontants[totalTTC]', montanttotal_ht = '$newMontants[totalHT]', montanttotal_horsfraisport_ttc = '$newMontants[sousTotalTTC]', montanttotal_horsfraisport_ht = '$newMontants[sousTotalHT]' WHERE factureid = '$newFactureid'") ;
	} 
	
	// Totaux 
	$newMontants = calculerTotalFacture($DB_site, $newFactureid) ;
	
	$affichageType = "TTC";			
	$montantCadeauType = 'montantCadeau'.$affichageType;
	$sousTotalType = 'sousTotal'.$affichageType;
	$totalType = 'total'.$affichageType;
	$montantPortType = 'montantPort'.$affichageType;
	$montantMaxicheque = 'montantMaxicheque'.$affichageType;
	$montantFidelite = 'montantFidelite'.$affichageType;
	$montantOperations = 'montantOperations'.$affichageType;
	$montantRemiseCommerciale = 'montantRemiseCommerciale'.$affichageType;
	$montantBonAchat = 'montantBonAchat'.$affichageType;
	
	//envoi JSON
	echo '{"resultat":"OK",
			"newFactureid": "'.$newFactureid.'",
			"lignesfactureid":"'.$articlesLignesFacture.'",
			"sousTotal":{"nouveau":"'.formaterPrix($newMontants[$sousTotalType]).'"},
			"montantPort":{"nouveau":"'.formaterPrix($newMontants[$montantPortType]).'"},
			"montantCadeau":{"nouveau":"'.formaterPrix($newMontants[$montantCadeauType]).'"},
			"total":{"nouveau":"'.formaterPrix($newMontants[$totalType]).'"},
			"montantTVA":{"nouveau":"'.formaterPrix($newMontants["montantTVA"]).'"}
			}';		
	
}
?>