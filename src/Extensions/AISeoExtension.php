<?php

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CheckboxField;
use PlasticStudio\SEO\Model\Extension\SeoPageExtension;

class AISeoExtension extends SeoPageExtension
{
    private static $db = [
        'Test' => 'Boolean',
    ];

    public function updateSettingsFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.MetaTags', CheckboxField::create('Test', 'Test'));

        return $fields;
    }
}
