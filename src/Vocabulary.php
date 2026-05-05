<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Vocabulary;

use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Exceptions\InvalidVocabularyException;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Interfaces\BindingTypeDefinitionInterface;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Interfaces\VocabularyInterface;

readonly class Vocabulary implements VocabularyInterface
{
    private const string VALID_VOCABULARY_IDENTIFIER_PATTERN = '/^(?!-)(?!.*--)[a-z-]+(?<!-)$/';

    private const string VALID_VOCABULARY_LABEL_PATTERN = '/^[A-Za-z0-9][A-Za-z0-9 \-\'&,.:()]*[A-Za-z0-9)]$/';

    private const string VALID_SEMANTIC_VERSION_PATTERN = '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|[0-9A-Za-z-]*[A-Za-z-][0-9A-Za-z-]*)(?:\.(?:0|[1-9]\d*|[0-9A-Za-z-]*[A-Za-z-][0-9A-Za-z-]*))*))?(?:\+([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?$/';

    /**
     * @param list<BindingTypeDefinitionInterface> $bindingTypeDefinitions
     *
     * @throws InvalidVocabularyException
     */
    public function __construct(
        private string $identifier,
        private string $label,
        private string $version,
        private array $bindingTypeDefinitions,
    ) {
        $this->guard();
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return list<BindingTypeDefinitionInterface>
     */
    public function getBindingTypeDefinitions(): array
    {
        return $this->bindingTypeDefinitions;
    }

    public function hasBindingTypeDefinition(string $identifier): bool
    {
        return $this->getBindingTypeDefinition($identifier) !== null;
    }

    public function getBindingTypeDefinition(string $identifier): ?BindingTypeDefinitionInterface
    {
        return array_find($this->bindingTypeDefinitions, fn ($bindingTypeDefinition) => $bindingTypeDefinition->getIdentifier() === $identifier);
    }

    /**
     * @throws InvalidVocabularyException
     */
    private function guard(): void
    {
        $this->guardIdentifier();
        $this->guardLabel();
        $this->guardVersion();

        $seenBindingTypeIdentifiers = [];

        foreach ($this->bindingTypeDefinitions as $bindingTypeDefinition) {
            $identifier = $bindingTypeDefinition->getIdentifier();

            if (isset($seenBindingTypeIdentifiers[$identifier])) {
                throw new InvalidVocabularyException(
                    sprintf(
                        'Vocabulary contains duplicate binding type definition "%s".',
                        $identifier,
                    )
                );
            }

            $seenBindingTypeIdentifiers[$identifier] = true;
        }
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @throws InvalidVocabularyException
     */
    private function guardIdentifier(): void
    {
        if (!preg_match(self::VALID_VOCABULARY_IDENTIFIER_PATTERN, $this->identifier)) {
            throw new InvalidVocabularyException(
                sprintf("The identifier '%s' is invalid. Identifiers may only contain lowercase letters and hyphens, and may not be empty.", $this->identifier)
            );
        }

        if (strlen($this->identifier) > 64) {
            throw new InvalidVocabularyException(
                sprintf("The identifier '%s' is invalid. A maximum of 64 characters is allowed.", $this->identifier)
            );
        }

        if (strlen($this->identifier) < 3) {
            throw new InvalidVocabularyException(
                sprintf("The identifier '%s' is invalid. A minimum of 3 characters is allowed.", $this->identifier)
            );
        }
    }

    /**
     * @throws InvalidVocabularyException
     */
    private function guardLabel(): void
    {
        $labelLength = strlen(trim($this->label));
        if ($labelLength > 64) {
            throw new InvalidVocabularyException(
                sprintf("The label '%s' is invalid. Labels may be a maximum of 64 characters long.", $this->label)
            );
        }
        if ($labelLength < 3) {
            throw new InvalidVocabularyException(
                sprintf("The label '%s' is invalid. Labels must be a minimum of 3 characters long.", $this->label)
            );
        }
        if (!preg_match(self::VALID_VOCABULARY_LABEL_PATTERN, $this->label)) {
            throw new InvalidVocabularyException(
                sprintf("The label '%s' is invalid. Labels may only contain numbers, letters, spaces, hyphens, apostrophes, ampersands, commas, parentheses, colons and full-stops (periods).", $this->label)
            );
        }
    }

    /**
     * @throws InvalidVocabularyException
     */
    private function guardVersion(): void
    {
        if (!preg_match(self::VALID_SEMANTIC_VERSION_PATTERN, $this->version)) {
            throw new InvalidVocabularyException(
                sprintf("The version '%s' is invalid. Versions must conform to semantic versioning 2.0 standards.", $this->version)
            );
        }
    }
}
