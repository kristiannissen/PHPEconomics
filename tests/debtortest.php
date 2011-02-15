<?php
/*
 * Run test for debtor
 */

include_once 'bootstrap.php';

class TestDebtor extends UnitTestCase {
	protected $ew;
	
	function setUp () {
		$this->ew = new EconomicsWS;
	}
	
	function tearDown () {
		unset($this->ew);
	}
	
	function testGetDebtors () {
		// Get list of all debtors
		$debtors = $this->ew->get_debtors();
		// Test that we have a list of debtors
		$this->assertTrue(count($debtors) > 0);
	}
	/*
	 * Returns a debtor data object for a given debtor.
	 * See https://www.e-conomic.com/secure/api1/EconomicWebservice.asmx?op=Debtor_GetData for more details
	 */
	function testGetDebtor () {
		// We will just take the first debtor for testing
		$debtor = array_shift($this->ew->get_debtors());
		// Test that our debtor has a handle
		$this->assertTrue(property_exists($debtor, 'Handle'));
		// Make sure number is an int
		$number = intval($debtor->Handle->Number);
		// Fetch debtor
		$debtor = $this->ew->get_debtor(array('number' => $number));
		// Verify that our debtor has a name
		$this->assertTrue(property_exists($debtor, 'Name'));
	}
	/*
	 * get_debtor_invoices
	 * https://www.e-conomic.com/secure/api1/EconomicWebservice.asmx?op=Invoice_GetDataArray
	 */
	function testGetDebtorInvoices () {
		// We will just take the first debtor for testing
		$debtor = array_shift($this->ew->get_debtors());
		// Test that our debtor has a handle
		$this->assertTrue(property_exists($debtor, 'Handle'));
		// Make sure number is an int
		$number = intval($debtor->Handle->Number);
		// Get list of invoices
		$invoices = $this->ew->get_debtor_invoices(array('number' => $number));
		// Test that we have a valid response
		$this->assertTrue(gettype($invoices) == 'array');
	}
	/*
	 * get_debtor_current_invoices
	 */
	function testGetDebtorCurrentInvoices () {
		// Fetch any debtor
		$debtor = array_shift($this->ew->get_debtors());
		// Fetch current invoices
		$invoices = $this->ew->get_debtor_current_invoices(array(
			'number' => intval($debtor->Handle->Number)
		));
		// Test if the return type is correct
		$this->assertTrue(is_array($invoices));
	}
}