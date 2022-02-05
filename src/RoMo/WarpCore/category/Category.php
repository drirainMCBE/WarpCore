<?php

declare(strict_types=1);

namespace RoMo\WarpCore\category;

use RoMo\WarpCore\warp\Warp;

class Category{

    /** @var string */
    private string $name;

    /** @var Warp[] */
    private array $warps = [];

    /**
     * @param Warp[] $warps
     */
    public function __construct(string $name, array $warps){
        $this->name = $name;
        $this->warps = $warps;
    }

    /**
     * @return string
     */
    public function getName() : string{
        return $this->name;
    }

    /**
     * @return Warp[]
     */
    public function getAllWarp() : array{
        return $this->warps;
    }

    /**
     * @param string $name
     * @return Warp|null
     */
    public function getWarp(string $name) : ?Warp{
        return $this->warps[$name] ?? null;
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
     */
    public function addWarp(Warp $warp) : void{
        $this->warps[$warp->getName()] = $warp;
    }

    /**
     * @param Warp $warp
     */
    public function removeWarp(Warp $warp) : void{
        if(!isset($this->warps[$warp->getName()])){
            return;
        }
        unset($this->warps[$warp->getName()]);
    }
}