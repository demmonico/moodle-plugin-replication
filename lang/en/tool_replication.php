<?php
/**
 * Strings for component 'tool_generator', language 'en'.
 *
 * @package    tool_replication
 * @copyright  demmonico
 */

//$string['clidriverlist'] = 'Available database drivers for migration';
//$string['cliheading'] = 'Database migration - make sure nobody is accessing the server during migration!';
//$string['climigrationnotice'] = 'Database migration in progress, please wait until the migration completes and server administrator updates configuration and deletes the $CFG->dataroot/climaintenance.html file.';
//$string['convertinglogdisplay'] = 'Converting log display actions';
//$string['dbexport'] = 'Database export';
//$string['dbtransfer'] = 'Database migration';
//$string['enablemaintenance'] = 'Enable maintenance mode';
//$string['enablemaintenance_help'] = 'This option enables maintanance mode during and after the database migration, it prevents access of all users until the migration is completed. Please note that administrator has to manually delete $CFG->dataroot/climaintenance.html file after updating config.php settings to resume normal operation.';
//$string['exportdata'] = 'Export data';
//$string['notargetconectexception'] = 'Can not connect target database, sorry.';
//$string['options'] = 'Options';
//$string['pluginname'] = 'Database transfer';
//$string['targetdatabase'] = 'Target database';
//$string['targetdatabasenotempty'] = 'Target database must not contain any tables with given prefix!';
//$string['transferdata'] = 'Transfer data';
//$string['transferdbintro'] = 'This script will transfer the entire contents of this database to another database server. It is often used for migration of data to different database type.';
//$string['transferdbtoserver'] = 'Transfer this Moodle database to another server';
//$string['transferringdbto'] = 'Transferring this {$a->dbtypefrom} database to {$a->dbtype} database "{$a->dbname}" on "{$a->dbhost}"';

// form
$string['cron'] = 'Replication cron timeset';
$string['headercommon'] = 'Common settings';
$string['headerhosts'] = 'Remote hosts';
$string['headernewhost'] = 'Add new remote host';
$string['hostname'] = 'Host IP / domain';
$string['hostport'] = 'Host port';
$string['hostuser'] = 'Host user';
$string['hostpath'] = 'Remote Moodle data path';
$string['messagecronedit'] = 'To edit cron job\'s timetable please, follow to the Site administration > Server > Scheduled tasks section';
$string['messageconfirmdelete'] = 'Are you sure you want to delete this host?';
$string['messageinvalidid'] = 'Invalid entity\'s id';
$string['messagemode'] = 'Your instance is at "{$a}" mode';
$string['messageunknownaction'] = 'Unknown action';
$string['modeswitch'] = 'Is instance in master mode';
$string['plugincronjobname'] = 'Cron job which replicate users\' data and cources\' data to remote instances';
$string['pluginname'] = 'Replication';
$string['pluginvisiblename'] = 'Replication users\' data and cources\' data to remote instances';
$string['port'] = 'Port';
$string['savereplicationparams'] = 'Save';
$string['sshKeyPath'] = 'SSH keys location path';

$string['clireplicationnotice'] = 'Replication instance in progress, please wait until the replication completes. It started at {$datetime}';
$string['replicationheader'] = 'Replication Moodle data files and database to remote instance';
$string['replicationintro'] = 'This plugin will transfer this database contents and Moodle files to instance\'s server. It is could used for deploy and synchronization multi-instances Moodle applications.';
$string['replicationlogdisplay'] = 'Replication log display actions';
$string['replicationlogdone'] = 'Done';

$string['replicator_init'] = 'Replicator was initialized';
$string['filesync_end'] = 'Files synchronization was ended';
$string['filesync_out'] = 'Files synchronization\'s output: \'{$a}\'';
$string['filesync_res'] = 'Files synchronization\'s result: \'{$a}\'';
$string['filesync_start'] = 'Files synchronization was started';

$string['dbsync_end'] = 'DB synchronization was ended';
$string['dbsync_out'] = 'DB synchronization\'s output: \'{$a}\'';
$string['dbsync_res'] = 'DB synchronization\'s result: \'{$a}\'';
$string['dbsync_start'] = 'DB synchronization was started';
