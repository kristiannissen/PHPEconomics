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

class EconomicsTest extends UnitTestCase {
	function setUp() {}
	
  function tearDown() {}
	
	function testDebtorNotFoundByName() {
		$debtor = debtor_find_by_name('CompuGlobalHyperMegaNet');
		// We test that this customer does not exist
		$this->assertTrue(is_null($debtor));
	}

	function testDebtorFindByName() {
		$debtor = debtor_find_by_name('Expotium GmbH');
		// We test that this customer does exist
		$this->assertTrue(is_object($debtor));
	}
	
	function testDebtorFindOnTwitter() {
		$debtor = debtor_find_by_name('Expotium GmbH');
		$twitter = debtor_uses_twitter($debtor);

		$this->assertTrue(is_array($twitter));
	}
	
	function testDebtorGeocodeAddress() {
		$debtor = debtor_find_by_name('Expotium GmbH');
		$geocode = debtor_geocode_address($debtor);
		
		$this->assertTrue(is_array($geocode));
	}
	
	function testDebtorFindByNumber() {
		$debtor = debtor_find_by_number(107);
		// We test that this customer exists
		$this->assertTrue(is_object($debtor));
	}
	
	function testDebtorNotFoundByNumber() {
		$debtor = debtor_find_by_number(42);
		// We test that this customer does not exist
		$this->assertTrue(is_null($debtor));
	}

	// Debtor_GetAll
  function testDebtorGetAll() {
		$debtors = debtor_get_all();
		// We test that we get an array in retur, check it's length
		$this->assertTrue(is_array($debtors));
  }
	// Debtor_GetData
	function testDebtorGetInvoices() {
		$invoices = debtor_get_invoices(105);
		
		$this->assertTrue(is_array($invoices));
	}
	
	function testDebtorHasNoInvoices() {
		$invoices = debtor_get_invoices(42);
		
		$this->assertTrue(is_null($invoices));
	}
	
	// Debtor_GetData
	function testDebtorGetCurrentInvoices() {
		
		$invoices = debtor_get_current_invoices(107);
		
		$this->assertTrue(is_array($invoices));
	}
	
	function testDebtorHasNoCurrentInvoices() {
		$invoices = debtor_get_current_invoices(105);
		
		$this->assertTrue(is_null($invoices));
	}
}
