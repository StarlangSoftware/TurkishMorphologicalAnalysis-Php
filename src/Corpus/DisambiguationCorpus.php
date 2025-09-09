<?php

namespace olcaytaner\MorphologicalAnalysis\Corpus;

use olcaytaner\Corpus\Corpus;
use olcaytaner\Corpus\Sentence;
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\MorphologicalParse;

class DisambiguationCorpus extends Corpus
{
    /**
     * Constructor which takes a file name {@link String} as an input and reads the file line by line. It takes each word of the line,
     * and creates a new {@link DisambiguatedWord} with current word and its {@link MorphologicalParse}. It also creates a new {@link Sentence}
     * when a new sentence starts, and adds each word to this sentence till the end of that sentence.
     *
     * @param string $fileName File which will be read and parsed.
     */
    public function __construct(?string $fileName = null){
        parent::__construct();
        if ($fileName !== null){
            $newSentence = null;
            $file = fopen($fileName, "r");
            while ($line = fgets($file)) {
                $word = mb_substr($line, 0, mb_strpos(trim($line), "\t"));
                $parse = mb_substr($line, mb_strpos(trim($line), "\t") + 1);
                if ($word != "" && $parse != "") {
                    $newWord = new DisambiguatedWord($word, new MorphologicalParse($parse));
                    if ($word == "<S>") {
                        $newSentence = new Sentence();
                    } else {
                        if ($word == "</S>") {
                            $this->addSentence($newSentence);
                        } else {
                            if ($word == "<DOC>" || $word == "</DOC>" || $word == "<TITLE>" || $word == "</TITLE>") {
                            } else {
                                if ($newSentence != null) {
                                    $newSentence->addWord($newWord);
                                }
                            }
                        }
                    }
                }
            }
            fclose($file);
        }
    }
}