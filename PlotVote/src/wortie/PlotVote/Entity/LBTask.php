<?php

declare(strict_types=1);

namespace wortie\PlotVote\Entity;

use wortie\PlotVote\Entity\LbEntity;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use wortie\PlotVote\PlotVote;

class LBTask extends Task {
	
	public function onRun(int $currentTick) {
		$level = Server::getInstance()->getDefaultLevel();
		foreach ($level->getEntities() as $entity) {
			if ($entity instanceof LbEntity) {
				$entity->setNameTagAlwaysVisible(true);
				$entity->setImmobile(true);
				$entity->setScale(1);
				$pv = Server::getInstance()->getPluginManager()->getPlugin("PlotVote");
				$entity->setNameTag($pv->getDatabase()->getTop());
			}
		}
	}
	
}