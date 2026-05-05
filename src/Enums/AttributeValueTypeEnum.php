<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums;

enum AttributeValueTypeEnum: string
{
    case String = 'string';
    case Identifier = 'identifier';
    case Enum = 'enum';
}
