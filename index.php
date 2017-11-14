<?php
/**
 * Replication tool
 *
 * @package    tool_replication
 * @copyright  demmonico
 */

use \tool_replication\repo\replicator;

define('NO_OUTPUT_BUFFERING', true);

require('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_once('forms/replication_common_form.php');
require_once('forms/replication_hosts_form.php');

require_login();
admin_externalpage_setup('toolreplication');

// create replicator instance
$replicator = new replicator();
$replicatorSettings = $replicator->getSettings();
$replicatorHosts = $replicator->getHosts();

$isMasterMode = 'master' === $mode = $replicator->getSettings('mode');

// Create the form.
$mformCommon = new replication_common_form(null, ['isMasterMode' => $isMasterMode]);
$mformHosts = new replication_hosts_form(null, ['hosts' => $replicatorHosts]);
$problem = '';

// clear url params
$PAGE->url->remove_all_params();



// Start output.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('replicationheader', 'tool_replication'));



// save common settings
if ($data = $mformCommon->get_data()) {

    // save common
    $hasUpdated = false;
    foreach ($replicatorSettings as $k => $v) {
        if (isset($data->$k) && $data->$k !== $v) {
            $replicatorSettings[$k] = trim($data->$k);
            $hasUpdated = true;
        }
    }
    // if need to change mode
    if ($data->isMasterMode && $replicatorSettings['mode'] !== 'master' || !$data->isMasterMode && $replicatorSettings['mode'] === 'master') {
        $replicatorSettings['mode'] = $data->isMasterMode ? 'master' : 'slave';
        $hasUpdated = true;
    }
    if ($hasUpdated) {
        $replicator->setSettings($replicatorSettings);
    }

    // Scroll down to the bottom when finished.
    $PAGE->requires->js_init_code("window.scrollTo(0, 5000000);");

    // Finish up.
    echo $OUTPUT->notification(get_string('success'), 'success');
//    echo $OUTPUT->continue_button("$CFG->wwwroot/$CFG->admin/");
    echo $OUTPUT->continue_button($PAGE->url);
}

// save hosts (only if master mode)
elseif ($isMasterMode && $data = $mformHosts->get_data()) {

    // save hosts
    $replicatorHosts[] = [
        'host' => $data->host,
        'user' => $data->user,
        'port' => $data->port,
        'path' => $data->path,
    ];
    $replicator->setHosts($replicatorHosts);

    // Scroll down to the bottom when finished.
    $PAGE->requires->js_init_code("window.scrollTo(0, 5000000);");

    // Finish up.
    echo $OUTPUT->notification(get_string('success'), 'success');
    echo $OUTPUT->continue_button($PAGE->url);
}

// delete host (only if master mode)
elseif ($isMasterMode && isset($_GET['action'])) {

    if ($_GET['action'] != 'delete') {
        echo $OUTPUT->notification(get_string('messageunknownaction', 'tool_replication'), 'error');
    } elseif (!isset($_GET['hostid']) || !$_GET['hostid']) {
        echo $OUTPUT->notification(get_string('messageinvalidid', 'tool_replication'), 'error');
    } else {
        // delete & save hosts
        foreach ($replicatorHosts as $i => $host) {
            if ($_GET['hostid'] == md5(implode(':', $host))) {
                unset($replicatorHosts[$i]);
            }
        }
        $replicator->setHosts($replicatorHosts);

        echo $OUTPUT->notification(get_string('success'), 'success');
    }

    // Scroll down to the bottom when finished.
    $PAGE->requires->js_init_code("window.scrollTo(0, 5000000);");

    // Finish up.
    echo $OUTPUT->continue_button($PAGE->url);
}

// Otherwise display the settings form.
else {

    // show current mode
    echo $OUTPUT->notification(get_string('messagemode', 'tool_replication', $mode), $isMasterMode ? 'warning' : 'info');

    $info = format_text(get_string('replicationintro', 'tool_replication'), FORMAT_MARKDOWN);
    echo $OUTPUT->box($info);


    //Set default data (if any)
    $mformCommon->set_data(array_merge($replicatorSettings, ['isMasterMode' => $isMasterMode]));
    //displays the form
    $mformCommon->display();


    if ($isMasterMode) {
        //Set default data (if any)
        $mformHosts->set_data($replicatorSettings);
        //displays the form
        $mformHosts->display();
    }


    if ($problem !== '') {
        echo $OUTPUT->box($problem, 'generalbox error');
    }
}



echo $OUTPUT->footer();
