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

    /** @var Warp[]|Menu[] */
    private array $contentForButton = [];

    public function __construct(array $formData){
        $buttons = [];
        foreach($formData["content"] as $contentData){
            if($contentData["type"] === "warp"){
                $this->contentForButton[] = $contentData["warp"];
            }elseif($contentData["type"] === "menu"){
                $this->contentForButton[] = $contentData["menu"];
            }elseif($contentData["type"] === "none"){
                $this->contentForButton[] = null;
            }else{
                continue;
            }
            $buttonData = ["text" => $contentData["text"]];
            if(isset($contentData["image"])){
                $buttonData["image"] = [
                    "type" => "path",
                    "data" => (string) $contentData["image"]
                ];
            }
            $buttons[] = $buttonData;
        }
        $this->formData = [
            "type" => "form",
            "title" => $formData["title"],
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
        }else{
            $player->sendForm($this);
        }
    }
}