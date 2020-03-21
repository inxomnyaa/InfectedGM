# InfectedGM
InfectedGM for [PMMP](https://github.com/pmmp/PocketMine-MP) using [gameapi](https://github.com/thebigsmileXD/gameapi)
## Download
Grab a phar from [Poggit](https://poggit.pmmp.io/ci/thebigsmileXD/InfectedGM)
## Setup
**Please use the plugin on a seperate server to your main server (lobby etc)** This is because the plugin modifies gameplay alot. You can use the `/transferserver` command to send players to the server.

Setup is really easy. The world can be automatically generated. To replace the map with another map, go to `/plugin_data/InfectedGM/worlds` and replace your 'infectedGM' data with your worlds data (you should keep the level.dat though)

There is a setup command, that makes your life substantially easier

`/infectedGM` opens an UI with settings and options to create new worlds/arenas

The generated world is a void world, i suggest to use a world editor like [MagicWE2](https://github.com/thebigsmileXD/MagicWE2) to place blocks.

Remember to use `/setworldspawn` in your map!

When you are done with building and setting the world spawn in a map, use `/infectedGM endsetup`! If you don't, the world won't get saved!

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
| `/infectedGM`,`/infectedGM setup` | `Main command for setup` | `infectedGM.command`,`infectedGM.command.setup`, |
| `/infectedGM leave` | `Used to leave a game` | `infectedGM.command.leave` |
| `/infectedGM forcestart` | `Force the start of an arena you are in` | `infectedGM.command.forcestart` |
| `/infectedGM stop` | `Stops the current game` | `infectedGM.command.stop` |
| `/infectedGM endsetup` | `Stops the setup and saves the world` | `infectedGM.command.endsetup` |
| `/infectedGM info` | `Information about the plugin` | `infectedGM.command.information` |
| `/infectedGM status` | `Status, TPS, Player count/percentage of InfectedGM arenas` | `infectedGM.command.status` |
## From source
**You need to set up DEVirion and install the [gameapi](https://github.com/thebigsmileXD/gameapi) virion properly if you are running from source!**
(turn over to poggit for a compiled phar)
**Please search up how this is done yourself!**

## Disclaimer
You can modify the code by your needs and wills (see [LICENSE](https://github.com/thebigsmileXD/InfectedGM/blob/master/LICENSE)).
