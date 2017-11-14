<?php
namespace tool_replication\repo;

use progress_trace;

defined('MOODLE_INTERNAL') || die();

/**
 * Replication db.
 *
 * @package    tool_replication
 * @copyright  demmonico
 */
class db_sync
{
    use trace_trait, configure_trait;

    /**
     * @var null|progress_trace
     */
    protected $tracer;


    /**
     * db_sync constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        // config
        $this->selfConfigure($config);
    }

    /**
     * @param string $dumpFile
     * @return bool
     */
    public function export($dumpFile)
    {
        global $CFG;
        $result = null;

        if ($CFG->dbname) {
            $this->trace('dbsync_start');

            // touch dir
            file_sync::touchDir(dirname($dumpFile));

            // options
            $dbname = $CFG->dbname;
            $dbMap = [
                'dbhost' => 'host',
                'dbuser' => 'user',
                'dbpass' => 'password',
            ];
            $options = '';
            foreach ($dbMap as $k => $v) {
                if ($CFG->$k) {
                    $options .= "--$v=\"{$CFG->$k}\" ";
                }
            }
            if ($CFG->dboptions && isset($CFG->dboptions['dbport']) && $CFG->dboptions['dbport']) {
                $options = "--port={$CFG->dboptions['dbport']} $options";
            }

            // mysqldump
            $cmd = "mysqldump $options $dbname > $dumpFile";
            $out = [];
            exec($cmd, $out, $result);

            $this->trace([['dbsync_out' => $out], ['dbsync_res' => $result]]);
            $this->trace('dbsync_end', true);
        }

        return $result === 0;
    }

    public function import($dumpFile)
    {
        global $CFG;
        $result = null;

        if ($CFG->dbname) {
            $this->trace('dbsync_start');

            if (!file_exists($dumpFile)) {
                throw new \Exception('Dump file doesn\'t exists');
            }

            // options
            $dbname = $CFG->dbname;
            $dbMap = [
                'dbhost' => 'host',
                'dbuser' => 'user',
                'dbpass' => 'password',
            ];
            $options = '';
            foreach ($dbMap as $k => $v) {
                if ($CFG->$k) {
                    $options .= "--$v=\"{$CFG->$k}\" ";
                }
            }
            if ($CFG->dboptions && isset($CFG->dboptions['dbport']) && $CFG->dboptions['dbport']) {
                $options = "--port={$CFG->dboptions['dbport']} $options";
            }

            // mysql import
            $cmd = "mysql $options $dbname < $dumpFile";
            $out = [];
            exec($cmd, $out, $result);

            $this->trace([['dbsync_out' => $out], ['dbsync_res' => $result]]);
            $this->trace('dbsync_end', true);
        }

        return $result === 0;
    }

}
