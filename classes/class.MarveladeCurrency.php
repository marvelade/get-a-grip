<?php

class MarveladeCurrency
{
	public static function display_currencies_select($def = 0)
	{
		$retval = null;
		
		require("inc.dbconnect.php");
		$sql = "SELECT currency_id, currency_iso_code 
					FROM " . Settings::get('TBL_PREFIX') . "tbl_currencies 
					WHERE 1
					ORDER BY currency_iso_code ASC";
		
		$stmt = $dbh -> prepare($sql);
		$stmt -> execute();
		
		$retval = '<select name="currency">' . "\n";
		
		
		
		while($row = $stmt -> fetch(PDO::FETCH_ASSOC))
		{
			$selected = $row['currency_id'] == $def ? " SELECTED" : "";
		
			$retval.= '<option value="' . $row['currency_id'] . '"' . $selected . '>' . $row['currency_iso_code'] . "</option>\n";
		}
		
		
		$retval.= '</select>' . "\n";
		
		return $retval;
	}
}
?>