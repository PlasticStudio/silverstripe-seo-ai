<?php

namespace PlasticStudio\SEOAI\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CheckboxField;
use PlasticStudio\SEO\Model\Extension\SeoPageExtension;

class SeoAIExtension extends SeoPageExtension
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
