<?php

namespace olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis;

use olcaytaner\Dictionary\Dictionary\TxtWord;
use olcaytaner\Dictionary\Dictionary\Word;
use olcaytaner\Dictionary\Language\TurkishLanguage;

class MorphotacticEngine
{
    /**
     * The resolveSh method takes a {@link String} $formation as an input. If the last character is a vowel, it concatenates
     * given $formation with ş, if the last character is not a vowel, and not 't' it directly returns given $formation, but if it
     * is equal to 't', it transforms it to 'd'.
     *
     * @param string $formation {@link String} input.
     * @return string resolved String.
     */
    public static function resolveSh(string $formation): string
    {
        if (TurkishLanguage::isVowel(mb_substr($formation, mb_strlen($formation) - 1, 1))) {
            return $formation . 'ş';
        } else {
            if (mb_substr($formation, mb_Strlen($formation) - 1, 1) != 't')
                return $formation;
            else
                return mb_substr($formation, 0, mb_strlen($formation) - 1) . 'd';
        }
    }

    /**
     * The resolveS method takes a {@link String} $formation as an input. It then concatenates given $formation with 's'.
     *
     * @param string $formation {@link String} input.
     * @return string resolved String.
     */
    public static function resolveS(string $formation): string
    {
        return $formation . 's';
    }

    /**
     * resolveD resolves the D metamorpheme to 'd' or 't' depending on the $root and current $formationToCheck. It adds
     * 'd' if the $root is an abbreviation; 't' if the last phoneme is one of the "çfhkpsşt" (fıstıkçı şahap) or 'd'
     * otherwise; 't' if the word is a number ending with 3, 4, 5, 40, 60, or 70 or 'd' otherwise.
     * @param TxtWord $root $root of the word
     * @param string $formation $formation is current status of the wordform in the current state of the finite state machine. It
     *                  is always equal to $formationToCheck except the case where there is an apostrophe after the
     *                  $formationToCheck such as (3').
     * @param string $formationToCheck $formationToCheck is current status of the wordform in the current state of the finite
     *                         state machine except the apostrophe at the end if it exists.
     * @return string formation with added 'd' or 't' character.
     */
    public static function resolveD(TxtWord $root, string $formation, string $formationToCheck): string
    {
        if ($root->isAbbreviation()) {
            return $formation . 'd';
        }
        if (Word::lastPhoneme($formationToCheck) >= '0' && Word::lastPhoneme($formationToCheck) <= '9') {
            switch (Word::lastPhoneme($formationToCheck)) {
                case '3':
                case '4':
                case '5':
                    //3->3'tü, 5->5'ti, 4->4'tü
                    return $formation . 't';
                case '0':
                    if (str_ends_with($root->getName(), "40") || str_ends_with($root->getName(), "60") || str_ends_with($root->getName(), "70"))
                        //40->40'tı, 60->60'tı, 70->70'ti
                        return $formation . 't';
                    else
                        //30->30'du, 50->50'ydi, 80->80'di
                        return $formation . 'd';
                default:
                    return $formation . 'd';
            }
        } else {
            if (TurkishLanguage::isSertSessiz(Word::lastPhoneme($formationToCheck))) {
                //yap+DH->yaptı
                return $formation . 't';
            } else {
                //sar+DH->sardı
                return $formation . 'd';
            }
        }
    }

    /**
     * resolveA resolves the A metamorpheme to 'a' or 'e' depending on the $root and current $formationToCheck. It adds
     * 'e' if the $root is an abbreviation; 'a' if the last vowel is a back vowel (except words that do not obey vowel
     * harmony during agglutination); 'e' if the last vowel is a front vowel (except words that do not obey vowel
     * harmony during agglutination); 'a' if the word is a number ending with 6, 9, 10, 30, 40, 60, or 90 or 'e'
     * otherwise.
     * @param TxtWord $root $root of the word
     * @param string $formation $formation is current status of the wordform in the current state of the finite state machine. It
     *                  is always equal to $formationToCheck except the case where there is an apostrophe after the
     *                  $formationToCheck such as (3').
     * @param bool $rootWord True if the current word form is $root form, false otherwise.
     * @param string $formationToCheck $formationToCheck is current status of the wordform in the current state of the finite
     *                         state machine except the apostrophe at the end if it exists.
     * @return string formation with added 'a' or 'e' character.
     */
    public static function resolveA(TxtWord $root, string $formation, bool $rootWord, string $formationToCheck): string
    {
        if ($root->isAbbreviation()) {
            return $formation . 'e';
        }
        if (Word::lastVowel($formationToCheck) >= '0' && Word::lastVowel($formationToCheck) <= '9') {
            switch (Word::lastVowel($formationToCheck)) {
                case '6':
                case '9':
                    //6'ya, 9'a
                    return $formation . 'a';
                case '0':
                    if (str_ends_with($root->getName(), "10") || str_ends_with($root->getName(), "30") || str_ends_with($root->getName(), "40") || str_ends_with($root->getName(), "60") || str_ends_with($root->getName(), "90"))
                        //10'a, 30'a, 40'a, 60'a, 90'a
                        return $formation . 'a';
                    else
                        //20'ye, 50'ye, 80'e, 70'e
                        return $formation . 'e';
                default:
                    //3'e, 8'e, 4'e, 2'ye
                    return $formation . 'e';
            }
        }
        if (TurkishLanguage::isBackVowel(Word::lastVowel($formationToCheck))) {
            if ($root->notObeysVowelHarmonyDuringAgglutination() && $rootWord) {
                //alkole, anormale
                return $formation . 'e';
            } else {
                //sakala, kabala
                return $formation . 'a';
            }
        }
        if (TurkishLanguage::isFrontVowel(Word::lastVowel($formationToCheck))) {
            if ($root->notObeysVowelHarmonyDuringAgglutination() && $rootWord) {
                //faika, halika
                return $formation . 'a';
            } else {
                //kediye, eve
                return $formation . 'e';
            }
        }
        if ($root->isNumeral() || $root->isFraction() || $root->isReal()) {
            if (str_ends_with($root->getName(), "6") || str_ends_with($root->getName(), "9") || str_ends_with($root->getName(), "10") || str_ends_with($root->getName(), "30") || str_ends_with($root->getName(), "40") || str_ends_with($root->getName(), "60") || str_ends_with($root->getName(), "90")) {
                return $formation . 'a';
            } else {
                return $formation . 'e';
            }
        }
        return $formation;
    }

    /**
     * resolveHforSpecialCaseTenseSuffix resolves the H metamorpheme to 'ı', 'i', 'u' or 'ü' for special case suffix
     * 'Hyor', depending on the  current $formationToCheck. After dropping the last character, it adds 'ü' if the
     * character before the last vowel is front rounded; 'i' if the character before the last vowel is front unrounded;
     * 'u' if the character before the  last vowel is back rounded; 'ı' if the character before the last vowel is back
     * unrounded.
     * @param string $formationToCheck $formationToCheck is current status of the word form in the current state of the finite
     *                         state machine except the apostrophe at the end if it exists.
     * @param string $formation $formation is current status of the wordform in the current state of the finite state machine. It
     *                  is always equal to $formationToCheck except the case where there is an apostrophe after the
     *                  $formationToCheck such as (3').
     * @return string|null formation with last character dropped and 'ı', 'i', 'u' or 'ü' character added.
     */
    public static function resolveHforSpecialCaseTenseSuffix(string $formationToCheck, string $formation): ?string
    {
        if (TurkishLanguage::isFrontRoundedVowel(Word::beforeLastVowel($formationToCheck))) {
            //büyülüyor, bölümlüyor, çözümlüyor, döşüyor
            return mb_substr($formation, 0, mb_strlen($formation) - 1) . 'ü';
        }
        if (TurkishLanguage::isFrontUnroundedVowel(Word::beforeLastVowel($formationToCheck))) {
            //adresliyor, alevliyor, ateşliyor, bekliyor
            return mb_substr($formation, 0, mb_strlen($formation) - 1) . 'i';
        }
        if (TurkishLanguage::isBackRoundedVowel(Word::beforeLastVowel($formationToCheck))) {
            //buğuluyor, bulguluyor, çamurluyor, aforozluyor
            return mb_substr($formation, 0, mb_strlen($formation) - 1) . 'u';
        }
        if (TurkishLanguage::isBackUnroundedVowel(Word::beforeLastVowel($formationToCheck))) {
            //açıklıyor, çalkalıyor, gazlıyor, gıcırdıyor
            return mb_substr($formation, 0, mb_strlen($formation) - 1) . 'ı';
        }
        return null;
    }

    /**
     * resolveH resolves the H metamorpheme to 'ı', 'i', 'u' or 'ü', depending on the  current $formationToCheck, $root,
     * and $formation. It adds 'i' if the $root is an abbreviation; 'ü' if the  character before the last vowel is
     * front rounded (or back rounded when the $root word does not obey vowel harmony during agglutination); 'i' if the
     * character before the last vowel is front unrounded; 'u' if the character before the  last vowel is back rounded;
     * 'ı' if the character before the last vowel is back unrounded (or front unrounded when the $root word does not obey
     * vowel harmony during agglutination); 'ı' if the word is a  number ending with 6, 40, 60 or 90; 'ü' if the word
     * is a number ending with 3, 4, or 00; 'u' if the word is a number ending with 9, 10, or 30; 'i' otherwise for
     * numbers. Special case for 'Hyor' suffix is handled with resolveHfor $specialCaseTenseSuffix method.
     * @param TxtWord $root $root of the word
     * @param string $formation $formation is current status of the wordform in the current state of the finite state machine. It
     *                  is always equal to $formationToCheck except the case where there is an apostrophe after the
     *                  $formationToCheck such as (3').
     * @param bool $beginningOfSuffix True if H appears in the beginning of the suffix, false otherwise.
     * @param bool $specialCaseTenseSuffix True if the suffix is 'Hyor', false otherwise.
     * @param bool $rootWord True if the current word form is $root form, false otherwise.
     * @param string $formationToCheck $formationToCheck is current status of the word form in the current state of the finite
     *                         state machine except the apostrophe at the end if it exists.
     * @return string formation with possibly last character dropped and 'ı', 'i', 'u' or 'ü' character added.
     */
    public static function resolveH(TxtWord $root, string $formation, bool $beginningOfSuffix, bool $specialCaseTenseSuffix, bool $rootWord, string $formationToCheck): string
    {
        if ($root->isAbbreviation())
            return $formation . 'i';
        if ($beginningOfSuffix && TurkishLanguage::isVowel(Word::lastPhoneme($formationToCheck)) && !$specialCaseTenseSuffix) {
            return $formation;
        }
        if ($specialCaseTenseSuffix) {
            //eğer ek Hyor eki ise,
            if ($rootWord) {
                if ($root->vowelAChangesToIDuringYSuffixation()) {
                    $result = self::resolveHforSpecialCaseTenseSuffix($formationToCheck, $formation);
                    if ($result != null) {
                        return $result;
                    }
                }
            }
            if (TurkishLanguage::isVowel(Word::lastPhoneme($formationToCheck))) {
                $result = self::resolveHforSpecialCaseTenseSuffix($formationToCheck, $formation);
                if ($result != null) {
                    return $result;
                }
            }
        }
        if (TurkishLanguage::isFrontRoundedVowel(Word::lastVowel($formationToCheck)) || (TurkishLanguage::isBackRoundedVowel(Word::lastVowel($formationToCheck)) && $root->notObeysVowelHarmonyDuringAgglutination())) {
            return $formation . 'ü';
        }
        if ((TurkishLanguage::isFrontUnroundedVowel(Word::lastVowel($formationToCheck)) && (!$root->notObeysVowelHarmonyDuringAgglutination() || !$rootWord)) || ((Word::lastVowel($formationToCheck) == 'a' || Word::lastVowel($formationToCheck) == 'â') && $root->notObeysVowelHarmonyDuringAgglutination())) {
            return $formation . 'i';
        }
        if (TurkishLanguage::isBackRoundedVowel(Word::lastVowel($formationToCheck))) {
            return $formation . 'u';
        }
        if (TurkishLanguage::isBackUnroundedVowel(Word::lastVowel($formationToCheck)) || (TurkishLanguage::isFrontUnroundedVowel(Word::lastVowel($formationToCheck)) && $root->notObeysVowelHarmonyDuringAgglutination())) {
            return $formation . 'ı';
        }
        if ($root->isNumeral() || $root->isFraction() || $root->isReal()) {
            if (str_ends_with($root->getName(), "6") || str_ends_with($root->getName(), "40") || str_ends_with($root->getName(), "60") || str_ends_with($root->getName(), "90")) {
                //6'yı, 40'ı, 60'ı
                return $formation . 'ı';
            } else {
                if (str_ends_with($root->getName(), "3") || str_ends_with($root->getName(), "4") || str_ends_with($root->getName(), "00")) {
                    //3'ü, 4'ü, 100'ü
                    return $formation . 'ü';
                } else {
                    if (str_ends_with($root->getName(), "9") || str_ends_with($root->getName(), "10") || str_ends_with($root->getName(), "30")) {
                        //9'u, 10'u, 30'u
                        return $formation . 'u';
                    } else {
                        //2'yi, 5'i, 8'i
                        return $formation . 'i';
                    }
                }
            }
        }
        if (Word::lastVowel($formationToCheck) == '0') {
            return $formation . 'i';
        }
        return $formation;
    }

    /**
     * The resolveC method takes a {@link String} $formation as an input. If the last phoneme is on of the "çfhkpsşt", it
     * concatenates given $formation with 'ç', if not it concatenates given $formation with 'c'.
     *
     * @param string $formation {@link String} input.
     * @param string $formationToCheck $formation produced until so far.
     * @return string resolved String.
     */
    public static function resolveC(string $formation, string $formationToCheck): string
    {
        if (TurkishLanguage::isSertSessiz(Word::lastPhoneme($formationToCheck))) {
            return $formation . 'ç';
        } else {
            return $formation . 'c';
        }
    }
}