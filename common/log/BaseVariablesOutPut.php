<?php
class Log_BaseVariablesOutPut {
    protected $_depth = 10;
    protected $_objects = [];
    protected function dumpInternal($var, $level=10){
        $output = "";
        switch (gettype($var)) {
            case 'boolean':
                $output = $var ? 'true' : 'false';
                break;
            case 'integer':
                $output = "$var";
                break;
            case 'double':
                $output = "$var";
                break;
            case 'string':
                $output = "'" . addslashes($var) . "'";
                break;
            case 'resource':
                $output = '{resource}';
                break;
            case 'NULL':
                $output = 'null';
                break;
            case 'unknown type':
                $output = '{unknown}';
                break;
            case 'array':
                if ($this->_depth <= $level) {
                    $output = '[...]';
                } elseif (empty($var)) {
                    $output = '[]';
                } else {
                    $keys = array_keys($var);
                    $spaces = str_repeat(' ', $level * 4);
                    $output = '[';
                    foreach ($keys as $key) {
                        $output .= "\n" . $spaces . '    ';
                        $output .= $this->dumpInternal($key, 0);
                        $output .= ' => ';
                        $output .= $this->dumpInternal($var[$key], $level + 1);
                    }
                    $output .= "\n" . $spaces . ']';
                }
                break;
            case 'object':
                if (($id = array_search($var, $this->_objects, true)) !== false) {
                    $output = get_class($var) . '#' . ($id + 1) . '(...)';
                } elseif ($this->_depth <= $level) {
                    $output = get_class($var) . '(...)';
                } else {
                    $id = array_push($this->_objects, $var);
                    $className = get_class($var);
                    $spaces = str_repeat(' ', $level * 4);
                    $output = "$className#$id\n" . $spaces . '(';
                    if ('__PHP_Incomplete_Class' !== get_class($var) && method_exists($var, '__debugInfo')) {
                        $dumpValues = $var->__debugInfo();
                        if (!is_array($dumpValues)) {
                            throw new Exception('__debuginfo() must return an array');
                        }
                    } else {
                        $dumpValues = (array) $var;
                    }
                    foreach ($dumpValues as $key => $value) {
                        $keyDisplay = strtr(trim($key), "\0", ':');
                        $output .= "\n" . $spaces . "    [$keyDisplay] => ";
                        $output .= self::dumpInternal($value, $level + 1);
                    }
                    $output .= "\n" . $spaces . ')';
                }
                break;
        }
        return $output;
    }
}