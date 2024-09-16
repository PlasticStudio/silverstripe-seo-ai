<?php

namespace PlasticStudio\SEOAI\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\TextareaField;

class SeoAISiteConfigExtension extends DataExtension
{
    private static $db = [
        'ContextPrompt' => 'Varchar(255)',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.SEO', TextareaField::create('ContextPrompt', 'Brand Context Prompt')->setDescription('Additional information to give AI about your brand / content for more accurate metadata generation'), 'UseTitleAsMetaTitle');
    }
}