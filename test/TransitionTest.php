<?php

use olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis\FsmMorphologicalAnalyzer;

class TransitionTest extends \PHPUnit\Framework\TestCase
{
    private FsmMorphologicalAnalyzer $fsm;
    protected function setUp(): void{
        ini_set('memory_limit', '250M');
        $this->fsm = new FsmMorphologicalAnalyzer();
    }

    public function testNumberWithAccusative(){
        $this->assertTrue($this->fsm->morphologicalAnalysis("2'yi")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("2'i")->size() == 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("5'i")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("9'u")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("10'u")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("30'u")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("3'ü")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("4'ü")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("100'ü")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("6'yı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("6'ı")->size() == 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("40'ı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("60'ı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("90'ı")->size() != 0);
    }

    public function testNumberWithDative()
    {
        $this->assertTrue($this->fsm->morphologicalAnalysis("6'ya")->size() != 0);
        $this->assertEquals(0, $this->fsm->morphologicalAnalysis("6'a")->size());
        $this->assertTrue($this->fsm->morphologicalAnalysis("9'a")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("10'a")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("30'a")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("40'a")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("60'a")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("90'a")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("2'ye")->size() != 0);
        $this->assertEquals(0, $this->fsm->morphologicalAnalysis("2'e")->size());
        $this->assertTrue($this->fsm->morphologicalAnalysis("8'e")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("5'e")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("4'e")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("1'e")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("3'e")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("7'ye")->size() != 0);
        $this->assertEquals(0, $this->fsm->morphologicalAnalysis("7'e")->size());
    }

    public function testPresentTense()
    {
        $this->assertTrue($this->fsm->morphologicalAnalysis("büyülüyor")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("bölümlüyor")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("buğuluyor")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("bulguluyor")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("açıklıyor")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("çalkalıyor")->size() != 0);
    }

    public function testA()
    {
        $this->assertTrue($this->fsm->morphologicalAnalysis("alkole")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("anormale")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("sakala")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("kabala")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("faika")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("halika")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("kediye")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("eve")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("saatinizi")->size() != 0);
    }

    public function testC()
    {
        $this->assertTrue($this->fsm->morphologicalAnalysis("gripçi")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("güllaççı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("gülütçü")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("gülükçü")->size() != 0);
    }

    public function testSH()
    {
        $this->assertTrue($this->fsm->morphologicalAnalysis("altışar")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("yedişer")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("üçer")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("beşer")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("dörder")->size() != 0);
    }

    public function testNumberWithD()
    {
        $this->assertTrue($this->fsm->morphologicalAnalysis("1'di")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("2'ydi")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("3'tü")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("4'tü")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("5'ti")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("6'ydı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("7'ydi")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("8'di")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("9'du")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("30'du")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("40'tı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("60'tı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("70'ti")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("50'ydi")->size() != 0);
    }

    public function testD()
    {
        $this->assertTrue($this->fsm->morphologicalAnalysis("koştu")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("kitaptı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("kaçtı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("evdi")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("fraktı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("sattı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("aftı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("kesti")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("ahtı")->size() != 0);
    }

    public function testExceptions()
    {
        $this->assertTrue($this->fsm->morphologicalAnalysis("yiyip")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("sana")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("bununla")->size() != 0);
        $this->assertEquals(0, $this->fsm->morphologicalAnalysis("buyla")->size());
        $this->assertTrue($this->fsm->morphologicalAnalysis("onunla")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("şununla")->size() != 0);
        $this->assertEquals(0, $this->fsm->morphologicalAnalysis("şuyla")->size());
        $this->assertTrue($this->fsm->morphologicalAnalysis("bana")->size() != 0);
    }

    public function testVowelEChangesToIDuringYSuffixation()
    {
        $this->assertTrue($this->fsm->morphologicalAnalysis("diyor")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("yiyor")->size() != 0);
    }

    public function testLastIdropsDuringPassiveSuffixation()
    {
        $this->assertTrue($this->fsm->morphologicalAnalysis("yoğruldu")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("buyruldu")->size() != 0);
    }

    public function testShowsSuRegularities()
    {
        $this->assertTrue($this->fsm->morphologicalAnalysis("karasuyu")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("suyu")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("suymuş")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("suyuymuş")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("suyla")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("suyuyla")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("suydu")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("suyuydu")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("suyuna")->size() != 0);
    }

    public function testDuplicatesDuringSuffixation()
    {
        $this->assertTrue($this->fsm->morphologicalAnalysis("tıbbı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("ceddi")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("zıddı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("serhaddi")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("fenni")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("haddi")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("hazzı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("şakkı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("şakı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("halli")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("hali")->size() != 0);
    }

    public function testLastIdropsDuringSuffixation()
    {
        $this->assertTrue($this->fsm->morphologicalAnalysis("hizbi")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("kaybı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("ahdi")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("nesci")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("zehri")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("zikri")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("metni")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("metini")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("katli")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("katili")->size() != 0);
    }

    public function testNounSoftenDuringSuffixation()
    {
        $this->assertTrue($this->fsm->morphologicalAnalysis("adabı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("amibi")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("armudu")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("ağacı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("akacı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("arkeoloğu")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("filoloğu")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("ahengi")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("küngü")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("kitaplığı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("küllüğü")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("adedi")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("adeti")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("ağıdı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("ağıtı")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("anotu")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("anodu")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("Kuzguncuk'u")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("Leylak'ı")->size() != 0);
    }

    public function testVerbSoftenDuringSuffixation()
    {
        $this->assertTrue($this->fsm->morphologicalAnalysis("cezbediyor")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("ediyor")->size() != 0);
        $this->assertTrue($this->fsm->morphologicalAnalysis("bahsediyor")->size() != 0);
    }

}