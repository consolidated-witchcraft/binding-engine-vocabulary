<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Vocabulary;

use ConsolidatedWitchcraft\BindingEngine\Parser\Diagnostics\Enums\DiagnosticSeverityEnum;
use ConsolidatedWitchcraft\BindingEngine\Parser\Diagnostics\Interfaces\DiagnosticInterface;

readonly class ValidationResult
{
    /**
     * @param list<DiagnosticInterface> $diagnostics
     */
    public function __construct(
        private array $diagnostics = [],
    ) {
    }

    /**
     * @return list<DiagnosticInterface>
     */
    public function getDiagnostics(): array
    {
        return $this->diagnostics;
    }

    public function hasDiagnostics(): bool
    {
        return $this->diagnostics !== [];
    }

    public function hasErrors(): bool
    {
        return array_any($this->diagnostics, fn ($diagnostic) => $diagnostic->getSeverity() === DiagnosticSeverityEnum::Error);
    }
}
