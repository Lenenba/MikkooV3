<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class Translations
{
    public static function forLocale(string $locale): array
    {
        $messages = [];
        $jsonPath = base_path("lang/{$locale}.json");
        $groupPath = base_path("lang/{$locale}");

        if (File::exists($jsonPath)) {
            $json = json_decode(File::get($jsonPath), true);
            if (is_array($json)) {
                $messages = $json;
            }
        }

        if (File::isDirectory($groupPath)) {
            foreach (File::files($groupPath) as $file) {
                $group = $file->getFilenameWithoutExtension();
                $messages[$group] = Lang::get($group, [], $locale);
            }
        }

        return $messages;
    }
}
