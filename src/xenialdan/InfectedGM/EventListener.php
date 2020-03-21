<?php

namespace xenialdan\InfectedGM;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\level\sound\GenericSound;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\gameapi\API;
use xenialdan\gameapi\Arena;

/**
 * Class EventListener
 * @package xenialdan\InfectedGM
 * Primes TNT blocks
 */
class EventListener implements Listener
{

    public function onHitPlayer(EntityDamageByEntityEvent $ev)
    {
        if ($ev->getCause() !== EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK) return;
        if (!($player = $ev->getEntity()) && !($hitter = $ev->getDamager() instanceof Player)) return;
        if (API::isArenaOf(Loader::getInstance(), $player->getLevel()) && API::isPlaying($player, Loader::getInstance())) {
            $arena = API::getArenaByLevel(Loader::getInstance(), $player->getLevel());
            if ($arena->getState() !== Arena::INGAME) return;
            $ev->setCancelled();
            if(!API::getTeamOfPlayer($hitter)->getName() === Loader::TEAM_INFECTED) return;
            if(!API::getTeamOfPlayer($player)->getName() === Loader::TEAM_PLAYERS) return;
            $arena->joinTeam($player,Loader::TEAM_INFECTED);
        }
    }
}