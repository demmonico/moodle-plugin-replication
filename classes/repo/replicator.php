<?php
namespace tool_replication\repo;

use progress_trace;

defined('MOODLE_INTERNAL') || die();

/**
 * Replication main class.
 *
 * @package    tool_replication
 * @copyright  demmonico
 */
class replicator
{
    use trace_trait;

    /**
     * @var null|progress_trace
     */
    protected $tracer;
    /**
     * @var file_sync
     */
    protected $fileSync;
    /**
     * @var db_sync
     */
    protected $dbSync;

    /**
     * @var string
     */
    protected $dataPath;
    /**
     * @var string
     */
    protected $mirrorPath;
    /**
     * @var string
     */
    protected $mirrorFolder = 'mirror';
    /**
     * @var string
     */
    protected $settingsPath;
    /**
     * @var string
     */
    protected $settingsFolder = 'settings';
    /**
     * @var string
     */
    protected $hostsFile = 'hosts.conf';
    /**
     * @var string
     */
    protected $settingsFile = 'settings.conf';

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var bool
     */
    protected $hasError = false;

    /**
     * @var float
     */
    protected $timeStart;
    /**
     * @var float
     */
    protected $timeLast;
    /**
     * @var string
     */
    protected $logFile = 'replication.log';
    /**
     * @var string
     */
    protected $logId;
    /**
     * @var bool
     */
    protected $logIsLogged = false;

    /**
     * @var string
     */
    protected $debugFlagFile = 'debug.enable';



    /**
     * @return array
     */
    public static function getDefaultCronSettings()
    {
        return [
            'minute' => '0',
            'hour' => '3',
            'day' => '*',
            'dayofweek' => '*',
            'month' => '*'
        ];
    }



    /**
     * mirror constructor.
     * @param progress_trace|null $tracer
     */
    public function __construct(progress_trace $tracer = null)
    {
        global $CFG;

        $this->timeStart = microtime(true);
        $this->logId = md5($this->timeStart);

        $this->dataPath = $CFG->dataroot;
        $this->mirrorPath = $this->dataPath . DIRECTORY_SEPARATOR . $this->mirrorFolder;
        $this->settingsPath = $this->mirrorPath . DIRECTORY_SEPARATOR . $this->settingsFolder;
        file_sync::touchDir($this->mirrorPath);

        $this->tracer = $tracer;

        $this->fileSync = new file_sync(['tracer' => $this->tracer]);
        $this->dbSync = new db_sync(['tracer' => $this->tracer]);

        $this->trace('replicator_init');
    }



    /**
     * @param string $key
     * @return array|string
     * @throws \Exception
     * @use
     * read hosts in format user@host:port:/local_path/to/data
     * return in format [..., ['host' => user@host, 'port' => port, 'path' => '/local_path/to/data'] ]
     */
    public function getSettings($key = '')
    {
        // load settings
        if (empty($this->settings)) {
            $settingsFile = $this->settingsPath . DIRECTORY_SEPARATOR . $this->settingsFile;
            if (file_exists($settingsFile)) {
                $settingsLines = (array) file($settingsFile, FILE_IGNORE_NEW_LINES & FILE_SKIP_EMPTY_LINES);

                // unpack data
                $settings = [];
                foreach ($settingsLines as $line) {
                    $set = explode('=', trim($line));
                    if (isset($set[0], $set[1]) && $set[0]) {
                        $settings[$set[0]] = $set[1];
                    }
                    unset($set);
                }
            } else {
                $settings = [
                    'cron' => trim(implode(' ', array_values(static::getDefaultCronSettings()))),   // TODO add edit cron job from replication admin panel
                    'isEnabled' => 1,       // TODO add disabled mode
                    'mode' => 'slave',     // master/slave
                    'sshKeyPath' => '/docker-shared/replication-ssh-keys/id_rsa',
                ];
                $this->setSettings($settings);
            }
            $this->settings = $settings;
        }

        if (!$key) {
            return $this->settings;
        } elseif(isset($this->settings[$key])) {
            return $this->settings[$key];
        } else {
            throw new \Exception('Key does\'t exist');
        }
    }

    /**
     * @param array $settings
     * @return bool
     */
    public function setSettings($settings)
    {
        file_sync::touchDir($this->settingsPath);
        $settingsFile = $this->settingsPath . DIRECTORY_SEPARATOR . $this->settingsFile;
        $content = '';
        foreach ($settings as $k => $v) {
            $content .= "$k=$v" . PHP_EOL;
        }
        return false !== file_put_contents($settingsFile, $content);
    }

    /**
     * @return bool
     */
    public function end()
    {
        $this->log('end');
        return !$this->hasError;
    }



    /**
     * @param string $comment
     * @param null $status
     * @param bool $isLogFromLast
     * @return $this
     */
    public function log($comment = '', $status = null, $isLogFromLast = true)
    {
        global $CFG;

        $start = $isLogFromLast && $this->timeLast ? $this->timeLast : $this->timeStart;
        $now = microtime(true);
        $log = [
            'id' => $this->logId,
            'start' => $start,
            'end' => $now,
            'diff' => ceil(($now - $start)*1000) . 'ms',
            'hasError' => (int) $this->hasError,
            'comment' => $comment,
            'status' => (isset($status) ? $status : !$this->hasError) ? 'ok' : 'fail',  // use $status or $this->hasError
        ];

        $message = '';
        foreach ($log as $k => $v) {
            if ($message !== '') {
                $message .= '|||';
            }
            $message .= "[$k=$v]";
        }

        $logFile = $this->settingsPath . DIRECTORY_SEPARATOR . $this->logFile;
        $debugFlagFile = $this->settingsPath . DIRECTORY_SEPARATOR . $this->debugFlagFile;

        // if file $debugFlagFile exists and log operation isn't logged yet (first log) then clear $logFile
        $flag = file_exists($debugFlagFile) && !$this->logIsLogged ? 0 : FILE_APPEND;

        // flag log operation logged already
        $this->logIsLogged = false !== @file_put_contents($logFile, $message.PHP_EOL, $flag);
        @chmod($logFile, $CFG->filepermissions);

        $this->timeLast = $now;

        return $this;
    }

    /**
     * @param string $comment
     * @param null $status
     * @return replicator
     */
    public function logTotal($comment = '', $status = null)
    {
        return $this->log($comment, $status, false);
    }



    /**
     * @return array
     * @use
     * read hosts in format user@host:port:/local_path/to/data
     * return in format [..., ['host' => user@host, 'port' => port, 'path' => '/local_path/to/data'] ]
     * e.g. [['host' => 'academysmart.ml','port' => '1022','user' => 'dev','path' => '/var/docker/projects/dbe2loc/data']]
     */
    public function getHosts()
    {
        $hostsFile = $this->settingsPath . DIRECTORY_SEPARATOR . $this->hostsFile;
        // get hosts
        if (file_exists($hostsFile)) {
            $hostsLines = (array) file($hostsFile, FILE_IGNORE_NEW_LINES & FILE_SKIP_EMPTY_LINES);

            // unpack host data
            $hosts = [];
            foreach ($hostsLines as $line) {
                $h = explode(':', trim($line));
                $userHost = explode('@', $h[0]);
                $hosts[] = [
                    'host' => $userHost[1],
                    'user' => $userHost[0],
                    'port' => $h[1],
                    'path' => $h[2],
                ];
                unset($dest);
            }
        }
        // create initial file
        else {
            $hosts = [];
            $this->setHosts($hosts);
        }

        return $hosts;
    }

    /**
     * @param array $hosts
     * @return bool
     */
    public function setHosts($hosts)
    {
        file_sync::touchDir($this->settingsPath);
        $hostsFile = $this->settingsPath . DIRECTORY_SEPARATOR . $this->hostsFile;
        $content = '';
        foreach ($hosts as $host) {
            $line = '';
            if ($host['user'] && $host['host'] && $host['port'] && $host['path']) {
                $line = trim("{$host['user']}@{$host['host']}:{$host['port']}:{$host['path']}");
            }
            if ($line) {
                $content .= $line . PHP_EOL;
            }
        }
        return false !== file_put_contents($hostsFile, $content);
    }

    /**
     * Update file to/from mirror
     * @return $this
     * @throws \Exception
     */
    public function update()
    {
        // error
        if ($this->hasError) {
            return $this;
        }

        $fileDir = DIRECTORY_SEPARATOR . 'filedir' . DIRECTORY_SEPARATOR;
        $dumpFile = $this->mirrorPath . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'dump.sql';

        // master
        if ($this->getSettings('mode') == 'master') {

            // update files at mirror
            $src = $this->dataPath . $fileDir;
            $dest = $this->mirrorPath . $fileDir;
            $this->hasError = !$this->fileSync->syncLocal($src, $dest, true);
            $this->log('sync files');
            if ($this->hasError) {
                return $this;
            }

            // db export
            $this->hasError = !$this->dbSync->export($dumpFile);
            $this->log('db export');
        }

        // slave
        elseif ($this->getSettings('mode') == 'slave') {

            // update files from mirror
            $src = $this->mirrorPath . $fileDir;
            // if mirror from remote doesn't exists yet - return
            if ($this->hasError = !file_exists($src)) {
                return $this;
            }
            $dest = $this->dataPath . $fileDir;
            $this->hasError = !$this->fileSync->syncLocal($src, $dest);
            $this->log('sync files');
            if ($this->hasError) {
                return $this;
            }

            // db export
            $this->hasError = !$this->dbSync->import($dumpFile);
            $this->log('db import');
        }

        // etc
        else {
            throw new \Exception('Invalid mode');
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function sync()
    {
        if (!$this->hasError) {

            $src = $this->mirrorPath . DIRECTORY_SEPARATOR;
            $sshKeyFile = $this->getSettings('sshKeyPath');
            $excluded = [DIRECTORY_SEPARATOR . $this->settingsFolder];

            $hosts = $this->getHosts();
            foreach ($hosts as $host) {
                $dest = "{$host['user']}@{$host['host']}:{$host['path']}" . DIRECTORY_SEPARATOR . $this->mirrorFolder;
                $this->hasError = !$this->fileSync->syncRemote($src, $dest, $host['port'], $sshKeyFile, $excluded);
                $this->log("sync mirror to {$host['host']}:{$host['port']}");
                if ($this->hasError) {
                    break;
                }
            }
        }

        return $this;
    }

}
