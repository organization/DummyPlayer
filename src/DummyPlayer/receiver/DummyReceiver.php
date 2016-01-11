<?php

namespace DummyPlayer\receiver;

use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\FullChunkDataPacket;
use pocketmine\network\SourceInterface;
use pocketmine\Player;
use pocketmine\utils\UUID;
use DummyPlayer\entity\DummyEntity;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\event\Timings;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\math\AxisAlignedBB;

class DummyReceiver extends Player {
	public $name = "";
	public $displayName = "";
	public $loggedIn = true;
	public $randomUUID;
	/** @var DummyEntity */
	public $entity = null;
	public function __construct(SourceInterface $interface, $clientID, $ip, $port) {
		parent::__construct ( $interface, $clientID, $ip, $port );
		$this->randomUUID = UUID::fromRandom ();
		$this->inventory = new DummyInventory ( $this );
	}
	public function doInit() {
		$nbt = $this->server->getOfflinePlayerData ( $this->name );
		if (! isset ( $nbt->NameTag )) {
			$nbt->NameTag = new StringTag ( "NameTag", $this->name );
		} else {
			$nbt ["NameTag"] = $this->name;
		}
		$this->gamemode = $nbt ["playerGameType"] & 0x03;
		if ($this->server->getForceGamemode ()) {
			$this->gamemode = $this->server->getGamemode ();
			$nbt->playerGameType = new IntTag ( "playerGameType", $this->gamemode );
		}
		
		$this->allowFlight = $this->isCreative ();
		
		if (($level = $this->server->getLevelByName ( $nbt ["Level"] )) === null) {
			$this->setLevel ( $this->server->getDefaultLevel () );
			$nbt ["Level"] = $this->level->getName ();
			$nbt ["Pos"] [0] = $this->level->getSpawnLocation ()->x;
			$nbt ["Pos"] [1] = $this->level->getSpawnLocation ()->y;
			$nbt ["Pos"] [2] = $this->level->getSpawnLocation ()->z;
		} else {
			$this->setLevel ( $level );
		}
		
		if (! ($nbt instanceof CompoundTag)) {
			$this->close ( $this->getLeaveMessage (), "Invalid data" );
			return;
		}
		
		$this->achievements = [ ];
		
		/** @var Byte $achievement */
		foreach ( $nbt->Achievements as $achievement ) {
			$this->achievements [$achievement->getName ()] = $achievement->getValue () > 0 ? true : false;
		}
		
		$nbt->lastPlayed = new LongTag ( "lastPlayed", floor ( microtime ( true ) * 1000 ) );
		if ($this->server->getAutoSave ()) {
			$this->server->saveOfflinePlayerData ( $this->name, $nbt, true );
		}
		
		$chunk = $this->level->getChunk ( $nbt ["Pos"] [0] >> 4, $nbt ["Pos"] [2] >> 4, true );
		assert ( $chunk !== null and $chunk->getProvider () !== null );
		
		$this->timings = Timings::getEntityTimings ( $this );
		
		$this->isPlayer = $this instanceof Player;
		
		$this->temporalVector = new Vector3 ();
		
		if ($this->eyeHeight === null) {
			$this->eyeHeight = $this->height / 2 + 0.1;
		}
		
		$this->id = Entity::$entityCount ++;
		$this->justCreated = true;
		$this->namedtag = $nbt;
		
		$this->chunk = $chunk;
		$this->setLevel ( $chunk->getProvider ()->getLevel () );
		$this->server = $chunk->getProvider ()->getLevel ()->getServer ();
		
		$this->boundingBox = new AxisAlignedBB ( 0, 0, 0, 0, 0, 0 );
		$this->setPositionAndRotation ( $this->temporalVector->setComponents ( $this->namedtag ["Pos"] [0], $this->namedtag ["Pos"] [1], $this->namedtag ["Pos"] [2] ), $this->namedtag->Rotation [0], $this->namedtag->Rotation [1] );
		$this->setMotion ( $this->temporalVector->setComponents ( $this->namedtag ["Motion"] [0], $this->namedtag ["Motion"] [1], $this->namedtag ["Motion"] [2] ) );
		
		assert ( ! is_nan ( $this->x ) and ! is_infinite ( $this->x ) and ! is_nan ( $this->y ) and ! is_infinite ( $this->y ) and ! is_nan ( $this->z ) and ! is_infinite ( $this->z ) );
		
		if (! isset ( $this->namedtag->FallDistance )) {
			$this->namedtag->FallDistance = new FloatTag ( "FallDistance", 0 );
		}
		$this->fallDistance = $this->namedtag ["FallDistance"];
		
		if (! isset ( $this->namedtag->Fire )) {
			$this->namedtag->Fire = new ShortTag ( "Fire", 0 );
		}
		$this->fireTicks = $this->namedtag ["Fire"];
		
		if (! isset ( $this->namedtag->Air )) {
			$this->namedtag->Air = new ShortTag ( "Air", 300 );
		}
		$this->setDataProperty ( self::DATA_AIR, self::DATA_TYPE_SHORT, $this->namedtag ["Air"] );
		
		if (! isset ( $this->namedtag->OnGround )) {
			$this->namedtag->OnGround = new ByteTag ( "OnGround", 0 );
		}
		$this->onGround = $this->namedtag ["OnGround"] > 0 ? true : false;
		
		if (! isset ( $this->namedtag->Invulnerable )) {
			$this->namedtag->Invulnerable = new ByteTag ( "Invulnerable", 0 );
		}
		$this->invulnerable = $this->namedtag ["Invulnerable"] > 0 ? true : false;
		
		$this->chunk->addEntity ( $this );
		$this->level->addEntity ( $this );
		$this->initEntity ();
		$this->lastUpdate = $this->server->getTick ();
		$this->server->getPluginManager ()->callEvent ( new EntitySpawnEvent ( $this ) );
		
		$this->scheduleUpdate ();
	}
	public function getDataProperties(){
		return $this->dataProperties;
	}
	public function dataPacket(DataPacket $packet, $needACK = false) {
		return parent::dataPacket ( $packet, $needACK );
	}
	public function handleDataPacket(DataPacket $packet) {
		parent::handleDataPacket ( $packet );
	}
	public function setDummyEntity(DummyEntity $entity) {
		$this->entity = $entity;
	}
	public function isOnline() {
		return true;
	}
	public function isAlive() {
		if ($this->entity !== null)
			return $this->entity->isAlive ();
		return false;
	}
	public function setName($name) {
		$this->name = $name;
		$this->username = $name;
	}
	public function getName() {
		return $this->name;
	}
	public function setDisplayName($name) {
		$this->displayName = $name;
	}
	public function getDisplayName() {
		return $this->displayName;
	}
	public function getUniqueId() {
		return $this->randomUUID;
	}
	public function getRawUniqueId() {
		return $this->randomUUID->toString ();
	}
	public function getSkinName() {
		return $this->skinName;
	}
	public function getSkinData() {
		return $this->skin;
	}
	public function setSkin($str, $skinName) {
		$this->skin = $str;
		$this->skinName = $skinName;
	}
	public function sendChunk($x, $z, $payload, $ordering = FullChunkDataPacket::ORDER_COLUMNS) {
		return;
	}
}
?>