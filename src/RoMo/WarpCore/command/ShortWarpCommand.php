<?php

namespace RoMo\WarpCore\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use RoMo\WarpCore\warp\Warp;
use RoMo\WarpCore\WarpCore;

class ShortWarpCommand extends Command{

    /** @var Warp */
    protected Warp $warp;

    public function __construct(Warp $warp){
        parent::__construct($warp->getName(), WarpCore::getTranslate("command.short.warp.description", [$warp->getName()]));
        $this->warp = $warp;
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

    }
}