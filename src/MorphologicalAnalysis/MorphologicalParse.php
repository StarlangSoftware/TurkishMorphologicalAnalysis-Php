<?php

namespace olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis;

use olcaytaner\Dictionary\Dictionary\Word;
use Transliterator;

class MorphologicalParse
{
    protected array $inflectionalGroups;
    protected Word $root;

    public function __construct(mixed $parseOrInflectionalGroups = null){
        if ($parseOrInflectionalGroups !== null) {
            if (!is_array($parseOrInflectionalGroups)) {
                $iGs = [];
                $st = $parseOrInflectionalGroups;
                while (str_contains($st, "^DB+")) {
                    $iGs[] = mb_substr($st, 0, mb_strpos($st, "^DB+"));
                    $st = mb_substr($st, mb_strpos($st, "^DB+") + 4);
                }
                $iGs[] = $st;
                if ($iGs[0] == "++Punc") {
                    $this->root = new Word("+");
                    $this->inflectionalGroups[] = new InflectionalGroup("Punc");
                } else {
                    if (mb_strpos($iGs[0], '+')) {
                        $this->root = new Word(mb_substr($iGs[0], 0, mb_strpos($iGs[0], '+')));
                        $this->inflectionalGroups[] = new InflectionalGroup(mb_substr($iGs[0], mb_strpos($iGs[0], '+') + 1));
                    } else {
                        $this->root = new Word($iGs[0]);
                    }
                    for ($i = 1; $i < count($iGs); $i++) {
                        $this->inflectionalGroups[] = new InflectionalGroup($iGs[$i]);
                    }
                }
            }  else {
                $groups = $parseOrInflectionalGroups;
                if (mb_strpos($groups[0], '+')) {
                    $this->root = new Word(mb_substr($groups[0], 0, mb_strpos($groups[0], '+')));
                    $this->inflectionalGroups[] = new InflectionalGroup(mb_substr($groups[0], mb_strpos($groups[0], '+') + 1));
                }
                for ($i = 1; $i < count($groups); $i++) {
                    $this->inflectionalGroups[] = new InflectionalGroup($groups[$i]);
                }
            }
        }
    }

    /**
     * The no-arg getWord method returns root {@link Word}.
     *
     * @return Word root {@link Word}.
     */
    public function getWord(): Word
    {
        return $this->root;
    }

    /**
     * The getTransitionList method gets the first item of inflectionalGroups {@link Array} as a {@link String},
     * then loops through the items of inflectionalGroups and concatenates them by using +.
     *
     * @return string String that contains transition list.
     */
    public function getMorphologicalParseTransitionList(): string
    {
        $result = $this->inflectionalGroups[0]->__toString();
        for ($i = 1; $i < count($this->inflectionalGroups); $i++) {
            $result = $result . "+" . $this->inflectionalGroups[$i]->__toString();
        }
        return $result;
    }

    /**
     * The getInflectionalGroupString method takes an {@link number} index as an input and if index is 0, it directly
     * returns the root and the first item of inflectionalGroups {@link Array}. If the index is not 0, it then returns
     * the corresponding item of inflectionalGroups {@link Array} as a {@link String}.
     *
     * @param int $index Integer input.
     * @return string corresponding item of inflectionalGroups at given index as a {@link String}.
     */
    public function getInflectionalGroupString(int $index): string
    {
        if ($index == 0) {
            return $this->root->getName() . "+" . $this->inflectionalGroups[0]->__toString();
        } else {
            return $this->inflectionalGroups[$index]->__toString();
        }
    }

    /**
     * The getInflectionalGroup method takes an {@link number} index as an input and it directly returns the
     * {@link InflectionalGroup} at given index.
     *
     * @param int|null $index Integer input.
     * @return InflectionalGroup InflectionalGroup at given index.
     */
    public function getInflectionalGroup(int $index = null): InflectionalGroup
    {
        if ($index == null) {
            $index = count($this->inflectionalGroups) - 1;
        }
        return $this->inflectionalGroups[$index];
    }

    /**
     * The getTag method takes an {@link number} $index as an input  and if the given $index is 0, it directly returns
     * the root. Then, it loops through the inflectionalGroups {@link Array} it returns the MorphologicalTag of the
     * corresponding inflectional group.
     *
     * @param int $index Integer input.
     * @return string|null the MorphologicalTag of the corresponding inflectional group, or null of invalid $index inputs.
     */
    public function getTag(int $index): string|null
    {
        $size = 1;
        if ($index == 0)
            return $this->root->getName();
        foreach ($this->inflectionalGroups as $group) {
            if ($index < $size + $group->size()) {
                return InflectionalGroup::getTag($group->getTagAtIndex($index - $size));
            }
            $size += $group->size();
        }
        return null;
    }

    /**
     * The tagSize method loops through the inflectionalGroups {@link Array} and accumulates the sizes of each inflectional group
     * in the inflectionalGroups.
     *
     * @return int total size of the inflectionalGroups {@link Array}.
     */
    public function tagSize(): int
    {
        $size = 1;
        foreach ($this->inflectionalGroups as $group) {
            $size += $group->size();
        }
        return $size;
    }

    /**
     * The size method returns the size of the inflectionalGroups {@link Array}.
     *
     * @return int the size of the inflectionalGroups {@link Array}.
     */
    public function size(): int
    {
        return count($this->inflectionalGroups);
    }

    /**
     * The firstInflectionalGroup method returns the first inflectional group of inflectionalGroups {@link Array}.
     *
     * @return InflectionalGroup the first inflectional group of inflectionalGroups {@link Array}.
     */
    public function firstInflectionalGroup(): InflectionalGroup
    {
        return $this->inflectionalGroups[0];
    }

    /**
     * The lastInflectionalGroup method returns the last inflectional group of inflectionalGroups {@link Array}.
     *
     * @return InflectionalGroup the last inflectional group of inflectionalGroups {@link Array}.
     */
    public function lastInflectionalGroup(): InflectionalGroup
    {
        return $this->inflectionalGroups[count($this->inflectionalGroups) - 1];
    }

    /**
     * The getWordWithPos method returns root with the MorphologicalTag of the first inflectional as a new word.
     *
     * @return Word root with the MorphologicalTag of the first inflectional as a new word.
     */
    public function getWordWithPos(): Word
    {
        return new Word($this->root->getName() . "+" . InflectionalGroup::getTag($this->firstInflectionalGroup()->getTagAtIndex(0)));
    }

    /**
     * The getPos method returns the MorphologicalTag of the last inflectional group.
     *
     * @return string the MorphologicalTag of the last inflectional group.
     */
    public function getPos(): string
    {
        return InflectionalGroup::getTag($this->lastInflectionalGroup()->getTagAtIndex(0));
    }

    /**
     * The getRootPos method returns the MorphologicalTag of the first inflectional group.
     *
     * @return string the MorphologicalTag of the first inflectional group.
     */
    public function getRootPos(): string
    {
        return InflectionalGroup::getTag($this->firstInflectionalGroup()->getTagAtIndex(0));
    }

    /**
     * The lastIGContainsCase method returns the MorphologicalTag of last inflectional group if it is one of the NOMINATIVE,
     * ACCUSATIVE, DATIVE, LOCATIVE or ABLATIVE cases, null otherwise.
     *
     * @return string the MorphologicalTag of last inflectional group if it is one of the NOMINATIVE,
     * ACCUSATIVE, DATIVE, LOCATIVE or ABLATIVE cases, null otherwise.
     */
    public function lastIGContainsCase(): string
    {
        $caseTag = $this->lastInflectionalGroup()->containsCase();
        if ($caseTag != null)
            return InflectionalGroup::getTag($caseTag);
        else
            return "NULL";
    }

    /**
     * The lastIGContainsTag method takes a MorphologicalTag as an input and returns true if the last inflectional group's
     * MorphologicalTag matches with one of the tags in the IG {@link Array}, false otherwise.
     *
     * @param MorphologicalTag $tag {@link MorphologicalTag} type input.
     * @return bool true if the last inflectional group's MorphologicalTag matches with one of the tags in the
     * IG {@link Array}, false otherwise.
     */
    public function lastIGContainsTag(MorphologicalTag $tag): bool
    {
        return $this->lastInflectionalGroup()->containsTag($tag);
    }

    /**
     * lastIGContainsPossessive method returns true if the last inflectional group contains one of the
     * possessives: P1PL, P1SG, P2PL, P2SG, P3PL AND P3SG, false otherwise.
     *
     * @return bool true if the last inflectional group contains one of the possessives: P1PL, P1SG, P2PL, P2SG, P3PL AND P3SG, false otherwise.
     */
    public function lastIGContainsPossessive(): bool
    {
        return $this->lastInflectionalGroup()->containsPossessive();
    }

    /**
     * The isCapitalWord method returns true if the character at first $index o f root is an uppercase letter, false otherwise.
     *
     * @return bool true if the character at first $index o f root is an uppercase letter, false otherwise.
     */
    public function isCapitalWord(): bool
    {
        $ch = mb_substr($this->root->getName(), 0, 1);
        return $ch == Transliterator::create("tr-Upper")->transliterate($ch);
    }

    /**
     * The isNoun method returns true if the part of speech is NOUN, false otherwise.
     *
     * @return true if the part of speech is NOUN, false otherwise.
     */
    public function isNoun(): bool
    {
        return $this->getPos() == "NOUN";
    }

    /**
     * The isVerb method returns true if the part of speech is VERB, false otherwise.
     *
     * @return bool true if the part of speech is VERB, false otherwise.
     */
    public function isVerb(): bool
    {
        return $this->getPos() == "VERB";
    }

    /**
     * The isRootVerb method returns true if the part of speech of root is BERV, false otherwise.
     *
     * @return bool true if the part of speech of root is VERB, false otherwise.
     */
    public function isRootVerb(): bool
    {
        return $this->getRootPos() == "VERB";
    }

    /**
     * The isAdjective method returns true if the part of speech is ADJ, false otherwise.
     *
     * @return bool true if the part of speech is ADJ, false otherwise.
     */
    public function isAdjective(): bool
    {
        return $this->getPos() == "ADJ";
    }

    /**
     * The isProperNoun method returns true if the first inflectional group's MorphologicalTag is a PROPERNOUN, false otherwise.
     *
     * @return bool true if the first inflectional group's MorphologicalTag is a PROPERNOUN, false otherwise.
     */
    public function isProperNoun(): bool
    {
        return $this->getInflectionalGroup(0)->containsTag(MorphologicalTag::PROPERNOUN);
    }

    /**
     * The isPunctuation method returns true if the first inflectional group's MorphologicalTag is a PUNCTUATION, false otherwise.
     *
     * @return bool true if the first inflectional group's MorphologicalTag is a PUNCTUATION, false otherwise.
     */
    public function isPunctuation(): bool
    {
        return $this->getInflectionalGroup(0)->containsTag(MorphologicalTag::PUNCTUATION);
    }

    /**
     * The isCardinal method returns true if the first inflectional group's MorphologicalTag is a CARDINAL, false otherwise.
     *
     * @return bool true if the first inflectional group's MorphologicalTag is a CARDINAL, false otherwise.
     */
    public function isCardinal(): bool
    {
        return $this->getInflectionalGroup(0)->containsTag(MorphologicalTag::CARDINAL);
    }

    /**
     * The isOrdinal method returns true if the first inflectional group's MorphologicalTag is a ORDINAL, false otherwise.
     *
     * @return bool true if the first inflectional group's MorphologicalTag is a ORDINAL, false otherwise.
     */
    public function isOrdinal(): bool
    {
        return $this->getInflectionalGroup(0)->containsTag(MorphologicalTag::ORDINAL);
    }

    /**
     * The isReal method returns true if the first inflectional group's MorphologicalTag is a REAL, false otherwise.
     *
     * @return true if the first inflectional group's MorphologicalTag is a REAL, false otherwise.
     */
    public function isReal(): bool
    {
        return $this->getInflectionalGroup(0)->containsTag(MorphologicalTag::REAL);
    }

    /**
     * The isNumber method returns true if the first inflectional group's MorphologicalTag is REAL or CARDINAL, false otherwise.
     *
     * @return bool true if the first inflectional group's MorphologicalTag is a REAL or CARDINAL, false otherwise.
     */
    public function isNumber(): bool
    {
        return $this->isReal() || $this->isCardinal();
    }

    /**
     * The isTime method returns true if the first inflectional group's MorphologicalTag is a TIME, false otherwise.
     *
     * @return bool true if the first inflectional group's MorphologicalTag is a TIME, false otherwise.
     */
    public function isTime(): bool
    {
        return $this->getInflectionalGroup(0)->containsTag(MorphologicalTag::TIME);
    }

    /**
     * The isDate method returns true if the first inflectional group's MorphologicalTag is a DATE, false otherwise.
     *
     * @return bool true if the first inflectional group's MorphologicalTag is a DATE, false otherwise.
     */
    public function isDate(): bool
    {
        return $this->getInflectionalGroup(0)->containsTag(MorphologicalTag::DATE);
    }

    /**
     * The isHashTag method returns true if the first inflectional group's MorphologicalTag is a HASHTAG, false otherwise.
     *
     * @return bool true if the first inflectional group's MorphologicalTag is a HASHTAG, false otherwise.
     */
    public function isHashTag(): bool
    {
        return $this->getInflectionalGroup(0)->containsTag(MorphologicalTag::HASHTAG);
    }

    /**
     * The isEmail method returns true if the first inflectional group's MorphologicalTag is a EMAIL, false otherwise.
     *
     * @return bool true if the first inflectional group's MorphologicalTag is a EMAIL, false otherwise.
     */
    public function isEmail(): bool
    {
        return $this->getInflectionalGroup(0)->containsTag(MorphologicalTag::EMAIL);
    }

    /**
     * The isPercent method returns true if the first inflectional group's MorphologicalTag is a PERCENT, false otherwise.
     *
     * @return bool true if the first inflectional group's MorphologicalTag is a PERCENT, false otherwise.
     */
    public function isPercent(): bool
    {
        return $this->getInflectionalGroup(0)->containsTag(MorphologicalTag::PERCENT);
    }

    /**
     * The isFraction method returns true if the first inflectional group's MorphologicalTag is a FRACTION, false otherwise.
     *
     * @return bool true if the first inflectional group's MorphologicalTag is a FRACTION, false otherwise.
     */
    public function isFraction(): bool
    {
        return $this->getInflectionalGroup(0)->containsTag(MorphologicalTag::FRACTION);
    }

    /**
     * The isRange method returns true if the first inflectional group's MorphologicalTag is a RANGE, false otherwise.
     *
     * @return bool true if the first inflectional group's MorphologicalTag is a RANGE, false otherwise.
     */
    public function isRange(): bool
    {
        return $this->getInflectionalGroup(0)->containsTag(MorphologicalTag::RANGE);
    }

    /**
     * The isPlural method returns true if {@link InflectionalGroup}'s MorphologicalTags are from the agreement plural
     * or possessive plural, $i.e A1PL, A2PL, A3PL, P1PL, P2PL or P3PL, and false otherwise.
     *
     * @return bool true if {@link InflectionalGroup}'s MorphologicalTags are from the agreement plural or possessive plural.
     */
    public function isPlural(): bool
    {
        foreach ($this->inflectionalGroups as $inflectionalGroup) {
            if ($inflectionalGroup->containsPlural()) {
                return true;
            }
        }
        return false;
    }

    /**
     * The isAuxiliary method returns true if the root equals to the et, ol, or yap, and false otherwise.
     *
     * @return bool true if the root equals to the et, ol, or yap, and false otherwise.
     */
    public function isAuxiliary(): bool
    {
        return $this->root->getName() == "et" || $this->root->getName() == "ol" || $this->root->getName() == "yap";
    }

    /**
     * The containsTag method takes a MorphologicalTag as an input and loops through the inflectionalGroups {@link ArrayList},
     * returns true if the input matches with on of the tags in the IG, false otherwise.
     *
     * @param MorphologicalTag $tag checked tag
     * @return bool true if the input matches with on of the tags in the IG, false otherwise.
     */
    public function containsTag(MorphologicalTag $tag): bool
    {
        foreach ($this->inflectionalGroups as $inflectionalGroup) {
            if ($inflectionalGroup->containsTag($tag)) {
                return true;
            }
        }
        return false;
    }

    /**
     * The getTreePos method returns the tree pos tag of a morphological analysis.
     *
     * @return string Tree pos tag of the morphological analysis in string form.
     */
    public function getTreePos(): string
    {
        if ($this->isProperNoun()) {
            return "NP";
        } else {
            if ($this->root->getName() == "değil") {
                return "NEG";
            } else {
                if ($this->isVerb()) {
                    if ($this->lastIGContainsTag(MorphologicalTag::ZERO)) {
                        return "NOMP";
                    } else {
                        return "VP";
                    }
                } else {
                    if ($this->isAdjective()) {
                        return "ADJP";
                    } else {
                        if ($this->isNoun() || $this->isPercent()) {
                            return "NP";
                        } else {
                            if ($this->containsTag(MorphologicalTag::ADVERB)) {
                                return "ADVP";
                            } else {
                                if ($this->isNumber() || $this->isFraction()) {
                                    return "NUM";
                                } else {
                                    if ($this->containsTag(MorphologicalTag::POSTPOSITION)) {
                                        return "PP";
                                    } else {
                                        if ($this->containsTag(MorphologicalTag::CONJUNCTION)) {
                                            return "CONJP";
                                        } else {
                                            if ($this->containsTag(MorphologicalTag::DETERMINER)) {
                                                return "DP";
                                            } else {
                                                if ($this->containsTag(MorphologicalTag::INTERJECTION)) {
                                                    return "INTJP";
                                                } else {
                                                    if ($this->containsTag(MorphologicalTag::QUESTIONPRONOUN)) {
                                                        return "WP";
                                                    } else {
                                                        if ($this->containsTag(MorphologicalTag::PRONOUN)) {
                                                            return "NP";
                                                        } else {
                                                            if ($this->isPunctuation()) {
                                                                switch ($this->root->getName()) {
                                                                    case "!":
                                                                    case "?":
                                                                        return ".";
                                                                    case ";":
                                                                    case "-":
                                                                    case "--":
                                                                        return ":";
                                                                    case "(":
                                                                    case "-LRB-":
                                                                    case "-lrb-":
                                                                        return "-LRB-";
                                                                    case ")":
                                                                    case "-RRB-":
                                                                    case "-rrb-":
                                                                        return "-RRB-";
                                                                    default:
                                                                        return $this->root->getName();
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
                }
            }
        }
        return "-XXX-";
    }

    /**
     * Returns the pronoun type of the parse for universal dependency feature ProType.
     * @return string|null "Art" if the pronoun is also a determiner; "Prs" if the pronoun is personal pronoun; "Rcp" if the
     * pronoun is 'birbiri'; "Ind" if the pronoun is an indeterminate pronoun; "Neg" if the pronoun is 'hiçbiri';
     * "Int" if the pronoun is a question pronoun; "Dem" if the pronoun is a demonstrative pronoun.
     */
    private function getPronType(): string|null
    {
        $lemma = $this->root->getName();
        if ($this->containsTag(MorphologicalTag::DETERMINER)) {
            return "Art";
        }
        if ($lemma == "kendi" || $this->containsTag(MorphologicalTag::PERSONALPRONOUN)) {
            return "Prs";
        }
        if ($lemma == "birbiri" || $lemma == "birbirleri") {
            return "Rcp";
        }
        if ($lemma == "birçoğu" || $lemma == "hep" || $lemma == "kimse"
            || $lemma == "bazı" || $lemma == "biri" || $lemma == "çoğu"
            || $lemma == "hepsi" || $lemma == "diğeri" || $lemma == "tümü"
            || $lemma == "herkes" || $lemma == "kimi" || $lemma == "öbür"
            || $lemma == "öteki" || $lemma == "birkaçı" || $lemma == "topu"
            || $lemma == "başkası") {
            return "Ind";
        }
        if ($lemma == "hiçbiri") {
            return "Neg";
        }
        if ($lemma == "kim" || $lemma == "nere" || $lemma == "ne"
            || $lemma == "hangi" || $lemma == "nasıl" || $lemma == "kaç"
            || $lemma == "mi" || $lemma == "mı" || $lemma == "mu" || $lemma == "mü") {
            return "Int";
        }
        if ($this->containsTag(MorphologicalTag::DEMONSTRATIVEPRONOUN)) {
            return "Dem";
        }
        return null;
    }

    /**
     * Returns the numeral type of the parse for universal dependency feature NumType.
     * @return string|null "Ord" if the parse is Time, Ordinal or the word is '%' or 'kaçıncı'; "Dist" if the word is a
     * distributive number such as 'beşinci'; "Card" if the number is cardinal or any number or the word is 'kaç'.
     */
    private function getNumType(): string|null
    {
        $lemma = $this->root->getName();
        if ($lemma == "%" || $this->containsTag(MorphologicalTag::TIME)) {
            return "Ord";
        }
        if ($this->containsTag(MorphologicalTag::ORDINAL) || $lemma == "kaçıncı") {
            return "Ord";
        }
        if ($this->containsTag(MorphologicalTag::DISTRIBUTIVE)) {
            return "Dist";
        }
        if ($this->containsTag(MorphologicalTag::CARDINAL) || $this->containsTag(MorphologicalTag::NUMBER) || $lemma == "kaç") {
            return "Card";
        }
        return null;
    }

    /**
     * Returns the value for the dependency feature Reflex.
     * @return string|null "Yes" if the root word is 'kendi', null otherwise.
     */
    private function getReflex(): string|null{
        $lemma = $this->root->getName();
        if ($lemma == "kendi") {
            return "Yes";
        }
        return null;
    }

    /**
     * Returns the agreement of the parse for the universal dependency feature Number.
     * @return string|null "Sing" if the agreement of the parse is singular (contains A1SG, A2SG, A3SG); "Plur" if the agreement
     * of the parse is plural (contains A1PL, A2PL, A3PL).
     */
    private function getNumber(): string|null{
        if ($this->lastIGContainsTag(MorphologicalTag::A1SG) || $this->lastIGContainsTag(MorphologicalTag::A2SG) ||
            $this->lastIGContainsTag(MorphologicalTag::A3SG)) {
            return "Sing";
        }
        if ($this->lastIGContainsTag(MorphologicalTag::A1PL) || $this->lastIGContainsTag(MorphologicalTag::A2PL) ||
            $this->lastIGContainsTag(MorphologicalTag::A3PL)) {
            return "Plur";
        }
        if ($this->containsTag(MorphologicalTag::A1SG) || $this->containsTag(MorphologicalTag::A2SG) ||
            $this->containsTag(MorphologicalTag::A3SG)) {
            return "Sing";
        }
        if ($this->containsTag(MorphologicalTag::A1PL) || $this->containsTag(MorphologicalTag::A2PL) ||
            $this->containsTag(MorphologicalTag::A3PL)) {
            return "Plur";
        }
        return null;
    }

    /**
     * Returns the possessive agreement of the parse for the universal dependency feature [Pos].
     * @return string|null "Sing" if the possessive agreement of the parse is singular (contains P1SG, P2SG, P3SG); "Plur" if the
     * possessive agreement of the parse is plural (contains P1PL, P2PL, P3PL).
     */
    private function getPossessiveNumber(): string|null{
        if ($this->lastIGContainsTag(MorphologicalTag::P1SG) || $this->lastIGContainsTag(MorphologicalTag::P2SG) ||
            $this->lastIGContainsTag(MorphologicalTag::P3SG)) {
            return "Sing";
        }
        if ($this->lastIGContainsTag(MorphologicalTag::P1PL) || $this->lastIGContainsTag(MorphologicalTag::P2PL) ||
            $this->lastIGContainsTag(MorphologicalTag::P3PL)) {
            return "Plur";
        }
        if ($this->containsTag(MorphologicalTag::P1SG) || $this->containsTag(MorphologicalTag::P2SG) ||
            $this->containsTag(MorphologicalTag::P3SG)) {
            return "Sing";
        }
        if ($this->containsTag(MorphologicalTag::P1PL) || $this->containsTag(MorphologicalTag::P2PL) ||
            $this->containsTag(MorphologicalTag::P3PL)) {
            return "Plur";
        }
        return null;
    }

    /**
     * Returns the case marking of the parse for the universal dependency feature case.
     * @return string|null "Acc" for accusative marker; "Dat" for dative marker; "Gen" for genitive marker; "Loc" for locative
     * marker; "Ins" for instrumentative marker; "Abl" for ablative marker; "Nom" for nominative marker.
     */
    private function getCase(): string|null{
        if ($this->containsTag(MorphologicalTag::ACCUSATIVE) || $this->containsTag(MorphologicalTag::PCACCUSATIVE)) {
            return "Acc";
        }
        if ($this->containsTag(MorphologicalTag::DATIVE) || $this->containsTag(MorphologicalTag::PCDATIVE)) {
            return "Dat";
        }
        if ($this->containsTag(MorphologicalTag::GENITIVE) || $this->containsTag(MorphologicalTag::PCGENITIVE)) {
            return "Gen";
        }
        if ($this->containsTag(MorphologicalTag::LOCATIVE)) {
            return "Loc";
        }
        if ($this->containsTag(MorphologicalTag::INSTRUMENTAL) || $this->containsTag(MorphologicalTag::PCINSTRUMENTAL)) {
            return "Ins";
        }
        if ($this->containsTag(MorphologicalTag::ABLATIVE) || $this->containsTag(MorphologicalTag::PCABLATIVE)) {
            return "Abl";
        }
        if ($this->containsTag(MorphologicalTag::NOMINATIVE) || $this->containsTag(MorphologicalTag::PCNOMINATIVE)) {
            return "Nom";
        }
        return null;
    }

    /**
     * Returns the definiteness of the parse for the universal dependency feature definite. It applies only for
     * determiners in Turkish.
     * @return string|null "Ind" for 'bir', 'bazı', or 'birkaç'. "Def" for 'her', 'bu', 'şu', 'o', 'bütün'.
     */
    private function getDefinite(): string|null
    {
        $lemma = $this->root->getName();
        if ($this->containsTag(MorphologicalTag::DETERMINER)) {
            if ($lemma == "bir" || $lemma == "bazı" || $lemma == "birkaç") {
                return "Ind";
            }
            if ($lemma == "her" || $lemma == "bu" || $lemma == "şu" || $lemma == "o" || $lemma == "bütün") {
                return "Def";
            }
        }
        return null;
    }

    /**
     * Returns the degree of the parse for the universal dependency feature degree.
     * @return string|null "Cmp" for comparative adverb 'daha'; "Sup" for superlative adjective or adverb 'en'.
     */
    private function getDegree(): string|null
    {
        $lemma = $this->root->getName();
        if ($lemma == "daha") {
            return "Cmp";
        }
        if ($lemma == "en" && !$this->isNoun()) {
            return "Sup";
        }
        return null;
    }

    /**
     * Returns the polarity of the verb for the universal dependency feature polarity.
     * @return string|null "Pos" for positive polarity containing tag POS; "Neg" for negative polarity containing tag NEG.
     */
    private function getPolarity(): string|null
    {
        if ($this->root->getName() == "değil") {
            return "Neg";
        }
        if ($this->containsTag(MorphologicalTag::POSITIVE)) {
            return "Pos";
        }
        if ($this->containsTag(MorphologicalTag::NEGATIVE)) {
            return "Neg";
        }
        return null;
    }

    /**
     * Returns the person of the agreement of the parse for the universal dependency feature person.
     * @return string|null "1" for first person; "2" for second person; "3" for third person.
     */
    private function getPerson(): string|null
    {
        if ($this->lastIGContainsTag(MorphologicalTag::A1SG) || $this->lastIGContainsTag(MorphologicalTag::A1PL)) {
            return "1";
        }
        if ($this->lastIGContainsTag(MorphologicalTag::A2SG) || $this->lastIGContainsTag(MorphologicalTag::A2PL)) {
            return "2";
        }
        if ($this->lastIGContainsTag(MorphologicalTag::A3SG) || $this->lastIGContainsTag(MorphologicalTag::A3PL)) {
            return "3";
        }
        if ($this->containsTag(MorphologicalTag::A1SG) || $this->containsTag(MorphologicalTag::A1PL)) {
            return "1";
        }
        if ($this->containsTag(MorphologicalTag::A2SG) || $this->containsTag(MorphologicalTag::A2PL)) {
            return "2";
        }
        if ($this->containsTag(MorphologicalTag::A3SG) || $this->containsTag(MorphologicalTag::A3PL)) {
            return "3";
        }
        return null;
    }

    /**
     * Returns the person of the possessive agreement of the parse for the universal dependency feature [pos].
     * @return string|null "1" for first person; "2" for second person; "3" for third person.
     */
    private function getPossessivePerson(): string|null
    {
        if ($this->lastIGContainsTag(MorphologicalTag::P1SG) || $this->lastIGContainsTag(MorphologicalTag::P1PL)) {
            return "1";
        }
        if ($this->lastIGContainsTag(MorphologicalTag::P2SG) || $this->lastIGContainsTag(MorphologicalTag::P2PL)) {
            return "2";
        }
        if ($this->lastIGContainsTag(MorphologicalTag::P3SG) || $this->lastIGContainsTag(MorphologicalTag::P3PL)) {
            return "3";
        }
        if ($this->containsTag(MorphologicalTag::P1SG) || $this->containsTag(MorphologicalTag::P1PL)) {
            return "1";
        }
        if ($this->containsTag(MorphologicalTag::P2SG) || $this->containsTag(MorphologicalTag::P2PL)) {
            return "2";
        }
        if ($this->containsTag(MorphologicalTag::P3SG) || $this->containsTag(MorphologicalTag::P3PL)) {
            return "3";
        }
        return null;
    }

    /**
     * Returns the voice of the verb parse for the universal dependency feature voice.
     * @return string|null "CauPass" if the verb parse is both causative and passive; "Pass" if the verb parse is only passive;
     * "Rcp" if the verb parse is reciprocal; "Cau" if the verb parse is only causative; "Rfl" if the verb parse is
     * reflexive.
     */
    private function getVoice(): string|null
    {
        if ($this->containsTag(MorphologicalTag::CAUSATIVE) && $this->containsTag(MorphologicalTag::PASSIVE)) {
            return "CauPass";
        }
        if ($this->containsTag(MorphologicalTag::PASSIVE)) {
            return "Pass";
        }
        if ($this->containsTag(MorphologicalTag::RECIPROCAL)) {
            return "Rcp";
        }
        if ($this->containsTag(MorphologicalTag::CAUSATIVE)) {
            return "Cau";
        }
        if ($this->containsTag(MorphologicalTag::REFLEXIVE)) {
            return "Rfl";
        }
        return null;
    }

    /**
     * Returns the aspect of the verb parse for the universal dependency feature aspect.
     * @return string|null "Perf" for past, narrative and future tenses; "Prog" for progressive tenses; "Hab" for Aorist; "Rapid"
     * for parses containing HASTILY tag; "Dur" for parses containing START, STAY or REPEAT tags.
     */
    private function getAspect(): string|null
    {
        if ($this->containsTag(MorphologicalTag::PASTTENSE) || $this->containsTag(MorphologicalTag::NARRATIVE) ||
            $this->containsTag(MorphologicalTag::FUTURE)) {
            return "Perf";
        }
        if ($this->containsTag(MorphologicalTag::PROGRESSIVE1) || $this->containsTag(MorphologicalTag::PROGRESSIVE2)) {
            return "Prog";
        }
        if ($this->containsTag(MorphologicalTag::AORIST)) {
            return "Hab";
        }
        if ($this->containsTag(MorphologicalTag::HASTILY)) {
            return "Rapid";
        }
        if ($this->containsTag(MorphologicalTag::START) || $this->containsTag(MorphologicalTag::STAY) ||
            $this->containsTag(MorphologicalTag::REPEAT)) {
            return "Dur";
        }
        return null;
    }

    /**
     * Returns the tense of the verb parse for universal dependency feature tense.
     * @return string|null "Past" for simple past tense; "Fut" for future tense; "Pqp" for narrative past tense; "Pres" for other
     * past tenses.
     */
    private function getTense(): string|null
    {
        if ($this->containsTag(MorphologicalTag::NARRATIVE) && $this->containsTag(MorphologicalTag::PASTTENSE)) {
            return "Pqp";
        }
        if ($this->containsTag(MorphologicalTag::NARRATIVE) || $this->containsTag(MorphologicalTag::PASTTENSE)) {
            return "Past";
        }
        if ($this->containsTag(MorphologicalTag::FUTURE)) {
            return "Fut";
        }
        if (!$this->containsTag(MorphologicalTag::PASTTENSE) && !$this->containsTag(MorphologicalTag::FUTURE)) {
            return "Pres";
        }
        return null;
    }

    /**
     * Returns the modality of the verb parse for the universal dependency feature mood.
     * @return string|null "GenNecPot" if both necessitative and potential is combined with a suffix of general modality;
     * "CndGenPot" if both conditional and potential is combined with a suffix of general modality;
     * "GenNec" if necessitative is combined with a suffix of general modality;
     * "GenPot" if potential is combined with a suffix of general modality;
     * "NecPot" if necessitative is combined with potential;
     * "DesPot" if desiderative is combined with potential;
     * "CndPot" if conditional is combined with potential;
     * "CndGen" if conditional is combined with a suffix of general modality;
     * "Imp" for imperative; "Cnd" for simple conditional; "Des" for simple desiderative; "Opt" for optative; "Nec" for
     * simple necessitative; "Pot" for simple potential; "Gen" for simple suffix of a general modality.
     */
    private function getMood(): string|null
    {
        if (($this->containsTag(MorphologicalTag::COPULA) || $this->containsTag(MorphologicalTag::AORIST)) &&
            $this->containsTag(MorphologicalTag::NECESSITY) && $this->containsTag(MorphologicalTag::ABLE)) {
            return "GenNecPot";
        }
        if ($this->containsTag(MorphologicalTag::CONDITIONAL) && ($this->containsTag(MorphologicalTag::COPULA) ||
                $this->containsTag(MorphologicalTag::AORIST)) && $this->containsTag(MorphologicalTag::ABLE)) {
            return "CndGenPot";
        }
        if (($this->containsTag(MorphologicalTag::COPULA) || $this->containsTag(MorphologicalTag::AORIST)) &&
            $this->containsTag(MorphologicalTag::NECESSITY)) {
            return "GenNec";
        }
        if ($this->containsTag(MorphologicalTag::NECESSITY) && $this->containsTag(MorphologicalTag::ABLE)) {
            return "NecPot";
        }
        if (($this->containsTag(MorphologicalTag::COPULA) || $this->containsTag(MorphologicalTag::AORIST)) &&
            $this->containsTag(MorphologicalTag::ABLE)) {
            return "GenPot";
        }
        if ($this->containsTag(MorphologicalTag::DESIRE) && $this->containsTag(MorphologicalTag::ABLE)) {
            return "DesPot";
        }
        if ($this->containsTag(MorphologicalTag::CONDITIONAL) && $this->containsTag(MorphologicalTag::ABLE)) {
            return "CndPot";
        }
        if ($this->containsTag(MorphologicalTag::CONDITIONAL) && ($this->containsTag(MorphologicalTag::COPULA) ||
                $this->containsTag(MorphologicalTag::AORIST))) {
            return "CndGen";
        }
        if ($this->containsTag(MorphologicalTag::IMPERATIVE)) {
            return "Imp";
        }
        if ($this->containsTag(MorphologicalTag::CONDITIONAL)) {
            return "Cnd";
        }
        if ($this->containsTag(MorphologicalTag::DESIRE)) {
            return "Des";
        }
        if ($this->containsTag(MorphologicalTag::OPTATIVE)) {
            return "Opt";
        }
        if ($this->containsTag(MorphologicalTag::NECESSITY)) {
            return "Nec";
        }
        if ($this->containsTag(MorphologicalTag::ABLE)) {
            return "Pot";
        }
        if ($this->containsTag(MorphologicalTag::PASTTENSE) || $this->containsTag(MorphologicalTag::NARRATIVE) ||
            $this->containsTag(MorphologicalTag::PROGRESSIVE1) || $this->containsTag(MorphologicalTag::PROGRESSIVE2) ||
            $this->containsTag(MorphologicalTag::FUTURE)) {
            return "Ind";
        }
        if (($this->containsTag(MorphologicalTag::COPULA) || $this->containsTag(MorphologicalTag::AORIST))) {
            return "Gen";
        }
        if ($this->containsTag(MorphologicalTag::ZERO) && !$this->containsTag(MorphologicalTag::A3PL)) {
            return "Gen";
        }
        return null;
    }

    /**
     * Returns the form of the verb parse for the universal dependency feature verbForm.
     * @return string|null "Part" for participles; "Vnoun" for infinitives; "Conv" for parses contaning tags SINCEDOINGSO,
     * WITHOUTHAVINGDONESO, WITHOUTBEINGABLETOHAVEDONESO, BYDOINGSO, AFTERDOINGSO, INFINITIVE3; "Fin" for others.
     */
    private function getVerbForm(): string|null
    {
        if ($this->containsTag(MorphologicalTag::PASTPARTICIPLE) || $this->containsTag(MorphologicalTag::FUTUREPARTICIPLE) ||
            $this->containsTag(MorphologicalTag::PRESENTPARTICIPLE)) {
            return "Part";
        }
        if ($this->containsTag(MorphologicalTag::INFINITIVE) || $this->containsTag(MorphologicalTag::INFINITIVE2)) {
            return "Vnoun";
        }
        if ($this->containsTag(MorphologicalTag::SINCEDOINGSO) || $this->containsTag(MorphologicalTag::WITHOUTHAVINGDONESO) ||
            $this->containsTag(MorphologicalTag::WITHOUTBEINGABLETOHAVEDONESO) || $this->containsTag(MorphologicalTag::BYDOINGSO) ||
            $this->containsTag(MorphologicalTag::AFTERDOINGSO) || $this->containsTag(MorphologicalTag::INFINITIVE3)) {
            return "Conv";
        }
        if ($this->containsTag(MorphologicalTag::COPULA) || $this->containsTag(MorphologicalTag::ABLE) ||
            $this->containsTag(MorphologicalTag::AORIST) || $this->containsTag(MorphologicalTag::PROGRESSIVE2) ||
            $this->containsTag(MorphologicalTag::DESIRE) || $this->containsTag(MorphologicalTag::NECESSITY) ||
            $this->containsTag(MorphologicalTag::CONDITIONAL) || $this->containsTag(MorphologicalTag::IMPERATIVE) ||
            $this->containsTag(MorphologicalTag::OPTATIVE) || $this->containsTag(MorphologicalTag::PASTTENSE) ||
            $this->containsTag(MorphologicalTag::NARRATIVE) || $this->containsTag(MorphologicalTag::PROGRESSIVE1) ||
            $this->containsTag(MorphologicalTag::FUTURE) || ($this->containsTag(MorphologicalTag::ZERO) &&
                !$this->containsTag(MorphologicalTag::A3PL))) {
            return "Fin";
        }
        return null;
    }

    private function getEvident(): string|null
    {
        if ($this->containsTag(MorphologicalTag::NARRATIVE)) {
            return "Nfh";
        } else {
            if ($this->containsTag(MorphologicalTag::COPULA) || $this->containsTag(MorphologicalTag::ABLE) || $this->containsTag(MorphologicalTag::AORIST) || $this->containsTag(MorphologicalTag::PROGRESSIVE2)
                || $this->containsTag(MorphologicalTag::DESIRE) || $this->containsTag(MorphologicalTag::NECESSITY) || $this->containsTag(MorphologicalTag::CONDITIONAL) || $this->containsTag(MorphologicalTag::IMPERATIVE) || $this->containsTag(MorphologicalTag::OPTATIVE)
                || $this->containsTag(MorphologicalTag::PASTTENSE) || $this->containsTag(MorphologicalTag::NARRATIVE) || $this->containsTag(MorphologicalTag::PROGRESSIVE1) || $this->containsTag(MorphologicalTag::FUTURE)) {
                return "Fh";
            }
        }
        return null;
    }

    /**
     * Construct the universal dependency features as an array of strings. Each element represents a single feature.
     * Every feature is given as featureType = featureValue.
     * @param string $uPos Universal dependency part of speech tag for the parse.
     * @return array An array of universal dependency features for this parse.
     */
    public function getUniversalDependencyFeatures(string $uPos): array{
    $featureList = [];
        $pronType = $this->getPronType();
        $uPosUpperCase = strtoupper($uPos);
        if ($pronType != null&& $uPosUpperCase != "NOUN" && $uPosUpperCase != "ADJ" && $uPosUpperCase != "VERB" && $uPosUpperCase != "CCONJ" && $uPosUpperCase != "PROPN"){
            $featureList[] = "PronType=" . $pronType;
        }
        $numType = $this->getNumType();
        if ($numType != null&& $uPosUpperCase != "VERB" && $uPosUpperCase != "NOUN" && $uPosUpperCase != "ADV"){
            $featureList[] = "NumType=" . $numType;
        }
        $reflex = $this->getReflex();
        if ($reflex != null&& $uPosUpperCase != "ADJ" && $uPosUpperCase != "VERB"){
            $featureList[] = "Reflex=" . $reflex;
        }
        $degree = $this->getDegree();
        if ($degree != null&& $uPosUpperCase != "ADJ"){
            $featureList[] = "Degree=" . $degree;
        }
        if ($this->isNoun() || $this->isVerb() || $this->root->getName() == "mi" || ($pronType != null&& $pronType != "Art")){
            $number = $this->getNumber();
            if ($number != null){
                $featureList[] = "Number=" . $number;
            }
            $possessiveNumber = $this->getPossessiveNumber();
            if ($possessiveNumber != null){
                $featureList[] = "Number[psor]=" . $possessiveNumber;
            }
            $person = $this->getPerson();
            if ($person != null&& $uPosUpperCase != "PROPN"){
                $featureList[] = "Person=" . $person;
            }
            $possessivePerson = $this->getPossessivePerson();
            if ($possessivePerson != null&& $uPosUpperCase != "PROPN"){
                $featureList[] = "Person[psor]=" . $possessivePerson;
            }
        }
        if ($this->isNoun() || ($pronType != null&& $pronType != "Art")) {
            $case_ = $this->getCase();
            if ($case_ != null){
                $featureList[] = "Case=" . $case_;
            }
        }
        if ($this->containsTag(MorphologicalTag::DETERMINER)){
            $definite = $this->getDefinite();
            if ($definite != null){
                $featureList[] = "Definite=" . $definite;
            }
        }
        if ($this->isVerb() || $this->root->getName() == "mi"){
            $polarity = $this->getPolarity();
            if ($polarity != null){
                $featureList[] = "Polarity=" . $polarity;
            }
            $voice = $this->getVoice();
            if ($voice != null&& $this->root->getName() != "mi"){
                $featureList[] = "Voice=" . $voice;
            }
            $aspect = $this->getAspect();
            if ($aspect != null&& $uPosUpperCase != "PROPN" && $this->root->getName() != "mi"){
                $featureList[] = "Aspect=" . $aspect;
            }
            $tense = $this->getTense();
            if ($tense != null&& $uPosUpperCase != "PROPN"){
                $featureList[] = "Tense=" . $tense;
            }
            $mood = $this->getMood();
            if ($mood != null&& $uPosUpperCase != "PROPN" && $this->root->getName() != "mi"){
                $featureList[] = "Mood=" . $mood;
            }
            $verbForm = $this->getVerbForm();
            if ($verbForm != null&& $uPosUpperCase != "PROPN"){
                $featureList[] = "VerbForm=" . $verbForm;
            }
            $evident = $this->getEvident();
            if ($evident != null&& $this->root->getName() != "mi"){
                $featureList[] = "Evident=" . $evident;
            }
        }
        sort($featureList);
        return $featureList;
    }

    /**
     * Returns the universal dependency part of speech for this parse.
     * @return string "AUX" for word 'değil; "PROPN" for proper nouns; "NOUN for nouns; "ADJ" for adjectives; "ADV" for
     * adverbs; "INTJ" for interjections; "VERB" for verbs; "PUNCT" for punctuation symbols; "DET" for determiners;
     * "NUM" for numerals; "PRON" for pronouns; "ADP" for post participles; "SCONJ" or "CCONJ" for conjunctions.
     */
    public function getUniversalDependencyPos(): string
    {
        $lemma = $this->root->getName();
        if ($lemma == "değil") {
            return "AUX";
        }
        if ($this->isProperNoun()) {
            return "PROPN";
        }
        if ($this->isNoun()) {
            return "NOUN";
        }
        if ($this->isAdjective()) {
            return "ADJ";
        }
        if ($this->getPos() == "ADV") {
            return "ADV";
        }
        if ($this->containsTag(MorphologicalTag::INTERJECTION)) {
            return "INTJ";
        }
        if ($this->isVerb()) {
            return "VERB";
        }
        if ($this->isPunctuation() || $this->isHashTag()) {
            return "PUNCT";
        }
        if ($this->containsTag(MorphologicalTag::DETERMINER)) {
            return "DET";
        }
        if ($this->isNumber() || $this->isDate() || $this->isTime() || $this->isOrdinal() || $this->isFraction() || $lemma == "%") {
            return "NUM";
        }
        if ($this->getPos() == "PRON") {
            return "PRON";
        }
        if ($this->getPos() == "POSTP") {
            return "ADP";
        }
        if ($this->getPos() == "QUES") {
            return "AUX";
        }
        if ($this->getPos() == "CONJ") {
            if ($lemma == "ki" || $lemma == "eğer" || $lemma == "diye") {
                return "SCONJ";
            } else {
                return "CCONJ";
            }
        }
        return "X";
    }

    /**
     * The overridden toString method gets the root and the first inflectional group as a result {@link String} then concatenates
     * with ^DB+ and the following inflectional groups.
     *
     * @return string string result {@link String}.
     */
    public function __toString(): string
    {
        $result = $this->root->getName() . "+" . $this->inflectionalGroups[0]->__toString();
        for ($i = 1; $i < count($this->inflectionalGroups); $i++) {
            $result = $result . "^DB+" . $this->inflectionalGroups[$i]->__toString();
        }
        return $result;
    }

}