<?php

declare(strict_types=1);

namespace dobron\Highlight\Tests\Functional;

use dobron\Highlight\Config\TypoTolerance;
use dobron\Highlight\Configuration;
use dobron\Highlight\Highlight;
use dobron\Highlight\HighlightFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HighlightTest extends TestCase
{
    protected function createHighlight(Configuration $configuration): Highlight
    {
        $factory = new HighlightFactory();

        return $factory->create(sys_get_temp_dir(), $configuration);
    }

    public static function highlightingProvider(): \Generator
    {
        yield 'Highlight with matches position only' => [
            'assassin',
            'An assassin is shot by her ruthless employer, Bill, and other members of their assassination circle – but she lives to plot her vengeance.',
            'An <em>assassin</em> is shot by her ruthless employer, Bill, and other members of their <em>assassination</em> circle – but she lives to plot her vengeance.',
        ];

        yield 'Highlight with matches position only (Deutsch)' => [
            'fünft abend',
            'Findet Nemo (Originaltitel Finding Nemo) ist ein US-amerikanischer Animationsfilm der Pixar Animation Studios aus dem Jahr 2003, der durch Walt Disney und Buena Vista vertrieben wurde. Es ist der fünfte abendfüllende Pixar-Spielfilm. Bei der Oscarverleihung 2004 wurde Findet Nemo als Bester animierter Spielfilm ausgezeichnet.',
            "Findet Nemo (Originaltitel Finding Nemo) ist ein US-amerikanischer Animationsfilm der Pixar Animation Studios aus dem Jahr 2003, der durch Walt Disney und Buena Vista vertrieben wurde. Es ist der <em>fünfte abendfüllende</em> Pixar-Spielfilm. Bei der Oscarverleihung 2004 wurde Findet Nemo als Bester animierter Spielfilm ausgezeichnet.",
        ];

        yield 'Highlight with matches position of stopwords' => [
            'her assassin',
            'An assassin is shot by her ruthless employer, Bill, and other members of their assassination circle – but she lives to plot her vengeance.',
            'An <em>assassin</em> is shot by her ruthless employer, Bill, and other members of their <em>assassination</em> circle – but she lives to plot her vengeance.',
            ['her'],
        ];

        yield 'Highlight with typo' => [
            'assasin',
            'An assassin is shot by her ruthless employer, Bill, and other members of their assassination circle – but she lives to plot her vengeance.',
            'An <em>assassin</em> is shot by her ruthless employer, Bill, and other members of their <em>assassination</em> circle – but she lives to plot her vengeance.',
        ];

        yield 'Highlight with custom start and end tag' => [
            'assasin',
            'An assassin is shot by her ruthless employer, Bill, and other members of their assassination circle – but she lives to plot her vengeance.',
            'An <mark>assassin</mark> is shot by her ruthless employer, Bill, and other members of their <mark>assassination</mark> circle – but she lives to plot her vengeance.',
            [],
            '<mark>',
            '</mark>',
        ];

        yield 'Highlight without typo' => [
            'assassin',
            'An assassin is shot by her ruthless employer, Bill, and other members of their assassination circle – but she lives to plot her vengeance.',
            'An <em>assassin</em> is shot by her ruthless employer, Bill, and other members of their <em>assassination</em> circle – but she lives to plot her vengeance.',
        ];

        yield 'Highlight multiple matches with and without typos' => [
            'Barier Reef',
            'Nemo, an adventurous young clownfish, is unexpectedly taken from his Great Barrier Reef home to a dentist\'s office aquarium. It\'s up to his worrisome father Marlin and a friendly but forgetful fish Dory to bring Nemo home -- meeting vegetarian sharks, surfer dude turtles, hypnotic jellyfish, hungry seagulls, and more along the way.',
            "Nemo, an adventurous young clownfish, is unexpectedly taken from his Great <em>Barrier Reef</em> home to a dentist's office aquarium. It's up to his worrisome father Marlin and a friendly but forgetful fish Dory to bring Nemo home -- meeting vegetarian sharks, surfer dude turtles, hypnotic jellyfish, hungry seagulls, and more along the way.",
        ];

        yield 'Highlight multiple matches across stop words' => [
            'racing to a boxing match',
            'While racing to a boxing match, Frank, Mike, John and Rey get more than they bargained for. A wrong turn lands them directly in the path of Fallon, a vicious, wise-cracking drug lord. After accidentally witnessing Fallon murder a disloyal henchman, the four become his unwilling prey in a savage game of cat & mouse as they are mercilessly stalked through the urban jungle in this taut suspense drama',
            'While <em>racing to a boxing match</em>, Frank, Mike, John and Rey get more than they bargained for. A wrong turn lands them directly in the path of Fallon, a vicious, wise-cracking drug lord. After accidentally witnessing Fallon murder a disloyal henchman, the four become his unwilling prey in a savage game of cat & mouse as they are mercilessly stalked through the urban jungle in this taut suspense drama',
            ['of', 'the', 'an', 'but', 'to', 'a'],
        ];

        yield 'Highlight literal match including stopwords' => [
            'Pirates of the Caribbean: The Curse of the Black Pearl',
            'Pirates of the Caribbean: The Curse of the Black Pearl',
            '<em>Pirates of the Caribbean</em>: <em>The Curse of the Black Pearl</em>',
            ['of', 'the', 'an', 'but', 'to', 'a', 'back'],
        ];

        yield 'Highlight partial in Deutsch' => [
            'Nemo',
            'Nemo, an adventurous young clownfish, is unexpectedly taken from his Great Barrier Reef home to a dentist\'s office aquarium. It\'s up to his worrisome father Marlin and a friendly but forgetful fish Dory to bring Nemo home -- meeting vegetarian sharks, surfer dude turtles, hypnotic jellyfish, hungry seagulls, and more along the way.',
            "<em>Nemo</em>, an adventurous young clownfish, is unexpectedly taken from his Great Barrier Reef home to a dentist's office aquarium. It's up to his worrisome father Marlin and a friendly but forgetful fish Dory to bring <em>Nemo</em> home -- meeting vegetarian sharks, surfer dude turtles, hypnotic jellyfish, hungry seagulls, and more along the way.",
        ];
    }

    /**
     * @param array<string> $stopWords
     */
    #[DataProvider('highlightingProvider')]
    public function testHighlighting(
        string $query,
        string $text,
        string $expectedResults,
        array $stopWords = [],
        string $highlightStartTag = '<em>',
        string $highlightEndTag = '</em>',
    ): void {
        $configuration = Configuration::create()
            ->withStopWords($stopWords);

        $highlight = $this->createHighlight($configuration);

        $highlightResult = $highlight->highlight(
            $query,
            $text,
            null,
            [],
            $highlightStartTag,
            $highlightEndTag,
        )->getHighlightedText();

        $this->assertSame(
            $expectedResults,
            $highlightResult,
        );
    }

    public function testPrefixSearchAndHighlightingWithTypoSearchEnabled(): void
    {
        $typoTolerance = TypoTolerance::create()->withEnabledForPrefixSearch(true);
        $configuration = Configuration::create()->withTypoTolerance($typoTolerance);

        $highlight = $this->createHighlight($configuration);


        $highlightResult = $highlight->highlight(
            'assat',
            'An assassin is shot by her ruthless employer, Bill, and other members of their assassination circle – but she lives to plot her vengeance.',
        )->getHighlightedText();

        $this->assertSame(
            'An <em>assassin</em> is shot by her ruthless employer, Bill, and other members of their <em>assassination</em> circle – but she lives to plot her vengeance.',
            $highlightResult,
        );
    }
}
