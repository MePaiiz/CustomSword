<?php

namespace sword;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\scheduler\Task;

use pocketmine\level\Level;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\math\Vector2;
use pocketmine\utils\Random;
use pocketmine\item\Item;

use pocketmine\entity\Effect;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\level\particle\FloatingTextParticle;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;

class Main extends PluginBase implements Listener {
	function onEnable () {
		$plugin = "SwordMaster";
		$this->getLogger()->info("§a".$plugin."§aWorking!");

		Server::getInstance()->getPluginManager()->registerEvents($this,$this);

		$this->crirate = [336 => "30", 276 => "10"]; //Critical Rate
		$this->cri = [336 => 2, 276 => 15]; //Critical Damage
		$this->name = [336 => "§5Night§dmare§7 S§fw§7o§fr§7d", 276 => "§5B§de§5t§da§7 S§fw§7o§fr§7d"]; //Name Sword
		$this->damage = [336 => 1, 276 => 10]; //Damage
		$this->lore = [336 => "§eThis is Powerful sword\n§aTIPS: §bKill Monster Lv.1000-2000", 276 => "§athis is a powerful sword\n§eBeta tester!"]; //Lore Sword
		$this->fire = [336 => 1, 276 => 1]; //Fire
		$this->blood = [336 => 2]; //Steal Blood
		$this->info = [336 => "§r §aSteal Blood§7[§fIII§7]\n§r §cFire Attack§7[§fIV§7]", 276 => "§cFire Attack§7[§fIV§7]"]; //Sword Info
		$this->kb = [336 => 0]; //KnockBack
		$this->light = []; //lightning
		$this->potion = []; //potion
	}

	function onItem (EntityInventoryChangeEvent $event) {
		if (!$event->getEntity() instanceof Player) return false;
		$item = $event->getNewItem();
		if (isset($this->name[$item->getId()])) {
			$cri = isset($this->cri[$item->getId()]) ? "\n§r§eCrit§7:§f ".$this->cri[$item->getId()]."" : "";
			$kb = isset($this->kb[$item->getId()]) ? "\n§r§bKnockback§7:§f ".$this->kb[$item->getId()]."" : "";
			$info = isset($this->info[$item->getId()]) ? "\n§r§bAbility:\n".$this->info[$item->getId()]."" : "";
			$crirate = isset($this->crirate[$item->getId()]) ? "\n§r§6Crit Rate§7:§f ".$this->crirate[$item->getId()]."%" : "";
			$tag = "§r".$this->name[$item->getId()]."\n§r§4A§ct§4t§ca§4c§ck §7:§f ".$this->damage[$item->getId()].$kb.$cri.$crirate."\n".$info."\n";
			$lore = "§r ".$this->lore[$item->getId()];
		    $item->setCustomName($tag);
			$item->setLore([$lore]);
			$event->setNewItem($item);
		}
	}

	public function onFight(EntityDamageEvent $event) {
        if($event instanceof EntityDamageByEntityEvent) {
            $hit = $event->getEntity();
            $damager = $event->getDamager();
            $item = $damager->getInventory()->getItemInHand();
            if(isset($this->damage[$item->getId()])){
				$persend = $this->crirate[$item->getId()];
				$damage = $this->damage[$item->getId()];
				$dmgcri = $this->cri[$item->getId()];
				$hp = $hit->getHealth();
				$hp1 = $hp - $damage;
				$hp2 = $hp - $dmgcri;
				$random = rand(1, 100);
		        if($random <= $persend){
		            $dmg = $dmgcri;
		            $event->setDamage($dmg);
		            $pos = $event->getEntity()->add(0.1 * mt_rand(1, 9) * mt_rand(-1, 1), 0.1 * mt_rand(5, 9), 0.1 * mt_rand(1, 9) * mt_rand(-1, 1));
		            $criticalParticle = new FloatingTextParticle($pos, "", "§6C§er§6i§et§6i§ec§6a§el");
			        $this->getServer()->getScheduler()->scheduleDelayedTask(new EventCheckTask($this, $criticalParticle, $event->getEntity()->getLevel(), $event), 1);
			        $pos = $event->getEntity()->add(0.1 * mt_rand(1, 9) * mt_rand(-1, 1), 0.1 * mt_rand(5, 9), 0.1 * mt_rand(1, 9) * mt_rand(-1, 1));
                    $damageParticle = new FloatingTextParticle($pos, "", "§cD§4M§cG §f-".$event->getDamage());
                    $pos = $event->getEntity()->add(0.1 * mt_rand(1, 9) * mt_rand(-1, 1), 0.1 * mt_rand(5, 9), 0.1 * mt_rand(1, 9) * mt_rand(-1, 1));
                    $healthParticle = new FloatingTextParticle($pos, "", "§2H§aP§f ".$hp2."§8/§f".$hit->getMaxHealth());
                    $this->getServer()->getScheduler()->scheduleDelayedTask(new EventCheckTask($this, $damageParticle, $event->getEntity()->getLevel(), $event), 1);
                    $this->getServer()->getScheduler()->scheduleDelayedTask(new EventCheckTask($this, $healthParticle, $event->getEntity()->getLevel(), $event), 1);
		        }else{
			        $dmg = $damage;
			        $event->setDamage($dmg);
			        $pos = $event->getEntity()->add(0.1 * mt_rand(1, 9) * mt_rand(-1, 1), 0.1 * mt_rand(5, 9), 0.1 * mt_rand(1, 9) * mt_rand(-1, 1));
                    $damageParticle = new FloatingTextParticle($pos, "", "§cD§4M§cG §f-".$event->getDamage());
                    $pos = $event->getEntity()->add(0.1 * mt_rand(1, 9) * mt_rand(-1, 1), 0.1 * mt_rand(5, 9), 0.1 * mt_rand(1, 9) * mt_rand(-1, 1));
                    $healthParticle = new FloatingTextParticle($pos, "", "§2H§aP§f ".$hp1."§8/§f".$hit->getMaxHealth());
                    $this->getServer()->getScheduler()->scheduleDelayedTask(new EventCheckTask($this, $damageParticle, $event->getEntity()->getLevel(), $event), 1);
                    $this->getServer()->getScheduler()->scheduleDelayedTask(new EventCheckTask($this, $healthParticle, $event->getEntity()->getLevel(), $event), 1);
		        }
			    if(isset($this->fire[$item->getId()])){
				    $hit->setOnFire($this->fire[$item->getId()]);
			    }
			    if(isset($this->kb[$item->getId()])){
				    $event->setKnockBack($this->kb[$item->getId()]);
			    }
			    if(isset($this->blood[$item->getId()])){
					$damager->setHealth($damager->getHealth() + $this->blood[$item->getId()]);
					$hit->setHealth($hit->getHealth() - $this->blood[$item->getId()]);
				}
				if(isset($this->potion[$item->getId()])){
					$hit->addEffect(Effect::getEffect($this->potion[$item->getId()])->setDuration(13*20)->setAmplifier(1));
				}
				if(isset($this->light[$item->getId()])){
					$x = $hit->getX();
                    $y = $hit->getY();
                    $z = $hit->getZ();
                    $hit->getLevel()->spawnLightning(new Vector3($x, $y, $z));
				}
            }
        }
    }
    
    public function eventCheck(FloatingTextParticle $particle, Level $level, $event) {
        if ($event instanceof EntityDamageEvent) { 
            if ($event->isCancelled()) { 
                return;
            } 
        }
        $level->addParticle($particle); 
        $this->getServer()->getScheduler()->scheduleDelayedTask(new DeleteParticlesTask($this, $particle, $event->getEntity()->getLevel()), 20);
    }
    
    public function deleteParticles(FloatingTextParticle $particle, Level $level) {
        $particle->setInvisible();
        $level->addParticle($particle);
    } 
}