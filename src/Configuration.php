<?php

declare(strict_types=1);

namespace dobron\Highlight;

use dobron\Highlight\Config\TypoTolerance;

final class Configuration
{
    /**
     * @var array<string>
     */
    private array $languages = [];
    /**
     * @var array<string,array<string, string[]>>
     */
    private array $synonyms = [];

    private int $maxQueryTokens = 10;

    private int $minTokenLengthForPrefixSearch = 3;

    /**
     * @var array<string>
     */
    private array $stopWords = [];

    private TypoTolerance $typoTolerance;

    private bool $languageDetection = false;

    public function __construct()
    {
        $this->typoTolerance = new TypoTolerance();
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * @return array<string>
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * @return array<string,array<string, string[]>>
     */
    public function getSynonyms(): array
    {
        return $this->synonyms;
    }

    public function getMaxQueryTokens(): int
    {
        return $this->maxQueryTokens;
    }

    public function getMinTokenLengthForPrefixSearch(): int
    {
        return $this->minTokenLengthForPrefixSearch;
    }

    /**
     * @return array<string>
     */
    public function getStopWords(): array
    {
        return $this->stopWords;
    }

    public function getTypoTolerance(): TypoTolerance
    {
        return $this->typoTolerance;
    }

    public function getLanguageDetection(): bool
    {
        return $this->languageDetection;
    }

    /**
     * @param array<string> $languages
     */
    public function withLanguages(array $languages): self
    {
        sort($languages);

        $clone = clone $this;
        $clone->languages = $languages;

        return $clone;
    }

    /**
     * @param array<string, array<string, string[]>> $synonyms
     */
    public function withSynonyms(array $synonyms): self
    {
        $clone = clone $this;
        $clone->synonyms = $synonyms;

        return $clone;
    }

    public function withMaxQueryTokens(int $maxQueryTokens): self
    {
        $clone = clone $this;
        $clone->maxQueryTokens = $maxQueryTokens;

        return $clone;
    }

    public function withMinTokenLengthForPrefixSearch(int $minTokenLengthForPrefixSearch): self
    {
        $clone = clone $this;
        $clone->minTokenLengthForPrefixSearch = $minTokenLengthForPrefixSearch;

        return $clone;
    }

    /**
     * @param array<string> $stopWords
     */
    public function withStopWords(array $stopWords): self
    {
        sort($stopWords);

        $clone = clone $this;
        $clone->stopWords = $stopWords;

        return $clone;
    }

    public function withTypoTolerance(TypoTolerance $tolerance): self
    {
        $clone = clone $this;
        $clone->typoTolerance = $tolerance;

        return $clone;
    }

    public function withLanguageDetection(bool $languageDetection): self
    {
        $clone = clone $this;
        $clone->languageDetection = $languageDetection;

        return $clone;
    }
}
