<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\Vocabulary\Enums;

enum AttributeValueTypeEnum: string
{
    case String = 'string';
    case Identifier = 'identifier';
    case Enum = 'enum';
}