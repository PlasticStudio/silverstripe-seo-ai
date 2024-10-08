# Silverstripe SEO AI Module
This module extends [Silverstripe SEO](https://github.com/PlasticStudio/Silverstripe-SEO) to allow users to generate SEO tags with the OpenAI API.

## Installation
```sh 
composer require "plasticstudio/silverstripe-seo-ai" 
```

## Setting Up
After installing the module, configure the API and model / temperature settings via YAML.

*app/_config/seo-ai.yml*
```yaml
---
Name: silverstripe-seo-ai
---
SilverStripe\Core\Injector\Injector:
  PlasticStudio\SEOAI\Extensions\SeoAICMSPageEditControllerExtension:
    properties:
      openaiKey: "`OPENAI_API_KEY`"
      model: "gpt-4o-mini"
      temperature: 0
```