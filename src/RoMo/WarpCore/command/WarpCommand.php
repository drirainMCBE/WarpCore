<?php

declare(strict_types=1);

namespace RoMo\WarpCore\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use RoMo\WarpCore\menu\Menu;
use RoMo\WarpCore\menu\MenuFactory;
use RoMo\WarpCore\warp\WarpFactory;
use RoMo\WarpCore\WarpCore;

class WarpCommand extends Command implements PluginOwned{

    use PluginOwnedTrait;

    private Menu $mainMenu;

    public function __construct(){
        $translator = WarpCore::getTranslator();
        $cmd = $translator->getCmd("warp");
        parent::__construct($cmd->getName(), $cmd->getDescription(), $cmd->getUsage(), $cmd->getAliases());
        $this->setPermission("warpcore.use.warp");
        $this->mainMenu = MenuFactory::getInstance()->getMainMenu();

        $this->owningPlugin = WarpCore::getInstance();
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        $translator = WarpCore::getTranslator();
        if(!$sender instanceof Player){
            $sender->sendMessage($translator->getMessage("must.do.in.game"));
            return;
        }
        if(!isset($args[0])){
            $sender->sendForm($this->mainMenu->getWarpMenuForm());
            return;
        }
        if(!WarpFactory::getInstance()->isExistWarp($args[0])){
            $sender->sendMessage($translator->getMessage("fail.to.find.warp", [$args[0]]));
            return;
        }
        $warp = WarpFactory::getInstance()->getWarp($args[0]);
        if(!$warp->isCommandRegister()){
            $sender->sendMessage($translator->getMessage("fail.to.find.warp", [$warp->getName()]));
            return;
        }
        $warp->teleport($sender);
    }
}