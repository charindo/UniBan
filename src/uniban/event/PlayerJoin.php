<?php

declare(strict_types=1);

namespace uniban\event;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;

use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\CommandData;
use pocketmine\network\mcpe\protocol\types\CommandEnum;
use pocketmine\network\mcpe\protocol\types\CommandParameter;

use uniban\Main;
use uniban\manager\BanManager;

class PlayerJoin implements Listener{

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

	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$this->sendCommandData($player);
	}

	public function sendCommandData(Player $player){
		/**** CommandParamater ****/
		$pk = new AvailableCommandsPacket();

		foreach(Main::$instance->getServer()->getCommandMap()->getCommands() as $name => $command){
			if(isset($pk->commandData[$command->getName()]) or $command->getName() === "help"){
				continue;
			}

			if($command->getName() === "uban"){
				$data = new CommandData();
				//TODO: commands containing uppercase letters in the name crash 1.9.0 client
				$data->commandName = strtolower($command->getName());
				$data->commandDescription = Main::$instance->getServer()->getLanguage()->translateString($command->getDescription());
				$data->flags = 0;
				$data->permission = 0;

				$parameter = new CommandParameter();
				$parameter->paramName = "player";
				$parameter->paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_TARGET;
				$parameter->isOptional = true;
				$data->overloads[0][0] = $parameter;

				$parameter = new CommandParameter();
				$parameter->paramName = "reason";
				$parameter->paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_RAWTEXT;
				$parameter->isOptional = true;
				$data->overloads[0][1] = $parameter;

				$parameter = new CommandParameter();
				$parameter->paramName = "type";
				$parameter->paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_RAWTEXT;
				$parameter->isOptional = true;
				$enum = new CommandEnum();
				$enum->enumName = "type";
				$enum->enumValues = ["year","month","day","hour","minute","second"];
				$parameter->enum = $enum;
				$data->overloads[0][2] = $parameter;

				$parameter = new CommandParameter();
				$parameter->paramName = "value";
				$parameter->paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_INT;
				$parameter->isOptional = true;
				$data->overloads[0][3] = $parameter;

				$aliases = $command->getAliases();
				if(count($aliases) > 0){
					if(!in_array($data->commandName, $aliases, true)){
					//work around a client bug which makes the original name not show when aliases are used
						$aliases[] = $data->commandName;
					}
					$data->aliases = new CommandEnum();
					$data->aliases->enumName = ucfirst($command->getName()) . "Aliases";
					$data->aliases->enumValues = array_values($aliases);
				}

				$pk->commandData[$command->getName()] = $data;
			}elseif($command->getName() === "unuban"){
				$data = new CommandData();
				//TODO: commands containing uppercase letters in the name crash 1.9.0 client
				$data->commandName = strtolower($command->getName());
				$data->commandDescription = Main::$instance->getServer()->getLanguage()->translateString($command->getDescription());
				$data->flags = 0;
				$data->permission = 0;

				$parameter = new CommandParameter();
				$parameter->paramName = "player";
				$parameter->paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_TARGET;
				$parameter->isOptional = true;
				$data->overloads[0][0] = $parameter;

				$aliases = $command->getAliases();
				if(count($aliases) > 0){
					if(!in_array($data->commandName, $aliases, true)){
					//work around a client bug which makes the original name not show when aliases are used
						$aliases[] = $data->commandName;
					}
					$data->aliases = new CommandEnum();
					$data->aliases->enumName = ucfirst($command->getName()) . "Aliases";
					$data->aliases->enumValues = array_values($aliases);
				}

				$pk->commandData[$command->getName()] = $data;
			}else{

				$data = new CommandData();
				//TODO: commands containing uppercase letters in the name crash 1.9.0 client
				$data->commandName = strtolower($command->getName());
				$data->commandDescription = Main::$instance->getServer()->getLanguage()->translateString($command->getDescription());
				$data->flags = 0;
				$data->permission = 0;

				$parameter = new CommandParameter();
				$parameter->paramName = "args";
				$parameter->paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_RAWTEXT;
				$parameter->isOptional = true;
				$data->overloads[0][0] = $parameter;

				$aliases = $command->getAliases();
				if(count($aliases) > 0){
					if(!in_array($data->commandName, $aliases, true)){
					//work around a client bug which makes the original name not show when aliases are used
						$aliases[] = $data->commandName;
					}
					$data->aliases = new CommandEnum();
					$data->aliases->enumName = ucfirst($command->getName()) . "Aliases";
					$data->aliases->enumValues = array_values($aliases);
				}

				$pk->commandData[$command->getName()] = $data;
			}
		}

		$player->sendDataPacket($pk);
	}
}