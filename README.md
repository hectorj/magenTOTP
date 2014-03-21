magenTOTP
=========

Magento module adding TOTP (2FA) support to admin login (packaged as Hj_TOTP to ensure the name's unicity)

## Disclaimer

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

Make backups, try it on a test server first, and be careful folks.

## How to

### Install

Just copy the files at the root of your magento folder. It should not overwrite any files.

It is advised to disable Magento compilation before installation.

Flushing your Magento cache may be required.

### Use

In the backend, go to the "System > My Account". If the module was correctly installed, there should be a new section named "OTP Information".

Click on the "Enable OTP" dropdown and select yes. A QRcode should appear. Just scan it with your TOTP application (Google Authenticator for example : https://support.google.com/accounts/answer/1066447?hl=en) and enter the 6-digits token the application gives you. Note that it is time sensitive, you'll have to click the save button before the token expires.

It is also advised to write down the seed (which should be displayed below the QRcode) and store it in a **secure** place in case your smartphone is lost, broken or wiped out.

Now the next time you will want to login to Magento with that account, you will have to enter an OTP token (like you just did) in addition to your username and password.

### Disable

To disable it, go back to "System > My Account" and switch the "Enable OTP" dropdown to "No", then save.

If for some reason you definitely lost the seed and can't access your account anymore, you will have to access your database, and set the TOTP_seed field for your account to NULL in the "admin_user" table. Again, be careful what you do, and backup your data.

## Compatibility

Tested on Magento 1.8.1.0/PHP Version 5.4.9-4ubuntu2.4/Apache 2.0

This module uses the OpenSSL php extension (http://www.php.net/manual/en/intro.openssl.php) to generate cryptographically secure numbers.
