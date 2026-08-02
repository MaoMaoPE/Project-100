<?php

namespace pocketmine\network\protocol\p91;

use pocketmine\resourcepacks\ResourcePackInfoEntry;
use pocketmine\network\protocol\DataPacket;

class ResourcePacksInfoPacket extends DataPacket{
    const NETWORK_ID = 0x07;

	public $mustAccept = false; //force client to use selected resource packs
	/** @var ResourcePackInfoEntry */
	public $behaviourPackEntries = [];
	/** @var ResourcePackInfoEntry */
	public $resourcePackEntries = [];

	public function decode($protocol){

	}

	public function encode($protocol){
		$this->reset();

		$this->putBool($this->mustAccept);
		$this->putShort(count($this->behaviourPackEntries));
		foreach($this->behaviourPackEntries as $entry){
			$this->putString($entry->getPackId());
			$this->putString($entry->getVersion());
			$this->putLong($entry->getPackSize());
		}
		$this->putShort(count($this->resourcePackEntries));
		foreach($this->resourcePackEntries as $entry){
			$this->putString($entry->getPackId());
			$this->putString($entry->getVersion());
			$this->putLong($entry->getPackSize());
		}
	}
}