<?php
include "./includes/header.php";

$referencepage="placedemarche_rueducommerce";
$pagetitle = "Places de marché - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

$class_menu_marketing_active = $class_menu_places_de_marche_active = "active";

if (isset($action) && $action == "MCID"){
	$libNavigSupp = $multilangue[liste_articles];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	eval(charge_template($langue,$referencepage,"MCID"));
}

if (isset($action) && $action == "attribut"){
	$libNavigSupp = $multilangue[liste_attributs];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	eval(charge_template($langue,$referencepage,"Attribut"));
}
if (isset($action) && $action == "generer"){
	if ($_POST[mcid] == ""){
		$rueducommerces = $DB_site->query("SELECT DISTINCT(MCID) FROM rueducommerce");
		$_POST[mcid] .= "1 ";
		while ($rueducommerce = $DB_site->fetch_array($rueducommerces)){
			$_POST[mcid] .= "OR MCID = '$rueducommerce[MCID]' ";
		}
		$tous = 1;
	}
	$contenu = "Identifiant produit; Denomination; Identifiant reference; Poids; Description; URL fiche produit; URL Photo1; Genre; MCID; Disponibilite du produit; Rétractation; Quantité; Delai de livraison; Unite delai de livraison; Delai d'expedition; Unite delai d'expedition; Frais de port; Prix TTC; Ecotaxe;Garantie";
	$attributs = $DB_site->query("SELECT * FROM rueducommerce_attribut WHERE MCID = '" . securiserSql($_POST[mcid]) . "' ORDER BY attributid");
	while ($attribut = $DB_site->fetch_array($attributs))
		$contenu .= ";".stripslashes($attribut[libelle]);
	$contenu .= "\n";
	$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) INNER JOIN rueducommerce USING(artid) WHERE activeV1 = '1' AND commandable = '1' AND prix > 0 AND MCID = '" . securiserSql($_POST[mcid]) . "' AND siteid = '1'");
	while ($article = $DB_site->fetch_array($articles)){
		if (!in_array(4, $modules) || (retournerStockArticle($DB_site, $article[artid]) > 0)){
			$article[description] = html_entity_decode(strip_tags($article[descriptionFR]));
			$article[description] = str_replace("\n"," ",$article[description]);
			$article[description] = str_replace("\r"," ",$article[description]);
			$article[description] = str_replace("\t"," ",$article[description]);
			$article[description] = str_replace("&bull;","- ",$article[description]);
			$article[description] = str_replace(";"," ",$article[description]);
			$article[fichetechnique] = html_entity_decode(strip_tags($article[fichetechniqueFR]));
			$article[fichetechnique] = str_replace("\n"," ",$article[fichetechnique]);
			$article[fichetechnique] = str_replace("\r"," ",$article[fichetechnique]);
			$article[fichetechnique] = str_replace("\t"," ",$article[fichetechnique]);
			$article[fichetechnique] = str_replace("&bull;","- ",$article[fichetechnique]);
			$article[fichetechnique] = str_replace(";"," ",$article[fichetechnique]);			
			$article[libelle] = html_entity_decode(strip_tags($article[libelleFR]));	
			// 1.identifiant_unique
			$contenu .= $article[artid].";" ;
			// 2.denomination
			$contenu .= $article[libelle].";" ;
			// 3.identifiant reference
			$contenu .= $article[artcode].";" ;
			//4.poids
			$contenu .= $article[poids].";" ;
			// 5.description
			$description = $article[description]." ".$article[fichetechnique] ;
			//$description = substr($article[description]." ".$article[fichetechnique], 0, 250) ;
			if ($description == "")
				$description="Pas de description pour cet article";
			$contenu .= $description.";";
			// 6.url fiche produit
			$contenu.="http://$host/".$regleurlrewrite[$langue][article]."-".url_rewrite($article[libelle])."-".$article[artid].".htm;";	
			// 7.url_image
			if ($article[image] != "")
				$contenu.="http://$host/ar-".url_rewrite($article[libelle])."-".$article[artid].".".$article[image].";";							
			else
				$contenu.=";";			
			// 8.genre
			$contenu .= "U;";
			// 9.MCID
			$contenu .= $info_rdc[MCID].";";
			// 10.disponibilite
			$contenu.="S;";
			//11.retractation
			$contenu.="Y;";
			//12.quantite
			if(in_array( "4", $modules))
				$contenu .= retournerStockArticle($DB_site, $article[artid]) . ";";
			else
				$contenu.="5;";			
			//13.delai de livraison
			$delivery = $DB_site->query_first("SELECT delai_livraison FROM stocks WHERE artid = '$article[artid]' AND parentid = '0' AND caractvalid = '0'");
			if ($delivery[delai_livraison] != "0" && $delivery[delai_livraison] != "")
				$contenu.=$delivery[delai_livraison].";";
			else
				$contenu.="2;";	
			//14.unite livraison
			$contenu.="D;";
			//15.delai d'expedition
			$contenu.="2;";
			//16.unite expedition
			$contenu.="D;";
			// 17.frais_de_livraison
			if ($params[type_tranches_port] == 0) // tranches de poids
				$valeuratester = $article[poids] ;
			else // tranches de prix
				$valeuratester = $article[prix] ;	
			$tranche = $DB_site->query_first("SELECT MIN(prix) prix FROM fraisport WHERE paysid = '57' AND debut <= '$valeuratester' AND fin >= '$valeuratester' AND modelivraisonid = '1'");
			if ($tranche[prix] && $tranche[prix] != NULL){
				$article[prixport] = $tranche[prix] ;
			}else{
				$tranche=$DB_site->query_first("SELECT MIN(prix) prix FROM fraisport WHERE paysid = '57' AND debut = '0' AND fin = '0' AND modelivraisonid = '1'");
				if ($tranche[prix] != NULL)
					$article[prixport] = $tranche[prix] ;
			}
			$article[prixport] = formaterPrix($article[prixport], 2, ",", "");
			$contenu.="$article[prixport];";
			//18.prix ttc
			if (estEnPromo($DB_site, $article[artid])){
				$article[prixremise] = formaterPrix($article[prix] * (1 - ($article[pctpromo] / 100)), 2, '.', '') ;
				$article[promotion] = formaterPrix($article[prix] - $article[prixremise], 2, ",", "") ;
			}else{
				$article[prixremise] = "" ;
				$article[promotion] = "" ;
			}
			$contenu .= formaterPrix($article[prix], 2, ",", "").";";
			//19.ecotaxe
			if(in_array( "5801", $modules ))  
				$contenu .= $article[ecotaxe].";";
			else
				$contenu .= "0;";
			//20.garantie
			$article[garantie] = str_replace(" de garantie", "", $article[garantie]);
			$contenu .= stripslashes($article[garantie]).";";
			$attributs = $DB_site->query("SELECT attributid FROM rueducommerce_attribut WHERE MCID = '" . securiserSql($_POST[mcid]) . "' ORDER BY attributid");
			while ($attribut = $DB_site->fetch_array($attributs)){
				$atributArt = $DB_site->query_first("SELECT valeur FROM article_attribut WHERE artid = '$article[artid]' AND attributid = '$attribut[attributid]'");
				$contenu .= stripslashes($atributArt[valeur]).";";
			}
			$contenu .= "\n" ;
		}
	}
	$_POST[mcid] = ($tous ? $multilangue[tous] : $_POST[mcid]);
	if (!is_dir($rootpath."configurations/$host/exports"))
		mkdir($rootpath."configurations/$host/exports",0777);
	$filename = $rootpath."configurations/$host/exports/export_rueducommerce_$_POST[mcid].csv";
	if (!$handle = fopen($filename, 'w')){
		echo "$multilangue[erreur_ouverture_fichier] ($filename)";
		exit;
	}else{
		if (fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE){
			echo "$multilangue[erreur_ecriture_fichier] ($filename)";
			fclose($handle);
			exit;
		}else{
			fclose($handle);
			header("location: ../configurations/$host/exports/export_rueducommerce_$_POST[mcid].csv");
		}
	}	
}	
		
if (!isset($action) or $action == ""){
	$rueducommerces = $DB_site->query("SELECT distinct(MCID) FROM rueducommerce ORDER BY MCID");
	while ($rueducommerce = $DB_site->fetch_array($rueducommerces))
		eval(charge_template($langue,$referencepage,"ListeBit"));
	construire_MCID($DB_site, 0);
	$libNavigSupp = $multilangue[selection_mcid];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	eval(charge_template($langue,$referencepage,"Liste"));
}

$TemplateIncludejavascript = eval(charge_template($langue, $referencepage,"Includejavascript"));
eval(charge_template($langue,"commun","header"));
eval(charge_template($langue,"commun","footer"));
eval(charge_template($langue,$referencepage,"index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();

?>