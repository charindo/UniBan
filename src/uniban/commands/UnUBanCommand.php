<?php

declare(strict_types=1);

namespace uniban\commands;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

use uniban\Main;
use uniban\manager\BanManager;

class UnUBanCommand extends Command{

	private $owner;
	private $server;

	public function __construct(string $command = "unuban", Main $owner){
		$description = "指定したプレイヤーの接続禁止状態を解除します";
		parent::__construct($command, $description, $description, [$command]);
		$this->owner = $owner;
		$this->server = $owner->getServer();
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		$name = $sender->getName();

		if($sender->isOp()){
			if(!isset($args[0])){
				$sender->sendMessage("§e§l名前を入力してください");
			}else{
				$result = BanManager::removeBan($args[0]);
				if($result){
					$sender->sendMessage("§a§lBanの解除に成功しました");
				}elseif(!$result){
					$sender->sendMessage("§c§l指定したプレイヤーはBanされていません");
				}
			}
		}else{
			$sender->sendMessage("§cこのコマンドを実行する権限がありません");
		}
	}

	public function getOwner() : Main{
		return $this->owner;
	}

	public function getServer() : Server{
		return $this->server;
	}
}