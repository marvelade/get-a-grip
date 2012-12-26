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

		<script language="JavaScript" type="text/javascript">
		
			function check(frm)
			{
				
				if(frm.c_name.value == "")
				{
					alert("Name cannot be left blank");
					return false;
				}
				else if(!frm.c_is_cst.checked && !frm.c_is_supp.checked)
				{
					alert("supplier and customer checkboxes cannot both be left blank");
					return false;
				}
				
				else
				{
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
		<table <?= Settings::get('MAIN_TABLE_ATTRIBUTES'); ?>>
			<tr>
				<td>
					<table <?= Settings::get('SUB_TABLE_ATTRIBUTES'); ?>>
						<tr>
							
							<?php			
								echo insert_left_menu();
							?>
							<td bgcolor="white" valign="top">
								<!-- CONTENT STARTS HERE -->
				
								<?php
								
									$p = new MarveladePayment();
									$p -> read_from_db($_GET['p_id']);
									echo $p -> display_input_form(MarveladePayment::WRITE_MODE_UPDATE);
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
	print_r($_POST);

	$p = new MarveladePayment();
	$p -> set_payment_id($_POST['p_id']);
	$p -> set_amount($_POST['p_amount']);
	$p -> set_currency($_POST['currency']);
	$p -> set_payment_description($_POST['p_desc']);
	$p -> set_invoice_date($_POST['p_inv_date']);
	$p -> set_due_date($_POST['p_due_date']);
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
	
	$p -> write_to_db(MarveladePayment::WRITE_MODE_UPDATE);

	header("Location:view-all.php");
	

}