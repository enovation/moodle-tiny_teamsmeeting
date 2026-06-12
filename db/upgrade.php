<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin upgrade steps.
 *
 * @package     tiny_teamsmeeting
 * @copyright   2023 Enovation Solutions
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute tiny_teamsmeeting upgrade from the given old version.
 *
 * @param int $oldversion Old plugin version.
 * @return bool
 */
function xmldb_tiny_teamsmeeting_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2025100205) {
        // Normalise percent-encoding in stored meeting links to uppercase (RFC 3986)
        // so lookups succeed whether or not Moodle has re-saved the HTML content
        // (Moodle uppercases %xx sequences when saving).
        $records = $DB->get_records('tiny_teamsmeeting', null, 'id ASC', 'id, link');
        foreach ($records as $record) {
            $normalised = preg_replace_callback('/%[0-9a-f]{2}/i', fn($m) => strtoupper($m[0]), $record->link);
            if ($normalised !== $record->link) {
                $DB->set_field('tiny_teamsmeeting', 'link', $normalised, ['id' => $record->id]);
            }
        }
        unset($records);

        upgrade_plugin_savepoint(true, 2025100205, 'tiny', 'teamsmeeting');
    }

    return true;
}
