<?php
class Hj_TOTP_Helper_Data extends Mage_Core_Helper_Abstract{

    public function getModuleMediaDir(){
	return Mage::getBaseDir('media').DS.'Hj_TOTP';
    }
    
    public function getModuleVarDir(){
	return Mage::getBaseDir('var').DS.'Hj_TOTP';
    }
    
    public function getQRCodesDir(){
        return $this->getModuleMediaDir().DS.'qrcodes';
    }
}