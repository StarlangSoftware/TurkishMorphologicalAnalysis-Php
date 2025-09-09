<?php

use olcaytaner\DataStructure\CounterHashMap;
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\FiniteStateMachine;

class FiniteStateMachineTest extends \PHPUnit\Framework\TestCase
{
    private FiniteStateMachine $fsm;
    private array $states;
    
    protected function setUp(): void
    {
        $this->fsm = new FiniteStateMachine("../turkish_finite_state_machine.xml");
        $this->states = $this->fsm->getStates();
    }

    public function testStateCount(){
        $this->assertCount(141, $this->states);
    }

    public function testEndStates(){
        $endStateCount = 0;
        foreach ($this->states as $state){
            if ($state->isEndState()){
                $endStateCount++;
            }
        }
        $this->assertEquals(37, $endStateCount);
    }

    public function testPosCounts(){
        $posCounts = new CounterHashMap();
        foreach ($this->states as $state){
            if ($state->getPos() !== null){
                $posCounts->put($state->getPos());
            }
        }
        $this->assertEquals(1, $posCounts->count("HEAD"));
        $this->assertEquals(6, $posCounts->count("PRON"));
        $this->assertEquals(1, $posCounts->count("PROP"));
        $this->assertEquals(8, $posCounts->count("NUM"));
        $this->assertEquals(7, $posCounts->count("ADJ"));
        $this->assertEquals(1, $posCounts->count("INTERJ"));
        $this->assertEquals(1, $posCounts->count("DET"));
        $this->assertEquals(1, $posCounts->count("ADVERB"));
        $this->assertEquals(1, $posCounts->count("QUES"));
        $this->assertEquals(1, $posCounts->count("CONJ"));
        $this->assertEquals(26, $posCounts->count("VERB"));
        $this->assertEquals(1, $posCounts->count("POSTP"));
        $this->assertEquals(1, $posCounts->count("DUP"));
        $this->assertEquals(11, $posCounts->count("NOUN"));
    }

    public function testTransitionCount(){
        $transitionCount = 0;
        foreach ($this->states as $state){
            $transitionCount += count($this->fsm->getTransitions($state));
        }
        $this->assertEquals(783, $transitionCount);
    }

}