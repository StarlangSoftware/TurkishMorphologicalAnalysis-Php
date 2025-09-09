<?php

namespace olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis;

use olcaytaner\Dictionary\Dictionary\TxtWord;
use olcaytaner\Dictionary\Dictionary\Word;
use olcaytaner\Dictionary\Language\TurkishLanguage;

class Transition
{
    private readonly State $toState;
    private readonly string $with;
    private readonly string $withName;
    private readonly ?string $toPos;

    /**
     * Another constructor of {@link Transition} class which takes  a {@link State}, and three {@link String}s as input. Then it
     * initializes toState, with, withName and toPos variables with given inputs.
     *
     * @param string $with String input.
     * @param string|null $withName String input.
     * @param State|null $toState {@link State} input.
     * @param string|null $toPos String input.
     */
    public function __construct(string $with, string $withName = null, State $toState = null, string $toPos = null)
    {
        $this->with = $with;
        $this->withName = $withName;
        $this->toState = $toState;
        $this->toPos = $toPos;
    }

    /**
     * Getter for the toState variable.
     *
     * @return State toState variable.
     */
    public function toState(): State
    {
        return $this->toState;
    }

    /**
     * Getter for the toPos variable.
     *
     * @return string toPos variable.
     */
    public function toPos(): string
    {
        if ($this->toPos != null) {
            return $this->toPos;
        } else {
            return "";
        }
    }

    /**
     * The transitionPossible method takes two {@link String} as inputs; currentSurfaceForm and realSurfaceForm. If the
     * length of the given currentSurfaceForm is greater than the given realSurfaceForm, it directly returns true. If not,
     * it takes a substring from given realSurfaceForm with the size of $currentSurfaceForm-> Then checks for the characters of
     * with variable.
     * <p>
     * If the character of with that makes transition is C, it returns true if the substring contains c or ç.
     * If the character of with that makes transition is D, it returns true if the substring contains d or t.
     * If the character of with that makes transition is A, it returns true if the substring contains a or e.
     * If the character of with that makes transition is K, it returns true if the substring contains k, g or ğ.
     * If the character of with that makes transition is other than the ones above, it returns true if the substring
     * contains the same character as with.
     *
     * @param string $currentSurfaceForm {@link String} input.
     * @param string $realSurfaceForm {@link String} input.
     * @return true when the transition is possible according to Turkish grammar, false otherwise.
     */
    public function transitionPossible(string $currentSurfaceForm, string $realSurfaceForm): bool
    {
        if (mb_strlen($currentSurfaceForm) == 0 || mb_strlen($currentSurfaceForm) >= mb_strlen($realSurfaceForm)) {
            return true;
        }
        $searchString = mb_substr($realSurfaceForm, mb_strlen($currentSurfaceForm));
        for ($i = 0; $i < mb_strlen($this->with); $i++) {
            switch (mb_substr($this->with, $i, 1)) {
                case 'C':
                    return str_contains($searchString, "c") || str_contains($searchString, "ç");
                case 'D':
                    return str_contains($searchString, "d") || str_contains($searchString, "t");
                case 'c':
                case 'e':
                case 'r':
                case 'p':
                case 'l':
                case 'b':
                case 'g':
                case 'o':
                case 'm':
                case 'v':
                case 'i':
                case 'ü':
                case 'z':
                    return str_contains($searchString, mb_substr($this->with, $i, 1));
                case 'A':
                    return str_contains($searchString, "a") || str_contains($searchString, "e");
                case 'k':
                    return str_contains($searchString, "k") || str_contains($searchString, "g") || str_contains($searchString, "ğ");
            }
        }
        return true;
    }

    /**
     * The transitionPossible method takes a {@link FsmParse} currentFsmParse as an input. It then checks some special cases;
     *
     * @param FsmParse $currentFsmParse Parse to be checked
     * @return bool true if transition is possible false otherwise
     */
    public function transitionPossibleFromParse(FsmParse $currentFsmParse): bool
    {
        if ($this->with == "Ar" && str_ends_with($currentFsmParse->getSurfaceForm(), "l") &&
            $currentFsmParse->getWord()->getName() != $currentFsmParse->getSurfaceForm()) {
            return false;
        }
        return true;
    }

    /**
     * The transitionPossibleFromRoot method takes root and current parse as inputs. It then checks some special cases.
     *
     * @param TxtWord $root Current root word
     * @param State $fromState From which state we arrived to this state.
     * @return true if transition is possible false otherwise
     */
    public function transitionPossibleFromRoot(TxtWord $root, State $fromState): bool
    {
        if ($root->isAdjective() && (($root->isNominal() && !$root->isExceptional()) || $root->isPronoun()) && $this->toState->getName() == "NominalRoot(ADJ)" && $this->with == "0") {
            return false;
        }
        if ($root->isAdjective() && $root->isNominal() && $this->with == "^DB+VERB+ZERO+PRES+A3PL" && $fromState->getName() == "AdjectiveRoot") {
            return false;
        }
        if ($root->isAdjective() && $root->isNominal() && $this->with == "SH" && $fromState->getName() == "AdjectiveRoot") {
            return false;
        }
        if ($this->with == "ki") {
            return $root->takesRelativeSuffixKi();
        }
        if ($this->with == "kü") {
            return $root->takesRelativeSuffixKu();
        }
        if ($this->with == "DHr") {
            if ($this->toState->getName() == "Adverb") {
                return true;
            } else {
                return $root->takesSuffixDIRAsFactitive();
            }
        }
        if ($this->with == "Hr" && ($this->toState->getName() == "AdjectiveRoot(VERB)" ||
                $this->toState->getName() == "OtherTense" || $this->toState->getName() == "OtherTense2")) {
            return $root->takesSuffixIRAsAorist();
        }
        return true;
    }

    /**
     * The withFirstChar method returns the first character of the with variable.
     *
     * @return string the first character of the with variable.
     */
    private function withFirstChar(): string
    {
        if (mb_strlen($this->with) == 0) {
            return '$';
        }
        if (mb_substr($this->with, 0, 1) != '\'') {
            return mb_substr($this->with, 0, 1);
        } else {
            if (mb_strlen($this->with) == 1) {
                return mb_substr($this->with, 0, 1);
            } else {
                return mb_substr($this->with, 1, 1);
            }
        }
    }

    /**
     * The startWithVowelorConsonantDrops method checks for some cases. If the first character of with variable is "nsy",
     * and with variable does not equal to one of the Strings; "ylA, ysA, ymHs, yDH, yken", it returns true. If
     * <p>
     * Or, if the first character of with variable is A, H: or any other vowels, it returns true.
     *
     * @return bool true if it starts with vowel or consonant drops, false otherwise.
     */
    private function startWithVowelorConsonantDrops(): bool
    {
        if (TurkishLanguage::isConsonantDrop($this->withFirstChar()) && $this->with != "ylA" && $this->with != "ysA" &&
            $this->with != "ymHs" && $this->with != "yDH" && $this->with != "yken") {
            return true;
        }
        if ($this->withFirstChar() == 'A' || $this->withFirstChar() == 'H' || TurkishLanguage::isVowel($this->withFirstChar())) {
            return true;
        }
        return false;
    }

    /**
     * The softenDuringSuffixation method takes a {@link TxtWord} root as an input. It checks two cases; first case returns
     * true if the given root is nominal or adjective and has one of the flags "IS_SD, IS_B_SD, IS_SDD" and with variable
     * equals o one of the Strings "Hm, nDAn, ncA, nDA, yA, yHm, yHz, yH, nH, nA, nHn, H, sH, Hn, HnHz, HmHz".
     * <p>
     * And the second case returns true if the given root is verb and has the "F_SD" flag, also with variable starts with
     * "Hyor" or equals one of the Strings "yHs, yAn, yA, yAcAk, yAsH, yHncA, yHp, yAlH, yArAk, yAdur, yHver, yAgel, yAgor,
     * yAbil, yAyaz, yAkal, yAkoy, yAmA, yHcH, HCH, Hr, Hs, Hn, yHn", yHnHz, Ar, Hl").
     *
     * @param $root {@link TxtWord} input.
     * @return bool true if there is softening during suffixation of the given root, false otherwise.
     */
    public function softenDuringSuffixation(TxtWord $root, State $startState): bool
    {
        if (!str_starts_with($startState->getName(), "VerbalRoot") && ($root->isNominal() || $root->isAdjective()) && $root->nounSoftenDuringSuffixation() &&
            ($this->with == "Hm" || $this->with == "nDAn" || $this->with == "ncA" || $this->with == "nDA" ||
                $this->with == "yA" || $this->with == "yHm" || $this->with == "yHz" || $this->with == "yH" ||
                $this->with == "nH" || $this->with == "nA" || $this->with == "nHn" || $this->with == "H" ||
                $this->with == "sH" || $this->with == "Hn" || $this->with == "HnHz" || $this->with == "HmHz")) {
            return true;
        }
        if (str_starts_with($startState->getName(), "VerbalRoot") && $root->isVerb() && $root->verbSoftenDuringSuffixation() &&
            (str_starts_with($this->with, "Hyor") || $this->with == "yHs" || $this->with == "yAn" || $this->with == "yA" ||
                str_starts_with($this->with, "yAcAk") || $this->with == "yAsH" || $this->with == "yHncA" || $this->with == "yHp" ||
                $this->with == "yAlH" || $this->with == "yArAk" || $this->with == "yAdur" || $this->with == "yHver" ||
                $this->with == "yAgel" || $this->with == "yAgor" || $this->with == "yAbil" || $this->with == "yAyaz" ||
                $this->with == "yAkal" || $this->with == "yAkoy" || $this->with == "yAmA" || $this->with == "yHcH" ||
                $this->with == "HCH" || str_starts_with($this->with, "Hr") || $this->with == "Hs" || $this->with == "Hn" ||
                $this->with == "yHn" || $this->with == "yHnHz" || str_starts_with($this->with, "Ar") || $this->with == "Hl")) {
            return true;
        }
        return false;
    }

    /**
     * The method is main driving method to accomplish the current transition from one state to another depending on
     * the root form of the word, current value of the word form, and the type of the start state. The method
     * (a) returns the original word form if the transition is an epsilon transition, (b) adds 'nunla' if the current
     * stem is 'bu', 'şu' or 'o', (c) returns 'bana' or 'sana' if the current stem is 'ben' or 'sen' respectively.
     * For other cases, the method first modifies current stem and then adds the transition using special metamorpheme
     * resolving methods. These cases are: (d) Converts 'y' of the first character of the transition to 'i' if the
     * current stem is 'ye' or 'de'. (e) Drops the last two characters and adds last character when the transition is
     * ('Hl' or 'Hn') and last 'I' drops during passive suffixation. (f) Adds 'y' character when the word ends with 'su'
     * and the transition does not start with 'y'. (g) Adds the last character again when the root duplicates during
     * suffixation. (h) Drops the last two characters and adds the last character when last 'i' drops during
     * suffixation. (i) Replaces the last character with a soft one when the root soften during suffixation.
     * @param TxtWord $root Root of the current word form
     * @param string $stem Current word form
     * @param State|null $startState The state from which this Fsm morphological analysis search has started.
     * @return string The current value of the word form after this transition is completed in the finite state machine.
     */
    public function makeTransition(TxtWord $root, string $stem, State $startState = null): string
    {
        if ($startState == null) {
            if ($root->isVerb()) {
                return $this->makeTransition($root, $stem, new State("VerbalRoot", true, false));
            } else {
                return $this->makeTransition($root, $stem, new State("NominalRoot", true, false));
            }
        } else {
            $rootWord = $root->getName() == $stem || ($root->getName() . "'") == $stem;
            $formation = $stem;
            $i = 0;
            if ($this->with == "0") {
                return $stem;
            }
            if (($stem == "bu" || $stem == "şu" || $stem == "o") && $rootWord && $this->with == "ylA") {
                return $stem . "nunla";
            }
            if ($this->with == "yA") {
                if ($stem == "ben") {
                    return "bana";
                }
                if ($stem == "sen") {
                    return "sana";
                }
            }
            //---vowelEChangesToIDuringYSuffixation---
            //de->d(i)yor, ye->y(i)yor
            if ($rootWord && $this->withFirstChar() == 'y' && $root->vowelEChangesToIDuringYSuffixation() &&
                (mb_substr($this->with, 1, 1) != 'H' || $root->getName() == "ye")) {
                $formation = mb_substr($stem, 0, mb_strlen($stem) - 1) . 'i';
                $formationToCheck = $formation;
            } else {
                //---lastIdropsDuringPassiveSuffixation---
                // yoğur->yoğrul, ayır->ayrıl, buyur->buyrul, çağır->çağrıl, çevir->çevril, devir->devril,
                // kavur->kavrul, kayır->kayrıl, kıvır->kıvrıl, savur->savrul, sıyır->sıyrıl, yoğur->yoğrul
                if ($rootWord && ($this->with == "Hl" || $this->with == "Hn") && $root->lastIdropsDuringPassiveSuffixation()) {
                    $formation = mb_substr($stem, 0, mb_strlen($stem) - 2) . mb_substr($stem, mb_strlen($stem) - 1, 1);
                    $formationToCheck = $stem;
                } else {
                    //---showsSuRegularities---
                    //karasu->karasuyu, su->suyu, ağırsu->ağırsuyu, akarsu->akarsuyu, bengisu->bengisuyu
                    if ($rootWord && $root->showsSuRegularities() && $this->startWithVowelorConsonantDrops()) {
                        $formation = $stem . 'y';
                        $i = 1;
                        $formationToCheck = $formation;
                    } else {
                        if ($rootWord && $root->duplicatesDuringSuffixation() && !str_starts_with($startState->getName(), "VerbalRoot") &&
                            TurkishLanguage::isConsonantDrop(mb_substr($this->with, 0, 1))) {
                            //---duplicatesDuringSuffixation---
                            if ($this->softenDuringSuffixation($root, $startState)) {
                                //--extra softenDuringSuffixation
                                switch (Word::lastPhoneme($stem)) {
                                    case 'p':
                                        //tıp->tıbbı
                                        $formation = mb_substr($stem, 0, mb_strlen($stem) - 1) . "bb";
                                        break;
                                    case 't':
                                        //cet->ceddi, met->meddi, ret->reddi, serhat->serhaddi, zıt->zıddı, şet->şeddi
                                        $formation = mb_substr($stem, 0, mb_strlen($stem) - 1) . "dd";
                                        break;
                                }
                            } else {
                                //cer->cerri, emrihak->emrihakkı, fek->fekki, fen->fenni, had->haddi, hat->hattı,
                                // haz->hazzı, his->hissi
                                $formation = $stem . mb_substr($stem, mb_strlen($stem) - 1, 1);
                            }
                            $formationToCheck = $formation;
                        } else {
                            if ($rootWord && $root->lastIdropsDuringSuffixation() &&
                                !str_starts_with($startState->getName(), "VerbalRoot") && !str_starts_with($startState->getName(), "ProperRoot") &&
                                $this->startWithVowelorConsonantDrops()) {
                                //---lastIdropsDuringSuffixation---
                                if ($this->softenDuringSuffixation($root, $startState)) {
                                    //---softenDuringSuffixation---
                                    switch (Word::lastPhoneme($stem)) {
                                        case 'p':
                                            //hizip->hizbi, kayıp->kaybı, kayıt->kaydı, kutup->kutbu
                                            $formation = mb_substr($stem, 0, mb_strlen($stem) - 2) . 'b';
                                            break;
                                        case 't':
                                            //akit->akdi, ahit->ahdi, lahit->lahdi, nakit->nakdi, vecit->vecdi
                                            $formation = mb_substr($stem, 0, mb_strlen($stem) - 2) . 'd';
                                            break;
                                        case 'ç':
                                            //eviç->evci, nesiç->nesci
                                            $formation = mb_substr($stem, 0, mb_strlen($stem) - 2) . 'c';
                                            break;
                                    }
                                } else {
                                    //sarıağız->sarıağzı, zehir->zehri, zikir->zikri, nutuk->nutku, omuz->omzu, ömür->ömrü
                                    //lütuf->lütfu, metin->metni, kavim->kavmi, kasıt->kastı
                                    $formation = mb_substr($stem, 0, mb_strlen($stem) - 2) . mb_substr($stem, mb_strlen($stem) - 1, 1);
                                }
                                $formationToCheck = $stem;
                            } else {
                                switch (Word::lastPhoneme($stem)) {
                                    //---nounSoftenDuringSuffixation or verbSoftenDuringSuffixation
                                    case 'p':
                                        //adap->adabı, amip->amibi, azap->azabı, gazap->gazabı
                                        if ($this->startWithVowelorConsonantDrops() && $rootWord && $this->softenDuringSuffixation($root, $startState)) {
                                            $formation = mb_substr($stem, 0, mb_strlen($stem) - 1) . 'b';
                                        }
                                        break;
                                    case 't':
                                        //adet->adedi, akort->akordu, armut->armudu
                                        //affet->affedi, yoket->yokedi, sabret->sabredi, rakset->raksedi
                                        if ($this->startWithVowelorConsonantDrops() && $rootWord && $this->softenDuringSuffixation($root, $startState)) {
                                            $formation = mb_substr($stem, 0, mb_strlen($stem) - 1) . 'd';
                                        }
                                        break;
                                    case 'ç':
                                        //ağaç->ağacı, almaç->almacı, akaç->akacı, avuç->avucu
                                        if ($this->startWithVowelorConsonantDrops() && $rootWord && $this->softenDuringSuffixation($root, $startState)) {
                                            $formation = mb_substr($stem, 0, mb_strlen($stem) - 1) . 'c';
                                        }
                                        break;
                                    case 'g':
                                        //arkeolog->arkeoloğu, filolog->filoloğu, minerolog->mineroloğu
                                        if ($this->startWithVowelorConsonantDrops() && $rootWord && $this->softenDuringSuffixation($root, $startState)) {
                                            $formation = mb_substr($stem, 0, mb_strlen($stem) - 1) . 'ğ';
                                        }
                                        break;
                                    case 'k':
                                        //ahenk->ahengi, künk->küngü, renk->rengi, pelesenk->pelesengi
                                        if ($this->startWithVowelorConsonantDrops() && $rootWord && $root->endingKChangesIntoG() &&
                                            (!$root->isProperNoun() || $startState->__toString() != "ProperRoot")) {
                                            $formation = mb_substr($stem, 0, mb_strlen($stem) - 1) . 'g';
                                        } else {
                                            //ablak->ablağı, küllük->küllüğü, kitaplık->kitaplığı, evcilik->evciliği
                                            if ($this->startWithVowelorConsonantDrops() && (!$rootWord ||
                                                    ($this->softenDuringSuffixation($root, $startState) && (!$root->isProperNoun() ||
                                                            $startState->__toString() != "ProperRoot")))) {
                                                $formation = mb_substr($stem, 0, mb_strlen($stem) - 1) . 'ğ';
                                            }
                                        }
                                        break;
                                }
                                $formationToCheck = $formation;
                            }
                        }
                    }
                }
            }
            if (TurkishLanguage::isConsonantDrop($this->withFirstChar()) &&
                !TurkishLanguage::isVowel(mb_substr($stem, mb_strlen($stem) - 1, 1)) &&
                ($root->isNumeral() || $root->isReal() || $root->isFraction() || $root->isTime() || $root->isDate() ||
                    $root->isPercent() || $root->isRange()) && (str_ends_with($root->getName(), "1") || str_ends_with($root->getName(), "3") ||
                    str_ends_with($root->getName(), "4") || str_ends_with($root->getName(), "5") || str_ends_with($root->getName(), "8") ||
                    str_ends_with($root->getName(), "9") || str_ends_with($root->getName(), "10") || str_ends_with($root->getName(), "30") ||
                    str_ends_with($root->getName(), "40") || str_ends_with($root->getName(), "60") || str_ends_with($root->getName(), "70") ||
                    str_ends_with($root->getName(), "80") || str_ends_with($root->getName(), "90") || str_ends_with($root->getName(), "00"))) {
                if (mb_substr($this->with, 0, 1) == '\'') {
                    $formation = $formation . '\'';
                    $i = 2;
                } else {
                    $i = 1;
                }
            } else {
                if ((TurkishLanguage::isConsonantDrop($this->withFirstChar()) && TurkishLanguage::isConsonant(Word::lastPhoneme($stem))) ||
                    ($rootWord && $root->consonantSMayInsertedDuringPossesiveSuffixation())) {
                    if (mb_substr($this->with, 0, 1) == '\'') {
                        $formation = $formation . '\'';
                        if ($root->isAbbreviation())
                            $i = 1;
                        else
                            $i = 2;
                    } else {
                        $i = 1;
                    }
                }
            }
            for (; $i < mb_strlen($this->with); $i++) {
                switch (mb_substr($this->with, $i, 1)) {
                    case 'D':
                        $formation = MorphotacticEngine::resolveD($root, $formation, $formationToCheck);
                        break;
                    case 'A':
                        $formation = MorphotacticEngine::resolveA($root, $formation, $rootWord, $formationToCheck);
                        break;
                    case 'H':
                        if (mb_substr($this->with, 0, 1) != '\'') {
                            $formation = MorphotacticEngine::resolveH($root, $formation, $i == 0, str_starts_with($this->with, "Hyor"), $rootWord, $formationToCheck);
                        } else {
                            $formation = MorphotacticEngine::resolveH($root, $formation, $i == 1, false, $rootWord, $formationToCheck);
                        }
                        $rootWord = false;
                        break;
                    case 'C':
                        $formation = MorphotacticEngine::resolveC($formation, $formationToCheck);
                        break;
                    case 'S':
                        $formation = MorphotacticEngine::resolveS($formation);
                        break;
                    case 'Ş':
                        $formation = MorphotacticEngine::resolveSh($formation);
                        break;
                    default:
                        if ($i == mb_strlen($this->with) - 1 && mb_substr($this->with, $i, 1) == 's') {
                            $formation .= 'ş';
                        } else {
                            $formation .= mb_substr($this->with, $i, 1);
                        }
                }
                $formationToCheck = $formation;
            }
            return $formation;
        }
    }

    /**
     * An overridden toString method which returns the with variable.
     *
     * @return string with variable.
     */
    public function __toString(): string
    {
        return $this->with;
    }

    /**
     * The with method returns the withName variable.
     *
     * @return string the withName variable.
     */
    public function getWith(): string
    {
        return $this->withName;
    }
}