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

AI Tool Bridge for PHP makes this straightforward. It allows you to define "tools" that the AI can use when it needs to talk to the outside world.

A "tool" in this context is any kind of external interaction that can be triggered by the AI, like making an API call to retrieve information from one of your backend systems.

This library

- helps you to define these tools so they can be used by the AI,
- provides a battle-tested prompt (that extends your own prompt) which ensures that the AI knows how and when to use these tools,
- and ensures that when the AI triggers a tool, it does so with complete and correctly formatted JSON.


### Example

Let's assume you have an ecommerce business and you want to provide an AI chat interface which allows to browse your product catalog. To do so, you've decided to integrate with OpenAI's GPT-4 model through the ChatGPT API.

You will probably prompt the AI assistant along the lines of "You are a friendly and helpful shopping assistant that informs the user about our product catalog..." and so on.

However, you cannot add your whole product catalog to the prompt. Thus, when your user asks the AI to "tell me about some kitchen helpers on offer", you need to identify that at this point in the conversation, the AI needs information from your ecommerce backend systems (e.g. by making a request to your Product Search API with query "kitchen helpers"), and you need to provide the resulting information back to the AI assistant, which can then summarize the product information for the user.

The AI knows best when it is time to retrieve these information from the external world. Because making your own code listen to the conversation and having it guess when it is time to make the Product Search API call is complex and error prone, and makes the idea of using a powerful AI a bit pointless. 




## Contributing

Contributions are welcome! To contribute, please familiarize yourself with [CONTRIBUTING.md](CONTRIBUTING.md).


## Coordinated Disclosure

Keeping user information safe and secure is a top priority, and we welcome the contribution of external security researchers. If you believe you've found a security issue in software that is maintained in this repository, please read [SECURITY.md](SECURITY.md) for instructions on submitting a vulnerability report.


## Copyright and License

AI Tool Bridge for PHP is copyright © [Manuel Kießling](https://manuel.kiessling.net) and licensed for use under the terms of the GNU General Public License (GPL-3.0-or-later) as published by the Free Software Foundation.

Please see [LICENSE](LICENSE) and [NOTICE](NOTICE) for more information.
