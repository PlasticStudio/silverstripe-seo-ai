<?php

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CheckboxField;
use PlasticStudio\SEO\Model\Extension\SeoExtension;

class AISeoExtension extends SeoExtension
{
    private static $db = [
        'Test' => 'Boolean',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields = parent::updateCMSFields($fields);

        $fields->addFieldToTab('Root.SEO', CheckboxField::create('Test', 'Test'));

        return $fields;
    }
}
