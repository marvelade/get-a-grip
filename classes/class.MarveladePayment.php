<?php
	
	require_once("config.php");

	class MarveladePayment
	{
		
		const TYPE_INCOMING = 1;
		const TYPE_OUTGOING = -1;
		
		const WRITE_MODE_INSERT = 1;
		const WRITE_MODE_UPDATE = 2;
		
		const CURRENCY_EUR = 0;
		const CURRENCY_USD = 1;
		
		static $currencies = Array('&euro;', '$');
	
		public function __construct()
		{
			$this -> notifications = Array();
			$this -> legal_write_modes = Array(self::WRITE_MODE_INSERT, self::WRITE_MODE_UPDATE);
			return true;
		}
		
		public function display_input_form($write_mode)	
		{
			if(!$this -> write_mode_is_legal($write_mode))
			{
				throw new Exception(__METHOD__ . ': false write mode argument supplied:"' . $write_mode . '"');
				return false;
			}
			else
			{
		
				$def_contact_id = ($this -> get_contact_id() !== false) ? $this -> get_contact_id() : "0";
				$def_payment_id = ($this -> get_payment_id() !== false) ? $this -> get_payment_id() : "0";
				$def_payment_type = ($this -> get_payment_type(true) !== false) ? $this -> get_payment_type(true) : "-1";
				$def_amount = ($this -> get_amount() !== false) ? $this -> get_amount() : "";
				$def_currency_id = ($this -> get_currency()!== false) ? $this -> get_currency() : "0";
				$def_inv_date = ($this -> get_invoice_date() !== false) ? $this -> get_invoice_date() : "";
				$def_description = ($this -> get_payment_description() !== false) ? $this -> get_payment_description() : "";
				$def_due_date = ($this -> get_due_date() !== false) ? $this -> get_due_date() : "";

	
				$retval = '
						<table>
							
							<form action="' . $_SERVER['PHP_SELF'] . '" method="post" onSubmit="return check(this);" name="payment_input_form">
								<input type="hidden" name="p_id" value="' . $def_payment_id . '" />
								<tr>
									<td>Contact:</td>
									<td>' . MarveladeContact :: display_contacts_select($def_contact_id) . '</td>
								</tr>
								<tr>
									<td>Amount (decimal = .):</td>
									<td><input type="text" name="p_amount" value="' . $def_amount . '"></td>
									<td>' . MarveladeCurrency :: display_currencies_select($def_currency_id) . '</td>
								</tr>
								<tr>
									<td>Money in or out?</td>
									<td>
										<select name="p_type">
											<option value="-1"';$retval.= $def_payment_type == -1 ? " SELECTED" : ""; $retval.='>OUTGOING (-)</option>
											<option value="1"';$retval.= $def_payment_type == 1 ? " SELECTED" : ""; $retval.='>INCOMING (+)</option>
										</select></td>
								</tr>
								<tr>
									<td>Description</td>
									<td><input type="text" name="p_desc" value="' . htmlspecialchars($def_description) . '" maxlength="255"></td>
								</tr>
								<tr>
									<td>Invoice Date</td>
									<td><input type="text" name="p_inv_date" value="' . $def_inv_date . '"></td>
									<td>
										<script language="JavaScript">
										new tcal ({
											\'formname\': \'payment_input_form\',
											\'controlname\': \'p_inv_date\'
										});
										</script>
	
									</td>
								</tr>
								<tr>
									<td>Due date</td>
									<td><input type="text" name="p_due_date" value="' . $def_due_date . '"></td>
									<td>
										<script language="JavaScript">
										new tcal ({
											\'formname\': \'payment_input_form\',
											\'controlname\': \'p_due_date\'
										});
										</script>
	
									</td>
								</tr>';
				$retval.= ($write_mode == self::WRITE_MODE_INSERT) ? '						
								<tr>
									<td>Recurrment period (months)</td>
									<td><input type="text" name="p_recurrment_months" value="" maxlength="2"></td>
									<td>(type 0 for a once-off payment)</td>
								</tr>
								
								<tr>
									<td># of repeats</td>
									<td><input type="text" name="p_num_repeats" value="" maxlength="2"></td>
								</tr>
								' : '';
				$retval.= '	<tr>
									<td></td>
									<td><input type="submit" name="go" value="{btn_text}"></td>
								</tr>
							</form>
						</table>	
				
				
	';
		
				switch($write_mode)
				{
				
					case self::WRITE_MODE_INSERT:
						$retval = str_replace("{btn_text}", "Save new payment", $retval);
					break;
					
					case self::WRITE_MODE_UPDATE:
						$retval = str_replace("{btn_text}", "Update payment", $retval);
					break;			
				}
		
				return $retval;
			}		
	
		}
			
		
	
		
	

		
		
		public function add_notification($d)
		{
			$this -> notifications[] = $d;
		}
		
		private function write_mode_is_legal($write_mode)
		{
			return (in_array($write_mode, $this -> legal_write_modes));
		}
		
		private function all_properties_set_for_db_write($write_mode)
		{
		
									
								
			if(!$this -> write_mode_is_legal($write_mode))
			{
				throw new Exception('"' . $write_mode . '" is an Illegal database write mode');
			}
			else
			{
			
				$common_conditions = (
										($this -> get_payment_type() !== false)
											&&
										($this -> get_invoice_date() !== false )
											&&
										($this -> get_due_date() !== false )
											&&
										($this -> get_contact_id() !== false )
											&&
										($this -> get_amount() !== false )
											&&
										($this -> get_currency() !== false)
									);
			
				switch($write_mode)
				{
					case self::WRITE_MODE_INSERT:
					
						return ($common_conditions);
												
					break;
					
					case self::WRITE_MODE_UPDATE:

						return ($common_conditions && $this -> get_payment_id() !== false);

					break;
				}
			}
			
			
			
		}
		
		private function get_lacking_properties($write_mode)
		{
			$lacks = Array();
			
			if($this -> get_payment_type() === false) { $lacks[] = "Payment type not set";}
			if($this -> get_invoice_date() === false) { $lacks[] = "Invoice date not set";}
			if($this -> get_due_date() === false) { $lacks[] = "Due date not set";}
			if($this -> get_contact_id() === false) { $lacks[] = "Contact ID not set";}
			if($this -> get_amount() === false) { $lacks[] = "Amount not set";}
			if($this -> get_currency() === false) { $lacks[] = "Currency not set";}
			
			if($write_mode == self::WRITE_MODE_UPDATE)
			{
				if($this -> get_payment_id() === false) { $lacks[] = "Payment ID not set";}
			}
			return (print_r($lacks,true));
		}
		
		
		public function write_to_db($write_mode = self::WRITE_MODE_INSERT)
		{
			
			require("inc.dbconnect.php");
			
			
			if(!$this -> write_mode_is_legal($write_mode))
			{
				throw new Exception('"' . $write_mode . '" is an Illegal database write mode');
			}
			if(!$this -> all_properties_set_for_db_write($write_mode) )
			{
				throw new Exception('This object is not ready for writing to the database:' . $this -> get_lacking_properties($write_mode));
			}
			else
			{
			
				$bindparams = Array("amt" => Array($this -> amount, PDO::PARAM_STR),
									"descr" => Array(htmlspecialchars_decode($this -> payment_description), PDO::PARAM_STR),
									"inv_d" => Array($this -> invoice_date, PDO::PARAM_STR),
									"due_d" => Array($this -> due_date, PDO::PARAM_STR),
									"paym_type" => Array(intval($this -> get_payment_type()), PDO::PARAM_STR),
									"ct_id" => Array($this -> contact_id, PDO::PARAM_INT)
									);
			
			
				switch($write_mode)
				{
					case self::WRITE_MODE_INSERT:
					
						$sql = "INSERT INTO `" . TBL_PREFIX . "tbl_payments`  SET	
							`payment_amount`=:amt,
							`payment_description`=:descr,							
							`payment_invoice_date`=:inv_d,
							`payment_due_date`=:due_d,
							`payment_io_type`=:paym_type,
							`payment_contact_id`=:ct_id
						";
						
						
						
					break;
					
					case self::WRITE_MODE_UPDATE:
					
					$sql = "UPDATE " . TBL_PREFIX . "tbl_payments
							SET
							`payment_amount`=:amt,
							`payment_description`=:descr,
							`payment_invoice_date`=:inv_d,
							`payment_due_date`=:due_d,
							`payment_io_type`=:paym_type,
							`payment_contact_id`=:ct_id
							WHERE payment_id=:p_id
							";
					$bindparams['p_id'] = Array($this -> payment_id, PDO::PARAM_INT);
				
					break;
				
				}
				
				$stmt = $dbh -> prepare($sql);
				
				$real_sql = $sql;
				
				foreach($bindparams as $param => $val_arr)
				{
					$stmt -> bindValue(":".$param, $val_arr[0], $val_arr[1]);
					$real_sql = str_replace(":".$param, $val_arr[0], $real_sql);					
				}
				

				
				$stmt -> execute();
				
				if(count($this -> notifications) > 0)
				{
					foreach($this -> notifications as $notif)
					{
						//
					}
				}
				
				
				
				
			}
			
			
		}
		
		public function read_from_db($p_id)
		{
			require("inc.dbconnect.php");
			
			$sql = "SELECT * 
					FROM " . TBL_PREFIX . "tbl_payments
					WHERE payment_id=:pid LIMIT 1";
			$stmt = $dbh -> prepare($sql);
			$stmt -> bindValue(":pid", intval($p_id), PDO::PARAM_INT);
			$stmt -> execute();
			
			$row = $stmt -> fetch(PDO::FETCH_ASSOC);
			
			
			
			switch($row['payment_io_type'])
			{
				case "-1":
					$this -> set_payment_type(MarveladePayment::TYPE_OUTGOING);
				break;
				
				case "1":
					$this -> set_payment_type(MarveladePayment::TYPE_INCOMING);
				break;
					
			}
			 
			$this -> set_payment_id($p_id);
			$this -> set_currency($row['payment_currency_id']);
			$this -> set_amount($row['payment_amount']);
			$this -> set_payment_description($row['payment_description']);
			$this -> set_invoice_date($row['payment_invoice_date']);
			$this -> set_due_date($row['payment_due_date']);
			$this -> set_contact_id($row['payment_contact_id']);
			

			
		}
		
		public function delete_from_db($p_id)
		{
			require("inc.dbconnect.php");
			
			$sql = "DELETE FROM " . TBL_PREFIX . "tbl_payments WHERE payment_id=:pid LIMIT 1";
			$stmt = $dbh -> prepare($sql);
			$stmt -> bindValue(":pid", intval($p_id), PDO::PARAM_INT);
			
			return($stmt -> execute());
		}
		
		public function make_invisible($p_id)
		{
			require("inc.dbconnect.php");
			
			$sql = "UPDATE 
						" . TBL_PREFIX . "tbl_payments
					SET
						 payment_visible ='0'
					WHERE
						payment_id=:pid
					LIMIT 1";
			$stmt = $dbh -> prepare($sql);
			$stmt -> bindValue(":pid", intval($p_id), PDO::PARAM_INT);
			
			return($stmt -> execute());
		}
		
		public function display_are_u_sure_dialog()
		{
			$retval = "";
			
			$c_ob = new MarveladeContact($this -> get_contact_id());
			
			
			$retval.= '
				<table border="1">
					<form action="' . $_SERVER['PHP_SELF'] . '" method="post">
						<input type="hidden" name="p_id" value="' . $_GET['p_id'] .'">
						<tr>
							<td>Contact:</td>
							<td>' . $c_ob -> get_contact_name() . '</td>
						</tr>
						<tr>
							<td>Amount:</td>
							<td>' . $this -> get_amount() . '</td>
						</tr>
						<tr>
							<td>Description:</td>
							<td>' . $this -> get_payment_description() . '</td>
						</tr>
						<tr>
							<td>Invoice Date:</td>
							<td>' . $this -> get_invoice_date() . '</td>
						</tr>
						<tr>
							<td>Due date:</td>
							<td>' . $this -> get_due_date() . '</td>
						</tr>
						<tr>
						<td></td>
							<td><input type="submit" name="go" value="Delete">
							<input type="button" onClick="javascript:history.go(-1);" value="Cancel"></td>
						</tr>
					</form>
				</table>';
		
			return $retval;
		
		}
		
		
		public function set_payment_type($pt)
		{
		
			$legal_types = Array(self::TYPE_INCOMING, self::TYPE_OUTGOING);
			if(in_array($pt, $legal_types))
			{
				$this -> payment_type = $pt;
				return true;
			}
			else
			{
				throw new Exception('"' . $type . '" is an Illegal type for this constructor');
			}
		}
		
		public function get_payment_type($return_as_integer=false)
		{
			if(isset($this -> payment_type))
			{
				if($return_as_integer === true)
				{
					switch($this -> payment_type)
					{
						case MarveladePayment::TYPE_OUTGOING:
							return -1;
						break;
						
						
						case MarveladePayment::TYPE_INCOMING:
							return 1;
						break;			
					}
				}
				else
				{
					return $this -> payment_type;
				}
			}
			else
			{
				return(false);
			}
		}

		
		
		public function set_currency($currency)
		{
			$this -> currency = $currency;
		}
		
		public function get_currency ()
		{
			if(isset($this -> currency))
			{
				return $this -> currency;
			}
			else
			{
				return(false);
			}
		}

		
		
		public function set_invoice_date($invoice_date)
		{
			$this -> invoice_date = $invoice_date;
		}
		
		public function get_invoice_date ()
		{
			if(isset($this -> invoice_date))
			{
				return $this -> invoice_date;
			}
			else
			{
				return(false);
			}
		}



		
		public function set_due_date($due_date)
		{
			$this -> due_date = $due_date;
		}
		
		public function get_due_date ()
		{
			if(isset($this -> due_date))
			{
				return $this -> due_date;
			}
			else
			{
				return(false);
			}
		}
	
	
		public function set_payment_description($payment_description)
		{
			$this -> payment_description = $payment_description;
		}
		
		public function get_payment_description()
		{
			if(isset($this -> payment_description))
			{
				return $this -> payment_description;
			}
			else
			{
				return(false);
			}
		}
	
		
		
		
		public function set_contact_id($c)
		{
			$this -> contact_id = $c;
		}
		
		public function get_contact_id()
		{
			if(isset($this -> contact_id))
			{
				return $this -> contact_id;
			}
			else
			{
				return(false);
			}
			
		}
		
		
		
		public function set_payment_id($payment_id)
		{
			$this -> payment_id = $payment_id;
		}
		
		public function get_payment_id()
		{
			if(isset($this -> payment_id))
			{
				return $this -> payment_id;
			}
			else
			{
				return(false);
			}
		}


		

		
		
		
		public function set_amount ($amount)
		{
			$this -> amount = $amount;
		}
		
		public function get_amount ()
		{
			if(isset($this -> amount))
			{
				return $this -> amount;
			}
			else
			{
				return(false);
			}
		}
		
		
		
		
		
	
	

		
	
	
	}


?>
