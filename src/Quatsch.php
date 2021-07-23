<?php
namespace Quatsch;

final class Quatsch
{
    /**
     * Default mean length of a sentence.
     */
    public const DEFAULT_SENTENCE_WORDS_COUNT = 24.46;

    /**
     * Default mean length of a sentence.
     */
    public const DEFAULT_SENTENCE_WORDS_DEVIATION = 5.08;
    public const DEFAULT_PARAGRAPH_SENTENCES_COUNT = 5.8;
    public const DEFAULT_PARAGRAPH_SENTENCES_DEVIATION = 1.93;

    /**
     * Fixed sentence start used to start longer generated texts.
     */
    private const SENTENCE_START = [
        'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing',
        'elit',
    ];

    /**
     * Generated text vocabulary, on top of `SENTENCE_START`.
     */
    private const VOCAB = [
        'a', 'ac', 'accumsan', 'ad', 'aenean', 'aliquam', 'aliquet', 'ante',
        'aptent', 'arcu', 'at', 'auctor', 'augue', 'bibendum', 'blandit',
        'class', 'commodo', 'condimentum', 'congue', 'consequat', 'conubia',
        'convallis', 'cras', 'cubilia', 'curabitur', 'curae', 'cursus',
        'dapibus', 'diam', 'dictum', 'dictumst', 'dignissim', 'dis', 'donec',
        'dui', 'duis', 'efficitur', 'egestas', 'eget', 'eleifend', 'elementum',
        'enim', 'erat', 'eros', 'est', 'et', 'etiam', 'eu', 'euismod', 'ex',
        'facilisi', 'facilisis', 'fames', 'faucibus', 'felis', 'fermentum',
        'feugiat', 'finibus', 'fringilla', 'fusce', 'gravida', 'habitant',
        'habitasse', 'hac', 'hendrerit', 'himenaeos', 'iaculis', 'id',
        'imperdiet', 'in', 'inceptos', 'integer', 'interdum', 'justo',
        'lacinia', 'lacus', 'laoreet', 'lectus', 'leo', 'libero', 'ligula',
        'litora', 'lobortis', 'luctus', 'maecenas', 'magna', 'magnis',
        'malesuada', 'massa', 'mattis', 'mauris', 'maximus', 'metus', 'mi',
        'molestie', 'mollis', 'montes', 'morbi', 'mus', 'nam', 'nascetur',
        'natoque', 'nec', 'neque', 'netus', 'nibh', 'nisi', 'nisl', 'non',
        'nostra', 'nulla', 'nullam', 'nunc', 'odio', 'orci', 'ornare',
        'parturient', 'pellentesque', 'penatibus', 'per', 'pharetra',
        'phasellus', 'placerat', 'platea', 'porta', 'porttitor', 'posuere',
        'potenti', 'praesent', 'pretium', 'primis', 'proin', 'pulvinar',
        'purus', 'quam', 'quis', 'quisque', 'rhoncus', 'ridiculus', 'risus',
        'rutrum', 'sagittis', 'sapien', 'scelerisque', 'sed', 'sem', 'semper',
        'senectus', 'sociosqu', 'sodales', 'sollicitudin', 'suscipit',
        'suspendisse', 'taciti', 'tellus', 'tempor', 'tempus', 'tincidunt',
        'torquent', 'tortor', 'tristique', 'turpis', 'ullamcorper', 'ultrices',
        'ultricies', 'urna', 'ut', 'varius', 'vehicula', 'vel', 'velit',
        'venenatis', 'vestibulum', 'vitae', 'vivamus', 'viverra', 'volutpat',
        'vulputate',
    ];

    private XoShiRo128pp $prng;
    private string $lastWord = '';

    /**
     * Create an instance.
     *
     * Optionally takes a seed for the PRNG.
     */
    public function __construct(int $seed = null)
    {
        $this->prng = XoShiRo128pp::from_rand($seed);
    }

    /**
     * Generate an integer and return it unprocessed from the underlying PRNG.
     *
     * The integer is in the range `[0, 2**32)`.
     */
    public function rawInt(): int
    {
        return $this->prng->next();
    }

    /**
     * Generate a number in the range `[0, 1]` or `[0, 1)`.
     */
    public function factor(bool $inclusive = true): float
    {
        $val = (float) $this->rawInt();

        return $val / (($inclusive ? 0 : 1) + (float) XoShiRo128pp::MAX);
    }

    /**
     * Generate a number with Gaussian distribution.
     *
     * Uses the Marsaglia polar method.
     */
    public function gauss(float $mean = 0, float $deviation = 1): float
    {
        do {
            $x = $this->factor() * 2 - 1;
            $y = $this->factor() * 2 - 1;
            $s = $x * $x + $y * $y;
        } while ($s <= 0 || $s >= 1);

        $t = sqrt(-2 * log($s) / $s);
        $v = $x * $t; // discard y

        return $mean + $v * $deviation;
    }

    /**
     * Generate an integer in the range `[min, max]`.
     */
    public function int(int $min, int $max): int
    {
        return $min + (int) ($this->factor(inclusive: false) * ($max - $min + 1));
    }

    /**
     * Generates a boolean.
     */
    public function bool(float $probability = 0.5): bool
    {
        return $this->factor() >= $probability;
    }

    /**
     * Generates a DateTime in the given range (inclusive).
     *
     * Bounds can be specified as DateTime objects, integer Unix timestamps, or
     * strings accepted by regular DateTime constructors.
     *
     * Note that this method has second granularity.
     */
    public function datetime(
        \DateTimeInterface|int|string $min,
        \DateTimeInterface|int|string $max,
    ): \DateTimeImmutable {
        $min = self::normalizeTimestamp($min);
        $max = self::normalizeTimestamp($max);
        return new \DateTimeImmutable('@' . $this->int($min, $max));
    }

    /**
     * Returns a random array key.
     *
     * @param mixed[] $arr
     * @return mixed
     */
    public function arrayKey(array $arr)
    {
        $keys = array_keys($arr);
        $idx = $this->int(0, count($keys) - 1);
        return $keys[$idx];
    }

    /**
     * Returns a random array value.
     *
     * @param mixed[] $arr
     * @return mixed
     */
    public function arrayValue(array $arr)
    {
        $values = array_values($arr);
        $idx = $this->int(0, count($values) - 1);
        return $values[$idx];
    }

    /**
     * Shuffle an array.
     *
     * NOTE: Like regular PHP `shuffle()`, this function assigns new keys to
     * the elements in the array.
     *
     * @param mixed[] $arr
     */
    public function shuffle(array &$arr): void
    {
        usort($arr, function ($a, $b) {
            return $this->bool() ? -1 : 1;
        });
    }

    /**
     * Generates a number of words based on lorem ipsum.
     */
    public function words(
        float $count,
        float $deviation = 0,
        bool $fixedStart = false,
    ): string {
        $arr = $this->wordsArray(...func_get_args());
        return implode(' ', $arr);
    }

    /**
     * Generates a number of words based on lorem ipsum.
     *
     * @return string[]
     */
    public function wordsArray(
        float $count,
        float $deviation = 0,
        bool $fixedStart = false,
    ): array {
        $count = (int) round($this->gauss($count, $deviation));
        $result = [];

        // Shuffles and concatenates the vocabulary until we have enough words.
        while (count($result) < $count) {
            // Shuffle until the last word of the result so far and the first
            // word of the next set of words differ.
            do {
                $next = $this->shuffledVocab(!$fixedStart);
            } while ($next[0] === $this->lastWord);

            if ($fixedStart) {
                array_push($result, ...self::SENTENCE_START);
            }
            array_push($result, ...$next);

            $this->lastWord = array_pop($next) ?? '';
            $fixedStart = false;
        }

        return array_slice($result, 0, $count);
    }

    /**
     * Generates a number of sentences based on lorem ipsum.
     */
    public function sentences(
        float $sentenceCount,
        float $sentenceDeviation = 0,
        float $wordCount = self::DEFAULT_SENTENCE_WORDS_COUNT,
        float $wordDeviation = self::DEFAULT_SENTENCE_WORDS_DEVIATION,
        bool $fixedStart = false,
    ): string {
        $arr = $this->sentencesArray(...func_get_args());
        return implode(' ', $arr);
    }

    /**
     * Generates a number of sentences based on lorem ipsum.
     *
     * @return string[]
     */
    public function sentencesArray(
        float $sentenceCount,
        float $sentenceDeviation = 0,
        float $wordCount = self::DEFAULT_SENTENCE_WORDS_COUNT,
        float $wordDeviation = self::DEFAULT_SENTENCE_WORDS_DEVIATION,
        bool $fixedStart = false,
    ): array {
        $count = (int) round($this->gauss($sentenceCount, $sentenceDeviation));
        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $result[] = $this->punctuate(
                $this->wordsArray($wordCount, $wordDeviation, $fixedStart)
            );
            $fixedStart = false;
        }

        return $result;
    }

    /**
     * Generates a number of paragraphs based on lorem ipsum.
     */
    public function paragraphs(
        float $paragraphCount,
        float $paragraphDeviation = 0,
        float $sentenceCount = self::DEFAULT_PARAGRAPH_SENTENCES_COUNT,
        float $sentenceDeviation = self::DEFAULT_PARAGRAPH_SENTENCES_DEVIATION,
        float $wordCount = self::DEFAULT_SENTENCE_WORDS_COUNT,
        float $wordDeviation = self::DEFAULT_SENTENCE_WORDS_DEVIATION,
        bool $fixedStart = false,
    ): string {
        $arr = $this->paragraphsArray(...func_get_args());
        return implode("\n\n", $arr);
    }

    /**
     * Generates a number of paragraphs based on lorem ipsum.
     *
     * @return string[]
     */
    public function paragraphsArray(
        float $paragraphCount,
        float $paragraphDeviation = 0,
        float $sentenceCount = self::DEFAULT_PARAGRAPH_SENTENCES_COUNT,
        float $sentenceDeviation = self::DEFAULT_PARAGRAPH_SENTENCES_DEVIATION,
        float $wordCount = self::DEFAULT_SENTENCE_WORDS_COUNT,
        float $wordDeviation = self::DEFAULT_SENTENCE_WORDS_DEVIATION,
        bool $fixedStart = false,
    ): array {
        $count = (int) round($this->gauss($paragraphCount, $paragraphDeviation));
        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $result[] = $this->sentences(
                $sentenceCount,
                $sentenceDeviation,
                $wordCount,
                $wordDeviation,
                $fixedStart,
            );
            $fixedStart = false;
        }

        return $result;
    }

    /**
     * Returns a shuffled vocabulary.
     *
     * Optionally omits `SENTENCE_START` from the result.
     *
     * @return string[]
     */
    private function shuffledVocab(bool $withSentenceStart = true): array
    {
        $vocab = $withSentenceStart
            ? [...self::SENTENCE_START, ...self::VOCAB]
            : self::VOCAB;
        $this->shuffle($vocab);
        return $vocab;
    }

    /**
     * Applies punctuation to a sentence.
     *
     * This includes a period at the end, the injection of commas as well as
     * capitalizing the first letter of the first word of the sentence.
     *
     * @param string[] $sentence
     */
    private function punctuate(array $sentence): string
    {
        $words = count($sentence);

        // Only worry about commas on sentences longer than 4 words
        if ($words > 4) {
            $mean = log($words, 6);
            $deviation = $mean / 6;
            $commas = round($this->gauss($mean, $deviation));

            for ($i = 1; $i <= $commas; $i++) {
                $word = round($i * $words / ($commas + 1));

                if ($word < ($words - 1) && $word > 0) {
                    $sentence[$word] .= ',';
                }
            }
        }

        return ucfirst(implode(' ', $sentence) . '.');
    }

    private static function normalizeTimestamp(
        \DateTimeInterface|int|string $value,
    ): int {
        if (is_string($value)) {
            $value = new \DateTime($value);
        }
        if (!is_int($value)) {
            $value = $value->getTimestamp();
        }
        return $value;
    }
}
