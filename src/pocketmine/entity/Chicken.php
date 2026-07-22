<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item as ItemItem;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Chicken extends Animal{
	const NETWORK_ID = 10;

	public $width = 0.6;
	public $length = 0.6;
	public $height = 0.6;
	public $speed = 1.0;

	private $moveDirection = null; //移动方向
	private $tempTicker = 0;
	private $tempTicking = false;
	private $moveTicker = 0; //运动计时器

	public $dropExp = [1, 3];
	
	public function getName() : string{
		return "Chicken";
	}

	protected function initEntity() {
		$this->setMaxHealth(4);
		return parent::initEntity();
	}
	
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Chicken::NETWORK_ID;
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

	private function generateRandomDirection(){ //运动
		return new Vector3(mt_rand(-1000, 1000) / 1000, 0, mt_rand(-1000, 1000) / 1000);
	}

	private function getVelY(){
		$expectedPos = (new Vector3($this->x + $this->moveDirection->x * $this->speed, $this->y + $this->motionY, $this->z + $this->moveDirection->z * $this->moveSpeed))->round();
		$block0 = $this->getLevel()->getBlock($expectedPos);
		$block1 = $this->getLevel()->getBlock($expectedPos->add(0, 1, 0));
		if($block1->getId() != 0) return 1.2;
		return 0;
	}

	public function onUpdate($tick) { //TODO 鸡的AI
		if (!$this->closed !== false) return false;

		return parent::onUpdate($tick);
		$hasUpdate = parent::onUpdate($tick);

		if($this->motionX ** 2 + $this->motionZ ** 2 <= $this->moveDirection->lengthSquared()){
			$motionY = $this->getVelY(); //僵尸运动计算
			if($motionY >= 0){
				$this->motionX = $this->moveDirection->x * $this->speed;
				$this->motionZ = $this->moveDirection->z * $this->speed;
				$this->motionY = $motionY;
			}else{
				$this->moveDirection = $this->generateRandomDirection(); //生成随机运动方向
				$this->moveTicker = 0;
				$this->tempTicking = true;
			}
		}
		return $hasUpdate or !$this->onGround or abs($this->motionX) > 0.00001 or abs($this->motionY) > 0.00001 or abs($this->motionZ) > 0.00001;
	}
	
	public function getDrops(){
		$cause = $this->lastDamageCause;
		if($cause instanceof EntityDamageByEntityEvent){
			$damager = $cause->getDamager();
			if($damager instanceof Player){
				$lootingL = $damager->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_LOOTING);
				$drops = [ItemItem::get(ItemItem::RAW_CHICKEN, 0, mt_rand(1, 3 + $lootingL))];
				$drops[] = ItemItem::get(ItemItem::FEATHER, 0, mt_rand(0, 2 + $lootingL));

				return $drops;
			}
		}

		return [];
	}
}