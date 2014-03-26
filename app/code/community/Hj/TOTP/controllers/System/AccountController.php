<?php
require_once(Mage::getModuleDir('controllers','Mage_Adminhtml').DS.'System'.DS.'AccountController.php');
class Hj_TOTP_System_AccountController extends Mage_Adminhtml_System_AccountController {
    
    public function indexAction(){
	//Set HTTP headers to try to ensure the TOTP seed won't be cached by the browser in any way
	$this->getResponse()->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate', true);
	$this->getResponse()->setHeader('Pragma', 'no-cache', true);
	$this->getResponse()->setHeader('Expires', '0', true);
	
	if(!Mage::app()->getStore()->isCurrentlySecure()){
	    Mage::getSingleton('adminhtml/session')->addError('It is strongly advised to activate HTTPS');//@TODO : write a better warning
	}
	
	return parent::indexAction();
    }
    
    public function totp_qrcodeAction(){
	//Set HTTP headers to try to ensure the TOTP seed won't be cached by the browser in any way
	$this->getResponse()->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate', true);
	$this->getResponse()->setHeader('Pragma', 'no-cache', true);
	$this->getResponse()->setHeader('Expires', '0', true);
	
	$hash=$this->getRequest()->getParam('id', false);
	if($hash!==false && preg_match('/^[a-f0-9]{64}$/i', $hash)){//check that we have an input and that it is really a sha256 hash
	    $content=file_get_contents(Mage::helper('Hj_TOTP')->getQRCodesDir().DS.$hash.'.png', 'r');
	    if($content !== false){
		echo $content;
		return;
	    }
	}
	echo 'Error';
    }
    
    /**
     * Saving edited user information
     */
    public function saveAction()
    {
        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $pwd    = null;

        $user = Mage::getModel('admin/user')->load($userId);

        $user->setId($userId)
            ->setUsername($this->getRequest()->getParam('username', false))
            ->setFirstname($this->getRequest()->getParam('firstname', false))
            ->setLastname($this->getRequest()->getParam('lastname', false))
            ->setEmail(strtolower($this->getRequest()->getParam('email', false)));
        if ( $this->getRequest()->getParam('new_password', false) ) {
            $user->setNewPassword($this->getRequest()->getParam('new_password', false));
        }

        if ($this->getRequest()->getParam('password_confirmation', false)) {
            $user->setPasswordConfirmation($this->getRequest()->getParam('password_confirmation', false));
        }

        $result = $user->validate();
        if (is_array($result)) {
            foreach($result as $error) {
                Mage::getSingleton('adminhtml/session')->addError($error);
            }
            $this->getResponse()->setRedirect($this->getUrl("*/*/"));
            return;
        }

        /* TOTP save */
        if($user->getData('TOTP_seed')){//user already had TOTP activated
            if(!$this->getRequest()->getParam('enable_TOTP', false)){
                $user->setData('TOTP_seed', null);
            }
        } else {//user did not have TOTP activated
            if($this->getRequest()->getParam('enable_TOTP', false)){
                $TOTP_seed=$this->getRequest()->getParam('new_TOTP_seed', false);
                if($TOTP_seed && Mage::helper('Hj_TOTP/TOTP')->verify_key($TOTP_seed, $this->getRequest()->getParam('new_TOTP_key', ''))){
                    $user->setData('TOTP_seed', Mage::helper('Hj_TOTP/Encryption')->encrypt($TOTP_seed));
                } else {
                    Mage::getSingleton('adminhtml/session')->addError('The OTP token is incorrect or expired. Please try again (the seed has been renewed).');
                }
            }
        }
        //Removing old QRcodes files
        $QRCodes_dir_path=Mage::helper('Hj_TOTP')->getQRCodesDir();
        $qrcodes_dir=opendir($QRCodes_dir_path);
        if($qrcodes_dir){
            while (false !== ($entry = readdir($qrcodes_dir))) {
                @unlink($QRCodes_dir_path.DS.$entry);
            }
        }
        //////////////


        try {
            $user->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The account has been saved.'));
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('An error occurred while saving account.'));
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
    }
}