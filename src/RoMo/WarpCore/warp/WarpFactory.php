<?php

declare(strict_types=1);

namespace RoMo\WarpCore\warp;

use JsonException;
use pocketmine\entity\Location;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use RoMo\WarpCore\WarpCore;

class WarpFactory{

    use SingletonTrait;

    /** @var Warp[] */
    private array $warps = [];

    /** @var string */
    private string $dataPath;

    public static function init() : void{
        self::$instance = new self();
    }

    /**
     * @throws JsonException
     */
    private function __construct(){
        $this->dataPath = WarpCore::getInstance()->getDataFolder() . "warps.json";
        if(!is_file($this->dataPath)){
            $this->save();
        }
        $data = json_decode(file_get_contents($this->dataPath), true, 512, JSON_THROW_ON_ERROR);
        foreach($data as $name => $warpData){
            $world = Server::getInstance()->getWorldManager()->getWorldByName($warpData["world"]);
            if($world !== null){
                $this->warps[$name] = new Warp(
                    $name,
                    new Location($warpData["x"], $warpData["y"], $warpData["z"], $world, $warpData["yaw"], $warpData["pitch"]),
                    $warpData["isCommandRegister"]
                );
            }
        }
    }

    /**
     * @throws JsonException
     */
    public function save() : void{
        $data = [];
        foreach($this->warps as $warp){
            $location = $warp->getLocation();
            $data[$warp->getName()] = [
                "x" => $location->getX(),
                "y" => $location->getY(),
                "z" => $location->getZ(),
                "yaw" => $location->getYaw(),
                "pitch" => $location->getPitch(),
                "world" => $location->getWorld()->getFolderName(),
                "isCommandRegister" => $warp->isCommandRegister()
            ];
        }
        file_put_contents($this->dataPath, json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}