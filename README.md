# Silverstripe SEO AI Module
This module extends [Silverstripe SEO](https://github.com/PlasticStudio/Silverstripe-SEO) to allow users to generate SEO tags with the OpenAI API.

## Installation
```sh 
composer require "plasticstudio/silverstripe-seo-ai" 
```

## Setting Up
After installing the module, configure the API and model / temperature settings via YAML.

Any model with text input / output on [OpenAI's Platform](https://platform.openai.com/docs/models) will work, GPT-4o Mini is the default (recommended).

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

## Usage

Currently this module generates tags for the fields "Meta Title" and "Meta Description".

To generate tags, navigate to the page you'd like to generate tags for and click "Generate SEO Tags" in the "Page SEO Settings" section.

![Zoomed In Screenshot](docs/images/zoomed-in-screenshot.png)

[Zoomed Out Screenshot](docs/images/zoomed-out-screenshot.png)

Once this process has completed, publish the page as you would normally. 