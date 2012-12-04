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
								
									$ct = new MarveladeContact($_GET['c_id']);
									echo $ct -> display_input_form("UPDATE");
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
	$ct -> update_properties($_POST);
	$ct = null;
	
	header("Location:contacts-manage.php");
	

}