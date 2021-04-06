<?php

/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Multilingual configuration
 */
if (env('APP_DEBUG', true)) {
    $locale = 'en_US';
} else {
    $locale = 'pt_BR';
}
return [
    // Default language
    'locale' => $locale,
    // Fallback language
    'fallback_locale' => ['en_US'],
    // The folder where the language files are stored
    'path' => base_path() . '/resource/translations',
];
