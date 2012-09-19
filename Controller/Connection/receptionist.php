<?php
namespace Controller\Connection;

use Model\Network\socket;
use Model\Utility\configuration;
use Model\Utility\registry;
use Model\Object\Actor\player;
use Model\Instruction\instruction;
use Model\Utility\MGCParser\Script;
use Model\Utility\MGCParser\Parser;

class receptionist{
    private $listenSocket;
    
    public function __construct(){
        $this->listenSocket = new socket(configuration::getSetting("host"), configuration::getSetting("port"));
    }
    
    public function initListener(){
        return $this->listenSocket->open();
    }
    
    
    public function checkDisconnects(){
        $players = registry::getObject("players");
        foreach($players as $index => $player){
            if(!$player->isConnected()){
            echo "disconnected player found...\n";
            $player->closeSocket();
            unset($players[$index]);
            }
        }
        rsort($players);
        registry::updateObject("players", $players);
    }
    
    public function checkNewConnections(){
        $this->checkDisconnects();
        $players = registry::getObject("players");
        $tmp = new player();
        if($tmp->accept($this->listenSocket->getSock())){
            if(sizeof($players) < configuration::getSetting("max_players")){
                $tmp->assignId();
                echo "New Player connected\n";
                $players[] = $tmp;
                registry::updateObject("players", $players);
            }else{
                $this->sendSystemFullMessage($tmp);
            }
        }
    }

    public function sendSystemFullMessage(player $player){
        $player->sendData(configuration::getSetting("system_full_message"));
        $player->closeSocket();
    }
    
    public function mapCommands(){
        $players = registry::getObject("players");
        foreach($players as $player){
            $res = $player->getData();
            if($res != ""){
                $instruction = new instruction($res, $player);

                $file = configuration::getSetting("root_dir")
                    . configuration::getSetting("scripts_dir") 
                    . $instruction->getCommand()
                    . "." . configuration::getSetting("scripts_ext");

                if(file_exists($file)){
                    $script = new Script($file);
                    Parser::execute($script, $player, $instruction);
                }else{
                    $player->sendData("what?");
                }
            }
        }
    }
}