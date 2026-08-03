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


class UseItemPacket extends DataPacket{
	const NETWORK_ID = Info::USE_ITEM_PACKET;

	public $x;
	public $y;
	public $z;
	public $blockId;
	public $face;
	public $item;
	public $fx;
	public $fy;
	public $fz;
	public $posX;
	public $posY;
	public $posZ;
	public $slot;

	public function decode($protocol){
		$this->getBlockCoords($this->x, $this->y, $this->z);
		$this->blockId = in_array($protocol, Info::ACCEPTED_PROTOCOLS_LESS_91) ? 0 : $this->getUnsignedVarInt();
		$this->face = $this->getVarInt();
		$this->getVector3f($this->fx, $this->fy, $this->fz);
		$this->getVector3f($this->posX, $this->posY, $this->posZ);
		$this->slot = $this->getVarInt();
		$this->item = $this->getSlot();
	}

	public function encode($protocol){

	}
}
