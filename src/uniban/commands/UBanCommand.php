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

class UBanCommand extends Command{

	private $owner;
	private $server;

	public function __construct(string $command = "uban", Main $owner){
		$description = "指定したプレイヤーを接続禁止にします";
		parent::__construct($command, $description, $description, [$command]);
		$this->owner = $owner;
		$this->server = $owner->getServer();
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		$name = $sender->getName();

		if($sender->isOp()){
			if(!isset($args[0])){
				$sender->sendMessage("§e§l名前を入力してください");
			}elseif(!isset($args[1])){
				$sender->sendMessage("§e§l理由を入力してください");
			}elseif(!isset($args[2])){
				$sender->sendMessage("§e§l期限のタイプを入力してください");
			}elseif(!isset($args[3])){
				$sender->sendMessage("§e§l値を入力してください");
			}else{
				$types = ["year", "month", "day", "hour", "minute", "second"];
				if(BanManager::getBanData($args[0]) === null){
					if(in_array($args[2], $types)){
						BanManager::addBan($sender->getName(), $args[0], $args[1], $args[2], $args[3], "Ridoronpa");
					}else{
						$sender->sendMessage("§c§lタイプに誤りがあります");
					}
				}else{
					$sender->sendMessage("§c§l指定したプレイヤーは既にBanされています");
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