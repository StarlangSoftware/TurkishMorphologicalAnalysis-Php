<?php

use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\FsmMorphologicalAnalyzer;

class FsmMorphologicalAnalyzerTest extends \PHPUnit\Framework\TestCase
{
    private FsmMorphologicalAnalyzer $fsm;
    protected function setUp(): void{
        ini_set('memory_limit', '250M');
        $this->fsm = new FsmMorphologicalAnalyzer();
    }

    public function testMorphologicalAnalysisSpecialProperNoun(){
        $this->assertTrue($this->fsm->morphologicalAnalysis("Won'u")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("koşarcasına")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("uça")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("TL")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("Slack'in")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("SPK'ya")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("Stephen'ın")->size() != 0);
    }

    public function testMorphologicalAnalysisNewWords(){
        $this->assertTrue($this->fsm->robustMorphologicalAnalysis("googlecılardan")->size() == 6);
        $this->assertTrue($this->fsm->robustMorphologicalAnalysis("zaptıraplaştırılmayana")->size() == 8);
        $this->assertTrue($this->fsm->robustMorphologicalAnalysis("abzürtleşenmiş")->size() == 5);
        $this->assertTrue($this->fsm->robustMorphologicalAnalysis("vışlığından")->size() == 8);
    }

    public function testMorphologicalAnalysisDataTimeNumber(){
        $this->assertTrue($this->fsm->morphologicalAnalysis("3/4")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("2:3")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("12:3")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("4:23")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("4/2/1973")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("14/2/1993")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("14/12/1933")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("6/12/1903")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("%34.5")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("%3")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("%56")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("11:56")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("1:2:3")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("3:12:3")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("5:4:23")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("7:11:56")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("12:2:3")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("10:12:3")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("11:4:23")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("22:11:56")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("45")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("34.23")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("3\\/4")->size() != 0);
    }

}