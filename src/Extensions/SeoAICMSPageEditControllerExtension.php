<?php

namespace PlasticStudio\SEOAI\Extensions;

use SilverStripe\Core\Config\Config;
use voku\helper\HtmlDomParser;
use SilverStripe\Core\Extension;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\ORM\ValidationException;

class SeoAICMSPageEditControllerExtension extends Extension
{

    public $openaiKey = '';

    public $model = 'gpt-4o-mini';

    public $temperature = 0;

    public $included_dom_selectors = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li'];

    public $excluded_dom_selectors = ['header', 'footer', 'nav'];

    private static $allowed_actions = [
        'generateTags',
    ];

    public function generateTags()
    {
        if ($this->owner->currentPage()->stagesDiffer('Stage', 'Live')){
            throw new ValidationException('Publish the page first for accurate tag generation');
        }
        $prompt = $this->generatePrompt();
        $response = $this->promptAPICall($prompt);
        $this->populateMetaTagsFromAPI($response);


        $this->owner->redirectBack();
    }

    /**
     * Build an LLM prompt based on brand context and content field
     *
     * @return String
     */
    public function generatePrompt()
    {
        // Get the content for the current page
        $page = $this->owner->currentPage();
        $pageLink = $page->AbsoluteLink();

        // Get brand context
        $brandContext = SiteConfig::current_site_config()->ContextPrompt;

        // Strip the content of header, footer and nav elements
        $domParser = HtmlDomParser::str_get_html(file_get_contents($pageLink));

        $excludedDomElements = $this->excluded_dom_selectors;
        foreach ($excludedDomElements as $element) {
            foreach ($domParser->find($element) as $node) {
                if ($node) {
                    $node->outertext = '';
                }
            }
        }

        // Find all elements with content tags
        $domContent = [];

        $includedDomElements = $this->included_dom_selectors;
        foreach ($includedDomElements as $element) {
            foreach ($domParser->find($element) as $node) {
                if ($node) {
                    $domContent[] = strip_tags(html_entity_decode($node->innertext()));
                }
            }
        }

        // Remove empty items
        $parsedContent = array_filter($domContent);

        // Assemble parsed content
        $content = implode(' ', $parsedContent);

        // Create a prompt including the page content
        $prompt = <<<EOT
        Your task is to scan the following content gathered from a web page, and generate the following meta-tags which obey the character limits specified:
        - MetaTitle (60 character limit)
        - MetaDescription (160 character limit)

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
        $key = $this->openaiKey;
        $url = 'https://api.openai.com/v1/chat/completions';
        $data = [
            "model" => $this->model,
            "temperature" => $this->temperature,
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

            $page = $this->owner->currentPage();
            $page->MetaTitle = $metaTags["metaTitle"] ?? '';
            $page->MetaDescription = $metaTags["metaDescription"] ?? '';

            $page->GenerateTags = false;
            $page->write();

            return true;
        }

        return false;
    }
}
