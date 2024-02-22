<?php

declare(strict_types=1);

namespace RoMo\WarpCore\form;

use pocketmine\form\Form;
use pocketmine\player\Player;
use RoMo\WarpCore\menu\Menu;
use RoMo\WarpCore\warp\Warp;
use RoMo\WarpCore\WarpCore;

class WarpMenuForm implements Form{

    /** @var array */
    private array $formData = [];

    /** @var Warp|Menu[] */
    private array $contentForButton = [];

    public function __construct(Menu $menu){
        $translator = WarpCore::getTranslator();
        $buttons = [];
        foreach($menu->getContent() as $value){
            $added = false;
            if($value instanceof Warp){
                $buttons[] = ["text" => $value->getName()];
                $added = true;
            }elseif($value instanceof Menu){
                $buttons[] = ["text" => $value->getName()];
                $added = true;
            }

            if($added){
                $this->contentForButton[] = $value;
            }
        }
        $this->formData = [
            "type" => "form",
            "title" => $translator->getTranslate("form.title"),
            "content" => "",
            "buttons" => $buttons
        ];
    }

    public function jsonSerialize() : array{
        return $this->formData;
    }


    public function handleResponse(Player $player, $data) : void{
        if($data === null){
            return;
        }
        $value = $this->contentForButton[$data] ?? null;
        if(is_null($value)){
            return;
        }
        if($value instanceof Warp){
            $value->teleport($player);
        }elseif($value instanceof Menu){
            $player->sendForm($value->getWarpMenuForm());
        }
    }
}