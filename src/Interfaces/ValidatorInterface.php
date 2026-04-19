<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\Vocabulary\Interfaces;

use ConundrumCodex\BindingEngine\Parser\Ast\Nodes\DocumentNode;
use ConundrumCodex\BindingEngine\Vocabulary\ValidationResult;

interface ValidatorInterface
{
    public function validate(DocumentNode $document): ValidationResult;
}
