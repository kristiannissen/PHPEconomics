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
 * if ($debtor = debtor_find_by_name('CompuGlobalHyperMegaNet')) {
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
			$debtor_handles = array();
			
			$handle = $result->Debtor_FindByNameResult->DebtorHandle;
			
			if (is_object($handle)) {
				array_push($debtor_handles, $handle);
			} else {
				$debtor_handles = $handle;
			}
			
			$result = $soap_client->Debtor_GetDataArray(array(
				'entityHandles' => $debtor_handles
			));
			
			return $result->Debtor_GetDataArrayResult->DebtorData;
		}
	}
	
	return null;
}

/**
 * This function accepts an array containing the debtor data to be updated
 * as well as a copy of the debtor to update
 * TODO: Error handling
 * https://www.e-conomic.com/secure/api1/EconomicWebservice.asmx?op=Debtor_UpdateFromData
 */
function debtor_update_data($params = array()) {
	global $soap_client;
	
	$debtor = (array) $params['Debtor'];
	
	unset($params['Debtor']);
	
	$data = array_merge($debtor, $params);
	
	$number = $soap_client->Debtor_UpdateFromData(array(
		'data' => $data
	))->Debtor_UpdateFromDataResult;
	
	$debtor = debtor_find_by_number(intval($number->Number));
	
	return $debtor;
}

function debtor_create($params = array()) {
	global $soap_client;
	
	$debtor = debtor_find_by_name($params['name']);
	
	if (!is_null($debtor)) {
		trigger_error(sprintf('Debtor with name %s exists', $params['name']), E_USER_ERROR);
	}
	
	$debtorgroup_handle = debtorgroup_find_by_name($params['debtorgroupname']);
	
	if (is_null($debtorgroup_handle)) {
		trigger_error(sprintf('DebtorGroupName %s does not exist', $params['debtorgroupname']), E_USER_ERROR);
	}
	// Remove the DebtorGroupName key since it's not accepted by the API
	unset($params['debtorgroupname']);
	
	$number = $soap_client->Debtor_GetNextAvailableNumber()->Debtor_GetNextAvailableNumberResult;
	
	$debtor = array_merge(array(
		'vatZone' => 'HomeCountry',
		'number' => $number,
		'debtorGroupHandle' => $debtorgroup_handle->Handle,
	), $params);
	
	if (is_int($number)) {
		$result = $soap_client->Debtor_Create($debtor);
		
		if (is_object($result) && property_exists($result, 'Debtor_CreateResult')) {
			$soap_client->Debtor_SetIsAccessible(array(
				'debtorHandle' => (object) array(
					'Number' => intval($result->Debtor_CreateResult->Number)
				),
				'value' => true
			));
			
			if (array_key_exists('address', $debtor)) {
				$soap_client->Debtor_SetAddress(array(
					'debtorHandle' => (object) array(
						'Number' => intval($result->Debtor_CreateResult->Number)
					),
					'value' => $debtor['address']
				));
			}
			
			if (array_key_exists('city', $debtor)) {
				$soap_client->Debtor_SetCity(array(
					'debtorHandle' => (object) array(
						'Number' => intval($result->Debtor_CreateResult->Number)
					),
					'value' => $debtor['city']
				));
			}
			
			if (array_key_exists('country', $debtor)) {
				$soap_client->Debtor_SetCountry(array(
					'debtorHandle' => (object) array(
						'Number' => intval($result->Debtor_CreateResult->Number)
					),
					'value' => $debtor['country']
				));
			}
			
			if (array_key_exists('postalcode', $debtor)) {
				$soap_client->Debtor_SetPostalCode(array(
					'debtorHandle' => (object) array(
						'Number' => intval($result->Debtor_CreateResult->Number)
					),
					'value' => $debtor['postalcode']
				));
			}
			
			if (array_key_exists('email', $debtor)) {
				$soap_client->Debtor_SetEmail(array(
					'debtorHandle' => (object) array(
						'Number' => intval($result->Debtor_CreateResult->Number)
					),
					'value' => $debtor['email']
				));
			}
			
			if (array_key_exists('website', $debtor)) {
				$soap_client->Debtor_SetWebsite(array(
					'debtorHandle' => (object) array(
						'Number' => intval($result->Debtor_CreateResult->Number)
					),
					'value' => $debtor['website']
				));
			}
			
			return debtor_find_by_number(intval($number));
		}
	}
	
	return null;
}

function debtor_delete($name = null) {
	global $soap_client;
	
	$debtor = debtor_find_by_name($name);

	if (is_array($debtor)) {
		$debtor = array_shift($debtor);
	}

	$result = $soap_client->Debtor_Delete(array(
		'debtorHandle' => (object) array(
			'Number' => intval($debtor->Number)
		)
	));
	
	if (property_exists($result, 'Number')) {
		return false;
	}
	
	return true;
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
 * } else {
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

/**
 * TemplateCollection_GetDataArray
 */
function templatecollection_get_all() {
	global $soap_client;
	
	$result = $soap_client->TemplateCollection_GetAll();
	
	if (is_object($result) && property_exists($result, 'TemplateCollection_GetAllResult')) {
		$entity_handles = $result->TemplateCollection_GetAllResult->TemplateCollectionHandle;
		
		if (is_array($entity_handles)) {
			$collection_data = $soap_client->TemplateCollection_GetDataArray(array(
				'entityHandles' => $entity_handles
			));
			
			if (is_object($collection_data) && property_exists($collection_data, 'TemplateCollection_GetDataArrayResult')) {
				return $collection_data->TemplateCollection_GetDataArrayResult->TemplateCollectionData;
			}
		}
	}
	
	return null;
}

/**
 * 
 */
function templatecollection_find_by_name($name = null) {
	global $soap_client;
	
	if (is_null($name)) {
		trigger_error('templatecollection_find_by_name($name) takes exactly one parameter, none passed', E_USER_ERROR);
	}
	
	$result = $soap_client->TemplateCollection_FindByName(array(
		'name' => $name
	));
	
	if (is_object($result) && property_exists($result, 'TemplateCollection_FindByNameResult')) {
		$handle = $result->TemplateCollection_FindByNameResult;

		if (is_object($handle) && property_exists($handle, 'TemplateCollectionHandle')) {
			if (property_exists($handle->TemplateCollectionHandle, 'Id')) {
				$result = $soap_client->TemplateCollection_GetData(array(
					'entityHandle' => $handle->TemplateCollectionHandle
				));

				return $result->TemplateCollection_GetDataResult;
			}
		}
	}
	
	return null;
}

/**
 * 
 */
function debtorgroup_get_all() {
	global $soap_client;
	
	$result = $soap_client->DebtorGroup_GetAll();
	
	if (is_object($result) && property_exists($result, 'DebtorGroup_GetAllResult')) {
		$handles = $result->DebtorGroup_GetAllResult;
		
		if (is_object($handles) && property_exists($handles, 'DebtorGroupHandle')) {
			$result = $soap_client->DebtorGroup_GetDataArray(array(
				'entityHandles' => $handles->DebtorGroupHandle
			));
			
			if (is_object($result) && property_exists($result, 'DebtorGroup_GetDataArrayResult')) {
				return $result->DebtorGroup_GetDataArrayResult->DebtorGroupData;
			}
		}
	}
	
	return null;
}

/**
 *
 */
function debtorgroup_find_by_name($name = null) {
	global $soap_client;
	
	$result = $soap_client->DebtorGroup_FindByName(array(
		'name' => $name
	));
	
	if (is_object($result) && property_exists($result, 'DebtorGroup_FindByNameResult')) {
		$handle = $result->DebtorGroup_FindByNameResult;
		
		if (is_object($handle) && property_exists($handle, 'DebtorGroupHandle')) {
			$result = $soap_client->DebtorGroup_GetData(array(
				'entityHandle' => $handle->DebtorGroupHandle
			));
			
			if (is_object($result) && property_exists($result, 'DebtorGroup_GetDataResult')) {
				return $result->DebtorGroup_GetDataResult;
			}
		}
	}
	
	return null;
}

$soap_client = get_soap_connection();