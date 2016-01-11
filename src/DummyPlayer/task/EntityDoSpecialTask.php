<?php

namespace DummyPlayer\task;

use pocketmine\scheduler\PluginTask;
use DummyPlayer\DummySystem;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\block\Block;
use pocketmine\item\Item;

class EntityDoSpecial extends PluginTask {
	/**
	 * 엔티티가 특별한 일을 하게 만드는 틱입니다.
	 *
	 * {@inheritDoc}
	 *
	 * @see \pocketmine\scheduler\Task::onRun()
	 */
	public function onRun($currentTicks) {
		foreach ( DummySystem::getInstance ()->getEntities () as $entity ) {
			if (! $entity->isCreated ())
				continue;
			switch (mt_rand ( 0, 1 )) {
				case 0 : // 블럭설치
					$pos = $entity->add ( mt_rand ( - 2, 2 ), mt_rand ( - 2, 2 ), mt_rand ( - 2, 2 ) );
					$block = Block::get ( Block::GRASS, 0, $pos );
					$item = Item::get ( Item::GRASS );
					$ev = new BlockPlaceEvent ( $entity, $block, $block, $block, $item );
					$this->getOwner ()->getServer ()->getPluginManager ()->callEvent ( $ev );
					if (! $ev->isCancelled ())
						$ev->getBlock ()->getLevel ()->setBlock ( $pos, $block );
					break;
				case 1 : // 블럭파괴
					
					break;
				case 2 : // 눈덩이 발사
					break;
				case 3 : // 화살 발사
					break;
				case 4 : // 채팅
					break;
				case 5 : // 숙이기
					break;
				case 6 : // 빠르게 달리기
					break;
				case 7 : // 화염구 발사
					break;
				case 8 : // 갑옷변경
					break;
				case 9 : // 음식먹기
					break;
				case 10 : // 아이템버리기
					break;
				case 11 : // 블럭터치
					break;
				case 12 : // 명령어 사용
					break;
				case 13 : // 리스폰
					break;
				case 14 : // 물뿌리기
					break;
				case 15 : // 침대 들어가고 나가기
					break;
				case 16 : // 게임모드 변경
					break;
				case 17 : // 텔레포트
					break;
				case 18 : // 들고있는 아이템 변경
					break;
				case 19 : // 업적달성
					break;
				case 20 : // 조합하기
					break;
				case 21 : // 상자열기
					break;
				case 22 : // 화로사용하게 하기
					break;
			}
		}
	}
}