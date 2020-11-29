<?php

declare(strict_types=1);

namespace uniban\event;

use pocketmine\Server;
use pocketmine\event\Listener;

use pocketmine\event\player\PlayerPreLoginEvent;

use uniban\Main;
use uniban\manager\BanManager;

class PlayerPreLogin implements Listener{

	/** @var Main */
	private $owner;
	/** @var Server */
	private $server;

	public function __construct(Main $owner){
		$this->owner = $owner;
		$this->server = Server::getInstance();
	}

	public function getOwner() : Main{
		return $this->owner;
	}

	public function getServer() : Server{
		return $this->server;
	}

	public function onPreLogin(PlayerPreLoginEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();

		BanManager::updateData($player);

		$bcn = BanManager::banCheck($player->getName());
		if($bcn === null){
			$bch = BanManager::banCheckByHost(gethostbyaddr($player->getAddress()));
			$bcu = BanManager::banCheckByUniqueId((string) $player->getUniqueId());

			if($bch || $bcu){
				BanManager::addBan($sender->getName(), $name, "BanEvading", "year", 80, "Ridoronpa");
				$event->setJoinMessage("§b貴方はBanされました\n§8理由: Ban Evading");
			}
		}else{
			if($bcn["pardon"] !== null){
				$player->kick("§b貴方はBanされています\n§8理由: {$bcn["reason"]}\n§cBan解除まで: {$bcn["pardon"]} ", false);
			}else{
				BanManager::removeBan($player->getName());
			}
		}
	}
}