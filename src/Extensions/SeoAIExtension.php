<?php

namespace PlasticStudio\SEOAI\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\ORM\DataExtension;

class SeoAIExtension extends DataExtension
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
