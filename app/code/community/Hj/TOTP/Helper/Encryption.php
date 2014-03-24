<?php
class Hj_TOTP_Helper_Encryption extends Mage_Core_Helper_Abstract{
    
    public function getEncryptionCipher(){
	return MCRYPT_RIJNDAEL_256;//note : in case of an update changing the cypher, we'll have to re-encrypt previously encoded datas to not break compatibility
    }
    
    public function getEncryptionMode(){
	return MCRYPT_MODE_CFB;//note : in case of an update changing the mode, we'll have to re-encrypt previously encoded datas to not break compatibility
    }
    
    public function getKeyFileName(){
	return 'k';
    }
    
    public function getKeyFileDir(){
	return Mage::helper('Hj_TOTP')->getModuleVarDir();
    }
    
    protected function getEncryptionIVSize(){
	return mcrypt_get_iv_size($this->getEncryptionCipher(), $this->getEncryptionMode());
    }
    
    public function getEncryptionKey(){
	return file_get_contents($this->getKeyFileDir().DS.$this->getKeyFileName());
    }
    
    public function encrypt($data){
	$iv = mcrypt_create_iv($this->getEncryptionIVSize(), MCRYPT_DEV_URANDOM);
	return $iv.mcrypt_encrypt($this->getEncryptionCipher(), $this->getEncryptionKey(), $data, $this->getEncryptionMode(), $iv);
    }
    
    public function decrypt($data){
	$iv_size = $this->getEncryptionIVSize();
	$iv = substr($data, 0, $iv_size);
	$data = substr($data, $iv_size);
	return mcrypt_decrypt($this->getEncryptionCipher(), $this->getEncryptionKey(), $data, $this->getEncryptionMode(), $iv);
    }
}