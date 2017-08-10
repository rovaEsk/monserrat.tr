<?
include "./includes/header.php";

$referencepage="remises_gros";
$pagetitle = "Remises de gros - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

//$mode = "test_modules";


// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

// Affichage des onglets
if (in_array("3", $modules) || $mode == "test_modules")
	eval(charge_template($langue, $referencepage, "ModificationRemisesGros"));
if ((in_array("3", $modules) && in_array("122", $modules)) || $mode == "test_modules")
	eval(charge_template($langue, $referencepage, "ModificationRemisesGrosPro"));

// Suppression d'une remise de gros
if(isset($action) and $action == "supprimer"){
	if($admin_droit[$scriptcourant][suppression]){
		if(isset($catid)){
			if(isset($module) and $module == "pro"){
				$DB_site->query("DELETE FROM remisepro_site WHERE remiseid = '$remiseid'");
				$DB_site->query("DELETE FROM remiseprocategorie WHERE remiseid = '$remiseid'");
				$DB_site->query("DELETE FROM remisepro WHERE remiseid = '$remiseid'");
				header("location: remises_gros.php?catid=$catid#remises_gros_pro");
			}else{
				$DB_site->query("DELETE FROM remise_site WHERE remiseid = '$remiseid'");
				$DB_site->query("DELETE FROM remisecategorie WHERE remiseid = '$remiseid'");
				$DB_site->query("DELETE FROM remise WHERE remiseid = '$remiseid'");
				header("location: remises_gros.php?catid=$catid");
			}
		}else{
			if(isset($module) and $module == "pro"){
				$DB_site->query("DELETE FROM remisepro_site WHERE remiseid = '$remiseid'");
				$DB_site->query("DELETE FROM remisepro WHERE remiseid = '$remiseid'");
				header("location: remises_gros.php#remises_gros_pro");
			}else{	
				$DB_site->query("DELETE FROM remise_site WHERE remiseid = '$remiseid'");
				$DB_site->query("DELETE FROM remise WHERE remiseid = '$remiseid'");
				header("location: remises_gros.php");
			}
		}
	}else{
		header('location: remises_gros.php?erreurdroits=1');	
	}
}

// Ajout d'une remise de gros
if(isset($action) and $action == "ajouter"){
	if($admin_droit[$scriptcourant][ecriture]){
		// Ajout de remises de gros pour une catégorie
		if(isset($catid)){
			// Ajout d'une remise de gros pro pour une catégorie
			if(isset($module) and $module == "pro"){
				if ($valeur > 0 && $pctremise > 0){
					$remise = $DB_site->query_first("SELECT * FROM remisepro INNER JOIN remisepro_site USING(remiseid) INNER JOIN remiseprocategorie USING(remiseid) WHERE catid = '$catid' AND valeur = '" . securiserSql($valeur) . "' AND pctremise = '" . securiserSql($pctremise) . "' AND siteid = '1'");
					if (!$remise[remiseid]){
						$DB_site->query("INSERT INTO remisepro(remiseid, typeremise, actif, cumul) VALUES ('', '" . ($typeremise ? 1 : 0) . "', '1', '1')");
						$remiseid = $DB_site->insert_id();
						$DB_site->query("INSERT INTO remiseprocategorie(catid, remiseid) VALUES ('$catid', '$remiseid')");
						$DB_site->query("INSERT INTO remisepro_site(remiseid, siteid, valeur, pctremise) VALUES ('$remiseid', '1', '" . securiserSql($valeur) . "', '" . securiserSql($pctremise) . "')");
					}
				}
				$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
				while ($site = $DB_site->fetch_array($sites)){
					$valeursite = ${"valeur" . $site[siteid]};
					$pctremisesite = ${"pctremise" . $site[siteid]};
					if ($valeursite > 0 && $pctremisesite > 0){
						$remise = $DB_site->query_first("SELECT * FROM remisepro INNER JOIN remisepro_site USING(remiseid) INNER JOIN remiseprocategorie USING(remiseid) WHERE catid = '$catid' AND valeur = '" . securiserSql($valeursite) . "' AND pctremise = '" . securiserSql($pctremisesite) . "' AND siteid = '$site[siteid]'");
						if (!$remise[remiseid]){
							$DB_site->query("INSERT INTO remisepro(remiseid, typeremise, actif, cumul) VALUES ('', '" . (${"typeremise" . $site[siteid]} ? 1 : 0) . "', '1', '1')");
							$remiseid = $DB_site->insert_id();
							$DB_site->query("INSERT INTO remiseprocategorie(catid, remiseid) VALUES ('$catid', '$remiseid')");
							$DB_site->query("INSERT INTO remisepro_site(remiseid, siteid, valeur, pctremise) VALUES ('$remiseid', '$site[siteid]', '" . securiserSql($valeursite) . "', '" . securiserSql($pctremisesite) . "')");
						}
					}
				}
				header("location: remises_gros.php?catid=$catid#remises_gros_pro");
			// Ajout d'une remise de gros pour une catégorie
			}else{
				if ($valeur > 0 && $pctremise > 0){
					$remise = $DB_site->query_first("SELECT * FROM remise INNER JOIN remise_site USING(remiseid) INNER JOIN remisecategorie USING(remiseid) WHERE catid = '$catid' AND valeur = '" . securiserSql($valeur) . "' AND pctremise = '" . securiserSql($pctremise) . "' AND siteid = '1'");
					if (!$remise[remiseid]){
						$DB_site->query("INSERT INTO remise(remiseid, typeremise, actif, cumul) VALUES ('', '" . ($typeremise ? 1 : 0) . "', '1', '1')");
						$remiseid = $DB_site->insert_id();
						$DB_site->query("INSERT INTO remisecategorie(catid, remiseid) VALUES ('$catid', '$remiseid')");
						$DB_site->query("INSERT INTO remise_site(remiseid, siteid, valeur, pctremise) VALUES ('$remiseid', '1', '" . securiserSql($valeur) . "', '" . securiserSql($pctremise) . "')");
					}
				}
				$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
				while ($site = $DB_site->fetch_array($sites)){
					$valeursite = ${"valeur" . $site[siteid]};
					$pctremisesite = ${"pctremise" . $site[siteid]};
					if ($valeursite > 0 && $pctremisesite > 0){
						$remise = $DB_site->query_first("SELECT * FROM remise INNER JOIN remise_site USING(remiseid) INNER JOIN remisecategorie USING(remiseid) WHERE catid = '$catid' AND valeur = '" . securiserSql($valeursite) . "' AND pctremise = '" . securiserSql($pctremisesite) . "' AND siteid = '$site[siteid]'");
						if (!$remise[remiseid]){
							$DB_site->query("INSERT INTO remise(remiseid, typeremise, actif, cumul) VALUES ('', '" . (${"typeremise" . $site[siteid]} ? 1 : 0) . "', '1', '1')");
							$remiseid = $DB_site->insert_id();
							$DB_site->query("INSERT INTO remisecategorie(catid, remiseid) VALUES ('$catid', '$remiseid')");
							$DB_site->query("INSERT INTO remise_site(remiseid, siteid, valeur, pctremise) VALUES ('$remiseid', '$site[siteid]', '" . securiserSql($valeursite) . "', '" . securiserSql($pctremisesite) . "')");
						}
					}
				}
				header("location: remises_gros.php?catid=$catid");
			}
		// Ajout de remises de gros pour le catalogue
		}else{
			// Ajout d'une remise de gros pro pour le catalogue
			if(isset($module) and $module == "pro"){
				if ($valeur > 0 && $pctremise > 0){
					$remise = $DB_site->query_first("SELECT  r.remiseid, r.typeremise,rs.siteid, rs.valeur, rs.pctremise FROM remise AS r
												INNER JOIN remisepro_site AS rs ON r.remiseid=rs.remiseid
												LEFT JOIN remiseproarticle AS ra ON r.remiseid=ra.remiseid
												LEFT JOIN remiseprocategorie AS rg ON r.remiseid=rg.remiseid
												WHERE ra.remiseid IS NULL AND rg.remiseid IS NULL AND valeur = '" . securiserSql($valeur) . "' AND pctremise = '" . securiserSql($pctremise) . "' AND siteid = '1'");
					if (!$remise[remiseid]){
						$DB_site->query("INSERT INTO remisepro(remiseid, typeremise, actif, cumul) VALUES ('', '" . ($typeremise ? 1 : 0) . "', '1', '1')");
						$remiseid = $DB_site->insert_id();
						$DB_site->query("INSERT INTO remisepro_site(remiseid, siteid, valeur, pctremise) VALUES ('$remiseid', '1', '" . securiserSql($valeur) . "', '" . securiserSql($pctremise) . "')");
					}
				}
				$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
				while ($site = $DB_site->fetch_array($sites)){
					$valeursite = ${"valeur" . $site[siteid]};
					$pctremisesite = ${"pctremise" . $site[siteid]};
					if ($valeursite > 0 && $pctremisesite > 0){
						$remise = $DB_site->query_first("SELECT  r.remiseid, r.typeremise,rs.siteid, rs.valeur, rs.pctremise FROM remise AS r
												INNER JOIN remisepro_site AS rs ON r.remiseid=rs.remiseid
												LEFT JOIN remiseproarticle AS ra ON r.remiseid=ra.remiseid
												LEFT JOIN remiseprocategorie AS rg ON r.remiseid=rg.remiseid
												WHERE ra.remiseid IS NULL AND rg.remiseid IS NULL AND valeur = '" . securiserSql($valeursite) . "' AND pctremise = '" . securiserSql($pctremisesite) . "' AND siteid = '$site[siteid]'");
						if (!$remise[remiseid]){
							$DB_site->query("INSERT INTO remisepro(remiseid, typeremise, actif, cumul) VALUES ('', '" . (${"typeremise" . $site[siteid]} ? 1 : 0) . "', '1', '1')");
							$remiseid = $DB_site->insert_id();
							$DB_site->query("INSERT INTO remisepro_site(remiseid, siteid, valeur, pctremise) VALUES ('$remiseid', '$site[siteid]', '" . securiserSql($valeursite) . "', '" . securiserSql($pctremisesite) . "')");
						}
					}
				}
				header("location: remises_gros.php#remises_gros_pro");
			// Ajout d'une remise de gros pour le catalogue
			}else{
				if ($valeur > 0 && $pctremise > 0){
					$remise = $DB_site->query_first("SELECT  r.remiseid, r.typeremise,rs.siteid, rs.valeur, rs.pctremise FROM remise AS r
												INNER JOIN remise_site AS rs ON r.remiseid=rs.remiseid
												LEFT JOIN remisearticle AS ra ON r.remiseid=ra.remiseid
												LEFT JOIN remisecategorie AS rg ON r.remiseid=rg.remiseid
												WHERE ra.remiseid IS NULL AND rg.remiseid IS NULL AND valeur = '" . securiserSql($valeur) . "' AND pctremise = '" . securiserSql($pctremise) . "' AND siteid = '1'");
					if (!$remise[remiseid]){
						$DB_site->query("INSERT INTO remise(remiseid, typeremise, actif, cumul) VALUES ('', '" . ($typeremise ? 1 : 0) . "', '1', '1')");
						$remiseid = $DB_site->insert_id();
						$DB_site->query("INSERT INTO remise_site(remiseid, siteid, valeur, pctremise) VALUES ('$remiseid', '1', '" . securiserSql($valeur) . "', '" . securiserSql($pctremise) . "')");
					}
				}
				$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
				while ($site = $DB_site->fetch_array($sites)){
					$valeursite = ${"valeur" . $site[siteid]};
					$pctremisesite = ${"pctremise" . $site[siteid]};
					if ($valeursite > 0 && $pctremisesite > 0){
						$remise = $DB_site->query_first("SELECT  r.remiseid, r.typeremise,rs.siteid, rs.valeur, rs.pctremise FROM remise AS r
												INNER JOIN remise_site AS rs ON r.remiseid=rs.remiseid
												LEFT JOIN remisearticle AS ra ON r.remiseid=ra.remiseid
												LEFT JOIN remisecategorie AS rg ON r.remiseid=rg.remiseid
												WHERE ra.remiseid IS NULL AND rg.remiseid IS NULL AND valeur = '" . securiserSql($valeursite) . "' AND pctremise = '" . securiserSql($pctremisesite) . "' AND siteid = '$site[siteid]'");
						if (!$remise[remiseid]){
							$DB_site->query("INSERT INTO remise(remiseid, typeremise, actif, cumul) VALUES ('', '" . (${"typeremise" . $site[siteid]} ? 1 : 0) . "', '1', '1')");
							$remiseid = $DB_site->insert_id();
							$DB_site->query("INSERT INTO remise_site(remiseid, siteid, valeur, pctremise) VALUES ('$remiseid', '$site[siteid]', '" . securiserSql($valeursite) . "', '" . securiserSql($pctremisesite) . "')");
						}
					}				
				}			
				header("location: remises_gros.php");
			}
			
		}
	}else{
		header('location: remises_gros.php?erreurdroits=1');	
	}
}


if(!isset($action) or $action == ""){
	if(isset($catid)){
		// Affichage de la catégorie dans le fil d'ariane
		$categorie = $DB_site->query_first("SELECT libelle FROM categorie_site INNER JOIN categorie USING(catid) WHERE catid=$catid");
		$libNavigSuppCategorie = "$multilangue[pour_la_categorie] : <i><b>\"$categorie[libelle]\"</b></i>";
		eval(charge_template($langue, $referencepage, "NavigSuppCategorie"));
		
		// Affichage des remises de gros pro pour une catégorie
		if ((in_array("3", $modules) && in_array("122", $modules)) || $mode == "test_modules"){
			
			// Site principal
			$siteprincipal = $DB_site->query_first("SELECT * FROM site WHERE siteid = '1'");
			$remises = $DB_site->query("SELECT * FROM remisepro INNER JOIN remisepro_site USING(remiseid) INNER JOIN remiseprocategorie USING(remiseid) WHERE catid = '$catid' AND siteid = '1' ORDER BY valeur");
			if ($DB_site->num_rows($remises)){
				while ($remise = $DB_site->fetch_array($remises)){
					$typeremise = ($remise[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
					$typeremisevaleur = ($remise[typeremise] ? $tabsites[1][devise_complete] : "$multilangue[articles]");
					eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosCategorieProBit"));
				}
				$remise = $DB_site->query_first("SELECT * FROM remisepro INNER JOIN remisepro_site USING(remiseid) INNER JOIN remiseprocategorie USING(remiseid) WHERE catid = '$catid' AND siteid = '1'");
				
				$display_articles_principal = $display_devise_principal = "";
				if($remise[typeremise]){
					$display_articles_principal="style=\"display:none;\"";
				}else{
					$display_devise_principal="style=\"display:none;\"";
				}
				
				$typeremise = ($remise[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
				//$typeremisevaleur = ($remise[typeremise] ? $tabsites[1][devise_complete] : "$multilangue[articles]");
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosCategorieProTypeExistant"));
			}else{
				$display_articles_principal = $display_devise_principal = "";
				$display_articles_principal="style=\"display:none;\"";
				$typeremise = $multilangue[sur_le_prix];
				$typeremisevaleur = $tabsites[1][devise_complete];
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosCategorieProType"));
			}
			
			// Autres sites
			$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
			$validatejscataloguecategoriepro = "";
			while ($site = $DB_site->fetch_array($sites)){
				$TemplateRemises_grosModificationFormulaireRemisesGrosCategorieProSiteBit="";
				$TemplateRemises_grosModificationFormulaireRemisesGrosCategorieProSiteType="";
				
				$devise_site_actuel=$tabsites[$site[siteid]][devise_complete];
				
				$remisessite = $DB_site->query("SELECT * FROM remisepro INNER JOIN remisepro_site USING(remiseid) INNER JOIN remiseprocategorie USING(remiseid) WHERE catid = '$catid' AND siteid = '$site[siteid]' ORDER BY valeur");
				if ($DB_site->num_rows($remisessite)){
					while ($remisesite = $DB_site->fetch_array($remisessite)){
						$typeremisesite = ($remisesite[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
						$typeremisesitevaleur = ($remisesite[typeremise] ? $tabsites[$site[siteid]][devise_complete] : "$multilangue[articles]");
						eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosCategorieProSiteBit"));
					}
					$remisesite = $DB_site->query_first("SELECT * FROM remisepro INNER JOIN remisepro_site USING(remiseid) INNER JOIN remiseprocategorie USING(remiseid) WHERE catid = '$catid' AND siteid = '$site[siteid]'");
					
					$display_articles = $display_devise = "";
					if($remisesite[typeremise]){
						$display_articles="style=\"display:none;\"";
					}else{
						$display_devise="style=\"display:none;\"";
					}
					
					$typeremisesite = ($remisesite[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
					eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosCategorieProSiteExistant"));
				}else{
					$display_articles = $display_devise = "";
					$display_articles="style=\"display:none;\"";
					$typeremisesite = $multilangue[sur_le_prix];
					$typeremisesitevaleur = $tabsites[$site[siteid]][devise_complete];
					eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosCategorieProSiteType"));
				}
				$validatejscataloguecategoriepro.=",valeur$site[siteid]: {
															min: 1
														},
															pctremise$site[siteid]: {
															range: [0,100]
														}";
				
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosCategorieProSite"));
			}
			
			eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosCategoriePro"));
		}
		
		// Affichage des remises de gros pour une catégorie
		// Site principal
		$siteprincipal = $DB_site->query_first("SELECT * FROM site WHERE siteid = '1'");
		$remises = $DB_site->query("SELECT * FROM remise INNER JOIN remise_site USING(remiseid) INNER JOIN remisecategorie USING(remiseid) WHERE catid = '$catid' AND siteid = '1' ORDER BY valeur");
		if ($DB_site->num_rows($remises)){
			while ($remise = $DB_site->fetch_array($remises)){
				$typeremise = ($remise[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
				$typeremisevaleur = ($remise[typeremise] ? $tabsites[1][devise_complete] : "$multilangue[articles]");
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosCategorieBit"));
			}
			$remise = $DB_site->query_first("SELECT * FROM remise INNER JOIN remise_site USING(remiseid) INNER JOIN remisecategorie USING(remiseid) WHERE catid = '$catid' AND siteid = '1'");
			
			$display_articles_principal = $display_devise_principal = "";
			if($remise[typeremise]){
				$display_articles_principal="style=\"display:none;\"";
			}else{
				$display_devise_principal="style=\"display:none;\"";
			}
			
			$typeremise = ($remise[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
			//$typeremisevaleur = ($remise[typeremise] ? $tabsites[1][devise_complete] : "$multilangue[articles]");
			eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosCategorieTypeExistant"));
		}else{
			$display_articles_principal = $display_devise_principal = "";
			$display_articles_principal="style=\"display:none;\"";
			$typeremise = $multilangue[sur_le_prix];
			$typeremisevaleur = $tabsites[1][devise_complete];
			eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosCategorieType"));
		}
		
		// Autres sites
		$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
		$validatejscataloguecategorie = "";
		while ($site = $DB_site->fetch_array($sites)){
			$TemplateRemises_grosModificationFormulaireRemisesGrosCategorieSiteBit="";
			$TemplateRemises_grosModificationFormulaireRemisesGrosCategorieSiteType="";
			
			$devise_site_actuel=$tabsites[$site[siteid]][devise_complete];
				
			$remisessite = $DB_site->query("SELECT * FROM remise INNER JOIN remise_site USING(remiseid) INNER JOIN remisecategorie USING(remiseid) WHERE catid = '$catid' AND siteid = '$site[siteid]' ORDER BY valeur");
			if ($DB_site->num_rows($remisessite)){
				while ($remisesite = $DB_site->fetch_array($remisessite)){
					$typeremisesite = ($remisesite[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
					$typeremisesitevaleur = ($remisesite[typeremise] ? $tabsites[$site[siteid]][devise_complete] : "$multilangue[articles]");
					eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosCategorieSiteBit"));
				}
				$remisesite = $DB_site->query_first("SELECT * FROM remise INNER JOIN remise_site USING(remiseid) INNER JOIN remisecategorie USING(remiseid) WHERE catid = '$catid' AND siteid = '$site[siteid]'");
				
				$display_articles = $display_devise = "";
				if($remisesite[typeremise]){
					$display_articles="style=\"display:none;\"";
				}else{
					$display_devise="style=\"display:none;\"";
				}
				
				$typeremisesite = ($remisesite[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosCategorieSiteExistant"));
			}else{
				$display_articles = $display_devise = "";
				$display_articles="style=\"display:none;\"";
				$typeremisesite = $multilangue[sur_le_prix];
				$typeremisesitevaleur = $tabsites[$site[siteid]][devise_complete];
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosCategorieSiteType"));
			}
			$validatejscataloguecategorie.=",valeur$site[siteid]: {
													min: 1
												},
													pctremise$site[siteid]: {
													range: [0,100]
												}";
			eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosCategorieSite"));
		}

		eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosCategorie"));
	}else{
		
		// Affichage des remises de gros pro
		if ((in_array("3", $modules) && in_array("122", $modules)) || $mode == "test_modules"){
			// Site principal
			$siteprincipal = $DB_site->query_first("SELECT * FROM site WHERE siteid = '1'");
			$remises = $DB_site->query("SELECT r.remiseid, r.typeremise,rs.siteid, rs.valeur, rs.pctremise FROM remisepro AS r INNER JOIN remisepro_site AS rs ON r.remiseid=rs.remiseid
									LEFT JOIN remiseproarticle AS ra ON r.remiseid=ra.remiseid
									LEFT JOIN remiseprocategorie AS rg ON r.remiseid=rg.remiseid
									WHERE ra.remiseid IS null AND rg.remiseid IS null AND siteid = '1' ORDER BY valeur");
			if ($DB_site->num_rows($remises)){
				while ($remise = $DB_site->fetch_array($remises)){
					$typeremise = ($remise[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
					$typeremisevaleur = ($remise[typeremise] ? $tabsites[1][devise_complete] : "$multilangue[articles]");
					eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosProBit"));
				}
				$remise = $DB_site->query_first("SELECT r.remiseid, r.typeremise,rs.siteid, rs.valeur, rs.pctremise FROM remisepro AS r
											INNER JOIN remisepro_site AS rs ON r.remiseid=rs.remiseid
											LEFT JOIN remiseproarticle AS ra ON r.remiseid=ra.remiseid
											LEFT JOIN remiseprocategorie AS rg ON r.remiseid=rg.remiseid
											WHERE ra.remiseid IS null AND rg.remiseid IS null AND siteid = '1'");
				
				$display_articles_principal = $display_devise_principal = "";
				if($remise[typeremise]){
					$display_articles_principal="style=\"display:none;\"";
				}else{
					$display_devise_principal="style=\"display:none;\"";
				}
				
				$typeremise = ($remise[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
				//$typeremisevaleur = ($remise[typeremise] ? $tabsites[1][devise_complete] : "$multilangue[articles]");
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosProTypeExistant"));
			}else{
				$display_articles_principal = $display_devise_principal = "";
				$display_articles_principal="style=\"display:none;\"";
				$typeremise = $multilangue[sur_le_prix];
				$typeremisevaleur = $tabsites[1][devise_complete];
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosProType"));
			}
			
			// Autres sites
			$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
			$validatejscataloguepro="";
			while ($site = $DB_site->fetch_array($sites)){
				$TemplateRemises_grosModificationFormulaireRemisesGrosProSiteBit="";			
				$TemplateRemises_grosModificationFormulaireRemisesGrosProSiteType="";
				
				$devise_site_actuel=$tabsites[$site[siteid]][devise_complete];
								
				$remisessite = $DB_site->query("SELECT r.remiseid, r.typeremise, rs.siteid, rs.valeur, rs.pctremise FROM remisepro AS r
											INNER JOIN remisepro_site AS rs ON r.remiseid=rs.remiseid
											LEFT JOIN remiseproarticle AS ra ON r.remiseid=ra.remiseid
											LEFT JOIN remiseprocategorie AS rg ON r.remiseid=rg.remiseid
											WHERE ra.remiseid IS null AND rg.remiseid IS null AND siteid = '$site[siteid]' ORDER BY valeur");
				if ($DB_site->num_rows($remisessite)){
					while ($remisesite = $DB_site->fetch_array($remisessite)){
						$typeremisesite = ($remisesite[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
						$typeremisesitevaleur = ($remisesite[typeremise] ? $tabsites[$site[siteid]][devise_complete] : "$multilangue[articles]");
						eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosProSiteBit"));
					}
					$remisesite = $DB_site->query_first("SELECT  r.remiseid, r.typeremise, rs.siteid, rs.valeur, rs.pctremise FROM remisepro AS r
													INNER JOIN remisepro_site AS rs ON r.remiseid=rs.remiseid
													LEFT JOIN remiseproarticle AS ra ON r.remiseid=ra.remiseid
													LEFT JOIN remiseprocategorie AS rg ON r.remiseid=rg.remiseid
													WHERE ra.remiseid IS null AND rg.remiseid IS null AND siteid = '$site[siteid]'");
					
					$display_articles = $display_devise = "";
					if($remisesite[typeremise]){
						$display_articles="style=\"display:none;\"";
					}else{
						$display_devise="style=\"display:none;\"";
					}
					
					$typeremisesite = ($remisesite[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
					eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosProSiteExistant"));
				}else{
					$display_articles = $display_devise = "";
					$display_articles="style=\"display:none;\"";
					$typeremisesite = $multilangue[sur_le_prix];
					$typeremisesitevaleur = $tabsites[$site[siteid]][devise_complete];
					eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosProSiteType"));
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosProSite"));
				$validatejscataloguepro.=",valeur$site[siteid]: {
												min: 1
											},
												pctremise$site[siteid]: {
												range: [0,100]
											}";
			}

			eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosPro"));
		}
		
		// Affichage des remises de gros
		// Site principal
		$siteprincipal = $DB_site->query_first("SELECT * FROM site WHERE siteid = '1'");		
		$remises = $DB_site->query("SELECT r.remiseid, r.typeremise,rs.siteid, rs.valeur, rs.pctremise FROM remise AS r INNER JOIN remise_site AS rs ON r.remiseid=rs.remiseid  
									LEFT JOIN remisearticle AS ra ON r.remiseid=ra.remiseid
									LEFT JOIN remisecategorie AS rg ON r.remiseid=rg.remiseid 
									WHERE ra.remiseid IS null AND rg.remiseid IS null AND siteid = '1' ORDER BY valeur");
		if ($DB_site->num_rows($remises)){
			while ($remise = $DB_site->fetch_array($remises)){			
				$typeremise = ($remise[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
				$typeremisevaleur = ($remise[typeremise] ? $tabsites[1][devise_complete] : "$multilangue[articles]");
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosBit"));
			}
			$remise = $DB_site->query_first("SELECT r.remiseid, r.typeremise,rs.siteid, rs.valeur, rs.pctremise FROM remise AS r 
											INNER JOIN remise_site AS rs ON r.remiseid=rs.remiseid  
											LEFT JOIN remisearticle AS ra ON r.remiseid=ra.remiseid
											LEFT JOIN remisecategorie AS rg ON r.remiseid=rg.remiseid 
											WHERE ra.remiseid IS null AND rg.remiseid IS null AND siteid = '1'");

			$display_articles_principal = $display_devise_principal = "";
			if($remise[typeremise]){
				$display_articles_principal="style=\"display:none;\"";
			}else{
				$display_devise_principal="style=\"display:none;\"";
			}
			
			$typeremise = ($remise[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
			//$typeremisevaleur = ($remise[typeremise] ? $tabsites[1][devise_complete] : "$multilangue[articles]");
			eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosTypeExistant"));
		}else{
			$display_articles_principal = $display_devise_principal = "";
			$display_articles_principal="style=\"display:none;\"";
			$typeremise = $multilangue[sur_le_prix];
			$typeremisevaleur = $tabsites[1][devise_complete];
			eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosType"));
		}
		
		
		// Autres sites
		$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
		$validatejscatalogue="";
		while ($site = $DB_site->fetch_array($sites)){
			$TemplateRemises_grosModificationFormulaireRemisesGrosSiteBit="";		
			$TemplateRemises_grosModificationFormulaireRemisesGrosSiteType="";
			
			$devise_site_actuel=$tabsites[$site[siteid]][devise_complete];
			
			$remisessite = $DB_site->query("SELECT r.remiseid, r.typeremise, rs.siteid, rs.valeur, rs.pctremise FROM remise AS r 
											INNER JOIN remise_site AS rs ON r.remiseid=rs.remiseid  
											LEFT JOIN remisearticle AS ra ON r.remiseid=ra.remiseid
											LEFT JOIN remisecategorie AS rg ON r.remiseid=rg.remiseid 
											WHERE ra.remiseid IS null AND rg.remiseid IS null AND siteid = '$site[siteid]' ORDER BY valeur");
			if ($DB_site->num_rows($remisessite)){
				while ($remisesite = $DB_site->fetch_array($remisessite)){
					$typeremisesite = ($remisesite[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
					$typeremisesitevaleur = ($remisesite[typeremise] ? $tabsites[$site[siteid]][devise_complete] : "$multilangue[articles]");
					eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosSiteBit"));
				}
				$remisesite = $DB_site->query_first("SELECT  r.remiseid, r.typeremise, rs.siteid, rs.valeur, rs.pctremise FROM remise AS r 
													INNER JOIN remise_site AS rs ON r.remiseid=rs.remiseid  
													LEFT JOIN remisearticle AS ra ON r.remiseid=ra.remiseid
													LEFT JOIN remisecategorie AS rg ON r.remiseid=rg.remiseid 
													WHERE ra.remiseid IS null AND rg.remiseid IS null AND siteid = '$site[siteid]'");
				$display_articles = $display_devise = "";
				if($remisesite[typeremise]){
					$display_articles="style=\"display:none;\"";					
				}else{
					$display_devise="style=\"display:none;\"";
				}

				$typeremisesite = ($remisesite[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);		
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosSiteTypeExistant"));
			}else{				
				$display_articles = $display_devise = "";
				$display_articles="style=\"display:none;\"";
				$typeremisesite = $multilangue[sur_le_prix];
				$typeremisesitevaleur = $tabsites[$site[siteid]][devise_complete];
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosSiteType"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosSite"));
			
			$validatejscatalogue.=",valeur$site[siteid]: {
										min: 1
									},
										pctremise$site[siteid]: {
										range: [0,100]
									}";
		}

		eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGros"));	
	}
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