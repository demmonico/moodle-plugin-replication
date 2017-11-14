<?php
namespace tool_replication\maintenance;

use bootstrap_renderer;
use core_shutdown_manager;
use tool_replication\repo\configure_trait;

defined('MOODLE_INTERNAL') || die();

/**
 * Maintenance dummy class.
 *
 * @package    tool_replication
 * @copyright  demmonico
 */
class dummy
{
    use configure_trait;

    /**
     * @var string
     */
    protected $layout;
    /**
     * @var string
     */
    protected $maintenanceFile;

    /**
     * @var string
     * @use if expression {$datetime} exists then it will be replaced with DateTime string in format $this->dateTimeFormat
     */
    protected $message = 'Maintenance in progress now, please wait until it completes. It started at {$datetime}';
    /**
     * @var string
     */
    protected $dateTimeFormat = 'H:m m/d/Y';

    /**
     * @var string
     */
    protected $enabledAt;
    /**
     * @var string
     */
    protected $flagName;



    /**
     * dummy constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        global $CFG;

        // config
        $this->selfConfigure($config);

        // set flag name at $CFG array
        $this->flagName = strtr(get_called_class(), ['\\' => '_']);
        // set maintenance filename
        $this->maintenanceFile = "$CFG->dataroot/climaintenance.html";
    }

    /**
     * Enable maintenance mode
     */
    public function enable()
    {
        global $CFG;

        // mark enabled datetime
        $this->enabledAt = date($this->dateTimeFormat);

        // set flag
        $CFG->{$this->flagName} = true;

        // define callback
        core_shutdown_manager::register_function([$this, 'disable']);

        // render maintenance file
        $this->createMaintenanceFile();
    }

    /**
     * Enable maintenance mode
     */
    public function disable()
    {
        global $CFG;
        if (isset($CFG->{$this->flagName}) && $CFG->{$this->flagName}) {
            // remove maintenance file
            if (file_exists($this->maintenanceFile)) {
                if (!unlink($this->maintenanceFile)) {
                    error_log('Error maintenance_dummy: Interrupted maintenance file deleting.');
                    return;
                }
            }
            // unset maintenance flag
            $CFG->{$this->flagName} = false;
        }
    }


    /**
     * Create CLI maintenance file to prevent all access.
     */
    protected function createMaintenanceFile()
    {
        global $CFG;

        $options = new \stdClass();
        $options->trusted = false;
        $options->noclean = false;
        $options->smiley = false;
        $options->filter = false;
        $options->para = true;
        $options->newlines = false;

        $message = format_text($this->getMessage(), FORMAT_MARKDOWN, $options);
        $message = bootstrap_renderer::early_error_content($message, '', '', array());
        $html = $this->renderContent(['content' => $message]);

        file_put_contents($this->maintenanceFile, $html);
        @chmod($this->maintenanceFile, $CFG->filepermissions);
    }

    /**
     * @param $data
     * @return string
     */
    protected function renderContent($data)
    {
        // we use special variable names here to avoid conflict when extracting data
        if(is_array($data)) {
            $_data_ = $data;
            unset($data);
            extract($_data_, EXTR_PREFIX_SAME, 'data');
        }

        // render layout
        $layout = $this->layout ?: (dirname(__FILE__).DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.'layout');
        ob_start();
        ob_implicit_flush(false);
        require("$layout.php");
        return ob_get_clean();
    }

    /**
     * @return string
     */
    protected function getMessage()
    {
        // replace datetime var
        return strtr($this->message, ['{$datetime}' => $this->enabledAt ?: '<Oops!>']);
    }
}

