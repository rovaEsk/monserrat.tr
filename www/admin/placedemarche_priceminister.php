<?
$prov = "comp" ;
require "includes/admin_global.php";

$contenu = "R&eacute;f&eacute;rence fabriquant;R&eacute;f&eacute;rence interne;Prix de vente;Quantit&eacute;;Qualit&eacute;;Commentaire annonce;URL Image 1;URL Image 2;Marque;Titre;Fiche Technique - Description;Type de Produit;Exp&eacute;dition, Enlevement;T&eacute;l&eacute;phone;Code postal;Pays";

$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE activeV1 = '1' AND commandable = '1' and prix > '0' AND siteid = '1'");
while ($article = $DB_site->fetch_array($articles)) {
	$article[description] = html_entity_decode(strip_tags($article[description]));
	$article[description] = str_replace("\n"," ",$article[description]);
	$article[description] = str_replace("\r"," ",$article[description]);
	$article[description] = str_replace("\t"," ",$article[description]);
	$article[description] = str_replace(";"," ",$article[description]);
	$article[libelle] = html_entity_decode(strip_tags($article[libelleFR]));
	$article[libelle] = str_replace(";"," ",$article[libelle]);
	$article[soustitre] = html_entity_decode(strip_tags($article[titre2FR]));
	$article[soustitre] = str_replace(";"," ",$article[soustitre]);	

	//image
	$article[urlimage] = "" ;
	if ($article[image] != "")
		$article[urlimage] = "http://$host/ar-".url_rewrite($article[libelle])."-".$article[artid].".".$article[image];	
	//image2
	$deuxiemeimage=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid = '$article[artid]' AND position = '1'");
	if ($deuxiemeimage[articlephotoid] != "")
		$article[urlimage2] = "http://$host/ar-".url_rewrite($article[libelle])."-".$article[artid]."_".$deuxiemeimage[articlephotoid].".".$deuxiemeimage[image];
	else
		$article[urlimage2] = "";
	
	//prix de vente TTC
	if (estEnPromo($DB_site, $article[artid])) 	
		$prixvente = formaterPrix($article[prix] * (1 - ($article[pctpromo] / 100)), 2, '.', '');
	else
		$prixvente = $article[prix];
	$prixvente = formaterPrix($prixvente, 2, ',', '');
		
	//debut de la ligne
	$contenu .= "\n";	
	// reference_fabricant
	$contenu .= "$article[artcode]";
	$contenu .= ";";
	// reference interne
	$contenu .= "$article[artcode]";
	$contenu .= ";";
	// prix de vente TTC
	$contenu .= "$prixvente";
	$contenu .= ";";
	// quantité
	if (in_array("4",$modules)) 
		$stock = $DB_site->query_first("SELECT SUM(total) total FROM stocks WHERE artid = '$article[artid]'");
	else
		$stock[total] = 100;
	$contenu .= "$stock[total]";
	$contenu .= ";";
	// qualité
	$contenu .= "N";
	$contenu .= ";";
	// Commentaire annonce
	$contenu .= "$article[soustitre]";
	$contenu .= ";";
	// url image 1
	$contenu .= "$article[urlimage]";
	$contenu .= ";";
	// url image 2
	$contenu .= "$article[urlimage2]";
	$contenu .= ";";
	// marque
	$article[marque] = "";
	$params[separateur_marques] = ", ";
	$article_marques = $DB_site->query("SELECT * FROM marque m, article_marque am WHERE m.marqueid = am.marqueid AND am.artid = '$article[artid]'");
	while ($article_marque=$DB_site->fetch_array($article_marques)){
		$article[marque]  .=  $article_marque[libelle] . $params[separateur_marques] ; // ici : rajouter les liens et autres de marque ...
	}
	$article[marque] = substr($article[marque], 0, -strlen($params[separateur_marques])) ;
	$contenu .= "$article[marque]";
	$contenu .= ";";
	// titre
	$contenu .= "$article[libelle]";
	$contenu .= ";";
	// fiche technique
	$contenu .= "$article[description]";
	$contenu .= ";";
	// type de produit
	$categ = $DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$article[catid]'");
	$categ[libelle] = html_entity_decode($categ[libelle]);
	$categ[libelle] = str_replace(";", " ", $categ[libelle]);			
	$contenu .= "$categ[libelle]";
	$contenu .= ";";
	// expéditon, enlèvement
	$contenu .= "EXP";
	$contenu .= ";";
	// telephone
	$contenu .= "+33(0)" . substr($params[telephone], 1);
	$contenu .= ";";
	// code postal
	$contenu .= "$params[cp]";
	$contenu .= ";";
	// pays
	$contenu .= "France";
}

if (!is_dir($rootpath."configurations/$host/exports")) {
	mkdir($rootpath."configurations/$host/exports",0777);
}
$filename = $rootpath."configurations/$host/exports/export_priceminister.csv";
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
		echo "L'adresse de votre fichier d'export PriceMinister est la suivante : <a href=\"http://$host/configurations/$host/exports/export_priceminister.csv\">http://$host/configurations/$host/exports/export_priceminister.csv</a><br>";
	}
}
	
?>