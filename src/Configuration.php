<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity;

final class Configuration
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        private readonly array $parameters
    ) {
    }

    public function getMaxClassCognitiveComplexity(): int
    {
        return $this->parameters['class'];
    }

    public function getMaxFunctionCognitiveComplexity(): int
    {
        return $this->parameters['function'];
    }
}
