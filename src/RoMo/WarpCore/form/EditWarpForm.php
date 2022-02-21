<?php

declare(strict_types=1);

namespace RoMo\WarpCore\form;

use pocketmine\form\Form;
use pocketmine\player\Player;
use RoMo\WarpCore\warp\Warp;
use RoMo\WarpCore\WarpCore;

class EditWarpForm implements Form{

    /** @var Warp */
    private Warp $warp;

    public function __construct(Warp $warp){
        $this->warp = $warp;
    }

    public function jsonSerialize() : array{
        $translator = WarpCore::getTranslator();
        return [
            "type" => "custom_form",
            "title" => $translator->getTranslate("form.title"),
            "content" => [
                [
                    "type" => "toggle",
                    "text" => $translator->getTranslate("is.title.toggle"),
                    "default" => $this->warp->isTitle()
                ],
                [
                    "type" => "toggle",
                    "text" => $translator->getTranslate("is.particle.toggle"),
                    "default" => $this->warp->isParticle()
                ],
                [
                    "type" => "toggle",
                    "text" => $translator->getTranslate("is.sound.toggle"),
                    "default" => $this->warp->isSound()
                ],
                [
                    "type" => "toggle",
                    "text" => $translator->getTranslate("is.permit.toggle"),
                    "default" => $this->warp->isPermit()
                ],
                [
                    "type" => "toggle",
                    "text" => $translator->getTranslate("is.command.register.toggle"),
                    "default" => $this->warp->isCommandRegister()
                ]
            ]
        ];
    }

    public function handleResponse(Player $player, $data) : void{
        if($data === null){
            $player->sendForm(new EditWarpListForm());
            return;
        }
        $this->warp->setIsTitle($data[0]);
        $this->warp->setIsParticle($data[1]);
        $this->warp->setIsSound($data[2]);
        $this->warp->setIsPermit($data[3]);
        $this->warp->setIsCommandRegister($data[4]);
        $player->sendMessage(WarpCore::getTranslator()->getMessage("success.edit.warp", [$this->warp->getName()]));
    }
}