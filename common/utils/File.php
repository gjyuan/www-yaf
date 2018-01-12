<?php
class Utils_File {
    const PATTERN_NODIR = 1;
    const PATTERN_ENDSWITH = 4;
    const PATTERN_MUSTBEDIR = 8;
    const PATTERN_NEGATIVE = 16;
    const PATTERN_CASE_INSENSITIVE = 32;

    /**
     * 规范化路径使用默认的分隔符：DIRECTORY_SEPARATOR
     * @param $path
     * @param string $ds
     * @return string
     */
    public static function normalizePath($path, $ds = DIRECTORY_SEPARATOR)
    {
        $path = rtrim(strtr($path, '/\\', $ds . $ds), $ds);
        if (strpos($ds . $path, "{$ds}.") === false && strpos($path, "{$ds}{$ds}") === false) {
            return $path;
        }
        $parts = [];
        foreach (explode($ds, $path) as $part) {
            if ($part === '..' && !empty($parts) && end($parts) !== '..') {
                array_pop($parts);
            } elseif ($part === '.' || $part === '' && !empty($parts)) {
                continue;
            } else {
                $parts[] = $part;
            }
        }
        $path = implode($ds, $parts);
        return $path === '' ? '.' : $path;
    }

    /**创建文件夹
     * @param $path
     * @param int $mode
     * @param bool $recursive
     * @return bool
     * @throws Exception
     */
    public static function createDirectory($path, $mode = 0775, $recursive = true)
    {
        if (is_dir($path)) {
            return true;
        }
        $parentDir = dirname($path);
        // recurse if parent dir does not exist and we are not at the root of the file system.
        if ($recursive && !is_dir($parentDir) && $parentDir !== $path) {
            static::createDirectory($parentDir, $mode, true);
        }
        try {
            if (!mkdir($path, $mode)) {
                return false;
            }
        } catch (Exception $e) {
            if (!is_dir($path)) {
                throw new Exception("Failed to create directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
            }
        }
        try {
            return chmod($path, $mode);
        } catch (Exception $e) {
            throw new Exception("Failed to change permissions for directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
        }
    }


}
