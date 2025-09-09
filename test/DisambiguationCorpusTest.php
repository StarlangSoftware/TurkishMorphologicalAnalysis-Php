<?php

use olcaytaner\MorphologicalAnalysis\Corpus\DisambiguationCorpus;

class DisambiguationCorpusTest extends \PHPUnit\Framework\TestCase
{
    public function testCorpus(){
        ini_set('memory_limit', '150M');
        $corpus = new DisambiguationCorpus("../penntreebank.txt");
        $this->assertEquals(19109, $corpus->sentenceCount());
        $this->assertEquals(170211, $corpus->numberOfWords());
    }
}