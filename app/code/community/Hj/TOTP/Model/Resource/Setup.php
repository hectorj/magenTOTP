<?php
class Hj_TOTP_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup{
    /**
     * Determine if updates should be applied in the current context
     * @return bool
     */
    protected function allowUpdates(){
	//We don't wanna make updates on a customer request or an Ajax request
	return Mage::getSingleton('admin/session')->isLoggedIn() && !Mage::app()->getRequest()->isXmlHttpRequest();
    }
    
    /**
     * Apply module resource install, upgrade and data scripts
     *
     * @return Mage_Core_Model_Resource_Setup
     */
    public function applyUpdates()
    {
	if($this->allowUpdates()){
	    return parent::applyUpdates();
	} else {
	    return $this;
	}
    }
    
    /**
     * Apply data updates to the system after upgrading.
     *
     * @param string $fromVersion
     * @return Mage_Core_Model_Resource_Setup
     */
    public function applyDataUpdates()
    {
	if($this->allowUpdates()){
	    return parent::applyDataUpdates();
	} else {
	    return $this;
	}
    }
    
    /**
     * Run resource installation file
     *
     * @param string $newVersion
     * @return Mage_Core_Model_Resource_Setup
     */
    protected function _installResourceDb($newVersion)
    {
        $oldVersion = $this->_modifyResourceDb(self::TYPE_DB_INSTALL, '', $newVersion);
        $appliedNewVersion=$this->_modifyResourceDb(self::TYPE_DB_UPGRADE, $oldVersion, $newVersion);
	//Zend_Debug::dump($oldVersion, 'oldVersion');
	//Zend_Debug::dump($newVersion, 'newVersion');
	//Zend_Debug::dump($appliedNewVersion, 'appliedNewVersion');
	if($appliedNewVersion!==false){
	    debug_print_backtrace();
	    $this->_getResource()->setDbVersion($this->_resourceName, $appliedNewVersion);
	}

        return $this;
    }

    /**
     * Run resource upgrade files from $oldVersion to $newVersion
     *
     * @param string $oldVersion
     * @param string $newVersion
     * @return Mage_Core_Model_Resource_Setup
     */
    protected function _upgradeResourceDb($oldVersion, $newVersion)
    {
        $appliedNewVersion=$this->_modifyResourceDb(self::TYPE_DB_UPGRADE, $oldVersion, $newVersion);
	
	if($appliedNewVersion!==false){
	    debug_print_backtrace();
	    $this->_getResource()->setDbVersion($this->_resourceName, $appliedNewVersion);
	}
	
        return $this;
    }
    
    /**
     * Run data install scripts
     *
     * @param string $newVersion
     * @return Mage_Core_Model_Resource_Setup
     */
    protected function _installData($newVersion)
    {
        $oldVersion = $this->_modifyResourceDb(self::TYPE_DATA_INSTALL, '', $newVersion);
        $appliedNewVersion=$this->_modifyResourceDb(self::TYPE_DATA_UPGRADE, $oldVersion, $newVersion);
	if($appliedNewVersion!==false){
	    $this->_getResource()->setDataVersion($this->_resourceName, $newVersion);
	}

        return $this;
    }

    /**
     * Run data upgrade scripts
     *
     * @param string $oldVersion
     * @param string $newVersion
     * @return Mage_Core_Model_Resource_Setup
     */
    protected function _upgradeData($oldVersion, $newVersion)
    {
        $appliedNewVersion=$this->_modifyResourceDb('data-upgrade', $oldVersion, $newVersion);
	if($appliedNewVersion!==false){
	    $this->_getResource()->setDataVersion($this->_resourceName, $newVersion);
	}

        return $this;
    }
    
    protected function _modifyResourceDb($actionType, $fromVersion, $toVersion) {
	ob_start();//start bufferisation, to prevent the print_r from parent to get displayed
	$return=false;
	try{
	    $this->getConnection()->beginTransaction();
	    $return=parent::_modifyResourceDb($actionType, $fromVersion, $toVersion);
	    $this->getConnection()->commit();
	} catch(Exception $e){
	    $this->getConnection()->rollback();
	    Mage::log('Installation error : '.$e->getMessage(), Zend_Log::ERR, Mage::helper('Hj_TOTP')->getLogFileName(), true);//log the error
	    
	    //if(Mage::getSingleton('admin/session')->isLoggedIn() && !Mage::app()->getRequest()->isXmlHttpRequest()){
	    Mage::getSingleton('core/session')->addError('Hj_TOTP installation error. See log file for more information.');//display error
	    //}
	}
	$output=ob_get_clean();
	if($output && !empty($output)){
	    Mage::log('Installation output : '.$output, Zend_Log::DEBUG, Mage::helper('Hj_TOTP')->getLogFileName());//dump the output in the log
	}
	return $return;
    }
}
