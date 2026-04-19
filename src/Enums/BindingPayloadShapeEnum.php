<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\Vocabulary\Enums;

enum BindingPayloadShapeEnum: string
{
    case Shorthand = 'shorthand';
    case AttributeList = 'attribute_list';
}
