<?php

namespace olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis;

class InflectionalGroup
{
    private array $IG;
    public static array $tags = ["NOUN", "ADV", "ADJ", "VERB", "A1SG",
        "A2SG", "A3SG", "A1PL", "A2PL", "A3PL",
        "P1SG", "P2SG", "P3SG", "P1PL", "P2PL",
        "P3PL", "PROP", "PNON", "NOM", "WITH",
        "WITHOUT", "ACC", "DAT", "GEN", "ABL",
        "ZERO", "ABLE", "NEG", "PAST",
        "CONJ", "DET", "DUP", "INTERJ", "NUM",
        "POSTP", "PUNC", "QUES", "AGT", "BYDOINGSO",
        "CARD", "CAUS", "DEMONSP", "DISTRIB", "FITFOR",
        "FUTPART", "INF", "NESS", "ORD", "PASS",
        "PASTPART", "PRESPART", "QUESP", "QUANTP", "RANGE",
        "RATIO", "REAL", "RECIP", "REFLEX", "REFLEXP",
        "TIME", "WHEN", "WHILE", "WITHOUTHAVINGDONESO", "PCABL",
        "PCACC", "PCDAT", "PCGEN", "PCINS", "PCNOM",
        "ACQUIRE", "ACTOF", "AFTERDOINGSO", "ALMOST", "AS",
        "ASIF", "BECOME", "EVERSINCE", "FEELLIKE", "HASTILY",
        "INBETWEEN", "JUSTLIKE", "LY", "NOTABLESTATE", "RELATED",
        "REPEAT", "SINCE", "SINCEDOINGSO", "START", "STAY",
        "EQU", "INS", "AOR", "DESR", "FUT",
        "IMP", "NARR", "NECES", "OPT", "PAST",
        "PRES", "PROG1", "PROG2", "COND", "COP",
        "POS", "PRON", "LOC", "REL", "DEMONS",
        "INF2", "INF3", "BSTAG", "ESTAG", "BTTAG",
        "ETTAG", "BDTAG", "EDTAG", "INF1", "ASLONGAS",
        "DIST", "ADAMANTLY", "PERCENT", "WITHOUTBEINGABLETOHAVEDONESO", "DIM",
        "PERS", "FRACTION", "HASHTAG", "EMAIL", "DATE",
        "CODE", "METRIC", "POL", "URGE"];

    public static array $morphoTags = [MorphologicalTag::NOUN, MorphologicalTag::ADVERB, MorphologicalTag::ADJECTIVE,
        MorphologicalTag::VERB, MorphologicalTag::A1SG, MorphologicalTag::A2SG, MorphologicalTag::A3SG, MorphologicalTag::A1PL,
        MorphologicalTag::A2PL, MorphologicalTag::A3PL, MorphologicalTag::P1SG, MorphologicalTag::P2SG, MorphologicalTag::P3SG, MorphologicalTag::P1PL,
        MorphologicalTag::P2PL, MorphologicalTag::P3PL, MorphologicalTag::PROPERNOUN, MorphologicalTag::PNON, MorphologicalTag::NOMINATIVE,
        MorphologicalTag::WITH, MorphologicalTag::WITHOUT, MorphologicalTag::ACCUSATIVE, MorphologicalTag::DATIVE, MorphologicalTag::GENITIVE,
        MorphologicalTag::ABLATIVE, MorphologicalTag::ZERO, MorphologicalTag::ABLE, MorphologicalTag::NEGATIVE, MorphologicalTag::PASTTENSE,
        MorphologicalTag::CONJUNCTION, MorphologicalTag::DETERMINER, MorphologicalTag::DUPLICATION, MorphologicalTag::INTERJECTION, MorphologicalTag::NUMBER,
        MorphologicalTag::POSTPOSITION, MorphologicalTag::PUNCTUATION, MorphologicalTag::QUESTION, MorphologicalTag::AGENT, MorphologicalTag::BYDOINGSO,
        MorphologicalTag::CARDINAL, MorphologicalTag::CAUSATIVE, MorphologicalTag::DEMONSTRATIVEPRONOUN, MorphologicalTag::DISTRIBUTIVE, MorphologicalTag::FITFOR,
        MorphologicalTag::FUTUREPARTICIPLE, MorphologicalTag::INFINITIVE, MorphologicalTag::NESS, MorphologicalTag::ORDINAL, MorphologicalTag::PASSIVE,
        MorphologicalTag::PASTPARTICIPLE, MorphologicalTag::PRESENTPARTICIPLE, MorphologicalTag::QUESTIONPRONOUN, MorphologicalTag::QUANTITATIVEPRONOUN, MorphologicalTag::RANGE,
        MorphologicalTag::RATIO, MorphologicalTag::REAL, MorphologicalTag::RECIPROCAL, MorphologicalTag::REFLEXIVE, MorphologicalTag::REFLEXIVEPRONOUN,
        MorphologicalTag::TIME, MorphologicalTag::WHEN, MorphologicalTag::WHILE, MorphologicalTag::WITHOUTHAVINGDONESO, MorphologicalTag::PCABLATIVE,
        MorphologicalTag::PCACCUSATIVE, MorphologicalTag::PCDATIVE, MorphologicalTag::PCGENITIVE, MorphologicalTag::PCINSTRUMENTAL, MorphologicalTag::PCNOMINATIVE,
        MorphologicalTag::ACQUIRE, MorphologicalTag::ACTOF, MorphologicalTag::AFTERDOINGSO, MorphologicalTag::ALMOST, MorphologicalTag::AS,
        MorphologicalTag::ASIF, MorphologicalTag::BECOME, MorphologicalTag::EVERSINCE, MorphologicalTag::FEELLIKE, MorphologicalTag::HASTILY,
        MorphologicalTag::INBETWEEN, MorphologicalTag::JUSTLIKE, MorphologicalTag::LY, MorphologicalTag::NOTABLESTATE, MorphologicalTag::RELATED,
        MorphologicalTag::REPEAT, MorphologicalTag::SINCE, MorphologicalTag::SINCEDOINGSO, MorphologicalTag::START, MorphologicalTag::STAY,
        MorphologicalTag::EQUATIVE, MorphologicalTag::INSTRUMENTAL, MorphologicalTag::AORIST, MorphologicalTag::DESIRE, MorphologicalTag::FUTURE,
        MorphologicalTag::IMPERATIVE, MorphologicalTag::NARRATIVE, MorphologicalTag::NECESSITY, MorphologicalTag::OPTATIVE, MorphologicalTag::PAST,
        MorphologicalTag::PRESENT, MorphologicalTag::PROGRESSIVE1, MorphologicalTag::PROGRESSIVE2, MorphologicalTag::CONDITIONAL, MorphologicalTag::COPULA,
        MorphologicalTag::POSITIVE, MorphologicalTag::PRONOUN, MorphologicalTag::LOCATIVE, MorphologicalTag::RELATIVE, MorphologicalTag::DEMONSTRATIVE,
        MorphologicalTag::INFINITIVE2, MorphologicalTag::INFINITIVE3, MorphologicalTag::BEGINNINGOFSENTENCE, MorphologicalTag::ENDOFSENTENCE, MorphologicalTag::BEGINNINGOFTITLE,
        MorphologicalTag::ENDOFTITLE, MorphologicalTag::BEGINNINGOFDOCUMENT, MorphologicalTag::ENDOFDOCUMENT, MorphologicalTag::INFINITIVE, MorphologicalTag::ASLONGAS,
        MorphologicalTag::DISTRIBUTIVE, MorphologicalTag::ADAMANTLY, MorphologicalTag::PERCENT, MorphologicalTag::WITHOUTBEINGABLETOHAVEDONESO, MorphologicalTag::DIMENSION,
        MorphologicalTag::PERSONALPRONOUN, MorphologicalTag::FRACTION, MorphologicalTag::HASHTAG, MorphologicalTag::EMAIL, MorphologicalTag::DATE,
        MorphologicalTag::CODE, MorphologicalTag::METRIC, MorphologicalTag::POLITE, MorphologicalTag::URGE];

    /**
     * The getMorphologicalTag method takes a String tag as an input and if the input matches with one of the elements of
     * tags array, it then gets the morphoTags of this tag and returns it.
     *
     * @param string $tag String to get morphoTags from.
     * @return MorphologicalTag|null morphoTags if found, null otherwise.
     */
    public static function getMorphologicalTag(string $tag): MorphologicalTag|null
    {
        for ($j = 0; $j < count(InflectionalGroup::$tags); $j++) {
            if (strtolower($tag) == strtolower(InflectionalGroup::$tags[$j])) {
                return InflectionalGroup::$morphoTags[$j];
            }
        }
        return null;
    }

    /**
     * The getTag method takes a MorphologicalTag type tag as an input and returns its corresponding tag from tags array.
     *
     * @param MorphologicalTag $tag MorphologicalTag type input to find tag from.
     * @return string|null tag if found, null otherwise.
     */
    public static function getTag(MorphologicalTag $tag): string|null
    {
        for ($j = 0; $j < InflectionalGroup::$morphoTags; $j++) {
            if ($tag == InflectionalGroup::$morphoTags[$j]) {
                return InflectionalGroup::$tags[$j];
            }
        }
        return null;
    }

    /**
     * A constructor of {@link InflectionalGroup} class which initializes the IG {@link ArrayList} by parsing given input
     * String IG by + and calling the getMorphologicalTag method with these substrings. If getMorphologicalTag method returns
     * a tag, it adds this tag to the IG {@link ArrayList}.
     *
     * @param string $IG String input.
     */
    public function __construct(string $IG)
    {
        $this->IG = [];
        $st = $IG;
        while (str_contains($st, "+")) {
            $morphologicalTag = mb_substr($st, 0, mb_stripos($st, "+"));
            $tag = InflectionalGroup::getMorphologicalTag($morphologicalTag);
            if ($tag != null) {
                $this->IG[] = $tag;
            }
            $st = mb_substr($st, mb_Stripos($st, "+") + 1);
        }
        $morphologicalTag = $st;
        $tag = InflectionalGroup::getMorphologicalTag($morphologicalTag);
        if ($tag != null) {
            $this->IG[] = $tag;
        }
    }

    /**
     * Another getTag method which takes index as an input and returns the corresponding tag from IG {@link ArrayList}.
     *
     * @param int $index to get tag.
     * @return MorphologicalTag tag at input index.
     */
    public function getTagAtIndex(int $index): MorphologicalTag
    {
        return $this->IG[$index];
    }

    /**
     * The size method returns the size of the IG {@link ArrayList}.
     *
     * @return int the size of the IG {@link ArrayList}.
     */
    public function size(): int
    {
        return count($this->IG);
    }

    /**
     * Overridden toString method to return resulting tags in IG {@link ArrayList}.
     *
     * @return string String result.
     */
    public function __toString(): string
    {
        $result = InflectionalGroup::getTag($this->IG[0]);
        for ($i = 1; $i < count($this->IG); $i++) {
            $result .= "+" . InflectionalGroup::getTag($this->IG[$i]);
        }
        return $result;
    }

    /**
     * The containsCase method loops through the tags in IG {@link ArrayList} and finds out the tags of the NOMINATIVE,
     * ACCUSATIVE, DATIVE, LOCATIVE or ABLATIVE cases.
     *
     * @return MorphologicalTag|null tag which holds the condition.
     */
    public function containsCase(): MorphologicalTag|null
    {
        foreach ($this->IG as $tag) {
            if ($tag == MorphologicalTag::NOMINATIVE || $tag == MorphologicalTag::ACCUSATIVE || $tag == MorphologicalTag::DATIVE || $tag == MorphologicalTag::LOCATIVE || $tag == MorphologicalTag::ABLATIVE) {
                return $tag;
            }
        }
        return null;
    }

    /**
     * The containsPlural method loops through the tags in IG {@link ArrayList} and checks whether the tags are from
     * the agreement plural or possessive plural, i.e A1PL, A2PL, A3PL, P1PL, P2PL and P3PL.
     *
     * @return bool true if the tag is plural, false otherwise.
     */
    public function containsPlural(): bool
    {
        foreach ($this->IG as $tag) {
            if ($tag == MorphologicalTag::A1PL || $tag == MorphologicalTag::A2PL || $tag == MorphologicalTag::A3PL || $tag == MorphologicalTag::P1PL || $tag == MorphologicalTag::P2PL || $tag == MorphologicalTag::P3PL) {
                return true;
            }
        }
        return false;
    }

    /**
     * The containsTag method takes a MorphologicalTag type tag as an input and loops through the tags in
     * IG {@link ArrayList} and returns true if the input matches with on of the tags in the IG.
     *
     * @param MorphologicalTag $tag MorphologicalTag type input to search for.
     * @return bool true if tag matches with the tag in IG, false otherwise.
     */
    public function containsTag(MorphologicalTag $tag): bool
    {
        foreach ($this->IG as $currentTag) {
            if ($currentTag == $tag) {
                return true;
            }
        }
        return false;
    }

    /**
     * The containsPossessive method loops through the tags in IG {@link ArrayList} and returns true if the tag in IG is
     * one of the possessives: P1PL, P1SG, P2PL, P2SG, P3PL AND P3SG.
     *
     * @return true if it contains possessive tag, false otherwise.
     */
    public function containsPossessive(): bool
    {
        foreach ($this->IG as $tag) {
            if ($tag == MorphologicalTag::P1PL || $tag == MorphologicalTag::P1SG || $tag == MorphologicalTag::P2PL || $tag == MorphologicalTag::P2SG || $tag == MorphologicalTag::P3PL || $tag == MorphologicalTag::P3SG) {
                return true;
            }
        }
        return false;
    }
}