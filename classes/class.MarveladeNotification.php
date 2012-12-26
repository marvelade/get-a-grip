<?php

	class MarveladeNotification
	{
		const NOTIFICATION_TYPE_EMAIL = 1;
		const EXCEPTION_MSG_CONSTRUCTOR_DIED =  'constructor died.';
		
		
		public function __construct($notif_type, $parent_payment_id, $seconds_before)
		{
		
			if($this -> set_notification_type($notif_type))
			{
				
				if($this -> set_parent_payment_id($parent_payment_id))
				{
				
					if($this -> set_seconds_before($seconds_before))
					{
						return true;
					}
					else
					{
						throw new Exception( __CLASS__ . '::' . MarveladeNotification::EXCEPTION_MSG_CONSTRUCTOR_DIED);
					}				
				}
				else
				{
					throw new Exception( __CLASS__ . '::' . MarveladeNotification::EXCEPTION_MSG_CONSTRUCTOR_DIED);
				}
			}
			else
			{
				throw new Exception( __CLASS__ . '::' . MarveladeNotification::EXCEPTION_MSG_CONSTRUCTOR_DIED);
			}
			
			
		}
		
		
		private function set_notification_type($nt)
		{
			$legal_notif_types = Array(self::NOTIFICATION_TYPE_EMAIL);
			
			if(in_array($nt, $legal_notif_types))
			{
				$this -> notification_type = $nt;
				return true;
			}
			else
			{
				throw new Exception($notif_type . '" is an illegal notification type');
			}
		}
		
		private function set_parent_payment_id($ppid)
		{
			require("inc.dbconnect.php");
			$sql = "SELECT payment_id FROM `" . Settings::get('TBL_PREFIX') . "tbl_payments` WHERE payment_id=:p_id";
			$stmt = $dbh -> prepare($sql);
			$stmt -> bindParam(":p_id", $ppid, PDO::PARAM_INT);
			
			$row = $stmt -> fetch(PDO::FETCH_ASSOC);
						
			if($row['payment_id'] == $ppid)
			{
				$this -> parent_payment_id = $ppid;
				return true;
			}
			else
			{
				throw new Exception('Payment ID "' . $ppid . '" was not found in the database ');
			}
		}
		
	
		private function set_seconds_before($secbf)
		{
			
			if($secbf > 0)
			{
				$this -> seconds_before = $secbf;
				return true;
			}
			else
			{
				throw new Exception($secbf . '" is an illegal value for the notification timer');
			}
		}
		
		
		
		
		public function write_to_db($write_mode = self::WRITE_MODE_INSERT)
		{
			require("inc.dbconnect.php");
			
			$legal_write_modes = Array(self::WRITE_MODE_INSERT, self::WRITE_MODE_UPDATE);
			if(! in_array($write_mode, $legal_write_modes))
			{
				throw new Exception('"' . $write_mode . '" is an Illegal database write mode');
			}
			elseif(!$this -> all_properties_set_for_db_write)
			{
				throw new Exception('This object is not ready for writing to the database');
			}
			elseif($writemode == self::WRITE_MODE_INSERT)
			{
				$sql = "INSERT INTO `" . Settings::get('TBL_PREFIX') . "tbl_payments` 
						(
							`payment_id`,
							`payment_amount`,
							`payment_invoice_date`,
							`payment_due_date`,
							`payment_io_type`,
							`payment_contact_id`
						)
						VALUES 
						(
							NULL ,
							:amt,
							:inv_d,
							:due_d,
							:paym_type,
							:ct_id
						)";
				$stmt = $dbh -> prepare($sql);
				
				$bindparams = Array("amt" => Array($this -> amount, PDO::PARAM_STR),
									"inv_d" => Array($this -> invoice_date, PDO::PARAM_STR),
									"due_d" => Array($this -> due_date, PDO::PARAM_STR),
									"paym_type" => Array($this -> payment_type, PDO::PARAM_INT),
									"ct_id" => Array($this -> contact_id, PDO::PARAM_INT)
									);
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
		
		
		
	
	}


?>