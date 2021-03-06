<?php


namespace Airline;

/**
 * Class AirlineFood
 * This is the primary interpreter for the "Airline Food" language
 *
 * @package Airline
 */
class AirlineFood
{

    const DEFAULTVARDATA = 1;

    const EMPTY_STACK = -1;

    protected $stack = [];

    protected $program = [];

    protected $pc = -1;

    protected $readIn = null;

    protected $writeOut = null;

    public function __construct($readIn = null, $writeOut = null)
    {
        // we'll provide a default implementation for reading in

        if (is_null($readIn)) {
            $this->readIn = function () {
                return 0;
            };
        } elseif (!is_callable($readIn)) {
            throw new \Exception("Argument for constructor expects a callable");
        } else {
            $this->readIn = $readIn;
        }

        if (is_null($writeOut)) {
            $this->writeOut = function ($data) {
                print($data);
            };
        } elseif (!is_callable($writeOut)) {
            throw new \Exception("Argument for constructor expects a callable");
        } else {
            $this->writeOut = $writeOut;
        }
    }

    /**
     * Stack pointed, the item we're currently pointing the stack at
     *
     * @var int
     */
    protected $sp = self::EMPTY_STACK;

    /**
     * Loads a program into memory.
     *
     * @param array $program
     */
    public function loadProgram(array $program)
    {
        $this->pc = 0;
        $this->sp = 0;
        $this->stack = [];
        $this->program = $program;
    }

    /**
     * This is primarily a debugging function
     * It will run a single step in the program
     * and can be used in combination with the debugger output
     * which is returned.
     */
    public function step()
    {
        if ($this->pc == -1 || $this->sp == -1) {
            throw new \Exception("Cannot step through program - uninitialized");
        }
        $this->interpret($this->program[$this->pc]);
        return $this->debug();
    }

    protected function setPC($newPC)
    {
        if ($this->pc >= 0) {
            $this->pc = $newPC;
        }
    }

    protected function incrementPC()
    {
        if ($this->pc >= 0) {
            $this->pc++;
        }
    }

    /**
     * This is the central interpreting function
     * It expects to match a single command - parsing a full program
     * should be done elsewhere
     *
     * @param $line
     */
    public function interpret($line)
    {
        $matches = [];
        if (preg_match("/^You ever notice (.*)\?$/", $line, $matches) == 1) {
            $varName = $matches[1];
            $this->addVariable($this->createVariable($varName));
            $this->incrementPC();
            return true;
        }
        if (preg_match(
            "/^What\'s the deal with (.*)\?$/",
            $line,
            $matches
          ) == 1) {
            $varName = $matches[1];
            $this->setSP(
              $this->addVariable($this->createVariable($varName))
            );
            $this->incrementPC();
            return true;
        }

        if (preg_match(
            "/^Um\,$/",
            $line
          ) == 1) {
            $this->decrementSP();
            $this->incrementPC();
            return true;
        }

        if (preg_match(
            "/^Yeah\,$/",
            $line
          ) == 1) {
            $this->incrementSP();
            $this->incrementPC();
            return true;
        }
        if (preg_match("/^Let's talk about (.*)\.$/", $line, $matches) == 1) {
            $varName = $matches[1];
            $this->setSpToVariable($varName);
            $this->incrementPC();
            return true;
        }

        if (preg_match("/^It's kinda like (.*)\.$/", $line, $matches) == 1) {
            $varName = $matches[1];
            $index = $this->getIndexOfVariable($varName);
            if ($index == false) {
                throw new \Exception("No variable of name {$varName} found.");
            }
            $this->stack[$this->sp]['data'] += $this->stack[$index]['data'];
            $this->incrementPC();
            return true;
        }

        if (preg_match("/^Not like (.*)\.$/", $line, $matches) == 1) {
            $varName = $matches[1];
            $index = $this->getIndexOfVariable($varName);
            if ($index == false) {
                throw new \Exception("No variable of name {$varName} found.");
            }
            $this->stack[$this->sp]['data'] -= $this->stack[$index]['data'];
            $this->incrementPC();
            return true;
        }

        if (preg_match("/^Just like (.*)\.$/", $line, $matches) == 1) {
            $varName = $matches[1];
            $index = $this->getIndexOfVariable($varName);
            if ($index == false) {
                throw new \Exception("No variable of name {$varName} found.");
            }
            $this->stack[$this->sp]['data'] *= $this->stack[$index]['data'];
            $this->incrementPC();
            return true;
        }

        if (preg_match("/^So...$/", $line, $matches) == 1) {
            if ($this->stack[$this->sp]['data'] == 0) {
                $targetPC = $this->findMatchingMovingOn($this->pc);
                $this->setPC($targetPC);
            } else {
                $this->incrementPC();
            }
            return true;
        }

        if (preg_match("/^Moving on...$/", $line, $matches) == 1) {
            if ($this->stack[$this->sp]['data'] != 0) {
                $targetPC = $this->findMatchingSo($this->pc);
                $this->setPC($targetPC);
            } else {
                $this->incrementPC();
            }
            return true;
        }

        if (preg_match("/^Right\?$/", $line, $matches) == 1) {
            $this->stack[$this->sp]['data'] = ($this->readIn)();
            $this->incrementPC();
        }

        if (preg_match("/^See\?$/", $line, $matches) == 1) {
            ($this->writeOut)($this->stack[$this->sp]['data']);
            $this->incrementPC();
        }

        return false;
    }

    protected function findMatchingMovingOn($soLineNumber)
    {
        //first let's check that this line is _actually_ a "So..."
        if ($this->program[$soLineNumber] != "So...") {
            throw new \Exception("Line {$soLineNumber} is not a 'So...'");
        }
        $numberInterimSos = 0; //if there are any other "So..."s, we need to
        // ignore them and their corresponding "Moving On..."s
        for ($i = $soLineNumber + 1; $i < count($this->program); $i++) {
            $line = $this->program[$i];
            if ($line == "So...") {
                $numberInterimSos++;
            } elseif ($line == "Moving on...") {
                if ($numberInterimSos > 0) {
                    $numberInterimSos--;
                } else {
                    return $i;
                }
            }
        }
        throw new \Exception(
          "Syntax Error: No matching 'Moving on...' for 'So...' line {$soLineNumber}"
        );
    }

    protected function findMatchingSo($moLineNumber)
    {
        //first let's check that this line is _actually_ a "So..."
        if ($this->program[$moLineNumber] != "Moving on...") {
            throw new \Exception("Line {$moLineNumber} is not a 'So...'");
        }
        $numberInterimMos = 0; //if there are any other "So..."s, we need to
        // ignore them and their corresponding "Moving On..."s
        for ($i = $moLineNumber - 1; $i >= 0; $i--) {
            $line = $this->program[$i];

            if ($line == "Moving on...") {
                $numberInterimMos++;
            } elseif ($line == "So...") {
                if ($numberInterimMos > 0) {
                    $numberInterimMos--;
                } else {
                    return $i;
                }
            }
        }
        throw new \Exception(
          "Syntax Error: No matching 'So...' for 'Moving on...' line {$moLineNumber}"
        );
    }

    protected function decrementSP()
    {
        if ($this->sp == self::EMPTY_STACK) {
            throw new \Exception("Error: Stack has not yet been initialized");
        }
        if ($this->sp > 0) {
            $this->sp--;
        }
    }

    protected function getIndexOfVariable($name)
    {
        foreach ($this->stack as $i => $v) {
            if ($v['name'] == $name) {
                return $i;
            }
        }
        return false;
    }

    protected function incrementSP()
    {
        if ($this->sp == self::EMPTY_STACK) {
            throw new \Exception("Error: Stack has not yet been initialized");
        }
        if ($this->sp < count($this->stack) - 1) {
            $this->sp++;
        }
    }

    protected function setSpToVariable($variable)
    {
        foreach ($this->stack as $k => $v) {
            if ($v['name'] == $variable) {
                $this->setSP($k);
                return;
            }
        }
    }

    protected function addVariable($variable)
    {
        //iterate through the stack to see if the variable already exists
        //crash out if it does
        foreach ($this->stack as $v) {
            if ($v['name'] !== null && $v['name'] == $variable['name']) {
                throw new \Exception(
                  "Variable of name {$variable['name']} already exists."
                );
            }
        }

        array_push($this->stack, $variable);
        end($this->stack);
        return key($this->stack);
    }

    protected function setSP($newIndex)
    {
        $this->sp = $newIndex;
    }

    protected function createVariable($name)
    {
        $varName = $name == "airline food" ? null : trim($name);
        return ["name" => $varName, "data" => self::DEFAULTVARDATA];
    }

    /**
     * Allows debuggers to set program state
     */
    public function setState(array $state)
    {
        if (!empty($state['pc'])) {
            $this->pc = $state['pc'];
        }
        if (!empty($state['sp'])) {
            $this->sp = $state['sp'];
        }
        if (!empty($state['stack'])) {
            $this->stack = $state['stack'];
        }
        if (!empty($state['program'])) {
            $this->program = $state['program'];
        }
    }

    /**
     * Returns debug information about machine state
     */
    public
    function debug(): array
    {
        return [
          "stack" => $this->stack,
          "sp" => $this->sp,
          "pc" => $this->pc,
          "program" => $this->program,
        ];
    }

}