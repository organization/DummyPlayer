<?php

namespace DummyPlayer;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use DummyPlayer\task\UpdateEntityTask;
use DummyPlayer\task\SpawnEntityTask;
use pocketmine\event\entity\EntityDeathEvent;
use DummyPlayer\entity\DummyEntity;

class Main extends PluginBase implements Listener {
	private $dummySystem;
	public function onEnable() {
		$skin = "";
		$resource = $this->getResource ( "skin.dat" );
		while ( ! feof ( $resource ) )
			$skin .= fgets ( $resource, 1024 );
		fclose ( $resource );
		$this->dummySystem = new DummySystem ( $skin );
		
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
		
		$this->getLogger ()->info ( TextFormat::DARK_AQUA . "#더미테스트를 시작하기 위해서 서버에 접속해주세요." );
		$this->getServer ()->getScheduler ()->scheduleRepeatingTask ( new UpdateEntityTask ( $this ), 1 );
		$this->getServer ()->getScheduler ()->scheduleRepeatingTask ( new SpawnEntityTask ( $this ), 20 );
	}
	public function getDummySystem() {
		return $this->dummySystem->getInstance ();
	}
	public function onEntityDeathEvent(EntityDeathEvent $event) {
		if ($event->getEntity () instanceof DummyEntity)
			$this->getDummySystem ()->deleteDummy ( $event->getEntity ()->getName () );
	}
}

?>