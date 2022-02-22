<?php

declare(strict_types=1);

namespace RoMo\WarpCore;

use JsonException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use RoMo\Translator\Translator;
use RoMo\Translator\TranslatorHolderTrait;
use RoMo\WarpCore\category\CategoryFactory;
use RoMo\WarpCore\command\ManageWarpCommand;
use RoMo\WarpCore\command\WarpCommand;
use RoMo\WarpCore\warp\WarpFactory;

class WarpCore extends PluginBase{

    use SingletonTrait;
    use TranslatorHolderTrait;

    public function onLoad() : void{
        self::$instance = $this;
    }

    public function onEnable() : void{
        self::setTranslator(new Translator($this, $this->getFile(), $this->getDataFolder(), "kor", true));
        WarpFactory::init();
        CategoryFactory::init();
        $this->getServer()->getCommandMap()->registerAll("WarpCore", [
            new ManageWarpCommand(),
            new WarpCommand()
        ]);
    }

    /** @throws JsonException */
    public function onDisable() : void{
        WarpFactory::getInstance()->save();
        CategoryFactory::getInstance()->save();
    }
}