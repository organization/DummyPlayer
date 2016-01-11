<?php

namespace DummyPlayer\receiver;

use pocketmine\network\SourceInterface;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\network\protocol\DataPacket;
use DummyPlayer\entity\DummyEntity;

class DummyInterface implements SourceInterface {
	private $server;
	private $sessions;
	private $ackStore;
	private $replyStore;
	private $skin;
	public function __construct(Server $server, $skin) {
		$this->server = $server;
		$this->skin = ( string ) $skin;
		$this->sessions = new \SplObjectStorage ();
		$this->ackStore = [ ];
		$this->replyStore = [ ];
	}
	public function isExist($name) {
		return isset ( $this->ackStore [$name] );
	}
	public function closeToName($name, $reason = "unknown reason") {
		$player = $this->server->getPlayer ( $name );
		if (! $player instanceof Player)
			return;
		$this->close ( $player, $reason );
	}
	public function close(Player $player, $reason = "unknown reason") {
		if ($player instanceof DummyReceiver) {
			if ($player->entity instanceof DummyEntity)
				$player->entity->close ();
			$player->close ();
		}
		$this->sessions->detach ( $player );
		unset ( $this->ackStore [$player->getName ()] );
		unset ( $this->replyStore [$player->getName ()] );
	}
	/**
	 * 더미 이벤트 수신 세션을 생성합니다.
	 *
	 * @param string $username        	
	 * @param string $address        	
	 * @param number $port        	
	 * @return DummyReceiver
	 */
	public function openSession($username, $address = "DUMMYPLAYER", $port = 0) {
		$player = new DummyReceiver ( $this, null, $address, $port );
		
		$player->setName ( $username );
		$player->setDisplayName ( $username );
		$player->setNameTagVisible ( true );
		$player->setNameTag ( $username );
		$player->setSkin ( $this->skin, "dummy" );
		
		$player->doInit ();
		$this->sessions->attach ( $player, $username );
		$this->ackStore [$username] = [ ];
		$this->replyStore [$username] = [ ];
		$this->server->addPlayer ( $username, $player );
		$this->server->addOnlinePlayer ( $player );
		return $player;
	}
	public function putPacket(Player $player, DataPacket $packet, $needACK = \false, $immediate = \true) {
		return true;
	}
	public function process() {
		return true;
	}
	public function setName($name) {
		return true;
	}
	public function shutdown() {
		return true;
	}
	public function emergencyShutdown() {
		return true;
	}
}

?>