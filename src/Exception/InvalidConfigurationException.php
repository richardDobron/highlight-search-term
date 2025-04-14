<?php

declare(strict_types=1);

namespace dobron\Highlight\Exception;

class InvalidConfigurationException extends \InvalidArgumentException implements HighlightExceptionInterface
{
    public static function becauseCouldNotCreateDataDir(string $folder): self
    {
        return new self(
            sprintf(
                'Could not create data directory at "%s".',
                $folder
            )
        );
    }
}
