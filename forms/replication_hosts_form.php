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
class replication_hosts_form extends moodleform
{
    /**
     * Define replication form.
     */
    protected function definition()
    {
        $mform = $this->_form;


        // exists hosts
        $mform->addElement('header', 'headerhosts', get_string('headerhosts', 'tool_replication'));

        if (isset($this->_customdata['hosts']) && !empty($this->_customdata['hosts']) && is_array($this->_customdata['hosts'])) {
            $table = new html_table();
            $table->head = ['Host/IP', 'User', 'Port' , 'Remote Moodle data path'];
            foreach ($this->_customdata['hosts'] as $host) {
                $link = $this->_form->getAttribute('action') . '?action=delete&hostid=' . md5(implode(':', $host));
                $confirm = 'onClick="javascript:return confirm(\'' . get_string('messageconfirmdelete', 'tool_replication') . '\');"';
                $table->data[] = [
                    $host['host'],
                    $host['user'],
                    $host['port'],
                    $host['path'],
                    '<a href="'.$link.'" ' . $confirm . '>Remove</a>',
                ];
            }
            $mform->addElement('html', html_writer::table($table));
        } else {
            $mform->addElement('html', 'No remote hosts');
        }


        // add new host
        $mform->addElement('header', 'headernewhost', get_string('headernewhost', 'tool_replication'));

        $mform->addElement('text', 'host', get_string('hostname', 'tool_replication'));
        $mform->setType('host', PARAM_HOST);
        $mform->addRule('host', get_string('required'), 'required', null);

        $mform->addElement('text', 'port', get_string('hostport', 'tool_replication'));
        $mform->setType('port', PARAM_INT);
        $mform->setDefault('port', 22);

        $mform->addElement('text', 'user', get_string('hostuser', 'tool_replication'));
        $mform->setType('user', PARAM_ALPHANUMEXT);
        $mform->addRule('user', get_string('required'), 'required', null);

        $mform->addElement('text', 'path', get_string('hostpath', 'tool_replication'));
        $mform->setType('path', PARAM_SAFEPATH);
        $mform->addRule('path', get_string('required'), 'required', null);

//        $mform->addElement('passwordunmask', 'pass', get_string('hostpass', 'tool_replication'));
//        $mform->setType('hostpass', PARAM_RAW);

        $this->add_action_buttons(false, get_string('savereplicationparams', 'tool_replication'));
    }
}
