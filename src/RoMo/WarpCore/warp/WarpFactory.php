<?php

namespace RoMo\WarpCore\warp;

use pocketmine\utils\SingletonTrait;
use RoMo\WarpCore\WarpCore;

class WarpFactory{

    use SingletonTrait;

    public static function init(){
        self::$instance = new self();
    }

    private function __construct(){
        $data = json_decode(file_get_contents(WarpCore::getInstance()->getDataFolder() . "warps.json"), true);
        //TODO
    }
}