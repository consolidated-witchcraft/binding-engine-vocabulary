<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Vocabulary\Interfaces;

use ConsolidatedWitchcraft\BindingEngine\Parser\Ast\Nodes\DocumentNode;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\ValidationResult;

interface ValidatorInterface
{
    public function validate(DocumentNode $document): ValidationResult;
}
