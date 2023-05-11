<?php

declare(strict_types=1);

namespace ManuelKiessling\AiToolBridge\Example;

use ManuelKiessling\AiToolBridge\AiToolBridge;

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
