<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();
$installer->run("ALTER TABLE `{$installer->getTable('admin/user')}` ADD `TOTP_seed` char(16) COLLATE 'utf8_general_ci' NULL COMMENT 'User TOTP seed';");
$installer->endSetup();