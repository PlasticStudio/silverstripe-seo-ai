<?php

namespace PlasticStudio\SEOAI\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\CompositeField;
use LeKoala\CmsActions\CmsInlineFormAction;

class SeoAIExtension extends DataExtension
{
    public function updateCMSFields(FieldList $fields)
    {
        $fields->insertAfter('SocialImage', CompositeField::create(
                CmsInlineFormAction::create('generateTags', 'Generate SEO Tags')
            )->setDescription('Generate SEO tags using AI (this may take several seconds)')
        );
    }
}