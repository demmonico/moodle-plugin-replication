<?php
/**
 * Replication sync script which manually runs replication_task.
 *
 * This script is meant to be called from a system cronjob.
 *
 * Sample cron entry:
 * # 5 minutes past 4am
 * 5 4 * * * sudo -u www-data /usr/bin/php /var/www/html/admin/tool/replication/cli/update.php
 *
 * Notes:
 *   - it is required to use the web server account when executing PHP CLI scripts
 *   - you need to change the "www-data" to match the apache user account
 *   - use "su" if "sudo" not available
 *   - For debugging & better logging, you are encouraged to use in the command line:
 *     -d log_errors=1 -d error_reporting=E_ALL -d display_errors=0 -d html_errors=0
 *
 * @package    tool_replication
 * @copyright  demmonico
 */

define('CLI_SCRIPT', true);

require(__DIR__.'/../../../../config.php');
global $CFG;
require_once($CFG->libdir.'/clilib.php');

// Now get cli options.
$optionsAllowed = [
    'v'=>'verbose',
    'h'=>'help'
];
list($options, $unrecognized) = cli_get_params(
    array_combine(array_values($optionsAllowed), array_fill(0, count($optionsAllowed), false)),
    $optionsAllowed
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Execute creating/updating replication data.
The tool_replication plugin must be enabled and properly configured.

Options:
-v, --verbose         Print verbose progress information
-h, --help            Print out this help

Example:
sudo -u www-data /usr/bin/php /var/www/html/admin/tool/replication/cli/update.php

Sample cron entry:
# 5 minutes past 4am
5 4 * * * sudo -u www-data /usr/bin/php /path_to_plugin/replication/cli/update.php
";

    echo $help;
    die;
}



// Abort execution of the CLI script if the \tool_replication\task\update is enabled.
//$task = \core\task\manager::get_scheduled_task('tool_replication\task\replication_task');
//if (!$task->get_disabled()) {
//    cli_error('[TOOL REPLICATION] The scheduled task replication_task is enabled, the cron execution has been aborted.');
//}



$exitCode = 0;
$task = \core\task\manager::get_scheduled_task('tool_replication\task\replication_task');
$exitCode = $task->execute();



// if all success then return 0 exit code else 1
exit($exitCode);
