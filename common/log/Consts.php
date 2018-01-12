<?php
class Log_Consts{
    const LEVEL_FATAL   = 0x01;
    const LEVEL_WARNING = 0x02;
    const LEVEL_NOTICE  = 0x04;
    const LEVEL_TRACE   = 0x08;
    const LEVEL_DEBUG   = 0x10;

    private static $logLevelMap = [
        self::LEVEL_FATAL => 'FATAL',
        self::LEVEL_WARNING => 'WARNING',
        self::LEVEL_NOTICE => 'NOTICE',
        self::LEVEL_TRACE => 'TRACE',
        self::LEVEL_DEBUG => 'DEBUG',
    ];

    public static function getLevelName($level){
        return isset(self::$logLevelMap[$level]) ? self::$logLevelMap[$level] : 'UNKNOWN';
    }

}