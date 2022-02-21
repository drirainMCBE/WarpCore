<?php

declare(strict_types=1);

namespace RoMo\WarpCore\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;
use RoMo\WarpCore\warp\Warp;

class PlayerWarpEvent extends PlayerEvent implements Cancellable{

    use CancellableTrait;

    /** @var Warp */
    private Warp $warp;

    public function __construct(Player $player, Warp $warp){
        $this->player = $player;
        $this->warp = $warp;
    }

    /**
     * @return Warp
     */
    public function getWarp() : Warp{
        return $this->warp;
    }
}