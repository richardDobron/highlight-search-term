<?php

declare(strict_types=1);

namespace dobron\Highlight\Internal\Tokenizer;

use Nitotm\Eld\LanguageDetector;
use Wamania\Snowball\NotFoundException;
use Wamania\Snowball\Stemmer\Stemmer;
use Wamania\Snowball\StemmerFactory;

class Tokenizer
{
    /**
     * @var array<string,array<string,string>>
     */
    private array $stemmerCache = [];

    /**
     * @var array<string,?Stemmer>
     */
    private array $stemmers = [];

    /**
     * @param array<string,array<string, string[]>> $synonyms
     */
    public function __construct(
        private ?LanguageDetector $languageDetector,
        private array $synonyms = [],
    ) {
    }

    /**
     * @param array<string> $stopWords
     */
    public function tokenize(string $string, ?int $maxTokens = null, ?string $language = null, array $stopWords = []): TokenCollection
    {
        if (! $language && $this->languageDetector) {
            $languageResult = $this->languageDetector->detect($string);

            // For one simple string we have to check if the language result is reliable. There's not enough data for
            // something like "Star Wars". It might be detected as nonsense, and we get weird stemming results.
            if ($languageResult->isReliable()) {
                $language = $languageResult->language;
            }
        }

        return $this->doTokenize($string, $language, $maxTokens, $stopWords);
    }

    /**
     * @param array<string> $stopWords
     */
    private function doTokenize(string $string, ?string $language, ?int $maxTokens = null, array $stopWords = []): TokenCollection
    {
        $iterator = \IntlRuleBasedBreakIterator::createWordInstance($language); // @phpstan-ignore-line - null is allowed
        $iterator->setText($string);

        $all = new TokenCollection();
        $tokens = new TokenCollection();
        $id = 0;
        $position = 0;
        $phrase = false;
        $negated = false;
        $whitespace = true;

        foreach ($iterator->getPartsIterator() as $term) {
            // Set negation if the previous token was not a word and we're not in a phrase
            if (! $phrase && $whitespace) {
                $negated = false;
                if ($term === '-') {
                    $negated = true;
                }
            }

            // Toggle phrases between quotes
            if ($term === '"') {
                $phrase = ! $phrase;
                if (! $phrase) {
                    $negated = false;
                }
            }

            $status = $iterator->getRuleStatus();
            $word = $this->isWord($status);
            $whitespace = $this->isWhitespace($status, $term);

            if (! $word) {
                $position += mb_strlen($term, 'UTF-8');

                continue;
            }

            if ($maxTokens !== null && $tokens->count() >= $maxTokens) {
                break;
            }

            $term = mb_strtolower($term, 'UTF-8');
            $variants = [];

            // Stem and fetch synonyms if we detected a language - but only if not part of a phrase
            if ($language !== null && ! $phrase) {
                $synonyms = $this->synonyms($term, $language);
                $variants = array_merge($variants, $synonyms);
                $stem = $this->stem($term, $language);
                if ($stem !== null && $term !== $stem) {
                    $variants[] = $stem;
                }
            }

            $token = new Token(
                $id++,
                $term,
                $position,
                $variants,
                $phrase,
                $negated
            );

            $position += $token->getLength();

            // Collect all tokens regardless of stop word status
            $all->add($token);

            // Skip stop words
            if ($token->isOneOf($stopWords)) {
                continue;
            }

            // Only add non-stop words to the result
            $tokens->add($token);
        }

        // If removing stop words resulted in an empty collection, return all tokens
        return $tokens->empty() ? $all : $tokens;
    }

    private function getStemmerForLanguage(string $language): ?Stemmer
    {
        if (isset($this->stemmers[$language])) {
            return $this->stemmers[$language];
        }

        try {
            $stemmer = StemmerFactory::create($language);
        } catch (NotFoundException) {
            $stemmer = null;
        }

        return $this->stemmers[$language] = $stemmer;
    }

    private function isWhitespace(?int $status, string $token): bool
    {
        return ($status === null || ($status >= \IntlBreakIterator::WORD_NONE && $status < \IntlBreakIterator::WORD_NONE_LIMIT)) && trim($token) === '';
    }

    private function isWord(?int $status): bool
    {
        return $status >= \IntlBreakIterator::WORD_NONE_LIMIT;
    }

    /**
     * @return array<string>
     */
    private function synonyms(string $term, string $language): array
    {
        return $this->synonyms[$language][$term] ?? [];
    }

    private function stem(string $term, string $language): ?string
    {
        if (isset($this->stemmerCache[$language][$term])) {
            return $this->stemmerCache[$language][$term];
        }

        $stemmer = $this->getStemmerForLanguage($language);

        if ($stemmer === null) {
            return null;
        }

        return $this->stemmerCache[$language][$term] = mb_strtolower($stemmer->stem($term), 'UTF-8');
    }
}
