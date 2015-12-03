<?php
namespace ApiCreator\Util;

/**
 * Create a directory
 */
class DirectoryCreator
{
    /**
     * Create a directory if it doesn't exist
     *
     * @param string $dirName path to create
     *
     * @return bool
     */
    public static function create($dirName)
    {
        if (!is_dir($dirName)) {
            if (!mkdir($dirName, 0777, true)) {
                return false;
            }
        }

        return true;
    }
}
