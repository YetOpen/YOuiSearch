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

Links
-----
[YetOpen](http://www.yetopen.it)

[YetOpen's gitHub](https://github.com/YetOpen/)