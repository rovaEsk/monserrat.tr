<?
ini_set('memory_limit','128M');
ini_set('max_execution_time','0');

set_time_limit(0);

$prov = "comp" ;
$nom_comparateur = "comparateur_lengow";
if (!$rootpath)
	$rootpath = $_SERVER['DOCUMENT_ROOT'] ;
if($onestdansuncron=="" or $onestdansuncron==0){
	require_once $rootpath."admin/includes/admin_global.php";
} else {
//	require_once $rootpath."admin/includes/fonctions.php";
}

if (!function_exists("lengowConstructArticle")) {
	function lengowConstructArticle($DB_site,$article, $siteid) {
		global $modules, $langue, $langueExport, $params, $libelle, $nom, $host, $description, $fichetechnique, $notreavis, $image, $garantie, $image2, $title, $titre2, $regleurlrewrite, $couleurid, $tailleid, $matiereid;
		
		
		//on cherche les carac
		$stocks = $DB_site->query("SELECT stockid, total FROM stocks WHERE artid = '$article[artid]'");
		$nbCombi = $DB_site->num_rows($stocks) ;
		$arrArticle = "";
		$typedeclinaison = "";
		
		if ($nbCombi >= 1){ // on a une ou plusieurs lignes
			$chaineCaracVal = array();
			$i = 0;
			while ($stock = $DB_site->fetch_array($stocks)){
				$stockid = $stock[stockid];
				$nbstock = $stock[total];
				//if ($params[exporter_stock_epuise] == 1 || (in_array(4, $modules) && $nbstock > 0)  || $article[stock_illimite] == 1){
					//on cherche les caracval
					$caracVals = $DB_site->query("SELECT *, cvs.libelle AS cvlib FROM stocks_caractval AS sc
							INNER JOIN caracteristiquevaleur AS cv USING(caractvalid) 
							INNER JOIN caracteristiquevaleur_site AS cvs ON cvs.caractvalid=cv.caractvalid AND cvs.siteid='$siteid'
							INNER JOIN caracteristique_site AS cs ON cv.caractid=cs.caractid AND cs.siteid='$siteid'
							WHERE stockid = '$stock[stockid]' 
							ORDER BY cv.caractid");
					
					$nbCaracVal = $DB_site->num_rows($caracVals) ;
					$chaineCaracVal = array();
					while ($caracVal=$DB_site->fetch_array($caracVals)){
						$chaineCaracVal[$caracVal[caractid]] = $caracVal[caractvalid].'_'.$caracVal[cvlib];
						if ($i == 0){
							$typedeclinaison .= " $caracVal[clib]";
						}
					}
					
					/*
					if($article[artid] == 11884){
						echo "SELECT *, cvs.libelle AS cvlib FROM stocks_caractval AS sc
						INNER JOIN caracteristiquevaleur AS cv USING(caractvalid)
						INNER JOIN caracteristiquevaleur_site AS cvs ON cvs.caractvalid=cv.caractvalid AND cvs.siteid='$siteid'
						INNER JOIN caracteristique_site AS cs ON cv.caractid=cs.caractid AND cs.siteid='$siteid'
						WHERE stockid = '$stock[stockid]'
						ORDER BY cv.caractid<br>"; 
						
						print_r($chaineCaracVal);
						exit;
					}*/
					
					if ($i == 0 && $nbCombi > 1){
						$arrArticle .= lengowConstructArticleTable($DB_site, $siteid,$article, array(), $typedeclinaison) ;	
					}
					//ajout de la ligne
					if ($i == 0 && $nbCombi == 1){
						$arrArticle .= lengowConstructArticleTable($DB_site, $siteid, $article, $chaineCaracVal, $typedeclinaison, $stockid, 1);
					}else{
						$arrArticle .= lengowConstructArticleTable($DB_site, $siteid, $article, $chaineCaracVal, $typedeclinaison, $stockid);
					}
					$i++;
				//}
			}
		}else{// on a 1 ligne
			//ajout de la ligne
			$arrArticle .= lengowConstructArticleTable($DB_site, $siteid, $article) ;
		}		
		return $arrArticle;
		
	}
}


if (!function_exists("lengowConstructArticleTable")) {
	function lengowConstructArticleTable ($DB_site, $siteid, $article, $chaineCarac = array(), $typedeclinaison = "", $stockid = 0, $artunique=0)	{
		global $libelle, $langueExport, $params, $nom, $description, $host, $fichetechnique, $notreavis, $image, $image2, $title, $titre2, $garantie, $regleurlrewrite, $couleurid, $tailleid, $matiereid;
		
		if ($params[export_minidesc_lengow]){
			$article[description]  = explode('</p>',$article[description]);
			$article[description] = $article[description][0];
		}
		$article[description] = secure_chaine_csv($article[description], 1);
		$article[libelle] = secure_chaine_csv($article[libelle], 1);
		$article[garantie] = secure_chaine_csv($article[garantie], 1);
		
		
		$categorie = $DB_site->query_first("SELECT * FROM categorie WHERE catid = '$article[catid]'");
		$categorie[libelle] = secure_chaine_csv($categorie[libelle], 1);
		
		$categorieParente = $DB_site->query_first("SELECT * FROM categorie WHERE catid = '$categorie[parentid]'");
		$categorieParente[libelle] = secure_chaine_csv($categorieParente[libelle], 1);
		
		$categorieSuperParente = $DB_site->query_first("SELECT * FROM categorie WHERE catid = '$categorieParente[parentid]'");
		$categorieSuperParente[libelle] = secure_chaine_csv($categorieSuperParente[libelle], 1);
		
		if (!$categorieSuperParente[catid]){
			if($categorie[parentid]==0){
				$categorieSuperParente[libelle] = "$categorie[libelle]";
				$categorieParente[libelle] = "";
				$categorie[libelle] = "";
			}else{
				$categorieSuperParente[$libelle] = $categorieParente[libelle];
				$categorieParente[libelle] = $categorie[libelle];
				$categorie[libelle] = $categorieSuperParente[libelle]." > ".$categorieParente[libelle];
			}
		}
		
		if (count($chaineCarac)){			
			$articleid = $article[artid];
			
			$couleur = explode('_',$chaineCarac[$couleurid]);
			$couleurvalid = $couleur[0];
			$couleur = $couleur[1];
			if ($couleur){
				$articleid .= '_'.$couleurvalid;
			}
			
			$taille = explode('_',$chaineCarac[$tailleid]);
			$taillevalid = $taille[0];
			$taille = $taille[1];
			if ($taille){
				$articleid .= '_'.$taillevalid;
			}
			
			$matiere = explode('_',$chaineCarac[$matiereid]);
			$matierevalid = $matiere[0];
			$matiere = $matiere[1];
			if ($matiere){
				$articleid .= '_'.$matierevalid;
			}
			
			//D�clinaisons
			$declinaisons = $DB_site->query("SELECT caractid FROM caracteristiquevaleur WHERE caractid NOT IN ('$couleurid','$tailleid','$matiereid') GROUP BY caractid ORDER BY caractid");
			while ($declinaison=$DB_site->fetch_array($declinaisons)) {
				$dec = explode('_',$chaineCarac[$declinaison[caractid]]);
				$decid = $dec[0];
				$dec = $dec[1];
				if ($dec){
					$articleid .= '_'.$decid;
				}
			}
			
			if ($artunique){
				//$articleid = $article[artid];
				$parentid = '';
			}else{
				$parentid = $article[artid];
			}
			/*echo $articleid."<br>";
			if($article[artid] > 4000){
				exit;
			}*/		
		}elseif ($typedeclinaison){
			$articleid = $article[artid];
			$parentid = $article[artid];
		}else{
			$parentid = "";
			$articleid = $article[artid];
		}
		
		$contenu = "\n" ;
		// Identifiant_unique
		$contenu .= "$articleid;";
		// Identifiant_parent
		$contenu .= "$parentid;";
		// Titre
		$contenu .= "$article[libelle];";
		// Description
		$contenu .= "$article[description];";
		// Marque
		$article[marque] = "";
		$params[separateur_marques] = ", ";
		$article_marques = $DB_site->query("SELECT * FROM marque INNER JOIN marque_site USING ( marqueid ) INNER JOIN article_marque USING ( marqueid ) WHERE artid = '$article[artid]' LIMIT 1") ;
		while ($article_marque=$DB_site->fetch_array($article_marques)) {
			$article[marque] .= $article_marque[$libelle] . $params[separateur_marques] ; // ici : rajouter les liens et autres de marque ...
		}
		$article[marque] = substr($article[marque], 0, -strlen($params[separateur_marques])) ;
		$article[marque] = secure_chaine_csv($article[marque],0);
		$contenu.="$article[marque];";
		// Categorie
		$contenu .= "$categorieSuperParente[libelle];";
		// Sous_categorie1
		$contenu .= "$categorieParente[libelle];";
		// Sous_categorie2
		$contenu .= "$categorie[libelle];";	
		// Prix_TTC
		$article[prixbarre] = 0;
		if ($stockid){
			$stockinfos = $DB_site->query_first("SELECT * FROM stocks WHERE stockid = '$stockid'");	
			$article[prix] = $article[prix]+$stockinfos[differenceprix];
		}
		$ajd=date("Y-m-d");
		if ($article[pctpromo] != NULL && $article[pctpromo] > 0 && $article[datedebut] <= $ajd && $article[datefin] > $ajd) {	
			$prix = number_format($article[prix] * (1 - ($article[pctpromo] / 100)), 2, '.', '') ;
			$article[prixbarre] = $article[prix];
		} else {
			$prix = $article[prix] ;
		}	
	
		
		$contenu .= number_format($prix, 2, ".", "").";";
		// prix_barre
		if ($article[prixbarre]) {	// Article en promo -> on affiche le prix promo
			$article[prixbarre] = number_format($article[prixbarre], 2, ".", "");
			$contenu .= "$article[prixbarre];";
		} else {
			if ($article[datedebut] == '2013-01-09')
				$prixb = $article[prix];
			else
				$prixb = '';
			$contenu .= "$prixb;";
		}
		
		// Frais_de_livraison
		$pays = $DB_site->query_first("SELECT montantgratuit FROM pays WHERE paysid = '$params[paysid]'");
		/*if ($article[prix] >= $pays[montantgratuit]) {
			$article[prixport] = 0;
		} else {*/
			if ($params[type_tranches_port] == 0) // tranches de poids
				$valeuratester = $article[poids];
			else // tranches de prix
				$valeuratester = $article[prix] ;	
			$tranche=$DB_site->query_first("SELECT MIN(prix) AS prix FROM fraisport WHERE prix != '0' AND paysid = '57' AND debut <= '$valeuratester' AND fin >= '$valeuratester'");
			if ($tranche[prix] && $tranche[prix] != NULL)
				$article[prixport] = $tranche[prix] ;
			else {
				$tranche=$DB_site->query_first("SELECT MIN(prix) AS prix FROM fraisport WHERE prix != '0' AND paysid = '57' AND debut = '0' AND fin = '0'");
				if ($tranche[prix] != NULL)
					$article[prixport] = $tranche[prix] ;
			}
		//}
		$article[prixport] = number_format($article[prixport], 2, ".", "");
		$contenu .= "$article[prixport];";
		// Disponibilite
		if ($stockid){
			if ($stockinfos[total] <= 0) {
				$contenu .= ($stockinfos[delaiappro]*7).";";
			} else {
				$contenu .= "0;";
			}	
		}else{
			$stock = $DB_site->query_first("SELECT nombre, delaiappro FROM stock WHERE artid='".$article[artid]."'");	
			if ($stock[nombre] <= 0) {
				$contenu .= ($stock[delaiappro]*7).";";
			} else {
				$contenu .= "0;";
			}	
		}
		// Quantit?n_Stock
		if ($stockid){
			if ($stockinfos[total] < 0)
				$stockinfos[total] = 0;
			$contenu .= $stockinfos[total].";";
		}else{
			if ($stock[nombre] < 0)
				$stock[nombre] = 0;
			$contenu .= $stock[nombre].";";
		}
		// Delais_de_livraison
		$contenu .= "48heures;";
		// Description_de_livraison
		$contenu .= "Colissimo Suivi 48H;";
		// URL_produit
		$contenu .= "http://$host/".$regleurlrewrite[$langueExport][article]."-".url_rewrite($article[$libelle])."-".$article[artid].".htm".";";
		// URL_image
		if ($article[image] != "") {
			$contenu .= "http://$host/ori-".url_rewrite($article[$libelle])."-".$article[artid].".".$article[image].";";					
		} else {
			$contenu .= ";";
		}
		// Garantie
		$contenu .= "$article[$garantie];";
		// Poids
		if ($stockid){
			$contenu .= (($article[poids]+$stockinfos[differencepoids]) / 1000).";";
		}else{
			$contenu .= (($article[poids]) / 1000).";";
		}
		
		// Genre
		$contenu .= "U;";
		// Occasion
		$contenu .= "0;";
		// Soldes
		$contenu .= "0;";
		// Promo_texte
		$contenu .= ";";
		// Pourcentage_promo
		if ($article[prixbarre]) {	// Article en promo -> on affiche le % promo
			$contenu .= "$article[pctpromo];";
		} else {
			$contenu .= ";";
		}
		// Date_de_debut_promo
		if ($article[prixbarre]) {	// Article en promo -> on affiche Date_de_debut_promo
			$contenu .= convertirDateEnChaineTiret($article[datedebut]).";";
		} else {
			$contenu .= ";";
		}
		// Date_de_fin_de_promo
		if ($article[prixbarre]) {	// Article en promo -> on affiche Date_de_fin_de_promo
			$contenu .= convertirDateEnChaineTiret($article[datefin]).";";
		} else {
			$contenu .= ";";
		}
		// Code_promo
		$contenu .= ";";
		// Description_code_promo
		$contenu .= ";";
		// Bundle
		$contenu .= "0;";
		// Shopinfo
		$contenu .= ";";
		// MPN
		if ($stockid)
			$mpn = $DB_site->query_first("SELECT reference_fabricant FROM stocks WHERE stockid = '$stockid'");	
		else
			$mpn[reference_fabricant] = $article[reference_fabricant];
			
		if (!$mpn[reference_fabricant] && $article[reference_fabricant])
			$mpn[reference_fabricant] = $article[reference_fabricant];
			
		$contenu .= $mpn[reference_fabricant].";";
		// EAN
		if ($stockid)
			$ean = $DB_site->query_first("SELECT code_EAN FROM stocks WHERE stockid = '$stockid'");	
		else
			$ean = $DB_site->query_first("SELECT code_EAN FROM stocks WHERE artid = '$article[artid]' LIMIT 1");	
		
			
		if (!$ean[code_EAN] && $article[code_EAN])
			$ean[code_EAN] = $article[code_EAN];
			
		$contenu .= $ean[code_EAN].";";
		// D3E
		$contenu .= number_format($article[ecotaxe], 2, ".", "").";";
		// Devise
		$devise = $DB_site->query_first("SELECT devise FROM devise INNER JOIN site USING(deviseid) WHERE siteid='$siteid'");
		$contenu.="$devise[devise];";
		// Ref
		$ref = $DB_site->query_first("SELECT reference FROM stocks WHERE stockid = '$stockid'");
		if ($ref[reference] != "")
			$contenu .= $ref[reference].";";
		else
			$contenu .= $article[artcode].";";
			
		// Ref fournisseur
		$contenu .= $article[reference_fabricant].";";
		
		// Couleur
		$contenu .= "$couleur;";
		// Taille
		$contenu .= "$taille;";
		// Matiere
		$contenu .= "$matiere;";
		
		//D�clinaisons
		$declinaisons = $DB_site->query("SELECT caractid FROM caracteristique INNER JOIN caracteristiquevaleur USING(caractid) WHERE caractid NOT IN ('$couleurid','$tailleid','$matiereid') GROUP BY caractid ORDER BY caractid");
		while ($declinaison=$DB_site->fetch_array($declinaisons)) {
			$dec = explode('_',$chaineCarac[$declinaison[caractid]]);
			$decid = $dec[0];
			$dec = $dec[1];
			if ($dec){
				$contenu .= $dec.';';
			}else{
				$contenu .= ';';
			}
		}
		
		// Type_declinaison
		$contenu .= "$typedeclinaison;";
		
		$photos = $DB_site->query("SELECT * FROM articlephoto WHERE artid = '$article[artid]' ORDER BY position LIMIT 2");
		$imageurl2 = $imageurl3 = "";
		while ($photo=$DB_site->fetch_array($photos)) {
			if (!$imageurl2)
				$imageurl2 = "http://$host/ori-".url_rewrite($article[$libelle])."-".$article[artid]."_".$photo[articlephotoid].".".$photo[image];
			elseif (!$imageurl3)
				$imageurl3 = "http://$host/ori-".url_rewrite($article[$libelle])."-".$article[artid]."_".$photo[articlephotoid].".".$photo[image];
		}
		// Image2
		$contenu .= "$imageurl2;";
		
		// Image3
		$contenu .= "$imageurl3;";
			
		// ASIN
		if ($stockid)
			$asin = $DB_site->query_first("SELECT ASIN FROM stocks WHERE stockid = '$stockid'");	
		else
			$asin['ASIN'] = $article['ASIN'];
		$contenu .= $asin['ASIN'].";";
		
		//Prix constat�
		if ($article[prixpublic])
			$contenu .= formaterPrix($article[prixpublic]).";";
		else
			$contenu .= ";";
			
		// Prix solde
		if ($article[datedebut] == '2013-01-09')
			$prix = number_format($article[prix] * (1 - ($article[pctpromo] / 100)), 2, '.', '') ;
		else
			$prix = 0;
		$contenu .= "$prix";
		
		return $contenu;
	}
}


if (!function_exists("exporter_lengow")) {
	function exporter_lengow($siteid){
		global $DB_site, $libelle, $description, $host,$provenance,$regleurlrewrite,$langueExport,$cheminsanshref,$modules,$datedujour, $params, $couleurid, $tailleid, $matiereid;
		$liste_artid = "SELECT artid FROM article_comparateur WHERE comparateurid = '27' AND siteid = '$siteid' ORDER BY artid";

		if ($params[exporter_non_visibles] == 0){
			$where .= "AND activeV1 = '1' ";
		}
		if ($params[exporter_non_commandables] == 0){
			$where .= "AND commandable = '1' ";
		}
		if ($params[exporter_cats_non_visibles] == 0){
			$aff_inactive = 0;
		}else{
			$aff_inactive = 1;	
		}
			
		$contenu = "Identifiant_unique;Identifiant_parent;Titre;Description;Marque;Categorie;Sous_categorie1;Sous_categorie2;Prix_TTC;Prix_barre;Frais_de_livraison;Disponibilite;Quantite_en_Stock;Delais_de_livraison;Description_de_livraison;URL_produit;URL_image;Garantie;Poids;Genre;Occasion;Soldes;Promo_texte;Pourcentage_promo;Date_de_debut_promo;Date_de_fin_promo;Code_promo;Description_code_promo;Bundle;Shopinfo;MPN;EAN;D3E;Devise;Ref;Ref Fournisseur;Couleur;Taille;Matiere;";
		$declinaisons = $DB_site->query("SELECT libelle FROM caracteristique_site INNER JOIN caracteristiquevaleur USING (caractid) WHERE caractid NOT IN ('$couleurid','$tailleid','$matiereid') AND siteid='$siteid' GROUP BY caractid ORDER BY caractid");
		while ($declinaison=$DB_site->fetch_array($declinaisons)) {
			$contenu .= $declinaison[libelle].';';
		}
		
		$contenu .= "Type_declinaison;URL_image2;URL_image3;code_ASIN;Prix_constate;Prix_solde";
		$libelle = "libelle";
		$nom="nom";
		$description="description";
		$fichetechnique="fichetechnique";
		$notreavis="notreavis";
		$image="image";
		$image2="image2";
		$title="title";
		$titre2 = "Titre2" ;
		
		if (isset($provenance) and $provenance == "selection") {
			$nom_fic = "export_lengow_selection".$siteid.".csv";
			$articles=$DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE siteid='$siteid' AND prix > 0 AND artid IN ($liste_artid) $where");
		}else {
			$nom_fic = "export_lengow".$siteid.".csv";
			$articles=$DB_site->query("SELECT * FROM article AS a INNER JOIN article_site AS asite USING(artid) WHERE siteid='$siteid' AND prix > 0 $where ORDER BY artid");
		}
		
		while ($article=$DB_site->fetch_array($articles)) {
			$export = 1;
			if ($aff_inactive == 0){
				$export = 0;
				$categs = $DB_site->query("SELECT catid FROM position WHERE artid = '$article[artid]'");
				while($categ=$DB_site->fetch_array($categs)){
					$catid = $categ[catid];
					while ($catid != 0 && $export == 0){
						$categ2=$DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$catid' AND siteid = '$siteid'");
						if ($categ2["visible_treeviewV1".$langueExport] == 1){
							$export = 1;	
						}
						$catid = $categ2[parentid];
					}
				}
			}else{
				$export = 1;
			}
			$nbstock = retournerStockArticle($DB_site, $article[artid]);
			/*echo $article[artid];
			echo "<br>";
			echo $nbstock;
			echo "<br>";*/
			if ($params[exporter_stock_epuise] == 0 && in_array(4, $modules) && $nbstock <= 0  && $article[stock_illimite] != 1){
				//$export = 0;
			}
			
			if ($export){
				$contenu .= lengowConstructArticle($DB_site,$article,$siteid);
			}
		}
		return $contenu;
	}
}
if(!$onestdansuncron){
	$sites = $DB_site->query("SELECT * FROM site");
	$comparateurid = $DB_site->query_first("SELECT comparateurid FROM comparateur WHERE fichier='".$nom_comparateur.".php'");
	
	while($site = $DB_site->fetch_array($sites)){
		$provenance = "";
		$contenu=exporter_lengow($site[siteid]);
		if (isset($provenance) and $provenance == "selection"){
			$nom_fic = "export_".$nom_comparateur."_selection".$site[siteid].".csv";
		}else{
			$nom_fic = "export_".$nom_comparateur.$site[siteid].".csv";
		}
		if (!is_dir($rootpath."configurations/$host/exports")) {
			mkdir($rootpath."configurations/$host/exports",0777);
		}
		$filename = $rootpath."configurations/$host/exports/".$nom_fic;
		if (!$handle = fopen($filename, 'w')) {
			echo "$multilangue[erreur_ouverture_fichier] ($filename)";
			exit;
		} else {
			fputs($handle, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF)));
			if (fwrite($handle,$contenu) === FALSE) {
				echo "$multilangue[erreur_ecriture_fichier] ($filename)";
				fclose($handle);
				exit;
			} else {
				fclose($handle);
				echo "$multilangue[adresse_fichier] : <a href=\"http://$host/configurations/$host/exports/$nom_fic\">http://$host/configurations/$host/exports/$nom_fic</a><br>";
			}
		}
		
		$articleselection = $DB_site->query("SELECT * FROM article_comparateur WHERE siteid=$site[siteid] AND comparateurid=$comparateurid[comparateurid]");
		if($DB_site->num_rows($articleselection)){
			$provenance = "selection";
			$contenu=exporter_lengow($site[siteid]);
			if (isset($provenance) and $provenance == "selection"){
				$nom_fic = "export_".$nom_comparateur."_selection".$site[siteid].".csv";
			}else{
				$nom_fic = "export_".$nom_comparateur.$site[siteid].".csv";
			}
			if (!is_dir($rootpath."configurations/$host/exports")) {
				mkdir($rootpath."configurations/$host/exports",0777);
			}
			$filename = $rootpath."configurations/$host/exports/".$nom_fic;
			if (!$handle = fopen($filename, 'w')) {
				echo "$multilangue[erreur_ouverture_fichier] ($filename)";
				exit;
			} else {
				fputs($handle, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF)));	
				if (fwrite($handle, $contenu) === FALSE) {
					echo "$multilangue[erreur_ecriture_fichier] ($filename)";
					fclose($handle);
					exit;
				} else {
					fclose($handle);
					echo "$multilangue[adresse_fichier] : <a href=\"http://$host/configurations/$host/exports/$nom_fic\">http://$host/configurations/$host/exports/$nom_fic</a><br>";
				}
			}
		}
		
	}
}

?>