<?php
namespace Core\Model\Instruction;
use Core\Model\Object\Actor\player;

class instruction{
    protected	$string,
		$command,
		$arguments;
    /**
     *
     * @var \Model\Object\Actor\player 
     */
    protected $player;
    
    public function __construct($string, player $player){
        $string = trim($string);
	$this->player = $player;
	$this->string = $string;
	$bits = explode(" ", $string);
	$this->command = array_shift($bits);
	$this->arguments = $bits;
    }
    
    public function getCommand(){
	return $this->command;
    }
    
    public function getArguments($index = -1){
	if($index == -1){
         return $this->arguments;   
        }else{
            return $this->arguments[$index];
        }        
    }
    
    public function getPlayer(){
	return $this->player;
    }
}