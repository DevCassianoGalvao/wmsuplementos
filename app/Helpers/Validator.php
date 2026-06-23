<?php

declare(strict_types=1);

namespace Maia\Helpers;

/**
 * Validação de dados de entrada.
 *
 * Uso:
 *   $v = new Validator($_POST);
 *   $v->required('name')->maxLen('name', 150)
 *     ->required('email')->email('email')
 *     ->required('password')->minLen('password', 8);
 *
 *   if ($v->fails()) {
 *       $errors = $v->errors();
 *   }
 */
class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // ─── Regras ──────────────────────────────────────────────────────────────

    public function required(string $field, string $label = ''): static
    {
        $value = $this->value($field);
        if ($value === null || trim((string)$value) === '') {
            $this->addError($field, $label ?: $field, 'é obrigatório');
        }
        return $this;
    }

    public function email(string $field, string $label = ''): static
    {
        $value = $this->value($field);
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $label ?: $field, 'deve ser um e-mail válido');
        }
        return $this;
    }

    public function minLen(string $field, int $min, string $label = ''): static
    {
        $value = $this->value($field);
        if ($value !== null && mb_strlen((string)$value, 'UTF-8') < $min) {
            $this->addError($field, $label ?: $field, "deve ter ao menos {$min} caracteres");
        }
        return $this;
    }

    public function maxLen(string $field, int $max, string $label = ''): static
    {
        $value = $this->value($field);
        if ($value !== null && mb_strlen((string)$value, 'UTF-8') > $max) {
            $this->addError($field, $label ?: $field, "deve ter no máximo {$max} caracteres");
        }
        return $this;
    }

    public function numeric(string $field, string $label = ''): static
    {
        $value = $this->value($field);
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, $label ?: $field, 'deve ser numérico');
        }
        return $this;
    }

    public function positiveNumber(string $field, string $label = ''): static
    {
        $value = $this->value($field);
        if ($value !== null && $value !== '' && (float)$value <= 0) {
            $this->addError($field, $label ?: $field, 'deve ser maior que zero');
        }
        return $this;
    }

    public function minValue(string $field, float $min, string $label = ''): static
    {
        $value = $this->value($field);
        if ($value !== null && $value !== '' && (float)$value < $min) {
            $this->addError($field, $label ?: $field, "deve ser no mínimo {$min}");
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $label = ''): static
    {
        $value = $this->value($field);
        if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
            $list = implode(', ', $allowed);
            $this->addError($field, $label ?: $field, "deve ser um dos valores: {$list}");
        }
        return $this;
    }

    public function phone(string $field, string $label = ''): static
    {
        $value = $this->value($field);
        if ($value !== null && $value !== '') {
            $digits = preg_replace('/\D/', '', (string)$value);
            if (!preg_match('/^\d{10,11}$/', (string)$digits)) {
                $this->addError($field, $label ?: $field, 'deve ser um telefone válido (10 ou 11 dígitos)');
            }
        }
        return $this;
    }

    public function url(string $field, string $label = ''): static
    {
        $value = $this->value($field);
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, $label ?: $field, 'deve ser uma URL válida');
        }
        return $this;
    }

    public function confirmed(string $field, string $confirmField, string $label = ''): static
    {
        $value   = $this->value($field);
        $confirm = $this->value($confirmField);
        if ($value !== $confirm) {
            $this->addError($field, $label ?: $field, 'e confirmação não conferem');
        }
        return $this;
    }

    public function uniqueInDb(string $field, string $table, string $column, ?int $ignoreId = null, string $label = ''): static
    {
        $value = $this->value($field);
        if ($value === null || $value === '') {
            return $this;
        }

        $sql    = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?";
        $params = [$value];

        if ($ignoreId !== null) {
            $sql    .= ' AND id != ?';
            $params[] = $ignoreId;
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        if ((int)$stmt->fetchColumn() > 0) {
            $this->addError($field, $label ?: $field, 'já está em uso');
        }

        return $this;
    }

    // ─── Resultado ───────────────────────────────────────────────────────────

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    /** Retorna array ['field' => 'mensagem de erro']. */
    public function errors(): array
    {
        return $this->errors;
    }

    /** Primeiro erro de um campo específico, ou null. */
    public function error(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    // ─── Interno ─────────────────────────────────────────────────────────────

    private function value(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    private function addError(string $field, string $label, string $message): void
    {
        // Só armazena o primeiro erro por campo
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = ucfirst($label) . ' ' . $message . '.';
        }
    }
}
