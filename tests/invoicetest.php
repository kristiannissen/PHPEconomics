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
		
	}
}