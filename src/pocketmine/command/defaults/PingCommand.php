<?php

/* 
 *  ____                  _                        ____   _____ 
 * / ___|   ___   _ __   (_)  ___   _   _   ___   / ___| |_   _|
 * | |  _   / _ \ | '_ \  | | / __| | | | | / __| | |  _    | |  
 * | |_| | |  __/ | | | | | | \__ \ | |_| | \__ \ | |_| |   | |  
 *  \____|  \___| |_| |_| |_| |___/  \__, | |___/  \____|   |_|  
 *  								 |___/                       
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author GenisysGT
 * @link https://github.com/MaoMaoPE/Project-100
 *
 * 注:部分代码是基于Glowstone核心
*/
namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use function count;

class PingCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%OBS.command.ping.description",
			"%commands.ping.usage",
			["connection"]
		);
		$this->setPermission("pocketmine.command.ping");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender) or !$sender instanceof Player){
			return true;
		}

		$target = null;

		if(count($args) === 1){
			$target = $sender->getServer()->getPlayer($args[0]);
		}

		if($target == null){
			if($sender instanceof Player){
				$target = $sender;
			}else{
				$sender->sendMessage(TextFormat::RED . "Please provide a player!");

				return true;
			}
		}

		$ping = $target->getPing();
		$color = TextFormat::GREEN;

		if($ping >= 150 && $ping <= 250){
			$color = TextFormat::GOLD;
		}elseif($ping > 250){
			$color = TextFormat::RED;
		}

		$sender->sendMessage($target->getName() . "'s Ping: " . $color . $ping . "ms");
		return true;
	}
}
