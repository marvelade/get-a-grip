<?php

	class MarveladePaymentTable
	{
		const TYPE_INCOMING = 1;
		const TYPE_ALL = 0;
		const TYPE_OUTGOING = -1;
		
		public function __construct($type)
		{
			if($this -> set_payment_type($type))
			{
				$this -> set_start_amount(0);
				$this -> render_payment_table();				
				return true;
			}
			else
			{
				throw new Exception(__CLASS__ . ' constructor died');
			}
		}
		
		public function set_start_amount($st_amt)
		{
			$this -> start_amount = floatval($st_amt);
		}

		public function get_start_amount()
		{
			return $this -> start_amount;
		}
			
		public function set_payment_type($pt)
		{
		
			$legal_types = Array(self::TYPE_INCOMING, self::TYPE_OUTGOING, self::TYPE_ALL);
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
	
	
		public function get_payment_type()
		{
			return $this -> payment_type;
		}
	
	
		private function render_payment_table()
		{
			
			require("inc.dbconnect.php");
			
			$sql = "SELECT 
						p.payment_id,
						p.payment_amount,
						p.payment_io_type,
						p.payment_currency_id, 
						p.payment_invoice_date,
						p.payment_description, 
						p.payment_due_date,
						
						c.contact_name,
						c.contact_color
					FROM 
						" . TBL_PREFIX . "tbl_payments AS p 
					JOIN 
						" . TBL_PREFIX . "tbl_contacts AS c 
					ON 
						p.payment_contact_id = c.contact_id
					WHERE  p.payment_visible='1'";
			
			if($this -> payment_type != self::TYPE_ALL)
			{
				$sql.= "AND payment_io_type ='" . $this -> payment_type . "' ";
			}
			
			$sql.= "ORDER BY p.payment_due_date ASC, c.contact_name ASC";

			
			$stmt = $dbh -> prepare($sql);
			$stmt -> execute();




			echo '<center><table border="1">';
			echo '<tr><th>ID<th>Contact name<th>Amount<th>Cumul.<th>Invoice date<th>Due date';
			$this -> start_amount = (isset($_REQUEST['start_amount'])) ? floatval($_REQUEST['start_amount']) : 0;
			$tally = $this -> start_amount;
			$rowcount = 0;
			
			
			
			while($row = $stmt -> fetch(PDO::FETCH_ASSOC))
			{
				$sign_unit = intval($row['payment_io_type']);
				$old_tally = $tally;
				$tally += floatval($sign_unit*$row['payment_amount']);
				$tally_sign_unit = ($tally > 0) ? 1 : -1;
				
				
				
				$monthnum = date("m", strtotime($row['payment_due_date']));
				$bgcolor = ($monthnum%2 == 0) ? '#ffffff' : '#eeeeee';
				
				echo '<tr bgcolor="' . $bgcolor . '">';
				echo '<td width="40" align="center"><a href="#" title="' . htmlspecialchars($row['payment_description']) . '">' . $row['payment_id'] . '</a></td>';
				echo '<td width="' . COLUMN_WIDTH_CONTACT_NAME . '"><span style="color:#' . $row['contact_color'] . '">' . $row['contact_name'] . '</span></td>';
				echo '<td width="' . COLUMN_WIDTH_MONEY_AMOUNT . '" align="right">' . $this -> render_amount($row['payment_currency_id'], $row['payment_amount'], $sign_unit) . '</td>';
				echo '<td width="' . COLUMN_WIDTH_MONEY_AMOUNT . '" align="right">' . $this -> render_amount(0, $tally, $tally_sign_unit) . '</td>';
				echo '<td width="' . COLUMN_WIDTH_INVOICE_DATE . '" align="center">' . $row['payment_invoice_date'] . '</td>';
				echo '<td width="' . COLUMN_WIDTH_DUE_DATE . '" align="center">' . $this -> render_due_date($row['payment_due_date']) . '</td>';
				echo '<td width="' . COLUMN_WIDTH_EDIT_BUTTON . '" align="center"><a href="payment-edit.php?p_id=' . $row['payment_id'] . '"><img src="img/icon_edit_item.gif" style="border:none"></a></td>';
				//echo '<td width="' . COLUMN_WIDTH_COPY_BUTTON . '" align="center"><a href="payment-copy.php?p_id=' . $row['payment_id'] . '"><img src="img/icon_copy_item.png" style="border:none"></a></td>';
				echo '<td width="' . COLUMN_WIDTH_DELETE_BUTTON . '" align="center"><a href="payment-delete.php?p_id=' . $row['payment_id'] . '"><img src="img/icon_delete_item.gif" style="border:none"></a></td>';
				
				echo '</tr>';
				

				$rowcount++;
			}
			
			if($this -> get_payment_type() == self::TYPE_ALL)
			{
				
			
				echo '
					<form action="' . $_SERVER['PHP_SELF'] .  '" method="GET">
						<tr>
							<td width="40">&nbsp;</td>
							<td width="' . COLUMN_WIDTH_CONTACT_NAME  .'" style="font-size:1.5em">In Bank account:</td>
							<td width="' . COLUMN_WIDTH_MONEY_AMOUNT . '" align="right" style="font-size:1.5em">
								<input type="text" name="start_amount" value="' . $this -> get_start_amount() . '" size="5">&nbsp;<input type="submit" name="go" value="&darr;">
							</td>
							<td width="' . COLUMN_WIDTH_INVOICE_DATE . '" align="center">&nbsp;</td>
							<td width="' . COLUMN_WIDTH_DUE_DATE . '" align="center">&nbsp;</td>
						</tr>
					</form>';
			}				
			echo '<tr>';
			echo '<td width="40">&nbsp;</td>';
			echo '<td width="' . COLUMN_WIDTH_CONTACT_NAME . '" style="font-size:1.5em">TOTAL</td>';
			echo '<td width="' . COLUMN_WIDTH_MONEY_AMOUNT . '" align="right" style="font-size:1.5em">' . $this -> render_amount(0, $tally) . '</td>';
			echo '<td width="' . COLUMN_WIDTH_INVOICE_DATE . '" align="center">&nbsp;</td>';
			echo '<td width="' . COLUMN_WIDTH_DUE_DATE . '" align="center">&nbsp;</td>';
			echo '</tr>';


			echo '</table></center>';
		
		
		}
		
		
		private function render_due_date($d)
		{
			$d_utc = strtotime($d);
			$now_utc = time();
			
			
			
			$difference = $d_utc - $now_utc;
			
			if($difference <= (60*60*24*NUM_DAYS_ALMOST_DUE) && $difference > 0)
			{
				$clr = COLOR_PAYMENT_ALMOST_DUE;
				$spanstyle = "color: " . $clr . "";
			}
			elseif($difference <= 0)
			{
				$clr = COLOR_PAYMENT_OVER_DUE;
				$spanstyle = "color: " . $clr . "; font-size:1.1em";
			}
			else
			{
				$clr = COLOR_PAYMENT_DEFAULT;
				$spanstyle = "color: " . $clr . "";
			}
			
			return '<span style="' . $spanstyle . '">' . $d . ' (' . ceil($difference/(60*60*24)) . 'd.)</span>';
		}
		
		
		private function render_amount($curr, $amt, $posneg_unit = 1)
		{
			//print_r(MarveladePayment::$currencies);
			
			if($posneg_unit == -1)
			{
				$clr = "red";
				$spanstyle = "color: " . $clr . "; font-weight: bold;";
				
			}
			else
			{
				$clr = "green";
				$spanstyle = "color: " . $clr . "; font-style: italic;";
			}
			
			
			
			return '<span style="' .$spanstyle  . '">' . MarveladePayment::$currencies[$curr]. "&nbsp;" . number_format( ($posneg_unit*abs($amt)), 2, ",", ".") . "</span>";
		
		}
	
	
	}


?>