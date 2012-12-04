<?php
ob_start();
header('Content-Type: text/html; charset=utf-8');
require("config.php");

if(!isset($_POST['go']))
{
?>
<html>
<!-- This message MUST remain (cannot be seen on website). Web template thanks to OZGRESSION Web Design (www.ozgression.vze.com) -->

	<head>
		<?php echo insert_title_tag(__FILE__); ?>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="css/basic.css">
		<link rel="stylesheet" href="css/calendar.css">

		<script language="JavaScript" src="calendar_db.js"></script>
		<script language="JavaScript" src="misc_functions.js"></script>

		<script language="JavaScript" type="text/javascript">
		
			function check(frm)
			{
				
				if(frm.contact.selectedIndex == 0)
				{
					alert("Who??");
					return false;
				}
				else if(frm.p_amount.value == '')
				{
					alert("Show me the monneh");
					return false;
				}
				else if(parseFloat(frm.p_amount.value) < 0)
				{
					alert("No neggies plz");
					return false;
				}
				else if(!is_empty(frm.p_amount.value) && !is_valid_number(frm.p_amount.value))
				{
					alert("That aint no monneh")
					return false;
				}
				else if(frm.p_inv_date.value == '')
				{
					alert("When u got it");
					return false;
				}
				else if(frm.p_due_date.value == '')
				{
					alert("When they need it");
					return false;
				}
				else if(frm.p_recurrment_months.value == '')
				{
					alert("At least enter a zero, u lazy dork!");
					return false;
				}
				else if(frm.p_recurrment_months.value == 0 && frm.p_num_repeats.value != '')
				{
					alert("Please don't enter a repeat count when it's not a recurring payment");
					return false;
				}
				else
				{
					alert("All's well in La-La-Land...");
					return true;
				}
			}
		
		</script> 
		
		
		
	</head>
	
	<body>
	
	<?php		
		echo insert_top_table()
	?>
	
		<center>
		<table <?= MAIN_TABLE_ATTRIBUTES; ?>>
			<tr>
				<td>
					<table <?= SUB_TABLE_ATTRIBUTES; ?>>
						<tr>
							
							<?php			
								echo insert_left_menu();
							?>
							<td bgcolor="white" valign="top">
								<!-- CONTENT STARTS HERE -->
				
								<?php
								
									$p = new MarveladePayment();
									echo $p -> display_input_form(MarveladePayment::WRITE_MODE_INSERT);
									$p = null;

								?>
				
								<!-- CONTENT ENDS HERE -->
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		</center>
		
		<?php
			echo insert_copyright_notice();
		?>
		<br>
	
	</body>
</html>
<?php

}
else
{
	//print_r($_POST);

	$p = new MarveladePayment();
	$p -> set_amount($_POST['p_amount']);
	$p -> set_currency($_POST['currency']);
	$p -> set_payment_description($_POST['p_desc']);
	$p -> set_contact_id($_POST['contact']);
		
	switch($_POST['p_type'])
	{
		case "-1":
			$p -> set_payment_type(MarveladePayment::TYPE_OUTGOING);
		break;
		
		
		case "1":
			$p -> set_payment_type(MarveladePayment::TYPE_INCOMING);
		break;
		
		default:
			throw new Exception('Fawk');
		break;
			
	}
	
	$num_repeats = (
						(intval($_POST['p_num_repeats']) != '') 
							&& 
						(intval($_POST['p_num_repeats']) != 0)
					)
						?
					intval($_POST['p_num_repeats']) : 1; 

	
	
	
	echo '<hr>nog effe niks<br />';

	for($i=1; $i<= $num_repeats; $i++)
	{
		$inv_date_utc = strtotime($_POST['p_inv_date']);
		$due_date_utc = strtotime($_POST['p_due_date']);
		$multiplier = $i-1;
		$num_months_to_add = $multiplier*intval($_POST['p_recurrment_months']);
		$month_s = $num_months_to_add != 1 ? 'months' : 'month';
		
		$num_seconds_to_add = 0;
		if($num_months_to_add > 0)
		{
			$inv_date_utc = strtotime('+' . $num_months_to_add . ' ' . $month_s, $inv_date_utc);
			$due_date_utc = strtotime('+' . $num_months_to_add . ' ' . $month_s, $due_date_utc);
		}
		
		$inv_date_iso = date('Y\-m\-d', $inv_date_utc);
		$due_date_iso = date('Y\-m\-d', $due_date_utc);
		
		//echo '$inv_date_iso = ' . $inv_date_iso . '(' . date('r', $inv_date_utc) . ')<br />';
		//echo '$due_date_iso = ' . $due_date_iso . '(' . date('r', $due_date_utc) . ')<br />';
		
		
		//echo '<hr>';
		
		//$inv_date_utc+= $num_seconds_to_add;
		$p -> set_invoice_date($inv_date_iso);
		$p -> set_due_date($due_date_iso);
		
		//echo '<pre>' . print_r($p,true) . '</pre>';
		$p -> write_to_db(MarveladePayment::WRITE_MODE_INSERT);
	
	}
	
	header("Location:view-all.php");
	

}