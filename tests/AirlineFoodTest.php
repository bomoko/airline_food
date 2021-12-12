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
        $this->assertTrue($af->interpret($command));$debugInfo = $af->debug();
        $this->assertTrue(count($debugInfo['stack']) == 3);
        $this->assertTrue($debugInfo['sp'] == 2);
        $this->assertTrue($debugInfo['stack'][$debugInfo['sp']]['name'] == 'third entry');

        // Now if we add a new variable, with a "You ever notice ...?"
        // The stack will increase to 4, but the sp will still be at 2
        $command = "You ever notice no stack pointer?";
        $this->assertTrue($af->interpret($command));$debugInfo = $af->debug();
        $this->assertTrue(count($debugInfo['stack']) == 4);
        $this->assertTrue($debugInfo['sp'] == 2);
        $this->assertTrue($debugInfo['stack'][$debugInfo['sp'] + 1]['name'] == 'no stack pointer');
    }




}
