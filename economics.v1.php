<?php

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
 * if ($invoices == debtor_get_invoices($params)) {
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
	
	$result = $soap_client->Debtor_GetInvoices(array(
		'debtorHandle' => (object) array(
			'Number' => (string) $number
		)
	));
	
	if (is_object($result) && property_exists($result, 'Debtor_GetInvoicesResult')) {
		$invoice_handles = $result->Debtor_GetInvoicesResult->InvoiceHandle;
		
		$result = $soap_client->Invoice_GetDataArray(array('entityHandles' => $invoice_handles));
		
		if (is_object($result) && property_exists($result, 'Invoice_GetDataArrayResult')) {
			return $result->Invoice_GetDataArrayResult->InvoiceData;
		}
	}
	
	return null;
}

$soap_client = get_soap_connection();