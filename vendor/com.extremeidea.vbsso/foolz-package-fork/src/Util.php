<?php

namespace Foolz\Package;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Collection of utilities used in Foolz\Package
 *
 * @author Foolz <support@foolz.us>
 * @package Foolz\Package
 * @license http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 */
class Util
{
    /**
     * Returns the value of a deep associative array by using a dotted notation for the keys
     *
     * @param   array   $config    The config file to fetch the value from
     * @param   string  $section   The dotted keys: akey.anotherkey.key
     * @param   mixed   $fallback  The fallback value
     * @return  mixed
     * @throws  \DomainException  if the fallback is \Foolz\Package\Void
     */
    public static function dottedConfig($config, $section, $fallback)
    {
        // get the section with the dot separated string
        $sections = explode('.', $section);
        $current = $config;

        foreach ($sections as $key) {
            if (isset($current[$key])) {
                $current = $current[$key];
            } else {
                if ($fallback instanceof VoidClassType) {
                    throw new \DomainException;
                }

                return $fallback;
            }
        }

        return $current;
    }

    /**
     * Saves an array to a PHP file with a return statement
     *
     * @param   string  $path   The target path
     * @param   array   $array  The array to save
     */
    public static function saveArrayToFile($path, $array)
    {
        $content = "<?php \n"."return ".var_export($array, true).';';

        file_put_contents($path, $content);
    }

    /**
     * Delete a file/recursively delete a directory
     *
     * NOTE: Be very careful with the path you pass to this!
     *
     * From: http://davidhancock.co/2012/11/useful-php-functions-for-dealing-with-the-file-system/
     *
     * @param string $path The path to the file/directory to delete
     *
     * @return VoidClassType
     */
    public static function delete($path)
    {
        if (is_dir($path)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    rmdir($file->getPathname());
                } else {
                    unlink($file->getPathname());
                }
            }

            rmdir($path);
        } else {
            unlink($path);
        }
    }

    /**
     * Copy a file or recursively copy a directories contents
     *
     * From: http://davidhancock.co/2012/11/useful-php-functions-for-dealing-with-the-file-system/
     *
     * @param string $src The path to the source file/directory
     * @param string $dst The path to the destination directory
     *
     * @return VoidClassType
     */
    public static function copy($src, $dst)
    {
        if (is_dir($src)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    mkdir($dst.DIRECTORY_SEPARATOR.$iterator->getSubPathName());
                } else {
                    copy($file, $dst.DIRECTORY_SEPARATOR.$iterator->getSubPathName());
                }
            }
        } else {
            copy($src, $dst);
        }
    }
}
