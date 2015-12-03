<?php
namespace Wsbox\Util;

use Symfony\Component\Yaml\Yaml;

/**
 * Wsbox yaml config parser, uses symfony Yaml after parsing our tags
 * the file replaces environment varaible tags with the actual value,
 * we need to do this because in future versions of  Symfony/Yaml it will
 * not support php parsing and reading from file
 *
 */
class YamlParser
{
    /**
     * The function searches for environment variables in config in the format
     * env{VARIABLE_NAME|DEFAULT_VALUE} and replaces them with the values from get_env
     * DEFAULT_VALUE is used when that env variable is not set
     *
     * @param string $fileName file name with absolute path
     *
     * @return array config
     *
     * @throws \RuntimeException
     */
    public static function parse($fileName)
    {
        if (!$fileName || !is_file($fileName) || !is_readable($fileName)) {
            throw new \RuntimeException(sprintf('Unable to parse "%s" as the file is not readable.', $fileName));
        }

        $content = file_get_contents($fileName);
        $pattern = '/env{(.*)(\|(.+))?}/U';
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                // 0 = tag, 1 = env variable name , 3 = default value
                $value = getenv($match[1]);
                if (!$value && isset($match[3])) {
                    $value = $match[3];
                }

                $content = str_replace($match[0], $value, $content);
            }
        }

        return Yaml::parse($content);
    }
}
