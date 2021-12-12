<?php

namespace RoMo\WarpCore\form;

use pocketmine\form\Form;
use pocketmine\player\Player;
use RoMo\WarpCore\warp\Warp;
use RoMo\WarpCore\warp\WarpFactory;
use RoMo\WarpCore\WarpCore;

class listWarpForm implements Form{

    /** @var Warp[] */
    protected array $warps = [];

    public function __construct(){
        $this->warps = array_values(WarpFactory::getInstance()->getAllWarps());
    }

    public function jsonSerialize() : array{
        $buttons = [];
        foreach($this->warps as $warp){
            $buttons[] = ["text" => WarpCore::getTranslate("warp.list.1", [$warp->getName()]) . "\n" . WarpCore::getTranslate("warp.list.2", [$warp->getName()])];
        }
        return [
            "type" => "form",
            "title" => WarpCore::getPrefix(),
            "content" => WarpCore::getTranslate("choose.to.warp"),
            "buttons" => $buttons
        ];
    }

    public function handleResponse(Player $player, $data) : void{
        if($data === null){
            return;
        }
        $warp = $this->warps[$data];
        $warp->teleport($player);
    }
}