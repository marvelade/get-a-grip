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
		<table <?= MAIN_TABLE_ATTRIBUTES; ?>>
			<tr>
				<td>
					<table <?= SUB_TABLE_ATTRIBUTES; ?>>
						<tr>
							
							<?php			
								echo insert_left_menu();
							?>
							<td bgcolor="white" valign="top" align="center">
								<!-- CONTENT STARTS HERE -->
								<h2>Are you sure you want to delete this payment?</h2>
								<?php
								
									$p = new MarveladePayment();
									$p -> read_from_db($_GET['p_id']);
									echo $p -> display_are_u_sure_dialog();
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
	$p -> make_invisible($_POST['p_id']);	

	header("Location:view-all.php");
	

}