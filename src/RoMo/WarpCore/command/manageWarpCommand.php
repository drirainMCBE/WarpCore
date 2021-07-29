<?php

namespace RoMo\WarpCore\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use RoMo\WarpCore\form\manageWarpForm;
use RoMo\WarpCore\WarpCore;

class manageWarpCommand extends Command{
    public function __construct(){
        $cmd = WarpCore::getCmd("manage.warp");
        parent::__construct($cmd["name"], $cmd["description"], $cmd["usageMessage"], $cmd["aliases"]);
        $this->setPermission("manage-warp");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            return;
        }
        if(!$sender instanceof Player){
            $sender->sendMessage(WarpCore::getMessage("must.do.in.game"));
            return;
        }
        $sender->sendForm(new manageWarpForm());
    }
}