<?php

namespace olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis;

use olcaytaner\Dictionary\Dictionary\Word;

class MetamorphicParse
{
    /**
     * metaMorphemes that can be used.
     */
    public static array $metaMorphemes = ["Ar", "Ar", "CA", "CA",
        "CA", "cAsHnA", "CH", "CHk",
        "DA", "DAn", "DH", "DHk",
        "DHkCA", "DHr", "DHr", "DHr",
        "H", "Hl", "Hm", "Hn",
        "Hn", "Hn", "HmHz", "HncH",
        "HnHz", "Hr", "Hr", "Hs",
        "Ht", "Hyor", "Hz", "k",
        "ki", "kü", "lAn", "lAr",
        "lArDHr", "lArH", "lArH'", "lAs",
        "lH", "lHk", "lHm", "m",
        "mA", "mA", "mAcA", "mAdAn",
        "mAk", "mAksHzHn", "mAktA", "mAlH",
        "mAzlHk", "mHs", "n", "n",
        "nA", "ncA", "nDA", "nDAn",
        "nH", "nHn", "nHz", "nlAr",
        "SA", "SAl", "sH", "SH",
        "SH", "SHn", "SHnHz", "SHnlAr",
        "SHz", "ŞAr", "t", "yA",
        "yA", "yAbil", "yAcAk", "yAcAk",
        "yAdur", "yAgel", "yAlH", "yAmA",
        "yAmAdAn", "yAn", "yArAk", "yAsH",
        "yDH", "yH", "yHcH", "yHm",
        "yHn", "yHncA", "yHp", "yHs",
        "yHver", "yHz", "yken", "ylA",
        "ymHs", "ysA", "z", "zsHn",
        "zsHnHz", "zlAr", "yAkal", "yAkoy",
        "yAgor"];

    public static array $morphotacticTags = [/*
              Aorist Tense : Her hafta sonunda futbol oynarlar.
            */
        MorphologicalTag::AORIST,
        /*
         * Causative Form : Pişirmek
        */
        MorphologicalTag::CAUSATIVE,
        /*
         * As if : Yaşarmışcasına
        */
        MorphologicalTag::ASIF,
        /*
         * -LY : Akıllıca
        */
        MorphologicalTag::LY,
        /*
         * Equative : Öylece
        */
        MorphologicalTag::EQUATIVE,
        /*
         * As if
        */
        MorphologicalTag::ASIF,
        /*
         * Agent : Toplayıcı
        */
        MorphologicalTag::AGENT,
        /*
         * Dimension : Küçücük
        */
        MorphologicalTag::DIMENSION,
        /*
         * Locative : Aşağıda
        */
        MorphologicalTag::LOCATIVE,
        /*
         * Ablative : Okuldan
        */
        MorphologicalTag::ABLATIVE,
        /*
         * Past tense : Yaşandı
        */
        MorphologicalTag::PASTTENSE,
        /*
         * Past participle : Kırılmış
        */
        MorphologicalTag::PASTPARTICIPLE,
        /*
         * As long as : Yaşadıkça
        */
        MorphologicalTag::ASLONGAS,
        /*
         * Copula : Mavidir
        */
        MorphologicalTag::COPULA,
        /*
         * Since : Yıllardır
        */
        MorphologicalTag::SINCE,
        /*
         * Causitive
        */
        MorphologicalTag::CAUSATIVE,
        /*
         * 3rd person singular possessive : Onun
        */
        MorphologicalTag::P3SG,
        /*
         * Passive : Açıldı
        */
        MorphologicalTag::PASSIVE,
        /*
         * 1st person singular possessive : Benim
        */
        MorphologicalTag::P1SG,
        /*
         * Reflexive : Kendi
        */
        MorphologicalTag::REFLEXIVE,
        /*
         * Passive
        */
        MorphologicalTag::PASSIVE,
        /*
         * 2nd person singular possessive :Senin
        */
        MorphologicalTag::P2SG,
        /*
         * 1st person plural possessive :  Bizim
        */
        MorphologicalTag::P1PL,
        /*
         * Ordinal Number : Altıncı
        */
        MorphologicalTag::ORDINAL,
        /*
         * 2nd person plural possessive : Sizin
        */
        MorphologicalTag::P2PL,
        /*
         * Aorist
        */
        MorphologicalTag::AORIST,
        /*
         * Causitive
        */
        MorphologicalTag::CAUSATIVE,
        /*
         * Reciprocal verb : Görüşmek
        */
        MorphologicalTag::RECIPROCAL,
        /*
         * Causitive
        */
        MorphologicalTag::CAUSATIVE,
        /*
         * Progressive1 : Görüyor
        */
        MorphologicalTag::PROGRESSIVE1,
        /*
         * 1st person plural agreement : Biz gideriz
        */
        MorphologicalTag::A1PL,
        /*
         * 1st person plural agreement
        */
        MorphologicalTag::A1PL,
        /*
         * Relative : Gelenin
        */
        MorphologicalTag::RELATIVE,
        /*
         * Relative
        */
        MorphologicalTag::RELATIVE,
        /*
         * Acquire : Kazanılan
        */
        MorphologicalTag::ACQUIRE,
        /*
         * 3rd person plural agreement : Onlar giderler
        */
        MorphologicalTag::A3PL,
        /*
         * Since
        */
        MorphologicalTag::SINCE,
        /*
         * 3rd person plural possessive : Onların
        */
        MorphologicalTag::P3PL,
        /*
         * 3rd person plural possessive
        */
        MorphologicalTag::P3PL,
        /*
         * Become : Abideleş
        */
        MorphologicalTag::BECOME,
        /*
         * With : Kalemle
        */
        MorphologicalTag::WITH,
        /*
         * Ness : Ağırbaşlılık
        */
        MorphologicalTag::NESS,
        /*
         * 1st person plural agreement
        */
        MorphologicalTag::A1PL,
        /*
         * 1st person singular agreement : Ben giderim
        */
        MorphologicalTag::A1SG,
        /*
         * Infinitive2 : Yapma
        */
        MorphologicalTag::INFINITIVE2,
        /*
         * Negative : Yapama
        */
        MorphologicalTag::NEGATIVE,
        /*
         * Act of : Aldatmaca
        */
        MorphologicalTag::ACTOF,
        /*
         * Without having done so : Çaktırmadan
        */
        MorphologicalTag::WITHOUTHAVINGDONESO,
        /*
         * Infinitive : Yapmak
        */
        MorphologicalTag::INFINITIVE,
        /*
         * Without having done so
        */
        MorphologicalTag::WITHOUTHAVINGDONESO,
        /*
         * Progressive2 : Görmekte
        */
        MorphologicalTag::PROGRESSIVE2,
        /*
         * Necessity : Yapmalı
        */
        MorphologicalTag::NECESSITY,
        /*
         * Notable state : Anlaşılmazlık
        */
        MorphologicalTag::NOTABLESTATE,
        /*
         * Narrative Narrative Past Tense : Oluşmuş
        */
        MorphologicalTag::NARRATIVE,
        /*
         * 2nd person singuular agreement : Sen gelirsin
        */
        MorphologicalTag::A2SG,
        /*
         * Passive
        */
        MorphologicalTag::PASSIVE,
        /*
         * Dative case : Bana
        */
        MorphologicalTag::DATIVE,
        /*
         * Equative
        */
        MorphologicalTag::EQUATIVE,
        /*
         * Locative
        */
        MorphologicalTag::LOCATIVE,
        /*
         * Ablative
        */
        MorphologicalTag::ABLATIVE,
        /*
         * Accusatıve : Beni
        */
        MorphologicalTag::ACCUSATIVE,
        /*
         * Genitive : Benim
        */
        MorphologicalTag::GENITIVE,
        /*
         * 2nd person plural agreement : Siz gelirsiniz
        */
        MorphologicalTag::A2PL,
        /*
         * 3rd person plural agreement
        */
        MorphologicalTag::A3PL,
        /*
         * Desire/Past Auxiliary : Çıkarsa
        */
        MorphologicalTag::DESIRE,
        /*
         * Related to : Davranışsal
        */
        MorphologicalTag::RELATED,
        /*
         * 3rd person singular possessive
        */
        MorphologicalTag::P3SG,
        /*
         * Just like : Destansı
        */
        MorphologicalTag::JUSTLIKE,
        /*
         * Almost : Dikensi
        */
        MorphologicalTag::ALMOST,
        /*
         * 2nd person singular agreement
        */
        MorphologicalTag::A2SG,
        /*
         * 2nd person plural agreement
        */
        MorphologicalTag::A2PL,
        /*
         * 3rd person plural agreement
        */
        MorphologicalTag::A3PL,
        /*
         * Without : Dikişsiz
        */
        MorphologicalTag::WITHOUT,
        /*
         * Distributive : altışar
        */
        MorphologicalTag::DISTRIBUTIVE,
        /*
         * Causitive form
        */
        MorphologicalTag::CAUSATIVE,
        /*
         * Dative case
        */
        MorphologicalTag::DATIVE,
        /*
         * Optative : Doğanaya
        */
        MorphologicalTag::OPTATIVE,
        /*
         * Ability, possibility : Olabilir
        */
        MorphologicalTag::ABLE,
        /*
         * Future participle : Gülecek
        */
        MorphologicalTag::FUTUREPARTICIPLE,
        /*
         * Future : Yağacak
        */
        MorphologicalTag::FUTURE,
        /*
         * Continuous : Yapadur
        */
        MorphologicalTag::REPEAT,
        /*
         * Ever since : Çıkagel
        */
        MorphologicalTag::EVERSINCE,
        /*
         * Since doing so : Amasyalı
        */
        MorphologicalTag::SINCEDOINGSO,
        /*
         * Not able state : Anlaşılmazlık
        */
        MorphologicalTag::NOTABLESTATE,
        /*
         * Wıthout beıng able to have done so: kararlamadan
        */
        MorphologicalTag::WITHOUTBEINGABLETOHAVEDONESO,
        /*
         * Present participle : Sarılan
        */
        MorphologicalTag::PRESENTPARTICIPLE,
        /*
         * By doing so : Zıplayarak
        */
        MorphologicalTag::BYDOINGSO,
        /*
         * Projection : Öpülesi
        */
        MorphologicalTag::FEELLIKE,
        /*
         * Past tense : Gitti
        */
        MorphologicalTag::PASTTENSE,
        /*
         * Accusative
        */
        MorphologicalTag::ACCUSATIVE,
        /*
         * Agent
        */
        MorphologicalTag::AGENT,
        /*
         * 1st person singular agreement
        */
        MorphologicalTag::A1SG,
        /*
         * 2nd person plural agreement
        */
        MorphologicalTag::A2PL,
        /*
         * When : Okuyunca
        */
        MorphologicalTag::WHEN,
        /*
         * After doing so : Yapıp
        */
        MorphologicalTag::AFTERDOINGSO,
        /*
         * Infinitive : Yapış
        */
        MorphologicalTag::INFINITIVE3,
        /*
         * Hastility : Yapıver
        */
        MorphologicalTag::HASTILY,
        /*
         * 1st person plural agreement
        */
        MorphologicalTag::A1PL,
        /*
         * While : Gelirken
        */
        MorphologicalTag::WHILE,
        /*
         * Instrumental : Kışın, arabayla
        */
        MorphologicalTag::INSTRUMENTAL,
        /*
         * Narrative
        */
        MorphologicalTag::NARRATIVE,
        /*
         * Conditional : Gelirse
        */
        MorphologicalTag::CONDITIONAL,
        /*
         * 3rd person singuular agreement : O gelir
        */
        MorphologicalTag::A3SG,
        /*
         * 2nd person singuular agreement
        */
        MorphologicalTag::A2SG,
        /*
         * 2nd person plural agreement
        */
        MorphologicalTag::A2PL,
        /*
         * 3rd person plural agreement
        */
        MorphologicalTag::A3PL,
        /*
         * Stay : Bakakal
        */
        MorphologicalTag::STAY,
        /*
         * Start : Alıkoy
        */
        MorphologicalTag::START,
        /*
         * Repeat : Yapagör
        */
        MorphologicalTag::REPEAT];

    private array $metaMorphemeList;
    private Word $root;

    /**
     * The getMetaMorphemeTag method takes a String tag as an input and takes the first char of the tag. If first char
     * is a punctuation it gets a substring from the tag. And gets the meta morphemes of this tag then adds to the
     * result {@link ArrayList}.
     *
     * @param string $tag String to get meta morphemes from.
     * @return array type result which holds meta morphemes.
     */
    public static function getMetaMorphemeTag(string $tag): array {
        $result = [];
        $s = mb_substr($tag, 0, 1);
        if (Word::isPunctuationSymbol($s)) {
            $tag = mb_substr($tag, 1);
        }
        for ($j = 0; $j < count(MetamorphicParse::$metaMorphemes); $j++) {
            if ($tag == MetamorphicParse::$metaMorphemes[$j]) {
                $result[] = MetamorphicParse::$morphotacticTags[$j];
            }
        }
        return $result;
    }

    /**
     * Another getMetaMorphemeTag method which also takes parse as an input. It also checks the morphotactic tags.
     *
     * @param MorphologicalParse $parse MorphologicalParse type input.
     * @param string $tag   String to get meta morphemes from.
     * @return array type result which holds meta morphemes.
     */
    public static function getMetaMorphemeTagForParse(MorphologicalParse $parse, string $tag): array {
        $result = [];
        $s = mb_substr($tag, 0, 1);
        if (Word::isPunctuationSymbol($s)) {
            $tag = mb_substr($tag, 1);
        }
        for ($j = 0; $j < count(MetamorphicParse::$metaMorphemes); $j++) {
            if ($tag == MetamorphicParse::$metaMorphemes[$j] && $parse->containsTag(MetamorphicParse::$morphotacticTags[$j])) {
                $result[] = MetamorphicParse::$morphotacticTags[$j];
            }
        }
        return $result;
    }

    /**
     * The getter method for Private Word root.
     *
     * @return Word Word type root.
     */
    public function getWord(): Word {
        return $this->root;
    }

    /**
     * A constructor of {@link MetamorphicParse} class which creates an {@link ArrayList} metaMorphemeList which has split words
     * according to +.
     *
     * @param string $parse String to parse.
     */
    public function __construct(string $parse) {
        $this->metaMorphemeList = [];
        if ($parse == "+") {
            $this->root = new Word("+");
        } else {
            $words = explode("+", $parse);
            $this->root = new Word($words[0]);
            for ($i = 1; $i < count($words); $i++){
                $this->metaMorphemeList[] = $words[$i];
            }
        }
    }

    /**
     * Given a combined meta-suffix such as 'yDHlAr', it returns an array list of meta-suffixes as 'yDH' and 'lAr'
     * @param string $combinedSuffix A string of combined meta suffixes
     * @return array Returns an array list of meta-suffixes
     */
    public static function createWithList(string $combinedSuffix): array {
        $list = [];
        if ($combinedSuffix == "HnA") {
            $list[]  = "H";
            $list[] = "nA";
            return $list;
        }
        while (mb_strlen($combinedSuffix) != 0) {
            for ($i = mb_strlen($combinedSuffix); $i >= 0; $i--) {
                $found = false;
                foreach (MetamorphicParse::$metaMorphemes as $metaMorpheme) {
                    if (mb_substr($combinedSuffix, 0, $i) == $metaMorpheme) {
                        $list[] = mb_substr($combinedSuffix, 0, $i);
                        $combinedSuffix = mb_substr($combinedSuffix, $i);
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    break;
                }
            }
        }
        return $list;
    }

    /**
     * The size method returns the size of the metaMorphemeList.
     *
     * @return int the size of the metaMorphemeList.
     */
    public function size(): int {
        return count($this->metaMorphemeList) + 1;
    }

    /**
     * The addMetaMorphemeList method splits input String by + and add to the metaMorphemeList.
     *
     * @param string $newTacticSet String to add the metaMorphemeList.
     */
    public function addMetaMorphemeList(string $newTacticSet): void {
        $tactics = explode("+", $newTacticSet);
        $this->metaMorphemeList[] = $tactics;
    }

    /**
     * The removeMetaMorphemeFromIndex method removes the meta morpheme at given index from metaMorphemeList.
     *
     * @param int $index to remove from metaMorphemeList.
     */
    public function removeMetaMorphemeFromIndex(int $index) {
        array_splice($this->metaMorphemeList, $index, count($this->metaMorphemeList) - $index);
    }

    /**
     * The getMetaMorpheme method gets the meta morpheme at given index.
     *
     * @param int $index is used to get the meta morpheme.
     * @return string metaMorphemeList's corresponding meta morpheme.
     */
    public function getMetaMorpheme(int $index): string {
        if ($index == 0) {
            return $this->root->getName();
        } else {
            return $this->metaMorphemeList[$index - 1];
        }
    }

    /**
     * Overridden toString method to return resulting meta morphemes in metaMorphemeList.
     *
     * @return string String result.
     */
    public function __toString(): string {
        $result = $this->root->getName();
        foreach ($this->metaMorphemeList as $metaMorpheme) {
            $result.= "+" . $metaMorpheme;
        }
        return $result;
    }

}