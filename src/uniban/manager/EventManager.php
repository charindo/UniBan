<?php

declare(strict_types=1);

namespace uniban\manager;

use pocketmine\Server;
use pocketmine\event\Listener;

use uniban\Main;

class EventManager implements Listener{

	public static function registerEvents(Main $class){
		Server::getInstance()->getPluginManager()->registerEvents(new \uniban\event\PlayerJoin($class), $class);
		Server::getInstance()->getPluginManager()->registerEvents(new \uniban\event\PlayerPreLogin($class), $class);
	}
}