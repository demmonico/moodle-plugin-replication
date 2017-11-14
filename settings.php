<?php
/**
 * Add hidden links db transfer tool
 *
 * @package    tool_replication
 * @copyright  demmonico
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('server', new admin_externalpage('toolreplication', get_string('pluginvisiblename', 'tool_replication'),
        $CFG->wwwroot.'/'.$CFG->admin.'/tool/replication/index.php', 'moodle/site:config', false));
}
