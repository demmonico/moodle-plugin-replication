<?php
/**
 * Replication cron task.
 *
 * @package    tool_replication
 * @copyright  demmonico
 */

defined('MOODLE_INTERNAL') || die();

//$tasks = array(
//    array(
//        'classname' => '\tool_replication\task\replication_task',
//        'blocking' => 1,
////        'minute' => 'R',
//        'minute' => '*',
////        'hour' => '4',
//        'hour' => '*',
//        'day' => '*',
//        'dayofweek' => '*',
//        'month' => '*'
//    ),
//);

$tasks = [\tool_replication\task\replication_task::getDefaultCronSettings()];