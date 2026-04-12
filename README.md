# Conundrum Codex — Binding Engine Vocabulary

This library provides **semantic validation and vocabulary definitions** for the Conundrum Codex binding language.
This library is intentionally agnostic of any specific domain or world model.
It answers the question:

> “Given a syntactically valid binding, is it *meaningful* and *valid* in this world?”

---

## Overview

This library operates **after parsing**, and before later inference and projection layers.

It consumes the AST produced by the parser and validates it against a defined vocabulary.

---

## Design Goals

- Separation of concerns — parsing and meaning are independent
- Extensibility — users define their own vocabularies
- Deterministic validation — no hidden inference
- Composable diagnostics — integrates with parser diagnostics

---

## What This Library Validates

- Binding types are known
- Attributes are allowed for a given binding type
- Required attributes are present
- Non-repeatable attributes are not duplicated
- Attribute values conform to expected types
- Payload shape matches binding expectations (e.g. shorthand vs attribute list)

---

## What This Library Does NOT Do

- Parsing
- Inference
- Projection
- Link resolution
- Rendering
- Entity lookup

---

## Architecture

The Conundrum Codex binding system is designed as a pipeline of separate libraries and layers.

### 1. Parser

The parser is responsible for:

- turning source text into an AST
- identifying bindings and payloads
- producing syntax diagnostics

It does **not** know what bindings mean.

---

### 2. Vocabulary / Validation

This library is responsible for:

- defining allowed binding types
- defining allowed attributes
- validating payload shape and attribute usage
- producing semantic diagnostics

It operates on AST nodes produced by the parser.

---

### 3. Inference (future)

A later layer may:

- derive additional facts from validated bindings
- enforce logical consequences
- enrich the semantic graph

---

### 4. Projection (future)

A later layer may:

- transform validated and inferred data into application-facing models
- prepare data for rendering, querying, or persistence

---

## Core Concepts

### AttributeDefinition

Defines a single attribute:

- identifier
- value type
- required / optional
- repeatable / non-repeatable

---

### BindingTypeDefinition

Defines a binding type:

- name (e.g. `event`, `person`)
- allowed attributes
- required attributes
- expected payload shape

---

### Vocabulary

A collection of binding type definitions used for validation.

---

### Validator

The primary entry point for this library.

Consumes:

- a parsed `DocumentNode`
- a `Vocabulary`

Produces:

- a list of diagnostics describing semantic issues

The validator does _not_ modify the AST.

---

## Example

```php
$parser = new Parser();

$parseResult = $parser->parse('@event[type:birth, subject:jane-austen]');

$vocabulary = new Vocabulary([
    // definitions here
]);

$validator = new Validator($vocabulary);

$validationResult = $validator->validate($parseResult->getDocument());

$diagnostics = [
    ...$parseResult->getDiagnostics(),
    ...$validationResult->getDiagnostics(),
];
```
## Installation
```bash
  composer require conundrum-codex/binding-vocabulary
```