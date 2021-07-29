<?php

namespace RoMo\WarpCore\warp;

use pocketmine\entity\Location;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use RoMo\WarpCore\command\ShortWarpCommand;
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
            $world = Server::getInstance()->getWorldManager()->getWorldByName($locationData["world"]);
            if($world !== null){
                $this->warps[$name] = new Warp($name, new Location(
                    $locationData["x"],
                    $locationData["y"],
                    $locationData["z"],
                    $locationData["yaw"],
                    $locationData["pitch"],
                    $world
                ));
                $this->registShortWarpCommand($this->warps[$name]);
            }

        }
    }

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
              "world" => $location->getWorld()->getFolderName()
            ];
        }
    }

    public function getAllWarps() : array{
        return $this->warps;
    }

    public function addWarp(Warp $warp) : bool{
        if(isset($this->warps[$warp->getName()])){
            return false;
        }
        $this->warps[$warp->getName()] = $warp;
        $this->registShortWarpCommand($warp);
        return true;
    }

    public function removeWarp(Warp $warp) : bool{
        foreach($this->warps as $name => $checkingWarp){
            if($warp === $checkingWarp){
                unset($this->warps[$name]);
                $this->unRegistShortWarpCommand($warp);
                return true;
            }
        }
        return false;
    }

    public function registShortWarpCommand(Warp $warp) : void{
        Server::getInstance()->getCommandMap()->register("WarpCore", new ShortWarpCommand($warp));
        foreach(Server::getInstance()->getOnlinePlayers() as $player){
            $player->getNetworkSession()->syncAvailableCommands();
        }
    }

    public function unRegistShortWarpCommand(Warp $warp) : void{
        $command = Server::getInstance()->getCommandMap()->getCommand($warp->getName());
        if(!$command instanceof ShortWarpCommand){
            return;
        }
        Server::getInstance()->getCommandMap()->unregister($command);
        foreach(Server::getInstance()->getOnlinePlayers() as $player){
            $player->getNetworkSession()->syncAvailableCommands();
        }
    }
}