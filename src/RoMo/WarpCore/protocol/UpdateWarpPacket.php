<?php

declare(strict_types=1);

namespace RoMo\WarpCore\protocol;

use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\protocol\types\PacketHelper;
use RoMo\WarpCore\WarpCore;

class UpdateWarpPacket extends StarGatePacket{

    const CREATE = 0;
    const EDIT = 1;
    const REMOVE = 2;

    private String $serverName;
    private string $warpName;
    private int $updateType;

    public function encodePayload() : void{
        PacketHelper::writeString($this, WarpCore::getInstance()->getServerName());
        PacketHelper::writeString($this, $this->warpName);
        PacketHelper::writeInt($this, $this->updateType);
    }
    public function decodePayload() : void{
        $this->serverName = PacketHelper::readString($this);
        $this->warpName = PacketHelper::readString($this);
        $this->updateType = PacketHelper::readInt($this);
    }

    public function getPacketId() : int{
        return 0x1e;
    }

    /**
     * @param string $warpName
     */
    public function setWarpName(string $warpName) : void{
        $this->warpName = $warpName;
    }

    /**
     * @param int $updateType
     */
    public function setUpdateType(int $updateType) : void{
        $this->updateType = $updateType;
    }
}