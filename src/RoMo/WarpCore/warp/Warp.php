<?php

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
    protected string $name;

    /** @var Location */
    protected Location $location;

    public function __construct(string $name, Location $location){
        $this->name = $name;
        $this->location = $location;
    }

    public function getName() : string{
        return $this->name;
    }

    public function getLocation() : Location{
        return $this->location;
    }

    public function teleport(Player $player) : void{
        $event = new PlayerWarpEvent($player, $this);
        $event->call();
        if(!$event->isCancelled()){
            $player->teleport($this->location);
            $player->sendTitle(WarpCore::getTranslate("title"), WarpCore::getTranslate("subtitle", [$this->getName()]));
            WarpCore::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player) : void{
                $world = $player->getWorld();
                $position = $player->getPosition();
                $world->addParticle($position, new EndermanTeleportParticle());
                $world->addSound($position, new EndermanTeleportSound());
            }), 5);
        }
    }
}