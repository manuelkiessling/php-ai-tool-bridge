<?php

declare(strict_types=1);

namespace ManuelKiessling\Test\AiToolBridge;

use Exception;
use ManuelKiessling\AiToolBridge\AiToolBridge;
use PHPUnit\Framework\TestCase;

class AiToolBridgeTest extends TestCase
{
    public function testContainsToolQuery(): void
    {
        $functionDefinition = new DemoToolFunction();
        $bridge = new AiToolBridge(
            new DemoAiAssistantMessenger(),
            [$functionDefinition],
        );

        $this->assertFalse(
            $bridge->containsFunctionCall('Foo'),
        );

        $this->assertFalse(
            $bridge->containsFunctionCall('|CallToolBridgeFunction|Foo|'),
        );

        $this->assertTrue(
            $bridge->containsFunctionCall('|CallToolBridgeFunction|createUser|'),
        );

        $this->assertTrue(
            $bridge->containsFunctionCall('I want to call |CallToolBridgeFunction|createUser| please'),
        );
    }

    public function testGetFunctionDefinition(): void
    {
        $functionDefinition = new DemoToolFunction();
        $bridge = new AiToolBridge(
            new DemoAiAssistantMessenger(),
            [$functionDefinition],
        );

        $this->assertSame(
            'createUser',
            $bridge->getFunctionDefinition('|CallToolBridgeFunction|createUser|')->getName()
        );
    }

    /**
     * @throws Exception
     */
    public function testGetPrompt(): void
    {
        $functionDefinition = new DemoToolFunction();
        $bridge = new AiToolBridge(
            new DemoAiAssistantMessenger(),
            [$functionDefinition],
        );

        $this->assertSame(
            <<<'PROMPT'
When you found out what I want to do and you have gathered all information from me
to put my current intention into action,
you can make me use an external tool called 'AiToolBridge' for you.
The tool has the following functions:

Function 'createUser': creates a new user account.
When using function 'createUser' for you, I will need to call it with the following data:

name (of type STRING)
age (of type INTEGER)
interests (of type ARRAY)
hobbies (of type ARRAY)

Whenever you want to use one of the tool functions,
you need to simply write a single message starting with '|CallToolBridgeFunction|' followed by the function name,
like this: |CallToolBridgeFunction|createUser|.
This message must not contain any other text besides the |CallToolBridgeFunction| marker
followed by the exact tool function name and the final | character.

When you write such a tool-function-usage message,
I will follow up with questions regarding the values which you want me to use when using the tool function.
When I ask you to provide a value, you must answer only with the value I ask for,
and nothing else! We will talk about values step-by-step, until I have all the values I need to use the tool for you.

A value question from me might look like this:

Value for parameter 'name' (of type STRING):

Once I have gathered all parameter values this way,
I will then call the tool function accordingly, and then provide to you the result of running the tool function,
presenting the result data as a JSON object with top-level fields 'success' (of type boolean), 'message' (of type string),
and 'data', with 'success' being true if the tool function call was successful, 'message' providing general information about the
tool function call, and 'data' providing detailed information that you can extract and use in the further course of our dialogue.
Example:

|CallToolBridgeFunction|createUser|Result|:
{
  "success": true,
  "message": "Lorem ipsum dolor sit amet, consetetur sadipscing elitr...",
  "data": {
    "foo": "bar",
    "something": true,
    "baz": 1
  }
}

PROMPT,
            $bridge->getPrompt(),
        );
    }

    public function testDialog(): void
    {
        $functionDefinition = new DemoToolFunction();
        $bridge = new AiToolBridge(
            new DemoAiAssistantMessenger(),
            [$functionDefinition],
        );

        $result = $bridge->handleAssistantMessage('|CallToolBridgeFunction|createUser|');

        $this->assertSame(
            'J. Doe',
            $result->data['json']['name'],
        );

        $this->assertSame(
            26,
            $result->data['json']['age'],
        );


        $this->assertSame(2, sizeof($result->data['json']['interests']));

        $this->assertSame(
            'Painting',
            $result->data['json']['interests'][0],
        );

        $this->assertSame(
            'Horse Riding',
            $result->data['json']['interests'][1],
        );


        $this->assertSame(2, sizeof($result->data['json']['hobbies']));

        $this->assertSame(
            'Swimming',
            $result->data['json']['hobbies'][0]['name'],
        );

        $this->assertTrue(
            $result->data['json']['hobbies'][0]['active'],
        );

        $this->assertSame(
            'Cooking',
            $result->data['json']['hobbies'][1]['name'],
        );

        $this->assertFalse(
            $result->data['json']['hobbies'][1]['active'],
        );
    }
}
