<?
require_once $_SERVER['DOCUMENT_ROOT']."admin2/includes/admin_global.php";

$return = "";

//si $neteven_caracteristiquevaleurid est renseign� on recup�re neteven_univerid / neteven_caracteristiqueid

if($neteven_caracteristiquevaleurid != "") {
   	$sql="SELECT nc.neteven_univerid, nc.neteven_caracteristiqueid FROM neteven_caracteristique nc ";
	$sql.="INNER JOIN neteven_caracteristiquevaleur ncv ON (ncv.neteven_caracteristiqueid=nc.neteven_caracteristiqueid) ";
	$sql.="WHERE ncv.neteven_caracteristiquevaleurid = '$neteven_caracteristiquevaleurid'";
	
	$arrRecup = $DB_site->query_first($sql);
	$neteven_univerid = $arrRecup[neteven_univerid];
	$neteven_caracteristiqueid = $arrRecup[neteven_caracteristiqueid];
}elseif($neteven_caracteristiqueid != ""){
	$sql="SELECT nc.neteven_univerid, nc.neteven_caracteristiqueid FROM neteven_caracteristique nc ";
	$sql.="WHERE nc.neteven_caracteristiqueid = '$neteven_caracteristiqueid'";
	
	$arrRecup = $DB_site->query_first($sql);
	$neteven_univerid = $arrRecup[neteven_univerid];
}

//univeneteven_univers
//$return.="<b>Neteven Univers</b><br>";
$return.="<select name='neteven_univerid' class=\"form-control\" id='neteven_univerid' onchange='majSelectNeteven();'>";
$return.="<option value='0'>------------</option>";

$sql_univer = "SELECT * FROM neteven_univer ORDER BY libelle";
$req_univer = $DB_site->query($sql_univer);

while ($arr_univer=$DB_site->fetch_array($req_univer)){
	$selected="";
	if($neteven_univerid==$arr_univer[neteven_univerid]) {
		$selected="selected";
	}
	$return.="<option value=\"$arr_univer[neteven_univerid]\" $selected>$arr_univer[libelle]</option>";
}

$return.="</select>";


//neteven_caracteristique
if($neteven_univerid != "") {
	$return.="<br><br><b>Neteven Caract�ristiques</b><br>";
	$return.="<select name='neteven_caracteristiqueid' class=\"form-control\" id='neteven_caracteristiqueid' onchange='majSelectNeteven();'>";
	$return.="<option value='0'>------------</option>";
	
	$sql_carac = "SELECT * FROM neteven_caracteristique WHERE neteven_univerid = '$neteven_univerid' ORDER BY libelle";
	$req_carac = $DB_site->query($sql_carac);
	
	while ($arr_carac=$DB_site->fetch_array($req_carac)){
		$selected="";
		if($neteven_caracteristiqueid==$arr_carac[neteven_caracteristiqueid]) {
			$selected="selected";
		}
		$return.="<option value=\"$arr_carac[neteven_caracteristiqueid]\" $selected>$arr_carac[libelle]</option>";
	}
	$return.="</select>";
}

//neteven_caracteristiquevaleur
if($neteven_caracteristiqueid != "") {
	$return.="<br><br><b>Neteven Caract�ristiques valeurs</b><br>";
	$return.="<select name='neteven_caracteristiquevaleurid' class=\"form-control\" id='neteven_caracteristiquevaleurid'>";
	$return.="<option value='0'>------------</option>";
	
	$sql_caracVal = "SELECT * FROM neteven_caracteristiquevaleur WHERE neteven_caracteristiqueid = '$neteven_caracteristiqueid' ORDER BY libelle";
	$req_caracVal = $DB_site->query($sql_caracVal);
	
	while ($arr_caracVal=$DB_site->fetch_array($req_caracVal)){
		$selected="";
		if($neteven_caracteristiquevaleurid==$arr_caracVal[neteven_caracteristiquevaleurid]) {
			$selected="selected";
		}
		$infoSup = "";
		if($arr_caracVal[info_sup] != "") {
			$infoSup = " ($arr_caracVal[info_sup])";	
		}
		$return.="<option value=\"$arr_caracVal[neteven_caracteristiquevaleurid]\" $selected>$arr_caracVal[libelle]$infoSup</option>";
	}
	$return.="</select>";
}

echo $return;

?>