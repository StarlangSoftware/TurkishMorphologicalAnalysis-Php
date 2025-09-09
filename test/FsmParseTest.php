<?php

use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\FsmMorphologicalAnalyzer;
use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\FsmParse;

class FsmParseTest extends \PHPUnit\Framework\TestCase
{
    public FsmParse $parse1, $parse2, $parse3, $parse4, $parse5, $parse6, $parse7, $parse8, $parse9, $parse10;
    protected function setUp(): void
    {
        ini_set('memory_limit', '250M');
        $fsm = new FsmMorphologicalAnalyzer();
        $this->parse1 = $fsm->morphologicalAnalysis("açılır")->getFsmParse(1);
        $this->parse2 = $fsm->morphologicalAnalysis("koparılarak")->getFsmParse(0);
        $this->parse3 = $fsm->morphologicalAnalysis("toplama")->getFsmParse(0);
        $this->parse4 = $fsm->morphologicalAnalysis("değerlendirmede")->getFsmParse(0);
        $this->parse5 = $fsm->morphologicalAnalysis("soruşturmasının")->getFsmParse(0);
        $this->parse6 = $fsm->morphologicalAnalysis("karşılaştırmalı")->getFsmParse(1);
        $this->parse7 = $fsm->morphologicalAnalysis("esaslarını")->getFsmParse(0);
        $this->parse8 = $fsm->morphologicalAnalysis("güçleriyle")->getFsmParse(0);
        $this->parse9 = $fsm->morphologicalAnalysis("bulmayacakları")->getFsmParse(0);
        $this->parse10 = $fsm->morphologicalAnalysis("mü")->getFsmParse(0);
    }

    public function testGetLastLemmaWithTag(){
        $this->assertEquals("açıl", $this->parse1->getLastLemmaWithTag("VERB"));
        $this->assertEquals("koparıl", $this->parse2->getLastLemmaWithTag("VERB"));
        $this->assertEquals("değerlendir", $this->parse4->getLastLemmaWithTag("VERB"));
        $this->assertEquals("soruştur", $this->parse5->getLastLemmaWithTag("VERB"));
        $this->assertEquals("karşı", $this->parse6->getLastLemmaWithTag("ADJ"));
    }

    public function testGetLastLemma(){
        $this->assertEquals("açıl", $this->parse1->getLastLemma());
        $this->assertEquals("koparılarak", $this->parse2->getLastLemma());
        $this->assertEquals("değerlendirme", $this->parse4->getLastLemma());
        $this->assertEquals("soruşturma", $this->parse5->getLastLemma());
        $this->assertEquals("karşılaştır", $this->parse6->getLastLemma());
    }

    public function testGetTransitionList(){
        $this->assertEquals("aç+VERB^DB+VERB+PASS+POS+AOR+A3SG", $this->parse1->toString__());
        $this->assertEquals("kop+VERB^DB+VERB+CAUS^DB+VERB+PASS+POS^DB+ADV+BYDOINGSO", $this->parse2->toString__());
        $this->assertEquals("topla+NOUN+A3SG+P1SG+DAT", $this->parse3->toString__());
        $this->assertEquals("değer+NOUN+A3SG+PNON+NOM^DB+VERB+ACQUIRE^DB+VERB+CAUS+POS^DB+NOUN+INF2+A3SG+PNON+LOC", $this->parse4->toString__());
        $this->assertEquals("sor+VERB+RECIP^DB+VERB+CAUS+POS^DB+NOUN+INF2+A3SG+P3SG+GEN", $this->parse5->toString__());
        $this->assertEquals("karşı+ADJ^DB+VERB+BECOME^DB+VERB+CAUS+POS+NECES+A3SG", $this->parse6->toString__());
        $this->assertEquals("esas+ADJ^DB+NOUN+ZERO+A3PL+P2SG+ACC", $this->parse7->toString__());
        $this->assertEquals("güç+ADJ^DB+NOUN+ZERO+A3PL+P3PL+INS", $this->parse8->toString__());
        $this->assertEquals("bul+VERB+NEG^DB+ADJ+FUTPART+P3PL", $this->parse9->toString__());
        $this->assertEquals("mi+QUES+PRES+A3SG", $this->parse10->toString__());
    }

    public function testWithList(){
        $this->assertEquals("aç+Hl+Hr", $this->parse1->getWithList());
        $this->assertEquals("kop+Ar+Hl+yArAk", $this->parse2->getWithList());
        $this->assertEquals("topla+Hm+yA", $this->parse3->getWithList());
        $this->assertEquals("değer+lAn+DHr+mA+DA", $this->parse4->getWithList());
        $this->assertEquals("sor+Hs+DHr+mA+sH+nHn", $this->parse5->getWithList());
        $this->assertEquals("karşı+lAs+DHr+mAlH", $this->parse6->getWithList());
        $this->assertEquals("esas+lAr+Hn+yH", $this->parse7->getWithList());
        $this->assertEquals("güç+lArH+ylA", $this->parse8->getWithList());
        $this->assertEquals("bul+mA+yAcAk+lArH", $this->parse9->getWithList());
    }

    public function testSuffixList(){
        $this->assertEquals("VerbalRoot(F5PR)(aç)+PassiveHl(açıl)+OtherTense2(açılır)", $this->parse1->getSuffixList());
        $this->assertEquals("VerbalRoot(F1P1)(kop)+CausativeAr(kopar)+PassiveHl(koparıl)+Adverb1(koparılarak)", $this->parse2->getSuffixList());
        $this->assertEquals("NominalRoot(topla)+Possessive(toplam)+Case1(toplama)", $this->parse3->getSuffixList());
        $this->assertEquals("NominalRoot(değer)+VerbalRoot(F5PR)(değerlen)+CausativeDHr(değerlendir)+NominalRoot(değerlendirme)+Case1(değerlendirmede)", $this->parse4->getSuffixList());
        $this->assertEquals("VerbalRoot(F5PR)(sor)+Reciprocal(soruş)+CausativeDHr(soruştur)+NominalRoot(soruşturma)+Possessive3(soruşturması)+Case1(soruşturmasının)", $this->parse5->getSuffixList());
        $this->assertEquals("AdjectiveRoot(karşı)+VerbalRoot(F5PR)(karşılaş)+CausativeDHr(karşılaştır)+OtherTense(karşılaştırmalı)", $this->parse6->getSuffixList());
        $this->assertEquals("AdjectiveRoot(esas)+Plural(esaslar)+Possessive(esasların)+AccusativeNoun(esaslarını)", $this->parse7->getSuffixList());
        $this->assertEquals("AdjectiveRoot(güç)+Possesive3(güçleri)+Case1(güçleriyle)", $this->parse8->getSuffixList());
        $this->assertEquals("VerbalRoot(F5PW)(bul)+Negativema(bulma)+AdjectiveParticiple(bulmayacak)+Adjective(bulmayacakları)", $this->parse9->getSuffixList());
    }
}