<?php

declare(strict_types=1);

namespace RoMo\WarpCore\warp;

use pocketmine\entity\Location;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\sound\EndermanTeleportSound;
use RoMo\WarpCore\event\PlayerWarpEvent;
use RoMo\WarpCore\WarpCore;

class Warp{

    /** @var string */
    private string $name;

    /** @var Location */
    private Location $location;

    /** @var bool */
    private bool $isCommandRegister;

    public function __construct(string $name, Location $location, bool $isCommandRegister){
        $this->name = $name;
        $this->location = $location;
        $this->isCommandRegister = $isCommandRegister;
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
    public function isCommandRegister() : bool{
        return $this->isCommandRegister;
    }

    /**
     * @param bool $isCommandRegister
     */
    public function setIsCommandRegister(bool $isCommandRegister) : void{
        $this->isCommandRegister = $isCommandRegister;
    }

    /**
     * @param Player $player
     * @param bool $isVisual
     * @param Player[]|null $targetVisual
     * @param bool $isSound
     * @param Player[]|null $targetSound
     */
    public function teleport(Player $player, bool $isVisual = true, array $targetVisual = null, bool $isSound = true, array $targetSound = null) : void{
        $event = new PlayerWarpEvent($player, $this, $isVisual, $isSound);
        $event->call();
        if(!$event->isCancelled()){
            $translator = WarpCore::getTranslator();
            $player->teleport($this->getLocation());
            $player->sendTitle($translator->getTranslate("title"), $translator->getTranslate("subtitle", [$this->getName()]));
            WarpCore::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player, $isVisual, $targetVisual, $isSound, $targetSound) : void{
                if(!$isVisual && !$isSound){
                    return;
                }
                $world = $player->getWorld();
                $position = $player->getPosition();
                if($isVisual){
                    $world->addParticle($position, new EndermanTeleportParticle(), $targetVisual);
                }
                if($isSound){
                    $world->addSound($position, new EndermanTeleportSound(), $targetSound);
                }
            }), 5);
        }
    }
}