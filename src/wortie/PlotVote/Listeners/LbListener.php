<?php

namespace wortie\PlotVote\listeners;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\utils\TextFormat;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityMotionEvent;

use wortie\PlotVote\PlotVote;
use wortie\PlotVote\Entity\LbEntity;

class LbListener implements Listener {

    public $plugin;

    public function __construct(PlotVote $plugin) {
        $this->plugin = $plugin;
    }

	public function onHitLB(EntityDamageByEntityEvent $event) {
		$lb = $event->getEntity();
		$player = $event->getDamager();
		if ($lb instanceof LbEntity && $player instanceof Player) {
			$event->setCancelled(true);
			$player->sendPopup(TextFormat::GOLD . ">".TextFormat::GRAY." This is the plot LeaderBoard ".TextFormat::GOLD."<");
		}
	}
	
	public function onEntityMotion(EntityMotionEvent $event){
        $entity = $event->getEntity();
		if($entity instanceof Player)return;
        if($entity instanceof LbEntity) {
            $event->setCancelled(true);
			return;
        }
    }
}