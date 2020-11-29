<?php

namespace uniban;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

use uniban\manager\BanManager;
use uniban\manager\CommandManager;
use uniban\manager\EventManager;

class Main extends PluginBase{

	/** @var Main */
	public static $instance;
	
	public function onEnable(){
		self::$instance = $this;
		
		if(!file_exists($this->getDataFolder())) mkdir($this->getDataFolder(), 0744, true);

		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, [
			"mysql" => "",
		]);

		if(empty($this->config->get("mysql"))){
			$this->getLogger()->error("MySQLの情報をConfigに入力し、再度サーバーを起動してください");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}elseif(count(explode(";",$this->config->get("mysql"))) !== 4){
			$this->getLogger()->error("MySQLDataの記述方法に問題があります。再度確認しサーバーを起動してください。");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}else{
			$mysqlData = explode(";",$this->config->get("mysql"));

			$this->mysql = mysqli_connect($mysqlData[0], $mysqlData[1], $mysqlData[2], $mysqlData[3]);

			if(mysqli_connect_errno()){
				$this->getLogger()->error("§4データベースに接続できませんでした ".mysqli_connect_error());
			}else{
				$this->getLogger()->info("§aデータベースに接続しました");
			}

			/*** register ***/
			CommandManager::registerCommands($this);
			EventManager::registerEvents($this);

			BanManager::init();

			$this->getLogger()->info("UniBanを読み込みました");
		}
	}
}