<?php
include "./includes/header.php";

$referencepage="export_images";
$pagetitle = "Export d'images - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if (isset($action) && $action == "exportarticles") {
	$destinationZip = "../configurations/$host/images/produits/";
	$nomZip = "export".time().".zip";
	$listeFichiers = array();
	$i = 0;
	$where = "";
	$innerJoin = "";
	if ($critereVisible != ""){
		if ($critereVersion != ""){
				$where.=" AND activeV$critereVersion='$critereVisible'";
			}else{
				$where.=" AND (activeV1='$critereVisible' OR activeV2='$critereVisible')";
			}
	}
	if ($critereFournisseur != ""){
		$where.=" AND fournisseurid='$critereFournisseur'";
	}
	if ($critereCommandable != ""){
		$where.=" AND commandable='$critereCommandable'";
	}
	if ($critereVendu != ""){
		$tabArtsVendus = array();
		$artsVendus = $DB_site->query("SELECT DISTINCT(lf.artid) FROM lignefacture lf INNER JOIN facture f ON (lf.factureid=f.factureid) WHERE f.etatid IN (1,5)");
		while ($artVendu = $DB_site->fetch_array($artsVendus)){
			array_push($tabArtsVendus, $artVendu[artid]);
		}
		switch($critereVendu){
			case 0:
				$where.=" AND a.artid NOT IN (" . implode(',', $tabArtsVendus) . ")";
				break;
			case 1:
				$where.=" AND a.artid IN (" . implode(',', $tabArtsVendus) . ")";
				break;
			default:
				exit;
				break;
		}
	}
	if ($critereStockIllimite != ""){
		$where.=" AND stock_illimite='$critereStockIllimite'";
	}
	if ($critereImmateriel != ""){
		$where.=" AND immateriel='$critereImmateriel'";
	}
	if ($critereBundle != ""){
		$where.=" AND isbundle='$critereBundle'";
	}
	if ($critereSscateg == 1){
		if ($critereCatid != ""){
			$listeCatid = $critereCatid.catid_enfants($DB_site,$critereCatid);
			$listeCatid = str_replace(",","','",$listeCatid);
			$where.=" AND catid IN ('$listeCatid')";
		}
	}else{
		if ($critereCatid != ""){
			$where.=" AND catid='$critereCatid'";
		}
	}
	if ($critereMarque != "" || $criterePromotion != "" || $critereEnStock != "" || $critereVendu != "" || $critereNouveaute != "" || $critereTopVente != "" || $critereCoupDeCoeur != ""){
		if ($critereMarque != ""){
			$innerJoin.=" INNER JOIN article_marque am USING(artid)";
			$where.=" AND am.marqueid='$critereMarque'";
		}
		if ($criterePromotion != ""){
			$innerJoin.=" INNER JOIN article_promo_site aps USING(artid)";
			switch($criterePromotion){
				case 0:
					$where.=" AND aps.pctpromo IS NOT NULL AND aps.pctpromo = 0 OR aps.datedebut > " . time() . " OR aps.datefin < " . time();
					break;
				case 1:
					$where.=" AND aps.pctpromo IS NOT NULL AND aps.pctpromo > 0 AND aps.datedebut < " . time() . " AND aps.datefin > " . time();
					break;
				default:
					exit;
					break;
			}
		}
		if ($critereEnStock != ""){
			$innerJoin.=" INNER JOIN stock s USING(artid)";
			switch($critereEnStock){
				case 0:
					$where.=" AND s.nombre = 0";
					break;
				case 1:
					$where.=" AND (a.stock_illimite='1' OR s.nombre > 0)";
					break;
				default:
					exit;
					break;
			}
		}
		if ($critereNouveaute != ""){
			$innerJoin.=" INNER JOIN topnouveaute tn USING(artid)";
		}
		if ($critereTopVente != ""){
			$innerJoin.=" INNER JOIN topvente tv USING(artid)";
		}
		if ($critereCoupDeCoeur != ""){
			$innerJoin.=" INNER JOIN topcoupdecoeur tc USING(artid)";
		}
	}
	$selectgoogle = "";
	if (in_array(5922,$modules) && $article_googleshop == 'on'){
		$selectgoogle = ", gs.attributid";
		$innerJoin .= " INNER JOIN googleshopping gs ON (a.artid=gs.artid)";
	}
	if ($critereSite != "")
		$sql = "SELECT DISTINCT(a.artid), a.*, asite.* $selectgoogle FROM article a $innerJoin INNER JOIN article_site asite USING(artid) WHERE 1 $where AND siteid = '$critereSite' ORDER BY a.artid";
	else
		$sql = "SELECT DISTINCT(a.artid), a.*, asite.* $selectgoogle FROM article a $innerJoin INNER JOIN article_site asite USING(artid) WHERE 1 $where ORDER BY a.artid";
	$articles = $DB_site->query($sql);
	while($article = $DB_site->fetch_array($articles)) {
		if ($article[image] != "" && $article[image] != NULL) {
			$i++;
			$listeFichiers[$i] = array('emplacement' => $destinationZip.$article[artid].".".$article[image], 'nom' => $article[artcode].".".$article[image]);
		}
		$photos = $DB_site->query("SELECT * FROM articlephoto WHERE artid = '$article[artid]' ORDER BY position");
		while ($photo = $DB_site->fetch_array($photos)) {
			$i++;
			$listeFichiers[$i] = array('emplacement' => $destinationZip.$article[artid]."_".$photo[articlephotoid].".$photo[image]", 'nom' => $article[artcode]."_".$photo[position].".".$article[image]);
		}
	}
	if (count($listeFichiers)) {
		zipperFichiers($destinationZip.$nomZip, $listeFichiers);
		header("location: ../configurations/$host/images/produits/$nomZip");
	}else{
		header("location: export_images.php?erreur");
	}
	
}

if (isset($action) && $action == "exportcategories") {
	$destinationZip = "../configurations/$host/images/categories/FR/";
	$nomZip = "export" . time() . ".zip";
	$listeFichiers = array();
	$i = 0;
	$where = "";
	if ($critereVisible != "")
		$where .= " AND visible_treeviewV1 = '$critereVisible'";
	if ($critereVisible != "" && $critereContientArticle != "")
		$where.=" AND articles_actifsV1 = '$critereContientArticle'";
	if ($critereOnglet != "")
		$where.=" AND onglet='$critereOnglet'";
	if ($critereVisible != "" && $critereSite != "")
		$categories = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE 1 $where AND siteid = '$critereSite' ORDER BY catid");
	else
		$categories = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE 1 $where ORDER BY catid");
	while ($categorie = $DB_site->fetch_array($categories)) {
		if ($categorie[image] != "" && $categorie[image] != NULL) {
			++$i;
			$listeFichiers[$i] = array('emplacement' => $destinationZip.$categorie[catid].".".$categorie[image], 'nom' => $categorie[catid].".".$categorie[image]);
		}
		if (in_array ("5813", $modules)) {
			if ($categorie[image2] != "" && $categorie[image2] != NULL) {
				++$i;
				$listeFichiers[$i] = array('emplacement' => $destinationZip.$categorie[catid]."_2".".".$categorie[image2], 'nom' => $categorie[catid]."_2".".".$categorie[image2]);
			}
		}
		if (in_array ("5927", $modules)) {
			if ($categorie[image3] != "" && $categorie[image3] != NULL) {
				++$i;
				$listeFichiers[$i] = array('emplacement' => $destinationZip.$categorie[catid]."_3".".".$categorie[image3], 'nom' => $categorie[catid]."_3".".".$categorie[image3]);
			}
		}
	}
	if (count($listeFichiers)) {
		zipperFichiers($destinationZip.$nomZip, $listeFichiers);
		header("location: $destinationZip" . "$nomZip");
	}else{
		header("location: export_images.php?erreur");
	}
}

if (!isset($action) or $action == ""){
	if (isset($erreur))
	{
		$texteErreur = $multilangue[aucun_resultat];
		eval(charge_template($langue, $referencepage, "Erreur"));
	}
	$sites = $DB_site->query("SELECT * FROM site");
	while ($site = $DB_site->fetch_array($sites)) {
		eval(charge_template($langue, $referencepage, "ListeSite"));
	}
	$fournisseurs = $DB_site->query("SELECT DISTINCT(fournisseurid), libelle FROM fournisseur f
									INNER JOIN article a USING (fournisseurid)ORDER BY libelle");
	if ($DB_site->num_rows($fournisseurs > 0)) {
		while ($fournisseur = $DB_site->fetch_array($fournisseurs)) {
			eval(charge_template($langue, $referencepage, "ListeFournisseurBit"));
		}
		eval(charge_template($langue, $referencepage, "ListeFournisseur"));
	}
	$marques = $DB_site->query("SELECT DISTINCT(marqueid), libelle FROM marque m
								INNER JOIN marque_site ms USING(marqueid) 
								INNER JOIN article_marque am USING(marqueid) 
								WHERE siteid = '1' ORDER BY libelle");
	if ($DB_site->num_rows($marques > 0)) {
		while ($marque = $DB_site->fetch_array($marques)) {
			eval(charge_template($langue, $referencepage, "ListeMarqueBit"));
		}
		eval(charge_template($langue, $referencepage, "ListeMarque"));
	}
	if (in_array(5, $modules))
		eval(charge_template($langue, $referencepage, "ListePromotion"));
	if (in_array(17, $modules))
		eval(charge_template($langue, $referencepage, "ListeNouveaute"));
	if (in_array(19, $modules))
		eval(charge_template($langue, $referencepage, "ListeTopVente"));
	if (in_array(21, $modules))
		eval(charge_template($langue, $referencepage, "ListeCoupDeCoeur"));
	if (in_array(4, $modules))
		eval(charge_template($langue, $referencepage, "ListeEnStock"));
	if (in_array(5888, $modules))
		eval(charge_template($langue, $referencepage, "ListeImmateriel"));
	if (in_array(5901, $modules))
		eval(charge_template($langue, $referencepage, "ListeBundle"));
	eval(charge_template($langue, $referencepage, "Liste"));
}

$TemplateIncludejavascript = eval(charge_template($langue, $referencepage, "Includejavascript"));
eval(charge_template($langue, "commun", "header"));
eval(charge_template($langue, "commun", "footer"));
eval(charge_template($langue, $referencepage, "index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();

?>