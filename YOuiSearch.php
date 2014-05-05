<?php

/**
 * Yii (1.1) Oui Search extension
 * 
 * This extension provide a Yii component to look up a Mac address vendor information.
 * Data is retrieved from http://www.macvendorlookup.com
 * 
 * @author Lorenzo Milesi <maxxer@yetopen.it>
 * @copyright 2014 YetOpen S.r.l.
 * @license GPLv3
 * @link http://www.macvendorlookup.com/mac-address-api
 * 
 */

Yii::import('ext.YOuiSearch.models.OuiCache');

class YOuiSearch extends CApplicationComponent
{
    /** @var OuiCache */
    private $_ouiCache = null;
    /** @var string Message to display in case the MAC is not found */
    public $msgIfNotAvailable = "n/a";
    
    /**
     * Public method to perform lookup
     * @param type $mac
     * @return \YOuiSearch|null
     */
    public function lookup($mac = "") {
        $this->_ouiCache = NULL;
        if (empty($mac))
            return NULL;
        $mac = $this->_normalize($mac);
        try {
            $this->_search($mac);
        } catch (CHttpException $e) {
            Yii::log(get_class()." Unable to lookup mac: ".$e->getMessage());
        }
        return $this;
    }

    /**
     * Check if cache table exists, and creates it otherwise
     */
    private function _checkTable()
    {
        $tableName = '{{ouiCache}}';
        if (!Yii::app()->db->schema->getTable($tableName)) {
            Yii::app()->db->createCommand()->createTable($tableName, array(
                'id' => 'pk',
                'base' => 'varchar(6) UNIQUE',
                'company' => 'varchar(255)',
                'address' => 'text',
                'country' => 'varchar(255)',
                'created_on' => 'datetime',
            ));
        }
    }

    /*
     * Normalize mac address to format aa-bb-cc-00-11-22 even if it comes in other format
     */
    private function _normalize ($m) {
        return strtolower(implode("-", (str_split(preg_replace("/[^a-f0-9]/i", '', $m), 2))));
    }

    /**
     * Searches the mac, from cache first, online if it fails
     * @param string $mac
     * @throws CHttpException
     */
    private function _search($mac) {
        $this->_checkTable();
        $oui = substr(str_replace("-","",$mac),0,6);
        $oc = OuiCache::model()->findByAttributes(array('base'=>$oui));
        if (is_null($oc)) {
            // Search online
            if (!function_exists("curl_init")) {
                throw new CHttpException("php-curl required", 500);
            }
            // http://www.macvendorlookup.com/mac-address-api
            $curl = curl_init("http://www.macvendorlookup.com/api/v2/".$mac);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $curl_response = curl_exec($curl);
            if ($curl_response === false) {
                throw new CHttpException("Unable to contact macvendorlookup.com", 500);
            }
            $data = CJSON::decode($curl_response);
            if (empty($data[0]['company']))
                throw new CHttpException("Empty data from macvendorlookup.com", 500);
            $oc = new OuiCache();
            $oc->base = $oui;
            $oc->company = substr($data[0]['company'], 0, 255);
            $oc->address = $data[0]['addressL1']." ".$data[0]['addressL2']." ".$data[0]['addressL3'];
            $oc->country = substr($data[0]['country'], 0, 255);
            $oc->created_on = date("Y-m-d H:i:s");
            $oc->save();
            curl_close($curl);
        }
        $this->_ouiCache = $oc;
    }

    public function __get($name)
    {
            // FIXME
        if ($this->_ouiCache) 
            return $this->_ouiCache->$name;
        else
            return $this->msgIfNotAvailable;
    }
    
    public function init()
    {
        parent::init();
    }
}
