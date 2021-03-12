<?php

namespace atm;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use atm\Task\MoneyTask;



class Main extends PluginBase implements Listener{

    public $config;
    public $Argent;
    public $ArgentJoueur = [];
    public $economyAPI;

    public function onEnable(){

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->economyAPI = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");

        $this->Argent = new Config($this->getDataFolder() . "Argent.yml", Config::YAML, ["argentjoueur"=>[]]);
        $this->ArgentJoueur = $this->Argent->getAll();

        @mkdir($this->getDataFolder());
        if(!file_exists($this->getDataFolder() . "config.yml.yml")){
            $this->saveResource('config.yml');
        }
        $this->config = new Config($this->getDataFolder() . 'config.yml', Config::YAML);

        $this->getScheduler()->scheduleDelayedRepeatingTask(new MoneyTask($this), 1200*$this->config->get("temps"), 1200*$this->config->get("temps"));

    }

    public function onDisable(){

        $this->Argent->setAll($this->ArgentJoueur);
        $this->Argent->save();
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{

        if($sender instanceof Player){
            if($command->getName() === "atm"){
                $this->dayMoneyMenu($sender);
            }
        }
        return true;
    }

    public function newPlayer($player)
    {
        if ($player instanceof Player) {
            $player = $player->getName();
        }
        $player = strtolower($player);
        if (!isset($this->ArgentJoueur["argentjoueur"][$player])) {
            $this->ArgentJoueur["argentjoueur"][$player] = 0;
            return true;
        }
    }

    public function addMoney($player){
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);
        if(isset($this->ArgentJoueur["argentjoueur"][$player])){
            $this->ArgentJoueur["argentjoueur"][$player] +=$this->config->get("montant");
            return true;
        }
        return false;
    }

    public function resetMoney($player){
        if ($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);
        if(isset($this->ArgentJoueur["argentjoueur"][$player])){
            $this->ArgentJoueur["argentjoueur"][$player] = 0;
        }
    }

    public function getMoney($player){
        if ($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);
        if(isset($this->ArgentJoueur["argentjoueur"][$player])){
            return $this->ArgentJoueur["argentjoueur"][$player];
        }
        return NULL;
    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $this->newPlayer($player);
    }


    public function dayMoneyMenu($player){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $player, int $data = null){
            $result = $data;
            if ($result === null){
                return true;
            }
            $money = $this->getMoney($player);

            switch ($result){
                case 0;
                    $this->resetMoney($player);
                    $this->economyAPI->getInstance()->addMoney($player, $money);
                    break;

            }
        });
        $money = $this->getMoney($player);

        $form->setTitle($this->config->get("titre"));
        $form->setContent($this->config->get('text1') . "\n\n" . $this->config->get("text2") . "\n\n" . $this->config->get("textArgent") . $money);
        $form->addButton($this->config->get('boutton'));
        $form->sendToPlayer($player);
        return $form;
    }
}