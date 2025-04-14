<?php

declare(strict_types=1);

namespace dobron\Highlight;

interface HighlightFactoryInterface
{
    public function create(string $dataDir, Configuration $configuration): Highlight;
}
