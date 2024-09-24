<?php

namespace FM\TinyMCEBundle\Composer;

use Composer\Script\Event;

class TinyMCEScriptHandler
{
    public static function install(Event $event)
    {
        $options = self::getOptions($event);

        $webDir = $options['symfony-web-dir'];
        if (!is_dir($webDir)) {
            echo 'The web directory "' . $webDir . '" does not exist, skipping installation' . PHP_EOL;
            return;
        }

        $tinymceDir = $webDir . '/bundles/fmtinymce/tinymce';
        if (!is_dir($tinymceDir)) {
            mkdir($tinymceDir, 0777, true);
        }

        // Example code to copy TinyMCE files
        copy('path_to_tinymce', $tinymceDir . '/tinymce.min.js');
    }

    protected static function getOptions(Event $event)
    {
        $options = array_merge([
            'symfony-web-dir' => 'public',
        ], $event->getComposer()->getPackage()->getExtra());

        return $options;
    }
}
