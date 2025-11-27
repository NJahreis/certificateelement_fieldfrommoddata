<?php
// This file is part of the tool_certificate plugin for Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains the certificate element fieldfrommoddata's core interaction API.
 *
 * @package   certificateelement_fieldfrommoddata
 * @copyright 2025 Nikolai Jahreis <nikolai.jahreis@uni-bayreuth.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace certificateelement_fieldfrommoddata;

/**
 * The certificate element fieldfrommoddata's core interaction API.
 *
 * @package   certificateelement_fieldfrommoddata
 * @copyright 2025 Nikolai Jahreis <nikolai.jahreis@uni-bayreuth.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class element extends \tool_certificate\element {
    /**
     * This function renders the form elements when adding a certificate element.
     *
     * @param \MoodleQuickForm $mform the edit_form instance
     */
    public function render_form_elements($mform) {
        $mform->addElement('text', 'moddataid', get_string('moddataid', 'certificateelement_fieldfrommoddata'));
        $mform->setType('moddataid', PARAM_INT);
        $mform->addHelpButton('moddataid', 'moddataid', 'certificateelement_fieldfrommoddata');

        $mform->addElement('text', 'fieldname', get_string('fieldname', 'certificateelement_fieldfrommoddata'));
        $mform->setType('fieldname', PARAM_NOTAGS);
        $mform->addHelpButton('fieldname', 'fieldname', 'certificateelement_fieldfrommoddata');
        $mform->setDefault('fieldname', get_string('fieldname_default', 'certificateelement_fieldfrommoddata'));

        $behaviourtypes = ['First', 'Last', 'Cancatenate'];
        $mform->addElement('select', 'behaviour', get_string('behaviour', 'certificateelement_fieldfrommoddata'), $behaviourtypes);
        $mform->setDefault('behaviour', get_string('behaviour_default', 'certificateelement_fieldfrommoddata'));

        $mform->addHelpButton('behaviour', 'behaviour', 'certificateelement_fieldfrommoddata');

        $mform->addElement('text', 'delimiter', get_string('delimiter', 'certificateelement_fieldfrommoddata'));
        $mform->setType('delimiter', PARAM_NOTAGS);
        $mform->addHelpButton('delimiter', 'delimiter', 'certificateelement_fieldfrommoddata');
        $mform->setDefault('delimiter', get_string('delimiter_default', 'certificateelement_fieldfrommoddata'));
        $mform->disabledIf('delimiter', 'behaviour', 'neq', 2);

        parent::render_form_elements($mform);
    }

    /**
     * Handles saving the form elements created by this element.
     * Can be overridden if more functionality is needed.
     *
     * @param \stdClass $data the form data or partial data to be upfieldfrommoddatad (i.e. name, posx, etc.)
     */
    public function save_form_data(\stdClass $data) {
        $data->data = json_encode(['moddataid' => (string)$data->moddataid,
        'fieldname' => $data->fieldname,
        'behaviour' => $data->behaviour,
        'delimiter' => $data->delimiter,
        ]);
        parent::save_form_data($data);
    }

    /**
     * Prepare data to pass to moodleform::set_data()
     *
     * @return \stdClass|array
     */
    public function prepare_data_for_form() {
        $record = parent::prepare_data_for_form();
        if (!empty($this->get_data())) {
            $data = json_decode($this->get_data());
            $record->moddataid = $data->moddataid;
            $record->fieldname = $data->fieldname;
            $record->behaviour = $data->behaviour;
            $record->delimiter = $data->delimiter;
        }
        return $record;
    }

    /**
     * Handles rendering the element on the pdf.
     *
     * @param \pdf $pdf the pdf object
     * @param bool $preview true if it is a preview, false otherwise
     * @param \stdClass $user the user we are rendering this for
     * @param \stdClass $issue the issue we are rendering
     */
    public function render($pdf, $preview, $user, $issue) {
        // Decode the information stored in the database.
        $moddatainfo = @json_decode($this->get_data(), true);
        $moddatainfo += ['moddataid' => '', 'fieldname' => '', 'behaviour' => '', 'delimiter' => ''];

        // Ensure of the data is set. Else use placeholder text.
        if (!($moddatainfo['moddataid'] === '' || $moddatainfo['fieldname'] === '')) {
            $fieldvalues = $this->get_data_for_user($moddatainfo['moddataid'], $user->id, $moddatainfo['fieldname']);
            $value = $this->format_fieldvalues($fieldvalues, $moddatainfo['behaviour'], $moddatainfo['delimiter']);
        } else {
            $value = get_string('placeholder', 'certificateelement_fieldfrommoddata');
        }

        // Ensure that a value has been set.
        if (!empty($value)) {
            \tool_certificate\element_helper::render_content($pdf, $this, $value);
        } else {
            $value = get_string('placeholder', 'certificateelement_fieldfrommoddata');
        }
    }

    /**
     * Render the element in html.
     *
     * This function is used to render the element when we are using the
     * drag and drop interface to position it.
     *
     * @return string the html
     */
    public function render_html() {
        // During preview we can not ensure a valid mod data instace and user are selected.
        // Thus we just use a placeholder text.
        $placeholder = get_string('placeholder', 'certificateelement_fieldfrommoddata');
        return \tool_certificate\element_helper::render_html_content($this, $placeholder);
    }

    /**
     * Get field data from mod_data instance
     * @param int $cmid coursemodule id of the mod data instance
     * @param int $userid userid id of the user we are rendering for
     * @param string $fieldname the name of the field to pull
     * @return array Array of field values for this user
     */
    private function get_data_for_user($cmid, $userid, $fieldname) {
        global $DB;

        // Get the data instance id from course module.
        $cm = $DB->get_record('course_modules', ['id' => $cmid], 'instance', MUST_EXIST);
        $dataid = $cm->instance;

        // Get all records ids for this user in this data activity.
        $records = $DB->get_records('data_records', ['dataid' => $dataid, 'userid' => $userid], '', 'id');

        if (empty($records)) {
            return []; // No entries for this user.
        }

        $recordids = array_keys($records);

        // Get field values for this record.
        $sql = "SELECT c.content AS fieldvalue
        FROM {data_content} c
        JOIN {data_fields} f ON f.id = c.fieldid
        WHERE c.recordid IN (" . implode(',', $recordids) . ") AND f.name = :fieldname";
        $fieldvalues = $DB->get_fieldset_sql($sql, ['fieldname' => $fieldname]);

        return $fieldvalues;
    }

    /**
     * Format the field value data for the certificate element
     * @param array $fieldvalues array of values from mod data
     * @param string $behaviour: String which defines how multiple entries are handeled
     * @param string $delimiter the delimiter to use
     * @return string String to place on the certificate
     */
    private function format_fieldvalues($fieldvalues, $behaviour, $delimiter) {
        debugging('The field values: ' . implode($fieldvalues));
        debugging('The behaviour is: ' . $behaviour);
        if (count($fieldvalues) == 1) {
            return reset($fieldvalues);
        }

        if ($behaviour == 0) {
            return reset($fieldvalues);
        } else if ($behaviour == 1) {
            return end($fieldvalues);
        } else {
            $valuestring = reset($fieldvalues);
            array_shift($fieldvalues);
            foreach ($fieldvalues as $value) {
                $valuestring .= $delimiter . $value;
            }
            return $valuestring;
        }
    }
}
