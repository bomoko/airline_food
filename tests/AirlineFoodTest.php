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

    /** @test */
    public function itShouldTalkAboutX()
    {
        $af = new AirlineFood();
        //Add two new variables
        // Let's move the stack and then decrement
        $af->interpret("What's the deal with inc1?");
        $af->interpret("What's the deal with inc2?");
        $debugInfo = $af->debug();
        $this->assertTrue($debugInfo['sp'] == 1);
        $af->interpret("Let's talk about inc1.");
        $debugInfo = $af->debug();
        $this->assertTrue($debugInfo['sp'] == 0);
    }

    /** @test */
    public function itShouldAddToVariables()
    {
        $af = new AirlineFood();
        //Add two new variables
        // Let's move the stack and then decrement
        $af->interpret("What's the deal with item1?");
        $af->interpret("You ever notice item2?");
        $debugInfo = $af->debug();
        $this->assertEquals(0, $debugInfo['sp']);
        $this->assertEquals(2, count($debugInfo['stack']));

        // so we have 2 variables, item1 and item2, each initialized to 1
        // sp->item1, and we want to add item2 to item1
        // leaving us with the value 2 in item1

        $af->interpret("It's kinda like item2.");
        $debugInfo = $af->debug();
        $this->assertEquals(2, $debugInfo['stack'][0]['data']);
    }

    /** @test */
    public function itShouldSubtractFromVariables()
    {
        $af = new AirlineFood();
        //Add two new variables
        // Let's move the stack and then decrement
        $af->interpret("What's the deal with item1?");
        $af->interpret("You ever notice item2?");
        $debugInfo = $af->debug();
        $this->assertEquals(0, $debugInfo['sp']);
        $this->assertEquals(2, count($debugInfo['stack']));

        // so we have 2 variables, item1 and item2, each initialized to 1
        // sp->item1, and we want to subtract item2 from item1
        // leaving us with the value 0 in item1

        $af->interpret("Not like item2.");
        $debugInfo = $af->debug();
        $this->assertEquals(0, $debugInfo['stack'][0]['data']);
    }

    /** @test */
    public function itShouldMultiplyVariables()
    {
        $af = new AirlineFood();
        //Add two new variables
        // Let's move the stack and then decrement
        $af->interpret("What's the deal with item1?");
        $af->interpret("You ever notice item2?");
        $debugInfo = $af->debug();
        $this->assertEquals(0, $debugInfo['sp']);
        $this->assertEquals(2, count($debugInfo['stack']));

        // so we have 2 variables, item1 and item2, each initialized to 1
        // sp->item1, and we want to multiply item2 with item1
        // leaving us with the value 2 in item1

        $af->interpret("Just like item2.");
        $debugInfo = $af->debug();
        $this->assertEquals(1, $debugInfo['stack'][0]['data']);
    }

    /** @test */
    public function itShouldLoadAndStepThroughAProgram()
    {
        $af = new AirlineFood();
        $af->loadProgram(
          [
            "You ever notice item1?",
            "You ever notice item2?",
          ]
        );

        $debug = $af->step();
        $this->assertEquals(1, count($debug['stack']));
        $this->assertEquals(1, $debug['pc']);
    }


    /** @test */
    public function itShouldJumpConditionally()
    {
        $af = new AirlineFood();
        $af->loadProgram(
          [
            "You ever notice item1?", //0
            "So...",                  //1
            "So...",                  //2 -- here we have a nested So, corresponds to like 5
            "You ever notice item2?", //3
            "You ever notice item3?", //4
            "Moving on...",           //5
            "You ever notice item4?", //6
            "Moving on...",           //7
          ]
        );

        $af->setState(
          [
            'stack' => [
              ['name' => 'somevariable', 'data' => 1],
            ],
            'pc' => 1,
            'sp' => 0,
          ]
        );
        // With the above state, we should expect stepping to run line 1
        // and for sp to end up equalling 2, since the variable we're pointing at
        // has 1 as data and 'So...' only jumps when the var is 0
        $debug = $af->step();
        $this->assertEquals(2, $debug['pc']);


        $af->setState(
          [
            'stack' => [
              ['name' => 'somevariable', 'data' => 0],
            ],
            'pc' => 1,
            'sp' => 0,
          ]
        );
        // With the above state, we should expect stepping to run line 1
        // and for sp to end up equalling 7, since the variable we're pointing at
        // has 0 as data
        $debug = $af->step();
        $this->assertEquals(7, $debug['pc']);

        //Now we test jumping conditionally backwards

        //Now we test jumping conditionally backwards
        $af->setState(
          [
            'stack' => [
              ['name' => 'somevariable', 'data' => 0],
            ],
            'pc' => 5,
            'sp' => 0,
          ]
        );
        // With the above state, we should expect stepping to run line 5
        // and for sp to end up equalling 6, since the variable we're pointing at
        // has 0 as data and 'Moving on...' moves on when its data is > 0
        $debug = $af->step();
        $this->assertEquals(6, $debug['pc']);

        $af->setState(
          [
            'stack' => [
              ['name' => 'somevariable', 'data' => 1],
            ],
            'pc' => 7,
            'sp' => 0,
          ]
        );
        // With the above state, we should expect stepping to run line 7
        // and for sp to end up equalling 1, since the variable we're pointing at
        // has 1 as data and its corresponding `So...` is line 1
        $debug = $af->step();
        $this->assertEquals(1, $debug['pc']);

    }

}
