<?php

namespace RoMo\WarpCore;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use RoMo\WarpCore\command\manageWarpCommand;
use RoMo\WarpCore\lib\translateTrait;

class WarpCore extends PluginBase{

    use SingletonTrait;
    use translateTrait;

    public function onEnable() : void{
        self::initMessage("kor");
        $this->getServer()->getCommandMap()->register("WarpCore", new manageWarpCommand());
    }
}