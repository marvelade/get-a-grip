<?php
ob_start();
header('Content-Type: text/html; charset=utf-8');
require("config.php");
?>
<html>
<!-- This message MUST remain (cannot be seen on website). Web template thanks to OZGRESSION Web Design (www.ozgression.vze.com) -->

	<head>
		<?php echo insert_title_tag(__FILE__); ?>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="css/basic.css">
	</head>
	
	<body>
	
	<?php
		
		
		
		try
		{
			$p = new MarveladePayment(MarveladePayment::TYPE_INCOMING);
			$p -> set_invoice_date("2009-10-24");
			$p -> set_due_date("2009-11-13");
			$p -> set_contact_id(1);
			$p -> set_amount(150.00);
			$p -> set_currency(MarveladePayment::CURRENCY_EUR);
			
			
			
		}
		catch(Exception $e)
		{
			echo "Caught Exception ('{$e->getMessage()}')\n{$e}\n";
		}
		
		echo insert_top_table()
	?>
	
		<center>
		<table <?= Settings::get('MAIN_TABLE_ATTRIBUTES'); ?>>
			<tr>
				<td>
					<table <?= SUB_TABLE_ATTRIBUTES; ?>>
						<tr>
							
							<?php			
								echo insert_left_menu();
							?>
							<td bgcolor="white" valign="top">
								<!-- CONTENT STARTS HERE -->
								
								
								
								
				
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