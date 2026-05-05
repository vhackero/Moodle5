<?php
// This file is part of Moodle - http://moodle.org/

namespace local_qrcurp\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Typed accessor utilities for local_qrcurp plugin settings.
 */
class config {
    /** @var string */
    private const COMPONENT = 'local_qrcurp';

    /**
     * Get raw setting value.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $name, $default = '') {
        $value = get_config(self::COMPONENT, $name);
        return ($value === false || $value === null) ? $default : $value;
    }

    /**
     * Get normalized string value.
     */
    public static function get_string(string $name, string $default = ''): string {
        return trim((string) self::get($name, $default));
    }

    /**
     * Get integer value.
     */
    public static function get_int(string $name, int $default = 0): int {
        return (int) self::get($name, $default);
    }

    /**
     * Get bool-like value.
     */
    public static function get_bool(string $name, bool $default = false): bool {
        return (bool) self::get_int($name, $default ? 1 : 0);
    }

    /**
     * Get comma separated list from settings.
     *
     * @return string[]
     */
    public static function get_csv_list(string $name): array {
        $raw = self::get_string($name);
        if ($raw === '') {
            return [];
        }

        $items = array_map('trim', explode(',', $raw));
        $items = array_filter($items, static function(string $item): bool {
            return $item !== '';
        });

        return array_values($items);
    }
    /**
     * Parse category limits configuration.
     *
     * Supported formats:
     * - "100" (global limit for any category)
     * - "5:100,8:200" (specific category limits)
     * - "*:100,8:200" (global default + specific overrides)
     *
     * @return array{default:?int,categories:array<int,int>}
     */
    public static function get_category_limits(string $name): array {
        $raw = self::get_string($name);
        if ($raw == '') {
            return ['default' => null, 'categories' => []];
        }

        if (preg_match('/^\d+$/', $raw)) {
            return ['default' => (int) $raw, 'categories' => []];
        }

        $result = ['default' => null, 'categories' => []];
        foreach (self::get_csv_list($name) as $entry) {
            if (!str_contains($entry, ':')) {
                continue;
            }

            [$categorytoken, $limittoken] = array_map('trim', explode(':', $entry, 2));
            if (!preg_match('/^\d+$/', $limittoken)) {
                continue;
            }

            $limit = (int) $limittoken;
            if ($categorytoken === '*') {
                $result['default'] = $limit;
                continue;
            }

            if (preg_match('/^\d+$/', $categorytoken)) {
                $result['categories'][(int) $categorytoken] = $limit;
            }
        }

        return $result;
    }

}
