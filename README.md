magenTOTP
=========

Magento CE module adding Time-based One-Time Password (TOTP) (2 factor authentication (2FA)) support to admin login (packaged as Hj_TOTP to ensure the name's unicity)

**Important note : This module is still a beta version**

## **Disclaimer**

**This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.**

**Make backups, try it on a test server first, and be careful folks.**

## Useful (but not required) reads

If you want to understand more about what this extension does (2FA/TOTP), I recommend you read :
- https://en.wikipedia.org/wiki/Two-factor_authentication
- https://en.wikipedia.org/wiki/Time-based_One-time_Password_Algorithm
- RFC6238 : http://tools.ietf.org/html/rfc6238

## How to..

### ..Install

Just copy the files at the root of your magento folder. It should not overwrite any files.

It is advised to disable Magento compilation before installation.

Flushing your Magento cache may be required.

### ..Use

In the backend, go to the "System > My Account". If the module was correctly installed, there should be a new section named "OTP Information".

Click on the "Enable OTP" dropdown and select yes. A QRcode should appear. Just scan it with your TOTP application (Google Authenticator for example : https://support.google.com/accounts/answer/1066447?hl=en) and enter the 6-digits token the application gives you. Note that it is time sensitive, you'll have to click the save button before the token expires.

It is also advised to write down the seed (which should be displayed below the QRcode) and store it in a **secure** place in case your smartphone is lost, broken or wiped out.

Now the next time you will want to login to Magento with that account, you will have to enter an OTP token (like you just did) in addition to your username and password.

### ..Disable

To disable it, go back to "System > My Account" and switch the "Enable OTP" dropdown to "No", then save.

If for some reason you definitely lost the seed and can't access your account anymore, you will have to access your database, and set the TOTP_seed field for your account to NULL in the "admin_user" table. Again, be careful what you do, and backup your data.

### ..Uninstall

Remove all the module files, and then run the uninstall SQL script corresponding to your module version on your database (which can be found in that folder : [/app/code/community/Hj/TOTP/sql/Hj_TOTP_setup](app/code/community/Hj/TOTP/sql/Hj_TOTP_setup)).

**It will definitely remove all data related to this module**, be careful. As always, you should have a **backup** of your database ready to be rolled back in case something wrong happens.

## Security Notes

While 2FA is a good additional layer of security, it gets broken as soon as your seed is compromised.

The seed is given to you in two forms : textual, and a QRcode. None of them should be shared with **anyone**.

Do not generate your seed through untrusted proxys, an untrusted connection or an untrusted system/hardware.

Enabling HTTPS is **strongly recommended** to be sure that no one intercepted it while it was transfered to you (and if you are worried about security, it is something you should have done a long time ago already).

The seeds are stored encrypted in your databse. The encryption key is stored in your file system (in your "var" Magento directory) so anyone with an access to the databse and the key can compromise the TOTP system (anyone with an access to your Magento database can actually do nearly everything he wants to your store).

The QRcode files on the server-side are deleted as soon as possible.

The extension set HTTP headers to tell your browser to not keep it in cache.

To finish, using this module should not stop you from other good security practices, such as long, random passwords shared with no other people nor services etc... (you can find a few tips here, even if the article is a little outdated : http://addoa.com/blog/ten-tips-keeping-your-magento-store-secure )

## Troubleshoot

### After copying the files into my Magento folder, every page has become an error page

Something went wrong during installation. For an instant quickfix, change `<active>true</active>` to `<active>false</active>` in the file [/app/etc/modules/Hj_TOTP.xml](app/etc/modules/Hj_TOTP.xml)

For some more advanced debugging, check your Magento /var/report directory, and eventually come post an issue on Github (https://github.com/hectorj/magenTOTP/issues/new)

### I don't have the "OTP Information" section nor the OTP field at admin login

Check that you correctly copied the files and that they are readable, then flush your Magento cache.

### There is no QRcode above the OTP seed

Check that your /media folder is writable

## Compatibility

Tested on Magento CE 1.8.1.0/PHP Version 5.4.9-4ubuntu2.4/Apache 2.0

This module uses the OpenSSL php extension (http://www.php.net/manual/en/intro.openssl.php) to generate cryptographically secure numbers.

## Credits

This module makes use of :

- the (slightly modified) TOTP class from Phil : http://www.idontplaydarts.com/2011/07/google-totp-two-factor-authentication-for-php/ (license : originally GPL but I got his consent to change that)
- the phpqrcode library from Dominik Dzienia : http://phpqrcode.sourceforge.net/ (license : LGPL)
- the hashing class from Taylor Hornby : https://defuse.ca/php-pbkdf2.htm (license : ... some kind of homemade license you will find in the https://github.com/hectorj/magenTOTP/blob/master/app/code/community/Hj/TOTP/Helper/Hash.php file)
- and of course Magento CE : http://magento.com/ (license : OSL v3)

Thanks all of them for their open-source code.

## Contact

I want your feedback! I am open to criticism, and even more to suggestions. Do not hesitate to create issues and pull requests on Github (https://github.com/hectorj/magenTOTP) or to send me an email at hector.jusforgues@gmail.com

## License

This module is released under the Open Software License version 3.0 (see [LICENSE.md file](LICENSE.md) or here : http://opensource.org/licenses/OSL-3.0)

### Why that license?

Because I use some code from the Magento CE core, which is released under the OSL v3. If I understood it right, it forces me to release my code under the same license. If I had the choice I would prefer the GNU LGPL or some other restriction-free license.

## Conclusion

Thanks for your interest in this project.
