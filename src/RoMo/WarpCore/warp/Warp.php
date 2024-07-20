<?php

declare(strict_types=1);

namespace RoMo\WarpCore\warp;

use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\network\mcpe\protocol\CameraInstructionPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use pocketmine\world\format\Chunk;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\sound\EndermanTeleportSound;
use RoMo\Translator\Translator;
use RoMo\WarpCore\command\ShortWarpCommand;
use RoMo\WarpCore\entity\WarpEffectEntity;
use RoMo\WarpCore\entity\WarpEndEffectEntity;
use RoMo\WarpCore\event\PlayerWarpEvent;
use RoMo\WarpCore\protocol\UpdateWarpPacket;
use RoMo\WarpCore\protocol\WarpRequestPacket;
use RoMo\WarpCore\WarpCore;
use Generator;
use Closure;

class Warp{

    /** @var string */
    private string $name;
    private string $serverName;
    private string $worldName;

    /** @var Vector3 */
    private Vector3 $position;

    /** @var float */
    private float $yaw;
    private float $pitch;

    /** @var bool */
    private bool $isTitle;
    private bool $isParticle;
    private bool $isSound;
    private bool $isPermit;
    private bool $isCommandRegister;

    private WarpCore $warpCore;
    private TaskScheduler $scheduler;
    private CameraInstructionPacket $cameraInstructionPacket;
    private CameraInstructionPacket $cameraInstructionPacketInternal;
    private Translator $translator;

    public function __construct(string $name, string $serverName, string $worldName, Vector3 $position, float $yaw, float $pitch, bool $isTitle, bool $isParticle, bool $isSound, bool $isPermit, bool $isCommandRegister, CameraInstructionPacket $packet, CameraInstructionPacket $packetInternal){
        $this->name = $name;
        $this->serverName = $serverName;
        $this->worldName = $worldName;
        $this->position = $position;
        $this->yaw = $yaw;
        $this->pitch = $pitch;
        $this->isTitle = $isTitle;
        $this->isParticle = $isParticle;
        $this->isSound = $isSound;
        $this->isPermit = $isPermit;
        $this->isCommandRegister = $isCommandRegister;

        if($this->isCommandRegister){
            $this->commandRegister();
        }

        $this->warpCore = WarpCore::getInstance();
        $this->scheduler = WarpCore::getInstance()->getScheduler();
        $this->cameraInstructionPacket = $packet;
        $this->cameraInstructionPacketInternal = $packetInternal;
        $this->translator = WarpCore::getTranslator();
    }

    /**
     * @return string
     */
    public function getName() : string{
        return $this->name;
    }

    /**
     * @return string
     */
    public function getServerName() : string{
        return $this->serverName;
    }

    /**
     * @return string
     */
    public function getWorldName() : string{
        return $this->worldName;
    }

    /**
     * @return Vector3
     */
    public function getPosition() : Vector3{
        return $this->position;
    }

    /**
     * @return float
     */
    public function getYaw() : float{
        return $this->yaw;
    }

    /**
     * @return float
     */
    public function getPitch() : float{
        return $this->pitch;
    }

    /**
     * @return bool
     */
    public function isTitle() : bool{
        return $this->isTitle;
    }

    /**
     * @param bool $isTitle
     */
    public function setIsTitle(bool $isTitle) : void{
        $this->isTitle = $isTitle;
    }

    /**
     * @return bool
     */
    public function isParticle() : bool{
        return $this->isParticle;
    }

    /**
     * @param bool $isParticle
     */
    public function setIsParticle(bool $isParticle) : void{
        $this->isParticle = $isParticle;
    }

    /**
     * @return bool
     */
    public function isSound() : bool{
        return $this->isSound;
    }

    /**
     * @param bool $isSound
     */
    public function setIsSound(bool $isSound) : void{
        $this->isSound = $isSound;
    }

    /**
     * @return bool
     */
    public function isPermit() : bool{
        return $this->isPermit;
    }

    /**
     * @param bool $isPermit
     */
    public function setIsPermit(bool $isPermit) : void{
        $this->isPermit = $isPermit;
    }

    /**
     * @return bool
     */
    public function isCommandRegister() : bool{
        return $this->isCommandRegister;
    }

    /**
     * @param bool $isCommandRegister
     */
    public function setIsCommandRegister(bool $isCommandRegister) : void{
        $this->isCommandRegister = $isCommandRegister;
        if($this->isCommandRegister){
            $this->commandRegister();
        }else{
            $this->commandUnregister();
        }
    }

    public function sendUpdateSignal() : Generator{
        yield from $this->warpCore->getDatabase()->asyncChange("warp.edit", [
            "name" => $this->name,
            "is_title" => $this->isTitle,
            "is_particle" => $this->isParticle,
            "is_sound" => $this->isSound,
            "is_permit" => $this->isPermit,
            "is_command_register" => $this->isCommandRegister
        ]);

        $packet = new UpdateWarpPacket();
        $packet->setServerName(WarpCore::getInstance()->getServerName());
        $packet->setWarpName($this->name);
        $packet->setUpdateType(UpdateWarpPacket::EDIT);
        WarpCore::getInstance()->getStarGateClient()->sendPacket($packet);
    }

    public function updateWarpFromData(array $row) : void{
        $this->isTitle = (boolean) $row["is_title"];
        $this->isParticle = (boolean) $row["is_particle"];
        $this->isSound = (boolean) $row["is_sound"];
        $this->isPermit = (boolean) $row["is_permit"];
        $this->setIsCommandRegister((boolean) $row["is_command_register"]);
    }

    public function commandRegister() : void{
        if(Server::getInstance()->getCommandMap()->getCommand($this->getName()) instanceof ShortWarpCommand){
            return;
        }
        Server::getInstance()->getCommandMap()->register("WarpCore", new ShortWarpCommand($this));
        WarpFactory::getInstance()->syncCommandData();
    }

    public function commandUnregister() : void{
        $command = Server::getInstance()->getCommandMap()->getCommand($this->getName());
        if(!$command instanceof ShortWarpCommand){
            return;
        }
        Server::getInstance()->getCommandMap()->unregister($command);
        WarpFactory::getInstance()->syncCommandData();
    }

    /**
     * @param Player        $player
     * @param Player[]|null $targetVisual
     * @param Player[]|null $targetSound
     */
    public function teleport(Player $player, array $targetVisual = null, array $targetSound = null) : void{
        $event = new PlayerWarpEvent($player, $this);
        $event->call();
        if(!$event->isCancelled()){
            if(!$this->isPermit){
                if(!$player->hasPermission("warpcore.manage.warp")){
                    $player->sendMessage($this->translator->getMessage("fail.to.warp.by.not.permitting"));
                    return;
                }
            }
            if($this->serverName === $this->warpCore->getServerName()){
                $player->getNetworkSession()->sendDataPacket($this->cameraInstructionPacketInternal);
            }else{
                $player->getNetworkSession()->sendDataPacket($this->cameraInstructionPacket);
            }
            $entity = new WarpEffectEntity($player->getLocation());
            $entity->spawnToAll();

            $this->scheduler->scheduleDelayedTask(new ClosureTask(function() use ($player, $targetVisual, $targetSound){
                if(!$player->isConnected()){
                    return;
                }
                if($this->serverName !== $this->warpCore->getServerName()){
                    $playerName = $player->getName();
                    $packet = new WarpRequestPacket();
                    $packet->setServerName($this->serverName);
                    $packet->setWarpName($this->name);
                    $packet->setPlayerName($playerName);
                    WarpCore::getInstance()->getStarGateClient()->sendPacket($packet);
                    return;
                }

                if(is_null(($world = Server::getInstance()->getWorldManager()->getWorldByName($this->worldName)))){
                    $player->sendMessage($this->translator->getMessage("fail.to.find.world"));
                    return;
                }
                $location = new Location($this->position->getX(), $this->position->getY(), $this->position->getZ(), $world, $this->getYaw(), $this->getPitch());
                $entity = new WarpEndEffectEntity($location);
                $entity->spawnToAll();
                $player->teleport($location);

                $this->scheduler->scheduleDelayedTask(new ClosureTask(function() use ($player, $targetVisual, $targetSound) : void{
                    if(!$player->isConnected()){
                        return;
                    }
                    if($this->isTitle){
                        $player->sendTitle($this->translator->getTranslate("title"), $this->translator->getTranslate("subtitle", [$this->getName()]));
                    }
                    if(!$this->isParticle && !$this->isSound){
                        return;
                    }
                    $world = $player->getWorld();
                    $position = $player->getPosition();
                    if($this->isParticle){
                        $world->addParticle($position, new EndermanTeleportParticle(), $targetVisual);
                    }
                    /*if($this->isSound){
                        $world->addSound($position, new EndermanTeleportSound(), $targetSound);
                    }*/;
                }), 5);
            }), 10);
        }
    }

    public function teleportFromAnotherServer(Player $player, array $targetVisual = null, array $targetSound = null) : ?Closure{
        if(is_null(($world = Server::getInstance()->getWorldManager()->getWorldByName($this->worldName)))){
            $player->sendMessage($this->translator->getMessage("fail.to.find.world"));
            return null;
        }

        $location = new Location($this->position->getX(), $this->position->getY(), $this->position->getZ(), $world, $this->getYaw(), $this->getPitch());
        $entity = new WarpEndEffectEntity($location);
        $entity->spawnToAll();
        $player->teleport($location);

        return function() use ($player, $targetVisual, $targetSound) : void{
            if($this->isTitle){
                $player->sendTitle($this->translator->getTranslate("title"), $this->translator->getTranslate("subtitle", [$this->getName()]));
            }
            if(!$this->isParticle && !$this->isSound){
                return;
            }
            $world = $player->getWorld();
            $position = $player->getPosition();
            if($this->isParticle){
                $world->addParticle($position, new EndermanTeleportParticle(), $targetVisual);
            }
            /*if($this->isSound){
                $world->addSound($position, new EndermanTeleportSound(), $targetSound);
            }*/;
        };
    }
}