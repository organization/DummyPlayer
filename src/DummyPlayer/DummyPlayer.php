<?php

namespace DummyPlayer;

use DummyPlayer\entity\DummyEntity;
use DummyPlayer\receiver\DummyReceiver;

class DummyPlayer {
	protected $entity;
	protected $receiver;
	public function __construct(DummyEntity $entity, DummyReceiver $receiver) {
		$this->entity = $entity;
		$this->receiver = $receiver;
	}
	/**
	 * 더미 엔티티를 반환합니다.
	 * (Entity)
	 * 
	 * @return \DummyPlayer\entity\DummyEntity
	 */
	public function getEntity() {
		return $this->entity;
	}
	/**
	 * 더미 리시버를 반환합니다.
	 * (Player)
	 * 
	 * @return \DummyPlayer\receiver\DummyReceiver
	 */
	public function getReceiver() {
		return $this->receiver;
	}
}

?>