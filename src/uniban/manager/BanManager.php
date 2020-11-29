<?php

declare(strict_types=1);

namespace uniban\manager;

use pocketmine\Player;
use pocketmine\Server;

use uniban\Main;

class BanManager{

	public static function init(){
		$mysql = Main::$instance->mysql;

		$mysql->query("CREATE TABLE IF NOT EXISTS uniban_player_data (id INT(10) AUTO_INCREMENT PRIMARY KEY NOT NULL, name VARCHAR(100) NOT NULL, host VARCHAR(100) NOT NULL, uniqueId VARCHAR(100) NOT NULL)");
		$mysql->query("CREATE TABLE IF NOT EXISTS uniban_ban_data (id INT(10) AUTO_INCREMENT PRIMARY KEY NOT NULL, name VARCHAR(100) NOT NULL, server VARCHAR(100) NOT NULL, host VARCHAR(100) NOT NULL, uniqueId VARCHAR(100) NOT NULL, reason VARCHAR(100) NOT NULL, sender VARCHAR(100) NOT NULL, date VARCHAR(100) NOT NULL, deadline VARCHAR(100) NOT NULL)");
	}

	public static function getData(string $name) : ?array{
		$mysql = Main::$instance->mysql;

		$query = $mysql->query("SELECT * FROM uniban_player_data WHERE name='$name'");

		if($row = $query->fetch_assoc()){
			return $row;
		}
		return null;
	}

	public static function getBanData(string $name) : ?array{
		$mysql = Main::$instance->mysql;

		$query = $mysql->query("SELECT * FROM uniban_ban_data WHERE name='$name'");

		if($row = $query->fetch_assoc()){
			return $row;
		}
		return null;
	}

	public static function updateData(Player $player){
		$mysql = Main::$instance->mysql;

		$name = $player->getName();
		$host = gethostbyaddr($player->getAddress());
		$uid = (string) $player->getUniqueId();

		$data = self::getData($name);
		if(!$data){
			$mysql->query("INSERT INTO uniban_player_data(`name`,`host`,`uniqueId`) VALUES ('$name','$host','$uid')");
		}else{
			$mysql->query("UPDATE uniban_player_data SET host='$host', uniqueId='$uid' WHERE name='$name'");
		}
	}

	public static function addBan($sname, $name, $reason, $type, $value, $server){
		$query = Main::$instance->mysql->query("SELECT * FROM uniban_ban_data WHERE name='$name'");
		$now = date("Y/m/d H:i:s");
		if($type === "year") $deadline = date("Y/m/d H:i:s", strtotime("+ {$value} year"));
		if($type === "month") $deadline = date("Y/m/d H:i:s", strtotime("+ {$value} month"));
		if($type === "day") $deadline = date("Y/m/d H:i:s", strtotime("+ {$value} day"));
		if($type === "hour") $deadline = date("Y/m/d H:i:s", strtotime("+ {$value} hour"));
		if($type === "minute") $deadline = date("Y/m/d H:i:s", strtotime("+ {$value} minute"));
		if($type === "second") $deadline = date("Y/m/d H:i:s", strtotime("+ {$value} second"));
		$data = self::getData($name);
		if($data !== null){
			$host = $data["host"];
			$uniqueId = $data["uniqueId"];
			Main::$instance->mysql->query("INSERT INTO uniban_ban_data(`name`,`server`,`host`,`uniqueId`,`reason`,`sender`,`date`,`deadline`) VALUES ('$name','$server','$host','$uniqueId','$reason','$sname','$now','$deadline')");
			Main::$instance->getServer()->broadcastMessage("§a{$sname} was banned {$name}: {$reason}");
			$player = Main::$instance->getServer()->getPlayerExact($name);
			if($player !== null){
				$player->kick("§3貴方はBanされました\n§7理由: §c{$reason}", false);
			}
		}else{
			Main::$instance->mysql->query("INSERT INTO uniban_ban_data(`name`,`server`,`host`,`uniqueId`,`reason`,`sender`,`date`,`deadline`) VALUES ('$name','$server','None','None','$reason','$sname','$now','$deadline')");
			Main::$instance->getServer()->broadcastMessage("§a{$sname} was banned {$name}: {$reason}");
			$player = Main::$instance->getServer()->getPlayerExact($name);
			if($player !== null){
				$player->kick("§3貴方はBanされました\n§7理由: §c{$reason}", false);
			}
		}
	}

	public static function date_time_diff($d1, $d2){
		$diffDateTime = array();

		$timeStamp1 = strtotime($d1);
		$timeStamp2 = strtotime($d2);

		$difSeconds = $timeStamp2 - $timeStamp1;

		$diffDateTime['seconds'] = $difSeconds % 60;

		$difMinutes = ($difSeconds - ($difSeconds % 60)) / 60;
		$diffDateTime['minutes'] = $difMinutes % 60;

		$difHours = ($difMinutes - ($difMinutes % 60)) / 60;
		$diffDateTime['hours'] = $difHours % 24;
		
		$difDays = ($difHours - ($difHours % 24)) / 24;
		$diffDateTime['days'] = $difDays;

		return $diffDateTime;
	}

	public static function banCheck($name){
		$array = [];
		$query = Main::$instance->mysql->query("SELECT * FROM uniban_ban_data WHERE name='$name'");

		if($row = $query->fetch_assoc()){
			$n = $row["name"];
			$sender = $row["sender"];
			$reason = $row["reason"];
			$date = $row["date"];
			$deadline = $row["deadline"];
		}

		if(isset($n)){
			$array = [
				"reason" => $reason,
				"sender" => $sender,
				"date" => $date,
				"deadline" => $deadline,
				"pardon" => "取得不可",
			];
			if($date !== "" && $deadline !== null){
				$deadline_totime = strtotime($array["deadline"]);
				$now = strtotime("now");
				if($now >= $deadline_totime){
					$array["pardon"] = null;
				}else{
					$diff = self::date_time_diff(date("Y/m/d H:i:s"), date("Y/m/d H:i:s", $deadline_totime));
					$array["pardon"] = "{$diff["days"]} Days {$diff["hours"]} Hours {$diff["minutes"]} Minutes {$diff["seconds"]} Seconds";
				}
			}
			return $array;
		}else{
			return null;
		}
	}

	public static function removeBan(string $name) : bool{
		$mysql = Main::$instance->mysql;

		$bdata = self::getBanData($name);
		if($bdata){
			$mysql->query("DELETE FROM uniban_ban_data WHERE name='$name'");
			return true;
		}else{
			return false;
		}
	}

	public static function banCheckByHost(string $host) : bool{
		$mysql = Main::$instance->mysql;

		$query = $mysql->query("SELECT * FROM uniban_ban_data WHERE host='$host'");
		
		if($row = $query->fetch_assoc()){
			$bc = self::banCheck($row["name"]);
			if($bc !== null){
				if($bc["pardon"] !== null){
					return true;
				}else{
					self::removeBan($row["name"]);
				}
			}
		}
		return false;
	}

	public static function banCheckByUniqueId(string $uid) : bool{
		$mysql = Main::$instance->mysql;

		$query = $mysql->query("SELECT * FROM uniban_ban_data WHERE uniqueId='$uid'");
		
		if($row = $query->fetch_assoc()){
			$bc = self::banCheck($row["name"]);
			if($bc !== null){
				if($bc["pardon"] !== null){
					return true;
				}else{
					self::removeBan($row["name"]);
				}
			}
		}
		return false;
	}
}