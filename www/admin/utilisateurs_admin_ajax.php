<?php
	include "includes/header.php";

	if (!isset($action) or $action == ""){
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
	  			$orderby = "userid";
	  		break;
	  		case "1" :
	  			$orderby = "mail";
	  		break;
	  		case "2" :
	  			$orderby = "nom";
	  		break;
	  		case "3" :
	  			$orderby = "prenom";
	  		break;
	  		case "4" :
	  			$orderby = "telephone";
	  		break;
	  		case "5" :
	  			$orderby = "groupeid";
	  		break;
	  		default:
	  			$orderby = "userid";
	  		break;
	  	}
	  	
	  	$sensorder = $order[0][dir];
	  	
		$where = "AND (username LIKE '%$search[value]%'";
	  	$where .= " OR nom LIKE '%$search[value]%'";
	  	$where .= " OR prenom LIKE '%$search[value]%'";
	  	$where .= " OR mail LIKE '%$search[value]%'";
	  	$where .= " OR userid LIKE '%$search[value]%')";
	  	$utilisateurs = $DB_site->query("SELECT * FROM admin_utilisateur WHERE deleted = '0' $where ORDER BY $orderby $sensorder");
	  	$utilisateurcount = $DB_site->num_rows($utilisateurs);
	  	
	  	$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
	  	$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $utilisateurcount);
	  	
	  	if ($utilisateurcount > 0) {
	  	$utilisateurs = $DB_site->query("SELECT * FROM admin_utilisateur WHERE deleted = '0' $where ORDER BY $orderby $sensorder");
	  		while ($utilisateur = $DB_site->fetch_array($utilisateurs)){
	  			$cheminsanshref = "";

	  			$actions = '<a href="utilisateurs_admin.php?action=modifier&userid=' . $utilisateur[userid] . '" data-original-title="' . $multilangue[modifier] . '" data-placement="top" class="btn tooltips">';
	  			$actions .= '<i class="fa fa-edit fs-18 font-blue"></i>';
	  			$actions .= '</a>';
	  			$actions .= '<a href="#myModal' . $utilisateur[userid] . '" id="btn_suppr' . $utilisateur[userid]. '" data-original-title="' . $multilangue[supprimer] . '" data-placement="top" data-toggle="modal" role="button" class="btn tooltips">';
	  			$actions .= '<i class="fa fa-trash-o fs-18 font-red"></i>';
	  			$actions .= '</a>';
	  			$actions .= '<div aria-hidden="true" aria-labelledby="myModalLabe' . $utilisateur[userid] . '" role="dialog" tabindex="-1" class="modal fade" id="myModal' . $utilisateur[userid] . '" style="display: none;">';
	  			$actions .= '<div class="modal-dialog">';
	  			$actions .= '<div class="modal-content">';
	  			$actions .= '<div class="modal-header">';
	  			$actions .= '<button aria-hidden="true" data-dismiss="modal" class="close" type="button"></button>';
	  			$actions .= '<h4 class="modal-title">"'.$multilangue[suppression_utilisateur].'" "' . $utilisateur[username] . '" ?</h4>';
	  			$actions .= '</div>';
	  			$actions .= '<div class="modal-body">';
	  			$actions .= $multilangue[suppression_utilisateur];
	  			$actions .= '</div>';
	  			$actions .= '<div class="modal-footer">';
	  			$actions .= '<button aria-hidden="true" data-dismiss="modal" class="btn default">"'.$multilangue[non].'"</button>';
	  			$actions .= '<a href="utilisateurs_admin.php?action=supprimer&userid=' . $utilisateur[userid] . '" class="btn blue">"'.$multilangue[oui_supprimer].'"</a>';
	  			$actions .= '</div>';
	  			$actions .= '</div>';
	  			$actions .= '</div>';
	  			$actions .= '</div>';
				
				// Groupe utilisateur admin
				$groupe = $DB_site->query_first("SELECT libelle FROM admin_groupe WHERE groupeid = '".$utilisateur[groupeid]."' ");
				$utilisateur[groupe] = $groupe[0];
				
	  			$records["aaData"][] = array(
					"userid" => $utilisateur[userid],
					"username" => $utilisateur[username],
					"nom" => $utilisateur[nom],
					"prenom" => $utilisateur[prenom],
					"mail" => $utilisateur[mail],
					"telephone" => $utilisateur[telephone],
					"groupe" => $utilisateur[groupe],
					"action" => $actions
		   		);
	  		}
	  	}
	
	  	$records["iTotalRecords"] = $utilisateurcount;
	  	$records["iTotalDisplayRecords"] = $utilisateurcount;
	 
	  	echo json_encode($records);
	}
?>