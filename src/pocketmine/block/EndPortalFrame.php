<?php

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

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;

class EndPortalFrame extends Solid implements SolidLight {

    protected $id = self::END_PORTAL_FRAME;

    public function __construct($meta = 0){
        $this->meta = $meta & 0x03;
    }

    public function getLightLevel(){
        return 1;
    }

    public function getName() : string{
        return "End Portal Frame";
    }

    public function getHardness(){
        return -1;
    }

    public function getResistance(){
        return 18000000;
    }

    public function isBreakable(Item $item){
        return false;
    }

    protected function recalculateBoundingBox(){
        return new AxisAlignedBB(
            $this->x,
            $this->y,
            $this->z,
            $this->x + 1,
            $this->y + 0.8125,
            $this->z + 1
        );
    }

    public function getDirection(){
        return $this->meta & 0x03;
    }

    public function setDirection($direction){
        $this->meta = ($this->meta & ~0x03) | ($direction & 0x03);
    }

    public function canBeActivated() : bool{
        return false;
    }

    public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
        if($player instanceof Player){
            $this->setDirection(($player->getDirection() + 3) & 0x03);
        }
        $this->getLevel()->setBlock($block, $this, true, true);
        return true;
    }

    public function onActivate(Item $item, Player $player = null){
        return false;
    }

    public function getDrops(Item $item) : array{
        return [];
    }
}
