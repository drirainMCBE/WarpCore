<?php

declare(strict_types=1);

namespace RoMo\WarpCore\warp;

use pocketmine\entity\Location;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\sound\EndermanTeleportSound;
use RoMo\WarpCore\command\ShortWarpCommand;
use RoMo\WarpCore\command\WarpCommand;
use RoMo\WarpCore\event\PlayerWarpEvent;
use RoMo\WarpCore\WarpCore;

class Warp{

    /** @var string */
    private string $name;

    /** @var Location */
    private Location $location;

    /** @var bool */
    private bool $isTitle;
    private bool $isParticle;
    private bool $isSound;
    private bool $isPermit;
    private bool $isCommandRegister;

    public function __construct(string $name, Location $location, bool $isTitle, bool $isParticle, bool $isSound, bool $isPermit, bool $isCommandRegister){
        $this->name = $name;
        $this->location = $location;
        $this->isTitle = $isTitle;
        $this->isParticle = $isParticle;
        $this->isSound = $isSound;
        $this->isPermit = $isPermit;
        $this->isCommandRegister = $isCommandRegister;

        if($this->isCommandRegister){
            $this->commandRegister();
        }
    }

    /**
     * @return string
     */
    public function getName() : string{
        return $this->name;
    }

    /**
     * @return Location
     */
    public function getLocation() : Location{
        return $this->location;
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
     * @param Player $player
     * @param Player[]|null $targetVisual
     * @param Player[]|null $targetSound
     */
    public function teleport(Player $player, array $targetVisual = null, array $targetSound = null) : void{
        $event = new PlayerWarpEvent($player, $this);
        $event->call();
        if(!$event->isCancelled()){
            $translator = WarpCore::getTranslator();
            if(!$this->isPermit()){
                if(!$player->hasPermission("manage-warp")){
                    $player->sendMessage($translator->getMessage("fail.to.warp.by.not.permitting"));
                    return;
                }
            }
            $player->teleport($this->getLocation());
            if($this->isTitle()){
                $player->sendTitle($translator->getTranslate("title"), $translator->getTranslate("subtitle", [$this->getName()]));
            }
            $isParticle = $this->isParticle();
            $isSound = $this->isSound();
            WarpCore::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player, $isParticle, $targetVisual, $isSound, $targetSound) : void{
                if(!$isParticle && !$isSound){
                    return;
                }
                $world = $player->getWorld();
                $position = $player->getPosition();
                if($isParticle){
                    $world->addParticle($position, new EndermanTeleportParticle(), $targetVisual);
                }
                if($isSound){
                    $world->addSound($position, new EndermanTeleportSound(), $targetSound);
                }
            }), 5);
        }
    }
}