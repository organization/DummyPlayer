<?php

namespace DummyPlayer\task;

use pocketmine\scheduler\PluginTask;
use DummyPlayer\DummySystem;

class UpdateEntityTask extends PluginTask {
	/**
	 * 엔티티를 움직이게 하는 틱입니다.
	 *
	 * {@inheritDoc}
	 *
	 * @see \pocketmine\scheduler\Task::onRun()
	 */
	public function onRun($currentTicks) {
		foreach ( DummySystem::getInstance()->getEntities () as $entity ) {
			if ($entity->isCreated ()){
				$entity->updateTick ();
			}
		}
	}
}