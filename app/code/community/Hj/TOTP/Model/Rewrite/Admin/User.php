<?php
class Hj_TOTP_Model_Rewrite_Admin_User extends Mage_Admin_Model_User {
    /**
     * Login user
     *
     * @param $username
     * @param   string $password
     * @param   string $TOTP
     * @return  Mage_Admin_Model_User
     */
    public function login($username, $password, $TOTP=null)
    {
        if ($this->authenticate($username, $password, $TOTP)) {
            $this->getResource()->recordLogin($this);
        }
        return $this;
    }

    /**
     * Authenticate user name, password, and eventual TOTP and save loaded record
     *
     * @param string $username
     * @param string $password
     * @param string $TOTP
     * @return boolean
     * @throws Mage_Core_Exception
     */
    public function authenticate($username, $password, $TOTP=null)
    {
        $config = Mage::getStoreConfigFlag('admin/security/use_case_sensitive_login');
        $result = false;

        try {
            Mage::dispatchEvent('admin_user_authenticate_before', array(
                'username' => $username,
                'user'     => $this
            ));
            $this->loadByUsername($username);
            if($this->getData('TOTP_seed')){//if we have a seed in stock, TOTP is required
                if($TOTP===null){
                    Mage::throwException(Mage::helper('Hj_TOTP')->__('OTP is required'));
                } else {
                    if(!Mage::helper('Hj_TOTP/TOTP')->verify_key(Mage::helper('Hj_TOTP')->decrypt($this->getData('TOTP_seed')), $TOTP)){
                        Mage::throwException(Mage::helper('adminhtml')->__('OTP invalid or expired.'));
                    }
                }
            }

            $sensitive = ($config) ? $username == $this->getUsername() : true;

            if ($sensitive && $this->getId() && Mage::helper('core')->validateHash($password, $this->getPassword())) {
                if ($this->getIsActive() != '1') {
                    Mage::throwException(Mage::helper('adminhtml')->__('This account is inactive.'));
                }
                if (!$this->hasAssigned2Role($this->getId())) {
                    Mage::throwException(Mage::helper('adminhtml')->__('Access denied.'));
                }
                $result = true;
            }

            Mage::dispatchEvent('admin_user_authenticate_after', array(
                'username' => $username,
                'password' => $password,
                'user'     => $this,
                'result'   => $result,
            ));
        }
        catch (Mage_Core_Exception $e) {
            $this->unsetData();
            throw $e;
        }

        if (!$result) {
            $this->unsetData();
        }
        return $result;
    }
}