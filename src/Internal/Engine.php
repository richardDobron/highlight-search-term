<?php

declare(strict_types=1);

namespace dobron\Highlight\Internal;

use dobron\Highlight\Configuration;
use dobron\Highlight\Internal\Search\Highlighter\Highlighter;
use dobron\Highlight\Internal\Tokenizer\Tokenizer;
use Nitotm\Eld\LanguageDetector;

class Engine
{
    private Highlighter $highlighter;

    private ?Tokenizer $tokenizer = null;

    public function __construct(
        private Configuration $configuration,
        private ?string $dataDir = null
    ) {
        $this->highlighter = new Highlighter($this);
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getDataDir(): ?string
    {
        return $this->dataDir;
    }

    public function getHighlighter(): Highlighter
    {
        return $this->highlighter;
    }

    public function getTokenizer(): Tokenizer
    {
        if ($this->tokenizer instanceof Tokenizer) {
            return $this->tokenizer;
        }

        $languageDetector = null;

        if ($this->getConfiguration()->getLanguageDetection()) {
            $languages = $this->getConfiguration()->getLanguages();
            $ngramsFile = null;

            if ($languages !== []) {
                // Load from data dir - this seems unnecessarily complicated, but we want to avoid calling new LanguageDetector()
                // without arguments at all costs as this library currently loads the default ngram file even if only a subset
                // is used later on. So we want to optimize this for memory if we can.
                if ($this->getDataDir() !== null) {
                    $ngramsFile = $this->loadNGramsFile($languages);
                }
            }

            $languageDetector = new LanguageDetector($ngramsFile);
            $languageDetector->enableTextCleanup(true); // Clean stuff like URLs, domains etc. to improve language detection
        }

        return $this->tokenizer = new Tokenizer($languageDetector, $this->getConfiguration()->getSynonyms());
    }

    /**
     * @param array<string> $languages
     */
    private function loadNGramsFile(array $languages): ?string
    {
        $generateNgramsRefFile = function (string $ngramsRefFile, array $languages): bool {
            // Prepare for the next time
            $languageDetector = new LanguageDetector();
            $file = $languageDetector->langSubset($languages)->file;

            if ($file !== null) {
                file_put_contents($ngramsRefFile, '<?php return ' . var_export($file, true) . ';');

                return true;
            }

            return false;
        };

        sort($languages);
        $ngramsRefFile = $this->getDataDir() . '/ngrams_' . hash('sha256', implode(' ', $languages)) . '.php';
        if (! file_exists($ngramsRefFile)) {
            if (! $generateNgramsRefFile($ngramsRefFile, $languages)) {
                return null;
            }
        }

        $ngramsFile = require $ngramsRefFile;

        if (! \is_string($ngramsFile) || ! file_exists($ngramsFile)) {
            if (! $generateNgramsRefFile($ngramsRefFile, $languages)) {
                return null;
            }
        }

        return $ngramsFile;
    }
}
