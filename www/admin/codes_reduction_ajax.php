<?php
	include "includes/header.php";
	
if (isset($action) and $action == "jstreecateg"){
	header('Content-Type: application/json');
	$records = array();
	$records[] = array(
			"id" => 0,
			"parent" => "#",
			"text" => $titleFR,
			"state" => array("opened" => true)
	);
	$categories = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE siteid = '1' ORDER BY position");
	while ($categorie = $DB_site->fetch_array($categories)){
		$records[] = array(
				"id" => $categorie[catid],
				"parent" => $categorie[parentid],
				"text" => $categorie[libelle]/*,
				"state" => array("selected" => ($position[catid] ? true : false))*/
		);
	}
	echo json_encode($records);
}

if (isset($action) and $action == "jstreearticles"){
	header('Content-Type: application/json');
	$records = array();
	$records[] = array(
			"id" => "cat0",
			"parent" => "#",
			"text" => $titleFR,
			"state" => array("opened" => true)
	);
	
	$categories = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE siteid = '1' ORDER BY position");
	$arr_articles = array();
	while ($categorie = $DB_site->fetch_array($categories)){
		$records[] = array(
				"id" =>"cat$categorie[catid]",
				"parent" =>"cat$categorie[parentid]",
				"text" => $categorie[libelle]
		);
			
		$articles = $DB_site->query("SELECT * FROM article AS a
				INNER JOIN article_site AS asite USING(artid)
				WHERE siteid = '1'
				AND catid = '$categorie[catid]'
				ORDER BY asite.libelle");
					
		while ($article = $DB_site->fetch_array($articles)){
			$records[] = array(
					"id" => "art$article[artid]",
					"parent" => "cat$article[catid]",
					"text" => $article[libelle],
					"icon" => "fa fa-inbox"
			);
		}

	}
	$articles_niveau0 = $DB_site->query("SELECT * FROM article AS a INNER JOIN article_site AS asite USING(artid) WHERE asite.siteid = '1'  AND a.catid = '0' ORDER BY asite.libelle");
	while($article_niveau0 = $DB_site->fetch_array($articles_niveau0)){
		$records[] = array(
				"id" => "art$article_niveau0[artid]",
				"parent" => "cat0",
				"text" => $article_niveau0[libelle],
				"icon" => "fa fa-inbox"
		);
	}
	echo json_encode($records);
}

if(!isset($action) || $action = ""){
	$iDisplayLength = intval($length);
	$iDisplayStart = intval($start);

  	$records = array();
  	$records["aaData"] = array(); 

  	if(!isset($iDisplayStart))
  		$iDisplayStart = 0;
  	if(!isset($iDisplayLength))
  		$iDisplayLength = 10;
  	
  	switch($order[0][column]){
  		case "0" :
  			$orderby = "code";
  		break;
  		case "1" :
  			$orderby = "commentaire";
  		break;
  		case "2" :
  			$orderby = "typecadeauid";
  		break;
  		case "3" :
  			$orderby = "nbrfois";
  		break;
  		case "4" :
  			$orderby = "nbrfoiscommandes";
  		break;
  		case "4" :
  			$orderby = "datefin";
  		break;
  		case "10" :
  			$orderby = "totalcommandesttc";
  		break;
  		default:
  			$orderby = "cadeauid";
  		break;
  	}
  	
  	$sensorder = $order[0][dir];
  	$search[value] = securiserSql($search[value]);
	$where = "code LIKE '%$search[value]%'";
  	$where .= " OR commentaire LIKE '%$search[value]%'";
  	$where .= " OR valeurcadeau LIKE '%$search[value]%'";
  	$where .= " OR montantminimum LIKE '%$search[value]%'";
  	$where .= " OR nbrfois LIKE '%$search[value]%'";
  	$where .= " OR nbrfoiscommandes LIKE '%$search[value]%' OR totalcommandesttc LIKE '%$search[value]%'";
  	$cadeaux = $DB_site->query("SELECT * FROM cadeau WHERE $where ORDER BY $orderby $sensorder");
  	$cadeaucount = $DB_site->num_rows($cadeaux);
  	
  	$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
  	$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $cadeaucount);
  	
  	if ($cadeaucount > 0) {
  		$cadeaux = $DB_site->query("SELECT * FROM cadeau WHERE $where ORDER BY $orderby $sensorder LIMIT $limitlower, $perpage");
  		while ($cadeau = $DB_site->fetch_array($cadeaux)){
  			$devise = $DB_site->query_first("SELECT * FROM devise INNER JOIN site USING (deviseid) WHERE siteid = '$cadeau[siteid]'");
  			switch ($cadeau[typecadeauid]){
  				case "0":
  					$valeurcadeau = "$multilangue[montant] : <b>$cadeau[valeurcadeau] $devise[symbole]</b>";
  					if ($cadeau[montantminimum] > 0)
  						$valeurcadeau .= "  <i>-> $multilangue[a_partir_de] $cadeau[montantminimum] $devise[symbole] $multilangue[d_achat]</i>";
  					break;
  				case "1":
  					$valeurcadeau = "$multilangue[pourcentage] : <b>$cadeau[valeurcadeau] %</b>";
  					if ($cadeau[montantminimum] > 0)
  						$valeurcadeau .= "<br><i>-> $multilangue[montant] $cadeau[montantminimum] $devise[symbole] $multilangue[d_achat]</i>";
  					break;
  				case "2":
  					$valeurcadeau = "$multilangue[annule_frais_port_b]";
  					if ($cadeau[montantminimum] > 0)
  						$valeurcadeau .= "<br><i>-> $multilangue[a_partir_de] $cadeau[montantminimum] $devise[symbole] $multilangue[d_achat]</i>";
  					break;
  				case "3":
  					$valeurcadeau = "$multilangue[montant] : <b>$cadeau[valeurcadeau] $devise[symbole]</b> + $multilangue[annule_frais_port_b]";
  					if ($cadeau[montantminimum] > 0)
  						$valeurcadeau .= "  <i>-> $multilangue[a_partir_de] $cadeau[montantminimum] $devise[symbole] $multilangue[d_achat]</i>";
  					break;
  				case "4":
  					$valeurcadeau = "$multilangue[bonachat] : <b>$cadeau[valeurcadeau] $devise[symbole]</b>";
  					if ($cadeau[montantminimum] > 0)
  						$valeurcadeau .= "  <i>-> $multilangue[a_partir_de] $cadeau[montantminimum] $devise[symbole] $multilangue[d_achat]</i>";
  					break;
  				case "5" :
  					$valeurcadeau = $multilangue[desc_promoprogressif];
  					if ($cadeau[montantminimum] > 0)
  						$valeurcadeau .= "  <i>-> $multilangue[a_partir_de] $cadeau[montantminimum] $devise[symbole] $multilangue[d_achat]</i>";
  					break;
  			}
  			$nbrfois = ($cadeau[nbrfois] == "0" ? $multilangue[illimite] : $cadeau[nbrfois]);
  			$nbrmaxi = "$cadeau[nbrfoiscommandes] / " . ($cadeau[nbrmaxi] == "0" ? $multilangue[illimite] : $cadeau[nbrmaxi]);
  			$date = ($cadeau[datefin] == "0" ? $multilangue[aucune] : date("d/m/Y", $cadeau[datefin]));
  			$rayons = "";
  			$Rayons = $DB_site->query("SELECT * FROM categorie_cadeau INNER JOIN categorie USING(catid) INNER JOIN categorie_site USING(catid)
  									  WHERE cadeauid = '$cadeau[cadeauid]' ORDER BY libelle");
  			if ($DB_site->num_rows($Rayons) > 0) {
  				while ($Rayon = $DB_site->fetch_array($Rayons))
  					$rayons .= "$Rayon[libelle]<br>" ;
  			}else{
  				$rayons = "--";
  			}
  			$articles = "";
  			$Articles = $DB_site->query("SELECT * FROM article_cadeau INNER JOIN article USING(artid) INNER JOIN article_site USING(artid)
  										WHERE cadeauid = '$cadeau[cadeauid]' ORDER BY libelle");
  			if ($DB_site->num_rows($Articles) > 0) {
  				if($DB_site->num_rows($Articles) > 1) {
  					$articles .= $multilangue[plusieurs_articles];
  				}else{
  					while ($Article = $DB_site->fetch_array($Articles))
  						$articles .= "$Article[libelle]<br>" ;
  				}
  			}else{
  				$articles = "--";
  			}
  			if ($cadeau[userid]) {
  				$user = $DB_site->query_first("SELECT * FROM utilisateur WHERE userid = '$cadeau[userid]'");
  				$user = "$user[nom] $user[prenom]";
  			}else{
  				$user = $multilangue[tous];
  			}
  			if ($cadeau[active] == 1)
  				$actif = '<div data-original-title="' . $multilangue[desactiver] . '" data-placement="top" data-value="' . $cadeau[cadeauid] . '" class="btn default btn-sm green tooltips"><i class="fa fa-check-square-o"></i></div>';
  			else
  				$actif = '<div data-original-title="' . $multilangue[activer] . '" data-placement="top" data-value="' . $cadeau[cadeauid] . '" class="btn default btn-sm red tooltips"><i class="fa fa-square-o"></i></div>';
  			$action = '<a href="codes_reduction.php?action=modifier&cadeauid=' . $cadeau[cadeauid] . '" data-original-title="' . $multilangue[copier] . '" data-placement="top" class="btn tooltips">';
  			$action .= '<i class="fa fa-copy fs-18 font-blue"></i>';
  			$action .= '</a>';
  			if ($cadeau[nbrfoiscommandes] > 0){
  				$action .= '<a href="codes_reduction.php?action=voirutilisations&cadeauid=' . $cadeau[cadeauid] . '" data-original-title="' . $multilangue[voir] . '" data-placement="top" class="btn tooltips">';
				$action .= '<i class="fa fa-align-left fs-18 font-blue"></i>';
				$action .= '</a>';
  			}else{
  				$action .= '<a href="#myModal' . $cadeau[cadeauid] . '" id="btn_suppr' . $cadeau[cadeauid]. '" data-original-title="' . $multilangue[supprimer] . '" data-placement="top" data-toggle="modal" role="button" class="btn tooltips">';
				$action .= '<i class="fa fa-trash-o fs-18 font-red"></i>';
				$action .= '</a>';
				$action .= '<div aria-hidden="true" aria-labelledby="myModalLabe' . $cadeau[cadeauid] . '" role="dialog" tabindex="-1" class="modal fade" id="myModal' . $cadeau[cadeauid] . '" style="display: none;">';
				$action .= '<div class="modal-dialog">';
				$action .= '<div class="modal-content">';
				$action .= '<div class="modal-header">';
				$action .= '<button aria-hidden="true" data-dismiss="modal" class="close" type="button"></button>';
				$action .= '<h4 class="modal-title">'.$multilangue[suppression_code].'"' . $cadeau[code] . '" ?</h4>';
				$action .= '</div>';
				$action .= '<div class="modal-body">';																			
				$action .= $multilangue[suppression_code_infos];
				$action .= '</div>';
				$action .= '<div class="modal-footer">';
				$action .= '<button aria-hidden="true" data-dismiss="modal" class="btn default">'.$multilangue[non].'</button>';
				$action .= '<a href="codes_reduction.php?action=supprimer&cadeauid=' . $cadeau[cadeauid] . '" class="btn blue">'.$multilangue[oui_supprimer].'</a>';
				$action .= '</div>';
				$action .= '</div>';
				$action .= '</div>';
				$action .= '</div>';
  			}
			$cadeau[totalcommandesttc] = formaterPrix($cadeau[totalcommandesttc]) . " $devise[symbole]";
  			$records["aaData"][] = array(
  				"siteid" => $devise[libelle],
				"code" => $cadeau[code],
				"commentaire" => $cadeau[commentaire],
				"valeurcadeau" => $valeurcadeau,
				"nbrfois" => $nbrfois,
				"nbrmaxi" => $nbrmaxi,
				"date" => $date,
				"rayons" => $rayons,
				"articles" => $articles,
				"user" => $user,
				"actif" => $actif,
				"total"	=> $cadeau[totalcommandesttc],
				"action" => $action
	   		);
  		}
  	}

  	$records["iTotalRecords"] = $cadeaucount;
  	$records["iTotalDisplayRecords"] = $cadeaucount;
 
  	echo json_encode($records);
}
?>