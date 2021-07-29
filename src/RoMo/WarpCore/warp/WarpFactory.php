<?php

namespace RoMo\WarpCore\warp;

use pocketmine\entity\Location;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use RoMo\WarpCore\WarpCore;

class WarpFactory{

    /** @var Warp[] */
    protected array $warps;

    use SingletonTrait;

    public static function init(){
        self::$instance = new self();
    }

    private function __construct(){
        $data = json_decode(file_get_contents(WarpCore::getInstance()->getDataFolder() . "warps.json"), true);
        foreach($data as $name => $locationData){
            $this->warps[$name] = new Warp($name, new Location(
                $locationData["x"],
                $locationData["y"],
                $locationData["z"],
                $locationData["yaw"],
                $locationData["pitch"],
                Server::getInstance()->getWorldManager()->getWorldByName($locationData["world"])
            ));
        }
    }
}