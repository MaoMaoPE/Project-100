<?php

/*
 * 
 *  ____                          _       _                
 * / ___|   _   _   _ __    ___  | |__   (_)  _ __     ___ 
 * \___ \  | | | | | '_ \  / __| | '_ \  | | | '_ \   / _ \
 *  ___) | | |_| | | | | | \__ \ | | | | | | | | | | |  __/
 * |____/   \__,_| |_| |_| |___/ |_| |_| |_| |_| |_|  \___|
 *                                                               
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author MaoMaoPE Team
 * @link https://github.com/MaoMaoPE/Sunshine
 *
 * 
*/

namespace pocketmine\network\protocol;

#include <rules/DataPacket.h>
use pocketmine\utils\ChunkNetworkConverter;

class FullChunkDataPacket extends DataPacket{
	const NETWORK_ID = Info::FULL_CHUNK_DATA_PACKET;

	public $chunkX;
	public $chunkZ;
	public $data;

	public function decode($protocol){

	}

	public function encode($protocol){
		$this->reset($protocol);
		$this->putVarInt($this->chunkX);
		$this->putVarInt($this->chunkZ);
		if (in_array($protocol, Info::ACCEPTED_PROTOCOLS_LESS_91)) {
		    $this->putByte(0); //ORDER_COLUMNS
		    $this->putString(ChunkNetworkConverter::convertToP91($this->data));
		} else {
		    $this->putString($this->data);
		}
	}

}
