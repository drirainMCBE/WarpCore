<?php

declare(strict_types=1);

namespace RoMo\WarpCore\warp;

use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use RoMo\WarpCore\WarpCore;
use RoMo\XuidCore\libs\SOFe\AwaitGenerator\Await;
use Generator;

class WarpFactory{

    use SingletonTrait;

    private DataConnector $database;

    /** @var Warp[] */
    private array $warps = [];

    public static function init() : void{
        self::$instance = new self();
    }

    private function __construct(){
        $this->database = WarpCore::getInstance()->getDatabase();
        $this->database->executeSelect("initialization");

        //LOAD ALL WARPS
        Await::f2c(function() : Generator{
            $rows = yield from $this->database->asyncSelect("warp.get.all");

            foreach($rows as $row){
                $warp = new Warp(
                    $row["name"],
                    $row["server_name"],
                    $row["world_name"],
                    new Vector3($row["x"], $row["y"], $row["z"]),
                    $row["yaw"],
                    $row["pitch"],
                    (boolean) $row["is_title"],
                    (boolean) $row["is_particle"],
                    (boolean) $row["is_sound"],
                    (boolean) $row["is_permit"],
                    (boolean) $row["is_command_register"]
                );
                $this->warps[$warp->getName()] = $warp;
            }
        });
    }

    /**
     * @param string $name
     *
     * @return Warp|null
     */
    public function getWarp(string $name) : ?Warp{
        return $this->warps[$name] ?? null;
    }

    /**
     * @return Warp[]
     */
    public function getAllWarp() : array{
        return $this->warps;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isExistWarp(string $name) : bool{
        return !is_null($this->getWarp($name));
    }

    /**
     * @param Warp $warp
     *
     * @return Generator
     */
    public function addWarp(Warp $warp) : Generator{
        $vector = $warp->getPosition();
        yield from $this->database->asyncInsert("warp.add", [
            "name" => $warp->getName(),
            "server_name" => $warp->getServerName(),
            "world_name" => $warp->getWorldName(),
            "x" => $vector->x,
            "y" => $vector->y,
            "z" => $vector->z,
            "yaw" => $warp->getYaw(),
            "pitch" => $warp->getPitch(),
            "is_title" => $warp->isTitle(),
            "is_particle" => $warp->isParticle(),
            "is_sound" => $warp->isSound(),
            "is_permit" => $warp->isPermit(),
            "is_command_register" => $warp->isCommandRegister()
        ]);

        $this->warps[$warp->getName()] = $warp;
    }

    /**
     * @param Warp $warp
     *
     * @return Generator
     */
    public function removeWarp(Warp $warp) : Generator{
        yield from $this->database->asyncGeneric("warp.remove", [
            "name" => $warp->getName()
        ]);

        if(isset($this->warps[$warp->getName()])){
            $this->warps[$warp->getName()]->commandUnregister();
            unset($this->warps[$warp->getName()]);
        }
    }

    public function syncCommandData() : void{
        foreach(Server::getInstance()->getOnlinePlayers() as $player){
            $player->getNetworkSession()->syncAvailableCommands();
        }
    }
}