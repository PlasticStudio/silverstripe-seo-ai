<?php

namespace PlasticStudio\SEOAI\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use LeKoala\CmsActions\CmsInlineFormAction;

class SeoAIExtension extends DataExtension
{
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.Main', CmsInlineFormAction::create('generateTags', 'Generate SEO Tags'));
    }
}