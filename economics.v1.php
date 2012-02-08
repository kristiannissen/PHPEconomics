<?php
/**
 *
 */

function get_soap_connection() {
	static $client;
	
	if (!isset($client)) {
		$settings = parse_ini_file(realpath(dirname(__FILE__) .'/economics.ini'));
		
		try {
			$client = new SoapClient($settings['wsdl_endpoint'], array("trace" => 1, "exceptions" => 1));

			$client->Connect(array(
	      'agreementNumber' => $settings['agreement_number'],
	      'userName' => $settings['user_name'],
	      'password' => $settings['password']
	    ));
		} catch (SoapFault $fault) {
			trigger_error(sprintf("Soap fault %s - %s", $fault->faultcode, $fault->faultstring), E_USER_ERROR);
		}
	}
	
	return $client;
}

/**
 * Example:
 * if ($debtor == debtor_find_by_name('CompuGlobalHyperMegaNet')) {
 *	# Do something with debtor
 * } else {
 *	# No debtor found by that name
 * }
 */
function debtor_find_by_name($name = null) {
	global $soap_client;
	
	if (!is_string($name)) {
		trigger_error("Debtor name must be a string", E_USER_ERROR);
	}
	
	$result = $soap_client->Debtor_FindByName(array(
		'name' => $name
	));
	
	if (is_object($result) && property_exists($result, 'Debtor_FindByNameResult')) {
		if (property_exists($result->Debtor_FindByNameResult, 'DebtorHandle')) {
			$handle = $result->Debtor_FindByNameResult->DebtorHandle;
			
			if (function_exists('debtor_find_by_number')) {
				return debtor_find_by_number(intval($handle->Number));
			}
			
			return $handle;
		}
	}
	
	return null;
}

function debtor_geocode_address($debtor = null) {
	global $soap_client;
	
	if (!function_exists('json_decode')) {
		trigger_error("This feature required json_decode() see http://dk2.php.net/manual/en/function.json-decode.php", E_USER_ERROR);
	}
	// http://maps.googleapis.com/maps/api/geocode/json?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&sensor=true_or_false
	
	$address = array(
		urlencode($debtor->Address),
		urlencode($debtor->PostalCode),
		urlencode($debtor->City),
		urlencode($debtor->Country)
	);
	
	if (function_exists('http_get')) {
		# Use http_get http://dk2.php.net/manual/en/function.http-get.php
	} else {
		$google = file_get_contents(sprintf("http://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false", join(',', $address)));
	}
	
	$resp = json_decode($google);
	
	return $resp->results;
}

function debtor_uses_twitter($debtor = null) {
	global $soap_client;
	
	if (!function_exists('json_decode')) {
		trigger_error("This feature required json_decode() see http://dk2.php.net/manual/en/function.json-decode.php", E_USER_ERROR);
	}
	
	if (function_exists('http_get')) {
		# Use http_get http://dk2.php.net/manual/en/function.http-get.php
	} else {
		$twitter = file_get_contents("http://search.twitter.com/search.json?q=%40". urlencode($debtor->Name));
	}
	
	$resp = json_decode($twitter);
	
	return $resp->results;
}

/**
 * Example:
 * if ($debtor == debtor_find_by_number(42)) {
 *	# Do something with debtor
 * } else {
 *	# No debtor found
 * }
 */
function debtor_find_by_number($number = null) {
	global $soap_client;
	
	if (!is_int($number)) {
		trigger_error("Debtor number must be an integer", E_USER_ERROR);
	}
	
	$result = $soap_client->Debtor_FindByNumber(array(
		'number' => $number
	));

	if (is_object($result) && property_exists($result, 'Debtor_FindByNumberResult')) {
		if (property_exists($result->Debtor_FindByNumberResult, 'Number')) {
			$debtor = $soap_client->Debtor_GetData(array(
				'entityHandle' => $result->Debtor_FindByNumberResult
			));
			
			if (is_object($debtor) && property_exists($debtor, 'Debtor_GetDataResult')) {
				return $debtor->Debtor_GetDataResult;
			}
		}
	}
	
	return null;
}
/**
 * Example:
 * if ($debtors == debtor_get_all()) {
 * 	# Do something with debtors
 * } else {
 * 	# No debtors found
 * }
 */
function debtor_get_all() {
	global $soap_client;
	
	$debtor_result = $soap_client->Debtor_GetAll();

	if (is_object($debtor_result) && property_exists($debtor_result, 'Debtor_GetAllResult')) {
		$debtor_handles = $debtor_result->Debtor_GetAllResult->DebtorHandle;

		if (is_array($debtor_handles)) {
			$debtor_data = $soap_client->Debtor_GetDataArray(array('entityHandles' => $debtor_handles));

			if (is_object($debtor_data) && property_exists($debtor_data, 'Debtor_GetDataArrayResult')) {
				return $debtor_data->Debtor_GetDataArrayResult->DebtorData;
			}
		}
	}
	
	return null;
};

/**
 * Example:
 * if ($invoices == debtor_get_invoices(42)) {
 * 	# Do something with invoices
 * } else {
 * 	# No invoices for debtor found
 * }
 */
function debtor_get_invoices($number = null) {
	global $soap_client;

	// Make sure we have a valid number
	if (is_null($number)) {
		trigger_error("debtor_get_invoices('42') takes a debtor number as it's parameter", E_USER_ERROR);
	}
	
	$debtor = $soap_client->Debtor_FindByNumber(array(
    'number' => $number
  ));

	if (is_object($debtor) && property_exists($debtor, 'Debtor_FindByNumberResult')) {
		$result = $soap_client->Debtor_GetInvoices(array(
			'debtorHandle' => $debtor->Debtor_FindByNumberResult
		));

		if (is_object($result) && property_exists($result, 'Debtor_GetInvoicesResult')) {
			$invoice_handles = $result->Debtor_GetInvoicesResult->InvoiceHandle;

			$result = $soap_client->Invoice_GetDataArray(array('entityHandles' => $invoice_handles));

			if (is_object($result) && property_exists($result, 'Invoice_GetDataArrayResult')) {
				return $result->Invoice_GetDataArrayResult->InvoiceData;
			}
		}
	}
	
	return null;
}
/**
 * Example:
 * CurrentInvoice_GetDataArray
 */
function debtor_get_current_invoices($number = null) {
	global $soap_client;
	
	// Make sure we have a valid number
	if (is_null($number)) {
		trigger_error("debtor_get_current_invoices('42') takes a debtor number as it's parameter", E_USER_ERROR);
	}
	
	$result = $soap_client->Debtor_GetCurrentInvoices(array(
		'debtorHandle' => (object) array(
			'Number' => (string) $number
		)
	));

	if (is_object($result) && property_exists($result, 'Debtor_GetCurrentInvoicesResult')) {
		if (property_exists($result->Debtor_GetCurrentInvoicesResult, 'CurrentInvoiceHandle')) {
			$handles = $result->Debtor_GetCurrentInvoicesResult->CurrentInvoiceHandle;
			
			$result = $soap_client->CurrentInvoice_GetDataArray(array(
				'entityHandles' => $handles
			));

			if (property_exists($result, 'CurrentInvoice_GetDataArrayResult')) {
				return $result->CurrentInvoice_GetDataArrayResult->CurrentInvoiceData;
			}
		}
	}
	
	return null;
}
/**
 * Example:
 * if ($products == product_get_all()) {
 * 	foreach ($products as $product) {
 *		# Each product is an object
 *	}
 * else {
 * }
 */
function product_get_all() {
	global $soap_client;
	
	$result = $soap_client->Product_GetAll();
	
	if (is_object($result) && property_exists($result, 'Product_GetAllResult')) {
		$handles = $soap_client->Product_GetDataArray(array(
			'entityHandles' => $result->Product_GetAllResult->ProductHandle
		));
		
		if (is_object($handles) && property_exists($handles, 'Product_GetDataArrayResult')) {
			return $handles->Product_GetDataArrayResult->ProductData;
		}
	}
	
	return null;
}

$soap_client = get_soap_connection();