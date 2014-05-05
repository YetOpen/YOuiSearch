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
        if (empty($mac))
            return NULL;
        $mac = $this->_normalize($mac);
        $oui = substr(str_replace("-","",$mac),0,6);
        $this->_search($mac);
        return $this->_ouiCache[$oui];
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
                Yii::trace("curl exec error", get_class());
                $oc = $this->_emptyRecord($oui);
            } else {
                $data = CJSON::decode($curl_response);
                $oc = new OuiCache();
                if (!empty($data[0]['company'])) {
                    $oc->base = $oui;
                    $oc->company = substr($data[0]['company'], 0, 255);
                    $oc->address = $data[0]['addressL1']." ".$data[0]['addressL2']." ".$data[0]['addressL3'];
                    $oc->country = substr($data[0]['country'], 0, 255);
                    $oc->created_on = date("Y-m-d H:i:s");
                    $oc->save();
                } else {
                    Yii::trace("empty response", get_class());
                    $oc = $this->_emptyRecord($oui);
                }
            }
            curl_close($curl);
        }
        $this->_ouiCache[$oui] = $oc;
    }

    public function init()
    {
        parent::init();
    }
    
    private function _emptyRecord ($oui) {
        $o = new OuiCache;
        foreach ($o->attributeNames() as $l)
            $o->$l = $this->msgIfNotAvailable;
        return $o;
    }
}
