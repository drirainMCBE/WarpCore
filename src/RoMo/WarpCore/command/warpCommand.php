<?php

namespace RoMo\WarpCore\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use RoMo\WarpCore\form\listWarpForm;
use RoMo\WarpCore\WarpCore;

class warpCommand extends Command{

    public function __construct(){
        $cmd = WarpCore::getCmd("use.warp");
        parent::__construct($cmd["name"], $cmd["description"], $cmd["usageMessage"], $cmd["aliases"]);
        $this->setPermission("use-warp");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            return;
        }
        if(!$sender instanceof Player){
            $sender->sendMessage(WarpCore::getMessage("must.do.in.game"));
            return;
        }
        $sender->sendForm(new listWarpForm());
    }
}