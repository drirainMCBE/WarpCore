<?php

declare(strict_types=1);

namespace RoMo\WarpCore\form;

use pocketmine\form\Form;
use pocketmine\player\Player;
use RoMo\WarpCore\warp\Warp;
use RoMo\WarpCore\warp\WarpFactory;
use RoMo\WarpCore\WarpCore;
use SOFe\AwaitGenerator\Await;
use Generator;
use Throwable;

class RemoveWarpForm implements Form{

    /** @var Warp[] */
    private array $warpsForButton;

    public function jsonSerialize() : array{
        $translator = WarpCore::getTranslator();
        $buttons = [];
        foreach(WarpFactory::getInstance()->getAllWarp() as $warp){
            $this->warpsForButton[] = $warp;
            $buttons[] = ["text" => $translator->getTranslate("remove.warp.button", [$warp->getName()])];
        }
        return [
            "type" => "form",
            "title" => $translator->getTranslate("form.title"),
            "content" => "",
            "buttons" => $buttons
        ];
    }
    public function handleResponse(Player $player, $data) : void{
        if($data === null){
            $player->sendForm(new ManageWarpForm());
            return;
        }
        $translator = WarpCore::getTranslator();
        if(!isset($this->warpsForButton[$data])){
            $player->sendMessage($translator->getMessage("error.to.find.warp"));
            return;
        }
        $warp = $this->warpsForButton[$data];

        Await::f2c(function() use ($warp, $player, $translator) : Generator{
            try{
                yield from WarpFactory::getInstance()->removeWarp($warp);
            }catch(Throwable $e){
                $player->sendMessage($e->getMessage());
                return;
            }

            $player->sendMessage($translator->getMessage("success.remove.warp", [$warp->getName()]));
        });
    }
}