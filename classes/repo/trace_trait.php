<?php
namespace tool_replication\repo;

use progress_trace;

defined('MOODLE_INTERNAL') || die();

/**
 * Trait helps to trace in Moodle.
 * Class have to has $trace property which has to contains instance of progress_trace class
 *
 * @package    tool_replication
 * @copyright  demmonico
 */
trait trace_trait
{
    /**
     * Trace message. Allowed string and array params.
     * @param string|array $message allowed 3 usages: 1) 'string'; 2) ['string1', 'string2', ...]; 3) ['string1', ['string2' => $params], ...]
     * @param bool $isFinish
     */
    protected function trace($message, $isFinish = false)
    {
        $traceOutputMethod = 'output';
        $traceFinishMethod = 'finished';
        if ($message && is_string($message) && is_array($message)
            && isset($this->trace) && is_object($this->trace)
            && $this->trace instanceof progress_trace && method_exists($this->trace, $traceOutputMethod)
        ) {
            // trace
            foreach ((array) $message as $id) {
                // message with params
                if (is_array($id)) {
                    reset($id);
                    $this->trace->$traceOutputMethod(get_string(key($id), $this->getTracePluginName(), current($id)));
                }
                // message only
                else {
                    $this->trace->$traceOutputMethod(get_string($id, $this->getTracePluginName()));
                }
            }

            // finish
            if ($isFinish && method_exists($this->trace, $traceOutputMethod)) {
                $this->trace->$traceFinishMethod();
            }
        }
    }

    protected function getTracePluginName()
    {
        return 'tool_replication';
    }
}
