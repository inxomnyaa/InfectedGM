<?php

namespace xenialdan\InfectedGM;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use xenialdan\customui\elements\Button;
use xenialdan\customui\elements\Input;
use xenialdan\customui\elements\Label;
use xenialdan\customui\elements\StepSlider;
use xenialdan\customui\windows\CustomForm;
use xenialdan\customui\windows\ModalForm;
use xenialdan\customui\windows\SimpleForm;
use xenialdan\gameapi\API;
use xenialdan\gameapi\Arena;
use xenialdan\gameapi\event\WinEvent;
use xenialdan\gameapi\Game;
use xenialdan\gameapi\Team;
use xenialdan\InfectedGM\commands\InfectedGMCommand;

class Loader extends Game
{
    const TEAM_PLAYERS = "Players";
    const TEAM_INFECTED = "Infected";
    /** @var Loader */
    private static $instance = null;
    /** @var Skin */
    protected $skin;

    /**
     * Returns an instance of the plugin
     * @return Loader
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    public function onLoad()
    {
        self::$instance = $this;
    }

    public function onEnable()
    {
        $this->saveResource("infected_skin.png");
        $this->skin = new Skin("infectedGM_skin", \xenialdan\skinapi\API::fromImage(imagecreatefrompng($this->getDataFolder() . "infected_skin.png")));
        $this->getServer()->getPluginManager()->registerEvents(new JoinGameListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new LeaveGameListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getCommandMap()->register("InfectedGM", new InfectedGMCommand($this));
        /** @noinspection PhpUnhandledExceptionInspection */
        API::registerGame($this);
        foreach (glob($this->getDataFolder() . "*.json") as $v) {
            $this->addArena($this->getNewArena($v));
        }
    }

    /**
     * Create and return a new arena, used for addArena in onLoad, setupArena and resetArena (in @param string $settingsPath The path to the .json file used for the settings. Basename should be levelname
     * @return Arena
     * @see ArenaAsyncCopyTask)
     */
    public function getNewArena(string $settingsPath): Arena
    {
        $settings = new InfectedGMSettings($settingsPath);
        $levelname = basename($settingsPath, ".json");
        $arena = new Arena($levelname, $this, $settings);
        $team = new Team(TextFormat::RESET, self::TEAM_PLAYERS);
        $team->setMinPlayers(2);
        $team->setMaxPlayers((int)$settings->maxPlayers);
        $arena->addTeam($team);
        $team = new Team(TextFormat::DARK_GREEN, self::TEAM_INFECTED);
        $team->setMinPlayers(0);
        $team->setMaxPlayers((int)$settings->maxPlayers);
        $arena->addTeam($team);
        return $arena;
    }

    /**
     * @param Arena $arena
     */
    public function startArena(Arena $arena): void
    {
        Loader::getInstance()->getServer()->broadcastMessage(TextFormat::GRAY."Turn on sounds for better experience",$arena->getPlayers());
        $teamPlayers = $arena->getTeamByName(self::TEAM_PLAYERS);
        if (count($arena->getTeamByName(self::TEAM_INFECTED)->getPlayers()) < 1) {
            $arena->joinTeam($teamPlayers->getPlayers()[array_rand($teamPlayers->getPlayers())], self::TEAM_INFECTED);
        }
        $arena->bossbar->setSubTitle()->setTitle(count($teamPlayers->getPlayers()) . ' players alive')->setPercentage(1);
        $this->getScheduler()->scheduleDelayedRepeatingTask(new ClosureTask(function (int $currentTick) use ($arena) {
            foreach ($arena->getPlayers() as $player) {
                if (mt_rand(0, 30) > 1) continue;
                if ($arena->getTeamByPlayer($player)->getName() === self::TEAM_INFECTED) {
                    $spk = new PlaySoundPacket();
                    [$spk->x, $spk->y, $spk->z] = [$player->x, $player->y, $player->z];
                    $spk->volume = 1.0;
                    $spk->soundName = "mob.zombie.say";
                    $spk->pitch = 0.7;
                    $spk->pitch = 0.6;
                    $spk->soundName = "mob.zombie.remedy";
                    $arena->getLevel()->broadcastGlobalPacket($spk);
                } else {
                    $spk = new PlaySoundPacket();
                    [$spk->x, $spk->y, $spk->z] = [$player->x, $player->y, $player->z];
                    $spk->volume = 1.0;
                    switch (mt_rand(0, 3)) {
                        case 0:
                            $spk->soundName = "mob.horse.breathe";
                            $spk->pitch = 0.5;
                            break;
                        case 1:
                            $spk->soundName = "crossbow.loading.middle";
                            $spk->pitch = 0.8;
                            break;
                        case 2:
                            $spk->soundName = "crossbow.quick_charge.middle";
                            $spk->pitch = 0.8;
                            break;
                        default:
                            $spk->soundName = "jump.wood";
                            $spk->pitch = 0.8;
                            break;
                    }
                    $arena->getLevel()->broadcastGlobalPacket($spk);
                }
            }
        }), 20, 20);
    }

    /**
     * Called AFTER API::stopArena, do NOT use $arena->stopArena() in this function - will result in an recursive call
     * @param Arena $arena
     */
    public function stopArena(Arena $arena): void
    {
    }

    /**
     * Called right when a player joins a team in an arena of this game. Used to set up players
     * @param Player $player
     */
    public function onPlayerJoinTeam(Player $player): void
    {
        if (($team = API::getTeamOfPlayer($player))->getName() === self::TEAM_INFECTED) {
            $player->setSkin($this->skin);
            /** @var Player $player */
            $player->getLevel()->getServer()->broadcastMessage(TextFormat::DARK_GREEN . "{$player->getDisplayName()} got infected!", API::getArenaOfPlayer($player)->getPlayers());
            $player->addTitle(TextFormat::DARK_GREEN . "You have been infected!", TextFormat::GREEN . "Catch a human to infect!");
            $player->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 30, 100, false));
            $spk = new PlaySoundPacket();
            [$spk->x, $spk->y, $spk->z] = [$player->x, $player->y, $player->z];
            $spk->volume = 1.0;
            $spk->pitch = 0.6;
            $spk->soundName = "mob.zombie.remedy";
            $player->dataPacket($spk);
            $spk->soundName = "mob.husk.death";
            $spk->pitch = 0.7;
            $player->dataPacket($spk);
        }
        if(count(($arena = API::getArenaOfPlayer($player))->getTeamByName(self::TEAM_PLAYERS)->getPlayers()) < 1){
            $ev = new WinEvent($this, $arena, $arena->getTeamByName(self::TEAM_INFECTED));
            $ev->call();
            $ev->announce();
            $arena->stopArena();
            API::resetArena($arena);
        }
    }

    /**
     * A method for setting up an arena.
     * @param Player $player The player who will run the setup
     */
    public function setupArena(Player $player): void
    {
        $form = new SimpleForm("InfectedGM arena setup");
        $na = "New arena";
        $form->addButton(new Button($na));
        $ea = "Edit arena";
        $form->addButton(new Button($ea));
        $form->setCallable(function (Player $player, $data) use ($na, $ea) {
            if ($data === $na) {
                $form = new SimpleForm("InfectedGM arena setup", "New arena via");
                $nw = "New world";
                $form->addButton(new Button($nw));
                $ew = "Existing world";
                $form->addButton(new Button($ew));
                $form->setCallable(function (Player $player, $data) use ($ew, $nw) {
                    $new = true;
                    if ($data === $ew) {
                        $new = false;
                        $form = new SimpleForm("InfectedGM arena setup", "New arena from $data");
                        foreach (API::getAllWorlds() as $worldName) {
                            $form->addButton(new Button($worldName));
                        }
                    } else {
                        $form = new CustomForm("InfectedGM arena setup");
                        $form->addElement(new Label("New arena from $data"));
                        $form->addElement(new Input("World name", "Example: bw4x1"));
                    }
                    $form->setCallable(function (Player $player, $data) use ($new) {
                        $setup["name"] = $new ? $data[1] : $data;
                        if ($new) {
                            API::$generator->generateLevel($setup["name"]);
                        }
                        Server::getInstance()->loadLevel($setup["name"]);
                        $form = new CustomForm("InfectedGM teams setup");
                        $form->addElement(new StepSlider("Maximum players", array_keys(array_fill(2, 15, ""))));
                        $form->setCallable(function (Player $player, $data) use ($new, $setup) {
                            $setup["maxplayers"] = intval($data[0]);
                            //New arena
                            $settings = new InfectedGMSettings($this->getDataFolder() . $setup["name"] . ".json");
                            $settings->maxPlayers = $setup["maxplayers"];
                            $settings->save();
                            $this->addArena($this->getNewArena($this->getDataFolder() . $setup["name"] . ".json"));
                            //Messages
                            $player->sendMessage(TextFormat::GOLD . TextFormat::BOLD . "Done! InfectedGM arena was set up with following settings:");
                            $player->sendMessage(TextFormat::AQUA . "World name: " . TextFormat::DARK_AQUA . $setup["name"]);
                            $player->sendMessage(TextFormat::AQUA . "Maximum players: " . TextFormat::DARK_AQUA . $setup["name"]);
                        });
                        $player->sendForm($form);
                    });
                    $player->sendForm($form);
                });
                $player->sendForm($form);
            } else if ($data === $ea) {
                $form = new SimpleForm("Edit InfectedGM arena");
                $build = "Build in world";
                $button = new Button($build);
                $button->addImage(Button::IMAGE_TYPE_PATH, "textures/ui/icon_recipe_construction");
                $form->addButton($button);
                $delete = "Delete arena";
                $button = new Button($delete);
                $button->addImage(Button::IMAGE_TYPE_PATH, "textures/ui/trash");
                $form->addButton($button);
                $form->setCallable(function (Player $player, $data) use ($delete, $build) {
                    switch ($data) {
                        case $build:
                        {
                            $form = new SimpleForm($build, "Select the arena you'd like to build in");
                            foreach ($this->getArenas() as $arena) $form->addButton(new Button($arena->getLevelName()));
                            $form->setCallable(function (Player $player, $data) {
                                $worldname = $data;
                                $arena = API::getArenaByLevelName($this, $worldname);
                                $this->getServer()->broadcastMessage("Stopping arena, reason: Admin actions", $arena->getPlayers());
                                $arena->stopArena();
                                $arena->setState(Arena::SETUP);
                                if (!$this->getServer()->isLevelLoaded($worldname)) $this->getServer()->loadLevel($worldname);
                                $player->teleport($arena->getLevel()->getSpawnLocation());
                                $player->setGamemode(Player::CREATIVE);
                                $player->setAllowFlight(true);
                                $player->setFlying(true);
                                $player->getInventory()->clearAll();
                                $arena->getLevel()->stopTime();
                                $arena->getLevel()->setTime(Level::TIME_DAY);
                                $player->sendMessage(TextFormat::GOLD . "You may now freely edit the arena. You will not be able to break iron, gold or stained clay blocks, nor to place concrete YET");
                            });
                            $player->sendForm($form);
                            break;
                        }
                        case $delete:
                        {
                            $form = new SimpleForm("Delete InfectedGM arena", "Select an arena to remove. The world will NOT be deleted");
                            foreach ($this->getArenas() as $arena) $form->addButton(new Button($arena->getLevelName()));
                            $form->setCallable(function (Player $player, $data) {
                                $worldname = $data;
                                $form = new ModalForm("Confirm delete", "Please confirm that you want to delete the arena \"$worldname\"", "Delete $worldname", "Abort");
                                $form->setCallable(function (Player $player, $data) use ($worldname) {
                                    if ($data) {
                                        $arena = API::getArenaByLevelName($this, $worldname);
                                        $this->deleteArena($arena) ? $player->sendMessage(TextFormat::GREEN . "Successfully deleted the arena") : $player->sendMessage(TextFormat::RED . "Removed the arena, but config file could not be deleted!");
                                    }
                                });
                            });
                            $player->sendForm($form);
                            break;
                        }
                    }
                });
                $player->sendForm($form);
            }
        });
        $player->sendForm($form);
    }

    /**
     * @inheritDoc
     */
    public function removeEntityOnArenaReset(Entity $entity): bool
    {
        return true;
    }
}