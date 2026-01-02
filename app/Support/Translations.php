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

        return self::convertPlaceholders($messages);
    }

    /**
     * Convert Laravel-style placeholders (:name) to Vue i18n placeholders ({name}).
     *
     * @param mixed $value
     * @return mixed
     */
    private static function convertPlaceholders(mixed $value): mixed
    {
        if (is_array($value)) {
            $converted = [];
            foreach ($value as $key => $item) {
                $converted[$key] = self::convertPlaceholders($item);
            }
            return $converted;
        }

        if (! is_string($value)) {
            return $value;
        }

        return preg_replace('/(^|[^A-Za-z0-9_]):([A-Za-z0-9_]+)/', '$1{$2}', $value);
    }
}
