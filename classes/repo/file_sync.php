<?php
namespace tool_replication\repo;

use progress_trace;

defined('MOODLE_INTERNAL') || die();

/**
 * Replication files.
 *
 * @package    tool_replication
 * @copyright  demmonico
 */
class file_sync
{
    use trace_trait, configure_trait;

    /**
     * @var null|progress_trace
     */
    protected $tracer;



    /**
     * @param string $path
     */
    public static function touchDir($path)
    {
        global $CFG;
        try {
            if (!is_dir($path)) {
                mkdir($path, $CFG->directorypermissions, true);
                chmod($path, $CFG->directorypermissions);
            }
        } catch (\Exception $e) {
            echo "En error occurred while creating moodledata dir for replication. \n" . var_export($e, true);
            die;
        }
    }



    /**
     * file_sync constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        // config
        $this->selfConfigure($config);
    }



    /**
     * @param string $src
     * @param string $dest
     * @param bool $isDeleteDestOutsider
     * @return bool
     */
    public function syncLocal($src, $dest, $isDeleteDestOutsider = false)
    {
        $options = '-avzhH' . ($isDeleteDestOutsider ? ' --delete' : '');
        return $this->sync($src, $dest, $options);
    }

    /**
     * @param string $src
     * @param string $dest
     * @param string|int $sshKeyPort
     * @param string $sshKeyFile
     * @param array $excluded
     * @return bool
     * @example rsync -avzhH --progress --exclude "debug.enable" -e "ssh -p 22 -i /docker-shared/replication-ssh-keys/id_rsa" "/var/moodledata/mirror/" "dev@host:/var/docker/projects/dbe2loc/data/mirror"
     */
    public function syncRemote($src, $dest, $sshKeyPort, $sshKeyFile, $excluded = [])
    {
        $exc = '';
        foreach ($excluded as $i) {
            $exc .= "--exclude \"$i\" ";
        }
        $ssh = "-e \"ssh -p $sshKeyPort -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o LogLevel=quiet -i $sshKeyFile\"";
        $options = "-avzhH --progress $exc $ssh";

        return $this->sync($src, $dest, $options);
    }



    /**
     * @param string $src
     * @param string $dest
     * @param string $options
     * @return bool
     */
    protected function sync($src, $dest, $options)
    {
        $this->trace('filesync_start');

        $out = [];
        $result = null;
        $cmd = "rsync $options \"$src\" \"$dest\"";

        exec($cmd, $out, $result);

        $this->trace([['filesync_out' => $out], ['filesync_res' => $result]]);
        $this->trace('filesync_end', true);

        return $result === 0;
    }
}
