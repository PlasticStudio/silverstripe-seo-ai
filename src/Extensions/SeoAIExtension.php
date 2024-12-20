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
        $fields->insertBefore('MetaTitle', CompositeField::create(CmsInlineFormAction::create('generateTags', 'Generate SEO Tags')
                ->addExtraClass('generate-seo-button')
            )
            ->setDescription('NOTE: Publish the page before generating. The results are generated from the published page to ensure accuracy.')
        );
    }
}