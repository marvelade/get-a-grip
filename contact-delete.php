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
							<td bgcolor="white" valign="top" align="center">
								<!-- CONTENT STARTS HERE -->
								<h2>Are you sure you want to delete this contact?</h2>
								<?php
								
									$ct = new MarveladeContact($_GET['c_id']);
									echo $ct -> display_are_u_sure_dialog();
									$ct = null;

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

	$ct = new MarveladeContact($_POST['c_id']);
	$ct -> make_invisible();
	
	header("Location:contacts-manage.php");
	

}