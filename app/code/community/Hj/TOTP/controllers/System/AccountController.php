<?php
require_once(Mage::getModuleDir('controllers','Mage_Adminhtml').DS.'System'.DS.'AccountController.php');
class Hj_TOTP_System_AccountController extends Mage_Adminhtml_System_AccountController {
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
                    $user->setData('TOTP_seed', $TOTP_seed);
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