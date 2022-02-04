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

    /** @var bool */
    private bool $isVisual;
    private bool $isSound;

    public function __construct(Player $player, Warp $warp, bool $isVisual, bool $isSound){
        $this->player = $player;
        $this->warp = $warp;
        $this->isVisual = $isVisual;
        $this->isSound = $isSound;
    }

    /**
     * @return Warp
     */
    public function getWarp() : Warp{
        return $this->warp;
    }

    /**
     * @return bool
     */
    public function isVisual() : bool{
        return $this->isVisual;
    }

    /**
     * @return bool
     */
    public function isSound() : bool{
        return $this->isSound;
    }
}