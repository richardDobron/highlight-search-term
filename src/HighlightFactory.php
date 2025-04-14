<?php

declare(strict_types=1);

namespace dobron\Highlight;

use dobron\Highlight\Exception\InvalidConfigurationException;
use dobron\Highlight\Internal\Engine;

final class HighlightFactory implements HighlightFactoryInterface
{
    public function create(string $dataDir, Configuration $configuration): Highlight
    {
        if (!is_dir($dataDir) && !mkdir($dataDir, 0777, true)) {
            throw InvalidConfigurationException::becauseCouldNotCreateDataDir($dataDir);
        }

        return new Highlight(
            new Engine(
                $configuration,
                $dataDir
            )
        );
    }
}
