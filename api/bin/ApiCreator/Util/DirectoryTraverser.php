<?php
namespace ApiCreator\Util;

/**
 * Traverse a directory to find files matching a regex.
 */
class DirectoryTraverser
{
    /**
     * Find files that match a regex, recursively.
     *
     * @param string $dir     directory to traverse
     * @param string $regex filename pattern to match
     *
     * @return array|false
     * - array of filenames that match the regex
     * - false on failure
     */
    public static function findFiles($dir, $regex)
    {
        $files = array();

        $dh = opendir($dir);
        if (!$dh) {
            return $files;
        }

        while (($entry = readdir($dh)) !== false) {
            if (in_array($entry, array('.', '..'))) {
                continue;
            }

            if (is_dir($dir . '/' . $entry)) {
                $files = array_merge($files, self::findFiles($dir . '/' . $entry, $regex));
            }

            if (preg_match($regex, $entry)) {
                $files[] = $dir . '/' . $entry;
            }
        }

        return $files;
    }
}
