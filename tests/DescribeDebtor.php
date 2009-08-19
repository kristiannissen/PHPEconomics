<?php
/**
 * @author: Kristian Nissen
 * @version: 0.1
 */
include '../economicsws.php';
 
class DescribeDebtor extends PHPSpec_Context {
  protected $econ;  
  
  function beforeAll() { 
    $this->econ = new EconomicsWS();
  }
  function afterAll() {
    $this->econ->disconnect();
    
    unset($this->econ);
  }
  /**   
   */
  function itShouldReturnIdOnCreate() {
    $params = array(		
		  'debitor_name' => '',
		  'debitor_ean' => '',
		  'debitor_email' => '',
		  'debitor_phone_and_fax' => '',
		  'debitor_website' => '',
		  'debitor_address' => '',
		  'debitor_postalcode' => '',
		  'debitor_city' => '',
		  'debitor_country' => '',
		  'debitor_vatnumber' => '',
		  'currency' => '',
		  'term_of_payment' => '',
		  'template_name' => '',
		  'debitor_attention' => '',
		  'employee_number' => ''
	  );	
	  $this->spec($this->econ->create_debitor($params) > 0)->should->beTrue();	
  }
  /**
   * Helpers
   */
  function itShouldReturnAnArrayWhenGetTemplatesIsCalled() {
    $this->spec(gettype($this->econ->get_templates()) == 'array')->should->beTrue();
  }
  function itShouldReturnAnArrayWhenTermOfPaymentIsCalled() {
    $this->spec(gettype($this->econ->get_term_of_payment()) == 'array')->should->beTrue();
  }
  function itShouldReturnAnArrayWhenGetCurrenciesIsCalled() {
    $this->spec(gettype($this->econ->get_currencies()) == 'array')->should->beTrue();
  }  
  function itShouldReturnAnArrayWhenGetDebtorsIsCalled() {
    $this->spec(gettype($this->econ->get_debitors()) == 'array')->should->beTrue();
  }
  function itShouldReturnAnArrayWhenGetEmployessIsCalled() {
    $this->spec(gettype($this->econ->get_employees()) == 'array')->should->beTrue();
  }  
  function itShouldReturnAnObjectWhenGetDebitorIsCalled() {
    $params = array(
      'debitor_number' => ''
    );
    $this->spec(gettype($this->econ->get_debitor($params)) == 'object')->should->beTrue();
  }
  function itShouldReturnAnArrayWhenGetDebitorInvoicesIsCalled() {
    $params = array(
      'debitor_number' => ''
    );
    $this->spec(count($this->econ->get_debitor_invoices($params)) > 0)->should->beTrue();
  }
}
