<?php

namespace olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis;

class State
{
    private bool $startState;

    private bool $endState;
    private string $name;
    private ?string $pos;

    /**
     * First constructor of the {@link State} class which takes 3 parameters String name, boolean startState,
     * and boolean endState as input and initializes the private variables of the class also leaves pos as null.
     *
     * @param bool $startState boolean input.
     * @param bool $endState   boolean input.
     * @param string $name String input.
     * @param string|null $pos String input.
     */
    public function __construct(string $name, bool $startState, bool $endState, string $pos = null){
        $this->startState = $startState;
        $this->endState = $endState;
        $this->name = $name;
        $this->pos = $pos;
    }

    /**
     * Getter for the pos.
     *
     * @return string String pos.
     */
    public function getPos(): ?string
    {
        return $this->pos;
    }

    /**
     * Getter for the name.
     *
     * @return string String name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * The isEndState method returns endState's value.
     *
     * @return bool boolean endState.
     */
    public function isEndState(): bool
    {
        return $this->endState;
    }

    /**
     * Overridden toString method which  returns the name.
     *
     * @return string String name.
     */
    public function __toString(): string{
        return $this->name;
    }


}