<?php

declare(strict_types=1);

namespace RoMo\WarpCore\protocol;

use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\protocol\types\PacketHelper;

class WarpClientConnectPacket extends StarGatePacket{

    private string $clientName;

    public function encodePayload() : void{
        PacketHelper::writeString($this, $this->clientName);
    }
    public function decodePayload() : void{
        //NOTHING
    }

    public function getPacketId() : int{
        return 0x1d;
    }

    /**
     * @param string $clientName
     */
    public function setClientName(string $clientName) : void{
        $this->clientName = $clientName;
    }
}