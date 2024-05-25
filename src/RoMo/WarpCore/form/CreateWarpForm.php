<?php

declare(strict_types=1);

namespace RoMo\WarpCore\form;

use pocketmine\form\Form;
use pocketmine\player\Player;
use RoMo\WarpCore\warp\Warp;
use RoMo\WarpCore\warp\WarpFactory;
use RoMo\WarpCore\WarpCore;
use Generator;
use SOFe\AwaitGenerator\Await;
use Throwable;

class CreateWarpForm implements Form{
    public function jsonSerialize() : array{
        $translator = WarpCore::getTranslator();
        return [
            "type" => "custom_form",
            "title" => $translator->getTranslate("form.title"),
            "content" => [
                [
                    "type" => "input",
                    "text" => $translator->getTranslate("warp.name.input")
                ],
                [
                    "type" => "toggle",
                    "text" => $translator->getTranslate("is.title.toggle"),
                    "default" => true
                ],
                [
                    "type" => "toggle",
                    "text" => $translator->getTranslate("is.particle.toggle"),
                    "default" => true
                ],
                [
                    "type" => "toggle",
                    "text" => $translator->getTranslate("is.sound.toggle"),
                    "default" => true
                ],
                [
                    "type" => "toggle",
                    "text" => $translator->getTranslate("is.permit.toggle"),
                    "default" => true
                ],
                [
                    "type" => "toggle",
                    "text" => $translator->getTranslate("is.command.register.toggle"),
                    "default" => true
                ]
            ]
        ];
    }
    public function handleResponse(Player $player, $data) : void{
        if($data === null){
            $player->sendForm(new ManageWarpForm());
            return;
        }
        $translator = WarpCore::getTranslator();
        if(!isset($data[0]) || $data[0] == ""){
            $player->sendMessage($translator->getMessage("should.do.input.warp.name"));
            return;
        }
        $data[0] = (string) $data[0];
        if(WarpFactory::getInstance()->isExistWarp($data[0])){
            $player->sendMessage($translator->getMessage("already.exist.warp"));
            return;
        }
        $location = $player->getLocation();
        $warp = new Warp($data[0], WarpCore::getInstance()->getServerName(), $location->getWorld()->getFolderName(), $location->asVector3(), $location->getYaw(), $location->getPitch(), $data[1], $data[2], $data[3], $data[4], $data[5]);

        Await::f2c(function() use ($player, $warp, $translator) : Generator{
            try{
                yield from WarpFactory::getInstance()->addWarp($warp);
            }catch(Throwable $e){
                $player->sendMessage($e->getMessage());
                return;
            }

            $player->sendMessage($translator->getMessage("success.create.warp", [$warp->getName()]));
        });
    }
}