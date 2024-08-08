<?php

class Template {
    protected $file;
    protected $values           = array();
    protected $overrideKeys     = array();
    protected $paterns          = [1 => ["symbol"   => "$",
                                 "pattern"  => '/\[\$([^]]*?)\]/'],
                                2 => ["symbol"   => "@",
                                 "pattern"  => '/\[\@([^]]*?)\]/'],
                                3 => ["symbol"   => "#",
                                 "pattern"  => '/\[\#([^]]*?)\]/']];
    const TMP_VARIABLE          = 1;
    const TMP_TEMPLATE          = 2;
    const TMP_IMAGE             = 3;

    public function __construct($file, $overrideKeys = "") {
        $this->file         = $file;
        $this->overrideKeys = $overrideKeys;
    }
    
    public function set($key, $value, $type = self::TMP_TEMPLATE) {
        empty($this->overrideKeys) ?: !in_array($key, $this->overrideKeys, true) ?: $key = array_search($key, $this->overrideKeys);
        $this->values[$key]["value"]    = $value;
        $this->values[$key]["type"]     = $this->paterns[$type]["symbol"];
    }

    public function output() {
        $output = $this->loadFile();

        foreach ($this->values as $key => $value) {
            $tagToReplace = "[".$value["type"].$key."]";
            $output = str_replace($tagToReplace, $value["value"], $output);
        }

        return $output;
    }
    
    public function getKeys($type = self::TMP_TEMPLATE) {
        $input = $this->loadFile();
        preg_match_all($this->paterns[$type]["pattern"], $input, $matches);
        if(!empty($this->overrideKeys)) {
            foreach ($matches as $key => $value) {
                !in_array($value, $this->overrideKeys, true) ?: $matches[$key] = array_search($value, $this->overrideKeys);
            }
        }
        return $matches;
    }
    
    public function loadFile() {
        if (!file_exists($this->file)) {
            return "Error loading template file ($this->file).";
        }
        
        return file_get_contents($this->file);
    }
}