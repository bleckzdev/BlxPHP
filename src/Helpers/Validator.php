<?php
namespace BlxPHP\Helpers;
use BlxPHP\Responser;

class Validator
{

    public static function validate($data, $rules)
    {
        $errors = [];
        foreach ($rules as $rule) {
            if (empty($data[$rule])) {
                $errors[] = "El campo $rule es obligatorio";
            }
        }
        if (!empty($errors)) {
            Responser::badRequest($errors);
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  NUEVO SISTEMA DE VALIDACIÓN
    // ─────────────────────────────────────────────────────────────
    //
    //  Uso:
    //    Validator::check($data, [
    //        'nombre'   => 'required|string|min:2|max:100',
    //        'edad'     => 'required|int|min_val:1|max_val:120',
    //        'email'    => 'required|email|max:255',
    //        'telefono' => 'nullable|string|length:10',
    //        'monto'    => 'required|numeric|min_val:0.01',
    //        'status'   => 'required|in:activo,inactivo,pendiente',
    //        'fecha'    => 'required|date',
    //        'notas'    => 'nullable|string|max:500',
    //    ]);
    //
    //  Reglas disponibles:
    //    required        — el campo debe existir y no ser vacío
    //    nullable        — si el campo no existe o es null, se omiten las demás reglas
    //    string          — debe ser string
    //    int             — debe ser entero (o string numérico entero)
    //    float / numeric — debe ser numérico
    //    bool            — debe ser booleano (true/false/1/0/"1"/"0")
    //    array           — debe ser array
    //    email           — formato de email válido
    //    date            — formato de fecha válido (Y-m-d o Y-m-d H:i:s)
    //    url             — formato de URL válido
    //    min:N           — largo mínimo (string/array)
    //    max:N           — largo máximo (string/array)
    //    length:N        — largo exacto (string/array)
    //    min_val:N       — valor numérico mínimo
    //    max_val:N       — valor numérico máximo
    //    between_val:N,M — valor numérico entre N y M (inclusive)
    //    in:a,b,c        — el valor debe estar en la lista
    //    not_in:a,b,c    — el valor NO debe estar en la lista
    //    regex:/pattern/ — debe cumplir la expresión regular
    //    alpha           — solo letras
    //    alpha_num       — solo letras y números
    //    alpha_spaces    — solo letras y espacios
    // ─────────────────────────────────────────────────────────────

    /**
     * Valida $data contra un mapa de reglas. Si falla, responde 400 y termina.
     *
     * @param  array               $data   Datos a validar (ej. $_GET, Request::json())
     * @param  array<string,string> $rules  Mapa campo => reglas separadas por |
     * @return void
     */
    public static function check(array $data, array $rules): void
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $ruleParts = explode('|', $ruleString);
            $isNullable = in_array('nullable', $ruleParts, true);
            $isRequired = in_array('required', $ruleParts, true);
            $value = $data[$field] ?? null;
            $exists = array_key_exists($field, $data);

            // ── required / nullable ──
            if ($isRequired && (!$exists || $value === null || $value === '')) {
                $errors[] = "El campo '$field' es obligatorio";
                continue; // no validar más reglas si no existe
            }

            if ($isNullable && (!$exists || $value === null || $value === '')) {
                continue; // campo opcional y vacío, omitir
            }

            // ── procesar cada regla ──
            foreach ($ruleParts as $rule) {
                if ($rule === 'required' || $rule === 'nullable') {
                    continue;
                }

                $error = self::applyRule($field, $value, $rule);
                if ($error !== null) {
                    $errors[] = $error;
                    break; // una falla por campo es suficiente
                }
            }
        }

        if (!empty($errors)) {
            Responser::badRequest($errors);
        }
    }

    /**
     * Aplica una regla individual. Retorna mensaje de error o null si pasa.
     */
    private static function applyRule(string $field, mixed $value, string $rule): ?string
    {
        // ── Reglas con parámetros (rule:param) ──
        if (str_contains($rule, ':')) {
            [$ruleName, $param] = explode(':', $rule, 2);
            return self::applyParameterizedRule($field, $value, $ruleName, $param);
        }

        // ── Reglas simples ──
        return match ($rule) {
            'string' => is_string($value) ? null : "El campo '$field' debe ser texto",
            'int' => filter_var($value, FILTER_VALIDATE_INT) !== false ? null : "El campo '$field' debe ser entero",
            'float',
            'numeric' => is_numeric($value) ? null : "El campo '$field' debe ser numérico",
            'bool' => in_array($value, [true, false, 0, 1, '0', '1'], true) ? null : "El campo '$field' debe ser booleano",
            'array' => is_array($value) ? null : "El campo '$field' debe ser un arreglo",
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) ? null : "El campo '$field' debe ser un email válido",
            'url' => filter_var($value, FILTER_VALIDATE_URL) ? null : "El campo '$field' debe ser una URL válida",
            'date' => self::isValidDate($value) ? null : "El campo '$field' debe ser una fecha válida (Y-m-d)",
            'alpha' => ctype_alpha((string) $value) ? null : "El campo '$field' solo debe contener letras",
            'alpha_num' => ctype_alnum((string) $value) ? null : "El campo '$field' solo debe contener letras y números",
            'alpha_spaces' => preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/u', (string) $value) ? null : "El campo '$field' solo debe contener letras y espacios",
            default => null, // regla desconocida, no bloquear
        };
    }

    /**
     * Aplica reglas que requieren parámetros (min:N, max:N, in:a,b, etc.)
     */
    private static function applyParameterizedRule(string $field, mixed $value, string $ruleName, string $param): ?string
    {
        return match ($ruleName) {
            'min' => (is_string($value) && mb_strlen($value) < (int) $param)
            ? "El campo '$field' debe tener al menos $param caracteres"
            : ((is_array($value) && count($value) < (int) $param)
                ? "El campo '$field' debe tener al menos $param elementos"
                : null),

            'max' => (is_string($value) && mb_strlen($value) > (int) $param)
            ? "El campo '$field' debe tener máximo $param caracteres"
            : ((is_array($value) && count($value) > (int) $param)
                ? "El campo '$field' debe tener máximo $param elementos"
                : null),

            'length' => (is_string($value) && mb_strlen($value) !== (int) $param)
            ? "El campo '$field' debe tener exactamente $param caracteres"
            : ((is_array($value) && count($value) !== (int) $param)
                ? "El campo '$field' debe tener exactamente $param elementos"
                : null),

            'min_val' => is_numeric($value) && (float) $value < (float) $param
            ? "El campo '$field' debe ser mayor o igual a $param"
            : null,

            'max_val' => is_numeric($value) && (float) $value > (float) $param
            ? "El campo '$field' debe ser menor o igual a $param"
            : null,

            'between_val' => self::checkBetween($field, $value, $param),

            'in' => !in_array((string) $value, explode(',', $param), true)
            ? "El campo '$field' debe ser uno de: $param"
            : null,

            'not_in' => in_array((string) $value, explode(',', $param), true)
            ? "El campo '$field' no puede ser: $param"
            : null,

            'regex' => !preg_match($param, (string) $value)
            ? "El campo '$field' no cumple con el formato requerido"
            : null,

            default => null,
        };
    }

    // ─────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────

    private static function isValidDate(mixed $value): bool
    {
        if (!is_string($value))
            return false;

        // Y-m-d H:i:s
        $d = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
        if ($d && $d->format('Y-m-d H:i:s') === $value)
            return true;

        // Y-m-d
        $d = \DateTime::createFromFormat('Y-m-d', $value);
        if ($d && $d->format('Y-m-d') === $value)
            return true;

        return false;
    }

    private static function checkBetween(string $field, mixed $value, string $param): ?string
    {
        $parts = explode(',', $param);
        if (count($parts) !== 2)
            return null;

        $min = (float) $parts[0];
        $max = (float) $parts[1];

        if (is_numeric($value) && ((float) $value < $min || (float) $value > $max)) {
            return "El campo '$field' debe estar entre $min y $max";
        }

        return null;
    }
}