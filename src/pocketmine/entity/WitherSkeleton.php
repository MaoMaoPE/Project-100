<?php
/*
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
namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item as ItemItem;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\MobEquipmentPacket;
use pocketmine\Player;

class WitherSkeleton extends Monster implements ProjectileSource {
    const NETWORK_ID = 48;

    public $width = 0.6;
	public $length = 0.6;
	public $height = 1.97;

    public $dropExp = [5, 5];

    public function getName() : string{
		return "Wither Skeleton";
	}

    public function initEntity(){
		$this->setMaxHealth(20);
		parent::initEntity();
	}

    public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = self::NETWORK_ID;
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
		
		$pk = new MobEquipmentPacket();
		$pk->eid = $this->getId();
		$pk->item = new ItemItem(ItemItem::STONE_SWORD);
		$pk->slot = 0;
		$pk->selectedSlot = 0;

		$player->dataPacket($pk);
	}

    public function getDrops(){
		$cause = $this->lastDamageCause;
		if($cause instanceof EntityDamageByEntityEvent){
			$damager = $cause->getDamager();
			if($damager instanceof Player){
				$lootingL = $damager->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_LOOTING);
				$drops = [ItemItem::get(ItemItem::COAL, 0, mt_rand(0, 2 + $lootingL))];
				$drops[] = ItemItem::get(ItemItem::BONE, 0, mt_rand(0, 2 + $lootingL));

				return $drops;
			}
		}

		return [];
	}
}