<?php
/**
* Export personnalisé par sélection du catalogue
*/

ini_set('memory_limit','512M');
ini_set('max_execution_time','0');
set_time_limit(0);

require "includes/admin_global.php";

$mode = "test_modules";

$nb_sites_select=0;

if($critereSite){
	foreach ($critereSite as $siteid => $value){
		$tab_siteid[$siteid]=$siteid;
		$nb_sites_select++;
	}
}


$nom_fichier_export = 'articles.csv';

$filename = './export/csv/'.$nom_fichier_export;
if (!$handle = fopen($filename, 'w+')) {
	echo "$multilangue[erreur_ouverture_fichier] ($filename)";
	exit;
}
//$handle = fopen($filename, 'a');

// UTF8 pour csv
fputs($handle, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF)));

/**
 * Début ligne d'entête
 */
//construction des titres des colonnes
$contenu_ligne="";

if ($exporterTitre == 1){
	
	//Référence générale
	if ($article_artcode != "") $contenu_ligne.="$multilangue[reference];";

	//Identifiant article (artid)
	if ($article_artid != "") $contenu_ligne.="$multilangue[identifiant_article];";
	
	//Identifiant Catégorie
	if ($article_catid != "") $contenu_ligne.="$multilangue[identifiant] $multilangue[categorie];";
	
	//Code EAN général
	if ($article_code_EAN != "") $contenu_ligne.="$multilangue[code_ean];";
	
	//Code ASIN général
	if ($article_ASIN != "") $contenu_ligne.="$multilangue[code_asin];";
	
	//Référence fabricant générale
	if ($article_reference_fabricant != "") $contenu_ligne.="$multilangue[reference_fabricant];";	
	
	//Numéro tarifaire La Poste
	if ($article_numero_tarifaire_laposte != "") $contenu_ligne.="$multilangue[numero_tarifaire_laposte];";	
	
	//Libellés
	foreach ($tab_siteid as $siteid){
		if (${'article_libelle'.$siteid} != "") $contenu_ligne.=${'article_libelle'.$siteid}.";";
	}
	
	//Sous titres
	foreach ($tab_siteid as $siteid){
		if (${'article_titre2'.$siteid} != "") $contenu_ligne.=${'article_titre2'.$siteid} .";";
	}
	
	//Google Shopping (5922)
	if ($article_googleshop != "") $contenu_ligne.="$multilangue[categorie] Google Shopping;";	
	
	//Marques
	if ($article_marques != "") $contenu_ligne.="$multilangue[marques];";
	
	//Fournisseur
	if ($article_fournisseur != "") $contenu_ligne.="$multilangue[fournisseur];";
	
	//Poids général
	if ($article_poids != "") $contenu_ligne.="$multilangue[poids] ($multilangue[grammes]);";
	
	//Dimensions	
	if ($article_dim_longueur != "") $contenu_ligne.="$multilangue[longueur] ($multilangue[millimetres]);";
	if ($article_dim_largeur != "") $contenu_ligne.="$multilangue[largeur] ($multilangue[millimetres]);";
	if ($article_dim_hauteur != "") $contenu_ligne.="$multilangue[hauteur] ($multilangue[millimetres]);";
	
	
	//Prix de vente général
	foreach ($tab_siteid as $siteid){
		if (${'article_prix'.$siteid} != "") $contenu_ligne.=${'article_prix'.$siteid}.";";
	}
	
	//Prix public article_prixpublic1
	foreach ($tab_siteid as $siteid){
		if (${'article_prixpublic'.$siteid} != "") $contenu_ligne.=${'article_prixpublic'.$siteid}.";";
	}
	
	//Prix d'achat général
	if ($article_prixachat != "") $contenu_ligne.="$multilangue[prix_achat];";
		
	//Taux de tva
	if ($article_tauxchoisi != "") $contenu_ligne.="$multilangue[taux_tva];";
	
	//Délai de livraison général
	foreach ($tab_siteid as $siteid){
		if (${'article_delai'.$siteid} != "") $contenu_ligne.=${'article_delai'.$siteid}.";";
	}
	
	//Vendu au metre
	if ($article_aumetre != "") $contenu_ligne.="$multilangue[vendu_au_metre];";
	
	//Commentaire
	if ($article_commentaire != "") $contenu_ligne.="$multilangue[commentaire];";
	
	//Commandable
	foreach ($tab_siteid as $siteid){
		if (${'article_commandable'.$siteid} != "") $contenu_ligne.=${'article_commandable'.$siteid}.";";
	}	
	
	//Visible V1
	foreach ($tab_siteid as $siteid){
		if (${'article_activeV1'.$siteid} != "") $contenu_ligne.=${'article_activeV1'.$siteid}.";";
	}
	
	//Visible V2
	foreach ($tab_siteid as $siteid){
		if (${'article_activeV2'.$siteid} != "") $contenu_ligne.=${'article_activeV2'.$siteid}.";";
	}

	//Image 1
	if ($article_image != "") $contenu_ligne.="$multilangue[image] ($multilangue[url_complete]);";
	
	//Légendes 1
	foreach ($tab_siteid as $siteid){
		if (${'article_legende'.$siteid} != "") $contenu_ligne.=${'article_legende'.$siteid}.";";
	}
	
	//Image 2
	if ($article_image2 != "") $contenu_ligne.="$multilangue[image] 2 ($multilangue[url_complete]);";
	
	//Légendes 2
	foreach ($tab_siteid as $siteid){
		if (${'article_legende2'.$siteid} != "") $contenu_ligne.=${'article_legende2'.$siteid}.";";
	}
	
	//Descriptions
	foreach ($tab_siteid as $siteid){
		if (${'article_description'.$siteid} != "") $contenu_ligne.=${'article_description'.$siteid}.";";
	}
	
	//module fiche technique
	foreach ($tab_siteid as $siteid){
		if (${'article_fichetechnique'.$siteid} != "") $contenu_ligne.=${'article_fichetechnique'.$siteid}.";";
	}
	
	//module notre avis
	foreach ($tab_siteid as $siteid){
		if (${'article_notreavis'.$siteid} != "") $contenu_ligne.=${'article_notreavis'.$siteid}.";";
	}
	
	//Caractéristiques
	if ($article_caracteristiques != "") $contenu_ligne.="$multilangue[caracteristiques];";
	
	//Référence par combinaison
	if ($article_stock_artcode != "") $contenu_ligne.="$multilangue[reference_stock];";
	
	//Référence fabricant par combinaison
	if ($article_stock_reference_fabricant != "") $contenu_ligne.="$multilangue[reference_fabricant_par_stock];";
	
	//EAN par combinaison
	if ($article_stock_code_EAN != "") $contenu_ligne.="$multilangue[code_ean_par_stock];";
	
	//ASIN par combinaison
	if ($article_stock_ASIN != "") $contenu_ligne.="$multilangue[code_asin_par_stock];";
	
	//Stocks par combinaison
	if ($article_stock_total != "") $contenu_ligne.="$multilangue[stock];";
	
	//Stocks réservés par combinaison
	if ($article_stock_reserv != "") $contenu_ligne.="$multilangue[stock_reserv];";
	
	//Seuil d'alerte par combinaison
	if ($article_stock_seuil_alerte != "") $contenu_ligne.="$multilangue[seuil_alerte_par_stock];";
	
	//Délais de réappro par combinaison
	if ($article_stock_delai_appro != "") $contenu_ligne.="$multilangue[delai_appro_par_stock];";
	
	//Délais de livraison par combinaison
	if ($article_stock_delai_livraison != "") $contenu_ligne.="$multilangue[delai_livraison_stock];";
	
	//Zone de stockage par combinaison
	if ($article_stock_zonestockage != "") $contenu_ligne.="$multilangue[zone_stockage_stock];";
	
	//prix d'achat par combinaison
	if ($article_stock_prixachat != "") $contenu_ligne.="$multilangue[prix_achat_stock];";
	
	//Prix de vente par combinaison
	if ($article_stock_prix != "") $contenu_ligne.="$multilangue[prix_vente_stock] $multilangue[ttc];";
	
	// Colisage fournisseur
	if ($article_colisage != "") $contenu_ligne.="$multilangue[colisagefournisseurs];";
	
	//Balises meta title
	foreach ($tab_siteid as $siteid){
		if (${'article_ref_title'.$siteid} != "") $contenu_ligne.=${'article_ref_title'.$siteid}.";";
	}
	
	//Balises meta desc
	foreach ($tab_siteid as $siteid){
		if (${'article_ref_description'.$siteid} != "") $contenu_ligne.=${'article_ref_description'.$siteid}.";";
	}
	
	//Balises meta keywords
	foreach ($tab_siteid as $siteid){
		if (${'article_ref_keywords'.$siteid} != "") $contenu_ligne.=${'article_ref_keywords'.$siteid}.";";
	}
	
	//Articles conseillés
	if ($article_conseil != "") $contenu_ligne.="$multilangue[articles_conseilles];";
		
	//module tags
	foreach ($tab_siteid as $siteid){
		if (${'article_tags'.$siteid} != "") $contenu_ligne.=${'article_tags'.$siteid}.";";
	}	
	
	// Articles composant ce lot
	if ($article_bundle != "") $contenu_ligne.="$multilangue[articles_composant_lot];";
	
	////Prix promo
	foreach ($tab_siteid as $siteid){
		if (${'article_prixpromo'.$siteid} != "") $contenu_ligne.=${'article_prixpromo'.$siteid}.";";
	}
	
	////Pct promo
	foreach ($tab_siteid as $siteid){
		if (${'article_pctpromo'.$siteid} != "") $contenu_ligne.=${'article_pctpromo'.$siteid}.";";
	}
	
	//Début promo
	foreach ($tab_siteid as $siteid){
		if (${'article_datedebut'.$siteid} != "") $contenu_ligne.=${'article_datedebut'.$siteid}.";";
	}
	
	//Fin promo
	foreach ($tab_siteid as $siteid){
		if (${'article_datefin'.$siteid} != "") $contenu_ligne.=${'article_datefin'.$siteid}.";";
	}
	
	//Nombre de vente
	foreach ($tab_siteid as $siteid){
		if (${'article_nbventes'.$siteid} != "") $contenu_ligne.=${'article_nbventes'.$siteid}.";";
	}
	$contenu_ligne.="\n";
}
/**
 * Fin ligne d'entête
 */

fwrite($handle, $contenu_ligne);


/**
 * Début récupération critères et condtruction requête
 */
//Initialisation du séparateur décimal (choix entre . et ,).
$nonSeparateurDecimal=$seperateurDecimal=='.'?',':'.';

//requete en fonction des critères sélectionnés
$where="";
$or="";
$innerJoin="";
$join_asite=0;

if ($critereVisible != ""){
	if ($critereSiteVisible != "" || $critereVersion != ""){
		if ($critereSiteVisible != "" && $critereVersion != ""){
			$where .= " AND asite.activeV$critereVersion='$critereVisible'";
			$innerJoin .= " INNER JOIN article_site AS asite ON a.artid=asite.artid AND asite.siteid='$critereSiteVisible' ";
		}else{
			if ($critereSiteVisible != ""){
				$where .= " AND (asite.activeV1='$critereVisible' OR asite.activeV2='$critereVisible')";
				$innerJoin .= " INNER JOIN article_site AS asite ON a.artid=asite.artid AND asite.siteid='$critereSiteVisible' ";
			}
			if ($critereVersion != ""){
				$where .= " AND asite.activeV$critereVersion='$critereVisible'";
				$innerJoin .= " INNER JOIN article_site AS asite ON a.artid=asite.artid AND asite.siteid='1' ";
			}
		}
	}else{
		$where .= " AND (asite.activeV1='$critereVisible' OR asite.activeV2='$critereVisible')";
		$innerJoin .= " INNER JOIN article_site AS asite ON a.artid=asite.artid AND asite.siteid='1' ";
	}
	$join_asite=1;
}

//Fournisseur
if ($critereFournisseur != ""){
	$where.=" AND a.fournisseurid='$critereFournisseur'";	
}

//Commandable
if ($critereCommandable != ""){
	$where.=" AND asite.commandable='$critereCommandable'";
	if(!$join_asite){
		$innerJoin .= " INNER JOIN article_site AS asite ON a.artid=asite.artid AND asite.siteid='1' ";
	}
}

//A deja été vendu ?!
if ($critereVendu != ""){
	$tabArtsVendus=array();
	$artsVendus=$DB_site->query("SELECT DISTINCT(lf.artid) FROM lignefacture lf INNER JOIN facture f ON (lf.factureid=f.factureid) WHERE f.etatid IN (1,5)");	
	while ($artVendu=$DB_site->fetch_array($artsVendus)){
		array_push($tabArtsVendus,$artVendu[artid]);
	}
	
	switch($critereVendu){
		case 0:
			$where.=" AND a.artid NOT IN (".implode(',',$tabArtsVendus).")";
		break;
		case 1:
			$where.=" AND a.artid IN (".implode(',',$tabArtsVendus).")";
		break;
		default:
			exit;
		break;
	}
}

//En promotion
if ($criterePromotion != ""){
	$where.=" AND aps.pctpromo IS NOT NULL AND pctpromo>0 AND datedebut>'".time()."' AND datefin<'".time()."'";	
	$innerJoin .= " INNER JOIN article_promo_site AS aps ON a.artid=aps.artid AND aps.siteid='1' ";
	
}

//Stock illimité
if ($critereStockIllimite != ""){
	$where.=" AND a.stock_illimite='$critereStockIllimite'";
}

//Produits immatériel
if ($critereImmateriel != ""){
	$where.=" AND a.immateriel='$critereImmateriel'";
}

//Bundle
if ($critereBundle != ""){
	$where.=" AND a.isbundle='$critereBundle'";
}


//Catégorie
if ($critereCatid != ""){
	//Ss Catégorie
	if ($critereSscateg == 1){
		$listeCatid = $critereCatid.catid_enfants($DB_site,$critereCatid);
		$listeCatid = str_replace(",","','",$listeCatid);
		$where.=" AND a.catid IN ('$listeCatid')";
	}else{
		$where.=" AND a.catid='$critereCatid'";
	}
}

//les 7 critères ou on a besoin d'un innerjoin
if ($critereMarque != "" || $critereEnStock != "" || $critereVendu != "" || $critereNouveaute != "" || $critereTopVente != "" || $critereCoupDeCoeur != ""){
	
	//Marque
	if ($critereMarque != ""){
		$innerJoin.=" INNER JOIN article_marque am ON (a.artid=am.artid) AND am.marqueid='$critereMarque'";
	}
	
	//En stock
	if ($critereEnStock != ""){
		$innerJoin.=" INNER JOIN stock s ON (a.artid=s.artid)";
		$where.=" AND (a.stock_illimite='1' OR s.nombre>0)";
	}
	
	//En nouveauté
	if ($critereNouveaute != ""){
		$innerJoin.=" INNER JOIN topnouveaute tn ON (a.artid=tn.artid)";
	}
	
	//En top vente
	if ($critereTopVente != ""){
		$innerJoin.=" INNER JOIN topvente tv ON (a.artid=tv.artid)";
	}
	
	//En coup de coeur
	if ($critereCoupDeCoeur != ""){
		$innerJoin.=" INNER JOIN topcoupdecoeur tc ON (a.artid=tc.artid)";
	}
}

$selectgoogle = "";
if ($article_googleshop != ""){
	$selectgoogle = ", gs.attributid";
	$innerJoin.=" LEFT OUTER JOIN googleshopping gs ON (a.artid=gs.artid)";
}
/**
 * Fin récupération critères et condtruction requête
 */

/**
 * Début remplissage fichier
 */
$sql = "SELECT DISTINCT(a.artid),a.*$selectgoogle FROM article a $innerJoin WHERE 1=1 $where ORDER BY a.artid";

/*echo $sql;
exit;*/

$articles=$DB_site->query($sql);

while($article=$DB_site->fetch_array($articles)) {
	$contenu_ligne="";
	
	// Article site
	foreach ($tab_siteid as $siteid){
		$sql="SELECT * FROM article AS a LEFT JOIN article_site AS asite ON a.artid=asite.artid AND siteid='$siteid' WHERE a.artid='$article[artid]'";
		$article_site[$siteid]=$DB_site->query_first($sql);
	}
	
	
	//caracts de la forme taille:S,M,L|couleur:jaune,rouge;
	$caracteristiques=$DB_site->query("SELECT c.caractid, libelle FROM caracteristique AS c
										INNER JOIN caracteristique_site AS cs ON c.caractid = cs.caractid AND siteid='1'
										ORDER BY position");
	$libCaract="";
	while ($caracteristique=$DB_site->fetch_array($caracteristiques)) {
		$caract_valeurs=$DB_site->query("SELECT ac.caractvalid, cvs.libelle FROM caracteristiquevaleur cv
										INNER JOIN caracteristiquevaleur_site AS cvs ON cv.caractvalid = cvs.caractvalid AND siteid='1'
										INNER JOIN article_caractval ac ON cv.caractvalid = ac.caractvalid
										WHERE caractid = '$caracteristique[caractid]' 
										AND artid = '$article[artid]' 
										ORDER BY cv.position ASC");
		if ($DB_site->num_rows($caract_valeurs) > 0) {
			$libCaract.="|".$caracteristique[libelle].":";
			while ($caract_valeur=$DB_site->fetch_array($caract_valeurs)) {
				$libCaract.=$caract_valeur[libelle].",";
			}
			$libCaract = substr($libCaract, 0, -1);
		}
	}
	$libCaract=substr($libCaract, 1);
	
	//combinaisons par lignes de stocks
	$reference=$reference_fabricant=$code_EAN=$ASIN=$stock=$stock_reserv=$seuil_alerte=$delai_appro=$delai_livraison=$zonestockage=$prixachat=$prix_vente=$pipe="";
	
	$lignes_stocks=$DB_site->query("SELECT * FROM stocks WHERE artid='$article[artid]'");
	$count_lignes=$DB_site->num_rows($lignes_stocks);
	if ($count_lignes>0 ){ //Il y a des caracts
		while($ligne_stocks=$DB_site->fetch_array($lignes_stocks)){
			$stocks_caractval=$DB_site->query("SELECT * FROM stocks_caractval WHERE stockid='$ligne_stocks[stockid]'");
			$listCaractvalids=$libCaractVal=$virgule="";
			$where = "";
			$join = "";
			$i=1;
			while($stock_caractval=$DB_site->fetch_array($stocks_caractval)){

				$caractVal=$DB_site->query_first("SELECT libelle FROM caracteristiquevaleur_site WHERE caractvalid='$stock_caractval[caractvalid]' AND siteid='1'");
				$listCaractvalids.=$virgule."$stock_caractval[caractvalid]";
				$libCaractVal.=$virgule."$caractVal[libelle]";
				$virgule=",";
				if($article_stock_reserv == "on"){
					$join = "INNER JOIN lignefacturecaracteristique AS lfc$i USING(lignefactureid) ";
					$where = "AND lfc$i.caractvalid = '$stock_caractval[caractvalid]' ";
					$arts_reserves=$DB_site->query_first("SELECT SUM(qte) as qte_reserv FROM lignefacture AS lf
												INNER JOIN facture AS f USING(factureid)
												$join
												WHERE lf.artid = $article[artid]
												$where
												AND f.dateexpedition = '0000-00-00'
												AND f.datedecrementation != '0000-00-00'
												AND f.dateincrementation = '0000-00-00'
												AND f.deleted = '0'
												AND f.datefacture > '$ilyavingtjours';");
					$qte_reservee+=$arts_reserves[qte_reserv];
				}
				$i++;
				
			}
			
			$tabResults=retournerInformationsStockSite($DB_site,$article[artid],1,$listCaractvalids);
			$prix=$article_site[1][prix]+$tabResults[differenceprix];
			$lastmonth = mktime(0, 0, 0, date("m"), date("d")-20,   date("Y"));
			$ilyavingtjours = date("Y-m-d",$lastmonth);
			
											

			// $qte_reservee = 0;
			// while ($art_reserve=$DB_site->fetch_array($arts_reserves)){
				// $qte_reservee += $art_reserve[qte];
			// }
			
			
			if($count_lignes==1){ //Une seule combinaison
				$reference=$tabResults[reference];
				$reference_fabricant=$tabResults[reference_fabricant];
				$code_EAN=$tabResults[code_EAN];
				$ASIN=$tabResults['ASIN'];
				$stock=$tabResults[total];
				$stock_reserv=$qte_reservee;
				$seuil_alerte=$tabResults[seuil_alerte];
				$delai_appro=$tabResults[delai_appro];
				$delai_livraison=$tabResults[delai_livraison];
				$zonestockage=$tabResults[zonestockage];
				$prixachat=$tabResults[prixachat];
				$prix_vente=$prix;
			}else{ //Plein de combinaisons
				$reference.=$pipe."$libCaractVal:$tabResults[reference]";
				$reference_fabricant.=$pipe."$libCaractVal:$tabResults[reference_fabricant]";
				$code_EAN.=$pipe."$libCaractVal:$tabResults[code_EAN]";
				$ASIN.=$pipe."$libCaractVal:$tabResults[ASIN]";
				$stock.=$pipe."$libCaractVal:$tabResults[total]";
				$stock_reserv.=$pipe."$libCaractVal:$qte_reservee";
				$seuil_alerte.=$pipe."$libCaractVal:$tabResults[seuil_alerte]";
				$delai_appro.=$pipe."$libCaractVal:$tabResults[delai_appro]";
				$delai_livraison.=$pipe."$libCaractVal:$tabResults[delai_livraison]";
				$zonestockage.=$pipe."$libCaractVal:$tabResults[zonestockage]";
				$prixachat.=$pipe."$libCaractVal:$tabResults[prixachat]";
				$prix_vente.=$pipe."$libCaractVal:$prix";
				$pipe="|";
			}
		}
	}else{ //aucune caract
		$lastmonth = mktime(0, 0, 0, date("m"), date("d")-20,   date("Y"));
		$ilyavingtjours = date("Y-m-d",$lastmonth);
		if($article_stock_reserv == "on"){
			$arts_reserves=$DB_site->query("SELECT qte FROM lignefacture AS lf
											INNER JOIN facture AS f USING(factureid)
											WHERE lf.artid = $article[artid]
											AND f.dateexpedition = '0000-00-00'
											AND f.datedecrementation != '0000-00-00'
											AND f.dateincrementation = '0000-00-00'
											AND f.deleted = '0'
											AND f.datefacture > '$ilyavingtjours';");
			$qte_reservee = 0;
			while ($art_reserve=$DB_site->fetch_array($arts_reserves)){
				$qte_reservee += $art_reserve[qte];
			}	
		}
		$tabResults=retournerInformationsStock($DB_site,$article[artid],1);
		$stock.=$tabResults[total];
		$stock_reserv.=$qte_reservee;
		$zonestockage=$tabResults[zonestockage];
		$delai_appro=$tabResults[delai_appro];
	}

	/*
	if ($DB_site->num_rows($lignes_stocks)==1){ //une seule combinaison
		while($ligne_stocks=$DB_site->fetch_array($lignes_stocks)){
			$stocks_caractval=$DB_site->query("SELECT * FROM stocks_caractval WHERE stockid='$ligne_stocks[stockid]'");
			$listCaractvalids=$libCaractVal=$virgule="";
			while($stock_caractval=$DB_site->fetch_array($stocks_caractval)){
				$caractVal=$DB_site->query_first("SELECT $libelle FROM caracteristiquevaleur WHERE caractvalid='$stock_caractval[caractvalid]'");
				$listCaractvalids.=$virgule."$stock_caractval[caractvalid]";
				$virgule=",";
			}
			$tabResults=retournerInformationsStock($DB_site,$article[artid],$listCaractvalids);
			$prix=$article[prix]+$tabResults[differenceprix];
			
			$reference=$tabResults[reference];
			$reference_fabricant=$tabResults[reference_fabricant];
			$code_EAN=$tabResults[code_EAN];
			$stock=$tabResults[total];
			$seuil_alerte=$tabResults[seuil_alerte];
			$delai_appro=$tabResults[delai_appro];
			$delai_livraison=$tabResults[delai_livraison];
			$zonestockage=$tabResults[zonestockage];
			$prixachat=$tabResults[prixachat];
			$prix_vente=$prix;
		}
	}elseif($DB_site->num_rows($lignes_stocks)>0){ //plein de combinaisons
		while($ligne_stocks=$DB_site->fetch_array($lignes_stocks)){
			$stocks_caractval=$DB_site->query("SELECT * FROM stocks_caractval WHERE stockid='$ligne_stocks[stockid]'");
			if ($DB_site->num_rows($stocks_caractval)>0){
				$listCaractvalids="";
				$libCaractVal="";
				$virgule="";
				while($stock_caractval=$DB_site->fetch_array($stocks_caractval)){
					$caractVal=$DB_site->query_first("SELECT $libelle FROM caracteristiquevaleur WHERE caractvalid='$stock_caractval[caractvalid]'");
					$libCaractVal.=$virgule."$caractVal[$libelle]";
					$listCaractvalids.=$virgule."$stock_caractval[caractvalid]";
					$virgule=",";
				}
				$tabResults=retournerInformationsStock($DB_site,$article[artid],$listCaractvalids);
				$prix=$article[prix]+$tabResults[differenceprix];
				
				$reference.=$pipe."$libCaractVal:$tabResults[reference]";
				$reference_fabricant.=$pipe."$libCaractVal:$tabResults[reference_fabricant]";
				$code_EAN.=$pipe."$libCaractVal:$tabResults[code_EAN]";
				$stock.=$pipe."$libCaractVal:$tabResults[total]";
				$seuil_alerte.=$pipe."$libCaractVal:$tabResults[seuil_alerte]";
				$delai_appro.=$pipe."$libCaractVal:$tabResults[delai_appro]";
				$delai_livraison.=$pipe."$libCaractVal:$tabResults[delai_livraison]";
				$zonestockage.=$pipe."$libCaractVal:$tabResults[zonestockage]";
				$prixachat.=$pipe."$libCaractVal:$tabResults[prixachat]";
				$prix_vente.=$pipe."$libCaractVal:$prix";
				$pipe="|"; //désolé pr le nom de la variable :)
			}
		}
	}else{ //aucune caract
		$tabResults=retournerInformationsStock($DB_site,$article[artid]);
		$stock.=$tabResults[total];
		$zonestockage=$tabResults[zonestockage];
		$delai_appro=$tabResults[delai_appro];
	}*/
	
	//ici promo
	foreach ($tab_siteid as $siteid){
		
		$sql="SELECT * FROM article AS a LEFT JOIN article_promo_site AS aps ON a.artid=aps.artid AND siteid='$siteid' WHERE a.artid='$article[artid]'";
		$article_promo_site[$siteid]=$DB_site->query_first($sql);
		
		if (estEnPromoSite($DB_site, $article[artid], $siteid)) {
			$debutpromo[$siteid]=date("d/m/Y H:i:s",$article_promo_site[$siteid]['datedebut']);
			$finpromo[$siteid]=date("d/m/Y H:i:s",$article_promo_site[$siteid]['datefin']);
			$prixpromo[$siteid]=round(($article_site[$siteid]['prix'] * (1 - $article_promo_site[$siteid]['pctpromo']/100)),2);
			$pctpromo[$siteid]=round($article_promo_site[$siteid]['pctpromo']);
		}else{
			$debutpromo[$siteid]="";
			$finpromo[$siteid]="";
			$prixpromo[$siteid]="";
			$pctpromo[$siteid]="0";
		}
	}
	

	//Référence générale
	if ($article_artcode != ""){
		$contenu_ligne.=$article[artcode].";";
	}
	
	//Identifiant article
	if ($article_artid != ""){
		$contenu_ligne.=$article[artid].";";
	}
	
	//Identifiant catégorie
	if ($article_catid != ""){
		$contenu_ligne.=$article[catid].";";
	}
	
	//Code EAN général
	if ($article_code_EAN != ""){
		$contenu_ligne.=$article[code_EAN].";";
	}
	
	//Code ASIN général
	if ($article_ASIN != ""){
		$contenu_ligne.=$article['ASIN'].";";
	}
	
	//Référence fabricant générale
	if ($article_reference_fabricant != ""){
		$contenu_ligne.=$article[reference_fabricant].";";
	}	
	
	//Numéro tarifaire La Poste
	if ($article_numero_tarifaire_laposte != ""){
		$contenu_ligne.=$article[numero_tarifaire_laposte].";";
	}
	
	//Libellés
	foreach ($tab_siteid as $siteid){
		if (${'article_libelle'.$siteid} != ""){
			$contenu_ligne.=secure_chaine_csv($article_site[$siteid]['libelle'],1).";";
		}
	}
	
	//Sous titres
	foreach ($tab_siteid as $siteid){
		if (${'article_titre2'.$siteid} != ""){
			$contenu_ligne.=secure_chaine_csv($article_site[$siteid]['titre2'],1).";";
		}
	}
	
	//Google Shop
	if ($article_googleshop != ""){
		$contenu_ligne.=$article[attributid].";";
	}
	
	//Marque(s)
	if ($article_marques != ""){
		$mq="";
		$pipe="";
		$marques=$DB_site->query("SELECT libelle FROM article_marque AS am 
									INNER JOIN marque AS m ON (am.marqueid=m.marqueid) 
									INNER JOIN marque_site AS ms ON am.marqueid=m.marqueid AND siteid='1'
									WHERE am.artid='$article[artid]'");
		while ($marque=$DB_site->fetch_array($marques)){
			$mq.=$pipe."$marque[libelle]";
			$pipe="|";
		}
		$contenu_ligne.=secure_chaine_csv($mq,1).";";
	}
	
	//Fournisseur
	if ($article_fournisseur != ""){
		$fournisseur=$DB_site->query_first("SELECT libelle FROM fournisseur WHERE fournisseurid='$article[fournisseurid]'");
		$contenu_ligne.=secure_chaine_csv($fournisseur[libelle],1).";";
	}
	
	//Poids général en g
	if ($article_poids != ""){
		$contenu_ligne.=$article[poids].";";
	}
	
	//Dimensions	
	if ($article_dim_longueur != ""){
		$contenu_ligne.=$article[longueur].";";
	}
	if ($article_dim_largeur != ""){
		$contenu_ligne.=$article[largeur].";";
	}
	if ($article_dim_hauteur != ""){
		$contenu_ligne.=$article[hauteur].";";
	}		
	
	
	//Prix de vente général TTC
	foreach ($tab_siteid as $siteid){
		if (${'article_prix'.$siteid} != ""){
			$contenu_ligne.=str_replace($nonSeparateurDecimal,$seperateurDecimal,$article_site[$siteid][prix]).";";
		}
	}	
	
	//Prix public TTC	
	foreach ($tab_siteid as $siteid){
		if (${'article_prixpublic'.$siteid} != ""){
			$contenu_ligne.=str_replace($nonSeparateurDecimal,$seperateurDecimal,$article_site[$siteid][prixpublic]).";";
		}
	}
	
	//Prix d'achat général HT	
	if ($article_prixachat != ""){
		$contenu_ligne.=str_replace($nonSeparateurDecimal,$seperateurDecimal,$article[prixachat]).";";
	}	
	
	//Taux de tva
	if ($article_tauxchoisi != ""){
		switch($article[tauxchoisi]){
			case 0:
				$tauxtva=$multilangue[aucun];
			break;
			case 1:
				$tauxtva=$multilangue[taux_normal];
			break;
			case 2:
				$tauxtva=$multilangue[taux_reduit];
			break;
			case 3:
				$tauxtva=$multilangue[taux_intermediaire];
			break;
		}
		$contenu_ligne.=str_replace($nonSeparateurDecimal,$seperateurDecimal,$tauxtva).";";
	}
	
	//Délai de livraison général	
	foreach ($tab_siteid as $siteid){
		if (${'article_delai'.$siteid} != ""){
			$contenu_ligne.=secure_chaine_csv($article_site[$siteid]['delai'],1).";";
		}
	}
	
	//Vendu au mètre
	if ($article_aumetre != ""){
		switch ($article[prixaumetre]){
			case 0:
				$contenu_ligne.="$multilangue[non];";
				break;
			case 1:
				$contenu_ligne.="$multilangue[oui];";
				break;
		}
	}
	
	//Commentaire
	if ($article_commentaire != ""){
		switch($donneesTexte){
			case 0:
				$commentaire=$article[commentaire];
			break;
			case 1:
				$commentaire=strip_tags($article[commentaire]);
			break;
		}
		$contenu_ligne.=$commentaire.";";
	}
	
	//Commandable
	foreach ($tab_siteid as $siteid){
		if (${'article_commandable'.$siteid} != ""){
			switch ($article_site[$siteid][commandable]){
				case 0:
					$contenu_ligne.="$multilangue[non];";
					break;
				case 1:
					$contenu_ligne.="$multilangue[oui];";
					break;
			}
		}
	}
	
	//Visibles V1	
	foreach ($tab_siteid as $siteid){
		if (${'article_activeV1'.$siteid} != ""){
			switch ($article_site[$siteid]['activeV1']){
				case 0:
					$contenu_ligne.="$multilangue[non];";
				break;
				case 1:
					$contenu_ligne.="$multilangue[oui];";
				break;
			}
		}
	}
		
	//Visibles V2
	foreach ($tab_siteid as $siteid){
		if (${'article_activeV2'.$siteid} != ""){
			switch ($article_site[$siteid]['activeV2']){
				case 0:
					$contenu_ligne.="$multilangue[non];";
					break;
				case 1:
					$contenu_ligne.="$multilangue[oui];";
					break;
			}
		}
	}

	//Image	
	if ($article_image != ""){		
		if ($article_site[1][image] != NULL)
			$contenu_ligne.="http://".$host."/ar-".url_rewrite($article_site[1][libelle])."-".$article[artid].".".$article_site[1][image].";";
		else
			$contenu_ligne.=";";
	}
	
	
	//Légendes
	foreach ($tab_siteid as $siteid){
		if (${'article_legende'.$siteid} != ""){
			$contenu_ligne.=secure_chaine_csv($article_site[$siteid]['legende'],1).";";
		}
	}
	
	//Image 2
	if ($article_image2 != ""){
		$imgsup=$DB_site->query_first("SELECT * FROM articlephoto AS ap INNER JOIN articlephoto_site AS aps ON ap.articlephotoid=aps.articlephotoid AND siteid='1' WHERE artid = '$article[artid]' ORDER BY ap.articlephotoid");
		
		if ($imgsup['articlephotoid'] != 0)
			$contenu_ligne .= "http://".$host."/ar-".url_rewrite($article_site[1][libelle])."-".$imgsup[artid]."_".$imgsup[articlephotoid].".".$imgsup[image].";";
		else
			$contenu_ligne.=";";
	}

	//Légendes 2
	foreach ($tab_siteid as $siteid){
		$imgsup[$siteid]=$DB_site->query_first("SELECT * FROM articlephoto AS ap 
										INNER JOIN articlephoto_site AS aps ON ap.articlephotoid = aps.articlephotoid AND siteid='$siteid' 
										WHERE ap.artid = '$article[artid]'");
		if (${'article_legende2'.$siteid} != ""){
			$contenu_ligne.=secure_chaine_csv($imgsup[$siteid]['legende'],1).";";
		}
	}
	
	//Descriptions
	foreach ($tab_siteid as $siteid){
		if (${'article_description'.$siteid} != ""){
			switch($donneesTexte){
				case 0:
					$description=$article_site[$siteid]['description'];
				break;
				case 1:
					$description=strip_tags($article_site[$siteid]['description']);
				break;
			}
			$contenu_ligne.=secure_chaine_csv($description).";";
		}
	}
	
	//Fiche technique
	foreach ($tab_siteid as $siteid){
		if (${'article_fichetechnique'.$siteid} != ""){
			switch($donneesTexte){
				case 0:
					$fichetechnique=$article_site[$siteid]['fichetechnique'];
				break;
				case 1:
					$fichetechnique=strip_tags($article_site[$siteid]['fichetechnique']);
				break;
			}
			$contenu_ligne.=secure_chaine_csv($fichetechnique).";";
		}
	}
	
	//Notre avis
	foreach ($tab_siteid as $siteid){
		if (${'article_notreavis'.$siteid} != ""){
			switch($donneesTexte){
				case 0:
					$notreavis=$article_site[$siteid]['notreavis'];
				break;
				case 1:
					$notreavis=strip_tags($article_site[$siteid]['notreavis']);
				break;
			}
			$contenu_ligne.=secure_chaine_csv($notreavis).";";
		}
	}
	
	//Caracts
	if ($article_caracteristiques != ""){
		$contenu_ligne.=$libCaract.";";
	}
	
	//Références par combinaison
	if ($article_stock_artcode != ""){
		$contenu_ligne.=$reference.";";
	}
	
	//Références fabricant par combinaison
	if ($article_stock_reference_fabricant != ""){
		$contenu_ligne.=$reference_fabricant.";";
	}
	
	//EAN par combinaison
	if ($article_stock_code_EAN != ""){
		$contenu_ligne.=$code_EAN.";";
	}
	
	//ASIN par combinaison
	if ($article_stock_ASIN != ""){
		$contenu_ligne.=$ASIN.";";
	}
	
	//Stock
	if ($article_stock_total != ""){
		$contenu_ligne.=$stock.";";
	}
	
	//Stock réservé
	if ($article_stock_reserv != ""){
		$contenu_ligne.=$stock_reserv.";";
	}
	
	//Seuil d'alerte
	if ($article_stock_seuil_alerte != ""){
		$contenu_ligne.=$seuil_alerte.";";
	}
	
	//Délai réappro
	if ($article_stock_delai_appro != ""){
		$contenu_ligne.=$delai_appro.";";
	}
	
	//Délai de livraison par combinaison
	if ($article_stock_delai_livraison != ""){
		$contenu_ligne.=$delai_livraison.";";
	}
	
	//Zone de stockage par combinaison
	if ($article_stock_zonestockage != ""){
		$contenu_ligne.=$zonestockage.";";
	}
	
	//Prix d'achat par combinaison
	if ($article_stock_prixachat != ""){
		$contenu_ligne.=str_replace($nonSeparateurDecimal,$seperateurDecimal,$prixachat).";";
	}
	
	//Prix de vente TTC par combinaison
	if ($article_stock_prix != ""){
		$contenu_ligne.=str_replace($nonSeparateurDecimal,$seperateurDecimal,$prix_vente).";";
	}
	
	// Colisage fournisseur
	if ($article_colisage != ""){
		$contenu_ligne.=$article[colisagefournisseur].";";
	}
	
	//Balises meta title
	foreach ($tab_siteid as $siteid){
		if (${'article_ref_title'.$siteid} != ""){
			switch($donneesTexte){
				case 0:
					$ref_title=$article_site[$siteid]['ref_title'];
				break;
				case 1:
					$ref_title=strip_tags($article_site[$siteid]['ref_title']);
				break;
			}
			$contenu_ligne.=secure_chaine_csv($ref_title,1).";";
		}
	}
	
	//Balises meta desc
	foreach ($tab_siteid as $siteid){
		if (${'article_ref_description'.$siteid} != ""){
			switch($donneesTexte){
				case 0:
					$ref_description=$article_site[$siteid]['ref_description'];
				break;
				case 1:
					$ref_description=strip_tags($article_site[$siteid]['ref_description']);
				break;
			}
			$contenu_ligne.=secure_chaine_csv($ref_description,1).";";
		}
	}
	
	//Balises meta key
	foreach ($tab_siteid as $siteid){
		if (${'article_ref_keywords'.$siteid} != ""){
			switch($donneesTexte){
				case 0:
					$ref_keywords=$article_site[$siteid]['ref_keywords'];
				break;
				case 1:
					$ref_keywords=strip_tags($article_site[$siteid]['ref_keywords']);
				break;
			}
			$contenu_ligne.=secure_chaine_csv($ref_keywords,1).";";
		}
	}
	
	//Articles conseillés
	if ($article_conseil != ""){
		$arts_conseilles="";
		$pipe="";
		$conseils=$DB_site->query("SELECT a.artcode FROM article_conseil ac inner join article a on (ac.artid_conseille = a.artid) where ac.artid = '$article[artid]' order by a.artcode");
		while ($conseil=$DB_site->fetch_array($conseils)){
			$arts_conseilles.=$pipe.$conseil[artcode];
			$pipe="|";
		}
		$contenu_ligne.=$arts_conseilles.";";
	}
	
	//Tags
	foreach ($tab_siteid as $siteid){		
		if (${'article_tags'.$siteid} != ""){	
			$libTag[$siteid]="";
			$article_tags=$DB_site->query("SELECT * FROM article_tag at INNER JOIN tags t ON (at.tagid=t.tagid) WHERE at.artid='$article[artid]' AND t.siteid='$siteid'");
			while($article_tag=$DB_site->fetch_array($article_tags)){
				$libTag[$siteid].=$article_tag['tag']."|";
			}
			$libTag[$siteid] = substr($libTag[$siteid], 0, -1);
			$contenu_ligne.=$libTag[$siteid].";";
		}
	}
	
	// Articles composant ce lot
	if ($article_bundle != ""){
		$libBundle="";
		$articles_bundle=$DB_site->query("SELECT * FROM bundle WHERE artid='$article[artid]'");
		while($rs_bundle=$DB_site->fetch_array($articles_bundle)){
			$libBundle.=$rs_bundle[artid_bundle]."|";
		}
		$libBundle = substr($libBundle, 0, -1);
		$contenu_ligne.=$libBundle.";";
	}
	
	//Prix promo (fait sur le prix de base, ne tient pas compte de modifprix caract).
	foreach ($tab_siteid as $siteid){		
		if (${'article_prixpromo'.$siteid} != ""){
			$contenu_ligne.=str_replace($nonSeparateurDecimal,$seperateurDecimal,$prixpromo[$siteid]).";";
		}
	}

	//Pct promo
	foreach ($tab_siteid as $siteid){		
		if (${'article_pctpromo'.$siteid} != ""){
			$contenu_ligne.=str_replace($nonSeparateurDecimal,$seperateurDecimal,$pctpromo[$siteid])."%;";
		}
	}	
	
	//Debut promo
	foreach ($tab_siteid as $siteid){		
		if (${'article_datedebut'.$siteid} != ""){
			$contenu_ligne.=$debutpromo[$siteid].";";
		}
	}
	
	//Fin promo
	foreach ($tab_siteid as $siteid){		
		if (${'article_datefin'.$siteid} != ""){
			$contenu_ligne.=$finpromo[$siteid].";";
		}
	}
	
	//Nombre de ventes
	foreach ($tab_siteid as $siteid){		
		if (${'article_nbventes'.$siteid} != ""){
			$nbVentes = $DB_site->query_first("SELECT SUM(lf.qte) FROM lignefacture lf INNER JOIN facture f USING(factureid) WHERE f.etatid IN (1,5) AND f.siteid='$siteid' AND lf.artid = '$article[artid]'");
			if ($nbVentes[0] == ""){
				$nbVentes[0] = 0;
			}
			$contenu_ligne.=$nbVentes[0].";";
		}
	}
	
	
	
	$contenu_ligne.="\n";
	fwrite($handle, $contenu_ligne);
}
/**
 * Fin remplissage fichier
 */



fclose($handle);
$file = realpath(".")."/export/csv/".$nom_fichier_export;
header('Content-Description: File Transfer');
header('Content-Type: application/force-download');
header('Content-Length: ' . filesize($file));
header('Content-Disposition: attachment; filename=' . basename($file));
readfile($file);
exit;

?>