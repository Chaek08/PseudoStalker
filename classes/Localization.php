<?php

namespace app\forms\classes;

class Localization {
    private $translations = [];
    private $language;
    private $directory;

    private $languageMap = [
        'Русский' => 'rus',
        'English' => 'eng'
    ];

    public function __construct($language, $directory = './gamedata/config/locales/') {
        $this->directory = $directory;

        $internalLanguage = $this->resolveInternalLanguage('rus');
        $this->setLanguage($internalLanguage);
    }

    public function setLanguage($language) {
        $language = $this->resolveInternalLanguage($language);

        $this->language = $language;
        $filename = $this->directory . $language . '.json';

        if (file_exists($filename)) {
            $this->translations = json_decode(file_get_contents($filename), true);
        } else {
            throw new \Exception("Localization file not found: $filename");
        }
    }

    public function get($key) {
        return $this->translations[$key] ?? $key;
    }

    public function getCurrentLanguage() {
        return $this->language;
    }

    private function resolveInternalLanguage($language) {
        return $this->languageMap[$language] ?? $language;
    }

    public function getDisplayLanguage() {
        return array_search($this->language, $this->languageMap) ?: $this->language;
    }
}

