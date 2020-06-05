# InfectedGM
InfectedGM for [PMMP](https://github.com/pmmp/PocketMine-MP) using [gameapi](https://github.com/thebigsmileXD/gameapi)

A virus is spreading. Try not to get infected! Run for your life!
## Download
Grab a phar from [Poggit](https://poggit.pmmp.io/ci/thebigsmileXD/InfectedGM)
## Gameplay
- **Inspired by Hypixel, you can read the gameplay here: https://hypixel.net/threads/new-gamemode-minecraft-infected.334387/**
- At start, a random player is chosen to be the first infected person
- Infected players have to haunt down other players
- When all players got infected, the game ends
- To make locating players and infected players easier, ambient sounds randomly play.
Infected players have a zombie skin (can be modified, change the infected_skin.png file under the plugin data)
## Setup
**Please use the plugin on a seperate server to your main server (lobby etc)** This is because the plugin modifies gameplay alot. You can use the `/transferserver` command to send players to the server.

Setup is really easy. The world can be automatically generated. To replace the map with another map, go to `/plugin_data/InfectedGM/worlds` and replace your 'infectedgm' data with your worlds data (you should keep the level.dat though)

There is a setup command, that makes your life substantially easier

`/infectedgm` opens an UI with settings and options to create new worlds/arenas

The generated world is a void world, i suggest to use a world editor like [MagicWE2](https://github.com/thebigsmileXD/MagicWE2) to place blocks.

Remember to use `/setworldspawn` in your map!

When you are done with building and setting the world spawn in a map, use `/infectedgm endsetup`! If you don't, the world won't get saved!

Joining is done by using signs, but you can add any event for joining that you'd like - in JoinEventListener.php

Sign setup:
```
L1: [InfectedGM]
L2: mapname
L3: 
L4: 
```
Then, click on it, and you are set.

Only TNT blocks will work.

Players will not be damaged and can not build in the world, but there are setting files if you really need to change anything
### Setup rewards
Use [gamerewards](https://github.com/thebigsmileXD/gamerewards) to give the winner rewards and execute commands.

There is also a GameWinEvent getting called containing the winning players
## Commands
| Command | Description | Permission |
| --- | --- | --- |
| `/infectedgm`,`/infectedgm setup` | `Main command for setup` | `infectedgm.command`,`infectedgm.command.setup`, |
| `/infectedgm leave` | `Used to leave a game` | `infectedgm.command.leave` |
| `/infectedgm forcestart` | `Force the start of an arena you are in` | `infectedgm.command.forcestart` |
| `/infectedgm stop` | `Stops the current game` | `infectedgm.command.stop` |
| `/infectedgm endsetup` | `Stops the setup and saves the world` | `infectedgm.command.endsetup` |
| `/infectedgm info` | `Information about the plugin` | `infectedgm.command.information` |
| `/infectedgm status` | `Status, TPS, Player count/percentage of InfectedGM arenas` | `infectedgm.command.status` |
## From source
**You need to set up DEVirion and install the [gameapi](https://github.com/thebigsmileXD/gameapi) virion properly if you are running from source!**
(turn over to poggit for a compiled phar)
**Please search up how this is done yourself!**

## Disclaimer
You can modify the code by your needs and wills (see [LICENSE](https://github.com/thebigsmileXD/InfectedGM/blob/master/LICENSE)).
