-- Be careful before running this script, data will be definitely deleted. We recommend that you backup your entire SQL database before performing critical operations like this one.
-- Execute at your own risks.
-- Note : if your Magento installation uses a table prefix, you'll have to add it manually before every table name in this script
DROP TABLE `Hj_TOTP_InvalidToken`;
ALTER TABLE `admin_user` DROP `TOTP_seed`;
