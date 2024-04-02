<?php

namespace RoMo\WarpCore\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use RoMo\WarpCore\warp\Warp;
use RoMo\WarpCore\WarpCore;

class ShortWarpCommand extends Command implements PluginOwned{

    use PluginOwnedTrait;

    /** @var Warp */
    private Warp $warp;

    public function __construct(Warp $warp){
        $this->warp = $warp;
        parent::__construct($this->warp->getName(), WarpCore::getTranslator()->getTranslate("command.short.warp.description", [$this->warp->getName()]), "/" . $this->warp->getName());
        $this->setPermission("warpcore.use.warp");

        $this->owningPlugin = WarpCore::getInstance();
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
        if(!$sender instanceof Player){
            $sender->sendMessage(WarpCore::getTranslator()->getMessage("must.do.in.game"));
            return;
        }
        $this->warp->teleport($sender);
    }
}