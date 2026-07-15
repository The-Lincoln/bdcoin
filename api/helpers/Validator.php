<?php

namespace BDPay\API\Helpers;

class Validator {
    private array $data;
    private array $errors = [];
    private array $rules;

    public function __construct(array $data, array $rules) {
        $this->data = $data;
        $this->rules = $rules;
        $this->validate();
    }

    public static function make(array $data, array $rules): self {
        return new self($data, $rules);
    }

    private function validate(): void {
        foreach ($this->rules as $field => $fieldRules) {
            $ruleList = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);
            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }
                $methodName = 'rule' . ucfirst($rule);
                if (method_exists($this, $methodName)) {
                    $this->$methodName($field, $params);
                }
            }
        }
    }

    public function passes(): bool {
        return empty($this->errors);
    }

    public function errors(): array {
        return $this->errors;
    }

    public function firstError(): ?string {
        $all = $this->errors();
        return !empty($all) ? $all[array_key_first($all)][0] : null;
    }

    public function validated(): array {
        $result = [];
        foreach ($this->rules as $field => $rules) {
            if (isset($this->data[$field])) {
                $result[$field] = $this->data[$field];
            }
        }
        return $result;
    }

    private function addError(string $field, string $message): void {
        $this->errors[$field][] = $message;
    }

    private function ruleRequired(string $field, array $params): void {
        if (!isset($this->data[$field]) || (is_string($this->data[$field]) && trim($this->data[$field]) === '')) {
            $this->addError($field, "The $field field is required");
        }
    }

    private function ruleEmail(string $field, array $params): void {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "The $field field must be a valid email address");
        }
    }

    private function ruleNumeric(string $field, array $params): void {
        if (!empty($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->addError($field, "The $field field must be a number");
        }
    }

    private function ruleMin(string $field, array $params): void {
        $min = (float)($params[0] ?? 0);
        if (isset($this->data[$field])) {
            if (is_numeric($this->data[$field]) && (float)$this->data[$field] < $min) {
                $this->addError($field, "The $field field must be at least $min");
            } elseif (is_string($this->data[$field]) && strlen($this->data[$field]) < $min) {
                $this->addError($field, "The $field field must be at least $min characters");
            }
        }
    }

    private function ruleMax(string $field, array $params): void {
        $max = (float)($params[0] ?? 0);
        if (isset($this->data[$field])) {
            if (is_numeric($this->data[$field]) && (float)$this->data[$field] > $max) {
                $this->addError($field, "The $field field must not exceed $max");
            } elseif (is_string($this->data[$field]) && strlen($this->data[$field]) > $max) {
                $this->addError($field, "The $field field must not exceed $max characters");
            }
        }
    }

    private function ruleIn(string $field, array $params): void {
        if (!empty($this->data[$field]) && !in_array($this->data[$field], $params)) {
            $allowed = implode(', ', $params);
            $this->addError($field, "The $field field must be one of: $allowed");
        }
    }

    private function ruleString(string $field, array $params): void {
        if (!empty($this->data[$field]) && !is_string($this->data[$field])) {
            $this->addError($field, "The $field field must be a string");
        }
    }

    private function ruleBoolean(string $field, array $params): void {
        if (isset($this->data[$field])) {
            $val = $this->data[$field];
            if (!in_array($val, [true, false, 0, 1, '0', '1', 'true', 'false'], true)) {
                $this->addError($field, "The $field field must be a boolean");
            }
        }
    }

    private function ruleUrl(string $field, array $params): void {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_URL)) {
            $this->addError($field, "The $field field must be a valid URL");
        }
    }

    private function ruleRegex(string $field, array $params): void {
        if (!empty($this->data[$field]) && !empty($params[0])) {
            if (!preg_match($params[0], $this->data[$field])) {
                $this->addError($field, "The $field field format is invalid");
            }
        }
    }
}
