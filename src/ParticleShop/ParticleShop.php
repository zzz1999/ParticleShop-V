<?php
namespace ParticleShop;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\math\Vector3;	
use pocketmine\utils\TextFormat;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
//particle
use pocketmine\level\particle\BubbleParticle;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\EnchantParticle;
use pocketmine\level\particle\EntityFlameParticle;
use pocketmine\level\particle\ExplodeParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\InkParticle;
use pocketmine\level\particle\ItemBreakParticle;
use pocketmine\level\particle\LavaDripParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\MobSpawnParticle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\particle\SplashParticle;
use pocketmine\level\particle\SporeParticle;
use pocketmine\level\particle\TerrainParticle;
use pocketmine\level\particle\WaterDripParticle;
use pocketmine\level\particle\WaterParticle;

use onebone\economyapi\EconomyAPI;

class ParticleShop extends PluginBase implements Listener{
    
    private static $instance;
    
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
        @mkdir($this->getDataFolder(),0777,true);
        @mkdir($this->getDataFolder().'\\Players');
        $this->shops=new Config($this->getDataFolder().'ParticleShopVector3.yml',Config::YAML);
        self::$instance = $this;
		
		
    }
    
    public static function getInstance(){
        return self::$instance;
    }
    
    public function onTouch(PlayerInteractEvent $event){
        $block = $event->getBlock();
        $sign = $block->getLevel()->getTile(new Vector3($block->getX(),$block->getY(),$block->getZ()));
        $blockid=$block->getId();
		$player=$event->getPlayer();
		if(in_array($blockid,[63,68,323])){
        if($sign->getText()[0] == TextFormat::BLUE.'[点击购买粒子]'){
        if(array_key_exists($block->getX().','.$block->getY().','.$block->getZ().','.$player->getLevel()->getName(),$this->shops->getAll())){
			
			$config = new Config($this->getDataFolder().'\\Players\\'.$event->getPlayer()->getName().'.yml',Config::YAML,array('particle'));
            $money = EconomyAPI::getInstance()->myMoney($player);
        if($money < mb_substr(TextFormat::clean($sign->getText()[1]),3)){
                $player->sendMessage(TextFormat::RED.'你没有足够的钱');
                return;
            }else{
                $a=mb_substr(TextFormat::clean($sign->getText()[2]),5);
                $particle=$config->get('particle',array());
                if(!in_array($a,$config->get('particle',array()))){
				
					$price=mb_substr(TextFormat::clean($sign->getText()[1]),3);
					if($a=='暂无')return;
                EconomyAPI::getInstance()->reduceMoney($player, $price);
                $player->sendMessage(TextFormat::YELLOW.'你已经购买了'.$a);
                $particle[count($particle)] = $a;
                $config->set('particle',$particle);
                $config->save();
                }else{
                    $player->sendMessage(TextFormat::RED.'你已经拥有了这个粒子');
                        }
                    }
                }
            }
        }
    }	
    
    public function onWrite(SignChangeEvent $event){
        $line=$event->getLines();
        if($line[0]==='ps' or '粒子' or '粒子商店'){
            if($event->getPlayer()->isOp()){
               if(is_numeric($line[1])){	
                    if($this->getSignParticle($line[2])){
                        $event->setLine(0,TextFormat::BLUE.'[点击购买粒子]');
                        $event->setLine(1,TextFormat::YELLOW.'价格:'.$line[1]);
                        $event->setLine(2,TextFormat::WHITE.'粒子类型:'.$this->getSignParticle($line[2]));
						$event->setLine(3,null);
                        $level=$event->getBlock()->getLevel()->getName();
                        $block = $event->getBlock();
                        $event->getPlayer()->sendMessage(TextFormat::GREEN.'成功创建粒子商店');
                        $this->shops->set($block->getX().','.$block->getY().','.$block->getZ().','.$level);
                        $this->shops->save();
                        }else{
							$event->getPlayer()->sendMessage(TextFormat::RED.'没有'.$line[2].'这种粒子');
							}
					}
                }
            }
            
        }
    
    //获取粒子
    
    private function getSignParticle($p){
        switch(strtolower($p)){
            case '0':
            case 'bubbleparticle':
                return '气泡粒子';
            case '1':
            case 'criticalparticle':
                return '暴击粒子';
			case '2':
			case 'DestroyBlockParticle':
				return '破坏方块';
            case '3':
            case 'dustparticle':
                return '尘埃粒子';
            case '4':
            case 'enchantparticle':
                return '附魔粒子';
            case '5':
            case 'entityflameparticle':
                return '实体火焰';
            case '6':
            case 'explodeparticle':
                return '爆炸粒子';
            case '7':
            case 'flameparticle':
                return '火焰粒子';
            case '8':
            case 'heartparticle':
                return '爱心粒子';
            case '9':
            case 'inkparticle':
                return '墨水粒子';
            case '10':
            case 'itembreakparticle':
               return '暂无';
            case '11':
            case 'lavadripparticle':
                return '岩浆滴落';
			case '12':
			case 'LavaParticle':
				return '岩浆粒子';
			case '13':
			case 'MobSpawnParticle':
				return '生物烟雾';
			case '14':
			case 'PortalParticle':
				return '末影粒子';
			case '15':
			case 'redstoneparticle':
				return '红石粒子';
			case '16':
			case 'smokeparticle':
				return '烟雾粒子';
			case '17':
			case 'splashparticle':
				return '飞溅粒子';
			case '18':
			case 'sporeparticle':
				return '暂无';
			case '19':
			case 'terrainparticle':
				return '暂无';
			case '20':
			case 'waterdripparticle':
				return '水滴滴落';
			case '21':
			case 'waterparticle':
				return '水粒子';
			
				
            default: return false;
        }
    }//感觉翻译日了狗
	
	public function OnMove(PlayerMoveEvent $event){
			$player=$event->getPlayer();
			$x=$player->getX();
			$y=$player->getY();
			$z=$player->getZ();
			$pn=$this->getNow($player->getName());
            if(!$this->addParticle($pn,$player))return;
			
			$player->getLevel()->addParticle($this->addParticle($pn,new Vector3($x + 1,$y,$z)));
            $player->getLevel()->addParticle($this->addParticle($pn,new Vector3($x - 1,$y,$z)));
            $player->getLevel()->addParticle($this->addParticle($pn,new Vector3($x,$y,$z + 1)));
            $player->getLevel()->addParticle($this->addParticle($pn,new Vector3($x,$y,$z - 1)));
            $player->getLevel()->addParticle($this->addParticle($pn,new Vector3($x + 0.5,$y - 0.5,$z + 0.5)));
            $player->getLevel()->addParticle($this->addParticle($pn,new Vector3($x + 0.5,$y - 0.5,$z - 0.5)));
            $player->getLevel()->addParticle($this->addParticle($pn,new Vector3($x - 0.5,$y - 0.5,$z + 0.5)));
            $player->getLevel()->addParticle($this->addParticle($pn,new Vector3($x - 0.5,$y - 0.5,$z - 0.5)));		
			
		}
    
    private function addParticle($type,Vector3 $pos){
        switch(strtolower($type)){
            case '气泡粒子':
            return new BubbleParticle($pos);
            case '暴击粒子':
            return new CriticalParticle($pos);
			case '破坏方块':
			return new DestroyBlockParticle($pos, new Block(247, rand(0,2)));
            case '尘埃粒子':
            return new DustParticle($pos, 104, 204, 255);
            case '附魔粒子':
            return new EnchantParticle($pos);
            case '实体火焰':
            return new EntityFlameParticle($pos);
            case '爆炸粒子':
            return new ExplodeParticle($pos, 2, 2);
            case '火焰粒子':
            return new FlameParticle($pos);
            case '爱心粒子':
            return new HeartParticle($pos);
            case '墨水粒子':
            return new InkParticle($pos);
        //  case 'itembreak':
        //  return new ItemBreakParticle($pos,new Item(rand(1,91)));
            case '岩浆滴落':
            return new LavaDripParticle($pos);
			case '岩浆粒子':
			return new LavaParticle($pos);
			case '生物烟雾':
			return new MobSpawnParticle($pos);
			case '末影粒子':
			return new PortalParticle($pos);
			case '红石粒子';
			return new RedstoneParticle($pos, 2);
			case '烟雾粒子':
			return new SmokeParticle($pos);
			case '飞溅粒子':
			return new SplashParticle($pos);
		//	case 'terrain':
		//	return TerrainParticle($pos);
			case '水滴滴落':
			return new WaterDripParticle($pos);
			case '水粒子':
			return new WaterParticle($pos);
			
            default: return false;
        }//坑线
    }
    
    private function getNow($a){
        return (new Config($this->getDataFolder().'\\Players\\'.$a.'.yml',Config::YAML))->get('now');
    }
    
    private function getParticle($n){
        $config=new Config($this->getDataFolder().'\\Players\\'.$n.'.yml',Config::YAML);
        return $config->get('particle',array());
    }
    
    private function setParticle($name,$number){
        $config=new Config($this->getDataFolder().'\\Players\\'.$name.'.yml',Config::YAML);
        $config->set('now',($config->get('particle')[$number]));
        $config->save();
    }
    
    public function onCommand(CommandSender $sender,Command $command,$label,array $args){
        if($command->getName()=='ps' or '粒子'){
			if(isset($args[0])){
            $name=$sender->getName();
            $config=new Config($this->getDataFolder().'\\Players\\'.$name.'.yml',Config::YAML,array('particle'));
            //$particle=$config->get('particle',array());
                switch($args[0]){
				case '帮助':
				case 'help':
				$sender->sendMessage(TextFormat::GREEN.'[Particle]'.TextFormat::YELLOW.'Helps-------');
				$sender->sendMessage(TextFormat::GREEN.'[Particle]-ChooseParticle选择使用粒子  '.TextFormat::WHITE.'/ps set/ 设置 Kinds粒子种类');
				$sender->sendMessage(TextFormat::GREEN.'[Particle]-ListParticles列出可用粒子  '.TextFormat::WHITE.'/ps list/ 列表');
				$sender->sendMessage(TextFormat::GREEN.'[Particle]-StopParticle关闭粒子  '.TextFormat::WHITE.'/ps stop/ 关闭');
				return true;
                case '设置':
                case 'set':
				if(isset($args[1])){
                if(!array_key_exists($args[1],$this->getParticle($name))){
                    $sender->sendMessage(TextFormat::RED.'你没有这个粒子');
                }else{
				$this->setParticle($name,$args[1]);
                $sender->sendMessage(TextFormat::YELLOW.'成功设置粒子');
                }
				}else{
					$sender->sendMessage('ParticleShop.AI: '.TextFormat::RED.'你想表达什么');
				}
                return true;
                break;
                
                case '列表':
                case 'list':
                $sender->sendMessage(TextFormat::GOLD.'你拥有的粒子:'."\n");
                foreach($this->getParticle($sender->getName()) as $key=>$value){
                    $sender->sendMessage(TextFormat::YELLOW.$key.TextFormat::WHITE.'->'.TextFormat::GREEN.$value."\n");
                }
                return true;
                break;
                
                case '关闭':
                case 'stop':
                $config->set('now',null);
                $config->save();
                $sender->sendMessage(TextFormat::RED.'成功关闭粒子效果');
                return true;
                break;
            }
            unset($sender,$command,$label,$args);
			}else{
				return false;
			}
		}
    }
    public function onBlockBreak(BlockBreakEvent $event){
        if ($event->isCancelled()) return;
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if(in_array($block->getId(),[63,68,323])){
        if(array_key_exists($block->getX().','.$block->getY().','.$block->getZ().','.$player->getLevel()->getName(),$this->shops->getAll())){		
            if(!$player->isOp()){
        $player->sendMessage(TextFormat::RED.'§2[粒子商店]§f: §bHey,你不是OP,不能破坏商店');			
        $event->setCancelled(true);
		return;
                }
			$this->shops->remove($block->getX().','.$block->getY().','.$block->getZ().','.$player->getLevel()->getName());
			$this->shops->save();			
            } 	
        }	
    }	
}
