<?php
class Hj_TOTP_Model_Resource_InvalidToken extends Mage_Core_Model_Resource_Db_Abstract {
    protected function _construct()
    {
        $this->_init('Hj_TOTP/invalidToken', 'id');
    }
    
    public function loadByUser($userId, $timeframe=null){
	$adapter = $this->_getReadAdapter();

        $select = $adapter->select()
                    ->from($this->getMainTable())
                    ->where('admin_user_id=:adminuserid');

        $binds = array(
            'adminuserid' => $userId
        );
	
	if($timeframe){
	    $select->where('timestamp>=:timelimit');
	    $binds['timelimit']=time()-$timeframe;
	}

        return $adapter->fetchAll($select, $binds);
    }
    
    public static function clean($timeframe=30){
	//@TODO : a function to clean up old invalid tokens
    }
}
?>
