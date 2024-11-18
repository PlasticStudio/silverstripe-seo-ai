<?php

namespace PlasticStudio\SEOAI\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\CompositeField;
use LeKoala\CmsActions\CmsInlineFormAction;

class SeoAIExtension extends Extension
{
    public function init(){
        Requirements::css('plasticstudio/silverstripe-seo-ai:client/css/generate-button.css');
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->insertAfter('SocialImage', CompositeField::create(CmsInlineFormAction::create('generateTags', 'Generate SEO Tags')
                ->addExtraClass('generate-seo-button')
            )
            ->setDescription('Generate SEO tags using AI. Tags are generated from published page data only to ensure accuracy.')
        );
    }
}