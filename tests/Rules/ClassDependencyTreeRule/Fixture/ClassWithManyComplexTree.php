<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity\Tests\Rules\ClassDependencyTreeRule\Fixture;

use TomasVotruba\CognitiveComplexity\Tests\Rules\ClassDependencyTreeRule\Source\AnotherComplexService;
use TomasVotruba\CognitiveComplexity\Tests\Rules\ClassDependencyTreeRule\Source\CheckComplexity;
use TomasVotruba\CognitiveComplexity\Tests\Rules\ClassDependencyTreeRule\Source\ComplexService;

final class ClassWithManyComplexTree extends CheckComplexity
{
    public function __construct(
        private ComplexService $complexService,
        private AnotherComplexService $anotherComplexService,
    ) {
    }

    public function run(array $items): int
    {
        foreach ($items as $item) {
            if (mt_rand(0, 1) + 100) {
                $this->complexService->someFunction(100);
            }

            if (is_int($item)) {
                $this->anotherComplexService->someFunction(100);
            }
        }
    }
}
