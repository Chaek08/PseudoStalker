<?php

namespace app\forms\classes;

class Localization {
    private $translations = [];
    private $language;
    private $directory;

    public function __construct($language, $directory = './config/locales/') {
        $this->directory = $directory;
        $this->setLanguage('rus');
    }

    public function setLanguage($language) {
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
}

