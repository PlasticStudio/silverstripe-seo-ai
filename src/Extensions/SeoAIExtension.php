<?php

namespace PlasticStudio\SEOAI\Extensions;

use voku\helper\HtmlDomParser;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\SiteConfig\SiteConfig;

class SeoAIExtension extends DataExtension
{
    private static $db = [
        'GenerateTags' => 'Boolean(false)',
    ];

    public function updateSettingsFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.MetaTags', CheckboxField::create('GenerateTags', 'Re-generate meta tags')->setDescription('Check this box before publishing to re-generate the meta-tags. This may take several seconds.'), 'MetaTitle');
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        $doGenerateTags = $this->owner->GenerateTags;

        // If re-generate tags has been selected OR it's the first publish for this page
        if ($doGenerateTags || $this->owner->ID == 0) {
            // Do an OpenAI API call to generate meta tags
            $prompt = $this->generatePrompt();
            $response = $this->promptAPICall($prompt);
            $metaTags = json_decode($response, true); // Decode the response which is in JSON format
            $this->populateMetaTags($metaTags);

            // Reset generate tags field
            $this->owner->GenerateTags = false;
            $this->owner->write();
        }
    }

    /**
     * Build an LLM prompt based on brand context and content field
     *
     * @return String
     */
    public function generatePrompt()
    {
        // Get the content for the current page
        $pageLink = $this->owner->AbsoluteLink();

        // Get brand context
        $brandContext = SiteConfig::current_site_config()->ContextPrompt;

        // Strip the content of header, footer and nav elements
        $domParser = HtmlDomParser::str_get_html(file_get_contents($pageLink));

        foreach ($domParser->find('header,footer,nav') as $node) {
            $node->outertext = '';
        }

        // Find all elements with content tags
        $domContent = [];

        foreach ($domParser->find('p,h1,h2,h3,h4,h5') as $item) {
            $domContent[] = strip_tags(html_entity_decode($item->innertext()));
        }

        // Remove empty items
        $parsedContent = array_filter($domContent);

        // Assemble parsed content
        $content = implode(' ', $parsedContent);

        // Create a prompt including the page content
        $prompt = <<<EOT
        Your task is to scan the following content gathered from a web page, and generate the following meta-tags for it:
        - MetaTitle
        - MetaDescription
            
        You'll provide the response in JSON format every time, here's an example:
        {
            "metaTitle": "Meta Title",
            "metaDescription": "This is an example of the meta description."
        }

        Here is some background information on the brand which the web page belongs to, delimited by ---:

        ---
        $brandContext
        ---

        Here is the content for you to generate meta-tags for, deliniated by ~:

        ~
        $content
        ~

        EOT;

        return $prompt;
    }

    /**
     * Call an LLM API with generated prompt
     *
     * @param String
     *
     * @return String
     */
    public function promptAPICall($prompt)
    {
        $key = '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';
        $url = 'https://api.openai.com/v1/chat/completions';
        $data = [
            "model" => "gpt-4o-mini",
            "messages" => [
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ]
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $key,
            "Content-length: " . strlen(json_encode($data))
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        curl_close($ch);

        $data = json_decode($response, true);

        return $data["choices"][0]["message"]["content"];
    }

    /**
     * Populate the page's meta tags with AI generated content
     * @param Array
     * 
     * @return Void
     */
    public function populateMetaTags($metaTags)
    {
        $page = $this->owner;

        $page->MetaTitle = $metaTags["metaTitle"];
        $page->MetaDescription = $metaTags["metaDescription"];

        return;
    }
}