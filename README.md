# AI Tool Bridge for PHP

## A PHP library that simplifies making AIs talk to your own APIs

Note: This product is independently developed and not affiliated, endorsed, or sponsored by OpenAI.

![](https://manuel.kiessling.net/php-ai-tool-bridge/PHP_AI_Tool_Bridge_-_Demo_Video.gif)

## Installation

Install this package as a dependency using [Composer](https://getcomposer.org).

``` bash
composer require manuelkiessling/ai-tool-bridge
```


## Overview

The major challenge when integrating AI into any project is managing interactions between the AI and the rest of your application. This becomes especially complex when the AI needs to make API calls to retrieve information or trigger actions.

AI Tool Bridge for PHP elegantly solves this problem by offering a straightforward interface to define "tool functions" that the AI can utilize when it needs to interact with external systems.

An important optimization is the library's capability to generate the required JSON structure for a tool function. It does so by requesting only the required values from the AI and then generating the JSON based on a provided JSON schema. This approach guarantees the validity of the final JSON that reaches your application code.


Key features of this library include:

- Facilitating the definition of tools that the AI can use for external interactions.
- Providing a robust prompt structure to guide the AI on when and how to use these tools.
- Ensuring that the tools are triggered with complete and correctly formatted JSON.


## Example

Let's assume you have an ecommerce business and you want to provide an AI chat interface which allows to browse your product catalog. To do so, you've decided to integrate with OpenAI's GPT-4 model through the ChatGPT API.

You will probably prompt the AI assistant along the lines of "You are a friendly and helpful shopping assistant that informs the user about our product catalog..." and so on.

However, you cannot add your whole product catalog to the prompt. Thus, when your user asks the AI to "tell me about some kitchen helpers on offer", you need to identify that at this point in the conversation, the AI needs information from your ecommerce backend systems (e.g. by making a request to your Product Search API with query "kitchen helpers"), you need to retrieve this information for the AI, and you need to provide the resulting information back to the AI assistant, which can then summarize the product information for the user.

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

Next, you need to integrate the tool bridge with your existing AI setup. This is done using the `AiToolBridge` helper:

```php
class Example
{
    private AiToolBridge $aiToolBridge;
    private MyAiService $myAiService;

    public function __construct()
    {
        $this->myAiService = new MyAiService();

        $myProductSearchToolFunction = new MyProductSearchToolFunction();

        $this->aiToolBridge = new AiToolBridge(
            new $this->myAiService,
            [$myProductSearchToolFunction],
        );

        $this->myAiService->setSystemPrompt(
            "You are a friendly and helpful shopping assistant that informs the user about our product catalog...
             {$this->aiToolBridge->getPrompt()}"
        );
    }
}
```

This obviously makes several assumptions - the way your application is structured and the way you have integrated an AI service could be wildly different.

The integration points are always identical, though. Because this library needs to be able to talk to the AI assistant, you must provide an object that implements interface `AiAssistantMessenger`. See [src/Example/MyAiService.php]() for a bare-bones example.

You also need to attach the tool functions you've defined when creating the AiToolBridge object.

Next, in order to make the AI understand that it can use your tooling, you need to extend your own AI "system" prompt with the prompt generated by this library. To do so, use method `AiToolBridge::getPrompt` as shown above.

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

This is where the magic needs to happen — that is, this is the place to implement all the logic — YOUR logic — needed to do the actual product search.

Here, you do not need to consider the AI integration in any way — this is exactly the point of this library, to do the heavy lifting for you.

More precisely, when the `invoke` method is called, the heavy lifting has already been done — you can rest assured that the method will be called with a JSON string that on the one hand has the precise structure you have defined with the JSON Schema in method `MyProductSearchToolFunction::getInputJsonSchema`, and on the other hand is filled with the values that the AI considered useful when using the tool function.

So if, for example, the AI—User conversation went like this:

    AI: Hello, how can I help you?

    User: Tell me about some kitchen helpers on offer.

then the AI will have recognized that it should use the tool function `productSearch` to search for `kitchen helpers`, which eventually results in a call to `MyProductSearchToolFunction::invoke` with the following JSON string:

```json
{
    "searchterms": "kitchen helpers"
}
```

You have complete freedom with regards to how you implement this method (as long as you return a `ToolFunctionCallResult` object). In our example, it obviously makes sense to actually do a product search, but *how* you do this is completely up to you. Query a database, talk to an API, or anything else that retrieves production information about "kitchen helpers".

The two fields of interest on the `ToolFunctionCallResult` object that you need to return are the `message` and the `data`. In our example, that could look like this: 

```php
public function invoke(string $json): ToolFunctionCallResult
{
    $jsonAsArray = json_decode($json, true);
    
    // use $jsonAsArray['searchterm'] when talking to a DB or an API...
    
    return new ToolFunctionCallResult(
        $this,
        ToolFunctionCallResultStatus::SUCCESS,
        'Found 2 matching products',
        [
            [
                'id' => 84,
                'name' => 'Kawaii Chick Egg Separator',
                'price' => 14.99,
                'description' => 'Whether you’re a beginner baker or an experienced cook, the Kawaii Chick Egg Separator is a must-have kitchen tool that will make separating eggs a breeze.'
            ],
            [
                'id' => 2389,
                'name' => 'BlendJet 2',
                'price' => 49.99,
                'description' => 'Imagine the freedom of being able to go anywhere and blend your favorite smoothies, shakes, margaritas, frappés, or baby food without the limitations of a regular blender.'
            ]
        ]
    );
}
```

The `data` format is not limited to any specific schema.

Let's look at the final piece, and return to our `Example` class. We assume that in your implementation, there is a method `handleAssistantMessage` that is invoked whenever your application retrieved a new AI assistant message — again, this is a very specific implementation detail of your application.

This is the place — BEFORE we send the message to the user! — where we need to "hook" into the conversation. This allows the tool library to detect any tool function request from the AI, and handle it accordingly.

If a tool function was invoked and was successful, we need to feed the result back to the AI assistant — this way, it learns about the products that matched its product search:

```php
<?php

declare(strict_types=1);

class Example
{

    // ...

    public function handleAssistantMessage(string $message): void
    {
        $toolFunctionCallResult = $this->aiToolBridge->handleAssistantMessage($message);
    
        if (is_null($toolFunctionCallResult)) {
            // The AI didn't use a tool function, thus its message is meant for the user
            $this->sendAssistantMessageToUser($message);
        } else {
            // The AI used a tool function, we now need to send the result to the AI
            $dataAsJson = json_encode($toolFunctionCallResult->data);
            $this->sendUserMessageToAssistant($toolFunctionCallResult->message . ' ' . $dataAsJson);
        }
    }
    
    public function sendAssistantMessageToUser(string $message): void
    {
        // whatever code is needed to show an AI assistant message to the user
    }
    
    public function sendUserMessageToAssistant(string $message): void
    {
        // whatever code is needed to send a message to the AI assistant
    }
}
```

As you can see, when we inform the AI assistant about the roll function result, we act as the user telling the assistant about it. This is because from the AI assistant's perspective, it IS the user that provides the tool to the AI in the first place! See method [src/AiToolBridge.php](`AiToolBridge::getPrompt`) to understand why this is the case.


## Contributing

Contributions are welcome! To contribute, please familiarize yourself with [CONTRIBUTING.md](CONTRIBUTING.md).


## Coordinated Disclosure

Keeping user information safe and secure is a top priority, and we welcome the contribution of external security researchers. If you believe you've found a security issue in software that is maintained in this repository, please read [SECURITY.md](SECURITY.md) for instructions on submitting a vulnerability report.


## Copyright and License

AI Tool Bridge for PHP is copyright © [Manuel Kießling](https://manuel.kiessling.net) and licensed for use under the terms of the GNU General Public License (GPL-3.0-or-later) as published by the Free Software Foundation.

Please see [LICENSE](LICENSE) and [NOTICE](NOTICE) for more information.
