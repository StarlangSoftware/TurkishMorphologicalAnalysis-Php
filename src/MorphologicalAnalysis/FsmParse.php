<?php

namespace olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis;

use olcaytaner\Dictionary\Dictionary\TxtWord;

class FsmParse extends MorphologicalParse
{
    private array $suffixList = [];
    private array $formList = [];
    private array $transitionList = [];
    private array $withList = [];
    private ?string $initialPos;
    private ?string $pos;
    private string $form;
    private ?string $verbAgreement = null;
    private ?string $possessiveAgreement = null;

    /**
     * Another constructor of {@link FsmParse} class which takes a {@link TxtWord} root and a {@link State} as inputs.
     * First, initializes root variable with this {@link TxtWord}. It also initializes form with root's name, pos and
     * initialPos with given {@link State}'s POS, creates 4 new {@link Array} suffixList, formList, transitionList
     * and withList and adds given {@link State} to suffixList, form to formList.
     *
     * @param mixed $root {@link TxtWord} input.
     * @param ?State $startState {@link State} input.
     */
    public function __construct(mixed $root, ?State $startState = null)
    {
        parent::__construct();
        if ($root instanceof TxtWord && $startState !== null) {
            $this->root = $root;
            $this->form = $root->getName();
            $this->pos = $startState->getPos();
            $this->initialPos = $startState->getPos();
            $this->suffixList[] = $startState;
            $this->formList[] = $this->form;
        } else {
            if (is_numeric($root) && $startState !== null) {
                $num = new TxtWord($root);
                $num->addFlag("IS_SAYI");
                $this->root = $num;
                $this->form = $this->root->getName();
                $this->pos = $startState->getPos();
                $this->initialPos = $startState->getPos();
                $this->suffixList[] = $startState;
                $this->formList[] = $this->form;
            } else {
                if ($startState !== null) {
                    $this->root = new TxtWord($root);
                    $this->form = $this->root->getName();
                    $this->pos = $startState->getPos();
                    $this->initialPos = $startState->getPos();
                    $this->suffixList[] = $startState;
                    $this->formList[] = $this->form;
                } else {
                    $this->root = $root;
                }
            }
        }
    }

    /**
     * The constructInflectionalGroups method initially calls the transitionList method and assigns the resulting {@link String}
     * to the parse variable and creates a new {@link Array} as iGs. If parse {@link String} contains a derivational boundary
     * it adds the substring starting from the 0 to the index of derivational boundary to the iGs. If it does not contain a DB,
     * it directly adds parse to the iGs. Then, creates and initializes new {@link Array} as inflectionalGroups and fills with
     * the items of iGs.
     */
    public function constructInflectionalGroups(): void
    {
        $parse = $this->getFsmParseTransitionList();
        $iGs = [];
        while (str_contains($parse, "^DB+")) {
            $iGs[] = mb_substr($parse, 0, mb_strpos($parse, "^DB+"));
            $parse = mb_substr($parse, mb_strpos($parse, "^DB+") + 4);
        }
        $iGs[] = $parse;
        $this->inflectionalGroups[] = new InflectionalGroup(mb_substr($iGs[0], mb_strpos($iGs[0], '+') + 1));
        for ($i = 1; $i < count($iGs); $i++) {
            $this->inflectionalGroups[] = new InflectionalGroup($iGs[$i]);
        }
    }

    /**
     * Getter for the verbAgreement variable.
     *
     * @return string the verbAgreement variable.
     */
    public function getVerbAgreement(): string
    {
        return $this->verbAgreement;
    }

    /**
     * Getter for the getPossessiveAgreement variable.
     *
     * @return string the possessiveAgreement variable.
     */
    public function getPossesiveAgreement(): string
    {
        return $this->possessiveAgreement;
    }

    /**
     * The setAgreement method takes a {@link String} transitionName as an input and if it is one of the A1SG, A2SG, A3SG,
     * A1PL, A2PL or A3PL it assigns transitionName input to the verbAgreement variable. Or if it is ine of the PNON, P1SG, P2SG,P3SG,
     * P1PL, P2PL or P3PL it assigns transitionName input to the possesiveAgreement variable.
     *
     * @param string|null $transitionName {@link String} input.
     */
    public function setAgreement(?string $transitionName = null): void
    {
        if (($transitionName == "A1SG" || $transitionName == "A2SG" ||
            $transitionName == "A3SG" || $transitionName == "A1PL" || $transitionName == "A2PL" ||
            $transitionName == "A3PL")) {
            $this->verbAgreement = $transitionName;
        }
        if (($transitionName == "PNON" || $transitionName == "P1SG" ||
            $transitionName == "P2SG" || $transitionName == "P3SG" || $transitionName == "P1PL" ||
            $transitionName == "P2PL" || $transitionName == "P3PL")) {
            $this->possessiveAgreement = $transitionName;
        }
    }

    /**
     * The getLastLemmaWithTag method takes a String input pos as an input. If given pos is an initial pos then it assigns
     * root to the lemma, and assign null otherwise.  Then, it loops $i times where $i ranges from 1 to size of the formList,
     * if the item at $i-1 of transitionList is not null and contains a derivational boundary with pos but not with ZERO,
     * it assigns the ith item of formList to lemma.
     *
     * @param string $pos {@link String} input.
     * @return string String output lemma.
     */
    public function getLastLemmaWithTag(string $pos): string
    {
        if ($this->initialPos != null && $this->initialPos == $pos) {
            $lemma = $this->root->getName();
        } else {
            $lemma = null;
        }
        for ($i = 1; $i < count($this->formList); $i++) {
            if ($this->transitionList[$i - 1] != null && str_contains($this->transitionList[$i - 1], "^DB+" . $pos) &&
                !str_contains($this->transitionList[$i - 1], "^DB+" . $pos . "+ZERO")) {
                $lemma = $this->formList[$i];
            }
        }
        return $lemma;
    }

    /**
     * The getLastLemma method initially assigns root as lemma. Then, it loops $i times where $i ranges from 1 to size of the formList,
     * if the item at $i-1 of transitionList is not null and contains a derivational boundary, it assigns the ith item of formList to lemma.
     *
     * @return string String output lemma.
     */
    public function getLastLemma(): string
    {
        $lemma = $this->root->getName();
        for ($i = 1; $i < count($this->formList); $i++) {
            if ($this->transitionList[$i - 1] != null && str_contains($this->transitionList[$i - 1], "^DB+")) {
                $lemma = $this->formList[$i];
            }
        }
        return $lemma;
    }

    /**
     * The addSuffix method takes 5 different inputs; {@link State} suffix, {@link String} form, transition, with and toPos.
     * If the pos of given input suffix is not null, it then assigns it to the pos variable. If the pos of the given suffix
     * is null but given toPos is not null than it assigns toPos to pos variable. At the end, it adds suffix to the suffixList,
     * form to the formList, transition to the transitionList and if given with is not 0, it is also added to withList.
     *
     * @param State $suffix {@link State} input.
     * @param string $form {@link String} input.
     * @param string $transition {@link String} input.
     * @param string $with {@link String} input.
     * @param string $toPos {@link String} input.
     */
    public function addSuffix(State $suffix, string $form, string $transition, string $with, string $toPos): void
    {
        if ($suffix->getPos() != null) {
            $this->pos = $suffix->getPos();
        } else {
            if ($toPos != null) {
                $this->pos = $toPos;
            }
        }
        $this->suffixList[] = $suffix;
        $this->formList[] = $form;
        $this->transitionList[] = $transition;
        if ($with != "0") {
            $this->withList[] = $with;
        }
        $this->form = $form;
    }

    /**
     * Getter for the form variable.
     *
     * @return string the form variable.
     */
    public function getSurfaceForm(): string
    {
        return $this->form;
    }

    /**
     * The getStartState method returns the first item of suffixList {@link Array}.
     *
     * @return State the first item of suffixList {@link Array}.
     */
    public function getStartState(): State
    {
        return $this->suffixList[0];
    }

    /**
     * Getter for the pos variable.
     *
     * @return string the pos variable.
     */
    public function getFinalPos(): string
    {
        return $this->pos;
    }

    /**
     * Getter for the initialPos variable.
     *
     * @return string the initialPos variable.
     */
    public function getInitialPos(): string
    {
        return $this->initialPos;
    }

    /**
     * The setForm method takes a {@link String} name as an input and assigns it to the form variable, then it removes
     * the first item of formList {@link Array} and adds the given name to the formList.
     *
     * @param string $name String input to set form.
     */
    public function setForm(string $name): void
    {
        $this->form = $name;
        array_splice($this->formList, 0, 1);
        $this->formList[] = $name;
    }

    /**
     * The getFinalSuffix method returns the last item of suffixList {@link Array}.
     *
     * @return State the last item of suffixList {@link Array}.
     */
    public function getFinalSuffix(): State
    {
        return $this->suffixList[count($this->suffixList) - 1];
    }

    /**
     * The headerTransition method gets the first item of formList and checks for cases;
     * <p>
     * If it is &lt;DOC&gt;, it returns &lt;DOC&gt;+BDTAG which indicates the beginning of a document.
     * If it is &lt;/DOC&gt;, it returns &lt;/DOC&gt;+EDTAG which indicates the ending of a document.
     * If it is &lt;TITLE&gt;, it returns &lt;TITLE&gt;+BTTAG which indicates the beginning of a title.
     * If it is &lt;/TITLE&gt;, it returns &lt;/TITLE&gt;+ETTAG which indicates the ending of a title.
     * If it is &lt;S&gt;, it returns &lt;S&gt;+BSTAG which indicates the beginning of a sentence.
     * If it is &lt;/S&gt;, it returns &lt;/S&gt;+ESTAG which indicates the ending of a sentence.
     *
     * @return string corresponding tags of the headers and an empty {@link String} if any case does not match.
     */
    public function headerTransition(): string
    {
        if ($this->formList[0] == "<DOC>") {
            return "<DOC>+BDTAG";
        }
        if ($this->formList[0] == "</DOC>") {
            return "</DOC>+EDTAG";
        }
        if ($this->formList[0] == "<TITLE>") {
            return "<TITLE>+BTTAG";
        }
        if ($this->formList[0] == "</TITLE>") {
            return "</TITLE>+ETTAG";
        }
        if ($this->formList[0] == "<S>") {
            return "<S>+BSTAG";
        }
        if ($this->formList[0] == "</S>") {
            return "</S>+ESTAG";
        }
        return "";
    }

    /**
     * The pronounTransition method gets the first item of formList and checks for cases;
     * <p>
     * If it is "kendi", it returns kendi+PRON+REFLEXP which indicates a reflexive pronoun.
     * If it is one of the "hep, öbür, topu, öteki, kimse, hiçbiri, tümü, çoğu, hepsi, herkes, başkası, birçoğu, birçokları, biri, birbirleri, birbiri, birkaçı, böylesi, diğeri, cümlesi, bazı, kimi", it returns
     * +PRON+QUANTP which indicates a quantitative pronoun.
     * If it is one of the "o, bu, şu" and if it is "o" it also checks the first item of suffixList and if it is a PronounRoot(DEMONS),
     * it returns +PRON+DEMONSP which indicates a demonstrative pronoun.
     * If it is "ben", it returns +PRON+PERS+A1SG+PNON which indicates a 1st person singular agreement.
     * If it is "sen", it returns +PRON+PERS+A2SG+PNON which indicates a 2nd person singular agreement.
     * If it is "o" and the first item of suffixList, if it is a PronounRoot(PERS), it returns +PRON+PERS+A3SG+PNON which
     * indicates a 3rd person singular agreement.
     * If it is "biz", it returns +PRON+PERS+A1PL+PNON which indicates a 1st person plural agreement.
     * If it is "siz", it returns +PRON+PERS+A2PL+PNON which indicates a 2nd person plural agreement.
     * If it is "onlar" and the first item of suffixList, if it is a PronounRoot(PERS), it returns o+PRON+PERS+A3PL+PNON which
     * indicates a 3rd person plural agreement.
     * If it is one of the "nere, ne, kim, hangi", it returns +PRON+QUESP which indicates a question pronoun.
     *
     * @return string corresponding transitions of pronouns and an empty {@link String} if any case does not match.
     */
    public function pronounTransition(): string
    {
        if ($this->formList[0] == "kendi") {
            return "kendi+PRON+REFLEXP";
        }
        if ($this->formList[0] == "hep" || $this->formList[0] == "öbür" || $this->formList[0] == "topu" ||
            $this->formList[0] == "öteki" || $this->formList[0] == "kimse" || $this->formList[0] == "hiçbiri" ||
            $this->formList[0] == "tümü" || $this->formList[0] == "çoğu" || $this->formList[0] == "hepsi" ||
            $this->formList[0] == "herkes" || $this->formList[0] == "başkası" || $this->formList[0] == "birçoğu" ||
            $this->formList[0] == "birçokları" || $this->formList[0] == "birbiri" || $this->formList[0] == "birbirleri" ||
            $this->formList[0] == "biri" || $this->formList[0] == "birkaçı" || $this->formList[0] == "böylesi" ||
            $this->formList[0] == "diğeri" || $this->formList[0] == "cümlesi" || $this->formList[0] == "bazı" ||
            $this->formList[0] == "kimi") {
            return $this->formList[0] . "+PRON+QUANTP";
        }
        if (($this->formList[0] == "o" && $this->suffixList[0]->getName() == "PronounRoot(DEMONS)") ||
            $this->formList[0] == "bu" || $this->formList[0] == "şu") {
            return $this->formList[0] . "+PRON+DEMONSP";
        }
        if ($this->formList[0] == "ben") {
            return $this->formList[0] . "+PRON+PERS+A1SG+PNON";
        }
        if ($this->formList[0] == "sen") {
            return $this->formList[0] . "+PRON+PERS+A2SG+PNON";
        }
        if ($this->formList[0] == "o" && $this->suffixList[0]->getName() == "PronounRoot(PERS)") {
            return $this->formList[0] . "+PRON+PERS+A3SG+PNON";
        }
        if ($this->formList[0] == "biz") {
            return $this->formList[0] . "+PRON+PERS+A1PL+PNON";
        }
        if ($this->formList[0] == "siz") {
            return $this->formList[0] . "+PRON+PERS+A2PL+PNON";
        }
        if ($this->formList[0] == "onlar") {
            return "o+PRON+PERS+A3PL+PNON";
        }
        if ($this->formList[0] == "nere" || $this->formList[0] == "ne" || $this->formList[0] == "kaçı" ||
            $this->formList[0] == "kim" || $this->formList[0] == "hangi") {
            return $this->formList[0] . "+PRON+QUESP";
        }
        return "";
    }

    /**
     * The transitionList method first creates an empty {@link String} result, then gets the first item of suffixList and checks for cases;
     * <p>
     * If it is one of the "NominalRoot, NominalRootNoPossesive, CompoundNounRoot, NominalRootPlural", it assigns concatenation of first
     * item of formList and +NOUN to the result String.
     * Ex : Birincilik
     * <p>
     * If it is one of the "VerbalRoot, PassiveHn", it assigns concatenation of first item of formList and +VERB to the result String.
     * Ex : Başkalaştı
     * <p>
     * If it is "CardinalRoot", it assigns concatenation of first item of formList and +NUM+CARD to the result String.
     * Ex : Onuncu
     * <p>
     * If it is "FractionRoot", it assigns concatenation of first item of formList and NUM+FRACTION to the result String.
     * Ex : 1/2
     * <p>
     * If it is "TimeRoot", it assigns concatenation of first item of formList and +TIME to the result String.
     * Ex : 14:28
     * <p>
     * If it is "RealRoot", it assigns concatenation of first item of formList and +NUM+REAL to the result String.
     * Ex : 1.2
     * <p>
     * If it is "Punctuation", it assigns concatenation of first item of formList and +PUNC to the result String.
     * Ex : ,
     * <p>
     * If it is "Hashtag", it assigns concatenation of first item of formList and +HASHTAG to the result String.
     * Ex : #
     * <p>
     * If it is "DateRoot", it assigns concatenation of first item of formList and +DATE to the result String.
     * Ex : 11/06/2018
     * <p>
     * If it is "RangeRoot", it assigns concatenation of first item of formList and +RANGE to the result String.
     * Ex : 3-5
     * <p>
     * If it is "Email", it assigns concatenation of first item of formList and +EMAIL to the result String.
     * Ex : abc@
     * <p>
     * If it is "PercentRoot", it assigns concatenation of first item of formList and +PERCENT to the result String.
     * Ex : %12.5
     * <p>
     * If it is "DeterminerRoot", it assigns concatenation of first item of formList and +DET to the result String.
     * Ex : Birtakım
     * <p>
     * If it is "ConjunctionRoot", it assigns concatenation of first item of formList and +CONJ to the result String.
     * Ex : Ama
     * <p>
     * If it is "AdverbRoot", it assigns concatenation of first item of formList and +ADV to the result String.
     * Ex : Acilen
     * <p>
     * If it is "ProperRoot", it assigns concatenation of first item of formList and +NOUN+PROP to the result String.
     * Ex : Ahmet
     * <p>
     * If it is "HeaderRoot", it assigns the result of the headerTransition method to the result String.
     * Ex : &lt;DOC&gt;
     * <p>
     * If it is "InterjectionRoot", it assigns concatenation of first item of formList and +INTERJ to the result String.
     * Ex : Hey
     * <p>
     * If it is "DuplicateRoot", it assigns concatenation of first item of formList and +DUP to the result String.
     * Ex : Allak
     * <p>
     * If it is "CodeRoot", it assigns concatenation of first item of formList and +CODE to the result String.
     * Ex : 5000-WX
     * <p>
     * If it is "MetricRoot", it assigns concatenation of first item of formList and +METRIC to the result String.
     * Ex : 6cmx12cm
     * <p>
     * If it is "QuestionRoot", it assigns concatenation of first item of formList and +QUES to the result String.
     * Ex : Mı
     * <p>
     * If it is "PostP", and the first item of formList is one of the "karşı, ilişkin, göre, kadar, ait, yönelik, rağmen, değin,
     * dek, doğru, karşın, dair, atfen, binaen, hitaben, istinaden, mahsuben, mukabil, nazaran", it assigns concatenation of first
     * item of formList and +POSTP+PCDAT to the result String.
     * Ex : İlişkin
     * <p>
     * If it is "PostP", and the first item of formList is one of the "sonra, önce, beri, fazla, dolayı, itibaren, başka,
     * çok, evvel, ötürü, yana, öte, aşağı, yukarı, dışarı, az, gayrı", it assigns concatenation of first
     * item of formList and +POSTP+PCABL to the result String.
     * Ex : Başka
     * <p>
     * If it is "PostP", and the first item of formList is "yanısıra", it assigns concatenation of first
     * item of formList and +POSTP+PCGEN to the result String.
     * Ex : Yanısıra
     * <p>
     * If it is "PostP", and the first item of formList is one of the "birlikte, beraber", it assigns concatenation of first
     * item of formList and +PPOSTP+PCINS to the result String.
     * Ex : Birlikte
     * <p>
     * If it is "PostP", and the first item of formList is one of the "aşkın, takiben", it assigns concatenation of first
     * item of formList and +POSTP+PCACC to the result String.
     * Ex : Takiben
     * <p>
     * If it is "PostP", it assigns concatenation of first item of formList and +POSTP+PCNOM to the result String.
     * <p>
     * If it is "PronounRoot", it assigns result of the pronounTransition method to the result String.
     * Ex : Ben
     * <p>
     * If it is "OrdinalRoot", it assigns concatenation of first item of formList and +NUM+ORD to the result String.
     * Ex : Altıncı
     * <p>
     * If it starts with "Adjective", it assigns concatenation of first item of formList and +ADJ to the result String.
     * Ex : Güzel
     * <p>
     * At the end, it loops through the formList and concatenates each item with result {@link String}.
     *
     * @return string String result accumulated with items of formList.
     */
    public function getFsmParseTransitionList(): string
    {
        $result = "";
        if ($this->suffixList[0]->getName() == "NominalRoot" || $this->suffixList[0]->getName() == "NominalRootNoPossesive" ||
            $this->suffixList[0]->getName() == "CompoundNounRoot" || $this->suffixList[0]->getName() == "NominalRootPlural") {
            $result = $this->formList[0] . "+NOUN";
        } else {
            if (str_starts_with($this->suffixList[0]->getName(), "VerbalRoot") || $this->suffixList[0]->getName() == "PassiveHn") {
                $result = $this->formList[0] . "+VERB";
            } else {
                if ($this->suffixList[0]->getName() == "CardinalRoot") {
                    $result = $this->formList[0] . "+NUM+CARD";
                } else {
                    if ($this->suffixList[0]->getName() == "FractionRoot") {
                        $result = $this->formList[0] . "+NUM+FRACTION";
                    } else {
                        if ($this->suffixList[0]->getName() == "TimeRoot") {
                            $result = $this->formList[0] . "+TIME";
                        } else {
                            if ($this->suffixList[0]->getName() == "RealRoot") {
                                $result = $this->formList[0] . "+NUM+REAL";
                            } else {
                                if ($this->suffixList[0]->getName() == "Punctuation") {
                                    $result = $this->formList[0] . "+PUNC";
                                } else {
                                    if ($this->suffixList[0]->getName() == "Hashtag") {
                                        $result = $this->formList[0] . "+HASHTAG";
                                    } else {
                                        if ($this->suffixList[0]->getName() == "DateRoot") {
                                            $result = $this->formList[0] . "+DATE";
                                        } else {
                                            if ($this->suffixList[0]->getName() == "RangeRoot") {
                                                $result = $this->formList[0] . "+RANGE";
                                            } else {
                                                if ($this->suffixList[0]->getName() == "Email") {
                                                    $result = $this->formList[0] . "+EMAIL";
                                                } else {
                                                    if ($this->suffixList[0]->getName() == "PercentRoot") {
                                                        $result = $this->formList[0] . "+PERCENT";
                                                    } else {
                                                        if ($this->suffixList[0]->getName() == "DeterminerRoot") {
                                                            $result = $this->formList[0] . "+DET";
                                                        } else {
                                                            if ($this->suffixList[0]->getName() == "ConjunctionRoot") {
                                                                $result = $this->formList[0] . "+CONJ";
                                                            } else {
                                                                if ($this->suffixList[0]->getName() == "AdverbRoot") {
                                                                    $result = $this->formList[0] . "+ADV";
                                                                } else {
                                                                    if ($this->suffixList[0]->getName() == "ProperRoot") {
                                                                        $result = $this->formList[0] . "+NOUN+PROP";
                                                                    } else {
                                                                        if ($this->suffixList[0]->getName() == "HeaderRoot") {
                                                                            $result = $this->headerTransition();
                                                                        } else {
                                                                            if ($this->suffixList[0]->getName() == "InterjectionRoot") {
                                                                                $result = $this->formList[0] . "+INTERJ";
                                                                            } else {
                                                                                if ($this->suffixList[0]->getName() == "DuplicateRoot") {
                                                                                    $result = $this->formList[0] . "+DUP";
                                                                                } else {
                                                                                    if ($this->suffixList[0]->getName() == "CodeRoot") {
                                                                                        $result = $this->formList[0] . "+CODE";
                                                                                    } else {
                                                                                        if ($this->suffixList[0]->getName() == "MetricRoot") {
                                                                                            $result = $this->formList[0] . "+METRIC";
                                                                                        } else {
                                                                                            if ($this->suffixList[0]->getName() == "QuestionRoot") {
                                                                                                $result = "mi+QUES";
                                                                                            } else {
                                                                                                if ($this->suffixList[0]->getName() == "PostP") {
                                                                                                    if ($this->formList[0] == "karşı" || $this->formList[0] == "ilişkin" || $this->formList[0] == "göre" || $this->formList[0] == "kadar" || $this->formList[0] == "ait" || $this->formList[0] == "yönelik" || $this->formList[0] == "rağmen" || $this->formList[0] == "değin" || $this->formList[0] == "dek" || $this->formList[0] == "doğru" || $this->formList[0] == "karşın" || $this->formList[0] == "dair" || $this->formList[0] == "atfen" || $this->formList[0] == "binaen" || $this->formList[0] == "hitaben" || $this->formList[0] == "istinaden" || $this->formList[0] == "mahsuben" || $this->formList[0] == "mukabil" || $this->formList[0] == "nazaran") {
                                                                                                        $result = $this->formList[0] . "+POSTP+PCDAT";
                                                                                                    } else {
                                                                                                        if ($this->formList[0] == "sonra" || $this->formList[0] == "önce" || $this->formList[0] == "beri" || $this->formList[0] == "fazla" || $this->formList[0] == "dolayı" || $this->formList[0] == "itibaren" || $this->formList[0] == "başka" || $this->formList[0] == "çok" || $this->formList[0] == "evvel" || $this->formList[0] == "ötürü" || $this->formList[0] == "yana" || $this->formList[0] == "öte" || $this->formList[0] == "aşağı" || $this->formList[0] == "yukarı" || $this->formList[0] == "dışarı" || $this->formList[0] == "az" || $this->formList[0] == "gayrı") {
                                                                                                            $result = $this->formList[0] . "+POSTP+PCABL";
                                                                                                        } else {
                                                                                                            if ($this->formList[0] == "yanısıra") {
                                                                                                                $result = $this->formList[0] . "+POSTP+PCGEN";
                                                                                                            } else {
                                                                                                                if ($this->formList[0] == "birlikte" || $this->formList[0] == "beraber") {
                                                                                                                    $result = $this->formList[0] . "+POSTP+PCINS";
                                                                                                                } else {
                                                                                                                    if ($this->formList[0] == "aşkın" || $this->formList[0] == "takiben") {
                                                                                                                        $result = $this->formList[0] . "+POSTP+PCACC";
                                                                                                                    } else {
                                                                                                                        $result = $this->formList[0] . "+POSTP+PCNOM";
                                                                                                                    }
                                                                                                                }
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                } else {
                                                                                                    if (str_starts_with($this->suffixList[0]->getName(), "PronounRoot")) {
                                                                                                        $result = $this->pronounTransition();
                                                                                                    } else {
                                                                                                        if ($this->suffixList[0]->getName() == "OrdinalRoot") {
                                                                                                            $result = $this->formList[0] . "+NUM+ORD";
                                                                                                        } else {
                                                                                                            if (str_starts_with($this->suffixList[0]->getName(), "Adjective")) {
                                                                                                                $result = $this->formList[0] . "+ADJ";
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
        foreach ($this->transitionList as $transition) {
            if ($transition != "") {
                if (!str_starts_with($transition, "^")) {
                    $result = $result . "+" . $transition;
                } else {
                    $result = $result . $transition;
                }
            }
        }
        return $result;
    }

    /**
     * The suffixList method gets the first items of suffixList and formList and concatenates them with parenthesis and
     * assigns a String result. Then, loops through the formList and it the current ith item is not equal to previous
     * item it accumulates ith items of formList and suffixList to the result {@link String}.
     *
     * @return string string result {@link String} accumulated with the items of formList and suffixList.
     */
    public function getSuffixList(): string
    {
        $result = $this->suffixList[0]->getName() . '(' . $this->formList[0] . ')';
        for ($i = 1; $i < count($this->formList); $i++) {
            if ($this->formList[$i] != $this->formList[$i - 1]) {
                $result = $result . "+" . $this->suffixList[$i]->getName() . '(' . $this->formList[$i] . ')';
            }
        }
        return $result;
    }

    /**
     * The withList method gets the root as a result {@link String} then loops through the withList and concatenates each item
     * with result {@link String}.
     *
     * @return string result {@link String} accumulated with items of withList.
     */
    public function getWithList(): string
    {
        $result = $this->root->getName();
        foreach ($this->withList as $aWith) {
            $result = $result . "+" . $aWith;
        }
        return $result;
    }

    /**
     * Replace root word of the current parse with the new root word and returns the new word.
     * @param TxtWord $newRoot Replaced root word
     * @return string Root word of the parse will be replaced with the newRoot and the resulting surface form is returned.
     */
    public function replaceRootWord(TxtWord $newRoot): string
    {
        $result = $newRoot->getName();
        foreach ($this->withList as $aWith) {
            $transition = new Transition($aWith, null, null);
            $result = $transition->makeTransition($newRoot, $result);
        }
        return $result;
    }

    /**
     * The overridden toString method which returns transitionList method.
     *
     * @return string returns transitionList method.
     */

    public function __toString(): string
    {
        return $this->getFsmParseTransitionList();
    }

    /**
     * In order to morphologically parse special proper nouns in Turkish, whose affixes obeys not the original but their
     * pronunciations, the morphologicalAnalysis method replaces the original word with its pronunciation and do the
     * rest. This method reverts it back, that is it restores its original form by replacing the pronunciations in the
     * parses with the original form.
     * @param string $original Original form of the proper noun.
     * @param string $pronunciation Pronunciation of the proper noun.
     */
    public function restoreOriginalForm(string $original, string $pronunciation): void
    {
        $this->root = new TxtWord($original, "IS_OA");
        $this->form = $original . mb_substr($this->form, mb_strlen($pronunciation));
        for ($i = 0; $i < count($this->formList); $i++) {
            $this->formList[$i] = $original . mb_substr($this->formList[$i], mb_strlen($pronunciation));
        }
    }
}