YOuiSearch
==========

Mac Address information lookup extension for Yii
Data is retrieved from http://www.macvendorlookup.com

Installation
------------
Copy the source to your protected/extensions/YOuiSearch
Edit your config file and add:
```
return array(
    ...
    'components'=>array(
        ...
        'YOuiCache' => array(
            'class' => 'ext.YOuiSearch.YOuiSearch',
            'msgIfNotAvailable => 'not found', // Optional text to display in case mac is not found, default to "n/a"
        ),
        ...
    )
    ...
```

Usage
-----
```
echo Yii::app()->YOuiCache->lookup($yourMac)->company;
```
Available methods: company, address, country. 

Requirements
------------
* php5-curl
* database to store cached records

Links
-----
[YetOpen](http://www.yetopen.it)

[YetOpen's gitHub](https://github.com/YetOpen/)
