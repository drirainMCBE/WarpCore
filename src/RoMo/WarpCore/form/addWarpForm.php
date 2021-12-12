<?php

namespace RoMo\WarpCore\form;

use pocketmine\form\Form;
use pocketmine\player\Player;
use RoMo\WarpCore\warp\Warp;
use RoMo\WarpCore\warp\WarpFactory;
use RoMo\WarpCore\WarpCore;

class addWarpForm implements Form{
    public function jsonSerialize() : array{
        return [
            "type" => "custom_form",
            "title" => WarpCore::getPrefix(),
            "content" => [
                [
                    "type" => "input",
                    "text" => WarpCore::getTranslate("input.write.warp.name")
                ]
            ]
        ];
    }

    public function handleResponse(Player $player, $data) : void{
        if($data === null){
            return;
        }
        if(!isset($data[0]) || $data[0] === ""){
            $player->sendMessage(WarpCore::getMessage("write.warp.name"));
            return;
        }
        if(!WarpFactory::getInstance()->addWarp(($warp = new Warp($data[0], $player->getLocation())))){
            $player->sendMessage(WarpCore::getMessage("already.add.warp"));
            return;
        }
        $player->sendMessage(WarpCore::getMessage("success.add.warp", [$warp->getName()]));
    }
}