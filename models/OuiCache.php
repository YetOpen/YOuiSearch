<?php

/**
 * This is the model class for table "ouiCache".
 *
 * The followings are the available columns in table 'ouiCache':
 * @property integer $id
 * @property string $base
 * @property string $company
 * @property string $address
 * @property string $country
 * @property string $created_on
 */
class OuiCache extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{ouiCache}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('base', 'length', 'max' => 6),
        );
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return OuiCache the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
