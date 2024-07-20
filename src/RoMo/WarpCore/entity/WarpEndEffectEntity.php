<?php

declare(strict_types=1);

namespace RoMo\WarpCore\entity;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;

class WarpEndEffectEntity extends Entity{

    private int $lifeTick = 40;

    public function __construct(Location $location, ?CompoundTag $nbt = null){
        parent::__construct($location, $nbt);
        $this->setHasGravity(false);
    }

    public static function getNetworkTypeId() : string{
        return "blf:warp2";
    }

    protected function getInitialDragMultiplier() : float{
        return 0;
    }

    protected function getInitialGravity() : float{
        return 0;
    }

    protected function getInitialSizeInfo() : EntitySizeInfo{
        return new EntitySizeInfo(1.8, 0.6);
    }

    protected function entityBaseTick(int $tickDiff = 1) : bool{
        if(--$this->lifeTick < 1){
            $this->close();
        }
        return true;
    }

    public function attack(EntityDamageEvent $source) : void{
        return;
    }
}