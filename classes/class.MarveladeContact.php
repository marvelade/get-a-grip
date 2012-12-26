<?php

class MarveladeContact
{
	const GET_SUPPLIERS = -1;
	const GET_CUSTOMERS = 1;
	const GET_ALL = 0;

	public function __construct($candidate_id)
	{
		
		require("inc.dbconnect.php");
		if(intval($candidate_id) !== 0 && intval($candidate_id) !== -1 )
		{
			$this -> id = intval($candidate_id);
			
			$sql = "SELECT * FROM " . Settings::get('TBL_PREFIX') . "tbl_contacts
					WHERE contact_id=:cid
					LIMIT 1";
			
			
			$stmt = $dbh -> prepare($sql);
			$stmt -> bindValue(':cid', $this -> id, PDO::PARAM_INT);
			
			$stmt -> execute(); 
			
			$row = $stmt -> fetch(PDO::FETCH_ASSOC);
			
			if(is_array($row))
			{
				$this -> set_contact_name($row['contact_name']);
				$this -> set_contact_color($row['contact_color']);
				$this -> set_contact_is_customer($row['contact_is_customer'] == 1);
				$this -> set_contact_is_supplier($row['contact_is_supplier'] == 1);
				$this -> set_contact_is_active($row['contact_active'] == 1);
			}
			else
			{
				throw new Exception(__CLASS__ . ' constructor died : contact ID "' . $candidate_id . '" is not present in the database.');
			}
			
		}
		elseif($candidate_id == -1)
		{
			echo __LINE__ . "<br />";
			//let it slide, this is for a new customer
			// so we don't assign an ID
		}
		else
		{
			throw new Exception(__CLASS__ . ' constructor died (candidate_id = ' . $candidate_id . ')');
		}
		

	}
	
	
	
	public function set_contact_name($contact_name)
	{
		$this -> contact_name = $contact_name;
	}
	
	public function get_contact_name()
	{
		if(isset($this -> contact_name))
		{
			return $this -> contact_name;
		}
		else
		{
			return(false);
		}
	}
	
	
	public function set_contact_color($contact_color)
	{
		$this -> contact_color = $contact_color;
	}
	
	public function get_contact_color()
	{
		if(isset($this -> contact_color))
		{
			return $this -> contact_color;
		}
		else
		{
			return(false);
		}
	}



	public function set_contact_is_customer($contact_is_customer)
	{
		$this -> contact_is_customer = $contact_is_customer;
	}
	
	public function get_contact_is_customer()
	{
		if(isset($this -> contact_is_customer))
		{
			return $this -> contact_is_customer;
		}
		else
		{
			return(null); // null because the return value is boolean itself
		}
	}
	
	

	public function set_contact_is_supplier($contact_is_supplier)
	{
		$this -> contact_is_supplier = $contact_is_supplier;
	}
	
	public function get_contact_is_supplier()
	{
		if(isset($this -> contact_is_supplier))
		{
			return $this -> contact_is_supplier;
		}
		else
		{
			return(null); // null because the return value is boolean itself
		}
	}


	public function set_contact_is_active($contact_is_active)
	{
		$this -> contact_is_active = $contact_is_active;
	}
	
	public function get_contact_is_active()
	{
		if(isset($this -> contact_is_active))
		{
			return $this -> contact_is_active;
		}
		else
		{
			return(null); // null because the return value is boolean itself
		}
	}


	
	public function toggle_activity($referer)
	{
		$pi = (pathinfo($referer));
		if($pi['basename'] != 'contacts-manage.php')
		{
			throw new Exception(__METHOD__ . ': Wrong caller');
			return false;
		}
		else
		{
			require("inc.dbconnect.php");
			$sql = "UPDATE " . Settings::get('TBL_PREFIX') . "tbl_contacts
					SET contact_active = ABS(contact_active - 1)
					WHERE contact_id=:cid";
					
			$stmt = $dbh -> prepare($sql);
			$stmt -> bindValue(':cid', $this -> id, PDO::PARAM_INT);
			return($stmt -> execute());
		}
	}
	
	
	public function display_input_form($mode)
	{
		

		$retval = <<<EOF
				<table>
					
					<form action="{$_SERVER['PHP_SELF']}" method="post" onSubmit="return check(this);">
						<input type="hidden" name="c_id" value="{contact_id}" />
						<tr>
							<td>Contact Name</td>
							<td><input type="text" name="c_name" value="{name_value}" size="50"></td>
						</tr>
						<tr>
							<td>Contact is a customer?</td>
							<td><input type="checkbox" name="c_is_cst"{is_cst_checked}/></td>
						</tr>
						<tr>
							<td>Contact is a supplier?</td>
							<td><input type="checkbox" name="c_is_supp"{is_supp_checked}/></td>
						</tr>
						<tr>
							<td>Contact Active?</td>
							<td><input type="checkbox" name="c_is_active"{is_active_checked} /></td>
						</tr>
						<tr>
							<td>Contact color?</td>
							<td>#&nbsp;<input type="text" name="c_color" value="{color_value}" /></td>
						</tr>
						<tr>
							<td></td>
							<td><input type="submit" name="go" value="{btn_text}"></td>
						</tr>
					</form>
				</table>	
		
		
EOF;

		switch($mode)
		{
		
			case "INSERT":
				$retval = str_replace("{contact_id}", "", $retval);
				$retval = str_replace("{name_value}", "", $retval);
				$retval = str_replace("{is_cst_checked}", "", $retval);
				$retval = str_replace("{is_supp_checked}", "", $retval);
				$retval = str_replace("{is_active_checked}", "", $retval);
				$retval = str_replace("{color_value}", "", $retval);
				$retval = str_replace("{btn_text}", "Save new contact", $retval);
			break;
			
			case "UPDATE":
			

					require("inc.dbconnect.php");
					$sql = "SELECT * FROM " . Settings::get('TBL_PREFIX') . "tbl_contacts WHERE contact_id = :cid";
					$stmt = $dbh -> prepare($sql);
					$stmt -> bindValue(':cid', $this -> id, PDO::PARAM_INT);
					$stmt -> execute();
					
					while($row = $stmt -> fetch(PDO::FETCH_ASSOC))
					{
					
						$c_name = $row['contact_name'];
						$c_is_customer = ($row['contact_is_customer'] == 1) ? "CHECKED" : "";
						$c_is_supplier = ($row['contact_is_supplier'] == 1) ? "CHECKED" : "";
						$c_is_active = ($row['contact_active'] == 1) ? "CHECKED" : "";
					
						$retval = str_replace("{contact_id}", $this -> id, $retval);
						$retval = str_replace("{name_value}", htmlspecialchars($c_name), $retval);
						$retval = str_replace("{is_cst_checked}", $c_is_customer, $retval);
						$retval = str_replace("{is_supp_checked}", $c_is_supplier, $retval);
						$retval = str_replace("{is_active_checked}", $c_is_active, $retval);
						$retval = str_replace("{color_value}", $this -> contact_color, $retval);
						$retval = str_replace("{btn_text}", "Update contact", $retval);
					}
			break;
			
			default:
				throw new Exception(__METHOD__ . ': false mode argument supplied:"' . $mode . '"');
			break;
		
		
		}

		return $retval;
	

	}
	
	
	public function update_properties($data_array)
	{
		require("inc.dbconnect.php");
		$sql = "UPDATE " . Settings::get('TBL_PREFIX') . "tbl_contacts SET 
					contact_name=:c_name,
					contact_is_supplier=:c_is_suppl,
					contact_is_customer=:c_is_cust,
					contact_active=:c_is_active,
					contact_color=:c_color
				
				WHERE contact_id='" . $this -> id . "' LIMIT 1
				";
				
		$stmt = $dbh -> prepare($sql);
		
		$stmt -> bindParam(':c_name', htmlspecialchars_decode($data_array['c_name']), PDO::PARAM_STR);
		$stmt -> bindParam(':c_is_suppl', intval(isset($data_array['c_is_supp'])), PDO::PARAM_INT);
		$stmt -> bindParam(':c_is_cust', intval(isset($data_array['c_is_cst'])), PDO::PARAM_INT);
		$stmt -> bindParam(':c_is_active', intval(isset($data_array['c_is_active'])), PDO::PARAM_INT);
		$stmt -> bindParam(':c_color', htmlspecialchars_decode($data_array['c_color']), PDO::PARAM_STR);

		
		return($stmt -> execute());
	}
	
	
	
	public function set_properties($data_array)
	{
		require("inc.dbconnect.php");
		$sql = "INSERT INTO " . Settings::get('TBL_PREFIX') . "tbl_contacts SET 
					contact_name=:c_name,
					contact_is_supplier=:c_is_suppl,
					contact_is_customer=:c_is_cust,
					contact_active=:c_is_active,
					contact_color=:c_color
				";
			
				
		$stmt = $dbh -> prepare($sql);
		
		$stmt -> bindParam(':c_name', htmlspecialchars_decode($data_array['c_name']), PDO::PARAM_STR);
		$stmt -> bindParam(':c_is_suppl', intval(isset($data_array['c_is_supp'])), PDO::PARAM_INT);
		$stmt -> bindParam(':c_is_cust', intval(isset($data_array['c_is_cst'])), PDO::PARAM_INT);
		$stmt -> bindParam(':c_is_active', intval(isset($data_array['c_is_active'])), PDO::PARAM_INT);
		$stmt -> bindParam(':c_color', intval(isset($data_array['c_color'])), PDO::PARAM_STR);

		
		return($stmt -> execute());
	}



	static function display_contacts_select($def = 0)
	{
		$retval = null;
		
		require("inc.dbconnect.php");
		$sql = "SELECT contact_id, contact_name, contact_color 
					FROM " . Settings::get('TBL_PREFIX') . "tbl_contacts 
					WHERE 1
					ORDER BY contact_name ASC";
		
		$stmt = $dbh -> prepare($sql);
		$stmt -> execute();
		
		$retval = '<select name="contact">' . "\n";
		$retval.= '<option value="0">-- select contact --</option>' . "\n";
		
		
		while($row = $stmt -> fetch(PDO::FETCH_ASSOC))
		{
			$selected = $row['contact_id'] == $def ? " SELECTED" : "";
		
			$retval.= '<option value="' . $row['contact_id'] . '"' . $selected . '>' . $row['contact_name'] . "</option>\n";
		}
		
		
		$retval.= '</select>' . "\n";
		
		return $retval;
	}
	
	
	static function display_contacts_table($mode = self::GET_ALL)
	{
		require("inc.dbconnect.php");
		
		$sql = "SELECT * FROM " . Settings::get('TBL_PREFIX') . "tbl_contacts WHERE 1";
		
		if($mode == self::GET_SUPPLIERS)
		{
			$sql.= " AND contact_is_supplier = 1 ";
		}
		
		if($mode == self::GET_CUSTOMERS)
		{
			$sql.= " AND contact_is_customer = 1 ";
		}
		
		$sql .= " ORDER BY contact_name ASC";
		
		$stmt = $dbh -> prepare($sql);
		$stmt -> execute();
		
		$retval = null;
		$retval.= '<center><table border="1">';
		$retval.= '<tr><th>ID<th>Contact name<th>EDIT<th>DEACTIVATE';
		$tally = 0;
		while($row = $stmt -> fetch(PDO::FETCH_ASSOC))
		{
			
			$retval.= '<tr>';
			$retval.= '<td width="40" align="center">' . $row['contact_id'] . '</a></td>';
			$retval.= '<td width="' . COLUMN_WIDTH_CONTACT_NAME . '"><span style="color:#' . $row['contact_color'] . '">' . $row['contact_name'] . '</span></td>';
			$retval.= '<td width="' . COLUMN_WIDTH_EDIT_BUTTON . '" align="center"><a href="contact-edit.php?c_id=' . $row['contact_id'] . '"><img src="img/icon_edit_item.gif" style="border:none"></a></td>';
			$retval.= '<td width="' . COLUMN_WIDTH_DEACTIVATE_BUTTON . '" align="center"><a href="contact-toggle-activity.php?c_id=' . $row['contact_id'] . '">';
			
			if($row['contact_active'] == '1')
			{
				$retval.= '<img src="img/BallGreen.png" ';
			}
			else
			{
				$retval.= '<img src="img/BallRed.png" ';
			}
			$retval.= 'style="border:none"></a></td>';
			$retval.= '</tr>';

		}
		$retval.= '</table></center>';
		
		
		return $retval;
		
	}
}
?>