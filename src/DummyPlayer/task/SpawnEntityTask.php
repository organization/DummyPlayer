<?php

namespace DummyPlayer\task;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use DummyPlayer\DummySystem;
use DummyPlayer\receiver\DummyReceiver;

class SpawnEntityTask extends Task {
	public function onRun($currentTicks) {
		$server = Server::getInstance ();
		
		foreach ( $server->getOnlinePlayers () as $player ) {
			if ($player instanceof DummyReceiver)
				continue;

			//if(count(DummySystem::getInstance ()->getEntities ()) > 0) return;
			
			$radius = 25;
			$pos = $player->getPosition ();
			$pos->y = $player->level->getHighestBlockAt ( $pos->x += mt_rand ( - $radius, $radius ), $pos->z += mt_rand ( - $radius, $radius ) ) + 2;
			DummySystem::getInstance ()->addDummy ( $pos );
			
			echo "#현재 생성되어진 더미 수 :" . count ( DummySystem::getInstance ()->getEntities () ) . "\n";
		}
	}
}