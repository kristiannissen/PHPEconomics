<?php

include_once 'bootstrap.php';

class TestInvoice extends UnitTestCase {
	protected $ew;
	
	function setUp () {
		$this->ew = new EconomicsWS;
	}
	
	function tearDown () {
		unset($this->ew);
	}
	
	function testAddOrderToInvoice () {
		// Fetch any debtor
		$debtor = $this->ew->get_debtor(array('number' => 107));
		// Fetch any product
		$product = array_shift($this->ew->get_products());
		// Fetch debtors current invoices
		$invoices = $this->ew->get_debtor_current_invoices(array('number' => intval($debtor->Handle->Number)));
		// Add an order to current invoice
		$this->assertTrue($this->ew->add_order_to_invoice(array(
			'debtor_number' => intval($debtor->Handle->Number),
			'product_id' => intval($product->Handle->Number),
			'quantity' => '10',
			'invoice_id' => null,
			'price' => '12499.00'
		)));
	}
}