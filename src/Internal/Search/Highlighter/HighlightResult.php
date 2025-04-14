<?php

declare(strict_types=1);

namespace dobron\Highlight\Internal\Search\Highlighter;

class HighlightResult
{
    /**
     * @param array<int, array{start: int, length: int, stopword: bool}> $matches
     */
    public function __construct(
        private readonly string $highlightedText,
        private readonly array $matches
    ) {
    }

    public function getHighlightedText(): string
    {
        return $this->highlightedText;
    }

    /**
     * @return array<int, array{start: int, length: int, stopword: bool}>
     */
    public function getMatches(): array
    {
        return $this->matches;
    }
}
