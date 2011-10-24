<?php

/*
 * Establish reference to simpletest
 * we will include this in all our tests
 * it bootstraps the test suite
 */
ini_set('error_reporting', E_ALL);

$path_to_simpletest = realpath(dirname(dirname(dirname(__FILE__))));

set_include_path(join(PATH_SEPARATOR, array($path_to_simpletest, get_include_path())));

require_once 'simpletest/autorun.php';

require_once 'PHPEconomics/economics.v1.php';

class EconomicsTest extends UnitTestCase
{
	function setUp() {}
	
  function tearDown() {}
	// SoapClient
	function testSoapClient() {}
	// Debtor_GetAll
  function testDebtorGetAll() {
		$soap_client = get_soap_connection();
		
		$debtors = debtor_get_all();
		
		$this->assertTrue(is_array($debtors));
  }
	// Debtor_GetData
	function testDebtorGetInvoices() {
		$soap_client = get_soap_connection();
		
		$invoices = debtor_get_invoices(105);
		
		$this->assertTrue(is_array($invoices));
	}
}
