<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Vocabulary\Interfaces;

use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;

interface BindingTypeDefinitionInterface
{
    public function getIdentifier(): string;

    public function getLabel(): string;

    public function getDescription(): string;

    /**
     * @return list<BindingPayloadShapeEnum>
     */
    public function getAllowedPayloadShapes(): array;

    public function allowsPayloadShape(BindingPayloadShapeEnum $payloadShape): bool;

    /**
     * @return list<AttributeDefinitionInterface>
     */
    public function getAttributeDefinitions(): array;

    public function hasAttributeDefinition(string $identifier): bool;

    public function getAttributeDefinition(string $identifier): ?AttributeDefinitionInterface;

    /**
     * @return list<AttributeDefinitionInterface>
     */
    public function getRequiredAttributeDefinitions(): array;
}
