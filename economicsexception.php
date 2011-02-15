<?
class EconomicsException extends Exception {
	public function __toString () {
		$_msg = "Exception [". __CLASS__ ."] threw an exception: [". $this->getMessage() ."] at line: ". $this->getLine();
		
		if (error_log($_msg, 3, dirname(__FILE__) . 'economics.log')) {
      return $_msg;
    }
		
		return "[Could not log error]" . $_msg;
	}
}