<?php

declare(strict_types=1);

namespace RoMo\WarpCore\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use RoMo\WarpCore\form\ManageWarpForm;
use RoMo\WarpCore\WarpCore;

class ManageWarpCommand extends Command implements PluginOwned{

    use PluginOwnedTrait;

    public function __construct(){
        $cmd = WarpCore::getTranslator()->getCmd("manage.warp");
        parent::__construct($cmd->getName(), $cmd->getDescription(), $cmd->getUsage(), $cmd->getAliases());
        $this->setPermission("warpcore.manage.warp");

        $this->owningPlugin = WarpCore::getInstance();
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
        $translator = WarpCore::getTranslator();
        if(!$sender instanceof Player){
            $sender->sendMessage($translator->getMessage("must.do.in.game"));
            return;
        }
        $sender->sendForm(new ManageWarpForm());
    }
}