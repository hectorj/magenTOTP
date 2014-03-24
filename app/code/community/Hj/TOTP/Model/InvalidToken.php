<?php
class Hj_TOTP_Model_InvalidToken extends Mage_Core_Model_Abstract {
    protected function _construct()
    {
        $this->_init('Hj_TOTP/invalidToken');
    }
    
    public function checkIfExists($userId, $token, $timeframe=null){
	$invalidTokens=$this->loadByUser($userId, $timeframe);
	if(is_array($invalidTokens) && !empty($invalidTokens)){
	    $hash_helper=Mage::helper('Hj_TOTP/Hash');
	    /* @var $hash_helper Hj_TOTP_Helper_Hash */
	    foreach($invalidTokens as $invalidToken){
		if($hash_helper->validate_password($token, $invalidToken['token'])){
		    return $invalidToken['timestamp'];
		}
	    }
	}
	return false;
    }
    
    public function setToken($token){
	return parent::setToken(Mage::helper('Hj_TOTP/Hash')->create_hash($token));
    }
    
    public function setTimestamp($timestamp=null){
	return parent::setTimestamp($timestamp === null ? time() : $timestamp);
    }
    
    public function loadByUser($userId, $timeframe=null){
	return $this->_getResource()->loadByUser($userId, $timeframe);
    }
}
?>
