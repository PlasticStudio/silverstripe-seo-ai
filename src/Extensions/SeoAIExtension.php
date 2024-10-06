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

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.Main', CheckboxField::create('GenerateTags', 'Re-generate meta tags')
            ->setDescription('Check this box before publishing to re-generate the meta-tags. This may take several seconds.'), 'MetaTitle');
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        $doGenerateTags = $this->owner->GenerateTags;

        // If re-generate tags has been selected OR it's the first publish for this page
        if ($doGenerateTags || !$this->owner->isPublished()) {
            // Do an OpenAI API call to generate meta tags
            $prompt = $this->generatePrompt();
            $response = $this->promptAPICall($prompt);
            $this->populateMetaTagsFromAPI($response);
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
            "temperature" => 0,
            "messages" => [
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ],
            "response_format" => [
                "type" => "json_schema",
                "json_schema" => [
                    "name" => "metadata",
                    "schema" => [
                        "type" => "object",
                        "properties" => [
                            "metaTitle" => ["type" => "string"],
                            "metaDescription" => ["type" => "string"]
                        ],
                        "required" => ["metaTitle", "metaDescription"],
                        "additionalProperties" => false
                    ],
                    "strict" => true
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
     * @return Boolean
     */
    public function populateMetaTagsFromAPI($response)
    {
        $metaTags = json_decode($response, true);
        if ($metaTags) {
            if (!$this->owner->isPublished() == 0 && !isset($this->MetaTitle)){
                $this->owner->MetaTitle = $metaTags["metaTitle"] ?? '';
            }

            if (!$this->owner->isPublished() == 0 && !isset($this->MetaTitle)) {
                $this->owner->MetaDescription = $metaTags["metaDescription"] ?? '';
            }

            $this->owner->GenerateTags = false;
            $this->owner->write();

            return true;
        }

        return false;
    }
}