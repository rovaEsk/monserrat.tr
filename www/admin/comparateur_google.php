<?
ini_set('memory_limit','128M');
ini_set('max_execution_time','0');
set_time_limit(0);

$prov = "comp" ;
if (!$rootpath)
	$rootpath = $_SERVER['DOCUMENT_ROOT'] ;
$nom_comparateur = "comparateur_google";

if(!$onestdansuncron){
	require_once $rootpath."admin/includes/admin_global.php";
} else {
//	require_once $rootpath."admin/includes/fonctions.php";
}

if (!function_exists("cheminsanshref")) {
	function cheminsanshref($categorie, $DB) {
		global $libelle, $separateur_navigation, $titleFR, $host, $cheminsanshref ;
		$ss=$DB->query_first("SELECT COUNT(catid) FROM categorie WHERE parentid = '$categorie'");
		$categorie=$DB->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$categorie'");
		$cheminsanshref = " $separateur_navigation $categorie[libelle] $cheminsanshref" ;
		if ($categorie[parentid] != 0)
			cheminsanshref($categorie[parentid], $DB) ;
		else
			$cheminsanshref = "$titleFR $cheminsanshref" ;
		return $cheminsanshref ;
		}
	}
	
if (!function_exists("cheminsanshref2")) {
	function cheminsanshref2($categorie, $DB) {
		global $libelle, $separateur_navigation, $titleFR, $host, $cheminsanshref ;
		$ss=$DB->query_first("SELECT COUNT(attributid) FROM googleshopping_attribut WHERE parentid = '$categorie'");
		$categorie=$DB->query_first("SELECT * FROM googleshopping_attribut where attributid = '$categorie'");
		$cheminsanshref = $categorie[libelle].$cheminsanshref;
		if ($categorie[parentid] != 0){
			$cheminsanshref = $separateur_navigation.$cheminsanshref ;
			cheminsanshref2($categorie[parentid], $DB) ;
		}
		return $cheminsanshref ;
		}
	}

/*if (!function_exists("supprcarspe_google")){
	function supprcarspe_google($chaine){	
		$chaine_finale = "";
		for ($i=0;$i<strlen($chaine);$i++){
			if (ord($chaine[i]) != 38 && ord($chaine[i]) != 60 && ord($chaine[i]) != 62 && ord($chaine[i]) != 146 && ord($chaine[i]) < 250 && ord($chaine[i]) > 31 && ord($chaine[i]) != 128 && ord($chaine[i]) != 156 && ord($chaine[i]) != 140 && ord($chaine[i]) != 148 && ord($chaine[i]) != 147 && ord($chaine[i]) != 150){
				$chaine_finale .= $chaine[i];
			}elseif (ord($chaine[i]) == 128){
				$chaine_finale .= "euro";
			}elseif (ord($chaine[i]) == 156){
				$chaine_finale .= "oe";
			}elseif (ord($chaine[i]) == 140){
				$chaine_finale .= "Oe";
			}elseif (ord($chaine[i]) == 38){
				$chaine_finale .= "et";
			}elseif (ord($chaine[i]) == 150){
				$chaine_finale .= "-";
			}
		}
		//$chaine_finale = str_replace('&amp;','et',$chaine_finale);
		//$chaine_finale = str_replace('& ','et ',$chaine_finale);
		return $chaine_finale;
	}
}*/

if (!function_exists("exporter_google")) {
	function exporter_google($siteid){	
		global $DB_site, $libelle,$host,$provenance,$regleurlrewrite,$cheminsanshref,$modules, $tab, $ajd, $nom_site, $description_site;
		$title = "title".$siteid;
		global ${$title};
		$liste_artid = "SELECT artid FROM article_comparateur WHERE comparateurid = '13' AND siteid = '$siteid' ORDER BY artid";
		$nom_site = $DB_site->query_first("SELECT * FROM meta INNER JOIN meta_site USING(metaid) WHERE scriptname='/index.php'");
		$nom_site = $nom_site[title];
		$nom_site = str_replace("[boutiquetitre]", ${$title}, $nom_site);
		$nom_site = secure_chaine_csv($nom_site);
		$description_site = $DB_site->query_first("SELECT * FROM meta INNER JOIN meta_site USING(metaid) WHERE scriptname='/index.php'");
		$description_site = $description_site[description];
		$description_site = str_replace("[boutiquetitre]", ${$title}, $description_site);
		$description_site = secure_chaine_csv($description_site);
		
		$contenu="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<channel>\n
			<title>".$nom_site."</title>\n<link>".$host."</link>\n
			<description>".$description_site."</description>\n";
		
		$where = "";
		if ($tab[exporter_non_visibles] == 0){
			$where .= "AND activeV1 = '1' ";
		}
		if ($tab[exporter_non_commandables] == 0){
			$where .= "AND commandable = '1' ";
		}
		if ($tab[exporter_cats_non_visibles] == 0){
			$aff_inactive = 0;
		}else{
			$aff_inactive = 1;	
		}
		
		if (isset($provenance) and $provenance == "selection"){
			$nom_fic = "export_google_selection".$siteid.".xml";
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE siteid='$siteid' AND prix > 0 AND artid IN ($liste_artid) $where");	
		}else{
			$nom_fic = "export_google".$siteid.".xml";
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE siteid='$siteid' AND prix > 0 $where ORDER BY artid");
		}
		$nbexport = 0;
		while ($article = $DB_site->fetch_array($articles)){
			
			if ($aff_inactive == 0){
				$export = 0;
				$categs = $DB_site->query("SELECT catid FROM position WHERE artid = '$article[artid]'");
				while($categ=$DB_site->fetch_array($categs)){
					$catid = $categ[catid];
					while ($catid != 0 && $export == 0){
						$categ2=$DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$catid' AND siteid = '$siteid'");
						if ($categ2[visible_treeviewV1] == 1)
							$export = 1;
						$catid = $categ2[parentid];
					}
				}
			}else{
				$export = 1;
			}
			$nbstock = retournerStockArticle($DB_site, $article[artid]);
			if ($tab[exporter_stock_epuise] == 0 && in_array(4, $modules) && $nbstock <= 0  && $article[stock_illimite] != 1){
				$export = 0;
				//echo "$article[artid] - $nbstock<br>";
			}

			if ($export){
				$nbexport++;
				//Marque
				$article[marque] = "";
				$tab[separateur_marques] = ", ";
				$article_marques = $DB_site->query("SELECT * FROM marque INNER JOIN marque_site USING ( marqueid ) INNER JOIN article_marque USING ( marqueid ) WHERE artid = '$article[artid]' AND siteid = '$siteid' LIMIT 1") ;
								while ($article_marque=$DB_site->fetch_array($article_marques))
					$article[marque] .= $article_marque[libelle] . $tab[separateur_marques] ;
				$article[marque] = substr($article[marque], 0, -strlen($tab[separateur_marques])) ;
				$marque = secure_chaine_csv($article[marque],0);
				$contenu.="<item>\n";
				if ($marque == ''){$marque = 'Noname';}
				$contenu.="<g:brand>".$marque."</g:brand>\n";	 // Teste si la marque est bien entrée et l'ajoute.
								
				
				$article[description] = secure_chaine_csv($article[description],1);
				$article[libelle] = secure_chaine_csv($article[libelle],1);
				$article[artcode] = secure_chaine_csv($article[artcode],1);
				$article[code_EAN] = secure_chaine_csv($article[code_EAN],1);
				$contenu.= "<title>".$article[libelle]."</title>\n";	
				$contenu.= "<g:mpn>".$article[artcode]."</g:mpn>\n";
				$contenu.= "<g:gtin>".$article[code_EAN]."</g:gtin>\n";
				if (estEnPromo($DB_site, $article[artid],0)) {
					$prix = $article[prix];
					$prixpromo = round($article[prix] - ($article[prix]*$article[pctpromo]/100),2);
					$prixVente = $prixpromo;
					$contenu.="<g:featured_product>o</g:featured_product>\n";
					$contenu.="<g:price>".$prix."</g:price>\n";
					$contenu.="<g:sale_price>".$prixpromo."</g:sale_price>\n";
				} else {
					if ($tab[exporter_prixpublic] == 1 && $article[prixpublic]){
						$prix = $article[prixpublic];
						$prixpromo = $article[prix];
						$prixVente = $prixpromo;
						$contenu.="<g:featured_product>o</g:featured_product>\n";
						$contenu.="<g:price>".$prix."</g:price>\n";
						$contenu.="<g:sale_price>".$prixpromo."</g:sale_price>\n";
					}else{
						$prix = $article[prix];
						$prixVente = $article[prix];
						$contenu.="<g:featured_product>n</g:featured_product>\n";
						$contenu.="<g:price>".$prix."</g:price>\n";
					}
					
				}
				
				
				$contenu.="<link>http://$host/".$regleurlrewrite[$siteid][article]."-".url_rewrite($article[libelle])."-".$article[artid].".htm</link>\n";		
				if ($article[description] == ""){
					$article[description] = "Pas de description pour cet article";
				}
			
				$contenu.="<description>".$article[description]."</description>\n";
				if ($article[image] != ""){
					$contenu.="<g:image_link>http://$host/ori-".url_rewrite($article[libelle])."-".$article[artid].".".$article[image]."</g:image_link>\n";						
				}else{
					$contenu.="<g:image_link></g:image_link>\n";
				}
					
				$article_photo=$DB_site->query_first("SELECT articlephotoid, image FROM articlephoto WHERE artid = '$article[artid]' ORDER BY articlephotoid LIMIT 1");
				if ($article_photo[image]){
					$contenu.="<g:additional_image_link>http://$host/ori-".url_rewrite($article[libelle])."-".$article[artid]."_".$article_photo[articlephotoid].".".$article_photo[image]."</g:additional_image_link>\n";
				}else{
					$contenu.="<g:additional_image_link></g:additional_image_link>\n";
				}
				
				
				$contenu.="<g:id>".$article[artid]."</g:id>\n";
				
				
				$top = array("artid" => $article[artid], "prix" => $prixVente, "poids" => $article[poids]);
				$topFp = trouve_articleFraisPort($DB_site, $top, "", "", 0) ;
				
				$fraisport = number_format($topFp[fraisport], 2, '.', '') ;
				
				$dimpays = $DB_site->query_first("SELECT diminutif FROM pays WHERE paysid = '$paysid'");
				$contenu .= "<g:shipping>\n";
					$contenu .= "<g:country>".$dimpays[diminutif]."</g:country>\n";
					$contenu .= "<g:price>".$fraisport."</g:price>\n";
				$contenu .= "</g:shipping>\n";
					
				if ($article[poids]){
					$contenu.="<g:shipping_weight>".$article[poids]." g</g:shipping_weight>\n";
				}else{
					$contenu.="<g:shipping_weight></g:shipping_weight>\n";
				}
				
				$contenu .= "<g:condition>new</g:condition>\n";
				if ($tab[exporter_stock_epuise] == 1 && $nbstock <= 0){
					$contenu .= "<g:availability>out of stock</g:availability>\n";
				}else{
					$contenu .= "<g:availability>in stock</g:availability>\n";
				}
				// Récupération des caractéristiques.
				
				$caractarts = $DB_site->query("SELECT * FROM stocks INNER JOIN stocks_caractval USING(stockid) WHERE artid = '$article[artid]'");
				$limite = 0;
				while($caractart = $DB_site->fetch_array($caractarts)){
					$car_valeurs = $DB_site->query("SELECT * FROM caracteristique_site INNER JOIN caracteristiquevaleur USING(caractid) INNER JOIN caracteristiquevaleur_site USING(caractvalid) WHERE caractvalid = '$caractart[caractvalid]'");
					$nbvals = $DB_site->num_rows($car_valeurs);
					if ($nbvals >= 2){
						while($car_valeur = $DB_site->fetch_array($car_valeurs)){
							if ($car_valeur[libelle] != "" && $car_valeur[libelle] != "" && $limite < 6){
								$car_valeur[libelle] = secure_chaine_csv($car_valeur[libelle],1);
								$contenu .= "<g:feature>".$car_valeur[libelle]." : ".$car_valeur[libelle]."</g:feature>\n";
								$limite++;
							}
						}
					}
				}
				
				//Product type
				$google_cat = $DB_site->query_first("SELECT attributid FROM googleshopping where artid ='$article[artid]'");	
				if ($google_cat[attributid]){
					$cheminsanshref = "";
					$separateur_navigation = "&gt;";
					cheminsanshref2($google_cat[attributid],$DB_site);
					$cheminsanshref = secure_chaine_csv($cheminsanshref,1);
					$contenu .= "<g:google_product_category><![CDATA[".$cheminsanshref."]]></g:google_product_category>\n";
				}
				
				// Récupération catégorie
				$cheminsanshref = "";
				$separateur_navigation = "&gt;";
				cheminsanshref($article[catid],$DB_site);
				$cheminsanshref = secure_chaine_csv($cheminsanshref,1);
				$contenu .= "<g:product_type>".$cheminsanshref."</g:product_type>\n";
				
				$contenu .= "</item>\n";
	
			}
		}
		echo $nbexport;
		$contenu.="</channel>\n";
		return $contenu;
	}
}

if(!$onestdansuncron){
	
	$sites = $DB_site->query("SELECT * FROM site");
	$comparateurid = $DB_site->query_first("SELECT comparateurid FROM comparateur WHERE fichier='".$nom_comparateur.".php'");
	
	while($site = $DB_site->fetch_array($sites)){
		$provenance = "";
		$contenu=exporter_google($site[siteid]);
		if (isset($provenance) and $provenance == "selection"){
			$nom_fic = "export_".$nom_comparateur."_selection".$site[siteid].".xml";
		}else{
			$nom_fic = "export_".$nom_comparateur.$site[siteid].".xml";
		}
		if (!is_dir($rootpath."configurations/$host/exports")) {
			mkdir($rootpath."configurations/$host/exports",0777);
		}
		$filename = $rootpath."configurations/$host/exports/".$nom_fic;
		if (!$handle = fopen($filename, 'w')) {
			echo "$multilangue[erreur_ouverture_fichier] ($filename)";
			exit;
		} else {
			if (fwrite($handle,$contenu) === FALSE) {
				echo "$multilangue[erreur_ecriture_fichier] ($filename)";
				fclose($handle);
				exit;
			} else {
				fclose($handle);
			}
		}
		
		$articleselection = $DB_site->query("SELECT * FROM article_comparateur WHERE siteid=$site[siteid] AND comparateurid=$comparateurid[comparateurid]");
		if($DB_site->num_rows($articleselection)){
			$provenance = "selection";
			$contenu=exporter_google($site[siteid]);
			if (isset($provenance) and $provenance == "selection"){
				$nom_fic = "export_".$nom_comparateur."_selection".$site[siteid].".xml";
			}else{
				$nom_fic = "export_".$nom_comparateur.$site[siteid].".xml";
			}
			if (!is_dir($rootpath."configurations/$host/exports")) {
				mkdir($rootpath."configurations/$host/exports",0777);
			}
			$filename = $rootpath."configurations/$host/exports/".$nom_fic;
			if (!$handle = fopen($filename, 'w')) {
				echo "$multilangue[erreur_ouverture_fichier] ($filename)";
				exit;
			} else {
				if (fwrite($handle,$contenu) === FALSE) {
					echo "$multilangue[erreur_ecriture_fichier] ($filename)";
					fclose($handle);
					exit;
				} else {
					fclose($handle);
				}
			}
		}
	}
}


/*
Modif de Julien le 15/11/2010 : ajout du module 5922 : export google shopping

*/


?>