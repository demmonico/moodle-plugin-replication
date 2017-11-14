<?php

namespace tool_replication\task;

use null_progress_trace;
use text_progress_trace;
use tool_replication\maintenance\dummy;
use tool_replication\repo\replicator;

defined('MOODLE_INTERNAL') || die();

/**
 * Replication task
 *
 * @package    tool_replication
 * @copyright  demmonico
 */
class replication_task extends \core\task\scheduled_task
{
    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('plugincronjobname', 'tool_replication');
    }

    /**
     * Run task.
     * @return int
     * @throws \Exception
     */
    public function execute()
    {
        // increase execution time
        $oldExecutionTime = ini_get('max_execution_time');
        $oldExecutionTime = $oldExecutionTime ?: ($oldExecutionTime !== 0 ? 30 : 0);
        $newExecutionTime = 60*60;    // 60 min
        set_time_limit($newExecutionTime);

        // instantiate replicator
        $trace = empty($options['verbose']) ? new null_progress_trace() : new text_progress_trace();
        $replicator = new replicator($trace);

        // enable maintenance mode
        $maintenanceOptions = ['message' => get_string('clireplicationnotice', 'tool_replication')];
        $maintenance = new dummy($maintenanceOptions);
        $maintenance->enable();

        try {
            // update mirror or instance
            $replicator->update();

            // !!!
            // /docker-shared
            // mysqlshow -vv -u root -h db dbecloud2 > /docker-shared/db-stat-1.log
            // diff -y db-stat.log db-stat-1.log
            // diff -y --suppress-common-lines db-stat.log db-stat-1.log
            // diff -u db-stat.log db-stat-1.log
            // diff -c db-stat.log db-stat-1.log
            // diff db-stat.log db-stat-1.log

            // docker
            // TODO FIX for Moodle - add to setup rsync
            // TODO FIX for Moodle - config sitename
            // TODO add run cron to script starts container       FIX call cron
            // TODO add copying file /docker-shared/replication-ssh-keys/id_rsa.pub to /home/dev????/.ssh/authorized_keys   at offlines

            // +++
            // TODO add pack previous mirror version to backing up

        } catch (\Exception $e) {
            // Moodle catch all exceptions
            throw $e;
        } finally {
            $maintenance->disable();
        }

        // run sync with remote
        $exitCode = (int) !$replicator->sync()->end();

        // return back
        set_time_limit($oldExecutionTime);
        return $exitCode;
    }


    /**
     * @return array
     */
    public static function getDefaultCronSettings()
    {
        return array_merge(
            replicator::getDefaultCronSettings(),
            [
                'classname' => get_called_class(),
                'blocking' => 1,
            ]
        );
    }
}
