<?php

declare(strict_types=1);

namespace RoMo\WarpCore\warp;

use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\CameraInstructionPacket;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionColor;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionTime;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use RoMo\WarpCore\protocol\UpdateWarpPacket;
use RoMo\WarpCore\protocol\WarpRequestPacket;
use RoMo\WarpCore\WarpCore;
use Generator;
use SOFe\AwaitGenerator\Await;
use Throwable;
use Closure;

class WarpFactory{

    use SingletonTrait;

    private DataConnector $database;

    /** @var Warp[] */
    private array $warps = [];

    /** @var Warp[] */
    private array $warpQueue = [];

    /** @var Closure[] */
    private array $warpEffectQueue = [];

    /** @var CameraInstructionPacket */
    private CameraInstructionPacket $cameraInstructionPacket;
    private CameraInstructionPacket $cameraInstructionPacketInternal;

    public static function init() : void{
        self::$instance = new self();
    }

    private function __construct(){
        $this->cameraInstructionPacket = CameraInstructionPacket::create(
            null,
            false,
            new CameraFadeInstruction(
                new CameraFadeInstructionTime(0.5, 1.75, 0.5),
                new CameraFadeInstructionColor(0, 0, 0)
            ),
            null,
            null
        );
        $this->cameraInstructionPacketInternal = CameraInstructionPacket::create(
            null,
            false,
            new CameraFadeInstruction(
                new CameraFadeInstructionTime(0.5, 0, 0.5),
                new CameraFadeInstructionColor(0, 0, 0)
            ),
            null,
            null
        );


        $this->database = WarpCore::getInstance()->getDatabase();

        //LOAD ALL WARPS
        Await::f2c(function() : Generator{
            yield from $this->database->asyncGeneric("initialization");
            $rows = yield from $this->database->asyncSelect("warp.get.all");

            foreach($rows as $row){
                $warp = $this->getWarpFromData($row);
                if($warp !== null){
                    $this->warps[$warp->getName()] = $warp;
                }
            }

            WarpCore::getInstance()->onCompleteToLoadAllWarps();
        });

        Server::getInstance()->getPluginManager()->registerEvent(PlayerLoginEvent::class, function(PlayerLoginEvent $event) : void{
            $player = $event->getPlayer();
            $playerName = $player->getName();
            $warpQueued = $this->warpQueue[$playerName] ?? null;
            if($warpQueued !== null){
                unset($this->warpQueue[$playerName]);
                $this->warpEffectQueue[$playerName] = $warpQueued->teleportFromAnotherServer($player);
            }
        }, EventPriority::NORMAL, WarpCore::getInstance());

        Server::getInstance()->getPluginManager()->registerEvent(PlayerJoinEvent::class, function(PlayerJoinEvent $event) : void{
            $playerName = $event->getPlayer()->getName();
            $warpEffectQueued = $this->warpEffectQueue[$playerName] ?? null;
            if($warpEffectQueued !== null){
                unset($this->warpEffectQueue[$playerName]);
                $warpEffectQueued();
            }
        }, EventPriority::NORMAL, WarpCore::getInstance());
    }

    public function getWarpFromData(array $row) : ?Warp{
        try{
            return new Warp(
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
                (boolean) $row["is_command_register"],
                $this->cameraInstructionPacket,
                $this->cameraInstructionPacketInternal
            );
        } catch(Throwable $e){
            return null;
        }

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

        $packet = new UpdateWarpPacket();
        $packet->setServerName(WarpCore::getInstance()->getServerName());
        $packet->setWarpName($warp->getName());
        $packet->setUpdateType(UpdateWarpPacket::CREATE);
        WarpCore::getInstance()->getStarGateClient()?->sendPacket($packet);
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

        $packet = new UpdateWarpPacket();
        $packet->setServerName(WarpCore::getInstance()->getServerName());
        $packet->setWarpName($warp->getName());
        $packet->setUpdateType(UpdateWarpPacket::REMOVE);
        WarpCore::getInstance()->getStarGateClient()?->sendPacket($packet);
    }

    public function syncCommandData() : void{
        foreach(Server::getInstance()->getOnlinePlayers() as $player){
            $player->getNetworkSession()->syncAvailableCommands();
        }
    }

    public function onWarpUpdate(UpdateWarpPacket $packet) : void{
        switch($packet->getUpdateType()){
            case UpdateWarpPacket::CREATE:
                Await::f2c(function() use ($packet) : Generator{
                    $rows = yield from $this->database->asyncSelect("warp.get", ["name" => $packet->getWarpName()]);
                    if(!isset($rows[0])){
                        return;
                    }
                    $warp = $this->getWarpFromData($rows[0]);
                    if($warp === null){
                        return;
                    }
                    $this->warps[$warp->getName()] = $warp;
                });
                break;
            case UpdateWarpPacket::EDIT:
                Await::f2c(function() use ($packet) : Generator{
                    $rows = yield from $this->database->asyncSelect("warp.get", ["name" => $packet->getWarpName()]);
                    if(!isset($rows[0])){
                        return;
                    }
                    $warp = $this->warps[$packet->getWarpName()] ?? null;
                    if($warp === null){
                        return;
                    }
                    $warp->updateWarpFromData($rows[0]);
                });
                break;
            case UpdateWarpPacket::REMOVE:
                $warp = $this->warps[$packet->getWarpName()] ?? null;
                if($warp !== null){
                    $warp->commandUnregister();
                    unset($this->warps[$packet->getWarpName()]);
                }
                break;
        }
    }

    public function onWarpRequest(WarpRequestPacket $packet) : void{
        $warp = $this->warps[$packet->getWarpName()] ?? null;
        if(is_null($warp)){
            return;
        }
        $playerName = $packet->getPlayerName();
        $player = Server::getInstance()->getPlayerExact($playerName);
        if(is_null($player)){
            $this->warpQueue[$playerName] = $warp;
        }else{
            $warp->teleport($player);
        }
    }

    /**
     * @return CameraInstructionPacket
     */
    public function getCameraInstructionPacket() : CameraInstructionPacket{
        return $this->cameraInstructionPacket;
    }

    /**
     * @return CameraInstructionPacket
     */
    public function getCameraInstructionPacketInternal() : CameraInstructionPacket{
        return $this->cameraInstructionPacketInternal;
    }
}
