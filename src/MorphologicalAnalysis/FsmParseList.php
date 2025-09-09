<?php

namespace olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis;

use Collator;
use olcaytaner\Dictionary\Dictionary\Word;

class FsmParseList
{
    private array $fsmParses;
    private static array $longestRootExceptions = [
        "acağı acak NOUN VERB", "acağım acak NOUN VERB", "acağımı acak NOUN VERB", "acağımız acak NOUN VERB",
        "acağın acak NOUN VERB",
        "acağına acak NOUN VERB", "acağını acak NOUN VERB", "acağının acak NOUN VERB", "acağınız acak NOUN VERB",
        "acağınıza acak NOUN VERB",
        "acağınızdır acak NOUN VERB", "acağınızı acak NOUN VERB", "acağınızın acak NOUN VERB", "acağız acak NOUN VERB",
        "acakları acak NOUN VERB",
        "acaklarını acak NOUN VERB", "acaksa acak NOUN VERB", "acaktır acak NOUN VERB", "ardım ar NOUN VERB",
        "arız ar NOUN VERB",
        "arken ar NOUN VERB", "arsa ar NOUN VERB", "arsak ar NOUN VERB", "arsanız ar NOUN VERB", "arsınız ar NOUN VERB",
        "eceği ecek NOUN VERB", "eceğim ecek NOUN VERB", "eceğimi ecek NOUN VERB", "eceğimiz ecek NOUN VERB",
        "eceğin ecek NOUN VERB",
        "eceğine ecek NOUN VERB", "eceğini ecek NOUN VERB", "eceğinin ecek NOUN VERB", "eceğiniz ecek NOUN VERB",
        "eceğinizdir ecek NOUN VERB",
        "eceğinize ecek NOUN VERB", "eceğinizi ecek NOUN VERB", "eceğinizin ecek NOUN VERB", "eceğiz ecek NOUN VERB",
        "ecekleri ecek NOUN VERB",
        "eceklerini ecek NOUN VERB", "ecekse ecek NOUN VERB", "ecektir ecek NOUN VERB", "erdim er NOUN VERB",
        "eriz er NOUN VERB",
        "erken er NOUN VERB", "erse er NOUN VERB", "ersek er NOUN VERB", "erseniz er NOUN VERB", "ersiniz er NOUN VERB",
        "ilen i VERB VERB", "ilene i VERB VERB", "ilin i VERB VERB", "ilince i VERB VERB", "imiz i ADJ NOUN",
        "in i ADJ NOUN", "inde i ADJ NOUN", "ine i ADJ NOUN", "ini i ADJ NOUN", "inin i ADJ NOUN",
        "ılan ı NOUN VERB", "ılana ı NOUN VERB", "ılın ı NOUN VERB", "ılınca ı NOUN VERB", "la la VERB NOUN",
        "lar la VERB NOUN", "lardan la VERB NOUN", "lardandır la VERB NOUN", "lardır la VERB NOUN", "ları la VERB NOUN",
        "larıdır la VERB NOUN", "larım la VERB NOUN", "larımda la VERB NOUN", "larımdan la VERB NOUN", "larımız la VERB NOUN",
        "larımıza la VERB NOUN",
        "larımızda la VERB NOUN", "larımızdan la VERB NOUN", "larımızdaydı la VERB NOUN", "larımızı la VERB NOUN",
        "larımızın la VERB NOUN",
        "larımızla la VERB NOUN", "ların la VERB NOUN", "larına la VERB NOUN", "larında la VERB NOUN",
        "larındaki la VERB NOUN",
        "larındakiler la VERB NOUN", "larındakilere la VERB NOUN", "larındakileri la VERB NOUN",
        "larındakilerin la VERB NOUN", "larından la VERB NOUN",
        "larındandır la VERB NOUN", "larındaysa la VERB NOUN", "larını la VERB NOUN", "larının la VERB NOUN",
        "larınız la VERB NOUN",
        "larınıza la VERB NOUN", "larınızda la VERB NOUN", "larınızdaki la VERB NOUN", "larınızdan la VERB NOUN",
        "larınızı la VERB NOUN",
        "larınızın la VERB NOUN", "larınızla la VERB NOUN", "larıyla la VERB NOUN", "le le VERB NOUN",
        "ler le VERB NOUN",
        "lerden le VERB NOUN", "lerdendir le VERB NOUN", "lerdir le VERB NOUN", "leri le VERB NOUN",
        "leridir le VERB NOUN",
        "lerim le VERB NOUN", "lerimden le VERB NOUN", "lerimiz le VERB NOUN", "lerimizde le VERB NOUN",
        "lerimizden le VERB NOUN",
        "lerimizdeydi le VERB NOUN", "lerimize le VERB NOUN", "lerimizi le VERB NOUN", "lerimizin le VERB NOUN",
        "lerimizle le VERB NOUN",
        "lerin le VERB NOUN", "lerinde le VERB NOUN", "lerindeki le VERB NOUN", "lerindekiler le VERB NOUN",
        "lerindekilere le VERB NOUN",
        "lerindekileri le VERB NOUN", "lerindekilerin le VERB NOUN", "lerinden le VERB NOUN",
        "lerindendir le VERB NOUN", "lerindeyse le VERB NOUN",
        "lerine le VERB NOUN", "lerini le VERB NOUN", "lerinin le VERB NOUN", "leriniz le VERB NOUN",
        "lerinizde le VERB NOUN",
        "lerinizdeki le VERB NOUN", "lerinizden le VERB NOUN", "lerinize le VERB NOUN", "lerinizi le VERB NOUN",
        "lerinizin le VERB NOUN",
        "lerinizle le VERB NOUN", "leriyle le VERB NOUN", "madan ma NOUN VERB", "malı ma NOUN VERB",
        "malıdır ma NOUN VERB", "malıdırlar ma NOUN VERB", "malılar ma NOUN VERB", "malısınız ma NOUN VERB",
        "malıyım ma NOUN VERB",
        "malıyız ma NOUN VERB", "mam ma NOUN VERB", "mama ma NOUN VERB", "mamız ma NOUN VERB", "mamıza ma NOUN VERB",
        "mamızı ma NOUN VERB", "manız ma NOUN VERB", "manızda ma NOUN VERB", "manızdır ma NOUN VERB",
        "manızı ma NOUN VERB",
        "manızla ma NOUN VERB", "ması ma NOUN VERB", "masıdır ma NOUN VERB", "masın ma NOUN VERB",
        "masına ma NOUN VERB",
        "masında ma NOUN VERB", "masındaki ma NOUN VERB", "masını ma NOUN VERB", "masıyla ma NOUN VERB",
        "mdan m NOUN NOUN",
        "meden me NOUN VERB", "meli me NOUN VERB", "melidir me NOUN VERB", "melidirler me NOUN VERB",
        "meliler me NOUN VERB",
        "melisiniz me NOUN VERB", "meliyim me NOUN VERB", "meliyiz me NOUN VERB", "mem me NOUN VERB",
        "meme me NOUN VERB",
        "memiz me NOUN VERB", "memize me NOUN VERB", "memizi me NOUN VERB", "meniz me NOUN VERB",
        "menizde me NOUN VERB",
        "menizdir me NOUN VERB", "menizi me NOUN VERB", "menizle me NOUN VERB", "mesi me NOUN VERB",
        "mesidir me NOUN VERB",
        "mesin me NOUN VERB", "mesinde me NOUN VERB", "mesindeki me NOUN VERB", "mesine me NOUN VERB",
        "mesini me NOUN VERB",
        "mesiyle me NOUN VERB", "mişse miş NOUN VERB", "mını m NOUN NOUN", "mışsa mış NOUN VERB", "mız m NOUN NOUN",
        "na n NOUN NOUN", "ne n NOUN NOUN", "nin n NOUN NOUN", "niz n NOUN NOUN",
        "nın n NOUN NOUN", "nız n NOUN NOUN", "rdim r NOUN VERB", "rdım r NOUN VERB", "riz r NOUN VERB",
        "rız r NOUN VERB", "rken r NOUN VERB", "rken r NOUN VERB", "rsa r NOUN VERB", "rsak r NOUN VERB",
        "rsanız r NOUN VERB", "rse r NOUN VERB", "rsek r NOUN VERB", "rseniz r NOUN VERB", "rsiniz r NOUN VERB",
        "rsınız r NOUN VERB", "sa sa VERB ADJ", "se se VERB ADJ", "ulan u NOUN VERB", "un un VERB NOUN",
        "üne ün VERB NOUN", "unun un VERB NOUN", "ince i NOUN VERB", "unca u NOUN VERB", "ınca ı NOUN VERB",
        "unca un NOUN VERB", "ilen ile VERB VERB"];

    static function transitionCompare(FsmParse $fsmParse1, FsmParse $fsmParse2): int
    {
        $collator = new Collator('tr');
        $s1 = $fsmParse1->getFsmParseTransitionList();
        $s2 = $fsmParse2->getFsmParseTransitionList();
        return $collator->compare($s1, $s2);
    }

    /**
     * A constructor of {@link FsmParseList} class which takes an {@link Array} fsmParses as an input. First it sorts
     * the items of the {@link Array} then loops through it, if the current item's transitions equal to the next item's
     * transitions, it removes the latter item. At the end, it assigns this {@link Array} to the fsmParses variable.
     *
     * @param array $fsmParses {@link FsmParse} type{@link Array} input.
     */
    public function __construct(array $fsmParses){
        usort($fsmParses, [FsmParseList::class, "transitionCompare"]);
        for ($i = 0; $i < count($fsmParses) - 1; $i++) {
            if ($fsmParses[$i]->getFsmParseTransitionList() == $fsmParses[$i + 1]->getFsmParseTransitionList()) {
                array_splice($fsmParses, $i + 1, 1);
                $i--;
            }
        }
        $this->fsmParses = $fsmParses;
    }

    /**
     * The size method returns the size of fsmParses {@link Array}.
     *
     * @return int the size of fsmParses {@link Array}.
     */
    public function size(): int
    {
        return count($this->fsmParses);
    }

    /**
     * The getFsmParse method takes an integer index as an input and returns the item of fsmParses {@link Array} at
     * given index.
     *
     * @param int $index Integer input.
     * @return FsmParse the item of fsmParses {@link Array} at given index.
     */
    public function getFsmParse(int $index): FsmParse
    {
        return $this->fsmParses[$index];
    }

    /**
     * The rootWords method gets the first item's root of fsmParses {@link Array} and uses it as currentRoot. Then loops
     * through the fsmParses, if the current item's root does not equal to the currentRoot, it then assigns it as the
     * currentRoot and accumulates root words in a {@link String} result.
     *
     * @return string String result that has root words.
     */
    public function rootWords(): string
    {
        $result = $this->fsmParses[0]->getWord()->getName();
        $currentRoot = $result;
        for ($i = 1; $i < count($this->fsmParses); $i++) {
            if ($this->fsmParses[$i]->getWord()->getName() != $currentRoot) {
                $currentRoot = $this->fsmParses[$i]->getWord()->getName();
                $result = $result . "$" . $currentRoot;
            }
        }
        return $result;
    }

    /**
     * The reduceToParsesWithSameRootAndPos method takes a {@link Word} currentWithPos as an input and loops i times till
     * i equals to the size of the fsmParses {@link Array}. If the given currentWithPos does not equal to the ith item's
     * root and the MorphologicalTag of the first inflectional of fsmParses, it removes the ith item from the {@link Array}.
     *
     * @param Word $currentWithPos {@link Word} input.
     */
    public function reduceToParsesWithSameRootAndPos(Word $currentWithPos): void
    {
        $i = 0;
        while ($i < count($this->fsmParses)) {
            if ($this->fsmParses[$i]->getWordWithPos()->getName() != $currentWithPos->getName()) {
                array_splice($this->fsmParses, $i, 1);
            } else {
                $i++;
            }
        }
    }

    /**
     * The getParseWithLongestRootWord method returns the parse with the longest root word. If more than one parse has the
     * longest root word, the first parse with that root is returned. If the longest root word belongs to an
     * exceptional case, the parse with the next longest root word that does not, is returned.
     *
     * @return FsmParse Parse with the longest root word.
     */
    public function getParseWithLongestRootWord(): FsmParse
    {
        $maxLength = -1;
        $bestParse = null;
        if (count($this->fsmParses) > 0) {
            $bestParse = $this->fsmParses[0];
        }
        foreach ($this->fsmParses as $currentParse) {
            if (mb_strlen($currentParse->getWord()->getName()) > $maxLength && !$this->isLongestRootException($currentParse)) {
                $maxLength = mb_strlen($currentParse->getWord()->getName());
                $bestParse = $currentParse;
            }
        }
        return $bestParse;
    }

    /**
     * The isLongestRootException method returns true if the longest root word belongs to an exceptional case, false otherwise.
     *
     * @param FsmParse $fsmParse {@link FsmParse} input.
     * @return bool true if the longest root belongs to an exceptional case, false otherwise.
     */
    public function isLongestRootException(FsmParse $fsmParse): bool
    {
        $surfaceForm = $fsmParse->getSurfaceForm();
        $root = $fsmParse->getWord()->getName();
        foreach (FsmParseList::$longestRootExceptions as $longestRootException) {
            $exceptionItems = explode(" ", $longestRootException);
            $surfaceFormEnding = $exceptionItems[0];
            $longestRootEnding = $exceptionItems[1];
            $longestRootPos = $exceptionItems[2];
            $possibleRootPos = $exceptionItems[3];
            $possibleRoot = str_replace($surfaceFormEnding, "", $surfaceForm);
            if (str_ends_with($surfaceForm, $surfaceFormEnding) && str_ends_with($root, $longestRootEnding) &&
                $fsmParse->getRootPos() == $longestRootPos) {
                foreach ($this->fsmParses as $currentParse) {
                    if ($currentParse->getWord()->getName() == $possibleRoot && $currentParse->getRootPos() == $possibleRootPos) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * The reduceToParsesWithSameRoot method takes a {@link String} currentWithPos as an input and loops i times till
     * i equals to the size of the fsmParses {@link Array}. If the given currentRoot does not equal to the root of ith
     * item of fsmParses, it removes the ith item from the {@link Array}.
     *
     * @param string $currentRoot {@link String} input.
     */
    public function reduceToParsesWithSameRoot(string $currentRoot): void
    {
        $i = 0;
        while ($i < count($this->fsmParses)) {
            if ($this->fsmParses[$i]->getWord()->getName() != $currentRoot) {
                array_splice($this->fsmParses, $i, 1);
            } else {
                $i++;
            }
        }
    }

    /**
     * The constructParseListForDifferentRootWithPos method initially creates a result {@link Array} then loops through the
     * fsmParses {@link Array}. For the first iteration, it creates new {@link Array} as initial, then adds the
     * first item od fsmParses to initial and also add this initial {@link Array} to the result {@link Array}.
     * For the following iterations, it checks whether the current item's root with the MorphologicalTag of the first inflectional
     * equal to the previous item's  root with the MorphologicalTag of the first inflectional. If so, it adds that item
     * to the result {@link Array}, if not it creates new {@link Array} as initial and adds the first item od fsmParses
     * to initial and also add this initial {@link Array} to the result {@link Array}.
     *
     * @return array result {@link Array} type of {@link FsmParseList}.
     */
    public function constructParseListForDifferentRootWithPos(): array
    {
        $result = [];
        $i = 0;
        while ($i < count($this->fsmParses)) {
            if ($i == 0) {
                $initial = [];
                $initial[] = $this->fsmParses[$i];
                $result[] = new FsmParseList($initial);
            } else {
                if ($this->fsmParses[$i]->getWordWithPos()->getName() == $this->fsmParses[$i - 1]->getWordWithPos()->getName()) {
                    $result[count($result) - 1]->fsmParses[] = $this->fsmParses[$i];
                } else {
                    $initial = [];
                    $initial[] = $this->fsmParses[$i];
                    $result[] = new FsmParseList($initial);
                }
            }
            $i++;
        }
        return $result;
    }

    /**
     * The parsesWithoutPrefixAndSuffix method first creates a {@link String} array named analyses with the size of
     * fsmParses {@link Array}'s size.
     * <p>
     * If the size is just 1, it then returns the first item's transitionList, if it is greater than 1, loops through
     * the fsmParses and puts the transitionList of each item to the analyses array.
     * <p>
     * If the removePrefix condition holds, it loops through the analyses array and takes each item's substring after
     * the first + sign and updates that item of analyses array with that substring.
     * <p>
     * If the removeSuffix condition holds, it loops through the analyses array and takes each item's substring till the
     * last + sign and updates that item of analyses array with that substring.
     * <p>
     * It then removes the duplicate items of analyses array and returns a result {@link String} that has the
     * accumulated items of analyses array.
     *
     * @return string result {@link String} that has the accumulated items of analyses array.
     */
    public function parsesWithoutPrefixAndSuffix(): string
    {
        $analyses = [];
        $removePrefix = true;
        $removeSuffix = true;
        if (count($this->fsmParses) == 1) {
            return mb_substr($this->fsmParses[0]->getFsmParseTransitionList(), mb_strpos($this->fsmParses[0]->getFsmParseTransitionList(), "+") + 1);
        }
        for ($i = 0; $i < count($this->fsmParses); $i++) {
            $analyses[] = $this->fsmParses[$i]->getFsmParseTransitionList();
        }
        while ($removePrefix) {
            $removePrefix = true;
            for ($i = 0; $i < count($this->fsmParses) - 1; $i++) {
                if (!str_contains($analyses[$i], "+") || !str_contains($analyses[$i + 1], "+") ||
                    mb_substr($analyses[$i], 0, mb_strpos($analyses[$i], "+") + 1) != mb_substr($analyses[$i + 1], 0, mb_strpos($analyses[$i + 1], "+") + 1)) {
                    $removePrefix = false;
                    break;
                }
            }
            if ($removePrefix) {
                for ($i = 0; $i < count($this->fsmParses); $i++) {
                    $analyses[$i] = mb_substr($analyses[$i], mb_strpos($analyses[$i], "+") + 1);
                }
            }
        }
        while ($removeSuffix) {
            $removeSuffix = true;
            for ($i = 0; $i < count($this->fsmParses) - 1; $i++) {
                if (!str_contains($analyses[$i], "+") || !str_contains($analyses[$i + 1], "+") ||
                    mb_substr($analyses[$i], mb_strrpos($analyses[$i], "+")) != mb_substr($analyses[$i + 1], mb_strrpos($analyses[$i + 1], "+"))) {
                    $removeSuffix = false;
                    break;
                }
            }
            if ($removeSuffix) {
                for ($i = 0; $i < count($this->fsmParses); $i++) {
                    $analyses[$i] = mb_substr($analyses[$i], 0, mb_strrpos($analyses[$i], "+"));
                }
            }
        }
        for ($i = 0; $i < count($analyses); $i++) {
            for ($j = $i + 1; $j < count($analyses); $j++) {
                if ($analyses[$i] > $analyses[$j]) {
                    $tmp = $analyses[$i];
                    $analyses[$i] = $analyses[$j];
                    $analyses[$j] = $tmp;
                }
            }
        }
        $result = $analyses[0];
        for ($i = 1; $i < count($analyses); $i++) {
            $result = $result . "$" . $analyses[$i];
        }
        return $result;
    }

    /**
     * The overridden toString method loops through the fsmParses {@link Array} and accumulates the items to a result
     * {@link String}.
     *
     * @return string result {@link String} that has the items of fsmParses {@link Array}.
     */
    public function __toString(): string
    {
        $result = "";
        for ($i = 0; $i < count($this->fsmParses); $i++) {
            $result .= $this->fsmParses[$i] . "\n";
        }
        return $result;
    }
}