<?
$prov = "comp" ;
require "includes/admin_global.php";

$contenu="FUPID;SKU;id père;Language;MPN/ISBN;EAN13;Category Id;Id Segment;Id Marque;Titre;Sous titre;Description;Pays;Disponibilité;Prix HT;Eco Taxe;Code Etat;Frais livraison  HT;Frais livraison multiple HT;Site de vente;Description livraison;Prix HT Solde;Prix HT Bundle;Stock;Url du média;Principal;Type\n";

$ajd = date("Y-m-d");

$articles=$DB_site->query("SELECT * FROM article a inner join article_description ad on(a.artid = ad.artid) WHERE a.activeV1FR = '1' AND a.commandable = '1' and a.prix > 0");
while ($article=$DB_site->fetch_array($articles))
	{
	$info_pixmania = $DB_site->query_first("SELECT * FROM pixmania WHERE artid = '$article[artid]'");
	if ($info_pixmania[segmentID] != "" && $info_pixmania[marqueID] != "")
		{
		if (!in_array(4, $modules) || (retournerStockArticle($DB_site, $article[artid]) > 0))
			{
			$article[descriptionFR] = html_entity_decode(strip_tags($article[descriptionFR]));
			$article[descriptionFR] = str_replace("\n"," ",$article[descriptionFR]);
			$article[descriptionFR] = str_replace("\r"," ",$article[descriptionFR]);
			$article[descriptionFR] = str_replace("\t"," ",$article[descriptionFR]);
			$article[descriptionFR]=str_replace(";"," ",$article[descriptionFR]);
			$article[fichetechniqueFR] = html_entity_decode(strip_tags($article[fichetechniqueFR]));
			$article[fichetechniqueFR] = str_replace("\n"," ",$article[fichetechniqueFR]);
			$article[fichetechniqueFR] = str_replace("\r"," ",$article[fichetechniqueFR]);
			$article[fichetechniqueFR] = str_replace("\t"," ",$article[fichetechniqueFR]);
			$article[fichetechniqueFR]=str_replace(";"," ",$article[fichetechniqueFR]);
			
			$article[libelleFR] = html_entity_decode(strip_tags($article[libelleFR]));
			$article[titre2FR] = html_entity_decode(strip_tags($article[titre2FR]));	
			// FUPID
			$contenu .= ";" ;
			// SKU
			$contenu .= $article[artid].";" ;
			// id père
			$contenu .= ";" ;
			// Language
			$contenu .= "FR;" ;
			// MPN/ISBN
			$contenu .= $article[artcode].";" ;
			// EAN13
			$contenu .= $article[code_EAN].";" ;
			// Category Id
			$contenu .= ";" ;
			// Id Segment
			$contenu .= $info_pixmania[segmentID].";" ;
			// Id Marque
			$contenu .= $info_pixmania[marqueID].";" ;
			// Titre
			$contenu .= $article[libelleFR].";" ;
			// Sous titre
			//$contenu .= $article[titre2FR].";" ;
			$contenu .= ";" ;
			// Description
			$desc=$article[descriptionFR]."  ".$article[fichetechniqueFR];
			$description = substr($desc, 0, 4000) ;
			if ($description == "")
				$description="Pas de description pour cet article";
			$contenu .= $description.";";
			// Pays
			$contenu .= "FR;" ;
			// Disponibilité
			if ($article[delai] != "0")
				$contenu.=$article[delai].";";
			else
				$contenu.="2;";	
			// Prix HT
			$tvaFrance=$DB_site->query_first("SELECT TVAtauxnormal, TVAtauxreduit FROM pays WHERE paysid = '57'");
			switch ($article[tauxchoisi])
				{
				case "0" :
					$article[prixHT] = $article[prix];
					break;
				case "1" :
					$article[prixHT] = $article[prix] / (1 + ($tvaFrance[TVAtauxnormal] / 100));
					break;
				case "2" :
					$article[prixHT] = $article[prix] / (1 + ($tvaFrance[TVAtauxreduit] / 100));
					break;
				}
			$contenu.= formaterPrix($article[prixHT], 2, ",", "").";";	
			// Eco Taxe
			$contenu.="0;";	
			// Code Etat
			$contenu.="00000000;";	
			// Frais livraison  HT
			if ($params[type_tranches_port] == 0) // tranches de poids
				$valeuratester = $article[poids] ;
			else // tranches de prix
				$valeuratester = $article[prix] ;	
			$tranche=$DB_site->query_first("select MIN(prix) as prix from fraisport where paysid = '57' and debut <= '$valeuratester' and fin >= '$valeuratester'");
			if ($tranche[prix] && $tranche[prix] != NULL)
				$article[prixport] = $tranche[prix] ;
			else 
				{
				$tranche=$DB_site->query_first("select MIN(prix) as prix from fraisport where paysid = '57' and debut = '0' and fin = '0'");
				if ($tranche[prix] != NULL)
					$article[prixport] = $tranche[prix] ;
				}
			$article[prixportHT] = 	$article[prixport] / (1 + ($tvaFrance[TVAtauxnormal] / 100));
			$contenu.= formaterPrix($article[prixportHT], 2, ",", "").";";	
			// Frais livraison multiple HT
			$contenu .= ";";
			// Site de vente
			$contenu .= "1;";
			// Description livraison
			$contenu .= "Colissimo 2 jours;";
			// Prix HT Solde
			if (estEnPromo($DB_site, $article[artid])) 
				{	
				$article[prixremiseHT] = formaterPrix($article[prixHT] * (1 - ($article[pctpromo] / 100)), 2, ',', '') ;
				$article[promotionHT] = formaterPrix($article[prixHT] - $article[prixremiseHT], 2, ",", "") ;
				} 
			else
				{
				$article[prixremiseHT] = "" ;
				$article[promotionHT] = "" ;
				}
			$contenu.= $article[prixremiseHT].";";	
			// Prix HT Bundle
			$contenu.= formaterPrix($article[prixHT], 2, ",", "").";";	
			// Stock
			if (!in_array(4, $modules))
				$article[stock] = "5";
			else
				$article[stock] = retournerStockArticle($DB_site, $article[artid]);	
			$contenu.= $article[stock].";";	
			// Url du média
			if ($article[image] != "")
				$contenu.="http://$host/ar-".url_rewrite($article[libelleFR])."-".$article[artid].".".$article[image].";1;";							
			else
				$contenu.=";0;";
			// Principal
			//$contenu.= "1;";
			// Type
			$contenu.= "0\n" ;
			}
		}
	}

if (!is_dir($rootpath."configurations/$host/exports")) {
	mkdir($rootpath."configurations/$host/exports",0777);
}
$filename = $rootpath."configurations/$host/exports/export_pixmania.csv";
if (!$handle = fopen($filename, 'w')) {
	echo "Impossible d'ouvrir le fichier ($filename)";
	exit;
} else {
	if (fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE) {
		echo "Impossible d'écrire dans le fichier ($filename)";
		fclose($handle);
		exit;
	} else {
		fclose($handle);
		echo "L'adresse de votre fichier d'export Pixmania est la suivante : <a href=\"http://$host/configurations/$host/exports/export_pixmania.csv\">http://$host/configurations/$host/exports/export_pixmania.csv</a><br>";
	}
}
?>