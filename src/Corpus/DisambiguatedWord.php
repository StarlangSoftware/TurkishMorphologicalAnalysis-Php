<?php

namespace olcaytaner\MorphologicalAnalysis\Corpus;

use olcaytaner\Dictionary\Dictionary\Word;
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\MorphologicalParse;

class DisambiguatedWord extends Word
{
    private MorphologicalParse $parse;

    /**
     * The constructor of {@link DisambiguatedWord} class which takes a {@link String} and a {@link MorphologicalParse}
     * as inputs. It creates a new {@link MorphologicalParse} with given MorphologicalParse. It generates a new instance with
     * given {@link String}.
     *
     * @param string $name  Instances that will be a DisambiguatedWord.
     * @param MorphologicalParse $parse {@link MorphologicalParse} of the {@link DisambiguatedWord}.
     */
    public function __construct(string $name, MorphologicalParse $parse){
        parent::__construct($name);
        $this->parse = $parse;
    }

    /**
     * Accessor for the {@link MorphologicalParse}.
     *
     * @return MorphologicalParse MorphologicalParse.
     */
    public function getParse(): MorphologicalParse{
        return $this->parse;
    }
}