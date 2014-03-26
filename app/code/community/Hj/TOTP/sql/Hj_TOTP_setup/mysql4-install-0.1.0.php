<?php

$installer = $this;
$installer->startSetup();
/* @var $installer Mage_Core_Model_Resource_Setup */


if (!extension_loaded('openssl')) {
    Mage::throwException('Openssl PHP extension (http://www.php.net/manual/en/book.openssl.php) is required to use this module (HJ_TOTP)');
}
//$helper = Mage::helper('Hj_TOTP');
/* @var $helper Hj_TOTP_Helper_Data */
$encryption_helper = Mage::helper('Hj_TOTP/Encryption');
/* @var $encryption_helper Hj_TOTP_Helper_Encryption */

/* Generating and storing the encryption key which will be used to encrypt TOTP seeds before insertion in the DB */
$strong = false;

$key_size = mcrypt_get_key_size($encryption_helper->getEncryptionCipher(), $encryption_helper->getEncryptionMode());
$key = openssl_random_pseudo_bytes($key_size, $strong);
//@TODO : check for $strong and alert the user if false

mkdir($encryption_helper->getKeyFileDir(), 0777, true);

$file_path = $encryption_helper->getKeyFileDir() . DS . $encryption_helper->getKeyFileName();
echo $file_path;

if (is_file($file_path)) {
    $i = 0;
    while (is_file($file_path . 'old' . $i)) {//looking for an available file name
	$i++;
    }
    if (!rename($file_path, $file_path . 'old' . $i)) {
	Mage::throwException('A key (' . $file_path . ') already exists and we can\'t move it');
    }
}

$result = file_put_contents($file_path, $key);

if (!$result) {
    Mage::throwException('Error while writing into file "' . $file_path . '"');
}
chmod($file_path, 0700);

/////////////////////////
$installer->run("ALTER TABLE `{$installer->getTable('admin/user')}` ADD `TOTP_seed` VARBINARY(256) NULL COMMENT 'User TOTP seed';
	
    CREATE TABLE `{$installer->getTable('Hj_TOTP/invalidToken')}` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `admin_user_id` int(10) unsigned NOT NULL,
      `timestamp` timestamp NOT NULL,
      `token` varchar(256) NOT NULL COMMENT 'hashed token',
      FOREIGN KEY (`admin_user_id`) REFERENCES `{$installer->getTable('admin/user')}` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
    );");
$installer->endSetup();