<?php

namespace RoMo\WarpCore\form;

use pocketmine\form\Form;
use pocketmine\player\Player;
use RoMo\WarpCore\WarpCore;

class manageWarpForm implements Form{
    public function jsonSerialize() : array{
        return [
            "type" => "form",
            "title" => WarpCore::getPrefix(),
            "content" => WarpCore::getTranslate("choose.to.do"),
            "buttons" => [
                [
                    "text" => WarpCore::getTranslate("add.warp.button.1") . "\n" . WarpCore::getTranslate("add.warp.button.2")
                ],
                [
                    "text" => WarpCore::getTranslate("remove.warp.button.1") . "\n" . WarpCore::getTranslate("remove.warp.button.2")
                ]
            ]
        ];
    }
    public function handleResponse(Player $player, $data) : void{
        if($data === null){
            return;
        }
        if($data === 0){
            $player->sendForm(new addWarpForm());
        }
        if($data === 1){
            $player->sendForm(new removeWarpForm());
            return;
        }
    }
}