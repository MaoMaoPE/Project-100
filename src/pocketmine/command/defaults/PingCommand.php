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

		$sender->sendMessage("Ping: ". $sender->getPing() ."ms");
		return true;
	}
}
