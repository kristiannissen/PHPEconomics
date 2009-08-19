<?php
/**
 * @author: Kristian Nissen
 * @version: 0.2
 */
class EconomicsWS {
  // Economics SOAP
  const WSDL = 'https://www.e-conomic.com/secure/api1/EconomicWebservice.asmx?WSDL';
  const ADMINAGREEMENTNO = '';
  const ADMINUSERID = '';
  const ADMINUSERPASSWORD = '';
  const CLIENTAGREEMENTNO = '';
  // Soap client
  private $client;
  
  function __construct() {    
    try {
      $this->client = new SoapClient(EconomicsWS::WSDL, array("trace" => 1, "exceptions" => 1));    
      $this->client->ConnectAsAdministrator(array(
		    'adminAgreementNo' => intval(EconomicsWS::ADMINAGREEMENTNO), 
		    'adminUserID' => EconomicsWS::ADMINUSERID,
		    'adminUserPassword' => EconomicsWS::ADMINUSERPASSWORD,
		    'clientAgreementNo' => intval(EconomicsWS::CLIENTAGREEMENTNO)
		  ));
    } catch (Exception $e) {
      syslog(LOG_DEBUG, 'exception '. $e->getMessage() .' at line '. $e->getLine() .' SOAP request '. $this->client->__getLastRequest() .' SOAP response '. $this->client->__getLastResponse());
    }
  }
  function disconnect() {
    $this->client->Disconnect();  
  }
  /**
   * @param mixed $params
   * @param string [$vat_zone]
   * @return int
   */
  function create_debitor($params, $vat_zone = 'HomeCountry') {
    try {
	    $debtor = $this->client->Debtor_FindByName(array(
	      'name' => $params['debitor_name']
	    ))->Debtor_FindByNameResult->DebtorHandle;
	    
	    if (!$debtor) {
		    $number = $this->client->Debtor_GetNextAvailableNumber()->Debtor_GetNextAvailableNumberResult;
		    
		    $debtor_group_handles = $this->client->debtorGroup_GetAll()->DebtorGroup_GetAllResult->DebtorGroupHandle;		    
		    
		    $currency_handle = $this->client->Currency_FindByCode(array(
          'code' => $params['currency']
        ))->Currency_FindByCodeResult;
        
        $term_handle = $this->client->TermOfPayment_FindByName(array(
          'name' => $params['term_of_payment']
        ))->TermOfPayment_FindByNameResult->TermOfPaymentHandle;
        
        $templatecollection_handle = $this->client->TemplateCollection_FindByName(array(
          'name' => $params['template_name']
        ))->TemplateCollection_FindByNameResult->TemplateCollectionHandle;
        
        $employee_handle = $this->client->Employee_FindByNumber(array(
          'number' => employee_number
        ))->Employee_FindByNumberResult->EmployeeHandle;
        
        $debtor_data = (object)array(          
          'IsAccessible' => true,
          'Number' => $number,
          'DebtorGroupHandle' => $debtor_group_handles,
          'Name' => $params['debitor_name'],
          'VatZone' => $vat_zone,
          'CurrencyHandle' => $currency_handle,
          'TermOfPaymentHandle' => $term_handle,
          'LayoutHandle' => $templatecollection_handle,
          'Name' => $params['debitor_name'],
          'Email' => $params['debitor_email'],
          'TelephoneAndFaxNumber' => $params['debitor_phone_and_fax'],
          'Website' => $params['debitor_website'],
          'Address' => $params['debitor_address'],
          'PostalCode' => $params['debitor_postalcode'],
          'City' => $params['debitor_city'],
          'Country' => $params['debitor_city'],
          'OurReferenceHandle' => $employee_handle
        );        
		    $this->client->Debtor_CreateFromData(array(
		      'data' => $debtor_data
		    ));
		    $debtor = $this->client->Debtor_FindByNumber(array(
		      'number' => $number
		    ))->Debtor_FindByNumberResult;
		    
		    $debtor_contact_handle = $this->client->DebtorContact_Create(array(
		      'debtorHandle' => $debtor,
		      'name' => $params['debitor_attention']
		    ))->DebtorContact_CreateResult;
		    
		    $this->client->Debtor_SetAttention(array(
		      'debtorHandle' => $debtor,
		      'valueHandle' => $debtor_contact_handle
		    ));
		    
		    if(!empty($params['debitor_ean'])){
		      $this->client->Debtor_SetEan(array(
		        'debtorHandle' => $debtor,
		        'valueHandle' => $params['debitor_ean']
		      ));
		    }
	    } else {
	      $number = 0;
	    }
	  } catch (Exception $e) {
      syslog(LOG_DEBUG, 'exception '. $e->getMessage() .' at line '. $e->getLine() .' SOAP request '. $this->client->__getLastRequest() .' SOAP response '. $this->client->__getLastResponse());
      $number = 0;
	  }
	  syslog(LOG_DEBUG, 'new debtor '. $number);

	  return intval($number);
  }  
  /**
   * @return mixed
   */
  function get_currencies() {
    return $this->client->Currency_GetAll()->Currency_GetAllResult->CurrencyHandle;
  }
  /**
   * @return mixed
   */
  function get_term_of_payment() {
    $terms = $this->client->TermOfPayment_GetAll()->TermOfPayment_GetAllResult->TermOfPaymentHandle;
    return $this->client->TermOfPayment_GetDataArray(array(
      'entityHandles' => $terms
    ))->TermOfPayment_GetDataArrayResult->TermOfPaymentData;
  }
  /**
   * @return mixed
   */
  function get_templates() {
    $templates = $this->client->TemplateCollection_GetAll()->TemplateCollection_GetAllResult->TemplateCollectionHandle;
    return $this->client->TemplateCollection_GetDataArray(array(
      'entityHandles' => $templates
    ))->TemplateCollection_GetDataArrayResult->TemplateCollectionData;
  }
  /**
   * @return mixed
   */
  function get_debitors() {
    return $this->client->Debtor_GetAll()->Debtor_GetAllResult->DebtorHandle;
  }
  /**
   * @return mixed
   */
  function get_employees() {
    try {
      $employee_handle = $this->client->Employee_GetAll()->Employee_GetAllResult->EmployeeHandle;

      $employees = $this->client->Employee_GetDataArray(array(
        'entityHandles' => $employee_handle
      ))->Employee_GetDataArrayResult->EmployeeData;
    } catch (Exception $e) {
      syslog(LOG_DEBUG, 'exception '. $e->getMessage() .' at line '. $e->getLine() .' SOAP request '. $this->client->__getLastRequest() .' SOAP response '. $this->client->__getLastResponse());
    }
    
    return $employees;
  }
  /**
   * @return object
   */
  function get_debitor($params) {
    try {
      $debtor = $this->client->Debtor_FindByNumber(array(
        'number' => $params['debitor_number']
      ))->Debtor_FindByNumberResult;
      
      $debtor_data = $this->client->Debtor_GetData(array(
        'entityHandle' => $debtor
      ))->Debtor_GetDataResult->Handle;
    } catch (Exception $e) {
      syslog(LOG_DEBUG, 'exception '. $e->getMessage() .' at line '. $e->getLine() .' SOAP request '. $this->client->__getLastRequest() .' SOAP response '. $this->client->__getLastResponse());
      $debtor_data = (object)array();
    }
    
    return $debtor_data;
  }
}
