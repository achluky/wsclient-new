<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
  
if (! function_exists('ping')) {
	
	function ping($host,$port)
	{
		return @fsockopen($host, $port, $iErrno, $sErrStr, 1);
	}
}

if (! function_exists('debug')) {
	
	function debug($var)
	{
		echo "<pre/>";
		var_dump($var);
		die();
	}
}

if (! function_exists('prodi_id')) {
	
	function prodi_id($var)
	{
		switch ($var) {
			case 'EL':
				$id = '4c07562a-2a8e-41c3-8df4-32cbd76996ae';
				break;
			case 'FI':
				$id = '59a4eb4d-ffc2-4ec5-a8af-a4a09469a62d';
				break;
			case 'GD':
				$id = 'bd24df7f-0d50-45f9-a46f-cf3304dab44b';
				break;
			case 'GF':
				$id = '17c59392-c895-498f-9587-d9fdbc0e4621';
				break;
			case 'IF':
				$id = '18b2c2d4-4fb8-4fab-96c8-634e40d5de8d';
				break;
			case 'PWK':
				$id = '1d1058d2-e013-4476-923d-851b0c45681f';
				break;
			case 'SI':
				$id = '85a93130-d827-4a6d-b267-877d7acdfbfc';
				break;
			default:
				$id='';
				break;
		}
		return $id;
	}
}