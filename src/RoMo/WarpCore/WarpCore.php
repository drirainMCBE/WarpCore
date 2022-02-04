<?php

declare(strict_types=1);

namespace RoMo\WarpCore;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use RoMo\Translator\Translator;
use RoMo\Translator\TranslatorHolderTrait;
use RoMo\WarpCore\warp\WarpFactory;

class WarpCore extends PluginBase{

    use SingletonTrait;
    use TranslatorHolderTrait;

    public function onEnable() : void{
        self::setTranslator(new Translator($this, $this->getFile(), $this->getDataFolder(), "kor", true));
        WarpFactory::init();
    }
}