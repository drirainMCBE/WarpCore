<?php

namespace RoMo\WarpCore;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use RoMo\WarpCore\command\manageWarpCommand;
use RoMo\WarpCore\lib\translateTrait;
use RoMo\WarpCore\warp\WarpFactory;

class WarpCore extends PluginBase{

    use SingletonTrait;
    use translateTrait;

    public function onEnable() : void{
        self::initMessage("kor");
        WarpFactory::init();
        $this->getServer()->getCommandMap()->register("WarpCore", new manageWarpCommand());
    }
}