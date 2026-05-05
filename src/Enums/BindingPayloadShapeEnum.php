<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums;

enum BindingPayloadShapeEnum: string
{
    case Shorthand = 'shorthand';
    case AttributeList = 'attribute_list';
}
