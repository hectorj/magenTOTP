<?php
class Hj_TOTP_Helper_Data extends Mage_Core_Helper_Abstract{
    public function getQRCodesDir(){
        return Mage::getBaseDir('media').DS.'qrcodes';
    }
}