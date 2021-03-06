<?php

/*
 * Establish reference to simpletest
 * we will include this in all our tests
 * it bootstraps the test suite
 */
ini_set('error_reporting', E_ALL);

define('SIMPLETEST', 'simpletest_1.1.0');

$path_to_simpletest = realpath(dirname(dirname(dirname(__FILE__))));

set_include_path(join(PATH_SEPARATOR, array($path_to_simpletest, get_include_path())));

require_once SIMPLETEST .'/autorun.php';

require_once 'PHPEconomics/economics.v1.php';

class EconomicsTest extends UnitTestCase {
	function setUp() {}
	
  function tearDown() {}
	
	function testDebtorNotFoundByName() {
		// In order to test that a debtor does not exist, a random ID is added to the name
    $debtor = debtor_find_by_name(sprintf('CompuGlobalHyperMegaNet-%s', uniqid()));
		// We test that this customer does not exist
		$this->assertTrue(is_null($debtor));
	}
	
	function testDebtorFindSingleByName() {
		$debtor = debtor_find_by_name('Expotium GmbH');
		// We test that this customer does exist
		$this->assertTrue(count($debtor) == 1);
	}

  function testDebtorUpdate() {
    // Create a random dataset
    $countries = array(
      'Denmark',
      'Norway',
      'Sweden',
    );
    // Pick a random country from the dataset
    $test_value = $countries[rand(0, count($countries))];

		$debtor = debtor_find_by_name('Expotium GmbH');
		// This params should resembel a form POST
		$params = array(
			'VatZone' => 'HomeCountry',
			'Country' => $test_value,
			'Debtor' => $debtor,
		);
		
		$debtor = debtor_update_data($params);
		
		$this->assertTrue($debtor->Country == $test_value);
	}
	

	function testDebtorCreate() {
		// This params should resembel a form POST
		$params = array(
			'name' => 'CompuGlobalHyperMegaNet',
			'debtorgroupname' => 'Indenlandske',
			'address' => '742 Evergreen Terrace',
			'city' => 'Springfield',
			'postalcode' => 2300,
			'country' => 'Denmark',
			'email' => 'chunkylover53@aol.com',
			'website' => 'www.compuglobalhypermeganet.com'
		);

		if ($debtor = debtor_find_by_name($params['name'])) {
      // This part is entirely for housekeeping
      if (is_object($debtor)) {
        // Delete old entry
        debtor_delete($debtor->Name);
      }
      if (is_array($debtor)) {
        foreach ($debtor as $d) {
          debtor_delete($d->Name);
        }
      }
	  }
    // Create deptor based on params
    $debtor = debtor_create($params);
    
    $this->assertTrue($debtor->Name == $params['name']);
  }
	
	/*
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
	*/
	
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
	
	function testDebtorGetCurrentInvoices() {
		$invoices = debtor_get_current_invoices(107);
		
		$this->assertTrue(is_array($invoices));
	}
	
	function testDebtorHasNoCurrentInvoices() {
		$invoices = debtor_get_current_invoices(105);
		
		$this->assertTrue(is_null($invoices));
	}
	
	function testProductGetAll() {
		$products = product_get_all();
		
		$this->assertTrue(is_array($products));
	}
	
	function testTemplateCollectionGetAll() {
		$templatecollection = templatecollection_get_all();

		$this->assertTrue(is_array($templatecollection));
	}
	
	function testTemplateCollectionFindByName() {
		$templatecollection = templatecollection_find_by_name('DK. std. m. bankoplys 1.4');

		$this->assertTrue(is_object($templatecollection));
	}
	
	function testTemplateCollectionNotFoundByName() {
		$templatecollection = templatecollection_find_by_name('Does not exist');
		
		$this->assertTrue(is_null($templatecollection));
	}
	
	function testDebtorGroupGetAll() {
		$debtorgroups = debtorgroup_get_all();
		
		$this->assertTrue(is_array($debtorgroups));
	}
	
	function testDebtorGroupFindByName() {
		$debtorgroup = debtorgroup_find_by_name('Indenlandske');
		
		$this->assertTrue(is_object($debtorgroup));
	}
	
	function testDebtorGroupNotFindByName() {
		$debtorgroup = debtorgroup_find_by_name('Does not exist');
		
		$this->assertTrue(is_null($debtorgroup));
	}
}
