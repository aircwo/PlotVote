<?php

declare(strict_types=1);

namespace wortie\PlotVote;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\math\Vector3;

use pocketmine\Player;

use wortie\PlotVote\Cooldown;
use wortie\PlotVote\Entity\LBTask;
use wortie\PlotVote\Entity\LbEntity;
use wortie\PlotVote\PlotVoteDatabase;
use wortie\PlotVote\Listeners\LbListener;
use MyPlot\MyPlot;
use MyPlot\Plot;

class PlotVote extends PluginBase {

	private $MyPlot;
	private $database;
	private $commandstatus = true;
	public $commandCooldown = [ ];
	public $commandCooldownTime = [ ];

	public function onEnable() : void{
		$this->MyPlot = $this->getServer()->getPluginManager()->getPlugin("MyPlot");
		$this->database = new PlotVoteDatabase($this);
		$this->getScheduler()->scheduleRepeatingTask(new Cooldown($this), 20);
		$this->getScheduler()->scheduleRepeatingTask(new LBTask($this), 120);
		$this->saveResource("plotvotes.db");
		$this->regEntity();
		$this->regListeners();
	}
	
	public function getDatabase(): PlotVoteDatabase {
        return $this->database;
    }

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		switch($command->getName()){
			case "plotvote":
			##EXPERIMENTAL COOLDOWN
			if($this->commandstatus){
				if(!isset($this->commandCooldown[$sender->getName()])){
					$plot = $this->MyPlot->getPlotByPosition($sender);
					if($plot === null || $plot->owner === null) {  # Checks if player is in/on a plot
						$sender->sendMessage(TextFormat::RED . "Your not on a plot or that plot isn't claimed.");
						return true;
					}
					$owner = $plot->owner;
					$id = $this->getPlotById($plot);
					$votecheck = $this->getVotes($id); 
					if($votecheck === null) { # Checks to see if an entry in the DataBase has been created
						if($sender instanceof Player){
							$this->addDbEntry($owner, $plot, 0);
							$sender->sendMessage("Please Run that command again.");
							return true;
						}
					}
					if($this->getPlotById($plot) < 0){
						$sender->sendMessage(TextFormat::RED . "You cannot upvote an un-owned plot.");
						return true;
					}
					if($plot->owner === $sender->getName()){
						$sender->sendMessage(TextFormat::RED . "You Cannot upvote your own plot!");
						return true;
					}
					$this->addVote($id);
					$votes = $this->getVotes($id); 
					$sender->sendMessage("§7You Upvoted§a $owner §7Plot.");
					$sender->sendMessage("§7Plot: $plot now has: §a$votes §7votes.");
					$this->commandCooldown[$sender->getName()] = $sender->getName();
					#$time = "86400"; #1day
					$time = "3"; # for testing
					$this->commandCooldownTime[$sender->getName()] = $time;
				}else{
					$sender->sendPopup(TextFormat::RED."You can't vote for this plot for another: ".$this->commandCooldownTime[$sender->getName()]." seconds.");
				}
			}
			$this->commandstatus = !$this->commandstatus;
			return true;
			break;
			case 'pvtop':
				$message = $this->database->getTop();
				$sender->sendMessage($message);
				return true;
			break;
			case 'remlb':
				if ($sender->isOp()) {
					$npc = Server::getInstance()->getDefaultLevel()->getEntities();
					foreach ($npc as $entity) {
						if ($entity instanceof Leaderboard) {
							$entity->close();
						}
					}
				} else {
					$sender->sendMessage("You cant use this command!");
				}
				return true;
			break;
			case 'pvlb':
				if ($sender->isOp()) {
					$this->setLeaderboardEntity($sender->getPlayer());
				} else {
					$sender->sendMessage("You cant use this command!");
				}
			return true;
			default:
				return false;
		}
	}
	
	public function getPlotById(Plot $plot): int { # This simply returns the ID of a plot, this value will be bound with the data entry Therefor allowing defrentiation between plot votes
		return $plot->id;
	}
	
	public function addDbEntry(string $player, $plot, int $votes){ # This registers a database entry, Name, Plot, Votes
		$this->database->regPlotEnty($player, $plot, $votes);
		return;
	}
	
	public function setLeaderboardEntity(Player $player){
		$player->saveNBT();
		$nbt = Entity::createBaseNBT(new Vector3((float)$player->getX(), (float)$player->getY(), (float)$player->getZ()));
		$nbt->setTag(clone $player->namedtag->getCompoundTag("Skin"));
		$human = new LbEntity($player->getLevel(), $nbt);
		$human->setSkin(new Skin("textfloat", $human->getInvisibleSkin()));
		$human->setNameTagVisible(true);
		$human->setNameTagAlwaysVisible(true);
		$human->spawnToAll();
	}
	
	public function remDbEntry($player, $plot, int $votes){ #TODO
		$this->database->remPlotEnty($player, $plot, $votes);
		return;
	}
	
	public function regEntity(){
		Entity::registerEntity(LbEntity::class, true);
	}
	
	public function regListeners(){
		$this->getServer()->getPluginManager()->registerEvents(new LbListener($this), $this);
	}
	
	public function verifyDbEntry(Player $player){ # This might not be needed if checking for votes..
		$this->database->verifyPlayerInDB($player);
		return;
	}
	
	public function getVotes($plot){ ##TODO
		$votes = $this->database->getPlotVotes($plot);
		return $votes;
	}
	
	public function addVote($plot){
		$newvotes = $this->database->setVotes($plot);
		return;
	}
	
	public function onDisable(){
		$this->saveResource("plotvotes.db");
	}
}