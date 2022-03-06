<?php

namespace App\Helpers\Routes;

class RouteHelper
{
    public static function includeRouteFiles(string $folder){

        $directoryIterator = new \RecursiveDirectoryIterator($folder);

        /**
         * @var \RecursiveDirectoryIterator | \RecursiveIteratorIterator $iterator
         */
        $iterator = new \RecursiveIteratorIterator($directoryIterator);

        /**
         * Lets go over the contents of our iterator instance to list the files
         * that are located in ".../api/v1/". Learn more from this playlist:
         *
         * https://www.youtube.com/watch?v=M2OYIsHqaRU&list=PLSfH3ojgWsQosqpQUc28yP9jJZXrEylJY&index=14
         */
        while($iterator->valid()) {

            /**
             *  Check if we are pointing to a file listed in the directory
             *  Check if this is a file and not a directory
             *  Check if file is a PHP file
             *  Check if file is readable
             */
            if( !$iterator->isDot() && $iterator->isFile() && $iterator->isReadable() && $iterator->current()->getExtension() === 'php'){

                /**
                 *  Require each valid file listed in the directory e.g
                 *
                 *  require "full-path/routes/api/v1/users.php
                 *  require "full-path/routes/api/v1/stores.php
                 *  e.t.c
                 */
                require $iterator->current()->getPathname();

            }

            //  Iterate to the next item on the list
            $iterator->next();

        }

    }

}
