<div align="center">
  <img src="./logo/logo.svg" width="355px" alt="HIGHLIGHT your search">
  <p>Lightweight PHP library designed to <u>highlight</u> search terms in text.</p>
</div>

## 📖 Requirements
* PHP 8.1 or higher
* [Composer](https://getcomposer.org) is required for installation
* PHP Extensions: `ext-intl`, `ext-mbstring`

## 📦 Installation

Install the library using Composer:

```shell
$ composer require richarddobron/highlight-search-term
```

## ⚡️ Quick Start

Here’s how to use the library to highlight search terms:

```php
use dobron\Highlight\Config\TypoTolerance;
use dobron\Highlight\Configuration;
use dobron\Highlight\HighlightFactory;

$configuration = Configuration::create()
    ->withLanguages(['en'])
    ->withMaxQueryTokens(10)
    ->withTypoTolerance(TypoTolerance::create()->withFirstCharTypoCountsDouble(false)); // can be further fine-tuned but is enabled by default

$highlightResult = (new HighlightFactory())
    ->create(__DIR__ . '/cache', $configuration)
    ->highlight('world', 'Hello world!');

echo $highlightResult->getHighlightedText(); // Hello <em>world</em>!
```

## ⚙️ Configuration Options

You can customize the library with the following methods:

| Method                                                   | Description                                                    | Default                   |
|----------------------------------------------------------|----------------------------------------------------------------|---------------------------|
| `withLanguages(array $languages)`                        | Sets the languages to be used for tokenization.                | `[]`                      |
| `withMaxQueryTokens(int $maxQueryTokens)`                | Sets the maximum number of tokens to be used for tokenization. | `10`                      |
| `withStopWords(array $stopWords)`                        | Sets the stop words to be used for tokenization.               | `[]`                      |
| `withSynonyms(array $synonyms)`                          | Sets the synonyms to be used for tokenization.                 | `[]`                      |
| `withMinTokenLengthForPrefixSearch(int $minTokenLength)` | Sets the minimum token length for prefix search.               | `3`                       |
| `withTypoTolerance(TypoTolerance $typoTolerance)`        | Sets the typo tolerance configuration.                         | `TypoTolerance::create()` |
| `withLanguageDetection(bool $languageDetection)`         | Enables or disables language detection.                        | `false`                   |

## 📅 Change Log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 🧪 Testing

```shell
$ composer tests
```

## 🤝 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.


## 🙋 Credits

- [Richard Dobroň][link-author]
- [Yanick Witschi][link-loupe]

## ⚖️ License

This repository is MIT licensed, as found in the [LICENSE](LICENSE) file.

[link-author]: https://github.com/richardDobron
[link-loupe]: https://github.com/loupe-php/loupe