<?php

class hook_generic
{
    protected static $handler = null;
    private $hook = array();

    /**
     * This function creates or returns an instance of this class.
     *
     * @return object The Handler object
     */
    private static function getHandler()
    {
        if (self::$handler == null) {
            self::$handler = new self();
        }
        return self::$handler;
    }

    public function add($object = null, $function = null, $parameters = null) {
        $handler = self::getHandler();
        if (null != $object && is_object($object) && null != $function) {
            if (is_array($parameters)) {
                $handler->hook[$object] = $parameters;
            } elseif (null != $parameters) {
                error_log("Tried to add a hook of " . get_class($object)) . " using function name $function but with parameters which were not an array. Got " . print_r($parameters, true));
            }
            $handler->hook[$object]['function'] = $function;
        } else {
            return false;
        }
    }

    public function trigger() {
        $handler = self::getHandler();

        foreach ($handler->hook as $object => $parameters) {
            $function = $parameters['function'];
            try {
                $object->$function($parameters);
            } catch (Exception $e) {
                error_log("While trying to execute the hook: " . get_class($object) . "::$function with parameters: " . print_r($parameters, true) . " we got an exception " . $e);
            }

        }
    }
}