<?php
include "./includes/header.php";

$referencepage="derniers_mouvements_stocks";
$pagetitle = "Derniers mouvements de stock - $host - Admin Arobases";

if(!parse_template($referencepage,  $langue)){
	echo "erreur de chargement de template";
}

if (!isset($action) or $action == ""){
	$count = $DB_site->query_first("SELECT COUNT(*) count FROM stock_historique sh 
									INNER JOIN article a ON (a.artid = sh.artid) WHERE sh.delta != 0");
	if ($count[count] > 0){
		$historiques = $DB_site->query("SELECT * FROM stock_historique sh 
										INNER JOIN article a ON (a.artid = sh.artid) 
										WHERE sh.delta != 0 
										ORDER BY sh.dateline DESC LIMIT 10");
		while ($historique = $DB_site->fetch_array($historiques)) {
			$date = date("d/m/Y",  $historique[dateline]);
			$designation = $DB_site->query_first("SELECT *,a.image as image FROM article a INNER JOIN article_site USING(artid)
												WHERE a.artid = '$historique[artid]' AND siteid = '1'");
			if ($designation[image] != "")
				$img = 'http://' . $host . '/br-' . url_rewrite($designation[libelle]) . '-' . $designation[artid] . '.' . $designation[image];

			else
				$img = "";
			$facture = $DB_site->query_first("SELECT * FROM lignefacture lf 
											INNER JOIN facture f USING(factureid) 
											WHERE artid = '$historique[artid]' 
											AND factureid IN (SELECT factureid FROM facture WHERE datedecrementation = '$date' ORDER BY factureid DESC )");
			if ($historique[delta] > 0)
				$color = "text-success";
			else
				$color = "text-danger";
			eval(charge_template($langue,  $referencepage,  "ListeBit"));
		}
		eval(charge_template($langue,  $referencepage, "Liste"));
	}else{
		$texteInfo = "$multilangue[aucun] $multilangue[mouvements_des_stocks].";
		eval(charge_template($langue,  $referencepage, "Info"));
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