<?php

declare(strict_types=1);

namespace uniban\manager;

use pocketmine\Server;
use pocketmine\event\Listener;

use uniban\Main;

class CommandManager implements Listener{

	public static function registerCommands(Main $class){
		Server::getInstance()->getCommandMap()->register("UniBan", new \uniban\commands\UBanCommand("uban", $class));
		Server::getInstance()->getCommandMap()->register("UniBan", new \uniban\commands\UnUBanCommand("unuban", $class));
	}
}