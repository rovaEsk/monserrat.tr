<?php
	include "includes/header.php";
	
	$scriptcourant = "commandes.php";

	if(isset($action) && $action == "urlTransp"){
		$url = $DB_site->query_first("SELECT URL FROM mode_livraison WHERE modelivraisonid = '$modelivraisonid'");
		echo $url[URL];
	}
	
	if(isset($action) && $action == "supprCode"){
		if($admin_droit[$scriptcourant][suppression]){
			$DB_site->query("UPDATE facture SET cadeauid = '0', montantcadeau = '0' WHERE factureid = '$factureid'");
			$montants = calculerTotalFacture($DB_site, $factureid) ;
			$DB_site->query("UPDATE facture SET montanttotal_ttc = '$montants[totalTTC]', montanttotal_ht = '$montants[totalHT]', montanttotal_horsfraisport_ttc = '$montants[sousTotalTTC]', montanttotal_horsfraisport_ht = '$montants[sousTotalHT]' WHERE factureid = '$factureid'") ;
			echo formaterPrix($montants[totalTTC]);
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
	
	if (isset($action) && $action == "updateRemiseCommerciale") {
		if($admin_droit[$scriptcourant][ecriture]){
			if (is_numeric($remisecommerciale) && $remisecommerciale >= 0) {
				if (!isset($deviseid) || $deviseid == "1") {
					$facture = $DB_site->query_first("SELECT montanttotal_ttc, montantRemiseCommerciale FROM facture WHERE factureid = '$factureid'");
					$montanttotal_ttcN = $facture[montanttotal_ttc] + $facture[montantRemiseCommerciale] - $remisecommerciale;
					if ($montanttotal_ttcN >= 0) {
						$DB_site->query("UPDATE facture SET montantRemiseCommerciale = '$remisecommerciale' WHERE factureid = '$factureid'");
						$montants = calculerTotalFacture($DB_site, $factureid) ;
						$DB_site->query("UPDATE facture SET montanttotal_ttc = '$montants[totalTTC]', montanttotal_ht = '$montants[totalHT]', montanttotal_horsfraisport_ttc = '$montants[sousTotalTTC]', montanttotal_horsfraisport_ht = '$montants[sousTotalHT]' WHERE factureid = '$factureid'") ;
						$montanttotal_ttcNE = formaterPrix($montanttotal_ttcN) ;
					} else {
						$DB_site->query("UPDATE facture SET montantRemiseCommerciale = '$remisecommerciale' WHERE factureid = '$factureid'");
						$montants = calculerTotalFacture($DB_site, $factureid) ;
						$DB_site->query("UPDATE facture SET montanttotal_ttc = '$montants[totalTTC]', montanttotal_ht = '$montants[totalHT]', montanttotal_horsfraisport_ttc = '$montants[sousTotalTTC]', montanttotal_horsfraisport_ht = '$montants[sousTotalHT]' WHERE factureid = '$factureid'") ;
						$montanttotal_ttcNE = formaterPrix($montanttotal_ttcN*-1) ;
					}
				}
			}
			
			genererPdfCommande($DB_site, $factureid, "admin");
			echo  $montanttotal_ttcNE;
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
	
	if (isset($action) && $action == "ajouterBonReduction") {
		if ($codereduction != "") {
			if($admin_droit[$scriptcourant][ecriture]){
				$facture = $DB_site->query_first("SELECT * FROM facture WHERE factureid = '$factureid'");
				$cadeau = $DB_site->query_first("SELECT * FROM cadeau WHERE code = '$codereduction' AND siteid = '$facture[siteid]'");
				
				if($cadeau[siteid] == $facture[siteid]){
					$applicationCadeau = "0";
					$prixreduction = 0;
					$montantFactureAcontroler = $facture[montanttotal_horsfraisport_ttc] ;

					if ($cadeau[cadeauid]) {
						$cadeauRayon = $DB_site->query_first("SELECT catid, cadeauid FROM categorie_cadeau WHERE cadeauid = '$cadeau[cadeauid]'");
						$cadeauArticle = $DB_site->query_first("SELECT artid, cadeauid FROM article_cadeau WHERE cadeauid = '$cadeau[cadeauid]'");
						$articlesDansFacture = $DB_site->query("SELECT artid, catid FROM lignefacture WHERE factureid = '$factureid'") ;
						if ($cadeauRayon[cadeauid] == $cadeau[cadeauid]) {
							$applicationCadeau = "1";
						} else {
							$tabartids = array() ;
							while ($articleDansFacture=$DB_site->fetch_array($articlesDansFacture)){
								$artEnCours = $articleDansFacture[artid] ;
								$tabartids[$artEnCours] = $artEnCours ;
							}
							$cadeauArticlesSecu = $DB_site->query("SELECT artid FROM article_cadeau WHERE cadeauid = '$cadeau[cadeauid]'");
							while ($cadeauArticleSecu=$DB_site->fetch_array($cadeauArticlesSecu)){
								if (in_array ($cadeauArticleSecu[artid], $tabartids)){
									$applicationCadeau = "2" ;
								}
							}
						}
						if (!in_array("5831",$modules) || $cadeau[deviseid] == "1"){
							$montantReductionCadeau = $cadeau[valeurcadeau];
							$montantPort = $facture[montantport];
							$prix = "prix";
						}
						switch ($applicationCadeau){
							case "0": // sur tout le catalogue
								switch ($cadeau[typecadeauid]){
									case "0": // Montant de réduction
										if( $cadeau[chequecadeau] == 1) { // si c'est un cheque cadeau
											$prixreduction += $montantReductionCadeau;
										} else{
											if ($montantReductionCadeau < $montantFactureAcontroler ){
												$prixreduction += $montantReductionCadeau;
											}else{
												$prixreduction += $montantFactureAcontroler;
											}
										}
										break ;
									case "1": // Pourcentage de réduction
										$prixreduction += ($montantFactureAcontroler * $montantReductionCadeau / 100) ;
										break;
									case "2": // Annule les frais de port
										$prixreduction = $montantPort;
										break;
									case "3": // Montant de réduction + Annule les frais de port
										if ($montantReductionCadeau < $montantFactureAcontroler){
											$prixreduction += $montantReductionCadeau;
										}else{
											$prixreduction += $montantFactureAcontroler;
										}
										$prixreduction += $montantPort;
										break ;
								}
								break;
							case "1": // sur un rayon
								$tabLignesFactureUtilisees = array();
								$cadeauRayon = $DB_site->query_first("SELECT catid, cadeauid FROM categorie_cadeau WHERE cadeauid = '$cadeau[cadeauid]'");
								$listeRayonsFacture = $DB_site->query("SELECT artid, catid, $prix, qte, lignefactureid FROM lignefacture WHERE factureid = '$factureid'") ;
								$cats_enfant = array();
								$catEnCours = $cadeauRayon[catid];
								catid_enfants($DB_site, $catEnCours);
								$cats_enfant[$catEnCours] = $catEnCours;
								while ($listeRayonFacture=$DB_site->fetch_array($listeRayonsFacture)){
									if (in_array ($listeRayonFacture[catid], $cats_enfant) && !in_array ($listeRayonFacture[lignefactureid], $tabLignesFactureUtilisees)){
										$prixLigne = $listeRayonFacture[$prix] * $listeRayonFacture[qte];
										switch ($cadeau[typecadeauid]){
											case "0": // Montant de réduction
												if ($montantReductionCadeau < $prixLigne){
													if ($montantReductionCadeau > $prixreduction)
														$prixreduction += $montantReductionCadeau ;
												}else{
													if ($prixLigne > $prixreduction)
														$prixreduction += $prixLigne ;
												}
												break ;
											case "1": // Pourcentage de réduction
												$prixreduction += ($prixLigne * $montantReductionCadeau / 100);
												break;
											case "2": // Annule les frais de port
												$prixreduction = $montantPort;
												break;
											case "3": // Montant de réduction + Annule les frais de port
												if ($montantReductionCadeau < $prixLigne){
													if ($montantReductionCadeau > $prixreduction)
														$prixreduction += $montantReductionCadeau ;
												}else{
													if ($prixLigne > $prixreduction)
														$prixreduction += $prixLigne ;
												}
												$prixreduction += $montantPort;
												break ;
										}
										array_push($tabLignesFactureUtilisees, $listeRayonFacture[lignefactureid]);
									}
								}
								break;
							case "2": // sur un article
								$cadeauArticle = $DB_site->query_first("SELECT artid, cadeauid FROM article_cadeau WHERE cadeauid = '$cadeau[cadeauid]'");
								$tabartids = array();
								$cadeauArticlesSecu=$DB_site->query("SELECT artid FROM article_cadeau WHERE cadeauid = '$cadeau[cadeauid]'");
								while ($cadeauArticleSecu=$DB_site->fetch_array($cadeauArticlesSecu)){
									$artidSecu = $cadeauArticleSecu[artid];
									$tabartids[$artidSecu] = $artidSecu ;
								}
									
								$listeArticlesFacture = $DB_site->query("SELECT artid, catid, $prix, qte, lignefactureid FROM lignefacture WHERE factureid = '$factureid' ORDER BY $prix DESC") ;
								while ($listeArticleFacture=$DB_site->fetch_array($listeArticlesFacture)){
									if (in_array ($listeArticleFacture[artid], $tabartids)){
										$breakWhile = 0;
										$prixLigne = $listeArticleFacture[$prix] * $listeArticleFacture[qte];
										switch ($cadeau[typecadeauid]){
											case "0": // Montant de réduction
												if ($montantReductionCadeau < $prixLigne){
													if ($montantReductionCadeau > $prixreduction){
														$prixreduction += $montantReductionCadeau ;
													}
												}else{
													if ($prixLigne > $prixreduction){
														$prixreduction += $prixLigne ;
													}
												}
												$breakWhile = 1;
												break ;
											case "1": // Pourcentage de réduction
												$prixreduction += ($prixLigne * $montantReductionCadeau / 100) ;
												break;
											case "2": // Annule les frais de port
												$prixreduction = $montantPort;
												break;
											case "3": // Montant de réduction + Annule les frais de port
												if ($montantReductionCadeau < $prixLigne){
													if ($montantReductionCadeau > $prixreduction){
														$prixreduction += $montantReductionCadeau ;
													}
												}else{
													if ($prixLigne > $prixreduction){
														$prixreduction += $prixLigne ;
													}
												}
												$prixreduction += $montantPort;
												break ;
										}
										if($breakWhile == 1) {
											break;
										}
									}
								}
								break;
						}
						
						$DB_site->query("UPDATE facture SET cadeauid = '$cadeau[cadeauid]', montantcadeau = '$prixreduction' WHERE factureid = '$factureid'");
						if ($prixreduction < 0){
							$DB_site->query("UPDATE facture SET cadeauid = '0', montantcadeau = '0' WHERE factureid = '$factureid'");
						}
						$montants = calculerTotalFacture($DB_site, $factureid) ;
						$DB_site->query("UPDATE facture SET montanttotal_ttc = '$montants[totalTTC]', montanttotal_ht = '$montants[totalHT]', montanttotal_horsfraisport_ttc = '$montants[sousTotalTTC]', montanttotal_horsfraisport_ht = '$montants[sousTotalHT]' WHERE factureid = '$factureid'") ;
						echo formaterPrix($montants[totalTTC])."|$codereduction|$prixreduction";
					}
				}else{
					echo "erreur siteid";	
				}
			}else{
				header("HTTP/1.1 503 $multilangue[action_page_refuse]");
				exit;
			}
		}else{
			echo "pas de code de réduction";	
		}
	}
	
	if(isset($action) && $action == "updateFraisPort"){
		if($admin_droit[$scriptcourant][ecriture]){
			$fp = corrigerPrixVirgule($fraisPort);
			$facture=$DB_site->query_first("SELECT montantport, tvaport, montanttotal_ht, montanttotal_ttc FROM facture WHERE factureid = '$factureid'");
			$montanttotal_htN = $facture[montanttotal_ht] - ($facture[montantport] /(1+$facture[tvaport]/100)) + ($fp /(1+$facture[tvaport]/100));
			$montanttotal_ttcN = $facture[montanttotal_ttc] - $facture[montantport] + $fp;
			$DB_site->query("UPDATE facture SET montantport = '$fp', montanttotal_ht = '$montanttotal_htN', montanttotal_ttc = '$montanttotal_ttcN ' WHERE factureid = '$factureid'");
			echo formaterPrix($montanttotal_ttcN);
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
	
	if(isset($action) && $action == "modifChamp"){
		if($admin_droit[$scriptcourant][ecriture]){
			$lignefacture = $DB_site->query_first("SELECT tva FROM lignefacture WHERE lignefactureid = '$lignefactureid'");
			$prix = corrigerPrixVirgule($prix);
			$prixht = calculerLignePrixHT($prix, $lignefacture[tva]);
			$DB_site->query("UPDATE lignefacture SET prix = '$prix', prixht = '$prixht' WHERE lignefactureid = '$lignefactureid'");	
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
	
	if(isset($action) && $action == "modifLigne"){
		if($admin_droit[$scriptcourant][ecriture]){
			$update = "" ;
			$facture = $DB_site->query_first("SELECT * FROM facture WHERE factureid = '$factureid'");
			$article = $DB_site->query_first("SELECT * FROM article a INNER JOIN lignefacture lf USING (artid) WHERE lf.lignefactureid = '$lignefactureid'");
			$DB_site->query("DELETE FROM lignefacturechamp WHERE lignefactureid = '$lignefactureid'");
			
			$tabcaractvalids = array() ;
			$tabpersos = array() ;
			foreach ($_POST as $key => $value) {
				$caract = explode('t', $key);
				if ($caract[0] == "carac") {
					array_push($tabcaractvalids, $value) ;
				}elseif (substr($key, 0, 5) == "perso") {
					$tabpersos[substr($key, 5, strlen($key))] = $value;
				}
			}
			
			//Personnalisations
			foreach ($tabpersos as $key => $value){
				if ($value){
					$perso = $DB_site->query_first("SELECT * FROM article_champ INNER JOIN article_champ_site USING(articlechampid) WHERE articlechampid = '$key' AND siteid = '$facture[siteid]'");
					if($perso[type] == "1" || $perso[type] == "2" || $perso[type] == "4" || $perso[type] == "5"){
						$DB_site->query("INSERT INTO lignefacturechamp (lignefactureid, type, libelle, valeur, articlechampid) VALUES ('$lignefactureid','$perso[type]','$perso[$libelle]','$value','$key')");
					}else if($perso[type] == "3" || $perso[type] == "6"){
						$tabvaleurs = explode(',', $value);
						foreach ($tabvaleurs as $valeur){
							$DB_site->query("INSERT INTO lignefacturechamp (lignefactureid, type, libelle, valeur, articlechampid) VALUES ('$lignefactureid','$perso[type]','$perso[$libelle]','$valeur','$key')");
						}
					}else{
						$typefileperso = substr ($value, strlen ($value) - 3);
						$value = $lignefactureid."_".$key.".".$typefileperso;
						$DB_site->query("INSERT INTO lignefacturechamp (lignefactureid, type, libelle, valeur, articlechampid) VALUES ('$lignefactureid','$perso[type]','$perso[$libelle]','$value','$key')");
					}

					if ($perso[prixperso]){
						$prixperso = $DB_site->query_first("SELECT lf_prixperso FROM lignefacture WHERE lignefactureid='$lignefactureid'");
						$prixperso = $prixperso[lf_prixperso] + $perso[prixperso];
						$DB_site->query("UPDATE lignefacture SET lf_prixperso = '$prixperso' WHERE lignefactureid='$lignefactureid'");
					}
				}
			}
			
					
			if (count($tabcaractvalids) > 0){
				$resultget = retournerStockArticle($DB_site, $article[artid], $tabcaractvalids) ;
			} else {
				$resultget = retournerStockArticle($DB_site, $article[artid]) ;
			}
			// erreur stock
			$gestionStock = $DB_site->query_first("SELECT stock_illimite FROM article WHERE artid = '$article[artid]'");
			if (!in_array(4, $modules) || (in_array(4, $modules) && $gestionStock[stock_illimite]) || $qte <= $resultget) {
				$prix = corrigerPrixVirgule($prix);
				if(in_array(5937, $modules) && $article[prixaumetre]){
					$qte = corrigerPrixVirgule($qte);
					$nbcm = ", nbcm = '$qte'";
					$qte = 1;
					$prix = $prix*100/$qte;
				}else{
					$prix = $prix / $qte;
					$nbcm = "";
				}
				
				$DB_site->query("UPDATE lignefacture SET qte = '$qte', prix = '$prix' $nbcm WHERE lignefactureid = '$lignefactureid'");
				$tabanciennes = array();
				$anciennes_caractvalids=$DB_site->query("SELECT caractvalid FROM lignefacturecaracteristique WHERE lignefactureid = '$lignefactureid'");
				while ($ancienne_caractvalid=$DB_site->fetch_array($anciennes_caractvalids))
					array_push($tabanciennes, $ancienne_caractvalid[caractvalid]) ;
			
				sort($tabanciennes) ;
				sort($tabcaractvalids) ;
			
				if (in_array(4, $modules) && $article[typearticle] == "1" && !$gestionStock[stock_illimite]){
					$moyenpaiement=$DB_site->query_first("SELECT decrementer FROM moyenpaiement WHERE moyenid = '$facture[moyenid]'");
					// 0 passage commande : dans tous les cas, 1 validation : si etat 0 ou 1, 2 expédition : jamais
					if (($moyenpaiement[decrementer] == 0) || ($moyenpaiement[decrementer] == 1 && $facture[etatid] <= 1)) {
						if ($tabanciennes != $tabcaractvalids) {
							// réincrémente les stocks des anciennes caracteristiques
							decrementerStock($DB_site, $article[artid], retournerStockid($DB_site, $article[artid], $tabanciennes), $article[qte]);
							// décrémente les stocks des nouvelles caractéristiques
							decrementerStock($DB_site, $article[artid], retournerStockid($DB_site, $article[artid], $tabcaractvalids), $qte*(-1));
						}
						else
							decrementerStock($DB_site, $article[artid], retournerStockid($DB_site, $article[artid], $tabcaractvalids), ($qte-$article[qte])*(-1));
					}
				}
				
				foreach ($_POST as $key => $value) {
					$caract = explode('t', $key);
					if ($caract[0] == "carac") {
						
						$caractid= str_replace("-","",$caract[1]);
						
						$caracteristiquevaleur=$DB_site->query_first("SELECT * FROM caracteristiquevaleur 
																		INNER JOIN caracteristiquevaleur_site USING (caractvalid) 
																		WHERE caractvalid = '$value' AND siteid = '$facture[siteid]'");
						$DB_site->query("UPDATE lignefacturecaracteristique SET caractvalid = '$value', libcaractval = '".addslashes($caracteristiquevaleur[libelle])."' WHERE lignefactureid = '$lignefactureid' AND caractid = '$caractid'");
					}
				}
			
					
				$lignefacture = $DB_site->query_first("SELECT * FROM lignefacture WHERE lignefactureid = '$lignefactureid'");
				$prix = $lignefacture[prix] * $lignefacture[qte] ;
			
				$mtt_fraisport = calculerFraisPort($DB_site, $factureid) ;
				$DB_site->query("UPDATE facture SET montantport = '$mtt_fraisport' WHERE factureid = '$factureid'") ;
			
				$montants = calculerTotalFacture($DB_site, $factureid) ;
				$DB_site->query("UPDATE facture SET montanttotal_ttc = '$montants[totalTTC]', montanttotal_ht = '$montants[totalHT]', montanttotal_horsfraisport_ttc = '$montants[sousTotalTTC]', montanttotal_horsfraisport_ht = '$montants[sousTotalHT]' WHERE factureid = '$article[factureid]'") ;
					
				$prixE = formaterPrix($prix) ;
				$mtt_fraisportE = formaterPrix($mtt_fraisport) ;
			}
				
			if (in_array(5804, $modules))
				$DB_site->query("UPDATE lignefacture SET serialnumber = '".addslashes($numSerie)."' WHERE lignefactureid = '$lignefactureid'");
			genererPdfCommande($DB_site, $article[factureid], "admin");
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
	
	if (isset($action) && $action == "supprimerLigne") {
		if($admin_droit[$scriptcourant][suppression]){
			$article=$DB_site->query_first("SELECT * FROM article a INNER JOIN lignefacture lf USING (artid) WHERE lignefactureid = '$lignefactureid'");
			$tabanciennes = array();
			$anciennes_caractvalids=$DB_site->query("SELECT caractvalid FROM lignefacturecaracteristique WHERE lignefactureid = '$lignefactureid'");
			while ($ancienne_caractvalid=$DB_site->fetch_array($anciennes_caractvalids)) {
				array_push($tabanciennes, $ancienne_caractvalid[caractvalid]) ;
			}
		
			$facture=$DB_site->query_first("SELECT * FROM facture WHERE factureid = '$article[factureid]'");
			if (in_array(4, $modules) && $article[typearticle] == "1" && $facture[decrementation] == "1" && !$article[stock_illimite]) {
				$moyenpaiement=$DB_site->query_first("SELECT decrementer FROM moyenpaiement WHERE moyenid = '$facture[moyenid]'");
				// 0 passage commande : dans tous les cas, 1 validation : si etat 0 ou 1, 2 expédition : jamais
				if (($moyenpaiement[decrementer] == 0) || ($moyenpaiement[decrementer] == 1 && $facture[etatid] <= 1)) {
					// réincrémente les stocks des anciennes caracteristiques
					decrementerStock($DB_site, $article[artid], retournerStockid($DB_site, $article[artid], $tabanciennes), $article[qte]);
				}
			}
		
			// module articles et services complémentaires
			$liste_lignesenfants = "0";
			if (in_array(5807, $modules)) {
				$enfants = $DB_site->query("SELECT * FROM article a INNER JOIN lignefacture lf USING (artid) WHERE lignefactureidparent = '$lignefactureid' AND factureid = '$article[factureid]'");
				while ($enfant=$DB_site->fetch_array($enfants)) {
					$tabanciennes = array();
					$anciennes_caractvalids=$DB_site->query("SELECT caractvalid FROM lignefacturecaracteristique WHERE lignefactureid = '$enfant[lignefactureid]'");
					while ($ancienne_caractvalid=$DB_site->fetch_array($anciennes_caractvalids))
						array_push($tabanciennes, $ancienne_caractvalid[caractvalid]) ;
		
					$facture=$DB_site->query_first("SELECT * FROM facture WHERE factureid = '$enfant[factureid]'");
					if (in_array(4, $modules) && $article[typearticle] == "1" && !$article[stock_illimite]) {
						$moyenpaiement=$DB_site->query_first("SELECT decrementer FROM moyenpaiement WHERE moyenid = '$facture[moyenid]'");
						// 0 passage commande : dans tous les cas, 1 validation : si etat 0 ou 1, 2 expédition : jamais
						if (($moyenpaiement[decrementer] == 0) || ($moyenpaiement[decrementer] == 1 && $facture[etatid] <= 1)) {
							// réincrémente les stocks des anciennes caracteristiques
							decrementerStock($DB_site, $enfant[artid], retournerStockid($DB_site, $enfant[artid], $tabanciennes), $article[qte]);
						}
					}
					
					$DB_site->query("UPDATE lignefacture SET deleted = '1' WHERE lignefactureid = '$enfant[lignefactureid]'");
				}
			}
		
			$DB_site->query("UPDATE lignefacture SET deleted = '1' WHERE lignefactureid = '$lignefactureid'");
		
			$mtt_fraisport = calculerFraisPort($DB_site, $article[factureid]) ;
			$DB_site->query("UPDATE facture SET montantport = '$mtt_fraisport' WHERE factureid = '$article[factureid]'") ;
			$montants = calculerTotalFacture($DB_site, $article[factureid]) ;
			$DB_site->query("UPDATE facture SET montanttotal_ttc = '$montants[totalTTC]', montanttotal_ht = '$montants[totalHT]', montanttotal_horsfraisport_ttc = '$montants[sousTotalTTC]', montanttotal_horsfraisport_ht = '$montants[sousTotalHT]' WHERE factureid = '$article[factureid]'") ;
			$mtt_fraisportE = formaterPrix($mtt_fraisport) ;
			genererPdfCommande($DB_site, $article[factureid], "admin");
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
		
	if (isset($action) && $action == "supprimerLignechamp") {
		if($admin_droit[$scriptcourant][suppression]){
			$lignefacture = $DB_site->query_first("SELECT * FROM lignefacture WHERE lignefactureid = '$lignefactureid'");
			$facture = $DB_site->query_first("SELECT * FROM facture WHERE factureid = '$lignefacture[factureid]'");
			$DB_site->query("UPDATE lignefacture SET deleted = '1' WHERE lignefactureid = '$lignefactureid'");
		
			$montants = calculerTotalFacture($DB_site, $factureid) ;
			$mtt_fraisport = calculerFraisPort($DB_site, $factureid) ;
			$DB_site->query("UPDATE facture SET montanttotal_ttc = '$montants[totalTTC]', montanttotal_ht = '$montants[totalHT]', montanttotal_horsfraisport_ttc = '$montants[sousTotalTTC]', montanttotal_horsfraisport_ht = '$montants[sousTotalHT]', montantport = '$mtt_fraisport' WHERE factureid = '$factureid'") ;
		
			$mtt_fraisportE = formaterPrix($mtt_fraisport) ;
		
			genererPdfCommande($DB_site, $factureid, "admin");
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
		
	if (isset($action) && $action == "ajoutChamp") {
		if($admin_droit[$scriptcourant][ecriture]){
			$mtt = corrigerPrixVirgule($mtt);
			$facture=$DB_site->query_first("SELECT * FROM facture WHERE factureid = '$factureid'");
			$devise_actuelle = $tabsites[$facture[siteid]][devise_complete];
			$tva_user = $DB_site->query_first("SELECT TVAtauxnormal FROM pays WHERE paysid = (SELECT lpaysid FROM facture WHERE factureid = '$factureid')");
			$tva = $tva_user[TVAtauxnormal] ;
			$prixht = calculerLignePrixHT($mtt, $tva);
			$DB_site->query("INSERT INTO lignefacture (factureid, artcode, qte, libelle, prix, prixht, tva) VALUES ('$factureid', '$artcode', '1', '".addslashes($lib)."', '$mtt','$prixht', '$tva')");
			$lignefactureid = $DB_site->insert_id();

			$montants = calculerTotalFacture($DB_site, $factureid) ;
			$mtt_fraisport = calculerFraisPort($DB_site, $factureid) ;
			$montants[totalHT] = round($montants[totalTTC]/$tva);
			$montants[sousTotalHT] = round($montants[sousTotalTTC]/$tva);
			$DB_site->query("UPDATE facture SET montanttotal_ttc = '$montants[totalTTC]', montanttotal_ht = '$montants[totalHT]', montanttotal_horsfraisport_ttc = '$montants[sousTotalTTC]', montanttotal_horsfraisport_ht = '$montants[sousTotalHT]', montantport = '$mtt_fraisport' WHERE factureid = '$factureid'") ;
			$mttE = formaterPrix($mtt) ;
			$mtt_fraisportE = formaterPrix($mtt_fraisport) ;

			genererPdfCommande($DB_site, $factureid, "admin");
			
			$nouvelLigne = "<tr class='ta-center' id='$lignefactureid'>";
			$nouvelLigne .= "<td></td>";
			$nouvelLigne .= "<td></td>";
			$nouvelLigne .= "<td><label>$artcode</label></td>";
			$nouvelLigne .= "<td><label>$lib</label></td>";
			$nouvelLigne .= "<td></td>";
			$nouvelLigne .= "<td><label>1</label></td>";
			$nouvelLigne .= "<td><label>0,00 $devise_actuelle</label></td>";
			$nouvelLigne .= "<td><input type='text' class='form-control ta-center' value='$mtt' style='display: inline; width: 80px;'> $devise_actuelle</td>";
			$nouvelLigne .= "<td>
								<a class='btn blue btn_enregistrer_modif_champ' >$multilangue[enregistrer_diminutif]</a><br><br>
								<a href='#myModalSupprimerLigne$lignefactureid' class='btn blue' data-placement='top' data-toggle='modal' role='button' id='btn_remboursement_produit'>$multilangue[supprimer_diminutif]</a>
									<div aria-hidden='true' aria-labelledby='myModalLabel' role='dialog' tabindex='-1' class='modal fade' id='myModalSupprimerLigne$lignefactureid' style='display: none;'>
										<div class='modal-dialog'>
											<div class='modal-content'>
												<div class='modal-header'>
													<button aria-hidden='true' data-dismiss='modal' class='close' type='button'></button>
													<h4 class='modal-title'>$multilangue[suppr_article]</h4>
												</div>
												<div class='modal-body ta-center'>
													$multilangue[suppression_ligne_facture]
												</div>
												<div class='modal-footer'>
													<button aria-hidden='true' data-dismiss='modal' class='btn default'>$multilangue[retour]</button>
													<button class='btn blue' data-dismiss='modal' id='btn_supprimer_ligne' onclick='supprimerLigneChamp($lignefactureid)'>$multilangue[supprimer]</button>
												</div>
											</div>
										</div>
									</div>
							</td>";
			$nouvelLigne .= "</tr>";
			
			echo $nouvelLigne;
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
	
	if(isset($action) && $action == "ajoutArticle"){
		if($admin_droit[$scriptcourant][ecriture]){
			$facture = $DB_site->query_first("SELECT * FROM facture WHERE factureid = '$factureid'");
			$tabcaractvalids = array() ;
			foreach ($_POST as $key => $value) {
				if (substr($key, 0, 6) == "caract") {
					array_push($tabcaractvalids, $value) ;
				}
			}
			if (count($tabcaractvalids)) {
				$resultget = retournerStockArticle($DB_site, $artid, $tabcaractvalids);
			} else {
				$resultget = retournerStockArticle($DB_site, $artid);
			}
			$gestionStock = $DB_site->query_first("SELECT stock_illimite FROM article WHERE artid = '$artid'");
			if (!in_array(4, $modules) || (in_array(4, $modules) && $gestionStock[stock_illimite]) || $qte <= $resultget) {
				$count = $DB_site->query_first("SELECT COUNT(*) FROM lignefacture WHERE factureid = '$factureid'");
				
				$article = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING (artid) WHERE artid = '$artid' AND siteid = '$facture[siteid]'");
				
				
				switch($article[tauxchoisi]){
					case "1":
						$tva = $DB_site->query_first("SELECT TVAtauxnormal AS tva FROM pays WHERE paysid = '$facture[lpaysid]'");
						break;
					case "2":
						$tva = $DB_site->query_first("SELECT TVAtauxreduit AS tva FROM pays WHERE paysid = '$facture[lpaysid]'");
						break;
					case "3":
						$tva = $DB_site->query_first("SELECT TVAtauxintermediaire AS tva FROM pays WHERE paysid = '$facture[lpaysid]'");
						break;	
				}
				$prix = corrigerPrixVirgule($prix);
				
				$prixLigne = $prix / $qte;
				$prixHT = calculerLignePrixHT($prixLigne, $tva[tva]);
				
				$fournisseur=$DB_site->query_first("SELECT libelle,fournisseurid FROM fournisseur WHERE fournisseurid='$article[fournisseurid]'");
				$DB_site->query("INSERT INTO lignefacture (factureid, artid, catid, artcode, qte, delai, libelle, prix, prixht, prixbrut, ecotaxe, prixachat, tva, fournisseur, fournisseurid, qte_fournisseur) VALUES ('$factureid', '$artid', '$article[catid]', '$article[artcode]',  '$qte', '$article[delai]', '".addslashes($article[libelle])."', '$prixLigne', '$prixHT', '$article[prix]', '$article[ecotaxe]', '$article[prixachat]', '$tva[tva]', '".securiserSql($fournisseur[libelle])."', '$fournisseur[fournisseurid]', '$qte')");
				$lignefactureid = $DB_site->insert_id();
				
				foreach ($_POST as $key => $value) {
					$test_caracteristique = explode("-", $key);
					if ($test_caracteristique[0] == "caract_nouvel_article"){
						$libelles=$DB_site->query_first("SELECT cs.libelle AS libcaract, cvs.libelle AS libcaractval
															FROM caracteristique_site AS cs
															INNER JOIN caracteristiquevaleur AS cv ON cs.caractid = cv.caractid
															INNER JOIN caracteristiquevaleur_site AS cvs ON cv.caractvalid = cvs.caractvalid 
															WHERE cvs.caractvalid = '$value'");
						$DB_site->query("INSERT INTO lignefacturecaracteristique (lignefactureid, caractid, caractvalid, libcaract, libcaractval) VALUES ('$lignefactureid', '$test_caracteristique[1]', '$value', '".addslashes($libelles[libcaract])."', '".addslashes($libelles[libcaractval])."')");
					}
				}
			
				// Décrémentation des stocks
				if (in_array(4, $modules) && $article[typearticle] == "1" && $facture[decrementation] == "1" && !$gestionStock[stock_illimite]) {
					$moyenpaiement=$DB_site->query_first("SELECT decrementer FROM moyenpaiement WHERE moyenid = '$facture[moyenid]'");
					// 0 passage commande : dans tous les cas, 1 validation : si etat 0 ou 1, 2 expédition : jamais
					if (($moyenpaiement[decrementer] == 0) || ($moyenpaiement[decrementer] == 1 && $facture[etatid] <= 1)) {
						// réincrémente les stocks des anciennes caracteristiques
						$qteStock = $qte * -1;
						decrementerStock($DB_site, $article[artid], retournerStockid($DB_site, $article[artid], $tabcaractvalids), $qteStock);
					}
				}
			
				$lignefacture = $DB_site->query_first("SELECT * FROM lignefacture WHERE lignefactureid = '$lignefactureid'");
				$montants = calculerTotalFacture($DB_site, $factureid) ;
				$mtt_fraisport = calculerFraisPort($DB_site, $factureid) ;
				$montants[totalHT] =calculerLignePrixHT($montants[totalTTC], $tva[tva]);
				$montants[sousTotalHT] = calculerLignePrixHT($montants[sousTotalTTC], $tva[tva]);
				$DB_site->query("UPDATE facture SET montanttotal_ttc = '$montants[totalTTC]', montanttotal_ht = '$montants[totalHT]', montanttotal_horsfraisport_ttc = '$montants[sousTotalTTC]', montanttotal_horsfraisport_ht = '$montants[sousTotalHT]', montantport = '$mtt_fraisport' WHERE factureid = '$factureid'") ;
				$mttE = formaterPrix($mtt) ;
				$mtt_fraisportE = formaterPrix($mtt_fraisport) ;
				genererPdfCommande($DB_site, $factureid, "admin");

				$devise_actuelle = $tabsites[$facture[siteid]][devise_complete];
				
				$nouvelLigne = "<tr class='ta-center' id='$lignefactureid'>";
				$nouvelLigne .= "<td>
									<a href='' target='_blank' class='btn tooltips' ><i class='fa fa-search fs-18 font-blue'></i></a><br>
									<a href='produits.php?action=modifier&artid=$artid' target='_blank' class='btn tooltips' ><i class='fa fa-edit fs-18 font-blue'></i></a>
								</td>";
				$nouvelLigne .= "<td><img src='http://$host/br-a-$artid.jpg'></td>";
				$nouvelLigne .= "<td><label>$article[artcode]</label></td>";
				$nouvelLigne .= "<td><label>$article[libelle]</label><br>";
				if(in_array(5804, $modules)){
					$nouvelLigne .= "<label class='label-control'>$multilangue[numero_serie] :</label><input type='text' name='num_serie' value='' class='form-control'>";
				}
				if(in_array(5867, $modules)) {
					$multiMode = "";
					$verifMutltiMode = $DB_site->query_first("SELECT modelivraison_multi FROM facture WHERE factureid = '$factureid'");
					if($verifMutltiMode[modelivraison_multi] != 0) {
						$verifMutltiMode = $DB_site->query_first("SELECT modelivraison_multi_article FROM lignefacture WHERE lignefactureid = '$lignefactureid'");
						if($verifMutltiMode[modelivraison_multi_article] != 0) {
							$modesBis = $DB_site->query("SELECT nom FROM mode_livraison_site WHERE modelivraisonid IN ($verifMutltiMode[modelivraison_multi_article])");
							$multiMode .= "<br>$multilangue[mode_de_livraison] : ";
							$separateur  = "";
							while ($modeBis=$DB_site->fetch_array($modesBis)) {
								$multiMode .= $separateur."<i>".$modeBis[$nom]."</i>";
								$separateur = " $multilangue[ou] ";
							}
						}
					}
					$nouvelLigne .= "<label>$multiMode</label>";
					
				}
				if(in_array(113, $modules)){
					$champs = $DB_site->query("SELECT * FROM article_champ INNER JOIN article_champ_site USING (articlechampid) WHERE artid = '$lignefacture[artid]' AND siteid = '$facture[siteid]'");
					while($champ = $DB_site->fetch_array($champs)){
						$displayInputFile = "display: none";
						$champForm = "";
						$valeurs = $DB_site->query("SELECT * FROM article_champ_valeur INNER JOIN article_champ_valeur_site USING (articlechampvaleurid) WHERE articlechampid = '$champ[articlechampid]' AND siteid = '$facture[siteid]'");
						switch ($champ[type]){
							case "1": // boite texte
								$vals ="" ;
								$lignefacture_valeur = $DB_site->query_first("SELECT * FROM lignefacturechamp WHERE articlechampid = '$champ[articlechampid]' AND lignefactureid = '$lignefacture[lignefactureid]'");
								while ($valeur=$DB_site->fetch_array($valeurs)){
									$vals .= "$valeur[libelle]='$valeur[valeur]' " ;
								}
								$champForm = "<textarea class='form-control' id='perso[$lignefacture[lignefactureid]][$champ[articlechampid]]' name='perso[$lignefacture[lignefactureid]][$champ[articlechampid]]' $vals>$lignefacture[valeur]</textarea>";
								break ;
							case "2": // Boutons radio
								$lignefacture_valeur = $DB_site->query_first("SELECT * FROM lignefacturechamp WHERE articlechampid = '$champ[articlechampid]' AND lignefactureid = '$lignefacture[lignefactureid]'");
								$champForm = "<div class='radio-list radio-inline'>" ;
								while ($valeur=$DB_site->fetch_array($valeurs)){
									if($valeur[valeur] == $lignefacture_valeur[valeur]){
										$selected = "checked";
									}else{
										$selected = "";
									}
									$champForm .= "<label><input type='radio' class='radio-inline' id='perso[$lignefacture[lignefactureid]][$champ[articlechampid]]' name='perso[$lignefacture[lignefactureid]][$champ[articlechampid]]' $valeur[libelle]='$valeur[valeur]' $selected> $valeur[description]</label>";
								}
								$champForm .= "</div>";
								break ;
							case "3": // Cases à cocher
								$champForm = "<div class='checkbox-list>'" ;
								while ($valeur=$DB_site->fetch_array($valeurs)){
									$lignefacture_valeurs = $DB_site->query("SELECT * FROM lignefacturechamp WHERE articlechampid = '$champ[articlechampid]' AND lignefactureid = '$lignefacture[lignefactureid]'");
									$selected = "";
									while($lignefacture_valeur = $DB_site->fetch_array($lignefacture_valeurs)){
										if($valeur[valeur] == $lignefacture_valeur[valeur]){
											$selected = "checked";
										}
									}
									$champForm .= "<label><input type='checkbox' id='perso[$lignefacture[lignefactureid]][$champ[articlechampid]]' name='perso[$lignefacture[lignefactureid]][$champ[articlechampid]]' $valeur[libelle]='$valeur[valeur]' $selected> $valeur[description]</label>";
								}
								$champForm .= "</div>";
								break ;
							case "4": // Champ texte
								$lignefacture_valeur = $DB_site->query_first("SELECT * FROM lignefacturechamp WHERE articlechampid = '$champ[articlechampid]' AND lignefactureid = '$lignefacture[lignefactureid]'");
								$vals ="" ;
								while ($valeur=$DB_site->fetch_array($valeurs)){
									$vals .= "$valeur[libelle]='$valeur[valeur]' " ;
								}
								$champForm = "<input type='text' class='form-control' id='perso[$lignefacture[lignefactureid]][$champ[articlechampid]]' name='perso[$lignefacture[lignefactureid]][$champ[articlechampid]]' value='$lignefacture_valeur[valeur]' $vals>" ;
								break ;
							case "5": // Liste à sélection unique
								$lignefacture_valeur = $DB_site->query_first("SELECT * FROM lignefacturechamp WHERE articlechampid = '$champ[articlechampid]' AND lignefactureid = '$lignefacture[lignefactureid]'");
								$options = "" ;
								while ($valeur=$DB_site->fetch_array($valeurs)){
									if($valeur[valeur] == $lignefacture_valeur[valeur]){
										$selected = "selected='selected'";
									}else{
										$selected = "";
									}
									$options .= "<option $valeur[libelle]='$valeur[valeur]' $selected>$valeur[description]</option>" ;
								}
								$champForm = "<select class='form-control' id='perso[$lignefacture[lignefactureid]][$champ[articlechampid]]' name='perso[$lignefacture[lignefactureid]][$champ[articlechampid]]'><option value=''></option>$options</select>" ;
								break ;
							case "6": // Liste à sélections multiples
								$options = "" ;
								while ($valeur=$DB_site->fetch_array($valeurs)){
									$lignefacture_valeurs = $DB_site->query("SELECT * FROM lignefacturechamp WHERE articlechampid = '$champ[articlechampid]' AND lignefactureid = '$lignefacture[lignefactureid]'");
									$selected = "";
									while($lignefacture_valeur = $DB_site->fetch_array($lignefacture_valeurs)){
										if($valeur[valeur] == $lignefacture_valeur[valeur]){
											$selected = "selected";
										}
									}
									$options .= "<option $valeur[libelle]=$valeur[valeur] $selected>$valeur[description]</option>" ;
								}
								$champForm = "<select class='form-control' multiple='multiple' id='perso[$lignefacture[lignefactureid]][$champ[articlechampid]]' name='perso[$lignefacture[lignefactureid]][$champ[articlechampid]]'><option value=''></option>$options</select>" ;
								break ;
							case "7": // Insertion de fichier
								$lignefacture_valeur = $DB_site->query_first("SELECT * FROM lignefacturechamp WHERE articlechampid = '$champ[articlechampid]' AND lignefactureid = '$lignefacture[lignefactureid]'");
								if($lignefacture_valeur[valeur]){
									$champForm = "<a target='_blank' href='http://$host/uploads-$lignefacture_valeur[valeur]'><input type='button' onclick='fichierPersonnalisation' value='$multilangue[changer]'>";
								}else{
									$displayInputFile = "";
								}
								break ;
						}
						$nouvelLigne .= "<div style='text-align: left;'>
										<label><b>$multilangue[personnalisation] : </b></label>
										<label>$champ[libelle] : </label> $champForm<br>
										<div data-provides='fileinput' class='fileinput fileinput-new' style='$displayInputFile' >
											<span class='btn default btn-file'>
												<span class='fileinput-new'>
													$multilangue[selectionner]
												</span>
												<span class='fileinput-exists'>
													$multilangue[modification]
												</span>
												<input type='file' name='fichier_import' id='fichier_import'>
											</span>
											<span class='fileinput-filename'>
											</span>
											&nbsp;
											<a style='float: none' data-dismiss='fileinput' class='close fileinput-exists' href='#'></a>
										</div>
									</div>";
					}
				}
				
				
				$nouvelLigne .= "</td>";

				$nouvelLigne .= "<td>";
				foreach ($_POST as $key => $value) {
					$test_caracteristique = explode("-", $key);
					if ($test_caracteristique[0] == "caract_nouvel_article") {
						$caract = $DB_site->query_first("SELECT * FROM caracteristique_site WHERE caractid = '$test_caracteristique[1]' AND siteid = '1'");
						$caractvals = $DB_site->query("SELECT cv.caractvalid, cvs.libelle FROM caracteristiquevaleur AS cv 
														INNER JOIN caracteristiquevaleur_site AS cvs ON cv.caractvalid = cvs.caractvalid 
														INNER JOIN article_caractval AS acv ON cvs.caractvalid = acv.caractvalid
														WHERE cv.caractid = '$test_caracteristique[1]' AND acv.artid = '$lignefacture[artid]' AND cvs.siteid = '1' ");
						
						$nouvelLigne .= "<label>$caract[libelle] :</label><select class='form-control caract_article' id='caract-$caract[caractid]'>";
						
						while($caractval = $DB_site->fetch_array($caractvals)){
							if($caractval[caractvalid] == $value){
								$selected = "selected = 'selected'";
							}else{
								$selected = "";	
							}
							$nouvelLigne .= "<option value='$caractval[caractvalid]' $selected>$caractval[libelle]</option>";
						}
						$nouvelLigne .= "</select><br>";
					}
				}
				$nouvelLigne .= "</td>";
				
				if(in_array(5931, $modules) && $article[prixaumetre]){
					$nouvelLigne .= "<td><input type='text' class='form-control ta-center prixAuMetre_article' name='qte' value='$qte' style='width: 50px;'></td>";
				}else{
					$nouvelLigne .= "<td><input type='text' class='form-control ta-center qte_article' id='qte_$lignefactureid' name='qte' value='$qte' style='width: 50px;'></td>";
				}
				$nouvelLigne .= "<td><label>".formaterPrix($lignefacture[prixachat])." $devise_actuelle</label></td>";
				$nouvelLigne .= "<td><input type='text' name='prix' value='".formaterPrix($prix)."' class='form-control ta-center'  style='display: inline; width: 80px;'> $devise_actuelle</td>";
				$nouvelLigne .= "<td>
									<a class='btn blue btn_enregistrer_modif_ligne' >$multilangue[enregistrer_diminutif]</a><br><br>
									<a href='#myModalSupprimerLigne$lignefactureid' class='btn blue' data-placement='top' data-toggle='modal' role='button' id='btn_remboursement_produit'>$multilangue[supprimer_diminutif]</a>
									<div aria-hidden='true' aria-labelledby='myModalLabel' role='dialog' tabindex='-1' class='modal fade' id='myModalSupprimerLigne$lignefactureid' style='display: none;'>
										<div class='modal-dialog'>
											<div class='modal-content'>
												<div class='modal-header'>
													<button aria-hidden='true' data-dismiss='modal' class='close' type='button'></button>
													<h4 class='modal-title'>$multilangue[suppr_article]</h4>
												</div>
												<div class='modal-body ta-center'>
													$multilangue[suppression_ligne_facture]
												</div>
												<div class='modal-footer'>
													<button aria-hidden='true' data-dismiss='modal' class='btn default'>$multilangue[retour]</button>
													<button class='btn blue' data-dismiss='modal' id='btn_supprimer_ligne' onclick='supprimerLigne($lignefactureid)'>$multilangue[supprimer]</button>
												</div>
											</div>
										</div>
									</div>
								</td>";
			} else {
				$nouvelLigne = "Erreur";
			}
			
			$nouvelLigne .= "</tr>";
			echo $nouvelLigne ;
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
	
	if(isset($action) && $action == "statutlogistique"){
		if($admin_droit[$scriptcourant][ecriture]){
			$DB_site->query("UPDATE facture SET statutlogistiqueid = '$statut' WHERE factureid = '$factureid'");
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
	
	if(isset($action) && $action == "recherche"){
		$groupe_recherche = $DB_site->query("SELECT etat, moyen, client, email, ip, date_debut, date_fin, site, numerofacture, factureid, numero_suivi, lengow_orderid, shoppingflux_orderid, livraison, montant, statutlogistique FROM groupe_commande WHERE groupeid = '$groupeid'");
		$groupe_recherche = $DB_site->fetch_array($groupe_recherche);
		
		foreach ($groupe_recherche as $key => $value){
			if(!is_numeric($key))
				echo $key."|".$value."!";	
		}
	}
	
	if(isset($action) && $action == "groupe"){
		if($admin_droit[$scriptcourant][ecriture]){
			$DB_site->query("INSERT INTO groupe_commande (libelle) VALUES ('$_POST[libelle]')");
			$groupeid = $DB_site->insert_id();
			foreach ($_POST as $key => $value){
				if($value != "" && $value != "null" && $value != "undefined" && $value != "groupe"){
					$DB_site->query("UPDATE groupe_commande SET $key = '$value' WHERE groupeid = '$groupeid'");	
				}
			}
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
	
	if(isset($action) && $action == "chercherPrix"){
		$facture = $DB_site->query_first("SELECT * FROM facture WHERE factureid = '$factureid'");
		if(isset($lignefactureid) && $lignefactureid > 0){
			$artid = $DB_site->query_first("SELECT artid FROM lignefacture WHERE lignefactureid = '$lignefactureid'");
			$artid = $artid[artid];
		}
		$article = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING (artid) WHERE artid = '$artid' && siteid = '$facture[siteid]'");
		$user_facture = $DB_site->query_first("SELECT * FROM utilisateur WHERE userid = '$facture[userid]'");
		
		
		
		$tabcaractvalids = array();
		foreach ($_POST as $key => $value){
			$caract = explode("-", $key);
			if($caract[0] == "caract_nouvel_article" || $caract[0] == "caract"){
				array_push($tabcaractvalids, $value);
			}
		}
		////////////////////////////////////// Récupération du prix de l'article \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
		$util_remise=$DB_site->query_first("SELECT * FROM utilisateur WHERE userid = '$user_facture[userid]'") ;
		if ($util_remise[pctremise] != 0){
			$pctRemiseOnAll = $util_remise[pctremise] ;
		}elseif ($util_remise[groupeid] != 0){
			$gp_remise=$DB_site->query_first("SELECT * FROM groupe_utilisateur WHERE groupeid = '$util_remise[groupeid]'") ;
			$pctRemiseOnAll = $gp_remise[pctremise] ;
		}
		if(sizeof($tabcaractvalids) == 0){
			$gestionStock = $DB_site->query_first("SELECT stock_illimite FROM article WHERE artid = '$article[artid]'");
			if (in_array(4, $modules) && !$gestionStock[stock_illimite]){
				switch($params['tristock']){ //modif benj
					case 0: //le plus grand stock
						$sql="SELECT * FROM stocks WHERE artid = '$article[artid]' ORDER BY total DESC, stockid ASC LIMIT 1";
						break;
					case 1: //par position
						$sql="SELECT * FROM stocks WHERE artid = '$article[artid]' ORDER BY stockid ASC LIMIT 1";
						break;
					case 2: //le moins cher
						$sql="SELECT * FROM stocks WHERE artid = '$article[artid]' AND total>0 ORDER BY differenceprix ASC, stockid ASC LIMIT 1";
						break;
				}
					
				$biggerStock = $DB_site->query_first($sql);
				if ($biggerStock[stockid]) {
					$article[bigger_stock][0] = $biggerStock[total];
					$lignesStock = $DB_site->query("SELECT * FROM stocks_caractval WHERE stockid = '$biggerStock[stockid]'");
					while($ligneStock = $DB_site->fetch_array($lignesStock))  {
						array_push($tabcaractvalids, $ligneStock[caractvalid]);
					}
				} else {
					$article[bigger_stock][0] = retournerStockArticle($DB_site, $article[artid]);
				}
			}else{
				$combiMiniPrix = $DB_site->query_first("SELECT stockid FROM stocks
															INNER JOIN stocks_site USING(stockid)
															WHERE artid = '$article[artid]'
															ORDER BY differenceprix ASC, stockid ASC LIMIT 1");
				switch($params['tristock']){ //modif benj
					case 0: //le plus grand stock...  inutile dans ce cas vu qu'on est en stock illimité!
						$sql="SELECT * FROM stocks WHERE artid = '$article[artid]' ORDER BY total DESC, stockid ASC LIMIT 1";
						break;
					case 1: //par position
						$sql="SELECT * FROM stocks WHERE artid = '$article[artid]' ORDER BY stockid ASC LIMIT 1";
						break;
					case 2: //le moins cher
						$sql="SELECT * FROM stocks AS s INNER JOIN stocks_site AS ss ON s.stockid=ss.stockid AND ss.siteid='$facture[siteid]'
								WHERE s.artid = '$article[artid]' ORDER BY ss.differenceprix ASC, s.stockid ASC LIMIT 1";
						break;
				}
				$biggerStock = $DB_site->query_first($sql);
				if ($biggerStock[stockid]) {
					$lignesStock = $DB_site->query("SELECT * FROM stocks_caractval WHERE stockid = '$biggerStock[stockid]'");
					while($ligneStock = $DB_site->fetch_array($lignesStock))  {
						array_push($tabcaractvalids, $ligneStock[caractvalid]);
					}
				}
			}
		}
		
		$listecaractvalids = "";
		for($i=0;$i<sizeof($tabcaractvalids);$i++){
			$listecaractvalids .= "$tabcaractvalids[$i],";
		}
		
		$listecaractvalids = substr($listecaractvalids, 0, -1);
	
		$infosCombinaison = retournerInformationsStockSite($DB_site, $article[artid], $facture[siteid], $listecaractvalids);
		$gestionStock = $DB_site->query_first("SELECT stock_illimite FROM article WHERE artid = '$article[artid]'");
		$qteAtester = $qte?$qte:1;
		if (in_array(4, $modules) && !$gestionStock[stock_illimite]) {
			if ($qte && $qte > $bigger_stock[0]) {
				$qteAtester = $bigger_stock[0];
			}
		}
		
		if ($article[commandable] == "0" && $article[prix] == "0") {
			// article non commandable avec prix à 0, on n'affiche pas de prix
		} else {
			$prixArticle = 0;
			$tabRemises = array();
					
			if (in_array(122, $modules) && $user_facture[pro]){
				// prix de base
				$prixArticle = $article[$prixpro];
				// modification prix caract
				$prixArticle += $infosCombinaison[differenceprixproht];

				$prixNormal = $prixArticle;
			}else{
				// prix de base
				$prixArticle = $article[prix];
				// modification prix caract
				$prixArticle += $infosCombinaison[differenceprix];

				$prixNormal = $prixArticle;
	
				// promotion
				$remisePromotion = 0;
				if (estEnPromoSite($DB_site, $article[artid], $facture[siteid])) { // On vérifie si l'article est en promo
					$article_promo = $DB_site->query_first("SELECT pctpromo FROM article_promo_site WHERE artid = '$article[artid]' && siteid = '$facture[siteid]'");
					$remisePromotion = calculerPrixPourcent($prixArticle, $article_promo[pctpromo]);
					if ($params[promos_cumulables] == 1) { // On peut cumuler les promotions avec les remises
						$prixArticle -= $remisePromotion;
					}else{
						$tabRemises[1] = $remisePromotion;
					}
				}
			}
			// remise utilisateur / groupe
			$remiseUtilGroup = 0;

			if(isset($pctRemiseOnAll) && $pctRemiseOnAll != 0) {
				$remiseUtilGroup = calculerPrixPourcent($prixArticle, $pctRemiseOnAll);
			}
			// remise de gros
			$remiseGros = 0;
			if (in_array(3, $modules)){
				$tempPrixArticle = $prixArticle * $qteAtester;
				if (in_array(122, $modules) && $user_facture[pro]) {
					$tableRemiseGros = "remisepro";
				} else {
					$tableRemiseGros = "remise";
				}
				$pctRemiseGros = calculerRemiseGros($DB_site, $tableRemiseGros, $article[artid], $article[catid], $qteAtester, $tempPrixArticle);
				if ($pctRemiseGros) {
					$remiseGros = calculerPrixPourcent($prixArticle, $pctRemiseGros);
				}
			}
			// remise la plus avantageuse (se référer à la table type_remise pour les typeremiseid)
			$remiseMax = 0;
			$typeRemiseMax = 0;
			if ($remiseUtilGroup){
				$tabRemises[2] = $remiseUtilGroup;
			}
			if ($remiseGros) {
				$tabRemises[3] = $remiseGros;
			}
			if (count($tabRemises)) {
				foreach($tabRemises as $typeremise => $remise) {
					if ($remise >= $remiseMax) {
						$remiseMax = $remise;
						$typeRemiseMax = $typeremise;
					}
				}
			}
			if (!$remiseMax && !$typeRemiseMax && $remisePromotion) {
				$typeRemiseMax = 1;
			}
			$prixArticle -= $remiseMax;
			if (in_array(5937, $modules) && $choixlongueur){
				$prixArticleMetre = $prixArticle*$choixlongueur/100;
				$prixNormalMetre = $prixNormal*$choixlongueur/100;
				$article[prixmetreE] = formaterPrix($prixArticleMetre);
				$article[prixArticleMetreE] = formaterPrix($prixArticleMetre);
			}
		
			if (in_array(5937, $modules)) {
				$prixFinal = $article[prixmetreE]*$qte/100;
				echo formaterPrix($prixFinal, 2, '.', '');
			} else {
				$prixFinal = $prixArticle*$qte;
				echo formaterPrix($prixFinal, 2,'.', '');
			}
		}
		
	}
	
	if(isset($action) && $action == "chercherInfosArticle"){
		$facture = $DB_site->query_first("SELECT * FROM facture WHERE factureid = '$factureid'");
		$article = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING (artid) WHERE artid = '$artid' && siteid = '$facture[siteid]'");
		$user_facture = $DB_site->query_first("SELECT * FROM utilisateur WHERE userid = '$facture[userid]'");

		////////////////////////////////////// Récupération du prix de l'article \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
		$tabcaractvalids = array();
		
		$util_remise=$DB_site->query_first("SELECT * FROM utilisateur WHERE userid = '$user_facture[userid]'") ;
		if ($util_remise[pctremise] != 0){
			$pctRemiseOnAll = $util_remise[pctremise] ;
		}elseif ($util_remise[groupeid] != 0){
			$gp_remise=$DB_site->query_first("SELECT * FROM groupe_utilisateur WHERE groupeid = '$util_remise[groupeid]'") ;
			$pctRemiseOnAll = $gp_remise[pctremise] ;
		}
		
		$gestionStock = $DB_site->query_first("SELECT stock_illimite FROM article WHERE artid = '$article[artid]'");
		if (in_array(4, $modules) && !$gestionStock[stock_illimite]){
			switch($params['tristock']){ //modif benj
				case 0: //le plus grand stock
					$sql="SELECT * FROM stocks WHERE artid = '$article[artid]' ORDER BY total DESC, stockid ASC LIMIT 1";
					break;
				case 1: //par position
					$sql="SELECT * FROM stocks WHERE artid = '$article[artid]' ORDER BY stockid ASC LIMIT 1";
					break;
				case 2: //le moins cher
					$sql="SELECT * FROM stocks WHERE artid = '$article[artid]' AND total>0 ORDER BY differenceprix ASC, stockid ASC LIMIT 1";
					break;
			}
		
			$biggerStock = $DB_site->query_first($sql);
			if ($biggerStock[stockid]) {
				$article[bigger_stock][0] = $biggerStock[total];
				$lignesStock = $DB_site->query("SELECT * FROM stocks_caractval WHERE stockid = '$biggerStock[stockid]'");
				while($ligneStock = $DB_site->fetch_array($lignesStock))  {
					array_push($tabcaractvalids, $ligneStock[caractvalid]);
				}
			} else {
				$article[bigger_stock][0] = retournerStockArticle($DB_site, $article[artid]);
			}
		}else{
			switch($params['tristock']){ //modif benj
				case 0: //le plus grand stock...  inutile dans ce cas vu qu'on est en stock illimité!
					$sql="SELECT * FROM stocks WHERE artid = '$article[artid]' ORDER BY total DESC, stockid ASC LIMIT 1";
					break;
				case 1: //par position
					$sql="SELECT * FROM stocks WHERE artid = '$article[artid]' ORDER BY stockid ASC LIMIT 1";
					break;
				case 2: //le moins cher
					$sql="SELECT * FROM stocks AS s INNER JOIN stocks_site AS ss ON s.stockid=ss.stockid AND ss.siteid='$facture[siteid]'
							WHERE s.artid = '$article[artid]' ORDER BY ss.differenceprix ASC, s.stockid ASC LIMIT 1";
					break;
			}
			$biggerStock = $DB_site->query_first($sql);
			if ($biggerStock[stockid]) {
				$lignesStock = $DB_site->query("SELECT * FROM stocks_caractval WHERE stockid = '$biggerStock[stockid]'");
				while($ligneStock = $DB_site->fetch_array($lignesStock))  {
					array_push($tabcaractvalids, $ligneStock[caractvalid]);
				}
			}
		}
		
		$listecaractvalids = "";
		for($i=0;$i<sizeof($tabcaractvalids);$i++){
			$listecaractvalids .= "$tabcaractvalids[$i],";
		}
		
		$listecaractvalids = substr($listecaractvalids, 0, -1);

		$infosCombinaison = retournerInformationsStockSite($DB_site, $article[artid], $facture[siteid], $listecaractvalids);
		$gestionStock = $DB_site->query_first("SELECT stock_illimite FROM article WHERE artid = '$article[artid]'");
		$qteAtester = $qte?$qte:1;
		if (in_array(4, $modules) && !$gestionStock[stock_illimite]) {
			if ($qte && $qte > $bigger_stock[0]) {
				$qteAtester = $bigger_stock[0];
			}
		}

		if ($article[commandable] == "0" && $article[prix] == "0") {
			// article non commandable avec prix à 0, on n'affiche pas de prix
		} else {
			$prixArticle = 0;
			$tabRemises = array();
			
			if (in_array(122, $modules) && $user_facture[pro]){
				// prix de base
				$prixArticle = $article[$prixpro];
				// modification prix caract
				$prixArticle += $infosCombinaison[differenceprixproht];

				$prixNormal = $prixArticle;
			}else{
				// prix de base
				$prixArticle = $article[prix];
				// modification prix caract
				$prixArticle += $infosCombinaison[differenceprix];

				$prixNormal = $prixArticle;

				// promotion
				$remisePromotion = 0;
				if (estEnPromoSite($DB_site, $article[artid], $facture[siteid])) { // On vérifie si l'article est en promo
					$article_promo = $DB_site->query_first("SELECT pctpromo FROM article_promo_site WHERE artid = '$article[artid]' && siteid = '$facture[siteid]'");
					$remisePromotion = calculerPrixPourcent($prixArticle, $article_promo[pctpromo]);
					if ($params[promos_cumulables] == 1) { // On peut cumuler les promotions avec les remises
						$prixArticle -= $remisePromotion;
					}else{
						$tabRemises[1] = $remisePromotion;
					}
				}
			}
			// remise utilisateur / groupe
			$remiseUtilGroup = 0;

			if(isset($pctRemiseOnAll) && $pctRemiseOnAll != 0) {
				$remiseUtilGroup = calculerPrixPourcent($prixArticle, $pctRemiseOnAll);
			}
			// remise de gros
			$remiseGros = 0;
			if (in_array(3, $modules)){
				$tempPrixArticle = $prixArticle * $qteAtester;
				if (in_array(122, $modules) && $user_facture[pro]) {
					$tableRemiseGros = "remisepro";
				} else {
					$tableRemiseGros = "remise";
				}
				$pctRemiseGros = calculerRemiseGros($DB_site, $tableRemiseGros, $article[artid], $article[catid], $qteAtester, $tempPrixArticle);
				if ($pctRemiseGros) {
					$remiseGros = calculerPrixPourcent($prixArticle, $pctRemiseGros);
				}
			}
			// remise la plus avantageuse (se référer à la table type_remise pour les typeremiseid)
			$remiseMax = 0;
			$typeRemiseMax = 0;
			if ($remiseUtilGroup){
				$tabRemises[2] = $remiseUtilGroup;
			}
			if ($remiseGros) {
				$tabRemises[3] = $remiseGros;
			}
			if (count($tabRemises)) {
				foreach($tabRemises as $typeremise => $remise) {
					if ($remise >= $remiseMax) {
						$remiseMax = $remise;
						$typeRemiseMax = $typeremise;
					}
				}
			}
			if (!$remiseMax && !$typeRemiseMax && $remisePromotion) {
				$typeRemiseMax = 1;
			}
			$prixArticle -= $remiseMax;
			if (in_array(5937, $modules) && $article[prixaumetre]){
				$prixArticleMetre = $prixArticle*$choixlongueur/100;
				$prixNormalMetre = $prixNormal*$choixlongueur/100;
				/*$article[prixmetreE] = formaterPrix($prixArticleMetre);
				$article[prixArticleMetreE] = formaterPrix($prixArticleMetre);*/
			}
			if (in_array(5937, $modules) && $article[prixaumetre]) {
				echo "metre---";
				echo formaterPrix($prixArticleMetre, 2, '.', '');
			} else {
				echo "normal---";
				echo formaterPrix($prixArticle, 2,'.', '');
			}
		}

		// Bigger stock
		$gestionStock = $DB_site->query_first("SELECT stock_illimite FROM article WHERE artid = '$article[artid]'");
		if (in_array(4, $modules) && !$gestionStock[stock_illimite]){
			switch($params['tristock']){ //modif benj
				case 0: //le plus grand stock
					$sql="SELECT * FROM stocks WHERE artid = '$article[artid]' ORDER BY total DESC, stockid ASC LIMIT 1";
					break;
				case 1: //par position
					$sql="SELECT * FROM stocks WHERE artid = '$article[artid]' ORDER BY stockid ASC LIMIT 1";
					break;
				case 2: //le moins cher
					$sql="SELECT * FROM stocks WHERE artid = '$article[artid]' AND total>0 ORDER BY differenceprix ASC, stockid ASC LIMIT 1";
					break;
			}
		
			$biggerStock = $DB_site->query_first($sql);
			if ($biggerStock[stockid]) {
				$article[bigger_stock][0] = $biggerStock[total];
				$lignesStock = $DB_site->query("SELECT * FROM stocks_caractval WHERE stockid = '$biggerStock[stockid]'");
				while($ligneStock = $DB_site->fetch_array($lignesStock))  {
					array_push($tabcaractvalids, $ligneStock[caractvalid]);
				}
			} else {
				$article[bigger_stock][0] = retournerStockArticle($DB_site, $article[artid]);
			}
		}else{
			switch($params['tristock']){ //modif benj
				case 0: //le plus grand stock...  inutile dans ce cas vu qu'on est en stock illimité!
					$sql="SELECT * FROM stocks WHERE artid = '$article[artid]' ORDER BY total DESC, stockid ASC LIMIT 1";
					break;
				case 1: //par position
					$sql="SELECT * FROM stocks WHERE artid = '$article[artid]' ORDER BY stockid ASC LIMIT 1";
					break;
				case 2: //le moins cher
					$sql="SELECT * FROM stocks AS s INNER JOIN stocks_site AS ss ON s.stockid=ss.stockid AND ss.siteid='$facture[siteid]'
					WHERE s.artid = '$article[artid]' ORDER BY ss.differenceprix ASC, s.stockid ASC LIMIT 1";
					break;
			}
			$biggerStock = $DB_site->query_first($sql);
			if ($biggerStock[stockid]) {
				$lignesStock = $DB_site->query("SELECT * FROM stocks_caractval WHERE stockid = '$biggerStock[stockid]'");
				while($ligneStock = $DB_site->fetch_array($lignesStock))  {
					array_push($tabcaractvalids, $ligneStock[caractvalid]);
				}
			}
		}

		
		$article_caracteristiques = $DB_site->query("SELECT cvs.libelle AS libcaractval, cs.libelle AS libcaract, cs.caractid, cvs.caractvalid 
														FROM article_caractval AS ac
														INNER JOIN caracteristiquevaleur AS cv ON ac.caractvalid = cv.caractvalid
														INNER JOIN caracteristiquevaleur_site AS cvs ON cv.caractvalid = cvs.caractvalid										
														INNER JOIN caracteristique_site AS cs ON cv.caractid = cs.caractid
														WHERE ac.artid = '$artid' AND cvs.siteid = '1' AND cs.siteid = '1'");
		$libcaract = "";
		while($article_caracteristique = $DB_site->fetch_array($article_caracteristiques)){
			if(in_array($article_caracteristique[caractvalid],$tabcaractvalids)){
				if($libcaract != $article_caracteristique[libcaract] ){
					echo "!$article_caracteristique[libcaract]:$article_caracteristique[caractid]";
					echo "_|$article_caracteristique[libcaractval]:$article_caracteristique[caractvalid]";
					$libcaract = $article_caracteristique[libcaract];
				}else{
					echo "_|$article_caracteristique[libcaractval]:$article_caracteristique[caractvalid]";
				}
			}else{
				if($libcaract != $article_caracteristique[libcaract] ){
					echo "!$article_caracteristique[libcaract]:$article_caracteristique[caractid]";
					echo "_$article_caracteristique[libcaractval]:$article_caracteristique[caractvalid]";
					$libcaract = $article_caracteristique[libcaract];
				}else{
					echo "_$article_caracteristique[libcaractval]:$article_caracteristique[caractvalid]";
				}
			}
			
		}
		
	}
    

/** --- avoir le fichier zip de l'article de la commande--- **/
    /** avoir fichier Zip pour lignefacture **/
    if (isset($action) && $action == "getzipfile") {
	   if($admin_droit[$scriptcourant][ecriture]){
            if(isset($_POST["factureId"]) && $_POST["factureId"] !== "" ){
                echo ">> " . $_POST["factureId"];
                exit();
            }
        }
    }
/** --- avoir le fichier zip de l'article de la commande--- **/
	
	
	if(!isset($action) || $action = ""){
		$iDisplayLength = intval($length);
		$iDisplayStart = intval($start);
		
		$records = array();
		$records["aaData"] = array();
		
		if(!isset($iDisplayStart))
			$iDisplayStart = 0;
		if(!isset($iDisplayLength))
			$iDisplayLength = 10;
		
		switch($order[0][column]){
			case "0" :
				$orderby = "s.libelle";
				break;
			case "1" :
				$orderby = "f.numerofacture";
				break;
			case "2" :
				$orderby = "f.factureid";
				break;
			case "3" :
				$orderby = "f.timestamp";
				break;
			case "4" :
				$orderby = "f.prenom, f.nom";
				break;
			case "5" :
				$orderby = "f.montanttotal_ttc";
				break;
			case "6" :
				$orderby = " mps.libelle";
				break;
			case "7" :
				$orderby = "f.timestamp";
				break;
			case "8" :
				$orderby = "mls.nom";
				break;
									
			default:
				$orderby = "f.timestamp";
				break;
		}
		
		$sensorder = $order[0][dir];
		
		$where = "";
		if($libsite != "" && sizeof($libsite) > 0){
			$tous_sites=0;
			$liste_sites="";
			foreach ($libsite as $value){
				if($value == "-1"){
					$tous_sites=1;
				}else{
					$liste_sites.="$value,";
				}	
			}
			if($liste_sites != ""){
				$liste_sites=substr($liste_sites,0,-1);
			}
			if(!$tous_sites){
				$where .= "AND s.siteid IN ($liste_sites) ";
			}		
		}		
		if($numerofacture != ""){
			$where .= "AND f.numerofacture = '$numerofacture' ";
		}
		if($factureid != ""){
			$where .= "AND f.factureid = '$factureid' ";
		}
		if($numero_suivi != ""){
			$where .= "AND c.numero_suivi = '$numero_suivi' ";
		}
		if($lengow_orderid != ""){
			$where .= "AND f.lengow_orderid LIKE '%$lengow_orderid%' ";
		}
		if($shoppingflux_orderid != ""){
			$where .= "AND f.shoppingflux_orderid LIKE '%$shoppingflux_orderid%' ";
		}
		if($date_debut != "" && $date_fin == ""){
			list($jour, $mois, $annee) = explode('/', $date_debut);
			$date_debut = mktime(0,0,0,$mois, $jour, $annee);
			$where .= "AND f.timestamp2 >= '$date_debut' ";
		}
		if($date_debut == "" && $date_fin != ""){
			list($jour, $mois, $annee) = explode('/', $date_fin);
			$date_fin = mktime(0,0,0,$mois, $jour, $annee);
			$where .= "AND f.timestamp2 <= '$date_fin' ";
		}
		if($date_debut != "" && $date_fin != ""){
			list($jour, $mois, $annee) = explode('/', $date_debut);
			$date_debut = mktime(0,0,0,$mois, $jour, $annee);
			list($jour, $mois, $annee) = explode('/', $date_fin);
			$date_fin = mktime(0,0,0,$mois, $jour, $annee);
			$where .= "AND (f.timestamp2 >= '$date_debut' AND f.timestamp2 <= '$date_fin') ";
		}
		if($client != ""){
			$where .= "AND (f.nom LIKE '%$client%' OR f.prenom LIKE '%$client%') ";
		}
		if($email != ""){
			$where .= "AND f.mail LIKE '%$email%' ";
		}
		if($ip != ""){
			$ip = iptochaine($ip);
			$where .= "AND f.ip = '$ip' ";
		}
		if($montant != ""){
			$montant=str_replace(",",".",$montant);
			$where .= "AND (f.montanttotal_ttc like '%$montant%' OR ROUND(f.montanttotal_ttc,2) like '%$montant%' ) ";
		}
		if($moyen != "" && sizeof($moyen) > 0){
			$tous_moyen=0;
			$liste_moyen="";
			foreach ($moyen as $value){
				if($value == "-1"){
					$tous_moyen=1;
				}else{
					$liste_moyen.="$value,";
				}
			}
			if($liste_moyen != ""){
				$liste_moyen=substr($liste_moyen,0,-1);
			}
			if(!$tous_moyen){
				$where .= "AND mps.moyenid IN ($liste_moyen)  ";
			}
		}
		if($etat != "" && sizeof($etat) > 0){
			$tous_etat=0;
			$liste_etat="";
			foreach ($etat as $value){
				if($value == "-1"){
					$tous_etat=1;
				}else{
					$liste_etat.="$value,";
				}
			}
			if($liste_etat != ""){
				$liste_etat=substr($liste_etat,0,-1);
			}
			if(!$tous_etat){
				$where .= "AND efl.etatid IN ($liste_etat)  ";
			}
		}
		if($livraison != "" && sizeof($livraison) > 0){
			$tous_livraison=0;
			$liste_livraison="";
			foreach ($livraison as $value){
				if($value == "-1"){
					$tous_livraison=1;
				}else{
					$liste_livraison.="$value,";
				}
			}
			if($liste_livraison != ""){
				$liste_livraison=substr($liste_livraison,0,-1);
			}
			if(!$tous_livraison){
				$where .= "AND mls.modelivraisonid IN ($liste_livraison)  ";
			}
		}
		if($statutlogistique != "" && sizeof($statutlogistique) > 0){
			$tous_statut=0;
			$liste_statut="";
			foreach ($statutlogistique as $value){
				if($value == "-1"){
					$tous_statut=1;
				}else{
					$liste_statut.="$value,";
				}
			}
			if($liste_statut != ""){
				$liste_statut=substr($liste_statut,0,-1);
			}
			if(!$tous_statut){
				$where .= "AND f.statutlogistiqueid IN ($liste_statut)  ";
			}
		}
		
		$records["AAAAA"] = $where;
		$commandes = $DB_site->query("SELECT f.factureid, f.numerofacture, f.passefacture, f.montanttotal_ttc, f.nom, f.prenom, f.mail, f.ip, f.lengow_orderid, f.shoppingflux_orderid, f.statutlogistiqueid, f.timestamp2, mps.libelle AS moyenlibelle, s.libelle AS sitelibelle, s.siteid, s.paysid, s.classcolor, efl.libelle AS etatlibelle, ef.couleur, ef.etatid, mls.nom AS modelibelle
										FROM facture AS f
										INNER JOIN moyenpaiement_site AS mps ON mps.moyenid = f.moyenid AND mps.siteid = '1'
										INNER JOIN site AS s ON s.siteid = f.siteid
										INNER JOIN etatfacture AS ef ON ef.etatid = f.etatid
										INNER JOIN etatfacture_langue AS efl ON efl.etatid = f.etatid AND efl.siteid = '1'
										INNER JOIN mode_livraison_site AS mls ON mls.modelivraisonid = f.modelivraisonid AND mls.siteid = '1'
										WHERE f.deleted = '0' $where ORDER BY $orderby $sensorder");

		$commandescount = $DB_site->num_rows($commandes);
		
		$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
		$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $commandescount);
		
		if ($commandescount > 0) {
			$commandes = $DB_site->query("SELECT f.factureid, f.numerofacture, f.passefacture, f.montanttotal_ttc, f.nom, f.prenom, f.mail, f.ip, f.lengow_orderid, f.shoppingflux_orderid, f.statutlogistiqueid, f.timestamp2, mps.libelle AS moyenlibelle, s.libelle AS sitelibelle, s.siteid, s.paysid, s.classcolor, efl.libelle AS etatlibelle, ef.couleur, ef.etatid, mls.nom AS modelibelle
										FROM facture AS f
										INNER JOIN moyenpaiement_site AS mps ON mps.moyenid = f.moyenid AND mps.siteid = '1'
										INNER JOIN site AS s ON s.siteid = f.siteid
										INNER JOIN etatfacture AS ef ON ef.etatid = f.etatid
										INNER JOIN etatfacture_langue AS efl ON efl.etatid = f.etatid AND efl.siteid = '1'
										INNER JOIN mode_livraison_site AS mls ON mls.modelivraisonid = f.modelivraisonid AND mls.siteid = '1'
										WHERE f.deleted = '0' $where ORDER BY $orderby $sensorder LIMIT $limitlower, $perpage");
			
			while ($commande = $DB_site->fetch_array($commandes)){
				$numero_suivi = "";
				$nums_suivi = $DB_site->query("SELECT numero_suivi FROM colis WHERE factureid = '$commande[factureid]'");
				while($num_suivi = $DB_site->fetch_array($nums_suivi)){
					$numero_suivi .= "$num_suivi[numero_suivi]<br>";
				}
				$statut = $DB_site->query_first("SELECT libelle FROM statut_logistique WHERE statutlogistiqueid = '$commande[statutlogistiqueid]'");
				
				$client = "$commande[prenom] $commande[nom]";
				$actions = '<a href="commandes.php?action=modifier&factureid=' . $commande[factureid] . '" data-original-title="' . $multilangue[modifier] . '" data-placement="top" class="btn tooltips">';
	  			$actions .= '<i class="fa fa-edit fs-18 font-blue"></i>';
	  			$actions .= '</a>';
	  			
	  			$notices = $DB_site->query("SELECT * 
	  										FROM notice n 
	  										INNER JOIN lignefacturenotice lfn ON n.noticeid = lfn.noticeid 
	  										INNER JOIN lignefacture lf ON lfn.lignefactureid = lf.lignefactureid 
	  										WHERE lf.factureid = '$commande[factureid]' ORDER BY n.noticeid");
	  			$bc = "";
	  			$f = "";
	  			$bl = "";
	  			$bp = "";
	  			$not = "";
	  			
	  			if($commande[etatid] == '0'){
	  				$actions .= '<a href="http://'.$host.'/force_download.php?dest=facture&factureid='.$commande[factureid].'&type=13&passe='.$commande[passefacture].'&admin=1" data-original-title="'.$multilangue[proformaimprime].'" data-placement="top" class="btn tooltips"><i class="fa fa-print fs-18"></i></a>';
		  			$actions .= '<a href="#myModal' . $commande[factureid] . '" id="btn_suppr' . $commande[factureid]. '" data-original-title="' . $multilangue[supprimer] . '" data-placement="top" data-toggle="modal" role="button" class="btn tooltips">';
		  			$actions .= '<i class="fa fa-trash-o fs-18 font-red"></i>';
		  			$actions .= '</a>';
		  			$actions .= '<div aria-hidden="true" aria-labelledby="myModalLabe' . $commande[factureid] . '" role="dialog" tabindex="-1" class="modal fade" id="myModal' . $commande[factureid] . '" style="display: none;">';
		  			$actions .= '<div class="modal-dialog">';
		  			$actions .= '<div class="modal-content">';
		  			$actions .= '<div class="modal-header">';
		  			$actions .= '<button aria-hidden="true" data-dismiss="modal" class="close" type="button"></button>';
		  			$actions .= '<h4 class="modal-title">'.$multilangue[suppr_commande].' ' . $commande[factureid] . ' ?</h4>';
		  			$actions .= '</div>';
		  			$actions .= '<div class="modal-body ta-center">';
		  			$actions .= $multilangue[suppression_commande_infos];
		  			$actions .= '</div>';
		  			$actions .= '<div class="modal-footer">';
		  			$actions .= '<button aria-hidden="true" data-dismiss="modal" class="btn default">'.$multilangue[non].'</button>';
		  			$actions .= '<a href="commandes.php?action=supprcommande&factureid=' . $commande[factureid] . '" class="btn blue">'.$multilangue[oui_supprimer].'</a>';
		  			$actions .= '</div>';
		  			$actions .= '</div>';
		  			$actions .= '</div>';
		  			$actions .= '</div>';
	  			}else{
	  				if($commande[etatid] != '2' && $commande[etatid] != '3'){
		  				if($commande[etatid] != '1'){
		  					$f = '<a href="http://'.$host.'/force_download.php?dest=facture&factureid='.$commande[factureid].'&type=1&passe='.$commande[passefacture].'&admin=1" data-original-title="'.$multilangue[imprimer_facture].'" data-placement="top" class="tooltips"><i class="fa fa-print fs-18"></i></a>';
		  				}
		  				if($DB_site->num_rows($notices) > 0){
		  					$not = '<a href="http://'.$host.'/force_download.php?dest=facture&factureid='.$commande[factureid].'&type=12&passe='.$commande[passefacture].'&admin=1" data-original-title="'.$multilangue[imprimer_notice].'" data-placement="top" class="tooltips"><i class="fa fa-print fs-18"></i></a>';
		  				}
		  				$bc = '<a href="http://'.$host.'/force_download.php?dest=facture&factureid='.$commande[factureid].'&type=3&passe='.$commande[passefacture].'&admin=1" data-original-title="'.$multilangue[imprimer_bon_commande].'" data-placement="top" class="tooltips"><i class="fa fa-print fs-18"></i></a>';
		  				$bl = '<a href="http://'.$host.'/force_download.php?dest=facture&factureid='.$commande[factureid].'&type=5&passe='.$commande[passefacture].'&admin=1" data-original-title="'.$multilangue[imprimer_bon_livraison].'" data-placement="top" class="tooltips"><i class="fa fa-print fs-18"></i></a>';
		  				$bp = '<a href="http://'.$host.'/force_download.php?dest=facture&factureid='.$commande[factureid].'&type=7&passe='.$commande[passefacture].'&admin=1" data-original-title="'.$multilangue[imprimer_bon_preparation].'" data-placement="top" class="tooltips"><i class="fa fa-print fs-18"></i></a>';
		  			}
	  			}
	  			
	  			$date = date("d/m/Y H:i:s", $commande[timestamp2]);
	  			$devise = $tabsites[$commande[siteid]][devise_complete];
	  			$flag = $DB_site->query_first("SELECT diminutif FROM pays WHERE paysid = '$commande[paysid]'");
	  			$flag[diminutif] = strtolower($flag[diminutif]);
				$records["aaData"][] = array(
						"checkbox" => "<input type='checkbox' name='chk[$commande[factureid]]' class='chk' id='$commande[factureid]' onclick='ligneSelect();'>",
						"site" => "<img src='assets/img/flags/".$flag[diminutif].".png'> <font class='font-".$commande[classcolor]."'>$commande[sitelibelle]</font>",
						"factureid" => $commande[factureid],
						"numerofacture" => $commande[numerofacture],
						"numero_suivi" => $numero_suivi,
						"lengow_orderid" => $commande[lengow_orderid],
						"shoppingflux_orderid" => $commande[shoppingflux_orderid],
						"date" => $date,
						"client" => $client,
						"email" => $commande[mail],
						"ip" => chainetoip($commande[ip]),
						"montant" => formaterPrix($commande[montanttotal_ttc])." ".$devise,
						"moyen" => $commande[moyenlibelle],
						"etat" => "<font style='color: #".$commande[couleur]."'>$commande[etatlibelle]</font>",
						"livraison" =>$commande[modelibelle],
						"statutlogistique" => $statut[libelle],
						"actions" => $actions,
						"bc" => $bc,
						"f" => $f,
						"bl" => $bl,
						"bp" => $bp,
						"not" => $not
				);
			}
		}
		
		$records["iTotalRecords"] = $commandescount;
		$records["iTotalDisplayRecords"] = $commandescount;
		
		echo json_encode($records);
	}
?>