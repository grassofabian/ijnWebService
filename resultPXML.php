<?php
function array2XMLp($obj, $array)
{
	foreach ($array as $key => $value)
	{
		if(is_numeric($key))
			$key = 'item' . $key;

		if (is_array($value))
		{
			$node = $obj->addChild($key);
			array2XML($node, $value);
		}
		else
		{
			$obj->addChild($key, htmlspecialchars($value));
		}
	}
}


function objectToArrayp($d) {
	if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function
		$d = get_object_vars($d);
	}

	if (is_array($d)) {
		/*
		 * Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return array_map(__FUNCTION__, $d);
	}
	else {
		// Return array
		return $d;
	}
}


?>