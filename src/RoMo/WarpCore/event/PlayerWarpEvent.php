<?php

namespace RoMo\WarpCore\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\player\Player;
use RoMo\WarpCore\warp\Warp;

class PlayerWarpEvent extends Event implements Cancellable{

    use CancellableTrait;

    /** @var Player */
    protected Player $player;

    /** @var Warp */
    protected Warp $warp;

    public function __construct(Player $player, Warp $warp){
        $this->player = $player;
        $this->warp = $warp;
    }

    public function getPlayer() : Player{
        return $this->player;
    }

    public function getWarp() : Warp{
        return $this->warp;
    }
}