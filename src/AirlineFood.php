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

    /**
     * Stack pointed, the item we're currently pointing the stack at
     *
     * @var int
     */
    protected $sp = self::EMPTY_STACK;

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
            return true;
        }

        if (preg_match(
            "/^Um\,$/",
            $line
          ) == 1) {
            $this->decrementSP();
            return true;
        }

        if (preg_match(
            "/^Yeah\,$/",
            $line
          ) == 1) {
            $this->incrementSP();
            return true;
        }
        if (preg_match("/^Let's talk about (.*)\.$/", $line, $matches) == 1) {
            $varName = $matches[1];
            $this->setSpToVariable($varName);
            return true;
        }

        if (preg_match("/^It's kinda like (.*)\.$/", $line, $matches) == 1) {
            $varName = $matches[1];
            $index = $this->getIndexOfVariable($varName);
            if($index == FALSE) {
                throw new \Exception("No variable of name {$varName} found.");
            }
            $this->stack[$this->sp]['data'] += $this->stack[$index]['data'];
            return true;
        }

        return false;
    }

    protected function decrementSP()
    {
        if($this->sp == self::EMPTY_STACK) {
            throw new \Exception("Error: Stack has not yet been initialized");
        }
        if($this->sp > 0) {
            $this->sp--;
        }
    }

    protected function getIndexOfVariable($name)
    {
        foreach ($this->stack as $i => $v) {
            if($v['name'] == $name) return $i;
        }
        return FALSE;
    }

    protected function incrementSP()
    {
        if($this->sp == self::EMPTY_STACK) {
            throw new \Exception("Error: Stack has not yet been initialized");
        }
        if($this->sp < count($this->stack) - 1) {
            $this->sp++;
        }
    }

    protected function setSpToVariable($variable)
    {
        foreach ($this->stack as $k => $v) {
            if($v['name'] == $variable) {
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
        return ["name" => trim($name), "data" => self::DEFAULTVARDATA];
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
        ];
    }

}