<?php

declare(strict_types=1);

namespace RoMo\WarpCore;

use alemiz\sga\events\ClientAuthenticatedEvent;
use pocketmine\event\EventPriority;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use RoMo\Translator\Translator;
use RoMo\Translator\TranslatorHolderTrait;
use RoMo\WarpCore\command\ManageWarpCommand;
use RoMo\WarpCore\command\WarpCommand;
use RoMo\WarpCore\menu\MenuFactory;
use RoMo\WarpCore\protocol\WarpClientConnectPacket;
use RoMo\WarpCore\protocol\UpdateWarpPacket;
use RoMo\WarpCore\protocol\WarpRequestPacket;
use RoMo\WarpCore\warp\WarpFactory;

class WarpCore extends PluginBase{

    use SingletonTrait;
    use TranslatorHolderTrait;

    private DataConnector $database;

    private string $serverName = "";

    public function onLoad() : void{
        self::$instance = $this;
    }

    public function onEnable() : void{
        $this->saveDefaultConfig();
        self::setTranslator(new Translator($this, $this->getFile(), $this->getDataFolder(), $this->getConfig()->get("language")));
        $this->database = libasynql::create($this, $this->getConfig()->get("database"), [
            "mysql" => "mysql.sql"
        ]);
        WarpFactory::init();

        $this->getServer()->getPluginManager()->registerEvent(ClientAuthenticatedEvent::class, function(ClientAuthenticatedEvent $event) : void{
            $client = $event->getClient();
            $this->serverName = $client->getClientName();

            $codec = $event->getClient()->getProtocolCodec();
            $codec->registerPacket(0x1d, new WarpClientConnectPacket());
            $codec->registerPacket(0x1e, new UpdateWarpPacket());
            $codec->registerPacket(0x1f, new WarpRequestPacket());

            $packet = new WarpClientConnectPacket();
            $packet->setClientName($this->serverName);
            $client->sendPacket($packet);
        }, EventPriority::NORMAL, $this);
    }

    public function onCompleteToLoadAllWarps() : void{
        MenuFactory::init();
        $this->getServer()->getCommandMap()->registerAll("WarpCore", [
            new ManageWarpCommand(),
            new WarpCommand()
        ]);
    }

    /**
     * @return DataConnector
     */
    public function getDatabase() : DataConnector{
        return $this->database;
    }

    /**
     * @return string
     */
    public function getServerName() : string{
        return $this->serverName;
    }
}