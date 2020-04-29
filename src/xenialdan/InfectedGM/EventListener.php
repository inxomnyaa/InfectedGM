<?php

namespace xenialdan\InfectedGM;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use xenialdan\gameapi\API;
use xenialdan\gameapi\Arena;
use xenialdan\gameapi\event\WinEvent;

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
        $player = $ev->getEntity();
        $hitter = $ev->getDamager();
        if (!$player instanceof Player && !$hitter instanceof Player) return;
        if (API::isPlaying($player, Loader::getInstance()) && API::isArenaOf(Loader::getInstance(), $player->getLevel())) {
            $arena = API::getArenaByLevel(Loader::getInstance(), $player->getLevel());
            if ($arena === null || $arena->getState() !== Arena::INGAME) return;
            $ev->setCancelled();
            $teamInfected = API::getTeamOfPlayer($hitter);
            if (!$teamInfected->getName() === Loader::TEAM_INFECTED) return;
            if (!API::getTeamOfPlayer($player)->getName() === Loader::TEAM_PLAYERS) return;
            $arena->joinTeam($player, Loader::TEAM_INFECTED);
            if (count($teamInfected->getPlayers()) === 0) {
                $ev = new WinEvent($arena->getOwningGame(), $arena, $teamInfected);
                $ev->call();
                $ev->announce();
                API::resetArena($arena);
            }
        }
    }
}