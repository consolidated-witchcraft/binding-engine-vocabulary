<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\Vocabulary\Interfaces;

interface VocabularyInterface
{
    /**
     * @return list<BindingTypeDefinitionInterface>
     */
    public function getBindingTypeDefinitions(): array;

    public function hasBindingTypeDefinition(string $identifier): bool;

    public function getBindingTypeDefinition(string $identifier): ?BindingTypeDefinitionInterface;

    public function getVersion(): string;
}
