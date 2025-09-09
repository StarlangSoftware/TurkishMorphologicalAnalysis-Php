<?php

namespace olcaytaner\MorphologicalAnalysis\MorphologicalAnalysis;

enum MorphologicalTag
{
    /**
     * Noun : Alengir
     */
case NOUN;
    /**
     * Adverb : Alelacele
     */
case ADVERB;
    /**
     * Adjective : Alengirli
     */
case ADJECTIVE;
    /**
     * Verb : Alıkoy
     */
case VERB;
    /**
     * 1st person singular agreement : Ben gelirim
     */
case A1SG;
    /**
     * 2nd person singular agreement : Sen gelirsin
     */
case A2SG;
    /**
     * 3rd person singular agreement : O gelir
     */
case A3SG;
    /**
     * 1st person plural agreement : Biz geliriz
     */
case A1PL;
    /**
     * 2nd person plural agreement : Siz gelirsiniz
     */
case A2PL;
    /**
     * 3rd person plural agreement : Onlar gelirler
     */
case A3PL;
    /**
     * 1st person singular possessive : Benim
     */
case P1SG;
    /**
     * 2nd person singular possessive :Senin
     */
case P2SG;
    /**
     * 3rd person singular possessive : Onun
     */
case P3SG;
    /**
     * 1st person plural possessive :  Bizim
     */
case P1PL;
    /**
     * 2nd person plural possessive : Sizin
     */
case P2PL;
    /**
     * 3rd person plural possessive : Onların
     */
case P3PL;
    /**
     * Proper noun : John
     */
case PROPERNOUN;
    /**
     * None possessive : Ev
     */
case PNON;
    /**
     * Nominative Case : Kedi uyuyor.
     */
case NOMINATIVE;
    /**
     * With : Kalemle
     */
case WITH;
    /**
     * Without : Dikişsiz
     */
case WITHOUT;
    /**
     * Accusatıve : Beni
     */
case ACCUSATIVE;
    /**
     * Dative case : Bana
     */
case DATIVE;
    /**
     * Genitive : Benim
     */
case GENITIVE;
    /**
     * Ablative : Okuldan
     */
case ABLATIVE;
    /**
     * Perosnal pronoun : O
     */
case PERSONALPRONOUN;
    /**
     * Zero Derivation : Kırmızıydı
     */
case ZERO;
    /**
     * Ability, possibility : Olabilir
     */
case ABLE;
    /**
     * Negative : Yapama
     */
case NEGATIVE;
    /**
     * Past tense : Gitti
     */
case PASTTENSE;
    /**
     * Conjunction or disjunction : Ama, ise
     */
case CONJUNCTION;
    /**
     * Determiner : Birtakım
     */
case DETERMINER;
    /**
     * Duplication : Çıtır çıtır
     */
case DUPLICATION;
    /**
     * Interjection : Agucuk
     */
case INTERJECTION;
    /**
     * Number : bir
     */
case NUMBER;
    /**
     * Post posıtıon : Atfen
     */
case POSTPOSITION;
    /**
     * Punctuation : +
     */
case PUNCTUATION;
    /**
     * Question : Mı
     */
case QUESTION;
    /**
     * Agent : Toplayıcı
     */
case AGENT;
    /**
     * By doing so : Zıplayarak
     */
case BYDOINGSO;
    /**
     * Cardinal : yüz, bin
     */
case CARDINAL;
    /**
     * Causative Form : Pişirmek
     */
case CAUSATIVE;
    /**
     * Demonstrative pronoun : Bu, şu
     */
case DEMONSTRATIVEPRONOUN;
    /**
     * Distributive : altışar
     */
case DISTRIBUTIVE;
    /**
     * Fit for : Ahmetlik
     */
case FITFOR;
    /**
     * Future participle : Gülecek
     */
case FUTUREPARTICIPLE;
    /**
     * Infinitive : Biri
     */
case INFINITIVE;
    /**
     * Ness : Ağırbaşlılık
     */
case NESS;
    /**
     * Ordinal Number : Altıncı
     */
case ORDINAL;
    /**
     * Passive : Açıldı
     */
case PASSIVE;
    /**
     * Past participle : Kırılmış
     */
case PASTPARTICIPLE;
    /**
     * Present partıcıple : Sarılan
     */
case PRESENTPARTICIPLE;
    /**
     * Question pronoun : Kim
     */
case QUESTIONPRONOUN;
    /**
     * Quantitative pronoun : Each
     */
case QUANTITATIVEPRONOUN;
    /**
     * Range : 1 - 3
     */
case RANGE;
    /**
     * Ratio : 1/2
     */
case RATIO;
    /**
     * Real : 1.0
     */
case REAL;
    /**
     * Reciprocal verb : Görüşmek
     */
case RECIPROCAL;
    /**
     * Reflexive : Kendi
     */
case REFLEXIVE;
    /**
     * Reflexive pronoun : Kendim
     */
case REFLEXIVEPRONOUN;
    /**
     * Time : 14:28
     */
case TIME;
    /**
     * When : Okuyunca
     */
case WHEN;
    /**
     * While : Gelirken
     */
case WHILE;
    /**
     * Without having done so : Çaktırmadan
     */
case WITHOUTHAVINGDONESO;
    /**
     * PC ablative : Başka
     */
case PCABLATIVE;
    /***
     * PC accusative : Takiben
     */
case PCACCUSATIVE;
    /**
     * PC dative : İlişkin
     */
case PCDATIVE;
    /**
     * PC genitive : Yanısıra
     */
case PCGENITIVE;
    /**
     * PC instrumental : Birlikte
     */
case PCINSTRUMENTAL;
    /**
     * PC nominative
     */
case PCNOMINATIVE;
    /**
     * Acquire : Kazanılan
     */
case ACQUIRE;
    /**
     * Act of : Aldatmaca
     */
case ACTOF;
    /**
     * After doing so : Yapıp
     */
case AFTERDOINGSO;
    /**
     * Almost : Dikensi
     */
case ALMOST;
    /**
     * As : gibi
     */
case AS;
    /**
     * As if : Yaşarmışcasına
     */
case ASIF;
    /**
     * Become : Abideleş
     */
case BECOME;
    /**
     * Ever since : Çıkagel
     */
case EVERSINCE;
    /**
     * Projection : Öpülesi
     */
case FEELLIKE;
    /**
     * Hastility : Yapıver
     */
case HASTILY;
    /**
     * In between : Arasında
     */
case INBETWEEN;
    /**
     * Just like : Destansı
     */
case JUSTLIKE;
    /**
     * -LY : Akıllıca
     */
case LY;
    /**
     * Related to : Davranışsal
     */
case RELATED;
    /**
     * Continuous : Yapadur
     */
case REPEAT;
    /**
     * Since doing so : Amasyalı
     */
case SINCE;
    /**
     * Since doing so : Amasyalı
     */
case SINCEDOINGSO;
    /**
     * Start : Alıkoy
     */
case START;
    /**
     * Stay : Bakakal
     */
case STAY;
    /**
     * Equative : Öylece
     */
case EQUATIVE;
    /**
     * Instrumental : Kışın, arabayla
     */
case INSTRUMENTAL;
    /**
     * Aorist Tense : Her hafta sonunda futbol oynarlar.
     */
case AORIST;
    /**
     * Desire/Past Auxiliary : Çıkarsa
     */
case DESIRE;
    /**
     * Future : Yağacak
     */
case FUTURE;
    /**
     * Imperative : Otur!
     */
case IMPERATIVE;
    /**
     * Narrative Past Tense : Oluşmuş
     */
case NARRATIVE;
    /**
     * Necessity : Yapmalı
     */
case NECESSITY;
    /**
     * Optative : Doğanaya
     */
case OPTATIVE;
    /**
     * Past tense : Gitti
     */
case PAST;
    /**
     * Present partıcıple : Sarılan
     */
case PRESENT;
    /**
     * Progressive : Görüyorum
     */
case PROGRESSIVE1;
    /**
     * Progressive : Görmekteyim
     */
case PROGRESSIVE2;
    /**
     * Conditional : Gelirse
     */
case CONDITIONAL;
    /**
     * Copula : Mavidir
     */
case COPULA;
    /**
     * Positive : Gittim
     */
case POSITIVE;
    /**
     * Pronoun : Ben
     */
case PRONOUN;
    /**
     * Locative : Aşağıda
     */
case LOCATIVE;
    /**
     * Relative : Gelenin
     */
case RELATIVE;
    /**
     * Demonstrative : Bu
     */
case DEMONSTRATIVE;
    /**
     * Infinitive2 : Gitme
     */
case INFINITIVE2;
    /**
     * Infinitive3 : Gidiş
     */
case INFINITIVE3;
    /**
     * Sentence beginning header
     */
case BEGINNINGOFSENTENCE;
    /**
     * Sentence ending header
     */
case ENDOFSENTENCE;
    /**
     * Title beginning header
     */
case BEGINNINGOFTITLE;
    /**
     * Title ending header
     */
case ENDOFTITLE;
    /**
     * Document beginning header
     */
case BEGINNINGOFDOCUMENT;
    /**
     * Document ending header
     */
case ENDOFDOCUMENT;
    /**
     * As long as : Yaşadıkça
     */
case ASLONGAS;
    /**
     * Adamantly
     */
case ADAMANTLY;
    /**
     * Percent : 15%
     */
case PERCENT;
    /**
     * Without being able to have done so: kararlamadan
     */
case WITHOUTBEINGABLETOHAVEDONESO;
    /**
     * Dimension : Küçücük
     */
case DIMENSION;
    /**
     * Notable state : Anlaşılmazlık
     */
case NOTABLESTATE;
    /**
     * Fraction : 1/2
     */
case FRACTION;
    /**
     * Hash tag : #
     */
case HASHTAG;
    /**
     * E-mail : @
     */
case EMAIL;
    /**
     * Date : 11/06/2018
     */
case DATE;
    /**
     * Code : i7-9700K
     */
case CODE;
    /**
     * Metric : 6cmx7cmx8cm
     */
case METRIC;
    /**
     * Polite: yapınız, gidiniz
     */
case POLITE;
    /**
     * Urge: Baksana, yapsana
     */
case URGE;
}
