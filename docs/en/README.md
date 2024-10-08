# Silverstripe SEO AI Module

## Installation
``` composer require "plasticstudio/silverstripe-seo-ai" ```

## Setting Up
After installing the module, configure the API and model / temperature settings via YAML.

```
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