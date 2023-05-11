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
