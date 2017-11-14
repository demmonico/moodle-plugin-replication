<?php
namespace tool_replication\repo;

defined('MOODLE_INTERNAL') || die();

/**
 * Trait helps to configure class by construct config.
 *
 * @package    tool_replication
 * @copyright  demmonico
 */
trait configure_trait
{
    /**
     * @param array $config
     */
    protected function selfConfigure($config)
    {
        if (is_array($config) && $config) {
            $className = get_called_class();
            foreach ($config as $k => $v) {
                if (property_exists($className, $k)) {
                    $this->$k = $v;
                }
            }
        }
    }
}
