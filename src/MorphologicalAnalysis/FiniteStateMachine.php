<?php

namespace olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis;

use olcaytaner\XmlParser\XmlDocument;

class FiniteStateMachine
{
    private array $states = [];
    private array $transitions = [];

    /**
     * Constructor reads the finite state machine in the given input file. It has a NodeList which holds the states
     * of the nodes and there are 4 different type of nodes; $stateNode, root Node, $transitionNode and $withNode.
     * Also there are two states; state that a node currently in and state that a node will be in.
     * <p>
     * DOMParser is used to parse the given file. Firstly it gets the document to parse, then gets its elements by the
     * tag names. For instance, it gets states by the tag name 'state' and puts them into an ArrayList called stateList.
     * Secondly, it traverses this stateList and gets each Node's attributes. There are three attributes; name, start,
     * and end which will be named as states. If a node is in a $startState it is tagged as 'yes', otherwise 'no'.
     * Also, if a node is in a $startState, additional attribute will be fetched; $originalPos that represents its original
     * part of speech.
     * <p>
     * At the last step, by starting rootNode's first child, it gets all the transitionNodes and next states called $toState,
     * then continue with the nextSiblings. Also, if there is no possible $toState, it prints this case and the causative states.
     *
     * @param string $fileName the resource file to read the finite state machine. Only files in resources folder are supported.
     */
    public function __construct(string $fileName = "../turkish_finite_state_machine.xml"){
        $xmlDocument = new XmlDocument($fileName);
        $xmlDocument->parse();
        $stateListNode = $xmlDocument->getFirstChild();
        $stateNode = $stateListNode->getFirstChild();
        while ($stateNode != null) {
            if ($stateNode->hasAttributes()) {
                $stateName = $stateNode->getAttributeValue("name");
                $startState = $stateNode->getAttributeValue("start");
                $endState = $stateNode->getAttributeValue("end");
                if ($startState == "yes") {
                    $originalPos = $stateNode->getAttributeValue("originalpos");
                    $state = new State($stateName, true, $endState == "yes", $originalPos);
                } else {
                    $state = new State($stateName, false, $endState == "yes");
                }
                $this->states[] = $state;
            }
            $stateNode = $stateNode->getNextSibling();
        }
        $stateNode = $stateListNode->getFirstChild();
        while ($stateNode != null){
            if ($stateNode->hasAttributes()){
                $stateName = $stateNode->getAttributeValue("name");
                $state = $this->getState($stateName);
                $transitionNode = $stateNode->getFirstChild();
                while ($transitionNode != null){
                    if ($transitionNode->hasAttributes()){
                        $toStateName = $transitionNode->getAttributeValue("name");
                        $toState = $this->getState($toStateName);
                        $withName = $transitionNode->getAttributeValue("transitionname");
                        $rootToPos = $transitionNode->getAttributeValue("topos");
                        $withNode = $transitionNode->getFirstChild();
                        while ($withNode != null){
                            if ($withNode->hasAttributes()){
                                $withName = $withNode->getAttributeValue("name");
                                $toPos = $withNode->getAttributeValue("topos");
                            } else {
                                $toPos = "";
                            }
                            if ($toPos == ""){
                                if ($rootToPos == ""){
                                    $this->addTransition($state, $toState, $withNode->getPcData(), $withName);
                                } else {
                                    $this->addTransition($state, $toState, $withNode->getPcData(), $withName, $rootToPos);
                                }
                            } else {
                                $this->addTransition($state, $toState, $withNode->getPcData(), $withName, $toPos);
                            }
                            $withNode = $withNode->getNextSibling();
                        }
                    }
                    $transitionNode = $transitionNode->getNextSibling();
                }
            }
            $stateNode = $stateNode->getNextSibling();
        }
    }

    /**
     * The isValidTransition loops through states ArrayList and checks transitions between states. If the actual transition
     * equals to the given transition input, method returns true otherwise returns false.
     *
     * @param string $transition is used to compare with the actual transition of a state.
     * @return bool true when the actual transition equals to the transition input, false otherwise.
     */
    public function isValidTransition(string $transition): bool
    {
        foreach (array_keys($this->transitions) as $state) {
            foreach ($this->transitions[$state] as $transition1) {
                if ($transition1->__toString() != null && $transition1->__toString() == $transition) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * the getStates method returns the states in the FiniteStateMachine.
     * @return array StateList.
     */
    public function getStates(): array
    {
        return $this->states;
    }

    /**
     * The getState method is used to loop through the states {@link Array} and return the state whose name equal
     * to the given input name.
     *
     * @param string $name is used to compare with the state's actual name.
     * @return State|null state if found any, null otherwise.
     */
    public function getState(string $name): ?State
    {
        foreach ($this->states as $state) {
            if ($state->getName() == $name) {
                return $state;
            }
        }
        return null;
    }

    /**
     * Another addTransition method which takes additional argument; toPos and. It creates a new {@link Transition}
     * with given input parameters and adds the transition to transitions {@link Array}.
     *
     * @param State $fromState  State type input indicating the from state.
     * @param State $toState  State type input indicating the next state.
     * @param string $with     String input indicating with what the transition will be made.
     * @param string $withName String input.
     * @param string|null $toPos    String input.
     */
    public function addTransition(State $fromState, State $toState, string $with, string $withName, ?string $toPos = null): void
    {
        $newTransition = new Transition($with, $withName, $toState, $toPos);
        if (array_key_exists($fromState->getName(), $this->transitions)) {
            $transitionList = $this->transitions[$fromState->getName()];
        } else {
            $transitionList = [];
        }
        $transitionList[] = $newTransition;
        $this->transitions[$fromState->getName()] = $transitionList;
    }

    /**
     * The getTransitions method returns the transitions at the given state.
     *
     * @param State $state State input.
     * @return array transitions at given state.
     */
    public function getTransitions(State $state): array
    {
        if (array_key_exists($state->getName(), $this->transitions)) {
            return $this->transitions[$state->getName()];
        } else {
            return [];
        }
    }
}