<?php

use Airline\AirlineFood;
use PHPUnit\Framework\TestCase;

class AirlineFoodTest extends TestCase
{

    /** @test */
    public function itShouldMatchAYouEverNotice()
    {
        $af = new AirlineFood();
        $command = "You ever notice the weather?";
        $this->assertTrue($af->interpret($command));
        $debugInfo = $af->debug();
        $this->assertTrue($debugInfo['stack'][0]['name'] == "the weather");
    }

    /** @test */
    public function itShouldDealWithWhatsTheDealWiths()
    {
        $af = new AirlineFood();
        $command = "What's the deal with anime characters?";
        $this->assertTrue($af->interpret($command));
        $debugInfo = $af->debug();
        $this->assertTrue($debugInfo['stack'][0]['name'] == "anime characters");
        $this->assertTrue($debugInfo['sp'] == 0);
    }

    /** @test */
    public function itShouldNotMoveTheSPForwardWithA_youEverNotice()
    {
        $af = new AirlineFood();
        $debugInfo = $af->debug();

        $this->assertTrue($debugInfo['sp'] == AirlineFood::EMPTY_STACK);
        $command = "What's the deal with anime characters?";
        $this->assertTrue($af->interpret($command));
        $debugInfo = $af->debug();
        $this->assertTrue($debugInfo['stack'][0]['name'] == "anime characters");
        $this->assertTrue($debugInfo['sp'] == 0);

        // Let's move the SP along by a couple of items
        $command = "What's the deal with second entry?";
        $this->assertTrue($af->interpret($command));
        $command = "What's the deal with third entry?";

        // We should now have three items on the stack AND the SP point at third entry
        $this->assertTrue($af->interpret($command));
        $debugInfo = $af->debug();
        $this->assertTrue(count($debugInfo['stack']) == 3);
        $this->assertTrue($debugInfo['sp'] == 2);
        $this->assertTrue(
          $debugInfo['stack'][$debugInfo['sp']]['name'] == 'third entry'
        );

        // Now if we add a new variable, with a "You ever notice ...?"
        // The stack will increase to 4, but the sp will still be at 2
        $command = "You ever notice no stack pointer?";
        $this->assertTrue($af->interpret($command));
        $debugInfo = $af->debug();
        $this->assertTrue(count($debugInfo['stack']) == 4);
        $this->assertTrue($debugInfo['sp'] == 2);
        $this->assertTrue(
          $debugInfo['stack'][$debugInfo['sp'] + 1]['name'] == 'no stack pointer'
        );
    }

    /** @test */
    public function itShouldUmDecrementTheSP()
    {
        $af = new AirlineFood();
        $debugInfo = $af->debug();

        $this->assertTrue($debugInfo['sp'] == AirlineFood::EMPTY_STACK);
        $command = "What's the deal with anime characters?";
        $this->assertTrue($af->interpret($command));
        $debugInfo = $af->debug();
        $this->assertTrue($debugInfo['stack'][0]['name'] == "anime characters");
        $this->assertTrue($debugInfo['sp'] == 0);

        // Decrement the stack with 0 should still have sp == 0
        $this->assertTrue($af->interpret("Um,"));
        $debugInfo = $af->debug();
        $this->assertTrue($debugInfo['sp'] == 0);

        // Let's move the stack and then decrement
        $af->interpret("What's the deal with inc1?");
        $af->interpret("What's the deal with inc2?");
        $debugInfo = $af->debug();
        $this->assertTrue($debugInfo['sp'] == 2);

        // Decrement the stack with 3 should still have sp == 1
        $this->assertTrue($af->interpret("Um,"));
        $debugInfo = $af->debug();
        $this->assertTrue($debugInfo['sp'] == 1);

    }
    /** @test */
    public function itShouldYeahIncrementTheSP()
    {
        $af = new AirlineFood();
        $debugInfo = $af->debug();

        $this->assertTrue($debugInfo['sp'] == AirlineFood::EMPTY_STACK);
        $command = "What's the deal with anime characters?";
        $this->assertTrue($af->interpret($command));
        $debugInfo = $af->debug();
        $this->assertTrue($debugInfo['stack'][0]['name'] == "anime characters");
        $this->assertTrue($debugInfo['sp'] == 0);

        // Increment the stack with 1 item should still have sp == 0
        $this->assertTrue($af->interpret("Yeah,"));
        $debugInfo = $af->debug();
        $this->assertTrue($debugInfo['sp'] == 0);

        // Let's move the stack and then decrement
        $af->interpret("You ever notice item1?");
        $af->interpret("You ever notice item2?");
        $debugInfo = $af->debug();
        $this->assertTrue($debugInfo['sp'] == 0);
        $this->assertTrue(count($debugInfo['stack']) == 3);

        // Increment the stack with 3 should have sp == 1
        $this->assertTrue($af->interpret("Yeah,"));
        $debugInfo = $af->debug();
        $this->assertTrue($debugInfo['sp'] == 1);

        // Increment the stack with 3 should have sp == 2
        $this->assertTrue($af->interpret("Yeah,"));
        $debugInfo = $af->debug();
        $this->assertTrue($debugInfo['sp'] == 2);

        // Increment the stack with sp == 2 should have sp == 2
        // Can't move over the end of the stack
        $this->assertTrue($af->interpret("Yeah,"));
        $debugInfo = $af->debug();
        $this->assertTrue($debugInfo['sp'] == 2);

    }




}
