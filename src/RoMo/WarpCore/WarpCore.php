<?php

namespace RoMo\WarpCore;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use RoMo\WarpCore\command\manageWarpCommand;
use RoMo\WarpCore\command\warpCommand;
use RoMo\WarpCore\lib\translateTrait;
use RoMo\WarpCore\warp\WarpFactory;

class WarpCore extends PluginBase{

    use SingletonTrait;
    use translateTrait;

    public function onLoad() : void{
        self::$instance = $this;
    }

    public function onEnable() : void{
        self::initMessage("kor");
        $this->saveResource("warps.json");
        WarpFactory::init();
        $this->getServer()->getCommandMap()->registerAll("WarpCore", [
            new manageWarpCommand(),
            new warpCommand()
        ]);
    }

    public function onDisable() : void{
        WarpFactory::getInstance()->save();
    }
}