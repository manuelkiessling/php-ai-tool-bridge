# AI Tool Bridge for PHP

## A PHP library that simplifies making AIs talk to your own APIs

Note: This product is independently developed and not affiliated, endorsed, or sponsored by OpenAI.

## Installation

Install this package as a dependency using [Composer](https://getcomposer.org).

``` bash
composer require manuelkiessling/ai-tool-bridge
```


## Getting started

### The problem

Integrating AIs like the OpenAI GPT-3 and GPT-4 models into your own projects using the ChatGPT API is quick and easy if all you want to do is exchange text messages with the AI.

Things get much more interesting if you make the AI interact with the rest of your application, e.g. by having it make API calls to retrieve information of trigger external actions.

However, things also get messy when trying to do so - you need to identify if and when the AI is not simply talking to the user, but wants to talk to your applications or APIs. And you need to ensure that when the AI talks to your systems via some structured language like JSON, it does so using the exact JSON structure you need.


### The solution

AI Tool Bridge for PHP makes this straightforward. It allows you to define "tool functions" that the AI can use when it needs to talk to the outside world.

A "tool" in this context is any kind of external interaction that can be triggered by the AI, like making an API call to retrieve information from one of your backend systems.

This library

- helps you to define these tools so they can be used by the AI,
- provides a battle-tested prompt (that extends your own prompt) which ensures that the AI knows how and when to use these tools,
- and ensures that when the AI triggers a tool, it does so with complete and correctly formatted JSON.

An important optimization is that this library does NOT use the AI assistant to create the actual JSON needed for using a tool function. Instead, it asks the AI only for the required values, and — based on a you need to provide JSON Schema — uses them to generate the full JSON structure itself. This way, the library ensures that the final JSON is always valid.

### Example

Let's assume you have an ecommerce business and you want to provide an AI chat interface which allows to browse your product catalog. To do so, you've decided to integrate with OpenAI's GPT-4 model through the ChatGPT API.

You will probably prompt the AI assistant along the lines of "You are a friendly and helpful shopping assistant that informs the user about our product catalog..." and so on.

However, you cannot add your whole product catalog to the prompt. Thus, when your user asks the AI to "tell me about some kitchen helpers on offer", you need to identify that at this point in the conversation, the AI needs information from your ecommerce backend systems (e.g. by making a request to your Product Search API with query "kitchen helpers"), you need to rerieve this information for the AI, and you need to provide the resulting information back to the AI assistant, which can then summarize the product information for the user.

The AI knows best when it is time to retrieve these information from the external world. Because making your own code listen to the conversation and having it guess when it is time to make the Product Search API call is complex and error prone, and makes the idea of using a powerful AI a bit pointless. 

The best approach is to make the AI recognize that the time has come to talk to the outside world, and have it do so in a structured and unmistakable way.

The solution is to teach the AI, within the initial system prompt, that it has one or more tools at its disposal which it can use at will.

This is done by first writing a so-called tool function definition, like this:

```php
<?php

declare(strict_types=1);

namespace ManuelKiessling\AiToolBridge\Example;

use ManuelKiessling\AiToolBridge\ToolFunctionCallResult;
use ManuelKiessling\AiToolBridge\ToolFunctionCallResultStatus;
use ManuelKiessling\AiToolBridge\ToolFunction;

class MyProductSearchToolFunction implements ToolFunction
{
    public function getName(): string
    {
        return 'productSearch';
    }

    public function getDescription(): string
    {
        return 'allows to search the product catalogue and retrieve information about products';
    }

    public function getInputJsonSchema(): string
    {
        return <<<'JSON'
{
  "type": "object",
  "properties": {
    "searchterms": {
      "type": "string"
    }
  },
  "required": [
    "searchterms"
  ]
}
JSON;
    }

    public function invoke(string $json): ToolFunctionCallResult
    {
        // we will talk about this in a minute
        return new ToolFunctionCallResult(
            $this,
            ToolFunctionCallResultStatus::SUCCESS,
            '',
            []
        );
    }
}
```

Make sure that the name, the description, and the object keys in the JSON schema are useful and descriptive - this helps the AI to understand when and how to use this tool function.

You can define multiple tool function definitions - for example, another tool function could be added which enables the AI to put products into the checkout basket when the user asks for it. We will keep this example simple, though.

Next, you need to integrate the tool with your existing AI setup. This is done using the `AiToolBridge` helper:

```php
<?php

declare(strict_types=1);

namespace ManuelKiessling\AiToolBridge\Example;

use ManuelKiessling\AiToolBridge\AiToolBridge;

class Example
{
    public function setup(): void
    {
        $myAiService = new MyAiService();

        $myProductSearchToolFunction = new MyProductSearchToolFunction();

        $aiToolBridge = new AiToolBridge(
            new $myAiService,
            [$myProductSearchToolFunction],
        );

        $myAiService->setSystemPrompt(
            "You are a friendly and helpful shopping assistant that informs the user about our product catalog... {$aiToolBridge->getPrompt()}"
        );

        // Whatever code is needed to get the AI conversation going...
    }
}
```

This obviously makes several assumptions - the way you have integrated an AI service into your own project probably looks significantly different.

The integration points are always identical, though. Because this library needs to be able to talk to the AI assistant, you must provide an object that implements interface `AiAssistantMessenger`. See [src/Example/MyAiService.php]() for a bare-bones example.

You also need to attach the tool functions you've defined when creating the AiToolBridge object.

Next, in order to make the AI understand that it can use your tooling, you need to extend your own AI system prompt with the prompt generated by this library. To do so, use method `AiToolBridge::getPrompt` as shown above.

Your application and this library are now fully integrated and set up. One central piece is missing, though — the actual behaviour that should be triggered when the AI uses your tool function.

Let's look at method `invoke` of class `MyProductSearchToolFunction` again:

```php
public function invoke(string $json): ToolFunctionCallResult
{
    return new ToolFunctionCallResult(
        $this,
        ToolFunctionCallResultStatus::SUCCESS,
        '',
        []
    );
}
```






## Contributing

Contributions are welcome! To contribute, please familiarize yourself with [CONTRIBUTING.md](CONTRIBUTING.md).


## Coordinated Disclosure

Keeping user information safe and secure is a top priority, and we welcome the contribution of external security researchers. If you believe you've found a security issue in software that is maintained in this repository, please read [SECURITY.md](SECURITY.md) for instructions on submitting a vulnerability report.


## Copyright and License

AI Tool Bridge for PHP is copyright © [Manuel Kießling](https://manuel.kiessling.net) and licensed for use under the terms of the GNU General Public License (GPL-3.0-or-later) as published by the Free Software Foundation.

Please see [LICENSE](LICENSE) and [NOTICE](NOTICE) for more information.
