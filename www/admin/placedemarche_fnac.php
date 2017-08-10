<?
$prov = "comp" ;
require "includes/admin_global.php";

if (!function_exists("cheminsanshref")) {
	function cheminsanshref($categorie, $DB) {
		global $libelle, $separateur_navigation, $titleFR, $host, $cheminsanshref ;
		$ss=$DB->query_first("SELECT count(catid) FROM categorie where parentid = '$categorie'");
		$categorie=$DB->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$categorie'");
		$categorie[libelle] = html_entity_decode(strip_tags($categorie[libelle]));
		$categorie[libelle] = str_replace("\n"," ",$categorie[libelle]);
		$categorie[libelle] = str_replace("\r"," ",$categorie[libelle]);
		$categorie[libelle] = trim(str_replace("\t"," ",$categorie[libelle]));
		$cheminsanshref = "$separateur_navigation $categorie[libelle] $cheminsanshref" ;
		if ($categorie[parentid] != 0)
			cheminsanshref($categorie[parentid], $DB) ;
		else
			$cheminsanshref = $titleFR." ".$cheminsanshref;
	}
}

$contenu = "EAN;SKU PART;M&eacute;ta-Type article;Type article;Support ;Format;Arborescences r&eacute;f&eacute;rencement N1;Arborescences r&eacute;f&eacute;rencement N2;Arborescences r&eacute;f&eacute;rencement N3;Arborescences r&eacute;f&eacute;rencement N4;Arborescences r&eacute;f&eacute;rencement N5;Titre s&eacute;riel;Mots-cl&eacute;s;Titre;Description;Editeur;Constructeur / Marque;Taille;Coloris;Sexe;Date de sortie;Images principale;Hauteur;Longueur;Largeur;Poids;D&eacute;tails Techniques;Prix Public;Garantie";

$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE activeV1 = '1' AND commandable = '1' and prix > '0' AND siteid = '1'");
while ($article = $DB_site->fetch_array($articles))  {
	$article[description] = html_entity_decode(strip_tags($article[descriptionFR]));
	$article[description] = str_replace("\n"," ",$article[description]);
	$article[description] = str_replace("\r"," ",$article[description]);
	$article[description] = str_replace("\t"," ",$article[description]);
	$article[description] = str_replace(";"," ",$article[description]);
	$article[fichetechnique] = html_entity_decode(strip_tags($article[fichetechniqueFR]));
	$article[fichetechnique] = str_replace("\n"," ",$article[fichetechnique]);
	$article[fichetechnique] = str_replace("\r"," ",$article[fichetechnique]);
	$article[fichetechnique] = str_replace("\t"," ",$article[fichetechnique]);
	$article[fichetechnique] = str_replace(";"," ",$article[fichetechnique]);	
	$article[libelle] = html_entity_decode(strip_tags($article[libelleFR]));
	$article[libelle] = str_replace(";"," ",$article[libelle]);	
	$article[titre2] = html_entity_decode(strip_tags($article[titre2FR]));
	$article[titre2] = str_replace(";"," ",$article[titre2]);	
	$contenu .= "\n";
	//EAN
	$contenu .= "$article[code_EAN];";
	//SKU PART
	$contenu .= "$article[artcode];";
	//M&eacute;ta-Type article
	$contenu .= ";";
	//Type article
	$contenu .= "$article[titre2];";
	//Support
	$contenu .= ";";
	//Format
	$contenu .= ";";
	//Arborescences r&eacute;f&eacute;rencement N1
	$separateur_navigation = " > ";
	$cheminsanshref = "";
	cheminsanshref($article[catid], $DB_site);
	$contenu .= $cheminsanshref.";";
	//Arborescences r&eacute;f&eacute;rencement N2
	$contenu .= ";";
	//Arborescences r&eacute;f&eacute;rencement N3
	$contenu .= ";";
	//Arborescences r&eacute;f&eacute;rencement N4
	$contenu .= ";";
	//Arborescences r&eacute;f&eacute;rencement N5
	$contenu .= ";";
	//Titre
	$contenu .= ";";
	//Mos clés
	$contenu .= ";";
	//Titre
	$contenu .= "$article[libelle];";
	//Description
	$contenu .= "$article[description];";
	//Editeur
	$contenu .= ";";
	//Marque/Constructeur
	$article[marque] = "";
	$params[separateur_marques] = ", ";
	$article_marques = $DB_site->query("SELECT * FROM marque m, article_marque am WHERE m.marqueid = am.marqueid AND am.artid = '$article[artid]'");
	while ($article_marque=$DB_site->fetch_array($article_marques)) {
		$article[marque] .= $article_marque[libelle] . $params[separateur_marques];
	}
	$article[marque] = substr($article[marque], 0, -strlen($params[separateur_marques])) ;
	$contenu.="$article[marque];";
	//Taille
	$contenu .= ";";
	//Coloris
	$contenu .= ";";
	//Sexe
	$contenu .= ";";
	//Date sortie
	$contenu .= ";";
	//Image principale
	if ($article[image] != "" && $article[image] != NULL) {
		$contenu .= "http://$host/ori-".url_rewrite($article[libelle])."-".$article[artid].".".$article[image].";";
	} else {
		$contenu .= ";";
	}
	//Hauteur
	$contenu .= "$article[hauteur];";
	//Longueur
	$contenu .= "$article[longueur];";
	//Largeur
	$contenu .= "$article[largeur];";
	//Poids
	$contenu .= ";";
	//D�tails techniques
	$contenu .= "$article[fichetechnique];";
	//Prix public
	if (estEnPromo($DB_site, $article[artid])) 	
		$prixvente = formaterPrix($article[prix] * (1 - ($article[pctpromo] / 100)), 2, '.', '') ;
	else
		$prixvente = $article[prix] ;
	$prixvente = formaterPrix($prixvente, 2, ',', '');
	$contenu .= "$prixvente;";
	//Garantie
	$contenu .= "";
}

if (!is_dir($rootpath."configurations/$host/exports")) {
	mkdir($rootpath."configurations/$host/exports",0777);
}
$filename = $rootpath."configurations/$host/exports/export_fnac.csv";
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
		echo "L'adresse de votre fichier d'export Fnac est la suivante : <a href=\"http://$host/configurations/$host/exports/export_fnac.csv\">http://$host/configurations/$host/exports/export_fnac.csv</a><br>";
	}
}
	
?>