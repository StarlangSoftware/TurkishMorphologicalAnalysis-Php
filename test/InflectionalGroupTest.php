<?php

use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\InflectionalGroup;
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\MorphologicalTag;

class InflectionalGroupTest extends \PHPUnit\Framework\TestCase
{
    public function testGetMorphologicalTag(){
        $this->assertEquals(MorphologicalTag::NOUN, InflectionalGroup::getMorphologicalTag('noun'));
        $this->assertEquals(MorphologicalTag::WITHOUT, InflectionalGroup::getMorphologicalTag('without'));
        $this->assertEquals(MorphologicalTag::INTERJECTION, InflectionalGroup::getMorphologicalTag('interj'));
        $this->assertEquals(MorphologicalTag::INFINITIVE2, InflectionalGroup::getMorphologicalTag('inf2'));
    }

    public function testSize(){
        $this->assertEquals(1, (new InflectionalGroup("ADJ"))->size());
        $this->assertEquals(2, (new InflectionalGroup("ADJ+JUSTLIKE"))->size());
        $this->assertEquals(3, (new InflectionalGroup("ADJ+FUTPART+P1PL"))->size());
        $this->assertEquals(4, (new InflectionalGroup("NOUN+A3PL+P1PL+ABL"))->size());
        $this->assertEquals(5, (new InflectionalGroup("ADJ+WITH+A3SG+P3SG+ABL"))->size());
        $this->assertEquals(6, (new InflectionalGroup("VERB+ABLE+NEG+FUT+A3PL+COP"))->size());
        $this->assertEquals(7, (new InflectionalGroup("VERB+ABLE+NEG+AOR+A3SG+COND+A1SG"))->size());
    }

    public function testContainsCase(){
        $this->assertNotNull((new InflectionalGroup("NOUN+ACTOF+A3PL+P1PL+NOM"))->containsCase());
        $this->assertNotNull((new InflectionalGroup("NOUN+A3PL+P1PL+ACC"))->containsCase());
        $this->assertNotNull((new InflectionalGroup("NOUN+ZERO+A3SG+P3PL+DAT"))->containsCase());
        $this->assertNotNull((new InflectionalGroup("PRON+QUANTP+A1PL+P1PL+LOC"))->containsCase());
        $this->assertNotNull((new InflectionalGroup("NOUN+AGT+A3SG+P2SG+ABL"))->containsCase());
    }

    public function testContainsPlural(){
        $this->assertTrue((new InflectionalGroup("VERB+NEG+NECES+A1PL"))->containsPlural());
        $this->assertTrue((new InflectionalGroup("PRON+PERS+A2PL+PNON+NOM"))->containsPlural());
        $this->assertTrue((new InflectionalGroup("NOUN+DIM+A3PL+P2SG+GEN"))->containsPlural());
        $this->assertTrue((new InflectionalGroup("NOUN+A3PL+P1PL+GEN"))->containsPlural());
        $this->assertTrue((new InflectionalGroup("NOUN+ZERO+A3SG+P2PL+INS"))->containsPlural());
        $this->assertTrue((new InflectionalGroup("PRON+QUANTP+A3PL+P3PL+LOC"))->containsPlural());
    }

    public function testContainsTag(){
        $this->assertTrue((new InflectionalGroup("NOUN+ZERO+A3SG+P1SG+NOM"))->containsTag(MorphologicalTag::NOUN));
        $this->assertTrue((new InflectionalGroup("NOUN+AGT+A3PL+P2SG+ABL"))->containsTag(MorphologicalTag::AGENT));
        $this->assertTrue((new InflectionalGroup("NOUN+INF2+A3PL+P3SG+NOM"))->containsTag(MorphologicalTag::NOMINATIVE));
        $this->assertTrue((new InflectionalGroup("NOUN+ZERO+A3SG+P1PL+ACC"))->containsTag(MorphologicalTag::ZERO));
        $this->assertTrue((new InflectionalGroup("NOUN+ZERO+A3SG+P2PL+INS"))->containsTag(MorphologicalTag::P2PL));
        $this->assertTrue((new InflectionalGroup("PRON+QUANTP+A3PL+P3PL+LOC"))->containsTag(MorphologicalTag::QUANTITATIVEPRONOUN));
    }

    public function testContainsPossessive(){
        $this->assertTrue((new InflectionalGroup("NOUN+ZERO+A3SG+P1SG+NOM"))->containsPossessive());
        $this->assertTrue((new InflectionalGroup("NOUN+AGT+A3PL+P2SG+ABL"))->containsPossessive());
        $this->assertTrue((new InflectionalGroup("NOUN+INF2+A3PL+P3SG+NOM"))->containsPossessive());
        $this->assertTrue((new InflectionalGroup("NOUN+ZERO+A3SG+P1PL+ACC"))->containsPossessive());
        $this->assertTrue((new InflectionalGroup("NOUN+ZERO+A3SG+P2PL+INS"))->containsPossessive());
        $this->assertTrue((new InflectionalGroup("PRON+QUANTP+A3PL+P3PL+LOC"))->containsPossessive());
    }
}