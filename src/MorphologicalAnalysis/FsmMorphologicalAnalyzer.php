<?php

namespace olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis;

use olcaytaner\Corpus\Sentence;
use olcaytaner\DataStructure\LRUCache;
use olcaytaner\DataStructure\Queue;
use olcaytaner\Dictionary\Dictionary\Trie\Trie;
use olcaytaner\Dictionary\Dictionary\TxtDictionary;
use olcaytaner\Dictionary\Dictionary\TxtWord;
use olcaytaner\Dictionary\Dictionary\Word;
use olcaytaner\Dictionary\Dictionary\WordComparator;
use olcaytaner\Util\FileUtils;
use Transliterator;

class FsmMorphologicalAnalyzer
{
    private Trie $dictionaryTrie;
    private Trie $suffixTrie;
    private ?array $parsedSurfaceForms = null;
    private ?array $pronunciations = null;
    private FiniteStateMachine $finiteStateMachine;
    private static int $MAX_DISTANCE = 2;

    private TxtDictionary $dictionary;

    private ?LRUCache $cache = null;

    /**
     * Another constructor of FsmMorphologicalAnalyzer class. It generates a new TxtDictionary type dictionary from
     * given input dictionary, with given inputs $fileName and cacheSize.
     *
     * @param string|null $fileName the file to read the finite state machine.
     * @param mixed $dictionaryFileNameOrDictionary the dictionary file that will be used to generate dictionaryTrie.
     * @param int|null $cacheSize the size of the LRUCache.
     */
    public function __construct(?string $fileName = null, mixed $dictionaryFileNameOrDictionary = null, ?int $cacheSize = null)
    {
        if ($dictionaryFileNameOrDictionary == null) {
            $this->dictionary = new TxtDictionary();
        } else {
            if ($dictionaryFileNameOrDictionary instanceof TxtDictionary) {
                $this->dictionary = $dictionaryFileNameOrDictionary;
            } else {
                $this->dictionary = new TxtDictionary(WordComparator::TURKISH, $dictionaryFileNameOrDictionary);
            }
        }
        if ($fileName == null) {
            $this->finiteStateMachine = new FiniteStateMachine();
        } else {
            $this->finiteStateMachine = new FiniteStateMachine($fileName);
        }
        $this->prepareSuffixTrie();
        $this->dictionaryTrie = $this->dictionary->prepareTrie();
        if ($cacheSize > 0) {
            $this->cache = new LRUCache($cacheSize);
        }
        $this->addPronunciations("../pronunciations.txt");
    }

    /**
     * Constructs and returns the reverse string of a given string.
     * @param $s String to be reversed.
     * @return string Reverse of a given string.
     */
    private function reverseString(string $s): string
    {
        $result = "";
        for ($i = mb_strlen($s) - 1; $i >= 0; $i--) {
            $result .= mb_substr($s, $i, 1);
        }
        return $result;
    }

    /**
     * Constructs the suffix trie from the input file suffixes.txt. suffixes.txt contains the most frequent 6000
     * suffixes that a verb or a noun can take. The suffix trie is a trie that stores these suffixes in reverse form,
     * which can be then used to match a given word for its possible suffix content.
     */
    private function prepareSuffixTrie(): void{
        $this->suffixTrie = new Trie();
        $fh = fopen("../suffixes.txt", 'r');
        while ($line = fgets($fh)) {
            $reverseSuffix = $this->reverseString(trim($line));
            $this->suffixTrie->addWord($reverseSuffix, new Word($reverseSuffix));
        }
        fclose($fh);
    }

    /**
     * Reads the file for correct surface forms and their most frequent $root forms, in other words, the surface forms
     * which have at least one morphological analysis in  Turkish.
     * @param string $fileName Input file containing analyzable surface forms and their $root forms.
     */
    function addParsedSurfaceForms(string $fileName): void
    {
        $this->parsedSurfaceForms = FileUtils::readHashMap($fileName);
    }

    /**
     * Reads the file for foreign words and their pronunciations.
     * @param string $fileName Input file containing foreign words and their pronunciations.
     */
    function addPronunciations(string $fileName): void
    {
        $this->pronunciations = FileUtils::readHashMap($fileName);
    }

    /**
     * The getPossibleWords method takes {@link MorphologicalParse} and {@link MetamorphicParse} as input.
     * First it determines whether the given $morphologicalParse is the $root verb and whether it contains a verb tag.
     * Then it creates new $transition with -mak and creates a new {@link Set} $result.
     * <p>
     * It takes the given {@link MetamorphicParse} input as $currentWord and if there is a compound word starting with the
     * $currentWord, it gets this $compoundWord from dictionaryTrie. If there is a $compoundWord and the difference of the
     * $currentWord and compundWords is less than 3 than $compoundWord is added to the $result, otherwise $currentWord is added.
     * <p>
     * Then it gets the $root from $parse input as a $currentRoot. If it is not null, and $morphologicalParse input is verb,
     * it directly adds the verb to $result after making $transition to $currentRoot with $currentWord String. Else, it creates a new
     * $transition with -lar and make this $transition then adds to the $result.
     *
     * @param MorphologicalParse $morphologicalParse {@link MorphologicalParse} type input.
     * @param MetamorphicParse|null $metamorphicParse              {@link MetamorphicParse} type input.
     * @return array {@link HashSet} $result.
     */
    function getPossibleWords(MorphologicalParse $morphologicalParse, ?MetamorphicParse $metamorphicParse): array
    {
        $isRootVerb = $morphologicalParse->getRootPos() == "VERB";
        $containsVerb = $morphologicalParse->containsTag(MorphologicalTag::VERB);
        $verbTransition = new Transition("mAk");
        $result = [];
        if ($metamorphicParse == null || $metamorphicParse->getWord() == null) {
            return $result;
        }
        $currentWord = $metamorphicParse->getWord()->getName();
        $pluralIndex = -1;
        $compoundWord = $this->dictionaryTrie->getCompoundWordStartingWith($currentWord);
        if (!$isRootVerb) {
            if ($compoundWord != null && mb_strlen($compoundWord->getName()) - mb_strlen($currentWord) < 3) {
                $result[] = $compoundWord->getName();
            }
            $result[] = $currentWord;
        }
        $currentRoot = $this->dictionary->getWordWithName($metamorphicParse->getWord()->getName());
        if ($currentRoot == null && $compoundWord != null) {
            $currentRoot = $compoundWord;
        }
        if ($currentRoot != null) {
            if ($isRootVerb) {
                $verbWord = $verbTransition->makeTransition($currentRoot, $currentWord);
                $result[] = $verbWord;
            }
            $pluralWord = null;
            for ($i = 1; $i < $metamorphicParse->size(); $i++) {
                $transition = new Transition($metamorphicParse->getMetaMorpheme($i), null, null);
                if ($metamorphicParse->getMetaMorpheme($i) == "lAr") {
                    $pluralWord = $currentWord;
                    $pluralIndex = $i + 1;
                }
                $currentWord = $transition->makeTransition($currentRoot, $currentWord);
                $result[] = $currentWord;
                if ($containsVerb) {
                    $verbWord = $verbTransition->makeTransition($currentRoot, $currentWord);
                    $result[] = $verbWord;
                }
            }
            if ($pluralWord != null) {
                $currentWord = $pluralWord;
                for ($i = $pluralIndex; $i < $metamorphicParse->size(); $i++) {
                    $transition = new Transition($metamorphicParse->getMetaMorpheme($i), null, null);
                    $currentWord = $transition->makeTransition($currentRoot, $currentWord);
                    $result[] = $currentWord;
                    if ($containsVerb) {
                        $verbWord = $verbTransition->makeTransition($currentRoot, $currentWord);
                        $result[] = $verbWord;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * The getDictionary method is used to get TxtDictionary.
     *
     * @return TxtDictionary TxtDictionary type dictionary.
     */
    function getDictionary(): TxtDictionary
    {
        return $this->dictionary;
    }

    /**
     * The getFiniteStateMachine method is used to get FiniteStateMachine.
     *
     * @return FiniteStateMachine FiniteStateMachine type finiteStateMachine.
     */
    function getFiniteStateMachine(): FiniteStateMachine
    {
        return $this->finiteStateMachine;
    }

    /**
     * The isPossibleSubstring method first checks whether given short and long strings are equal to $root word.
     * Then, compares both short and long strings' chars till the last two chars of short string. In the presence of mismatch,
     * false is returned. On the other hand, it counts the distance between two strings until it becomes greater than 2,
     * which is the MAX_DISTANCE also finds the index of the last char.
     * <p>
     * If the substring is a rootWord and equals to 'ben', which is a special case or $root holds the lastIdropsDuringSuffixation or
     * lastIdropsDuringPassiveSuffixation conditions, then it returns true if distance is not greater than MAX_DISTANCE.
     * <p>
     * On the other hand, if the shortStrong ends with one of these chars 'e, a, p, ç, t, k' and 't 's a rootWord with
     * the conditions of rootSoftenDuringSuffixation, vowelEChangesToIDuringYSuffixation, vowelAChangesToIDuringYSuffixation
     * or endingKChangesIntoG then it returns true if the last index is not equal to 2 and distance is not greater than
     * MAX_DISTANCE and false otherwise.
     *
     * @param string $shortString the possible substring.
     * @param string $longString the long string to compare with substring.
     * @param TxtWord $root the $root of the long string.
     * @return bool true if given substring is the actual substring of the $longString, false otherwise.
     */
    private function isPossibleSubstring(string $shortString, string $longString, TxtWord $root): bool
    {
        $rootWord = (($shortString == $root->getName()) || $longString == $root->getName());
        $distance = 0;
        $last = 1;
        for ($j = 0; $j < mb_strlen($shortString); $j++) {
            if (mb_substr($shortString, $j, 1) != mb_substr($longString, $j, 1)) {
                if ($j < mb_strlen($shortString) - 2) {
                    return false;
                }
                $last = mb_strlen($shortString) - $j;
                $distance++;
                if ($distance > FsmMorphologicalAnalyzer::$MAX_DISTANCE) {
                    break;
                }
            }
        }
        if ($rootWord && ($root->getName() == "ben" || $root->getName() == "sen" ||
                $root->lastIdropsDuringSuffixation() || $root->lastIdropsDuringPassiveSuffixation())) {
            return ($distance <= FsmMorphologicalAnalyzer::$MAX_DISTANCE);
        } else {
            if (str_ends_with($shortString, "e") || str_ends_with($shortString, "a") || str_ends_with($shortString, "p") ||
                str_ends_with($shortString, "ç") || str_ends_with($shortString, "t") || str_ends_with($shortString, "k") ||
                ($rootWord && ($root->rootSoftenDuringSuffixation() || $root->vowelEChangesToIDuringYSuffixation() ||
                        $root->vowelAChangesToIDuringYSuffixation() || $root->endingKChangesIntoG()))) {
                return ($last != 2 && $distance <= FsmMorphologicalAnalyzer::$MAX_DISTANCE - 1);
            } else {
                return ($distance <= FsmMorphologicalAnalyzer::$MAX_DISTANCE - 2);
            }
        }
    }

    /**
     * The initializeParseList method initializes the given given fsm ArrayList with given $root words by parsing them.
     * <p>
     * It checks many conditions;
     * isPlural; if $root holds the condition then it gets the state with the name of NominalRootPlural, then
     * creates a new parsing and adds this to the input $fsmParse Arraylist.
     * Ex : Açıktohumlular
     * <p>
     * !isPlural and isPortmanteauEndingWithSI, if $root holds the conditions then it gets the state with the
     * name of NominalRootNoPossesive.
     * Ex : Balarısı
     * <p>
     * !isPlural and isPortmanteau, if $root holds the conditions then it gets the state with the name of
     * CompoundNounRoot.
     * Ex : Aslanağızı
     * <p>
     * !isPlural, !isPortmanteau and isHeader, if $root holds the conditions then it gets the state with the
     * name of HeaderRoot.
     * Ex :  </title>
     * <p>
     * !isPlural, !isPortmanteau and isInterjection, if $root holds the conditions then it gets the state
     * with the name of InterjectionRoot.
     * Ex : Hey, Aa
     * <p>
     * !isPlural, !isPortmanteau and isDuplicate, if $root holds the conditions then it gets the state
     * with the name of DuplicateRoot.
     * Ex : Allak,
     * !isPlural, !isPortmanteau and isCode, if $root holds the conditions then it gets the state
     * with the name of CodeRoot.
     * Ex : 9400f,
     * <p>
     * !isPlural, !isPortmanteau and isMetric, if $root holds the conditions then it gets the state
     * with the name of MetricRoot.
     * Ex : 11x8x12,
     * <p>
     * !isPlural, !isPortmanteau and isNumeral, if $root holds the conditions then it gets the state
     * with the name of CardinalRoot.
     * Ex : Yüz, bin
     * <p>
     * !isPlural, !isPortmanteau and isReal, if $root holds the conditions then it gets the state
     * with the name of RealRoot.
     * Ex : 1.2
     * <p>
     * !isPlural, !isPortmanteau and isFraction, if $root holds the conditions then it gets the state
     * with the name of FractionRoot.
     * Ex : 1/2
     * <p>
     * !isPlural, !isPortmanteau and isDate, if $root holds the conditions then it gets the state
     * with the name of DateRoot.
     * Ex : 11/06/2018
     * <p>
     * !isPlural, !isPortmanteau and isPercent, if $root holds the conditions then it gets the state
     * with the name of PercentRoot.
     * Ex : %12.5
     * <p>
     * !isPlural, !isPortmanteau and isRange, if $root holds the conditions then it gets the state
     * with the name of RangeRoot.
     * Ex : 3-5
     * <p>
     * !isPlural, !isPortmanteau and isTime, if $root holds the conditions then it gets the state
     * with the name of TimeRoot.
     * Ex : 13:16:08
     * <p>
     * !isPlural, !isPortmanteau and isOrdinal, if $root holds the conditions then it gets the state
     * with the name of OrdinalRoot.
     * Ex : Altıncı
     * <p>
     * !isPlural, !isPortmanteau, and isVerb if $root holds the conditions then it gets the state
     * with the name of VerbalRoot. Or isPassive, then it gets the state with the name of PassiveHn.
     * Ex : Anla (!isPAssive)
     * Ex : Çağrıl (isPassive)
     * <p>
     * !isPlural, !isPortmanteau and isPronoun, if $root holds the conditions then it gets the state
     * with the name of PronounRoot. There are 6 different Pronoun state names, REFLEX, QUANT, QUANTPLURAL, DEMONS, PERS, QUES.
     * REFLEX = Reflexive Pronouns Ex : kendi
     * QUANT = Quantitative Pronouns Ex : öbür, hep, kimse, hiçbiri, bazı, kimi, biri
     * QUANTPLURAL = Quantitative Plural Pronouns Ex : tümü, çoğu, hepsi
     * DEMONS = Demonstrative Pronouns Ex : o, bu, şu
     * PERS = Personal Pronouns Ex : ben, sen, o, biz, siz, onlar
     * QUES = Interrogatıve Pronouns Ex : nere, ne, kim, hangi
     * <p>
     * !isPlural, !isPortmanteau and isAdjective, if $root holds the conditions then it gets the state
     * with the name of AdjectiveRoot.
     * Ex : Absürt, Abes
     * <p>
     * !isPlural, !isPortmanteau and isPureAdjective, if $root holds the conditions then it gets the state
     * with the name of Adjective.
     * Ex : Geçmiş, Cam
     * <p>
     * !isPlural, !isPortmanteau and isNominal, if $root holds the conditions then it gets the state
     * with the name of NominalRoot.
     * Ex : Görüş
     * <p>
     * !isPlural, !isPortmanteau and $isProper, if $root holds the conditions then it gets the state
     * with the name of ProperRoot.
     * Ex : Abdi
     * <p>
     * !isPlural, !isPortmanteau and isQuestion, if $root holds the conditions then it gets the state
     * with the name of QuestionRoot.
     * Ex : Mi, mü
     * <p>
     * !isPlural, !isPortmanteau and isDeterminer, if $root holds the conditions then it gets the state
     * with the name of DeterminerRoot.
     * Ex : Çok, bir
     * <p>
     * !isPlural, !isPortmanteau and isConjunction, if $root holds the conditions then it gets the state
     * with the name of ConjunctionRoot.
     * Ex : Ama , ancak
     * <p>
     * !isPlural, !isPortmanteau and isPostP, if $root holds the conditions then it gets the state
     * with the name of PostP.
     * Ex : Ait, dair
     * <p>
     * !isPlural, !isPortmanteau and isAdverb, if $root holds the conditions then it gets the state
     * with the name of AdverbRoot.
     * Ex : Acilen
     *
     * @param array $fsmParse ArrayList to initialize.
     * @param TxtWord $root word to check properties and add to $fsmParse according to them.
     * @param bool $isProper is used to check a word is proper or not.
     */
    private function initializeParseList(array &$fsmParse, TxtWord $root, bool $isProper): void
    {
        if ($root->isPlural()) {
            $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("NominalRootPlural"));
            $fsmParse[] = $currentFsmParse;
        } else {
            if ($root->isPortmanteauEndingWithSI()) {
                $currentFsmParse = new FsmParse(mb_substr($root->getName(), 0, mb_strlen($root->getName()) - 2), $this->finiteStateMachine->getState("CompoundNounRoot"));
                $fsmParse[] = $currentFsmParse;
                $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("NominalRootNoPossesive"));
                $fsmParse[] = $currentFsmParse;
            } else {
                if ($root->isPortmanteau()) {
                    if ($root->isPortmanteauFacedVowelEllipsis()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("NominalRootNoPossesive"));
                        $fsmParse[] = $currentFsmParse;
                        $currentFsmParse = new FsmParse(mb_substr($root->getName(), 0, mb_strlen($root->getName()) - 2) . mb_substr($root->getName(), mb_strlen($root->getName()) - 1) . mb_substr($root->getName(), mb_strlen($root->getName()) - 2), $this->finiteStateMachine->getState("CompoundNounRoot"));
                    } else {
                        if ($root->isPortmanteauFacedSoftening()) {
                            $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("NominalRootNoPossesive"));
                            $fsmParse[] = $currentFsmParse;
                            $currentFsmParse = match (mb_substr($root->getName(), mb_strlen($root->getName()) - 2)) {
                                'b' => new FsmParse(mb_substr($root->getName(), 0, mb_strlen($root->getName()) - 2) . 'p', $this->finiteStateMachine->getState("CompoundNounRoot")),
                                'c' => new FsmParse(mb_substr($root->getName(), 0, mb_strlen($root->getName()) - 2) . 'ç', $this->finiteStateMachine->getState("CompoundNounRoot")),
                                'd' => new FsmParse(mb_substr($root->getName(), 0, mb_strlen($root->getName()) - 2) . 't', $this->finiteStateMachine->getState("CompoundNounRoot")),
                                'ğ' => new FsmParse(mb_substr($root->getName(), 0, mb_strlen($root->getName()) - 2) . 'k', $this->finiteStateMachine->getState("CompoundNounRoot")),
                                default => new FsmParse(mb_substr($root->getName(), 0, mb_strlen($root->getName()) - 1), $this->finiteStateMachine->getState("CompoundNounRoot")),
                            };
                        } else {
                            $currentFsmParse = new FsmParse(mb_substr($root->getName(), 0, mb_strlen($root->getName()) - 1), $this->finiteStateMachine->getState("CompoundNounRoot"));
                        }
                    }
                    $fsmParse[] = $currentFsmParse;
                } else {
                    if ($root->isHeader()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("HeaderRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isInterjection()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("InterjectionRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isDuplicate()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("DuplicateRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isCode()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("CodeRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isMetric()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("MetricRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isNumeral()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("CardinalRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isReal()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("RealRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isFraction()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("FractionRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isDate()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("DateRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isPercent()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("PercentRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isRange()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("RangeRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isTime()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("TimeRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isOrdinal()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("OrdinalRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isVerb() || $root->isPassive()) {
                        if ($root->verbType() != "") {
                            $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("VerbalRoot(" . $root->verbType() . ")"));
                        } else {
                            if (!$root->isPassive()) {
                                $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("VerbalRoot"));
                            } else {
                                $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("PassiveHn"));
                            }
                        }
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isPronoun()) {
                        if ($root->getName() == "kendi") {
                            $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("PronounRoot(REFLEX)"));
                            $fsmParse[] = $currentFsmParse;
                        }
                        if ($root->getName() == "öbür" || $root->getName() == "öteki" || $root->getName() == "hep" || $root->getName() == "kimse" || $root->getName() == "diğeri" || $root->getName() == "hiçbiri" || $root->getName() == "böylesi" || $root->getName() == "birbiri" || $root->getName() == "birbirleri" || $root->getName() == "biri" || $root->getName() == "başkası" || $root->getName() == "bazı" || $root->getName() == "kimi") {
                            $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("PronounRoot(QUANT)"));
                            $fsmParse[] = $currentFsmParse;
                        }
                        if ($root->getName() == "tümü" || $root->getName() == "topu" || $root->getName() == "herkes" || $root->getName() == "cümlesi" || $root->getName() == "çoğu" || $root->getName() == "birçoğu" || $root->getName() == "birkaçı" || $root->getName() == "birçokları" || $root->getName() == "hepsi") {
                            $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("PronounRoot(QUANTPLURAL)"));
                            $fsmParse[] = $currentFsmParse;
                        }
                        if ($root->getName() == "o" || $root->getName() == "bu" || $root->getName() == "şu") {
                            $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("PronounRoot(DEMONS)"));
                            $fsmParse[] = $currentFsmParse;
                        }
                        if ($root->getName() == "ben" || $root->getName() == "sen" || $root->getName() == "o" || $root->getName() == "biz" || $root->getName() == "siz" || $root->getName() == "onlar") {
                            $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("PronounRoot(PERS)"));
                            $fsmParse[] = $currentFsmParse;
                        }
                        if ($root->getName() == "nere" || $root->getName() == "ne" || $root->getName() == "kaçı" || $root->getName() == "kim" || $root->getName() == "hangi") {
                            $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("PronounRoot(QUES)"));
                            $fsmParse[] = $currentFsmParse;
                        }
                    }
                    if ($root->isAdjective()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("AdjectiveRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isPureAdjective()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("Adjective"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isNominal()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("NominalRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isAbbreviation()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("NominalRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isProperNoun() && $isProper) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("ProperRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isQuestion()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("QuestionRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isDeterminer()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("DeterminerRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isConjunction()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("ConjunctionRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isPostP()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("PostP"));
                        $fsmParse[] = $currentFsmParse;
                    }
                    if ($root->isAdverb()) {
                        $currentFsmParse = new FsmParse($root, $this->finiteStateMachine->getState("AdverbRoot"));
                        $fsmParse[] = $currentFsmParse;
                    }
                }
            }
        }
    }

    /**
     * The initializeParseListFromRoot method is used to create an {@link Array} which consists of initial fsm parsings.
     * First, traverses this HashSet and uses each word as a $root and calls initializeParseList method with this $root
     * and Array.
     *
     * @param array $parseList ArrayList to initialize.
     * @param TxtWord $root the $root form to generate initial $parse list.
     * @param bool $isProper is used to check a word is proper or not.
     */
    private function initializeParseListFromRoot(array &$parseList, TxtWord $root, bool $isProper): void
    {
        $this->initializeParseList($parseList, $root, $isProper);
        if ($root->obeysAndNotObeysVowelHarmonyDuringAgglutination()) {
            $newRoot = clone $root;
            $newRoot->removeFlag("IS_UU");
            $newRoot->removeFlag("IS_UUU");
            $this->initializeParseList($parseList, $newRoot, $isProper);
        }
        if ($root->rootSoftenAndNotSoftenDuringSuffixation()) {
            $newRoot = clone $root;
            $newRoot->removeFlag("IS_SD");
            $newRoot->removeFlag("IS_SDD");
            $this->initializeParseList($parseList, $newRoot, $isProper);
        }
        if ($root->lastIDropsAndNotDropDuringSuffixation()) {
            $newRoot = clone $root;
            $newRoot->removeFlag("IS_UD");
            $newRoot->removeFlag("IS_UDD");
            $this->initializeParseList($parseList, $newRoot, $isProper);
        }
        if ($root->duplicatesAndNotDuplicatesDuringSuffixation()) {
            $newRoot = clone $root;
            $newRoot->removeFlag("IS_ST");
            $newRoot->removeFlag("IS_STT");
            $this->initializeParseList($parseList, $newRoot, $isProper);
        }
        if ($root->endingKChangesIntoG() && $root->containsFlag("IS_OA")) {
            $newRoot = clone $root;
            $newRoot->removeFlag("IS_OA");
            $this->initializeParseList($parseList, $newRoot, $isProper);
        }
    }

    /**
     * The initializeParseListFromSurfaceForm method is used to create an {@link Array} which consists of initial fsm parsings. First,
     * it calls getWordsWithPrefix methods by using input String $surfaceForm and generates a {@link Set}. Then, traverses
     * this HashSet and uses each word as a $root and calls initializeParseListFromRoot method with this $root and ArrayList.
     * <p>
     *
     * @param string $surfaceForm the String used to generate a HashSet of words.
     * @param bool $isProper is used to check a word is proper or not.
     * @return array ArrayList.
     */
    private function initializeParseListFromSurfaceForm(string $surfaceForm, bool $isProper): array
    {
        $initialFsmParse = [];
        if (mb_strlen($surfaceForm) == 0) {
            return $initialFsmParse;
        }
        $words = $this->dictionaryTrie->getWordsWithPrefix($surfaceForm);
        foreach ($words as $word) {
            $root = $word;
            $this->initializeParseListFromRoot($initialFsmParse, $root, $isProper);
        }
        return $initialFsmParse;
    }

    /**
     * The addNewParsesFromCurrentParse method initially gets the final suffixes from input $currentFsmParse called as currentState,
     * and by using the currentState information it gets the new analysis. Then loops through each currentState's $transition.
     * If the $currentTransition is possible, it makes the $transition.
     *
     * @param FsmParse $currentFsmParse $fsmParse type input.
     * @param Queue $fsmParse an ArrayList of $fsmParse.
     * @param int $maxLength mb_strlen($Maximum) of the $parse.
     * @param TxtWord $root            TxtWord used to make $transition.
     */
    private function addNewParsesFromCurrentParseLength(FsmParse $currentFsmParse, Queue $fsmParse,
                                                        int      $maxLength, TxtWord $root): void
    {
        $currentState = $currentFsmParse->getFinalSuffix();
        $currentSurfaceForm = $currentFsmParse->getSurfaceForm();
        foreach ($this->finiteStateMachine->getTransitions($currentState) as $currentTransition) {
            if ($currentTransition->transitionPossibleFromParse($currentFsmParse) && ($currentSurfaceForm != $root->getName() ||
                    ($currentSurfaceForm == $root->getName() && $currentTransition->transitionPossibleFromRoot($root, $currentState)))) {
                $tmp = $currentTransition->makeTransition($root, $currentSurfaceForm, $currentFsmParse->getStartState());
                if (mb_strlen($tmp) <= $maxLength) {
                    $newFsmParse = clone $currentFsmParse;
                    $newFsmParse->addSuffix($currentTransition->toState(), $tmp, $currentTransition->getWith(),
                        $currentTransition->__toString(), $currentTransition->toPos());
                    $newFsmParse->setAgreement($currentTransition->getWith());
                    $fsmParse->enqueue($newFsmParse);
                }
            }
        }
    }

    /**
     * The addNewParsesFromCurrentParse method initially gets the final suffixes from input $currentFsmParse called as currentState,
     * and by using the currentState information it gets the $currentSurfaceForm. Then loops through each currentState's $transition.
     * If the $currentTransition is possible, it makes the $transition
     *
     * @param FsmParse $currentFsmParse $fsmParse type input.
     * @param Queue $fsmParse an ArrayList of $fsmParse.
     * @param string $surfaceForm     String to use during $transition.
     * @param TxtWord $root            TxtWord used to make $transition.
     */
    private function addNewParsesFromCurrentParseSurfaceForm(FsmParse $currentFsmParse, Queue $fsmParse,
                                                             string   $surfaceForm, TxtWord $root): void
    {
        $currentState = $currentFsmParse->getFinalSuffix();
        $currentSurfaceForm = $currentFsmParse->getSurfaceForm();
        foreach ($this->finiteStateMachine->getTransitions($currentState) as $currentTransition) {
            if ($currentTransition->transitionPossible($currentFsmParse->getSurfaceForm(), $surfaceForm) && $currentTransition->transitionPossibleFromParse($currentFsmParse) && ($currentSurfaceForm != $root->getName() || ($currentSurfaceForm == $root->getName() && $currentTransition->transitionPossibleFromRoot($root, $currentState)))) {
                $tmp = $currentTransition->makeTransition($root, $currentSurfaceForm, $currentFsmParse->getStartState());
                if ((mb_strlen($tmp) < mb_strlen($surfaceForm) && $this->isPossibleSubstring($tmp, $surfaceForm, $root)) || (mb_strlen($tmp) == mb_strlen($surfaceForm) && ($root->lastIdropsDuringSuffixation() || ($tmp == $surfaceForm)))) {
                    $newFsmParse = clone $currentFsmParse;
                    $newFsmParse->addSuffix($currentTransition->toState(), $tmp, $currentTransition->getWith(), $currentTransition->__toString(), $currentTransition->toPos());
                    $newFsmParse->setAgreement($currentTransition->getWith());
                    $fsmParse->enqueue($newFsmParse);
                }
            }
        }
    }

    /**
     * The parseExists method is used to check the existence of the $parse.
     *
     * @param array $fsmParse an ArrayList of $fsmParse
     * @param $surfaceForm String to use during $transition.
     * @return bool true when the currentState is end state and input $surfaceForm id equal to $currentSurfaceForm, otherwise false.
     */
    private function parseExists(array $fsmParse, string $surfaceForm): bool
    {
        $parseQueue = new Queue(1000);
        $parseQueue->enqueueAll($fsmParse);
        while (!$parseQueue->isEmpty()) {
            $currentFsmParse = $parseQueue->peek();
            $parseQueue->dequeue();
            $root = $currentFsmParse->getWord();
            $currentState = $currentFsmParse->getFinalSuffix();
            $currentSurfaceForm = $currentFsmParse->getSurfaceForm();
            if ($currentState->isEndState() && $currentSurfaceForm == $surfaceForm) {
                return true;
            }
            $this->addNewParsesFromCurrentParseSurfaceForm($currentFsmParse, $parseQueue, $surfaceForm, $root);
        }
        return false;
    }

    /**
     * The parseWord method is used to $parse a given $fsmParse. It simply adds new parses to the current $parse by
     * using addNewParsesFromCurrentParse method.
     *
     * @param array $fsmParse an ArrayList of $fsmParse
     * @param int $maxLength mb_strlen($maximum) of the surfaceform.
     * @return array $result <a href="psi_element://Array">Array</a> which has the $currentFsmParse.
     */
    private function parseWordLength(array $fsmParse, int $maxLength): array
    {
        $result = [];
        $resultTransitionList = [];
        $parseQueue = new Queue(1000);
        $parseQueue->enqueueAll($fsmParse);
        while (!$parseQueue->isEmpty()) {
            $currentFsmParse = $parseQueue->peek();
            $parseQueue->dequeue();
            $root = $currentFsmParse->getWord();
            $currentState = $currentFsmParse->getFinalSuffix();
            $currentSurfaceForm = $currentFsmParse->getSurfaceForm();
            if ($currentState->isEndState() && mb_strlen($currentSurfaceForm) <= $maxLength) {
                $currentTransitionList = $currentSurfaceForm . " " . $currentFsmParse->getFsmParseTransitionList();
                if (!array_key_exists($currentTransitionList, $resultTransitionList)) {
                    $result[] = $currentFsmParse;
                    $currentFsmParse->constructInflectionalGroups();
                    $resultTransitionList[$currentTransitionList] = true;
                }
            }
            $this->addNewParsesFromCurrentParseLength($currentFsmParse, $parseQueue, $maxLength, $root);
        }
        return $result;
    }

    /**
     * The parseWord method is used to $parse a given $fsmParse. It simply adds new parses to the current $parse by
     * using addNewParsesFromCurrentParse method.
     *
     * @param array $fsmParse an ArrayList of $fsmParse
     * @param $surfaceForm String to use during $transition.
     * @return array $result <a href="psi_element://Array">Array</a> which has the $currentFsmParse.
     */
    private function parseWordSurfaceForm(array $fsmParse, string $surfaceForm): array
    {
        $result = [];
        $resultTransitionList = [];
        $parseQueue = new Queue(1000);
        $parseQueue->enqueueAll($fsmParse);
        while (!$parseQueue->isEmpty()) {
            $currentFsmParse = $parseQueue->peek();
            $parseQueue->dequeue();
            $root = $currentFsmParse->getWord();
            $currentState = $currentFsmParse->getFinalSuffix();
            $currentSurfaceForm = $currentFsmParse->getSurfaceForm();
            if ($currentState->isEndState() && $currentSurfaceForm == $surfaceForm) {
                $currentTransitionList = $currentFsmParse->getFsmParseTransitionList();
                if (!array_key_exists($currentTransitionList, $resultTransitionList)) {
                    $result[] = $currentFsmParse;
                    $currentFsmParse->constructInflectionalGroups();
                    $resultTransitionList[$currentTransitionList] = true;
                }
            }
            $this->addNewParsesFromCurrentParseSurfaceForm($currentFsmParse, $parseQueue, $surfaceForm, $root);
        }
        return $result;
    }

    /**
     * The morphologicalAnalysis with 3 inputs is used to initialize an {@link Array} and add a new FsmParse
     * with given $root and state.
     *
     * @param TxtWord $root        TxtWord input.
     * @param string $surfaceForm String input to use for parsing.
     * @param string|null $state String input.
     * @return array method with newly populated $fsmParse ArrayList and input $surfaceForm.
     */
    function morphologicalAnalysisFromRoot(TxtWord $root, string $surfaceForm, ?string $state): array
    {
        $initialFsmParse = [];
        if ($state != null) {
            $initialFsmParse[] = new FsmParse($root, $this->finiteStateMachine->getState($state));
        } else {
            $this->initializeParseListFromRoot($initialFsmParse, $root, $this->isProperNoun($surfaceForm));
        }
        return $this->parseWordSurfaceForm($initialFsmParse, $surfaceForm);
    }

    /**
     * The generateAllParses with 2 inputs is used to generate all parses with given $root. Then it calls initializeParseListFromRoot method to initialize list with newly created ArrayList, input $root,
     * and mb_strlen($maximum).
     *
     * @param TxtWord $root        TxtWord input.
     * @param int $maxLength Maximum length of the surface form.
     * @return array method with newly populated $fsmParse ArrayList and mb_strlen($maximum).
     */
    function generateAllParses(TxtWord $root, int $maxLength): array
    {
        $initialFsmParse = [];
        if ($root->isProperNoun()) {
            $this->initializeParseListFromRoot($initialFsmParse, $root, true);
        }
        $this->initializeParseListFromRoot($initialFsmParse, $root, false);
        return $this->parseWordLength($initialFsmParse, mb_strlen($maxLength));
    }

    /**
     * Replaces previous lemma in the sentence with the new lemma. Both lemma can contain multiple words.
     * @param Sentence $original Original sentence to be $replaced with.
     * @param string $previousWord $root word in the $original sentence
     * @param string $newWord New word to be $replaced.
     * @return Sentence Newly generated sentence by replacing the previous word in the $original sentence with the new word.
     */
    function replaceWord(Sentence $original, string $previousWord, string $newWord): Sentence
    {
        $previousWordSplitted = null;
        $newWordSplitted = null;
        $result = new Sentence();
        $replacedWord = null;
        $previousWordMultiple = str_contains($previousWord, " ");
        $newWordMultiple = str_contains($newWord, " ");
        if ($previousWordMultiple) {
            $previousWordSplitted = explode(" ", $previousWord);
            $lastWord = $previousWordSplitted[count($previousWordSplitted) - 1];
        } else {
            $lastWord = $previousWord;
        }
        if ($newWordMultiple) {
            $newWordSplitted = explode(" ", $newWord);
            $newRootWord = $newWordSplitted[count($newWordSplitted) - 1];
        } else {
            $newRootWord = $newWord;
        }
        $newRootTxtWord = $this->dictionary->getWordWithName($newRootWord);
        $parseList = $this->morphologicalAnalysisFromSentence($original);
        for ($i = 0; $i < count($parseList); $i++) {
            $replaced = false;
            for ($j = 0; $j < $parseList[$i]->size(); $j++) {
                if ($parseList[$i]->getFsmParse($j)->getWord()->getName() == $lastWord && $newRootTxtWord != null) {
                    $replaced = true;
                    $replacedWord = $parseList[$i]->getFsmParse($j)->replaceRootWord($newRootTxtWord);
                }
            }
            if ($replaced && $replacedWord != null) {
                if ($previousWordMultiple) {
                    for ($k = 0; $k < $i - mb_strlen($previousWordSplitted) + 1; $k++) {
                        $result->addWord($original->getWord($k));
                    }
                }
                if ($newWordMultiple) {
                    for ($k = 0; $k < mb_strlen($newWordSplitted) - 1; $k++) {
                        if ($result->wordCount() == 0) {
                            $result->addWord(new Word(Transliterator::create("tr-Upper")->transliterate(mb_substr($newWordSplitted[$k], 0, 1)) . mb_substr($newWordSplitted[$k], 1)));
                        } else {
                            $result->addWord(new Word($newWordSplitted[$k]));
                        }
                    }
                }
                if ($result->wordCount() == 0) {
                    $replacedWord = Transliterator::create("tr-Upper")->transliterate(mb_substr($replacedWord, 0, 1)) . mb_substr($replacedWord, 1);
                }
                $result->addWord(new Word($replacedWord));
                if ($previousWordMultiple) {
                    $i++;
                    break;
                }
            } else {
                if (!$previousWordMultiple) {
                    $result->addWord($original->getWord($i));
                }
            }
        }
        if ($previousWordMultiple) {
            for (; $i < count($parseList); $i++) {
                $result->addWord($original->getWord($i));
            }
        }
        return $result;
    }

    /**
     * The analysisExists method checks several cases. If the given $surfaceForm is a punctuation or double then it
     * returns true. If it is not a $root word, then it initializes the $parse list and returns the parseExists method with
     * this newly initialized list and $surfaceForm.
     *
     * @param TxtWord $rootWord    TxtWord $root.
     * @param string $surfaceForm String input.
     * @param bool $isProper    boolean variable indicates a word is proper or not.
     * @return bool true if $surfaceForm is punctuation or double, otherwise returns parseExist method with given $surfaceForm.
     */
    private function analysisExists(TxtWord $rootWord, string $surfaceForm, bool $isProper): bool
    {
        if (Word::isPunctuationSymbol($surfaceForm)) {
            return true;
        }
        if ($this->isDouble($surfaceForm)) {
            return true;
        }
        if ($rootWord != null) {
            $initialFsmParse = [];
            $this->initializeParseListFromRoot($initialFsmParse, $rootWord, $isProper);
        } else {
            $initialFsmParse = $this->initializeParseListFromSurfaceForm($surfaceForm, $isProper);
        }
        return $this->parseExists($initialFsmParse, $surfaceForm);
    }

    /**
     * The analysis method is used by the morphologicalAnalysis method. It gets String $surfaceForm as an input and checks
     * its type such as punctuation, number or compares with the regex for date, fraction, percent, time, range, hashtag,
     * and mail or checks its variable type as integer or double. After finding the right case for given $surfaceForm, it calls
     * constructInflectionalGroups method which creates sub-word units.
     *
     * @param string $surfaceForm String to analyse.
     * @param bool $isProper    is used to indicate the proper words.
     * @return array ArrayList type $initialFsmParse which holds the analyses.
     */
    function analysis(string $surfaceForm, bool $isProper): array
    {
        if (Word::isPunctuationSymbol($surfaceForm) && $surfaceForm != "%") {
            $initialFsmParse = [];
            $fsmParse = new FsmParse($surfaceForm, new State(("Punctuation"), true, true));
            $fsmParse->constructInflectionalGroups();
            $initialFsmParse[] = $fsmParse;
            return $initialFsmParse;
        }
        if ($this->isNumber($surfaceForm)) {
            $initialFsmParse = [];
            $fsmParse = new FsmParse($surfaceForm, new State(("CardinalRoot"), true, true));
            $fsmParse->constructInflectionalGroups();
            $initialFsmParse[] = $fsmParse;
            return $initialFsmParse;
        }
        if (preg_match("/^\\d+\/\\d+$/", $surfaceForm) === 1) {
            $initialFsmParse = [];
            $fsmParse = new FsmParse($surfaceForm, new State(("FractionRoot"), true, true));
            $fsmParse->constructInflectionalGroups();
            $initialFsmParse[] = $fsmParse;
            $fsmParse = new FsmParse($surfaceForm, new State(("DateRoot"), true, true));
            $fsmParse->constructInflectionalGroups();
            $initialFsmParse[] = $fsmParse;
            return $initialFsmParse;
        }
        if ($this->isDate($surfaceForm)) {
            $initialFsmParse = [];
            $fsmParse = new FsmParse($surfaceForm, new State(("DateRoot"), true, true));
            $fsmParse->constructInflectionalGroups();
            $initialFsmParse[] = $fsmParse;
            return $initialFsmParse;
        }
        if (preg_match("/^\\d+\\\\\/\\d+$/", $surfaceForm) === 1) {
            $initialFsmParse = [];
            $fsmParse = new FsmParse($surfaceForm, new State(("FractionRoot"), true, true));
            $fsmParse->constructInflectionalGroups();
            $initialFsmParse[] = $fsmParse;
            return $initialFsmParse;
        }
        if ($surfaceForm == "%" || $this->isPercent($surfaceForm)) {
            $initialFsmParse = [];
            $fsmParse = new FsmParse($surfaceForm, new State(("PercentRoot"), true, true));
            $fsmParse->constructInflectionalGroups();
            $initialFsmParse[] = $fsmParse;
            return $initialFsmParse;
        }
        if ($this->isTime($surfaceForm)) {
            $initialFsmParse = [];
            $fsmParse = new FsmParse($surfaceForm, new State(("TimeRoot"), true, true));
            $fsmParse->constructInflectionalGroups();
            $initialFsmParse[] = $fsmParse;
            return $initialFsmParse;
        }
        if ($this->isRange($surfaceForm)) {
            $initialFsmParse = [];
            $fsmParse = new FsmParse($surfaceForm, new State(("RangeRoot"), true, true));
            $fsmParse->constructInflectionalGroups();
            $initialFsmParse[] = $fsmParse;
            return $initialFsmParse;
        }
        if (str_starts_with($surfaceForm, "#")) {
            $initialFsmParse = [];
            $fsmParse = new FsmParse($surfaceForm, new State(("Hashtag"), true, true));
            $fsmParse->constructInflectionalGroups();
            $initialFsmParse[] = $fsmParse;
            return $initialFsmParse;
        }
        if (str_contains($surfaceForm, "@")) {
            $initialFsmParse = [];
            $fsmParse = new FsmParse($surfaceForm, new State(("Email"), true, true));
            $fsmParse->constructInflectionalGroups();
            $initialFsmParse[] = $fsmParse;
            return $initialFsmParse;
        }
        if (str_ends_with($surfaceForm, ".") && $this->isInteger(mb_substr($surfaceForm, 0, mb_strlen($surfaceForm) - 1))) {
            $initialFsmParse = [];
            $fsmParse = new FsmParse((int)(mb_substr($surfaceForm, 0, mb_strlen($surfaceForm) - 1)), $this->finiteStateMachine->getState("OrdinalRoot"));
            $fsmParse->constructInflectionalGroups();
            $initialFsmParse[] = $fsmParse;
            return $initialFsmParse;
        }
        if ($this->isInteger($surfaceForm)) {
            $initialFsmParse = [];
            $fsmParse = new FsmParse((int)($surfaceForm), $this->finiteStateMachine->getState("CardinalRoot"));
            $fsmParse->constructInflectionalGroups();
            $initialFsmParse[] = $fsmParse;
            return $initialFsmParse;
        }
        if ($this->isDouble($surfaceForm)) {
            $initialFsmParse = [];
            $fsmParse = new FsmParse((float)($surfaceForm), $this->finiteStateMachine->getState("RealRoot"));
            $fsmParse->constructInflectionalGroups();
            $initialFsmParse[] = $fsmParse;
            return $initialFsmParse;
        }
        $initialFsmParse = $this->initializeParseListFromSurfaceForm($surfaceForm, $isProper);
        return $this->parseWordSurfaceForm($initialFsmParse, $surfaceForm);
    }

    /**
     * The isProperNoun method takes $surfaceForm String as input and checks its each char whether they are in the range
     * of letters between A to Z or one of the Turkish letters such as $i, Ü, Ğ, Ş, Ç, and Ö.
     *
     * @param string $surfaceForm String to check for proper noun.
     * @return bool false if $surfaceForm is null mb_strlen($or) of 0, return true if it is a letter.
     */
    function isProperNoun(string $surfaceForm): bool
    {
        if ($surfaceForm == null || mb_strlen($surfaceForm) == 0) {
            return false;
        }
        return (mb_substr($surfaceForm, 0, 1) >= 'A' && mb_substr($surfaceForm, 0, 1) <= 'Z') || (mb_substr($surfaceForm, 0, 1) == 'İ') ||
            (mb_substr($surfaceForm, 0, 1) == 'Ç') || (mb_substr($surfaceForm, 0, 1) == 'Ğ') || (mb_substr($surfaceForm, 0, 1) == 'Ö') ||
            (mb_substr($surfaceForm, 0, 1) == 'Ş') || (mb_substr($surfaceForm, 0, 1) == 'Ü'); // $i, Ü, Ğ, Ş, Ç, Ö
    }

    /**
     * The isCode method takes $surfaceForm String as input and checks if it consists of both letters and numbers
     *
     * @param $surfaceForm String to check for code-like word.
     * @return bool true if it is a code-like word, return false otherwise.
     */
    function isCode(string $surfaceForm): bool{
        if ($surfaceForm == null || mb_strlen($surfaceForm) == 0) {
            return false;
        }
        return preg_match( "/^.*[0-9].*$/", $surfaceForm) === 1 && preg_match("/^.*[a-zA-ZçöğüşıÇÖĞÜŞİ].*$/", $surfaceForm) === 1;
    }

    /**
     * Identifies a possible new $root word for a given surface form. It also adds the new $root form to the dictionary
     * for further usage. The method first searches the suffix trie for the reverse string of the surface form. This
     * way, it can identify if the word has a suffix that is in the most frequently used suffix list. Since a word can
     * have multiple possible suffixes, the method identifies the longest suffix and returns the substring of the
     * surface form tht does not contain the suffix. $say the word is 'googlelaştırdık', it will identify 'tık' as
     * a suffix and will return 'googlelaştır' as a possible $root form. Another example will be 'homelesslerimizle', it
     * will identify 'lerimizle' as suffix and will return 'homeless' as a possible $root form. If the $root word ends
     * with 'ğ', it is replacesd with 'k'. 'morfolojikliğini' will return 'morfolojikliğ' then which will be $replaced
     * with 'morfolojiklik'.
     * @param string $surfaceForm Surface form for which we will identify a possible new $root form.
     * @return array Possible new $root form.
     */
    private function rootOfPossiblyNewWord(string $surfaceForm): array
    {
        $words = $this->suffixTrie->getWordsWithPrefix($this->reverseString($surfaceForm));
        $candidateList = [];
        foreach ($words as $word) {
            $candidateWord = mb_substr($surfaceForm, 0, mb_strlen($surfaceForm) - mb_strlen($word->getName()));
            if (str_ends_with($candidateWord, "ğ")) {
                $candidateWord = mb_substr($candidateWord, 0, mb_strlen($candidateWord) - 1) . "k";
                $newWord = new TxtWord($candidateWord, "CL_ISIM");
                $newWord->addFlag("IS_SD");
            } else {
                $newWord = new TxtWord($candidateWord, "CL_ISIM");
                $newWord->addFlag("CL_FIIL");
            }
            $candidateList[] = $newWord;
            $this->dictionaryTrie->addWord($candidateWord, $newWord);
        }
        return $candidateList;
    }

    /**
     * The robustMorphologicalAnalysis is used to analyse $surfaceForm String. First it gets the currentParse of the $surfaceForm
     * then, if the size of the currentParse is 0, and given $surfaceForm is a proper noun, it adds the $surfaceForm
     * whose state name is ProperRoot to an {@link Array}, of it is not a proper noon, it adds the $surfaceForm
     * whose state name is NominalRoot to the {@link Array}.
     *
     * @param $surfaceForm String to analyse.
     * @return FsmParseList type currentParse which holds morphological analysis of the $surfaceForm.
     */
    function robustMorphologicalAnalysis(string $surfaceForm): FsmParseList
    {
        if ($surfaceForm == null || $surfaceForm == "") {
            return new FsmParseList([]);
        }
        $currentParse = $this->morphologicalAnalysis($surfaceForm);
        if ($currentParse->size() == 0) {
            $fsmParse = [];
            if ($this->isProperNoun($surfaceForm)) {
                $fsmParse[] = new FsmParse($surfaceForm, $this->finiteStateMachine->getState("ProperRoot"));
            }
            if ($this->isCode($surfaceForm)) {
                $fsmParse[] = new FsmParse($surfaceForm, $this->finiteStateMachine->getState("CodeRoot"));
            }
            $newCandidateList = $this->rootOfPossiblyNewWord($surfaceForm);
            if (count($newCandidateList) != 0) {
                foreach ($newCandidateList as $word) {
                    $fsmParse[] = new FsmParse($word, $this->finiteStateMachine->getState("VerbalRoot"));
                    $fsmParse[] = new FsmParse($word, $this->finiteStateMachine->getState("NominalRoot"));
                }
            }
            $fsmParse[] = new FsmParse($surfaceForm, $this->finiteStateMachine->getState("NominalRoot"));
            return new FsmParseList($this->parseWordSurfaceForm($fsmParse, $surfaceForm));
        } else {
            return $currentParse;
        }
    }

    /**
     * The morphologicalAnalysis is used for debug purposes.
     *
     * @param Sentence $sentence  to get word from.
     * @return array FsmParseList type $result.
     */
    function morphologicalAnalysisFromSentence(Sentence $sentence): array
    {
        $result = [];
        for ($i = 0; $i < $sentence->wordCount(); $i++) {
            $originalForm = $sentence->getWord($i)->getName();
            $spellCorrectedForm = $this->dictionary->getCorrectForm($originalForm);
            if ($spellCorrectedForm == null) {
                $spellCorrectedForm = $originalForm;
            }
            $wordFsmParseList = $this->morphologicalAnalysis($spellCorrectedForm);
            $result[] = $wordFsmParseList;
        }
        return $result;
    }

    /**
     * The robustMorphologicalAnalysis method takes just one argument as an input. It gets the name of the words from
     * input sentence then calls robustMorphologicalAnalysis with $surfaceForm.
     *
     * @param Sentence $sentence Sentence type input used to get $surfaceForm.
     * @return array FsmParseList array which holds the $result of the analysis.
     */
    function robustMorphologicalAnalysisFromSentence(Sentence $sentence): array
    {
        $result = [];
        for ($i = 0; $i < $sentence->wordCount(); $i++) {
            $originalForm = $sentence->getWord($i)->getName();
            $spellCorrectedForm = $this->dictionary->getCorrectForm($originalForm);
            if ($spellCorrectedForm == null) {
                $spellCorrectedForm = $originalForm;
            }
            $fsmParseList = $this->robustMorphologicalAnalysis($spellCorrectedForm);
            $result[] = $fsmParseList;
        }
        return $result;
    }

    /**
     * The isInteger method compares input $surfaceForm with regex \+?\d+ and returns the $result.
     * Supports positive integer checks only.
     *
     * @param string $surfaceForm String to check.
     * @return bool true if $surfaceForm matches with the regex.
     */
    private function isInteger(string $surfaceForm): bool
    {
        if (preg_match("/^[+-]?\\d+$/", $surfaceForm) === 0)
            return false;
        $len = mb_strlen($surfaceForm);
        if ($len < 10) {
            return true;
        } else {
            if ($len > 10) {
                return false;
            } else {
                return $surfaceForm >= "2147483647";
            }
        }
    }

    /**
     * The isDouble method compares input $surfaceForm with regex \+?(\d+)?\.\d* and returns the $result.
     *
     * @param $surfaceForm String to check.
     * @return bool true if $surfaceForm matches with the regex.
     */
    private function isDouble(string $surfaceForm): bool
    {
        return preg_match("/^[+-]?(\\d+)?\\.\\d*$/", $surfaceForm) === 1;
    }

    /**
     * The isNumber method compares input $surfaceForm with the array of written numbers and returns the $result.
     *
     * @param string $surfaceForm String to check.
     * @return bool true if $surfaceForm matches with the regex.
     */
    private function isNumber(string $surfaceForm): bool
    {
        $count = 0;
        $numbers = ["bir", "iki", "üç", "dört", "beş", "altı", "yedi", "sekiz", "dokuz",
            "on", "yirmi", "otuz", "kırk", "elli", "altmış", "yetmiş", "seksen", "doksan",
            "yüz", "bin", "milyon", "milyar", "trilyon", "katrilyon"];
        $word = $surfaceForm;
        while ($word != "") {
            $found = false;
            foreach ($numbers as $number) {
                if (str_starts_with($word, $number)) {
                    $found = true;
                    $count++;
                    $word = mb_substr($word, mb_strlen($number));
                    break;
                }
            }
            if (!$found) {
                break;
            }
        }
        return $word == "" && $count > 1;
    }

    /**
     * Checks if a given surface form matches to a percent value. It should be something like %4, %45, %4.3 or %56.786
     * @param string $surfaceForm Surface form to be checked.
     * @return bool true if the surface form is in percent form
     */
    private function isPercent(string $surfaceForm): bool
    {
        return preg_match("/^%(\\d\\d|\\d)$/", $surfaceForm) === 1 ||
            preg_match("/^%(\\d\\d|\\d)\\.\\d+$/", $surfaceForm) === 1;
    }

    /**
     * Checks if a given surface form matches to a time form. It should be something like 3:34, 12:56 etc.
     * @param string $surfaceForm Surface form to be checked.
     * @return bool true if the surface form is in time form
     */
    private function isTime(string $surfaceForm): bool
    {
        return preg_match("/^(\\d\\d|\\d):(\\d\\d|\\d):(\\d\\d|\\d)$/", $surfaceForm) === 1 ||
            preg_match("/^(\\d\\d|\\d):(\\d\\d|\\d)$/", $surfaceForm) === 1;
    }

    /**
     * Checks if a given surface form matches to a range form. It should be something like 123-1400 or 12:34-15:78 or
     * 3.45-4.67.
     * @param string $surfaceForm Surface form to be checked.
     * @return bool true if the surface form is in range form
     */
    private function isRange(string $surfaceForm): bool
    {
        return preg_match("/^\\d+-\\d+$/", $surfaceForm) === 1 ||
            preg_match("/^(\\d\\d|\\d):(\\d\\d|\\d)-(\\d\\d|\\d):(\\d\\d|\\d)$/", $surfaceForm) === 1 ||
            preg_match("/^(\\d\\d|\\d)\\.(\\d\\d|\\d)-(\\d\\d|\\d)\\.(\\d\\d|\\d)$/", $surfaceForm) === 1;
    }

    /**
     * Checks if a given surface form matches to a date form. It should be something like 3/10/2023 or 2.3.2012
     * @param string $surfaceForm Surface form to be checked.
     * @return bool true if the surface form is in date form
     */
    private function isDate(string $surfaceForm): bool
    {
        return preg_match("/^(\\d\\d|\\d)\/(\\d\\d|\\d)\/\\d+$/", $surfaceForm) === 1 ||
            preg_match("/^(\\d\\d|\\d)\\.(\\d\\d|\\d)\\.\\d+$/", $surfaceForm) === 1;
    }

    /**
     * The morphologicalAnalysis method is used to analyse a FsmParseList by comparing with the regex.
     * It creates an {@link Array} $fsmParse to hold the $result of the analysis method. For each $surfaceForm input,
     * it gets a substring and considers it as a $possibleRoot. Then compares with the regex.
     * <p>
     * If the $surfaceForm input string matches with Turkish chars like Ç, Ş, $i, Ü, Ö, it adds the $surfaceForm to Trie with IS_OA tag.
     * If the $possibleRoot contains /, then it is added to the Trie with IS_KESIR tag.
     * If the $possibleRoot contains \d\d|\d)/(\d\d|\d)/\d+, then it is added to the Trie with IS_DATE tag.
     * If the $possibleRoot contains \\d\d|\d, then it is added to the Trie with IS_PERCENT tag.
     * If the $possibleRoot contains \d\d|\d):(\d\d|\d):(\d\d|\d), then it is added to the Trie with IS_ZAMAN tag.
     * If the $possibleRoot contains \d+-\d+, then it is added to the Trie with IS_RANGE tag.
     * If the $possibleRoot is an Integer, then it is added to the Trie with IS_SAYI tag.
     * If the $possibleRoot is a Double, then it is added to the Trie with IS_REELSAYI tag.
     *
     * @param $surfaceForm String to analyse.
     * @return fsmParseList which holds the analysis.
     */
    function morphologicalAnalysis(string $surfaceForm): FsmParseList
    {
        $lowerCased = Transliterator::create("tr-Lower")->transliterate($surfaceForm);
        $possibleRootLowerCased = "";
        $pronunciation = "";
        $isRootReplaced = false;
        if ($this->parsedSurfaceForms != null && isset($this->parsedSurfaceForms[$lowerCased]) &&
            !$this->isInteger($surfaceForm) && !$this->isDouble($surfaceForm) && !$this->isPercent($surfaceForm) &&
            !$this->isTime($surfaceForm) && !$this->isRange($surfaceForm) && !$this->isDate($surfaceForm)) {
            $parses = [];
            $parses[] = new FsmParse(new Word($this->parsedSurfaceForms[$lowerCased]));
            return new FsmParseList($parses);
        }
        if ($this->cache != null && $this->cache->contains($surfaceForm)) {
            return $this->cache->get($surfaceForm);
        }
        if (preg_match("/^(\\w|Ç|Ş|İ|Ü|Ö)\\.$/", $surfaceForm) === 1) {
            $this->dictionaryTrie->addWord($lowerCased, new TxtWord($lowerCased, "IS_OA"));
        }
        $defaultFsmParse = $this->analysis($lowerCased, $this->isProperNoun($surfaceForm));
        if (count($defaultFsmParse) > 0) {
            $fsmParseList = new FsmParseList($defaultFsmParse);
            if ($this->cache != null) {
                $this->cache->add($surfaceForm, $fsmParseList);
            }
            return $fsmParseList;
        }
        $fsmParse = [];
        if (str_contains($surfaceForm, "'")) {
            $possibleRoot = mb_substr($surfaceForm, 0, mb_strpos($surfaceForm, '\''));
            if ($possibleRoot != "") {
                if (str_contains($possibleRoot, "/") || str_contains($possibleRoot, "\\/")) {
                    $this->dictionaryTrie->addWord($possibleRoot, new TxtWord($possibleRoot, "IS_KESIR"));
                    $fsmParse = $this->analysis($lowerCased, $this->isProperNoun($surfaceForm));
                } else {
                    if ($this->isDate($possibleRoot)) {
                        $this->dictionaryTrie->addWord($possibleRoot, new TxtWord($possibleRoot, "IS_DATE"));
                        $fsmParse = $this->analysis($lowerCased, $this->isProperNoun($surfaceForm));
                    } else {
                        if (preg_match("/^\\d+\/\\d+$/", $possibleRoot) === 1) {
                            $this->dictionaryTrie->addWord($possibleRoot, new TxtWord($possibleRoot, "IS_KESIR"));
                            $fsmParse = $this->analysis($lowerCased, $this->isProperNoun($surfaceForm));
                        } else {
                            if ($this->isPercent($possibleRoot)) {
                                $this->dictionaryTrie->addWord($possibleRoot, new TxtWord($possibleRoot, "IS_PERCENT"));
                                $fsmParse = $this->analysis($lowerCased, $this->isProperNoun($surfaceForm));
                            } else {
                                if ($this->isTime($surfaceForm)) {
                                    $this->dictionaryTrie->addWord($possibleRoot, new TxtWord($possibleRoot, "IS_ZAMAN"));
                                    $fsmParse = $this->analysis($lowerCased, $this->isProperNoun($surfaceForm));
                                } else {
                                    if ($this->isRange($surfaceForm)) {
                                        $this->dictionaryTrie->addWord($possibleRoot, new TxtWord($possibleRoot, "IS_RANGE"));
                                        $fsmParse = $this->analysis($lowerCased, $this->isProperNoun($surfaceForm));
                                    } else {
                                        if ($this->isInteger($possibleRoot)) {
                                            $this->dictionaryTrie->addWord($possibleRoot, new TxtWord($possibleRoot, "IS_SAYI"));
                                            $fsmParse = $this->analysis($lowerCased, $this->isProperNoun($surfaceForm));
                                        } else {
                                            if ($this->isDouble($possibleRoot)) {
                                                $this->dictionaryTrie->addWord($possibleRoot, new TxtWord($possibleRoot, "IS_REELSAYI"));
                                                $fsmParse = $this->analysis($lowerCased, $this->isProperNoun($surfaceForm));
                                            } else {
                                                if (Word::isCapital($possibleRoot) || str_contains("QXW", mb_substr($possibleRoot, 0, 1))) {
                                                    $possibleRootLowerCased = Transliterator::create("tr-Lower")->transliterate($possibleRoot);
                                                    if (isset($this->pronunciations[$possibleRootLowerCased])) {
                                                        $isRootReplaced = true;
                                                        $pronunciation = $this->pronunciations[$possibleRootLowerCased];
                                                        if ($this->dictionary->getWordWithName($pronunciation) != null) {
                                                            ($this->dictionary->getWordWithName($pronunciation))->addFlag("IS_OA");
                                                        } else {
                                                            $newWord = new TxtWord($pronunciation, "IS_OA");
                                                            $this->dictionaryTrie->addWord($pronunciation, $newWord);
                                                        }
                                                        $replacedWord = $pronunciation . mb_substr($lowerCased, mb_strlen($possibleRootLowerCased));
                                                        $fsmParse = $this->analysis($replacedWord, $this->isProperNoun($surfaceForm));
                                                    } else {
                                                        if ($this->dictionary->getWordWithName($possibleRootLowerCased) != null) {
                                                            ($this->dictionary->getWordWithName($possibleRootLowerCased))->addFlag("IS_OA");
                                                        } else {
                                                            $newWord = new TxtWord($possibleRootLowerCased, "IS_OA");
                                                            $this->dictionaryTrie->addWord($possibleRootLowerCased, $newWord);
                                                        }
                                                        $fsmParse = $this->analysis($lowerCased, $this->isProperNoun($surfaceForm));
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if (!$isRootReplaced) {
            foreach ($fsmParse as $parse) {
                $parse->restoreOriginalForm($possibleRootLowerCased, $pronunciation);
            }
        }
        $fsmParseList = new FsmParseList($fsmParse);
        if ($this->cache != null && $fsmParseList->size() > 0) {
            $this->cache->add($surfaceForm, $fsmParseList);
        }
        return $fsmParseList;
    }

    /**
     * The morphologicalAnalysisExists method calls analysisExists to check the existence of the analysis with given
     * $root and $surfaceForm.
     *
     * @param TxtWord $rootWord TxtWord input $root.
     * @param string $surfaceForm String to check.
     * @return bool true an analysis exists, otherwise return false.
     */
    function morphologicalAnalysisExists(TxtWord $rootWord, string $surfaceForm): bool
    {
        return $this->analysisExists($rootWord, Transliterator::create("tr-Lower")->transliterate($surfaceForm), true);
    }
}