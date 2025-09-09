<?php

use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\MorphologicalParse;

class MorphologicalParseTest extends \PHPUnit\Framework\TestCase
{
    private MorphologicalParse $parse1, $parse2, $parse3, $parse4, $parse5, $parse6, $parse7, $parse8, $parse9;

    protected function setUp(): void
    {
        $this->parse1 = new MorphologicalParse("bayan+NOUN+A3SG+PNON+NOM");
        $this->parse2 = new MorphologicalParse("yaşa+VERB+POS^DB+ADJ+PRESPART");
        $this->parse3 = new MorphologicalParse("serbest+ADJ");
        $this->parse4 = new MorphologicalParse("et+VERB^DB+VERB+PASS^DB+VERB+ABLE+NEG+AOR+A3SG");
        $this->parse5 = new MorphologicalParse("sür+VERB^DB+VERB+CAUS^DB+VERB+PASS+POS^DB+NOUN+INF2+A3SG+P3SG+NOM");
        $this->parse6 = new MorphologicalParse("değiş+VERB^DB+VERB+CAUS^DB+VERB+PASS+POS^DB+VERB+ABLE+AOR^DB+ADJ+ZERO");
        $this->parse7 = new MorphologicalParse("iyi+ADJ^DB+VERB+BECOME^DB+VERB+CAUS^DB+VERB+PASS+POS^DB+VERB+ABLE^DB+NOUN+INF2+A3PL+P3PL+ABL");
        $this->parse8 = new MorphologicalParse("değil+ADJ^DB+VERB+ZERO+PAST+A3SG");
        $this->parse9 = new MorphologicalParse("hazır+ADJ^DB+VERB+ZERO+PAST+A3SG");
    }

    public function testGetTransitionList()
    {
        $this->assertEquals("NOUN+A3SG+PNON+NOM", $this->parse1->getMorphologicalParseTransitionList());
        $this->assertEquals("VERB+POS+ADJ+PRESPART", $this->parse2->getMorphologicalParseTransitionList());
        $this->assertEquals("ADJ", $this->parse3->getMorphologicalParseTransitionList());
        $this->assertEquals("VERB+VERB+PASS+VERB+ABLE+NEG+AOR+A3SG", $this->parse4->getMorphologicalParseTransitionList());
        $this->assertEquals("VERB+VERB+CAUS+VERB+PASS+POS+NOUN+INF2+A3SG+P3SG+NOM", $this->parse5->getMorphologicalParseTransitionList());
        $this->assertEquals("VERB+VERB+CAUS+VERB+PASS+POS+VERB+ABLE+AOR+ADJ+ZERO", $this->parse6->getMorphologicalParseTransitionList());
        $this->assertEquals("ADJ+VERB+BECOME+VERB+CAUS+VERB+PASS+POS+VERB+ABLE+NOUN+INF2+A3PL+P3PL+ABL", $this->parse7->getMorphologicalParseTransitionList());
        $this->assertEquals("ADJ+VERB+ZERO+PAST+A3SG", $this->parse8->getMorphologicalParseTransitionList());
    }

    public function testGetTag()
    {
        $this->assertEquals("A3SG", $this->parse1->getTag(2));
        $this->assertEquals("PRESPART", $this->parse2->getTag(4));
        $this->assertEquals("serbest", $this->parse3->getTag(0));
        $this->assertEquals("AOR", $this->parse4->getTag(7));
        $this->assertEquals("P3SG", $this->parse5->getTag(10));
        $this->assertEquals("ABLE", $this->parse6->getTag(8));
        $this->assertEquals("ABL", $this->parse7->getTag(15));
    }

    public function testGetTagSize()
    {
        $this->assertEquals(5, $this->parse1->tagSize());
        $this->assertEquals(5, $this->parse2->tagSize());
        $this->assertEquals(2, $this->parse3->tagSize());
        $this->assertEquals(9, $this->parse4->tagSize());
        $this->assertEquals(12, $this->parse5->tagSize());
        $this->assertEquals(12, $this->parse6->tagSize());
        $this->assertEquals(16, $this->parse7->tagSize());
        $this->assertEquals(6, $this->parse8->tagSize());
    }

    public function testSize()
    {
        $this->assertEquals(1, $this->parse1->size());
        $this->assertEquals(2, $this->parse2->size());
        $this->assertEquals(1, $this->parse3->size());
        $this->assertEquals(3, $this->parse4->size());
        $this->assertEquals(4, $this->parse5->size());
        $this->assertEquals(5, $this->parse6->size());
        $this->assertEquals(6, $this->parse7->size());
        $this->assertEquals(2, $this->parse8->size());
    }

    public function testGetRootPos()
    {
        $this->assertEquals("NOUN", $this->parse1->getRootPos());
        $this->assertEquals("VERB", $this->parse2->getRootPos());
        $this->assertEquals("ADJ", $this->parse3->getRootPos());
        $this->assertEquals("VERB", $this->parse4->getRootPos());
        $this->assertEquals("VERB", $this->parse5->getRootPos());
        $this->assertEquals("VERB", $this->parse6->getRootPos());
        $this->assertEquals("ADJ", $this->parse7->getRootPos());
        $this->assertEquals("ADJ", $this->parse8->getRootPos());
    }

    public function testGetPos()
    {
        $this->assertEquals("NOUN", $this->parse1->getPos());
        $this->assertEquals("ADJ", $this->parse2->getPos());
        $this->assertEquals("ADJ", $this->parse3->getPos());
        $this->assertEquals("VERB", $this->parse4->getPos());
        $this->assertEquals("NOUN", $this->parse5->getPos());
        $this->assertEquals("ADJ", $this->parse6->getPos());
        $this->assertEquals("NOUN", $this->parse7->getPos());
        $this->assertEquals("VERB", $this->parse8->getPos());
    }

    public function testGetWordWithPos()
    {
        $this->assertEquals("bayan+NOUN", $this->parse1->getWordWithPos()->getName());
        $this->assertEquals("yaşa+VERB", $this->parse2->getWordWithPos()->getName());
        $this->assertEquals("serbest+ADJ", $this->parse3->getWordWithPos()->getName());
        $this->assertEquals("et+VERB", $this->parse4->getWordWithPos()->getName());
        $this->assertEquals("sür+VERB", $this->parse5->getWordWithPos()->getName());
        $this->assertEquals("değiş+VERB", $this->parse6->getWordWithPos()->getName());
        $this->assertEquals("iyi+ADJ", $this->parse7->getWordWithPos()->getName());
        $this->assertEquals("değil+ADJ", $this->parse8->getWordWithPos()->getName());
    }

    public function testLastIGContainsCase()
    {
        $this->assertEquals("NOM", $this->parse1->lastIGContainsCase());
        $this->assertEquals("NULL", $this->parse2->lastIGContainsCase());
        $this->assertEquals("NULL", $this->parse3->lastIGContainsCase());
        $this->assertEquals("NULL", $this->parse4->lastIGContainsCase());
        $this->assertEquals("NOM", $this->parse5->lastIGContainsCase());
        $this->assertEquals("NULL", $this->parse6->lastIGContainsCase());
        $this->assertEquals("ABL", $this->parse7->lastIGContainsCase());
    }

    public function testLastIGContainsPossessive()
    {
        $this->assertTrue(!$this->parse1->lastIGContainsPossessive());
        $this->assertTrue(!$this->parse2->lastIGContainsPossessive());
        $this->assertTrue(!$this->parse3->lastIGContainsPossessive());
        $this->assertTrue(!$this->parse4->lastIGContainsPossessive());
        $this->assertTrue($this->parse5->lastIGContainsPossessive());
        $this->assertTrue(!$this->parse6->lastIGContainsPossessive());
        $this->assertTrue($this->parse7->lastIGContainsPossessive());
    }

    public function testIsPlural()
    {
        $this->assertTrue(!$this->parse1->isPlural());
        $this->assertTrue(!$this->parse2->isPlural());
        $this->assertTrue(!$this->parse3->isPlural());
        $this->assertTrue(!$this->parse4->isPlural());
        $this->assertTrue(!$this->parse5->isPlural());
        $this->assertTrue(!$this->parse6->isPlural());
        $this->assertTrue($this->parse7->isPlural());
    }

    public function testIsAuxiliary()
    {
        $this->assertTrue(!$this->parse1->isAuxiliary());
        $this->assertTrue(!$this->parse2->isAuxiliary());
        $this->assertTrue(!$this->parse3->isAuxiliary());
        $this->assertTrue($this->parse4->isAuxiliary());
        $this->assertTrue(!$this->parse5->isAuxiliary());
        $this->assertTrue(!$this->parse6->isAuxiliary());
        $this->assertTrue(!$this->parse7->isAuxiliary());
    }

    public function testIsNoun()
    {
        $this->assertTrue($this->parse1->isNoun());
        $this->assertTrue($this->parse5->isNoun());
        $this->assertTrue($this->parse7->isNoun());
    }

    public function testIsAdjective()
    {
        $this->assertTrue($this->parse2->isAdjective());
        $this->assertTrue($this->parse3->isAdjective());
        $this->assertTrue($this->parse6->isAdjective());
    }

    public function testIsVerb()
    {
        $this->assertTrue($this->parse4->isVerb());
        $this->assertTrue($this->parse8->isVerb());
    }

    public function testIsRootVerb()
    {
        $this->assertTrue($this->parse2->isRootVerb());
        $this->assertTrue($this->parse4->isRootVerb());
        $this->assertTrue($this->parse5->isRootVerb());
        $this->assertTrue($this->parse6->isRootVerb());
    }

    public function testGetTreePos(){
        $this->assertEquals("NP", $this->parse1->getTreePos());
        $this->assertEquals("ADJP", $this->parse2->getTreePos());
        $this->assertEquals("ADJP", $this->parse3->getTreePos());
        $this->assertEquals("VP", $this->parse4->getTreePos());
        $this->assertEquals("NP", $this->parse5->getTreePos());
        $this->assertEquals("ADJP", $this->parse6->getTreePos());
        $this->assertEquals("NP", $this->parse7->getTreePos());
        $this->assertEquals("NEG", $this->parse8->getTreePos());
        $this->assertEquals("NOMP", $this->parse9->getTreePos());
    }
}