<?php


require("class.MarveladeStepGraphPoint.php");
class MarveladeStepGraph
{
	
	public function __construct()
	{
		$this -> points = Array();
		$this -> t_mon_min_arr = Array();
		$this -> t_mon_max_arr = Array();
		$this -> start_amount = 0;
	}
	
	
	public function set_start_amount($st_amt)
	{
		$this -> start_amount = floatval($st_amt);
	}
	
	

	public function add_point($x, $y)
	{
		$this -> points[] = new MarveladeStepGraphPoint($x, $y);
	}
	
	public function read_payments_from_db()
	{
		require("inc.dbconnect.php");
		
		
		
		
		$sql_mon_min = "SELECT SUM(payment_amount) AS mon_min FROM " . TBL_PREFIX . "tbl_payments WHERE payment_io_type='-1'";
		$stmt = $dbh -> prepare($sql_mon_min);
		$stmt -> execute();
		$row = $stmt -> fetch(PDO::FETCH_ASSOC);
		$mon_min = -1 * floatval($row['mon_min']);		

		$this -> t_mon_min_arr = Array(0, $mon_min);
		
		$this -> add_point($this -> t_mon_min_arr[0], $this -> t_mon_min_arr[1]);
		
		
		
		$sql_mon_max = "SELECT SUM(payment_amount) AS mon_max FROM " . TBL_PREFIX . "tbl_payments WHERE payment_io_type='1'";
		$stmt = $dbh -> prepare($sql_mon_max);
		$stmt -> execute();
		$row = $stmt -> fetch(PDO::FETCH_ASSOC);
		$mon_max = floatval($row['mon_max']);
				
		$sql_t_max = "SELECT MAX(payment_due_date) AS t_max FROM " . TBL_PREFIX . "tbl_payments WHERE 1";
		$stmt = $dbh -> prepare($sql_t_max);
		$stmt -> execute();
		$row = $stmt -> fetch(PDO::FETCH_ASSOC);
		$t_max = (strtotime($row['t_max']) - time()) / (60*60*24);
		
		$this -> t_mon_max_arr = Array($t_max, $mon_max);
		
		
		
		
		
		$sql = "SELECT payment_amount, payment_due_date, payment_io_type FROM " . TBL_PREFIX . "tbl_payments ORDER BY payment_due_date ASC";
		$stmt = $dbh -> prepare($sql);
		$stmt -> execute();
	
		//echo $sql;
		
		$firstrun = true;
		while($row = $stmt -> fetch(PDO::FETCH_ASSOC))
		{
			
			
			
			
			$signed_amount = intval($row['payment_io_type']) * floatval($row['payment_amount']);
			
			if($firstrun)
			{
				$base_date_t0 = strtotime($row['payment_due_date']);
				$start_amount = $signed_amount + $this -> start_amount;
				$firstrun = false;
			}
			else
			{
				$start_amount+=$signed_amount;
			}
			
			$num_days = round((strtotime($row['payment_due_date']) - $base_date_t0) / (60*60*24));
			
			//echo '(' . $num_days . ',' . $start_amount . ')<br />';
			//echo '<pre>' . print_r($row,true) . '</pre>';	
			
			$point_xy = MarveladeCoordinateConverter::convert(	MarveladeCoordinateConverter::TIME_MONEY_DOMAIN,
																MarveladeCoordinateConverter::X_Y_DOMAIN,
																Array($num_days, $start_amount),
																$this -> t_mon_max_arr,
																$this -> t_mon_min_arr
																); 
			
			$this -> add_point($point_xy[0], $point_xy[1]);
			
		}
		
	}
	
	public function render($file)
	{
	
		$x_bounds = Array("min_bound" => 0, "max_bound" => 500);
		$y_bounds = Array("min_bound" => -100, "max_bound" => 1000);
		/*
$x_bounds = $this -> get_bounds("x");
		$y_bounds = $this -> get_bounds("y");
*/
		
		$x_span = 500;
		$y_span = 600;
		
/*
		$x_span = $x_bounds['max_bound'] - $x_bounds['min_bound'];
		$y_span = $y_bounds['max_bound'] - $y_bounds['min_bound'];
*/
		
		//echo 'x_span : ' . print_r($x_bounds, true) . '<br />';
		//echo 'y_span : ' . print_r($y_bounds, true) . '<br />';
		
		$image_width = $x_span + (2*GRAPH_PADDING_X_PX);
		$image_height = $y_span + (2*GRAPH_PADDING_Y_PX);
		
		$im = ImageCreate($image_width, $image_height);
		
		$background_color = ImageColorAllocate ($im, 234, 234, 234);
		$axis_color = ImageColorAllocate ($im, 10, 10, 10);
		$line_color = ImageColorAllocate ($im, 233, 14, 91);
		
		
		$this -> draw_x_axis($im, $axis_color);
		$this -> draw_y_axis($im, $axis_color);
		$this -> draw_y_markers($im, $axis_color);
		
		//echo '<pre>' . print_r($this -> points,true) . '</pre>';
		
		$firstrun = true;
		$counter = 0;
		

		
		while($counter < (count($this -> points)-1))
		{
	
			$now_point = $this -> points[$counter];
			$next_point = 	$this -> points[($counter+1)];
			
			imageline (
						$im, 
						$now_point -> xco, $now_point -> yco, 
						$next_point -> xco, $now_point -> yco, 
						$line_color
						);
						
			imageline (
						$im, 
						$next_point -> xco, $now_point -> yco, 
						$next_point -> xco, $next_point -> yco, 
						$line_color
						);
			
			$counter++;
		}
		
		
		
		ImagePng ($im, $file);
	}


	private function draw_x_axis($im_rsrc, $clr_rsrc)
	{
		
		$x_axis_start_xy = MarveladeCoordinateConverter::convert(	MarveladeCoordinateConverter::TIME_MONEY_DOMAIN,
																	MarveladeCoordinateConverter::X_Y_DOMAIN,
																	Array(0, 0),
																	$this -> t_mon_max_arr,
																	$this -> t_mon_min_arr
																);
		
		$x_axis_end_xy = MarveladeCoordinateConverter::convert(		MarveladeCoordinateConverter::TIME_MONEY_DOMAIN,
																	MarveladeCoordinateConverter::X_Y_DOMAIN,
																	Array(200, 0),
																	$this -> t_mon_max_arr,
																	$this -> t_mon_min_arr
																);
		
		imageline ($im_rsrc, 
					$x_axis_start_xy[0], $x_axis_start_xy[1], 
					$x_axis_end_xy[0], $x_axis_end_xy[1], 
					$clr_rsrc);
	}
	
	private function draw_y_axis($im_rsrc, $clr_rsrc)
	{

		
	
		$y_axis_start_xy = MarveladeCoordinateConverter::convert(	MarveladeCoordinateConverter::TIME_MONEY_DOMAIN,
																	MarveladeCoordinateConverter::X_Y_DOMAIN,
																	Array(0, MON_AXIS_MIN_VALUE),
																	$this -> t_mon_max_arr,
																	$this -> t_mon_min_arr
																);
		
		$y_axis_end_xy = MarveladeCoordinateConverter::convert(		MarveladeCoordinateConverter::TIME_MONEY_DOMAIN,
																	MarveladeCoordinateConverter::X_Y_DOMAIN,
																	Array(0, MON_AXIS_MAX_VALUE),
																	$this -> t_mon_max_arr,
																	$this -> t_mon_min_arr
																);
		
		imageline ($im_rsrc, 
					$y_axis_start_xy[0], $y_axis_start_xy[1], 
					$y_axis_end_xy[0], $y_axis_end_xy[1], 
					$clr_rsrc);
					
					
		
					
					
	}
	
	private function draw_y_markers($im_rsrc, $clr_rsrc, $marker_interval = 500)
	{
		for($i = 0; $i<= MON_AXIS_MAX_VALUE; $i+= $marker_interval)
		{
			$y_marker_start_xy = MarveladeCoordinateConverter::convert(	MarveladeCoordinateConverter::TIME_MONEY_DOMAIN,
																	MarveladeCoordinateConverter::X_Y_DOMAIN,
																	Array(0, $i),
																	$this -> t_mon_max_arr,
																	$this -> t_mon_min_arr
																); 
																
			$y_marker_end_xy = MarveladeCoordinateConverter::convert(	MarveladeCoordinateConverter::TIME_MONEY_DOMAIN,
																		MarveladeCoordinateConverter::X_Y_DOMAIN,
																		Array(1, $i),
																		$this -> t_mon_max_arr,
																		$this -> t_mon_min_arr
																	); 
			//print_r($y_marker_start_xy);
			//print_r($y_marker_end_xy);
			
			imageline ($im_rsrc, 
						$y_marker_start_xy[0], $y_marker_start_xy[1], 
						$y_marker_end_xy[0], $y_marker_end_xy[1], 
						$clr_rsrc);
						
			imagettftext($im_rsrc, 8, 0, $y_marker_end_xy[0], $y_marker_end_xy[1], $clr_rsrc, "./Verdana.ttf", number_format($i ,0 , ",", "."));
		}
	}


	private function get_bounds($mode)
	{
		$min_value = 100000000;
		$max_value = -100000000;
		
		foreach($this -> points as $sgp_obj)
		{
			switch($mode)
			{
				case "x":
					$prop_to_check = $sgp_obj -> xco;
				break;
				
				case "y":
					$prop_to_check = $sgp_obj -> yco;
				break;
				
				default:
					throw new Exception("No valid mode for " . __METHOD__);
				break;
			}
			
			if($prop_to_check <= $min_value)
			{
				$min_value = $prop_to_check;
			}
			
			if($prop_to_check >= $max_value)
			{
				$max_value = $prop_to_check;
			}
		}
		
		return Array("min_bound" => $min_value, "max_bound" => $max_value);
	}
		
		
		
	 
	
}


?>