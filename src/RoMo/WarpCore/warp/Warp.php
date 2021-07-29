<?php

namespace RoMo\WarpCore\warp;

use pocketmine\entity\Location;

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
}