<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\Vocabulary\Interfaces;

use ConundrumCodex\BindingEngine\Vocabulary\Enums\AttributeValueTypeEnum;

interface AttributeDefinitionInterface
{
    public function getIdentifier(): string;

    public function getLabel(): string;

    public function getDescription(): string;

    public function getValueType(): AttributeValueTypeEnum;

    public function isRequired(): bool;

    public function isRepeatable(): bool;

    /**
     * @return list<string>|null
     */
    public function getAllowedValues(): ?array;

    public function hasAllowedValues(): bool;
}
