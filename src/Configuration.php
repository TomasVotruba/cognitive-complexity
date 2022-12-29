<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity;

final class Configuration
{
    /**
     * @var array<string, mixed>
     * @readonly
     */
    private $parameters;
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
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
