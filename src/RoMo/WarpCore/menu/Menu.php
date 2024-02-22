<?php

declare(strict_types=1);

namespace RoMo\WarpCore\menu;

use RoMo\WarpCore\form\WarpMenuForm;
use RoMo\WarpCore\warp\Warp;

class Menu{

    /** @var string */
    private string $name;

    /** @var Warp|Menu[] */
    private array $content;

    private WarpMenuForm $warpMenuForm;

    public function __construct(string $name, array $content){
        $this->name = $name;
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getName() : string{
        return $this->name;
    }

    /**
     * @return array
     */
    public function getContent() : array{
        return $this->content;
    }

    /**
     * @param array $content
     */
    public function setContent(array $content) : void{
        $this->content = $content;
    }

    public function initWarpMenuForm() : void{
        $this->warpMenuForm = new WarpMenuForm($this);
    }

    /**
     * @return WarpMenuForm
     */
    public function getWarpMenuForm() : WarpMenuForm{
        return $this->warpMenuForm;
    }
}