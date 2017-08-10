<?php
include "./includes/header.php";

$referencepage="suivi_activite";
$pagetitle = "Suivi d'activité (prospects et paniers) - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}



if (isset($action) and $action == "details"){
	$session = $DB_site->query_first("SELECT * FROM session_unique WHERE sessionid = '$sessionid'");
	$user = $DB_site->query_first("SELECT * FROM utilisateur WHERE userid = '$session[userid]'");
	$pages_vues = $DB_site->query("SELECT * FROM session_action WHERE session_chaine = '$session[session_chaine]' ORDER BY date_action DESC");
	$pays = $DB_site->query_first("SELECT * FROM pays WHERE paysid = '$session[paysid]'");
	$count = $DB_site->num_rows($pages_vues);
	$ip = chainetoip($session[ipsession]);
	if ($user[userid]){
		eval(charge_template($langue, $referencepage, "DetailsTitre"));
		eval(charge_template($langue, $referencepage, "DetailsValue"));
		eval(charge_template($langue, $referencepage, "DetailsActionTitre"));
		eval(charge_template($langue, $referencepage, "DetailsAction"));
	}
	while ($page_vue = $DB_site->fetch_array($pages_vues)){
		$page_vue[date_action] = date("d/m/Y H:i:s", $page_vue[date_action]);
		$texte_action = TraduireAction($DB_site, $page_vue[page_affichee] , $page_vue[get_action], $page_vue[post_action]);
		eval(charge_template($langue, $referencepage, "DetailsBit"));
	}
	if ($session[panierid]){
		$total = 0;
		$lignes = $DB_site->query("SELECT * FROM lignepanier INNER JOIN article USING(artid) INNER JOIN article_site USING(artid) WHERE panierid = '$session[panierid]'");
		while ($ligne = $DB_site->fetch_array($lignes)){
			$ligne[lp_prix] = formaterPrix($ligne[lp_prix] * $ligne[qte]);
			$total += ($ligne[lp_prix] * $ligne[qte]);
			eval(charge_template($langue, $referencepage, "DetailsPanierBit"));
		}
		$panier = $DB_site->query_first("SELECT * FROM panier WHERE panierid = '$session[panierid]'");
		$total += $panier[montantport];
		$panier[montantport] = formaterPrix($panier[montantport]);
		$total = formaterPrix($total);
		eval(charge_template($langue, $referencepage, "DetailsPanier"));
	}
	$libNavigSupp = $multilangue[liste_actions_session];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	eval(charge_template($langue, $referencepage, "Details"));
}

if (!isset($action) or $action == ""){
	$sessions = $DB_site->query("SELECT * FROM session_unique WHERE date_derniere_action > '" . (time() - 1200) . "'");
	$texteInfo = $multilangue[il_y_a_actuellement]." ". $DB_site->num_rows($sessions) . $multilangue[personnes_connectees_site];
	eval(charge_template($langue, $referencepage, "Info"));
	$libNavigSupp = $multilangue[liste_sessions_actives];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
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