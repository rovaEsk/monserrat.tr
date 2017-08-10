<?php
	include "includes/header.php";
	
	$iDisplayLength = intval($length);
	$iDisplayStart = intval($start);
	
	$records = array();
	$records["aaData"] = array();
	
	if(!isset($iDisplayStart))
		$iDisplayStart = 0;
	if(!isset($iDisplayLength))
		$iDisplayLength = 100;
	
	switch($order[0][column]){
		case "0" :
			$orderby = "sessionid";
			break;
		case "1" :
			$orderby = "ipsession";
			break;
		case "2" :
			$orderby = "date_derniere_action";
			break;
		case "7" :
			$orderby = "pages_vues";
			break;
		default:
			$orderby = "sessionid";
		break;
	}
	 
	$sensorder = $order[0][dir];
	$search[value] = securiserSql($search[value]);
	$where = "sessionid LIKE '%$search[value]%'";
	$where .= " OR ipsession LIKE '%$search[value]%'";
	$sessions = $DB_site->query("SELECT *, COUNT(session_chaine) pages_vues FROM session_unique INNER JOIN session_action USING(session_chaine) WHERE $where GROUP BY session_chaine ORDER BY $orderby $sensorder");
	$sessioncount = $DB_site->num_rows($sessions);
	 
	$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
	$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $sessioncount);
	$color = array("3C763D","48703D","546A3E","60653E","6C5F3F","785A3F","845440","904F40","9C4941","A94442");
	
	if ($sessioncount > 0) {
		$sessions = $DB_site->query("SELECT *, COUNT(session_chaine) pages_vues FROM session_unique INNER JOIN session_action USING(session_chaine) WHERE $where GROUP BY session_chaine ORDER BY $orderby $sensorder LIMIT $limitlower, $perpage");
		while ($session = $DB_site->fetch_array($sessions)){
			$user = $DB_site->query_first("SELECT * FROM utilisateur WHERE userid = '$session[userid]'");
			$pays = $DB_site->query_first("SELECT * FROM pays WHERE paysid = '$session[paysid]'");
			$records["aaData"][] = array(
					"sessionid" => $session[sessionid],
					"ipsession" => chainetoip($session[ipsession]),
					"date_derniere_action" => date("d/m/Y H:i:s", $session[date_derniere_action]),
					"pays" => $pays[libelle],
					"panier" => '<span class="' . ($session[panierid] != '0' ? 'text-success' : '') . '">' . ($session[panierid] != '0' ? $multilangue[oui] : $multilangue[non]) . '</span>',
					"nom" => $user[nom],
					"prenom" => $user[prenom],
					"pages_vues" => '<font color="#' . $color[((10 * $session[pages_vues]) / 100) - 1] . '">' . $session[pages_vues] . '</font>'
			);
		}
	}

	$records["iTotalRecords"] = $sessioncount;
	$records["iTotalDisplayRecords"] = $sessioncount;
	
	echo json_encode($records);
?>