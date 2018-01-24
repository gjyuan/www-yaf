<?php
class Log_Print extends Log_BaseVariablesOutPut {
    protected $logPath;
    protected $logFile;
    protected $messages = [];
    protected $fileMode = 0755;
    protected $dirMode = 0755;
    protected $maxFileSize = 10240; //日志文件最大值 KB
    protected $maxLogFiles = 5;
    protected $enableRotation = true;
    private $_output;


    public function __construct(){
        $logPath = $this->getLogPath();
        if (!is_dir($logPath)) {
            Utils_File::createDirectory($logPath, $this->dirMode, true);
        }
        if ($this->maxLogFiles < 1) {
            $this->maxLogFiles = 1;
        }
        if ($this->maxFileSize < 1) {
            $this->maxFileSize = 1;
        }
        $this->logFile = $this->getLogFile();
    }
    /**
     * 获取系统日志目录的位置 -
     * 默认为当前项目根目录下
     */
    private function getLogPath(){
        if (empty($this->logPath)){
            $this->logPath = Config::getValue('log.path');
            if(empty($this->logPath)){
                $this->logPath = Config::getApplicationPath() . DIRECTORY_SEPARATOR . "logs";
            }else{
                $this->logPath = ROOT_PATH;
            }
        }
        return rtrim($this->logPath,"\\/ ");
    }

    /**根据当前的日志级别和日期获取日志文件的名称
     * @param int $level
     * @param bool $divisionByDay
     * @return string
     */
    private function getLogFile($level = Log_Consts::LEVEL_TRACE, $divisionByDay = true){
        $level = empty($level) ? Log_Consts::LEVEL_TRACE : $level;
        if($divisionByDay){
            return $this->getLogPath() . DIRECTORY_SEPARATOR .APP_NAME . '-' .strtolower(Log_Consts::getLevelName($level)) . '-' . date('Ymd') . ".log";
        }else{
            return $this->getLogPath() . DIRECTORY_SEPARATOR .APP_NAME . '-' .strtolower(Log_Consts::getLevelName($level)) . ".log";
        }
    }

    public function formatMessage($message){
        list($text, $level, $category, $timestamp) = $message;
        $level = Log_Consts::getLevelName($level);
        if (!is_string($text)) {
            if ($text instanceof Throwable || $text instanceof Exception) {
                $text = (string) $text;
            } else {
                $text = $this->formatExportVariable($text,0);
            }
        }
        $traces = [];
        if (isset($message[4])) {
            foreach ($message[4] as $trace) {
                $traces[] = "in {$trace['file']}:{$trace['line']}";
            }
        }

        $prefix = $this->getMessagePrefix($message);
        return date('Y-m-d H:i:s', $timestamp) . " {$prefix}[$level][$category] $text"
            . (empty($traces) ? '' : "\n    " . implode("\n    ", $traces));
    }

    private function formatExportVariable($var, $level){
        $output = "";
        switch (gettype($var)) {
            case 'NULL':
                $output = 'null';
                break;
            case 'array':
                if (empty($var)) {
                    $output = '[]';
                } else {
                    $keys = array_keys($var);
                    $outputKeys = ($keys !== range(0, count($var) - 1));
                    $spaces = str_repeat(' ', $level * 4);
                    $output .= '[';
                    foreach ($keys as $key) {
                        $output .= "\n" . $spaces . '    ';
                        if ($outputKeys) {
                            $output .= $this->formatExportVariable($key, 0);
                            $output .= ' => ';
                        }
                        $output .= $this->formatExportVariable($var[$key], $level + 1);
                        $output .= ',';
                    }
                    $output .= "\n" . $spaces . ']';
                }
                break;
            case 'object':
                if ($var instanceof \Closure) {
                    $output = $this->exportClosure($var);
                } else {
                    try {
                        $output = 'unserialize(' . var_export(serialize($var), true) . ')';
                    } catch (\Exception $e) {
                        if ($var instanceof \IteratorAggregate) {
                            $varAsArray = [];
                            foreach ($var as $key => $value) {
                                $varAsArray[$key] = $value;
                            }
                            $output .= $this->formatExportVariable($varAsArray, $level);
                        } elseif ('__PHP_Incomplete_Class' !== get_class($var) && method_exists($var, '__toString')) {
                            $output = var_export($var->__toString(), true);
                        } else {
                            $output = var_export($this->dumpInternal($var), true);
                        }
                    }
                }
                break;
            default:
                $this->_output .= var_export($var, true);
        }
        return $output;
    }

    /**
     * Exports a [[Closure]] instance.
     * @param \Closure $closure closure instance.
     * @return string
     */
    private function exportClosure(Closure $closure)
    {
        $reflection = new ReflectionFunction($closure);

        $fileName = $reflection->getFileName();
        $start = $reflection->getStartLine();
        $end = $reflection->getEndLine();

        if ($fileName === false || $start === false || $end === false) {
            return 'function() {/* Error: unable to determine Closure source */}';
        }

        --$start;

        $source = implode("\n", array_slice(file($fileName), $start, $end - $start));
        $tokens = token_get_all('<?php ' . $source);
        array_shift($tokens);

        $closureTokens = [];
        $pendingParenthesisCount = 0;
        foreach ($tokens as $token) {
            if (isset($token[0]) && $token[0] === T_FUNCTION) {
                $closureTokens[] = $token[1];
                continue;
            }
            if ($closureTokens !== []) {
                $closureTokens[] = isset($token[1]) ? $token[1] : $token;
                if ($token === '}') {
                    $pendingParenthesisCount--;
                    if ($pendingParenthesisCount === 0) {
                        break;
                    }
                } elseif ($token === '{') {
                    $pendingParenthesisCount++;
                }
            }
        }

        return implode('', $closureTokens);
    }
    /**
     * Writes log messages to a file.
     * @throws InvalidConfigException if unable to open the log file for writing
     */
    private function export(){
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";
        if (($fp = @fopen($this->logFile, 'a')) === false) {
            throw new Exception("Unable to append to log file: {$this->logFile}");
        }
        @flock($fp, LOCK_EX);
        if ($this->enableRotation) {
            clearstatcache();
        }
        if ($this->enableRotation && @filesize($this->logFile) > $this->maxFileSize * 1024) {
            $this->rotateFiles();
            @flock($fp, LOCK_UN);
            @fclose($fp);
            @file_put_contents($this->logFile, $text, FILE_APPEND | LOCK_EX);
        } else {
            @fwrite($fp, $text);
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }
        if ($this->fileMode !== null) {
            @chmod($this->logFile, $this->fileMode);
        }
    }

    /**
     * Rotates log files.
     */
    protected function rotateFiles()
    {
        $file = $this->logFile;
        for ($i = $this->maxLogFiles; $i >= 0; --$i) {
            // $i == 0 is the original log file
            $rotateFile = $file . ($i === 0 ? '' : '.' . $i);
            if (is_file($rotateFile)) {
                // suppress errors because it's possible multiple processes enter into this section
                if ($i === $this->maxLogFiles) {
                    @unlink($rotateFile);
                } else {
                    if ($this->rotateByCopy) {
                        @copy($rotateFile, $file . '.' . ($i + 1));
                        if ($fp = @fopen($rotateFile, 'a')) {
                            @ftruncate($fp, 0);
                            @fclose($fp);
                        }
                        if ($this->fileMode !== null) {
                            @chmod($file . '.' . ($i + 1), $this->fileMode);
                        }
                    } else {
                        @rename($rotateFile, $file . '.' . ($i + 1));
                    }
                }
            }
        }
    }






    public function getMessagePrefix($message)
    {
        if ($this->prefix !== null) {
            return call_user_func($this->prefix, $message);
        }

        if (Yii::$app === null) {
            return '';
        }

        $request = Yii::$app->getRequest();
        $ip = $request instanceof Request ? $request->getUserIP() : '-';

        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        if ($user && ($identity = $user->getIdentity(false))) {
            $userID = $identity->getId();
        } else {
            $userID = '-';
        }
        $session = Yii::$app->has('session', true) ? Yii::$app->get('session') : null;
        $sessionID = $session && $session->getIsActive() ? $session->getId() : '-';

        return "[$ip][$userID][$sessionID]";
    }
    private static function exportInternal($var, $level)
    {
        switch (gettype($var)) {
            case 'NULL':
                self::$_output .= 'null';
                break;
            case 'array':
                if (empty($var)) {
                    self::$_output .= '[]';
                } else {
                    $keys = array_keys($var);
                    $outputKeys = ($keys !== range(0, count($var) - 1));
                    $spaces = str_repeat(' ', $level * 4);
                    self::$_output .= '[';
                    foreach ($keys as $key) {
                        self::$_output .= "\n" . $spaces . '    ';
                        if ($outputKeys) {
                            self::exportInternal($key, 0);
                            self::$_output .= ' => ';
                        }
                        self::exportInternal($var[$key], $level + 1);
                        self::$_output .= ',';
                    }
                    self::$_output .= "\n" . $spaces . ']';
                }
                break;
            case 'object':
                if ($var instanceof Closure) {
                    self::$_output .= self::exportClosure($var);
                } else {
                    try {
                        $output = 'unserialize(' . var_export(serialize($var), true) . ')';
                    } catch (\Exception $e) {
                        // serialize may fail, for example: if object contains a `\Closure` instance
                        // so we use a fallback
                        if ($var instanceof Arrayable) {
                            self::exportInternal($var->toArray(), $level);
                            return;
                        } elseif ($var instanceof \IteratorAggregate) {
                            $varAsArray = [];
                            foreach ($var as $key => $value) {
                                $varAsArray[$key] = $value;
                            }
                            self::exportInternal($varAsArray, $level);
                            return;
                        } elseif ('__PHP_Incomplete_Class' !== get_class($var) && method_exists($var, '__toString')) {
                            $output = var_export($var->__toString(), true);
                        } else {
                            $outputBackup = self::$_output;
                            $output = var_export(self::dumpAsString($var), true);
                            self::$_output = $outputBackup;
                        }
                    }
                    self::$_output .= $output;
                }
                break;
            default:
                self::$_output .= var_export($var, true);
        }
    }



//
//    public function export() {
//        $messageArray = $this->messages;
//        foreach($messageArray as $message){
//            $textArr =  array_map([$this, 'formatMessage'], array($message));
//            $text = self::$sequence.' '.$textArr[0];
//            $levelId = $message[1];
//            $level = Log_Consts::getLevelName($levelId);
//            $fileName = $this->logPath.$level . '-log-' .date('Ymd');
//            $this->logFile = $fileName;
//            if (($fp = @fopen($fileName, 'a')) === false) {
//                throw new InvalidConfigException("Unable to append to log file: {$fileName}");
//            }
//            @flock($fp, LOCK_EX);
//            if ($this->enableRotation) {
//                // clear stat cache to ensure getting the real current file size and not a cached one
//                // this may result in rotating twice when cached file size is used on subsequent calls
//                clearstatcache();
//            }
//            if ($this->enableRotation && @filesize($fileName) > $this->maxFileSize * 1024) {
//                $this->rotateFiles();
//                @flock($fp, LOCK_UN);
//                @fclose($fp);
//                @file_put_contents($fileName, $text, FILE_APPEND | LOCK_EX);
//            } else {
//                @fwrite($fp, $text);
//                @flock($fp, LOCK_UN);
//                @fclose($fp);
//            }
//            if ($this->fileMode !== null) {
//                @chmod($fileName, $this->fileMode);
//            }
//        }
//    }






























    protected $intLevel;
    protected $strLogFile;
    protected $bolAutoRotate;
    protected $addNotice = array();

    private $_maxLineLength = 4096;

    private static $strLogPath  = null;
    private static $strDataPath = null;
    private static $arrInstance = array();
    private static $logWriters=array(); // 多源打印日志

    public static $current_instance;
    const DEFAULT_FORMAT = "%L: %t [%f:%N] errno[%E] logId[%l] uri[%U] user[%u] refer[%{referer}i] cookie[%{cookie}i] %S %M";
    const DEFAULT_FORMAT_STD = "%L: %{%m-%d %H:%M:%S}t %{app}x * %{pid}x [logid=%l filename=%f lineno=%N errno=%{err_no}x %{encoded_str_array}x errmsg=%{u_err_msg}x]";
    const DEFAULT_FORMAT_STD_DETAIL = "%L: %{%m-%d %H:%M:%S}t %{app}x * %{pid}x [logid=%l filename=%f lineno=%N errno=%{err_no}x %{encoded_str_array}x errmsg=%{u_err_msg}x cookie=%{u_cookie}x]";
    const DEFAULT_ELK_HTTP_FORMAT = "%t|%l|%h|%M";

    /**
     * @brief 日志的前缀为AppName，
     *
     * @return  public static function
     * @retval
     * @see
     * @note
     * @author
     * @date
    **/
    public static function getLogPrefix(){
        return Lj_AppEnv::getCurrApp();
    }
    // 获取指定App的log对象，默认为当前App
    /**
     *
     * @return Lj_Log
     * */
    public static function getInstance($app = null){
        if(empty($app))
        {
            $app = self::getLogPrefix();
        }

        if(empty(self::$arrInstance[$app]))
        {
            $g_log_conf = Lj_Conf::get("log");
            // 生成路径
            $logPath = self::getLogPath();

            if($g_log_conf["use_sub_dir"] == "1")
            {
                if(!is_dir($logPath."/$app"))
                {
                    @mkdir($logPath."/$app");
                }
                $log_file = $logPath."/$app/$app.log";
            }
            else
            {
                $log_file = $logPath."/$app.log";
            }

            //get log format
            if (isset($g_log_conf["format"])) {
                $format = $g_log_conf["format"];
            } else {
                $format = self::DEFAULT_FORMAT;
            }
            if (isset($g_log_conf["format_wf"])) {
                $format_wf = $g_log_conf["format_wf"];
            } else {
                $format_wf = $format;
            }

            $log_conf = array(
                "level"         => intval($g_log_conf['level']) ? intval($g_log_conf['level']) : self::LOG_LEVEL_NOTICE,
                "auto_rotate"   => ($g_log_conf["auto_rotate"] == "1"),
                "log_file"      => $log_file,
                "format"        => $format,
                "format_wf"     => $format_wf,
            );

            self::$arrInstance[$app] = new Lj_Log($log_conf);
        }
        return self::$arrInstance[$app];
    }
    public function get_str_args() {
        $strArgs = '';
        empty($this->current_args) || $strArgs = json_encode($this->current_args,JSON_UNESCAPED_UNICODE);
        return $strArgs;
    }
    // 生成logid
    public static function genLogID(){
        if(defined("LOG_ID")){
            return LOG_ID;
        }
        // 优先从post中获取 不用$_REQUEST是因为他慢
        if(isset($_SERVER["HTTP_X_LJ_LOGID"]) && intval(trim($_SERVER["HTTP_X_LJ_LOGID"])) !== 0){
            define("LOG_ID", trim($_SERVER["HTTP_X_LJ_LOGID"]));
        }elseif(isset($_POST["request_id"]) && intval($_POST["request_id"]) !== 0){
            define("LOG_ID", intval($_POST["request_id"]));
        }elseif(isset($_GET["request_id"]) && intval($_GET["request_id"]) !== 0){
            define("LOG_ID", intval($_GET["request_id"]));
        }elseif(isset($_POST["logid"]) && intval($_POST["logid"]) !== 0){
            define("LOG_ID", intval($_POST["logid"]));
        }elseif(isset($_GET["logid"]) && intval($_GET["logid"]) !== 0){
            define("LOG_ID", intval($_GET["logid"]));
        }else{
            $arr = gettimeofday();
            $logId = ((($arr["sec"]*100000 + $arr["usec"]/10) & 0x7FFFFFFF) | 0x80000000);
            define("LOG_ID", $logId);
        }
        return LOG_ID;
    }
    // 生成logid
    public static function genRequestId(){
        if(defined("REQUEST_ID")){
            return REQUEST_ID;
        }
        // 优先从post中获取 不用$_REQUEST是因为他慢
        if(isset($_SERVER["HTTP_UNIQID"]) && intval(trim($_SERVER["HTTP_UNIQID"])) !== 0){
            define("REQUEST_ID", trim($_SERVER["HTTP_UNIQID"]));
        }else{
            define("REQUEST_ID", self::genLogID());
        }
        return REQUEST_ID;
    }
    private function writeLog($intLevel, $str, $errno = 0, $arrArgs = null, $depth = 0, $filename_suffix = "", $log_format = null)
    {
        if( $intLevel > $this->intLevel || !isset(self::$arrLogLevels[$intLevel]) )
        {
            return;
        }

        //log file name
        $strLogFile = $this->strLogFile;
        if( ($intLevel & self::LOG_LEVEL_WARNING) || ($intLevel & self::LOG_LEVEL_FATAL) )
        {
            $strLogFile .= ".wf";
        }

        $strLogFile .= $filename_suffix;

        //assign data required
        $this->current_log_level = self::$arrLogLevels[$intLevel];

        //build array for use as strargs
        $_arr_args = false;
        $_add_notice = false;
        if (is_array($arrArgs) && count($arrArgs) > 0) {
            $_arr_args = true;
        }
        if (!empty($this->addNotice)) {
            $_add_notice = true;
        }

        if ($_arr_args && $_add_notice) { //both are defined, merge
            $this->current_args = $arrArgs + $this->addNotice;
        } else if (!$_arr_args && $_add_notice) { //only add notice
            $this->current_args = $this->addNotice;
        } else if ($_arr_args && !$_add_notice) { //only arr args
            $this->current_args = $arrArgs;
        } else { //empty
            $this->current_args = array();
        }

        $this->current_err_no = $errno;
        $this->current_err_msg = $str;
        $trace = debug_backtrace();
        $depth2 = $depth + 1;
        if( $depth >= count($trace) )
        {
            $depth = count($trace) - 1;
            $depth2 = $depth;
        }
        $this->current_file = isset( $trace[$depth]["file"] ) ? basename($trace[$depth]["file"]) : "" ;
        $this->current_line = isset( $trace[$depth]["line"] ) ? $trace[$depth]["line"] : "";
        $this->current_function = isset( $trace[$depth2]["function"] ) ? $trace[$depth2]["function"] : "";
        $this->current_class = isset( $trace[$depth2]["class"] ) ? $trace[$depth2]["class"] : "" ;
        $this->current_function_param = isset( $trace[$depth2]["args"] ) ? $trace[$depth2]["args"] : "";

        self::$current_instance = $this;

        //get the format
        if ($log_format == null)
            $format = $this->getFormat($intLevel);
        else
            $format = $log_format;
        $str = $this->getLogString($format);

        if (strlen($str) > $this->_maxLineLength) {
            $tail = sprintf(" ... (%dB)\n", strlen($str));
            $str = substr($str, 0, $this->_maxLineLength - strlen($tail)) . $tail;
        }
        if($this->bolAutoRotate)
        {
            $strLogFile .= ".".date("Ymd");
        }
        foreach (self::$logWriters as $writer){
            $writer->write($str);
        }
        return file_put_contents($strLogFile, $str, FILE_APPEND);
    }
    // added support for self define format
    private function getFormat($level) {
        if ($level == self::LOG_LEVEL_FATAL || $level == self::LOG_LEVEL_WARNING) {
            $fmtstr = $this->strFormatWF;
        } else {
            $fmtstr = $this->strFormat;
        }
        return $fmtstr;
    }
    public function getLogString($format) {
        $md5val = md5($format);
        $func = "_lj_log_$md5val";
        if (function_exists($func)) {
            return $func();
        }
        $dataPath = self::getDataPath();
        $dataPath = rtrim($dataPath, "/");
        $filename = $dataPath . '/log/'.$md5val.'.php';
        if (!file_exists($filename)) {
            if(function_exists('posix_getpid')){
                $tmp_filename = $filename . '.' . posix_getpid() . '.' . rand();
            }else{
                $tmp_filename = $filename . '.' . get_current_user() . '.' . rand();
            }
            if (!is_dir($dataPath . '/log')) {
                @mkdir($dataPath . '/log');
            }
            file_put_contents($tmp_filename, $this->parseFormat($format));
            rename($tmp_filename, $filename);
        }
        include_once($filename);
        $str = $func();

        return $str;
    }
    /**
     * 注册一个新的logWriter
     * @param Bd_Log_Writer $writer
     */
    private static function registerWriter(Lj_Log_Writer $writer){
      self::$logWriters[] = $writer;
    }

    public static function debug($str, $errno = 0, $arrArgs = null, $depth = 0)
    {
        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_DEBUG, $str, $errno, $arrArgs, $depth + 1);
    }

    public static function trace($str, $errno = 0, $arrArgs = null, $depth = 0)
    {
        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_TRACE, $str, $errno, $arrArgs, $depth + 1);
    }

    public static function notice($str, $errno = 0, $arrArgs = null, $depth = 0)
    {
        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_NOTICE, $str, $errno, $arrArgs, $depth + 1);
    }

    public static function warning($str, $errno = 0, $arrArgs = null, $depth = 0)
    {
        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_WARNING, $str, $errno, $arrArgs, $depth + 1);

    }

    public static function fatal($str, $errno = 0, $arrArgs = null, $depth = 0)
    {
        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_FATAL, $str, $errno, $arrArgs, $depth + 1);
    }
    public static function elkHttp($message, $httpInfo, $arrArgs = null, $depth = 0)
    {
        if (!is_array($httpInfo)) {
            return;
        }
        if (!is_array($message)) {
            $message = array($message);
        }
        $meta = array(
            isset($httpInfo["request_method"]) ? $httpInfo["request_method"] : "",
            isset($httpInfo["url"]) ? $httpInfo["url"] : "",
            isset($httpInfo["request_body"]) ? $httpInfo["request_body"] : "",
            isset($httpInfo["http_code"]) ? $httpInfo["http_code"] : "",
            isset($httpInfo["total_time"]) ? $httpInfo["total_time"] : "",
            isset($httpInfo["size_download"]) ? $httpInfo["size_download"] : "",
            json_encode($message),
        );
        foreach ($meta as $key => $value) {
            $meta[$key] = strtr($value, array(
                "|" => "\|",
                "\n" => "\\n",
                "\r" => "\\r",
                "\t" => "\\t",
            ));
        }

        $str = join("|", $meta);
        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_NOTICE, $str, $errno = 0, $arrArgs, $depth + 1, $filename_suffix = ".elk_http", $log_format = self::DEFAULT_ELK_HTTP_FORMAT);
    }

    /**
     * @brief 日志库依赖的数据文件根目录
     *
     * @return  public static function
     * @retval
     * @see
     * @note
     * @author
     * @date 2012/07/31 17:16:30
    **/
    public static function getDataPath(){
        if (self::$strDataPath == null){
            self::$strDataPath = Lj_Conf::get("log.data_path");
        }
        return self::$strDataPath;
    }// 获取客户端ip
    public static function getClientIp()
    {
        $uip = '';
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], 'unknown')) {
            $uip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            strpos($uip, ',') && list($uip) = explode(',', $uip);
        } else if(isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] && strcasecmp($_SERVER['HTTP_CLIENT_IP'], 'unknown')) {
            $uip = $_SERVER['HTTP_CLIENT_IP'];
        } else if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $uip = $_SERVER['REMOTE_ADDR'];
        }
        return $uip;
    }
    //helper functions for use in generated code
    public static function flattenArgs($args) {
        if (!is_array($args)) return '';
        $str = array();
        foreach($args as $a) {
            $str[] = preg_replace('/[ \n\t]+/', " ", $a);
        }
        return implode(', ', $str);
    }
    // parse format and generate code
    public function parseFormat($format) {
        $matches = array();
        $regex = '/%(?:{([^}]*)})?(.)/';
        preg_match_all($regex, $format, $matches);
        $prelim = array();
        $action = array();
        $prelim_done = array();

        $len = count($matches[0]);
        for($i = 0; $i < $len; $i++) {
            $code = $matches[2][$i];
            $param = $matches[1][$i];
            switch($code) {
            case 'h':
                $action[] = "(defined('CLIENT_IP')? CLIENT_IP : Lj_Log::getClientIp())";
                break;
            case 't':
                $action[] = ($param == '')? "strftime('%Y-%m-%d %H:%M:%S')" : "strftime(" . var_export($param, true) . ")";
                break;
            case 'i':
                $key = 'HTTP_' . str_replace('-', '_', strtoupper($param));
                $key = var_export($key, true);
                $action[] = "(isset(\$_SERVER[$key])? \$_SERVER[$key] : '')";
                break;
            case 'a':
                $action[] = "(defined('CLIENT_IP')? CLIENT_IP : Lj_Log::getClientIp())";
                break;
            case 'A':
                $action[] = "(isset(\$_SERVER['SERVER_ADDR'])? \$_SERVER['SERVER_ADDR'] : '')";
                break;
            case 'c':
                $action[] = 'Lj_CallId::getCallId()';
                break;
            case 'C':
                if ($param == '') {
                    $action[] = "(isset(\$_SERVER['HTTP_COOKIE'])? \$_SERVER['HTTP_COOKIE'] : '')";
                } else {
                    $param = var_export($param, true);
                    $action[] = "(isset(\$_COOKIE[$param])? \$_COOKIE[$param] : '')";
                }
                break;
            case 'D':
                $action[] = "(defined('REQUEST_TIME_US')? (microtime(true) * 1000 - REQUEST_TIME_US/1000) : '')";
                break;
            case 'e':
                $param = var_export($param, true);
                $action[] = "((getenv($param) !== false)? getenv($param) : '')";
                break;
            case 'f':
                $action[] = 'Lj_Log::$current_instance->current_file';
                break;
            case 'H':
                $action[] = "(isset(\$_SERVER['SERVER_PROTOCOL'])? \$_SERVER['SERVER_PROTOCOL'] : '')";
                break;
            case 'm':
                $action[] = "(isset(\$_SERVER['REQUEST_METHOD'])? \$_SERVER['REQUEST_METHOD'] : '')";
                break;
            case 'p':
                $action[] = "(isset(\$_SERVER['SERVER_PORT'])? \$_SERVER['SERVER_PORT'] : '')";
                break;
            case 'q':
                $action[] = "(isset(\$_SERVER['QUERY_STRING'])? \$_SERVER['QUERY_STRING'] : '')";
                break;
            case 'T':
                switch($param) {
                case 'ms':
                    $action[] = "(defined('REQUEST_TIME_US')? (microtime(true) * 1000 - REQUEST_TIME_US/1000) : '')";
                    break;
                case 'us':
                    $action[] = "(defined('REQUEST_TIME_US')? (microtime(true) * 1000000 - REQUEST_TIME_US) : '')";
                    break;
                default:
                    $action[] = "(defined('REQUEST_TIME_US')? (microtime(true) - REQUEST_TIME_US/1000000) : '')";
                }
                break;
            case 'U':
                $action[] = "(isset(\$_SERVER['REQUEST_URI'])? \$_SERVER['REQUEST_URI'] : '')";
                break;
            case 'v':
                $action[] = "(isset(\$_SERVER['HOSTNAME'])? \$_SERVER['HOSTNAME'] : '')";
                break;
            case 'V':
                $action[] = "(isset(\$_SERVER['HTTP_HOST'])? \$_SERVER['HTTP_HOST'] : '')";
                break;

            case 'L':
                $action[] = 'Lj_Log::$current_instance->current_log_level';
                break;
            case 'N':
                $action[] = 'Lj_Log::$current_instance->current_line';
                break;
            case 'E':
                $action[] = 'Lj_Log::$current_instance->current_err_no';
                break;
            case 'l':
                $action[] = "Lj_Log::genRequestId()";
                break;
            case 'u':
                // if (!isset($prelim_done['user'])) {
                //     $prelim[] = '$____user____ = Bd_Passport::getUserInfoFromCookie();';
                //     $prelim_done['user'] = true;
                // }
                // $action[] = "((defined('CLIENT_IP') ? CLIENT_IP: Lj_Log::getClientIp()) . ' ' . \$____user____['uid'] . ' ' . \$____user____['uname'])";
                break;
            case 'S':
                if ($param == '') {
                    $action[] = 'Lj_Log::$current_instance->get_str_args()';
                } else {
                    $param_name = var_export($param, true);
                    if (!isset($prelim_done['S_'.$param_name])) {
                        $prelim[] =
                            "if (isset(Lj_Log::\$current_instance->current_args[$param_name])) {
                            \$____curargs____[$param_name] = Lj_Log::\$current_instance->current_args[$param_name];
                            unset(Lj_Log::\$current_instance->current_args[$param_name]);
                        } else \$____curargs____[$param_name] = '';";
                        $prelim_done['S_'.$param_name] = true;
                    }
                    $action[] = "\$____curargs____[$param_name]";
                }
                break;
            case 'M':
                $action[] = 'Lj_Log::$current_instance->current_err_msg';
                break;
            case 'x':
                $need_urlencode = false;
                if (substr($param, 0, 2) == 'u_') {
                    $need_urlencode = true;
                    $param = substr($param, 2);
                }
                switch($param) {
                case 'log_level':
                case 'line':
                case 'class':
                case 'function':
                case 'err_no':
                case 'err_msg':
                    $action[] = 'Lj_Log::$current_instance->current_'.$param;
                    break;
                case 'log_id':
                    $action[] = "Lj_Log::genLogID()";
                    break;
                case 'app':
                    $action[] = "Lj_Log::getLogPrefix()";
                    break;
                case 'function_param':
                    $action[] = 'Lj_Log::flattenArgs(Lj_Log::$current_instance->current_function_param)';
                    break;
                case 'argv':
                    $action[] = '(isset($GLOBALS["argv"])? Lj_Log::flattenArgs($GLOBALS["argv"]) : \'\')';
                    break;
                case 'pid':
                    $action[] = 'posix_getpid()';
                    break;
                case 'cookie':
                    $action[] = "(isset(\$_SERVER['HTTP_COOKIE'])? \$_SERVER['HTTP_COOKIE'] : '')";
                    break;
                default:
                    $action[] = "''";
                }
                if ($need_urlencode) {
                    $action_len = count($action);
                    $action[$action_len-1] = 'rawurlencode(' . $action[$action_len-1] . ')';
                }
                break;
            case '%':
                $action[] =  "'%'";
                break;
            default:
                $action[] = "''";
            }
        }

        $strformat = preg_split($regex, $format);
        $code = var_export($strformat[0], true);
        for($i = 1; $i < count($strformat); $i++) {
            $code = $code . ' . ' . $action[$i-1] . ' . ' . var_export($strformat[$i], true);
        }
        $code .=  ' . "\n"';
        $pre = implode("\n", $prelim);

        $cmt = "Used for app " . self::getLogPrefix() . "\n";
        $cmt .= "Original format string: " . str_replace('*/', '* /', $format);

        $md5val = md5($format);
        $func = "_lj_log_$md5val";
        $str = "<?php \n/*\n$cmt\n*/\nfunction $func() {\n$pre\nreturn $code;\n}";
        return $str;
    }
}

