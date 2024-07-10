<?php

declare(strict_types=1);

namespace RoMo\WarpCore\menu;

use RoMo\WarpCore\form\WarpMenuForm;
use RoMo\WarpCore\warp\Warp;
use RoMo\WarpCore\warp\WarpFactory;

class Menu{

    /** @var string */
    private string $id;

    /** @var array */
    private array $formData;

    private WarpMenuForm $warpMenuForm;

    public static function create(string $id, array $formData) : array{
        $menu = new self($id, $formData);
        return [$menu, $menu->initMenuForButton(...)];
    }

    private function __construct(string $id, array $formData){
        $this->id = $id;
        $this->formData = $formData;
    }

    private function initMenuForButton(array $menus) : void{
        foreach($this->formData["content"] as $index => $content){
            //LOAD OTHER MENUS
            if($content["type"] === "menu"){
                //LOAD MENU FORM BUTTON
                $menu = $menus[$content["menu"]] ?? null;
                if($menu !== null){
                    $this->formData["content"][$index]["menu"] = $menu;
                }else{
                    unset($this->formData["content"][$index]);
                }
            }elseif($content["type"] === "warp"){ //LOAD WARPS
                $warp = WarpFactory::getInstance()->getWarp($content["warp"]);
                if($warp !== null){
                    $this->formData["content"][$index]["warp"] = $warp;
                }else{
                    unset($this->formData["content"][$index]);
                }
            }else{
                unset($this->formData["content"][$index]);
            }
        }
        $this->formData["content"] = array_values($this->formData["content"]);

        $this->warpMenuForm = new WarpMenuForm($this->formData);
    }

    /**
     * @return string
     */
    public function getId() : string{
        return $this->id;
    }

    /**
     * @return WarpMenuForm
     */
    public function getWarpMenuForm() : WarpMenuForm{
        return $this->warpMenuForm;
    }
}