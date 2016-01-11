<?php

namespace DummyPlayer;

use DummyPlayer\receiver\DummyInterface;
use pocketmine\Server;
use DummyPlayer\entity\BaseEntity;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\level\Location;
use DummyPlayer\receiver\DummyReceiver;
use DummyPlayer\entity\DummyEntity;
use pocketmine\level\Level;

class DummySystem {
	private static $instance = null;
	
	/** @var DummyInterface */
	private $interface;
	
	/** @var BaseEntity[] */
	private $entities = [ ];
	private $autoNameBase = "dummy";
	private $autoNameIndex = 0;
	
	/** @var Server */
	private $server;
	public function __construct($skin) {
		if (self::$instance === null)
			self::$instance = $this;
		$this->server = Server::getInstance ();
		$this->interface = new DummyInterface ( $this->server, $skin );
	}
	/**
	 * 더미 플레이어를 생성합니다.
	 *
	 * @param string $username        	
	 * @param Position $pos        	
	 * @return NULL|\DummyPlayer\DummyPlayer
	 */
	public function addDummy(Position $pos, $username = null, $isFriendly = null) {
		if ($username === null)
			$username = $this->getAutoDummyName ();
		
		if ($this->interface->isExist ( $username ))
			return null;
		
		$receiver = $this->interface->openSession ( $username );
		$entity = $this->createEntity ( $receiver, $pos );
		
		if (! $entity instanceof BaseEntity)
			return null;
		
		$this->entities [] = $entity;
		$receiver->setDummyEntity ( $entity );
		$entity->setDummyReceiver ( $receiver );
		
		if ($isFriendly === null) {
			$entity->setFriendly ( mt_rand ( 0, 1 ) );
		} else {
			$entity->setFriendly ( $isFriendly );
		}
		
		return (new DummyPlayer ( $entity, $receiver ));
	}
	/**
	 * 더미플레이어를 삭제처리합니다.
	 *
	 * @param string $username        	
	 * @return boolean
	 */
	public function deleteDummy($username) {
		if (! $this->interface->isExist ( ( string ) $username ))
			return false;
		
		$this->interface->closeToName ( ( string ) $username );
		return true;
	}
	/**
	 * 더미플레이어 목록을 반환합니다.
	 *
	 * @return \DummyPlayer\receiver\DummyReceiver[]|\pocketmine\Player[]
	 */
	public function getDummys() {
		$dummys = array ();
		foreach ( $this->server->getOnlinePlayers () as $player )
			if ($player instanceof DummyReceiver)
				$dummys [] = $player;
		return $dummys;
	}
	/**
	 * 더미엔티티 목록을 반환합니다.
	 *
	 * @param Level $level        	
	 * @return \DummyPlayer\entity\BaseEntity[]
	 */
	public function getEntities(Level $level = null) {
		$entities = $this->entities;
		if ($level != null) {
			foreach ( $entities as $id => $entity ) {
				if (! $entity instanceof DummyEntity) {
					unset ( $entities [$id] );
					continue;
				}
				if ($entity->getLevel () !== $level) {
					unset ( $entities [$id] );
					continue;
				}
			}
		}
		return $entities;
	}
	/**
	 * 움직이는 플레이어 엔티티를 생성합니다.
	 *
	 * @param int|string $type        	
	 * @param Position $source        	
	 * @param mixed ...$args        	
	 *
	 * @return BaseEntity|Entity
	 */
	public static function createEntity(DummyReceiver $receiver, Position $source, ...$args) {
		$chunk = $source->getLevel ()->getChunk ( $source->x >> 4, $source->z >> 4, true );
		
		if ($chunk == null)
			return null;
		if (! $chunk->isLoaded ())
			$chunk->load ();
		if (! $chunk->isGenerated ())
			$chunk->setGenerated ();
		if (! $chunk->isPopulated ())
			$chunk->setPopulated ();
		
		$nbt = new CompoundTag ( "", [ 
				"Pos" => new ListTag ( "Pos", [ 
						new DoubleTag ( "", $source->x ),
						new DoubleTag ( "", $source->y ),
						new DoubleTag ( "", $source->z ) 
				] ),
				"Motion" => new ListTag ( "Motion", [ 
						new DoubleTag ( "", 0 ),
						new DoubleTag ( "", 0 ),
						new DoubleTag ( "", 0 ) 
				] ),
				"Rotation" => new ListTag ( "Rotation", [ 
						new FloatTag ( "", $source instanceof Location ? $source->yaw : 0 ),
						new FloatTag ( "", $source instanceof Location ? $source->pitch : 0 ) 
				] ) 
		] );
		
		/** @var BaseEntity $entity */
		$entity = new DummyEntity ( $chunk, $nbt, ...$args );
		if ($entity != null && $entity->isCreated ())
			$entity->spawnToAll ();
		
		return $entity;
	}
	/**
	 * 더미 플레이어의 이름을 겹치지 않게 자동으로 지어줍니다.
	 */
	public function getAutoDummyName() {
		$name = ( string ) $this->autoNameBase . $this->autoNameIndex;
		$this->autoNameIndex ++;
		return $name;
	}
	/**
	 * 더미 리시버를 생성하는 인터페이스를 반환합니다.
	 *
	 * @return \DummyPlayer\receiver\DummyInterface
	 */
	public function getInterface() {
		return $this->interface;
	}
	/**
	 * 더미시스템의 인스턴스를 반환합니다.
	 *
	 * @return \DummyPlayer\DummySystem
	 */
	public static function getInstance() {
		return self::$instance;
	}
}

?>