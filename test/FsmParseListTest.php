<?php

use olcaytaner\Dictionary\Dictionary\Word;
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\FsmMorphologicalAnalyzer;
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\FsmParseList;

class FsmParseListTest extends \PHPUnit\Framework\TestCase
{
    public FsmParseList $parse1, $parse2, $parse3, $parse4, $parse5, $parse6, $parse7, $parse8, $parse9, $parse10,
        $parse11, $parse12, $parse13, $parse14, $parse15, $parse16, $parse17, $parse18;
    protected function setUp(): void
    {
        ini_set('memory_limit', '250M');
        $fsm = new FsmMorphologicalAnalyzer();
        $this->parse1 = $fsm->morphologicalAnalysis('açılır');
        $this->parse2 = $fsm->morphologicalAnalysis('koparılarak');
        $this->parse3 = $fsm->morphologicalAnalysis('toplama');
        $this->parse4 = $fsm->morphologicalAnalysis('değerlendirmede');
        $this->parse5 = $fsm->morphologicalAnalysis('soruşturmasının');
        $this->parse6 = $fsm->morphologicalAnalysis('karşılaştırmalı');
        $this->parse7 = $fsm->morphologicalAnalysis('esaslarını');
        $this->parse8 = $fsm->morphologicalAnalysis('güçleriyle');
        $this->parse9 = $fsm->morphologicalAnalysis('bulmayacakları');
        $this->parse10 = $fsm->morphologicalAnalysis('kitabı');
        $this->parse11 = $fsm->morphologicalAnalysis('kitapları');
        $this->parse12 = $fsm->morphologicalAnalysis('o');
        $this->parse13 = $fsm->morphologicalAnalysis('arabası');
        $this->parse14 = $fsm->morphologicalAnalysis('sana');
        $this->parse15 = $fsm->morphologicalAnalysis('açacağını');
        $this->parse16 = $fsm->morphologicalAnalysis('kollarımız');
        $this->parse17 = $fsm->morphologicalAnalysis('yapmamızı');
        $this->parse18 = $fsm->morphologicalAnalysis('koşmalıyız');
    }

    public function testSize(){
        $this->assertEquals(2, $this->parse1->size());
        $this->assertEquals(2, $this->parse2->size());
        $this->assertEquals(6, $this->parse3->size());
        $this->assertEquals(4, $this->parse4->size());
        $this->assertEquals(5, $this->parse5->size());
        $this->assertEquals(12, $this->parse6->size());
        $this->assertEquals(8, $this->parse7->size());
        $this->assertEquals(6, $this->parse8->size());
        $this->assertEquals(5, $this->parse9->size());
        $this->assertEquals(4, $this->parse14->size());
    }

    public function testRootWords(){
        $this->assertEquals('aç', $this->parse1->rootWords());
        $this->assertEquals('kop$kopar', $this->parse2->rootWords());
        $this->assertEquals('topla$toplam$toplama', $this->parse3->rootWords());
        $this->assertEquals('değer$değerlen$değerlendir$değerlendirme', $this->parse4->rootWords());
        $this->assertEquals('sor$soru$soruş$soruştur$soruşturma', $this->parse5->rootWords());
        $this->assertEquals('karşı$karşıla$karşılaş$karşılaştır$karşılaştırma$karşılaştırmalı', $this->parse6->rootWords());
        $this->assertEquals('esas', $this->parse7->rootWords());
        $this->assertEquals('güç', $this->parse8->rootWords());
        $this->assertEquals('bul', $this->parse9->rootWords());
    }

    public function testGetParseWithLongestRootWord(){
        $this->assertEquals('kopar', $this->parse2->getParseWithLongestRootWord()->getWord()->getName());
        $this->assertEquals('toplama', $this->parse3->getParseWithLongestRootWord()->getWord()->getName());
        $this->assertEquals('değerlendirme', $this->parse4->getParseWithLongestRootWord()->getWord()->getName());
        $this->assertEquals('soruşturma', $this->parse5->getParseWithLongestRootWord()->getWord()->getName());
        $this->assertEquals('karşılaştırmalı', $this->parse6->getParseWithLongestRootWord()->getWord()->getName());
        $this->assertEquals('aç', $this->parse15->getParseWithLongestRootWord()->getWord()->getName());
        $this->assertEquals('kol', $this->parse16->getParseWithLongestRootWord()->getWord()->getName());
        $this->assertEquals('yap', $this->parse17->getParseWithLongestRootWord()->getWord()->getName());
        $this->assertEquals('koş', $this->parse18->getParseWithLongestRootWord()->getWord()->getName());
    }

    public function testReduceToParsesWithSameRootAndPos(){
        $this->parse2->reduceToParsesWithSameRootAndPos(new Word('kop+VERB'));
        $this->assertEquals(1, $this->parse2->size());
        $this->parse3->reduceToParsesWithSameRootAndPos(new Word('topla+VERB'));
        $this->assertEquals(2, $this->parse3->size());
        $this->parse6->reduceToParsesWithSameRootAndPos(new Word('karşıla+VERB'));
        $this->assertEquals(2, $this->parse6->size());
    }

    public function testReduceToParsesWithSameRoot(){
        $this->parse2->reduceToParsesWithSameRoot('kop');
        $this->assertEquals(1, $this->parse2->size());
        $this->parse3->reduceToParsesWithSameRoot('topla');
        $this->assertEquals(3, $this->parse3->size());
        $this->parse6->reduceToParsesWithSameRoot('karşı');
        $this->assertEquals(4, $this->parse6->size());
        $this->parse7->reduceToParsesWithSameRoot('esas');
        $this->assertEquals(8, $this->parse7->size());
        $this->parse8->reduceToParsesWithSameRoot('güç');
        $this->assertEquals(6, $this->parse8->size());
    }

    public function testConstructParseListForDifferentRootWithPos(){
        $this->assertCount(1, $this->parse1->constructParseListForDifferentRootWithPos());
        $this->assertCount(2, $this->parse2->constructParseListForDifferentRootWithPos());
        $this->assertCount(5, $this->parse3->constructParseListForDifferentRootWithPos());
        $this->assertCount(4, $this->parse4->constructParseListForDifferentRootWithPos());
        $this->assertCount(5, $this->parse5->constructParseListForDifferentRootWithPos());
        $this->assertCount(7, $this->parse6->constructParseListForDifferentRootWithPos());
        $this->assertCount(2, $this->parse7->constructParseListForDifferentRootWithPos());
        $this->assertCount(2, $this->parse8->constructParseListForDifferentRootWithPos());
        $this->assertCount(1, $this->parse9->constructParseListForDifferentRootWithPos());
    }

    public function testParsesWithoutPrefixAndSuffix(){
        $this->assertEquals('P3SG+NOM$PNON+ACC', $this->parse10->parsesWithoutPrefixAndSuffix());
        $this->assertEquals('A3PL+P3PL+NOM$A3PL+P3SG+NOM$A3PL+PNON+ACC$A3SG+P3PL+NOM', $this->parse11->parsesWithoutPrefixAndSuffix());
        $this->assertEquals('DET$PRON+DEMONSP+A3SG+PNON+NOM$PRON+PERS+A3SG+PNON+NOM', $this->parse12->parsesWithoutPrefixAndSuffix());
        $this->assertEquals('NOUN+A3SG+P3SG+NOM$NOUN^DB+ADJ+ALMOST', $this->parse13->parsesWithoutPrefixAndSuffix());
    }
}