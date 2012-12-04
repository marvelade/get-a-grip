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
								
									$pt = new MarveladePaymentTable(MarveladePaymentTable::TYPE_INCOMING);

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