<?php

declare(strict_types=1);

namespace dobron\Highlight;

use dobron\Highlight\Internal\Engine;
use dobron\Highlight\Internal\Search\Highlighter\HighlightResult;

final class Highlight
{
    public function __construct(
        public readonly Engine $engine
    ) {
    }

    /**
     * @param array<string> $stopWords
     */
    public function highlight(
        string $query,
        string $text,
        ?string $language = null,
        array $stopWords = [],
        string $highlightStartTag = '<em>',
        string $highlightEndTag = '</em>',
    ): HighlightResult {
        $tokenCollection = $this->engine->getTokenizer()
            ->tokenize(
                $query,
                $this->engine->getConfiguration()->getMaxQueryTokens(),
                $language,
                $stopWords,
            );

        return $this->engine->getHighlighter()->highlight(
            $text,
            $tokenCollection,
            $highlightStartTag,
            $highlightEndTag,
        );
    }

    public function getConfiguration(): Configuration
    {
        return $this->engine->getConfiguration();
    }
}
