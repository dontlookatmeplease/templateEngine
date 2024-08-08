<?php

/**
 * Fabricates page from templates
 *
 * @author jirkap
 */
include 'template.php';

class Fabricator {
    protected $template;
    protected $config;
    protected $page;
    protected $index = [];

    public function __construct($page = "index") {
        $this->config = json_decode(file_get_contents('config/config.json'), true);
        $this->page = $page;
        $this->build();
    }
    
    public function build($block = "index") {
        $path = $this->path($block);
        
        if(isset($path["file"]["tpl"])) {
            $overrideKeys = isset($this->config["tabs"][$this->page]["overrideKeys"]) ? $this->config["tabs"][$this->page]["overrideKeys"] : "";
            $this->index[$block] = new Template($path["file"]["tpl"], $overrideKeys);
            
            foreach($this->index[$block]->getKeys(Template::TMP_VARIABLE)[1] as $tag) {
                $tag != "tab" ?: $this->index[$block]->set($tag, $this->config["tabs"][$this->page]["title"], Template::TMP_VARIABLE);
                !isset($this->config[$block]) ?: $this->index[$block]->set($tag, $this->config[$block][$tag], Template::TMP_VARIABLE);
            }

            foreach($this->index[$block]->getKeys(Template::TMP_IMAGE)[1] as $tag) {
                $img = empty($this->config["tabs"][$this->page]["overrideImg"][$tag]) ? $tag : $this->config["tabs"][$this->page]["overrideImg"][$tag];
                $imgPath = $this->path($img, "img", ["jpg", "png"])["file"];
                !isset($imgPath) ?: $this->index[$block]->set($tag, isset($imgPath["jpg"]) ? $imgPath["jpg"] : $imgPath["png"], Template::TMP_IMAGE);
            }

            foreach($this->index[$block]->getKeys(Template::TMP_TEMPLATE)[1] as $tag) {
                !in_array($tag, $this->config["tabs"][$this->page]["blocks"]) ?: $this->build($tag);
                $this->index[$block]->set($tag, isset($this->index[$tag]) ? $this->index[$tag]->output() : "");
            }
        }
    }
    
    function path($name, $dir = "tpl", $ext = []) {
        array_push($ext, "tpl");
        $directory = $_SERVER['DOCUMENT_ROOT'].($dir == "" ?: "/".$dir);
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
                                                    RecursiveIteratorIterator::SELF_FIRST);
        $ret = [];
        foreach ($iterator as $item) {
            $basename = $item->getBasename();

            $basename != $name ?:$ret["dir"] = $item->getPathname();
            foreach($ext as $e){
                $filename = $name.".".$e;
                
                $basename != $filename ?: $ret["file"][$e] = ($dir == "img") ? "..". strstr($item->getPathname(), "/".$dir) : $item->getPathname();
            }
        }
        return $ret;
    }
    
    public function output() {
        echo $this->index["index"]->output();
    }
}
