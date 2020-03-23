<?php

namespace wortie\PlotVote;

use pocketmine\Player;
use pocketmine\scheduler\Task;

class Cooldown extends Task{

    public function __construct($plugin){
        $this->plugin = $plugin;
    }
  
    public function onRun($tick){
        foreach($this->plugin->commandCooldown as $player){
			if($this->plugin->commandCooldownTime[$player] <= 0){
				unset($this->plugin->commandCooldown[$player]);
				unset($this->plugin->commandCooldownTime[$player]);
			}else{
				$this->plugin->commandCooldownTime[$player]--;
			}
        }  
    }
}