<?php

declare(strict_types=1);

namespace RoMo\WarpCore\form;

use pocketmine\form\Form;
use pocketmine\player\Player;
use RoMo\WarpCore\WarpCore;

class ManageWarpForm implements Form{
    public function jsonSerialize() : array{
        $translator = WarpCore::getTranslator();
        return [
            "type" => "form",
            "title" => $translator->getTranslate("form.title"),
            "content" => "",
            "buttons" => [
                [
                    "text" => $translator->getTranslate("warp.create.button")
                ],
                [
                    "text" => $translator->getTranslate("warp.remove.button")
                ],
                [
                    "text" => $translator->getTranslate("warp.edit.button")
                ]
            ]
        ];
    }
    public function handleResponse(Player $player, $data) : void{
        if($data === null){
            return;
        }
        if($data === 0){
            $player->sendForm(new CreateWarpForm());
            return;
        }
        if($data === 1){
            $player->sendForm(new RemoveWarpForm());
            return;
        }
        if($data === 2){
            $player->sendForm(new EditWarpListForm());
            return;
        }
    }
}