<?php
class Hj_TOTP_Block_Rewrite_Adminhtml_System_Account_Edit_Form extends Mage_Adminhtml_Block_System_Account_Edit_Form {
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $user = Mage::getModel('admin/user')
            ->load($userId);
        $user->unsetData('password');

        $fieldset = $this->getForm()->addFieldset('TOTP_fieldset', array('legend'=>Mage::helper('adminhtml')->__('OTP Information')));
        $enable_field=$fieldset->addField('enable_TOTP', 'select', array(
                'name'  => 'enable_TOTP',
                'label' => Mage::helper('adminhtml')->__('Enable OTP :'),
                'title' => Mage::helper('adminhtml')->__('Enable OTP'),
                'options' => array(0=>'No', 1=>'Yes'),
                'value' => $user->getData('TOTP_seed') ? 1 : 0
            )
        );
        if(!$user->getData('TOTP_seed')){

            $new_TOTP_seed=Mage::helper('Hj_TOTP/TOTP')->generate_secret_key();

            require_once(Mage::getModuleDir(null,'Hj_TOTP').DS.'lib'.DS.'phpqrcode'.DS.'qrlib.php');//require the QRcode library

            $new_TOTP_seed_dir_path=Mage::helper('Hj_TOTP')->getQRCodesDir();
            if(!is_dir($new_TOTP_seed_dir_path)){
                mkdir($new_TOTP_seed_dir_path, 0777, true);//create the QRcode media dir
            }

            $new_TOTP_seed_hash=hash('sha256', $new_TOTP_seed.openssl_random_pseudo_bytes(128));//we salt & hash the seed so it doesn't appear in clear in the QRcode URL

            $TOTP_id=str_replace('/', '_', str_replace(array('https://','http://'), '', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB))).'admin';

            QRcode::png('otpauth://totp/'.$TOTP_id.'?secret='.$new_TOTP_seed, $new_TOTP_seed_dir_path.DS.$new_TOTP_seed_hash.'.png', QR_ECLEVEL_H, 5);//creation of the QRcode png

            $new_TOTP_seed_field=$fieldset->addField('new_TOTP_seed', 'hidden', array(
                    'name'  => 'new_TOTP_seed',
                    'value' => $new_TOTP_seed
                )
            );

            $new_TOTP_seed_field->setAfterElementHtml($new_TOTP_seed_field->getAfterElementHtml().'<img src="'.$this->getUrl('*/*/totp_qrcode', array('id'=>$new_TOTP_seed_hash)).'" style="margin: auto; display: block;" /><p style="text-align:center;">OTP seed : <strong>'.$new_TOTP_seed.'</strong></p>');

            $new_TOTP_key_field=$fieldset->addField('new_TOTP_key', 'text', array(
                    'name'  => 'new_TOTP_key',
                    'label' => Mage::helper('adminhtml')->__('Scan the QRCode and enter the OTP token :'),
                    'title' => Mage::helper('adminhtml')->__('Scan the QRCode and enter the OTP token'),
                )
            );

            $this->setChild('form_after',$this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap($enable_field->getHtmlId(),$enable_field->getName())
                ->addFieldMap($new_TOTP_key_field->getHtmlId(),$new_TOTP_key_field->getName())
                ->addFieldMap($new_TOTP_seed_field->getHtmlId(),$new_TOTP_seed_field->getName())
                ->addFieldDependence($new_TOTP_key_field->getName(),$enable_field->getName(),1)
                ->addFieldDependence($new_TOTP_seed_field->getName(),$enable_field->getName(),1) );
        }
        return $this;
    }
}