<?php

	class MarveladeCoordinateConverter
	{
		const X_Y_DOMAIN = 0;
		const TIME_MONEY_DOMAIN = 1;
		
		public static function convert($source_domain, $target_domain, $uv_arr, $uv_max_arr, $uv_min_arr)
		{
			if($source_domain == self::TIME_MONEY_DOMAIN && $target_domain == self::X_Y_DOMAIN)
			{
				$t = $uv_arr[0];
				$mon = $uv_arr[1];
				$mon_max = $uv_max_arr[1];
				
				$x = (GRAPH_TXY * $t) + GRAPH_PADDING_X_PX;
				$y = (GRAPH_MXY * ($mon_max - $mon)) + GRAPH_PADDING_Y_PX + 300; // pas deze laatste factor aan om de graph verticaal te verschuiven
				
				return Array($x, $y);
				
			}
			else
			{
				throw new Exception("Cant do anything else than (t,mon) -> (x,y) at the moment");
			}
		}
	
	
	}

?>