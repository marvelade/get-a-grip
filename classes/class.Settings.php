<?php 

	class Settings
	{
		private static $settings_array = array();
		
		public static function set($_key, $_val)
		{
			self :: $settings_array[$_key] = $_val;
		}
		
		public static function get($_key)
		{
			if(array_key_exists(strval($_key), self :: $settings_array ))
			{
				return self :: $settings_array[$_key];
			}
			else
			{
				throw new SettingsException('No key "' .$_key  . '".');
			}
			
		}
	}

?>