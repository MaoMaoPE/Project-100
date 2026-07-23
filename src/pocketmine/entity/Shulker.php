<?php
namespace pocketmine\entity;
/*
 * 
 *  ____                     _                 _             _    ___     ___  
 * |  _ \   _ __    ___     (_)   ___    ___  | |_          / |  / _ \   / _ \ 
 * | |_) | | '__|  / _ \    | |  / _ \  / __| | __|  _____  | | | | | | | | | |
 * |  __/  | |    | (_) |   | | |  __/ | (__  | |_  |_____| | | | |_| | | |_| |
 * |_|     |_|     \___/   _/ |  \___|  \___|  \__|         |_|  \___/   \___/ 
 *                        |__/                                                 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author MaoMaoPE Team
 * @link https://github.com/MaoMaoPE/Project-100
 *
 * 
*/
//潜影贝

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\nbt\tag\{CompoundTag, ByteTag};
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Shulker extends Monster implements Colorable{
    const NETWORK_ID = 54;

    public $width = 1;
    public $height = 1;

    public $dropExp = [5, 5];

    public function getName(): string {
        return "Shulker";
    }

    public function __construct(Level $level, CompoundTag $nbt) {
        parent::__construct($level, $nbt);
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
        $pk->speedX = $this->motionX;
        $pk->speedY = $this->motionY;
        $pk->speedZ = $this->motionZ;
        $pk->yaw = $this->yaw;
        $pk->pitch = $this->pitch;
        $pk->metadata = $this->dataProperties;
        $player->dataPacket($pk);

        parent::spawnTo($player);
    }

    public function attack($damage, EntityDamageEvent $source): bool {
        parent::attack($damage, $source);
    
        if(!$source->isCancelled()) {
            if(mt_rand(1, 10) == 1) {
                $this->level->addSound(new EndermanTeleportSound($this));
                $this->move(mt_rand(-10, 10), 0, mt_rand(-10, 10));
            }
        }

        return true;
    }
}