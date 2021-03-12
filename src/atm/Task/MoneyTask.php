<?php

namespace atm\Task;

use pocketmine\scheduler\Task;
use atm\Main;

class MoneyTask extends Task{

    public $plugin;

    public function __construct(Main $plugin){

        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick){

        $this->plugin->Argent->save();

        $all = $this->plugin->getServer()->getOnlinePlayers();
        foreach ($all as $player){
            $this->plugin->addMoney($player);
        }
    }
}

