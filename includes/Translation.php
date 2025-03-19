<?php
class Translation {
    private $translations = [];
    private $lang = 'en';
    private $availableLanguages = ['en', 'de'];
    private $fallbackLang = 'en';

    public function __construct($lang = null) {
        $this->setLanguage($lang)->loadTranslations();
    }

    public function setLanguage($lang = null) {
        if ($lang && in_array($lang, $this->availableLanguages)) {
            $this->lang = $lang;
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['lang'] = $lang;
            }
        } elseif (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['lang']) && in_array($_SESSION['lang'], $this->availableLanguages)) {
            $this->lang = $_SESSION['lang'];
        }
        return $this;
    }

    public function loadTranslations() {
        $langFile = dirname(__DIR__) . "/languages/{$this->lang}.php";
        if (file_exists($langFile)) {
            $this->translations = include $langFile;
        } else {
            $langFile = dirname(__DIR__) . "/languages/{$this->fallbackLang}.php";
            $this->translations = include $langFile;
        }
        return $this;
    }

    public function get($key, $replacements = []) {
        $translation = isset($this->translations[$key]) ? $this->translations[$key] : $key;
        foreach ($replacements as $k => $v) {
            $translation = str_replace("{{$k}}", $v, $translation);
        }
        return $translation;
    }

    public function getCurrentLanguage() {
        return $this->lang;
    }
    
    public function renderLanguageSwitcher() {
        $currentLang = $this->lang;
        $currentPage = htmlspecialchars($_SERVER['PHP_SELF']);
        $queryString = $_SERVER['QUERY_STRING'];
        
        // Remove existing lang parameter if it exists
        $queryString = preg_replace('/(&|\?)lang=[^&]*/', '', $queryString);
        $queryConnector = empty($queryString) ? '?' : '&';
        
        $html = '<li class="nav-item dropdown">';
        $html .= '<a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
        $html .= ($currentLang === 'en') ? 'English' : 'Deutsch';
        $html .= '</a>';
        $html .= '<ul class="dropdown-menu" aria-labelledby="languageDropdown">';
        
        foreach ($this->availableLanguages as $lang) {
            if ($lang !== $currentLang) {
                $langName = ($lang === 'en') ? 'English' : 'Deutsch';
                $html .= '<li><a class="dropdown-item" href="' . $currentPage;
                if (!empty($queryString)) {
                    $html .= '?' . $queryString . $queryConnector . 'lang=' . $lang;
                } else {
                    $html .= '?lang=' . $lang;
                }
                $html .= '">' . $langName . '</a></li>';
            }
        }
        
        $html .= '</ul></li>';
        return $html;
    }
}
?>
