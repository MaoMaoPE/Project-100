<?php
namespace pocketmine\entity;

//潜影贝
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Shulker extends Monster {
    const NETWORK_ID = 54;

    public $width = 1.0;
    public $height = 1.0;

    public $dropExp = [5, 5];

    public function getName(): string {
        return "Shulker";
    }

    public function initEntity(){
		$this->setMaxHealth(30);
		parent::initEntity();
	}

    public function spawnTo(Player $player) {
        $pk = new AddEntityPacket();
        $pk->eid = self::NETWORK_ID;
        $pk->x = $this->x;
        $pk->y = $this->y;
        $pk->z = $this->z;
        
        $pk->yaw = $this->yaw;
        $pk->pitch = $this->pitch;
        $pk->metadata = $this->dataProperties;
        $player->dataPacket($pk);

        parent::spawnTo($player);
    }
}