<?php
defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->libdir.'/formslib.php');


/**
 * Definition of replication tool settings form.
 *
 * @package    tool_replication
 * @copyright  demmonico
 */
class replication_common_form extends moodleform
{
    /**
     * Define replication form.
     */
    protected function definition()
    {
        $mform = $this->_form;

        $mform->addElement('header', 'headercommon', get_string('headercommon', 'tool_replication'));

        global $OUTPUT;
        $mform->addElement('html', $OUTPUT->notification(get_string('messagecronedit', 'tool_replication'), 'info'));

        $mform->addElement('checkbox', 'isMasterMode', get_string('modeswitch', 'tool_replication'));
        $mform->setType('isMasterMode', PARAM_BOOL);
        $mform->setDefault('isMasterMode', 0);

        // TODO add edit cron params
//        $mform->addElement('text', 'cron', get_string('cron', 'tool_replication'));
//        $mform->setType('cron', PARAM_ALPHANUMEXT);
//        $mform->addRule('cron', get_string('required'), 'required', null);

        if (isset($this->_customdata['isMasterMode']) && $this->_customdata['isMasterMode']) {
            $mform->addElement('text', 'sshKeyPath', get_string('sshKeyPath', 'tool_replication'));
            $mform->setType('sshKeyPath', PARAM_SAFEPATH);
            $mform->addRule('sshKeyPath', get_string('required'), 'required', null);
        }

        $this->add_action_buttons(false, get_string('savereplicationparams', 'tool_replication'));
    }
}
