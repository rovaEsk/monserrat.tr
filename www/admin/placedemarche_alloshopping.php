<?
$prov = "comp" ;
require "includes/admin_global.php";

if (!function_exists("cheminsanshref")) {
	function cheminsanshref($categorie, $DB) {
		global $libelle, $separateur_navigation, $titleFR, $host, $cheminsanshref ;
		$ss=$DB->query_first("SELECT count(catid) FROM categorie where parentid = '$categorie'");
		$categorie=$DB->query_first("SELECT parentid, $libelle, catid FROM categorie where catid = '$categorie'");
		$categorie[$libelle] = html_entity_decode(strip_tags($categorie[$libelle]));
		$categorie[$libelle] = str_replace("\n"," ",$categorie[$libelle]);
		$categorie[$libelle] = str_replace("\r"," ",$categorie[$libelle]);
		$categorie[$libelle] = trim(str_replace("\t"," ",$categorie[$libelle]));
		$cheminsanshref = "$separateur_navigation $categorie[$libelle] $cheminsanshref" ;
		if ($categorie[parentid] != 0)
			cheminsanshref($categorie[parentid], $DB) ;
		else
			$cheminsanshref = $titleFR." ".$cheminsanshref;
		//return $cheminsanshref ;
	}
}

$contenu="reference produit;titre produit;description produit;description technique;déclinaisons;marque;URL petite image;URL grande image;stock;délai livraison;prix TTC;frais de port TTC;TVA;catégories;ecotaxe\n";


$articles=$DB_site->query("SELECT * FROM article a inner join article_description ad on(a.artid = ad.artid) WHERE a.activeV1FR = '1' AND a.commandable = '1' and a.prix > 0");
while ($article=$DB_site->fetch_array($articles)) 
	{
	$article[description] = html_entity_decode(strip_tags($article[descriptionFR]));
	$article[description] = str_replace("\n"," ",$article[description]);
	$article[description] = str_replace("\r"," ",$article[description]);
	$article[description] = str_replace("\t"," ",$article[description]);
	$article[description]=str_replace(";"," ",$article[description]);
	$article[fichetechnique] = html_entity_decode(strip_tags($article[fichetechniqueFR]));
	$article[fichetechnique] = str_replace("\n"," ",$article[fichetechnique]);
	$article[fichetechnique] = str_replace("\r"," ",$article[fichetechnique]);
	$article[fichetechnique] = str_replace("\t"," ",$article[fichetechnique]);
	$article[fichetechnique]=str_replace(";"," ",$article[fichetechnique]);	
	$article[libelle] = html_entity_decode(strip_tags($article[libelleFR]));
	$article[libelle]=str_replace(";"," ",$article[libelle]);	
	//reference produit
	$contenu .= "$article[artcode];";
	// titre produit
	$contenu .= "$article[libelle];" ;
	// description produit
	$contenu .= "$article[description];";
	// description technique
	$contenu .= "$article[fichetechnique];";
	// déclinaisons
	$lignesStock = $DB_site->query("SELECT * FROM stocks WHERE artid = '$article[artid]'");
	if ($DB_site->num_rows($lignesStock)) {
		while ($ligneStock = $DB_site->fetch_array($lignesStock)) {
			$ligneStock[label] = "";
			$caractvals = $DB_site->query("SELECT cv.$libelle FROM caracteristiquevaleur cv INNER JOIN stocks_caractval sc ON (sc.caractvalid = cv.caractvalid) WHERE sc.stockid = '$ligneStock[stockid]'");
			while ($caractval = $DB_site->fetch_array($caractvals)) {
				$ligneStock[label] .= stripslashes($caractval[$libelle])." ";
			}
			$contenu .= "$ligneStock[label] : $ligneStock[total] / ";		
		}	
	}
	$contenu .= ";";
	// marque
	$article[marque] = "";
	$params[separateur_marques] = ", ";
	$article_marques=$DB_site->query("select $libelle from marque m, article_marque am where m.marqueid = am.marqueid and am.artid = '$article[artid]'") ;
	while ($article_marque=$DB_site->fetch_array($article_marques)) {
		$article[marque] .= $article_marque[$libelle] . $params[separateur_marques] ; // ici : rajouter les liens et autres de marque ...
	}
	$article[marque] = substr($article[marque], 0, -strlen($params[separateur_marques])) ;
	$contenu.="$article[marque];";
	// URL image
	if ($article[image] != "" && $article[image] != NULL)
		{
		// URL petite image
		$contenu .= "http://$host/ar-".url_rewrite($article[libelle])."-".$article[artid].".".$article[image].";";
		// URL grance image	
		$contenu .= "http://$host/ori-".url_rewrite($article[libelle])."-".$article[artid].".".$article[image].";";
		}
	else
		{
		// URL petite image
		$contenu .= ";";
		// URL grance image	
		$contenu .= ";";
		}
	// stock
	if (in_array("4",$modules)) 
		$stock = $DB_site->query_first("SELECT total FROM stocks WHERE artid = '$article[artid]' AND caractvalid = '0' AND parentid = '0'");
	else
		$stock[total] = 5;
	$contenu.="$stock[total];";
	// délai livraison
	$contenu .= "$article[delai] jours;";
	// prix TTC
	if (estEnPromo($DB_site, $article[artid])) 	
		$prixvente = formaterPrix($article[prix] * (1 - ($article[pctpromo] / 100)), 2, '.', '') ;
	else
		$prixvente = $article[prix] ;
	$prixvente = formaterPrix($prixvente, 2, ',', '');
	$contenu .= "$prixvente;";
	// frais de port TTC
	if ($params[type_tranches_port] == 0) // tranches de poids
		$valeuratester = $article[poids] ;
	else // tranches de prix
		$valeuratester = $article[prix] ;
	$tranche=$DB_site->query_first("select MIN(prix) as prix from fraisport where paysid = '57' and debut <= '$valeuratester' and fin >= '$valeuratester'");
	if ($tranche[prix] && $tranche[prix] != NULL)
		$article[prixport] = $tranche[prix] ;
	else{
		$tranche=$DB_site->query_first("select MIN(prix) as prix from fraisport where paysid = '57' and debut = '0' and fin = '0'");
		if ($tranche[prix] != NULL)
			$article[prixport] = $tranche[prix] ;
	}
	$contenu.=round($article[prixport]).";";
	// TVA
	$tvaFrance = $DB_site->query_first("SELECT TVAtauxnormal, TVAtauxreduit FROM pays WHERE paysid = '57'");
	switch ($article[tauxchoisi])
		{
		case "0":
			$taux = 0;
			break;
		case "1":
			$taux = $tvaFrance[TVAtauxnormal];
			break;
		case "2":
			$taux = $tvaFrance[TVAtauxreduit];
			break;
		}
	$contenu .= formaterPrix($taux, 2, ",", "").";";
	// categorie
	$separateur_navigation = " > ";
	$cheminsanshref="";
	cheminsanshref($article[catid],$DB_site);
	$contenu.=$cheminsanshref.";";		
	// ecotaxe
	$contenu .= formaterPrix($article[ecotaxe], 2, ",", "").";";
	$contenu .= "\n" ;
	}

if (!is_dir($rootpath."configurations/$host/exports")) {
	mkdir($rootpath."configurations/$host/exports",0777);
}
$filename = $rootpath."configurations/$host/exports/export_alloshopping.csv";
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
		echo "L'adresse de votre fichier d'export Alloshopping est la suivante : <a href=\"http://$host/configurations/$host/exports/export_alloshopping.csv\">http://$host/configurations/$host/exports/export_alloshopping.csv</a><br>";
	}
}

	
?>