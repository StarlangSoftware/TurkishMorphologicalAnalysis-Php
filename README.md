Morphological Analysis
============

## Morphology

In linguistics, the term morphology refers to the study of the internal structure of words. Each word is assumed to consist of one or more morphemes, which can be defined as the smallest linguistic unit having a particular meaning or grammatical function. One can come across morphologically simplex words, i.e. roots, as well as morphologically complex ones, such as compounds or affixed forms.

Batı-lı-laş-tır-ıl-ama-yan-lar-dan-mış-ız 
west-With-Make-Caus-Pass-Neg.Abil-Nom-Pl-Abl-Evid-A3Pl
‘It appears that we are among the ones that cannot be westernized.’

The morphemes that constitute a word combine in a (more or less) strict order. Most morphologically complex words are in the ”ROOT-SUFFIX1-SUFFIX2-...” structure. Affixes have two types: (i) derivational affixes, which change the meaning and sometimes also the grammatical category of the base they are attached to, and (ii) inflectional affixes serving particular grammatical functions. In general, derivational suffixes precede inflectional ones. The order of derivational suffixes is reflected on the meaning of the derived form. For instance, consider the combination of the noun göz ‘eye’ with two derivational suffixes -lIK and -CI: Even though the same three morphemes are used, the meaning of a word like gözcülük ‘scouting’ is clearly different from that of gözlükçü ‘optician’.

## Dilbaz

Here we present a new morphological analyzer, which is (i) open: The latest version of source codes, the lexicon, and the morphotactic rule engine are all available here, (ii) extendible: One of the disadvantages of other morphological analyzers is that their lexicons are fixed or unmodifiable, which prevents to add new bare-forms to the morphological analyzer. In our morphological analyzer, the lexicon is in text form and is easily modifiable, (iii) fast: Morphological analysis is one of the core components of any NLP process. It must be very fast to handle huge corpora. Compared to other morphological analyzers, our analyzer is capable of analyzing hundreds of thousands words per second, which makes it one of the fastest Turkish morphological analyzers available.

The morphological analyzer consists of five main components, namely, a lexicon, a finite state transducer, a rule engine for suffixation, a trie data structure, and a least recently used (LRU) cache.

In this analyzer, we assume all idiosyncratic information to be encoded in the lexicon. While phonologically conditioned allomorphy will be dealt with by the transducer, other types of allomorphy, all exceptional forms to otherwise regular processes, as well as words formed through derivation (except for the few transparently compositional derivational suffixes are considered to be included in the lexicon.

In our morphological analyzer, finite state transducer is encoded in an xml file.

To overcome the irregularities and also to accelerate the search for the bareforms, we use a trie data structure in our morphological analyzer, and store all words in our lexicon in that data structure. For the regular words, we only store that word in our trie, whereas for irregular words we store both the original form and some prefix of that word. 

Video Lectures
============

[<img src="https://github.com/StarlangSoftware/TurkishMorphologicalAnalysis/blob/master/video1.jpg" width="50%">](https://youtu.be/KxguxpbgDQc)[<img src="https://github.com/StarlangSoftware/TurkishMorphologicalAnalysis/blob/master/video2.jpg" width="50%">](https://youtu.be/UMmA2LMkAkw)[<img src="https://github.com/StarlangSoftware/TurkishMorphologicalAnalysis/blob/master/video3.jpg" width="50%">](https://youtu.be/dP97ovMSSfE)[<img src="https://github.com/StarlangSoftware/TurkishMorphologicalAnalysis/blob/master/video4.jpg" width="50%">](https://youtu.be/Tgmy5tts_pY)

For Developers
============

You can also see [Python](https://github.com/starlangsoftware/TurkishMorphologicalAnalysis-Py), 
[Java](https://github.com/starlangsoftware/TurkishMorphologicalAnalysis), [C++](https://github.com/starlangsoftware/TurkishMorphologicalAnalysis-CPP), [C](https://github.com/starlangsoftware/TurkishMorphologicalAnalysis-C),
[Swift](https://github.com/starlangsoftware/TurkishMorphologicalAnalysis-Swift), [Cython](https://github.com/starlangsoftware/TurkishMorphologicalAnalysis-Cy), [Js](https://github.com/starlangsoftware/TurkishMorphologicalAnalysis-Js),
or [C#](https://github.com/starlangsoftware/TurkishMorphologicalAnalysis-CS) repository.

For Contibutors
============

### composer.json file

1. autoload is important when this package will be imported.
```
  "autoload": {
    "psr-4": {
      "olcaytaner\\WordNet\\": "src/"
    }
  },
```
2. Dependencies should be maximum (not only direct but also indirect references should also be given), everything directly in the code should be given here.
```
  "require-dev": {
    "phpunit/phpunit": "11.4.0",
    "olcaytaner/dictionary": "1.0.0",
    "olcaytaner/xmlparser": "1.0.1",
    "olcaytaner/morphologicalanalysis": "1.0.0"
  }
```

### Data files
1. Add data files to the project folder. Subprojects should include all data files of the parent projects.

### Php files

1. Do not forget to comment each function.
```
    /**
     * Returns true if specified semantic relation type presents in the relations list.
     *
     * @param SemanticRelationType $relationType element whose presence in the list is to be tested
     * @return bool true if specified semantic relation type presents in the relations list
     */
    public function containsRelationType(SemanticRelationType $relationType): bool{
        foreach ($this->relations as $relation){
            if ($relation instanceof SematicRelation && $relation->getRelationType() == $relationType){
                return true;
            }
        }
        return false;
    }
```
2. Function names should follow caml case.
```
    public function getRelation(int $index): Relation{
```
3. Write getter and setter methods.
```
    public function getOrigin(): ?string
    public function setName(string $name): void
```
4. Use standard javascript test style by extending the TestCase class. Use setup when necessary.
```
class WordNetTest extends TestCase
{
    private WordNet $turkish;

    protected function setUp(): void
    {
        ini_set('memory_limit', '450M');
        $this->turkish = new WordNet();
    }

    public function testSize()
    {
        $this->assertEquals(78327, $this->turkish->size());
    }
```
5. Enumerated types should be declared with enum.
```
enum CategoryType
{
    case MATHEMATICS;
    case SPORT;
    case MUSIC;
    case SLANG;
    case BOTANIC;
```
6. If there are multiple constructors for a class, define them as constructor1, constructor2, ..., then from the original constructor call these methods.
```
    public function constructor1(string $path, string $fileName): void
    public function constructor2(string $path, string $extension, int $index): void
    public function __construct(string $path, string $extension, ?int $index = null)
```
7. Use __toString method if necessary to create strings from objects.
```
    public function __toString(): string
```
8. Use xmlparser package for parsing xml files.
```
  $doc = new XmlDocument("../test.xml");
  $doc->parse();
  $root = $doc->getFirstChild();
  $firstChild = $root->getFirstChild();
```
