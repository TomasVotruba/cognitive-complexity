<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity;

final readonly class Configuration
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        private array $parameters
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

    /**
     * @return string[]
     */
    public function getDependencyTreeTypes(): array
    {
        return $this->parameters['dependency_tree_types'] ?? [];
    }

    public function getMaxDependencyTreeComplexity(): int
    {
        return $this->parameters['dependency_tree'];
    }

    public function isDependencyTreeEnabled(): bool
    {
        return $this->getDependencyTreeTypes() !== [];
    }
}
