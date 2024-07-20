<?php

declare(strict_types=1);

namespace RoMo\WarpCore;

use alemiz\sga\client\StarGateClient;
use alemiz\sga\events\ClientAuthenticatedEvent;
use customiesdevs\customies\entity\CustomiesEntityFactory;
use kim\present\sqlcore\SqlCore;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\event\EventPriority;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use RoMo\Translator\Translator;
use RoMo\Translator\TranslatorHolderTrait;
use RoMo\WarpCore\command\ManageWarpCommand;
use RoMo\WarpCore\command\WarpCommand;
use RoMo\WarpCore\entity\WarpEffectEntity;
use RoMo\WarpCore\menu\MenuFactory;
use RoMo\WarpCore\protocol\WarpClientConnectPacket;
use RoMo\WarpCore\protocol\UpdateWarpPacket;
use RoMo\WarpCore\protocol\WarpRequestPacket;
use RoMo\WarpCore\warp\WarpFactory;

class WarpCore extends PluginBase{

    use SingletonTrait;
    use TranslatorHolderTrait;

    private DataConnector $database;

    private StarGateClient $starGateClient;
    private string $serverName = "";

    public function onLoad() : void{
        self::$instance = $this;
    }

    public function onEnable() : void{
        $this->saveDefaultConfig();

        //INIT TRANSLATOR
        self::setTranslator(new Translator($this, $this->getFile(), $this->getDataFolder(), $this->getConfig()->get("language")));

        //CREATE A CONNECTION WITH MYSQL
        if(class_exists(SqlCore::class)){
            $this->database = libasynql::create($this, SqlCore::getSqlConfig(), [
                "mysql" => "mysql.sql"
            ]);
        }else{
            $this->database = libasynql::create($this, $this->getConfig()->get("database"), [
                "mysql" => "mysql.sql"
            ]);
        }

        //REGISTER ENTITY
        CustomiesEntityFactory::getInstance()->registerEntity(WarpEffectEntity::class, WarpEffectEntity::getNetworkTypeId());

        WarpFactory::init();

        $this->saveResource("menu.json");

        $this->getServer()->getPluginManager()->registerEvent(ClientAuthenticatedEvent::class, function(ClientAuthenticatedEvent $event) : void{
            $this->starGateClient = $event->getClient();
            $this->serverName = $this->starGateClient->getClientName();

            $codec = $this->starGateClient->getProtocolCodec();
            $codec->registerPacket(0x1d, new WarpClientConnectPacket());
            $codec->registerPacket(0x1e, new UpdateWarpPacket());
            $codec->registerPacket(0x1f, new WarpRequestPacket());

            $packet = new WarpClientConnectPacket();
            $packet->setClientName($this->serverName);
            $this->starGateClient->sendPacket($packet);
        }, EventPriority::NORMAL, $this);
    }

    public function onCompleteToLoadAllWarps() : void{
        MenuFactory::init();
        $this->getServer()->getCommandMap()->registerAll("WarpCore", [
            new ManageWarpCommand(),
            new WarpCommand()
        ]);
    }

    protected function onDisable() : void{
        $this->database->close();
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

    /**
     * @return StarGateClient
     */
    public function getStarGateClient() : StarGateClient{
        return $this->starGateClient;
    }
}