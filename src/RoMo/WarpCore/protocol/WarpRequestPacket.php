<?php

declare(strict_types=1);

namespace RoMo\WarpCore\protocol;

use alemiz\sga\codec\StarGatePacketHandler;
use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\protocol\types\PacketHelper;
use RoMo\WarpCore\warp\WarpFactory;

class WarpRequestPacket extends StarGatePacket{

    private string $serverName;
    private string $warpName;
    private string $playerName;

    public function encodePayload() : void{
        PacketHelper::writeString($this, $this->serverName);
        PacketHelper::writeString($this, $this->warpName);
        PacketHelper::writeString($this, $this->playerName);
    }
    public function decodePayload() : void{
        $this->serverName = PacketHelper::readString($this);
        $this->warpName = PacketHelper::readString($this);
        $this->playerName = PacketHelper::readString($this);
    }

    public function getPacketId() : int{
        return 0x1f;
    }

    /**
     * @return string
     */
    public function getServerName() : string{
        return $this->serverName;
    }

    /**
     * @param string $serverName
     */
    public function setServerName(string $serverName) : void{
        $this->serverName = $serverName;
    }

    /**
     * @return string
     */
    public function getWarpName() : string{
        return $this->warpName;
    }

    /**
     * @param string $warpName
     */
    public function setWarpName(string $warpName) : void{
        $this->warpName = $warpName;
    }

    /**
     * @return string
     */
    public function getPlayerName() : string{
        return $this->playerName;
    }

    /**
     * @param string $playerName
     */
    public function setPlayerName(string $playerName) : void{
        $this->playerName = $playerName;
    }

    public function handle(StarGatePacketHandler $handler) : bool{
        WarpFactory::getInstance()->onWarpRequest($this);
        return true;
    }
}