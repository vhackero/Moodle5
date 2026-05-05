<?php
// This file is part of Moodle - http://moodle.org/

namespace local_qrcurp\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Manage required custom profile fields for local_qrcurp.
 */
class profile_fields_manager {
    /**
     * Default profile fields used by the plugin.
     *
     * @return array<string, string>
     */
    public static function default_fields(): array {
        return [
            'cp' => 'Código Postal',
            'estado_residencia' => 'Estado de residencia',
            'estado_nacimiento' => 'Estado de nacimiento',
            'fecha_nacimiento' => 'Fecha de nacimiento',
            'ocupacion' => 'Ocupación',
            'curp' => 'CURP',
            'genero' => 'Género',
            'edad' => 'Edad',
            'matricula' => 'Matrícula',
            'rol' => 'Id rol',
            'rol_name' => 'Rol',
            'courseid' => 'Id Curso',
            'grouping' => 'Id Grupo',
        ];
    }

    /**
     * Resolve field shortnames from config with defaults.
     *
     * @return string[]
     */
    public static function get_configured_shortnames(): array {
        $raw = trim((string) get_config('local_qrcurp', 'profilefieldslist'));
        if ($raw === '') {
            return array_keys(self::default_fields());
        }

        $items = array_map('trim', explode(',', $raw));
        $items = array_filter($items, static function(string $item): bool {
            return $item !== '' && preg_match('/^[a-z0-9_]+$/', $item);
        });

        return array_values(array_unique($items));
    }

    /**
     * Get missing profile fields.
     *
     * @param string[] $shortnames
     * @return string[]
     */
    public static function get_missing_fields(array $shortnames): array {
        global $DB;

        $missing = [];
        foreach ($shortnames as $shortname) {
            if (!$DB->record_exists('user_info_field', ['shortname' => $shortname])) {
                $missing[] = $shortname;
            }
        }

        return $missing;
    }

    /**
     * Ensure the requested profile fields exist.
     *
     * @param string[] $shortnames
     * @return string[] Created field shortnames.
     */
    public static function ensure_fields(array $shortnames): array {
        global $DB;

        $defaultnames = self::default_fields();
        $created = [];
        $categoryid = (int) $DB->get_field_sql('SELECT MIN(id) FROM {user_info_category}');
        if ($categoryid <= 0) {
            $categoryid = 1;
        }

        $sortorder = (int) $DB->get_field_sql('SELECT COALESCE(MAX(sortorder), 0) FROM {user_info_field}');

        foreach ($shortnames as $shortname) {
            if ($DB->record_exists('user_info_field', ['shortname' => $shortname])) {
                continue;
            }

            $sortorder++;
            $record = (object) [
                'shortname' => $shortname,
                'name' => $defaultnames[$shortname] ?? ucfirst(str_replace('_', ' ', $shortname)),
                'datatype' => 'text',
                'description' => 'Campo creado automáticamente por local_qrcurp',
                'descriptionformat' => 1,
                'categoryid' => $categoryid,
                'sortorder' => $sortorder,
                'required' => 0,
                'locked' => 1,
                'visible' => 2,
                'forceunique' => 0,
                'signup' => 0,
                'defaultdata' => '',
                'defaultdataformat' => 0,
                'param1' => 30,
                'param2' => 2048,
            ];

            $DB->insert_record('user_info_field', $record);
            $created[] = $shortname;
        }

        return $created;
    }
}
