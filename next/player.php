<?php include("config.php"); ?>
<?php include("head.php"); ?>
<?php include("pdet.php"); ?>

<body>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
<?php include("navbar.php") ?>
      </div>
    </div>
    <?php
    $name = $db->real_escape_string($_GET['name']);
    $name = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    //$name = preg_replace("/[^a-zA-Z0-9]+/", "", $name);
    $json = file_get_contents('medals.json');
    $players = json_decode($json,true);
    $player = [];
    if(!isset($players[$name])) {
        $player['medalGold'] = 0;
        $player['medalSilver'] = 0;
        $player['medalBronze'] = 0;
    } else {
        $player = $players[$name];
    }

    $result = $db->query("SELECT COUNT(*) as count,SUM(wonGame=1) as won , SUM(ggs.framesToCalculatePlaytime)/30 as playtime FROM glestgameplayerstats s, glestgamestats ggs WHERE playerName='$name' AND controltype>4 AND s.gameUUID=ggs.gameUUID"); //controltype needs to be 5 to prevent counting of cpus when their name is not set
	$row = $result->fetch_assoc();
	$totalGames = $row['count'];
	$won = $row['won'];
	$playtime = secondsToTime($row['playtime']);
	$lost = $row['count']-$row['won'];
	//name
	//we need this if we change to playeruuids
	//$result = $db->query("SELECT playerName FROM glestgameplayerstats WHERE playerName='$name' LIMIT 1");
	//$name = $result->fetch_assoc()['playerName'];
	//faction
	$result = $db->query("SELECT factionTypeName,COUNT(*) as count FROM glestgameplayerstats WHERE playerName='$name' GROUP BY factionTypeName ORDER BY count DESC");
	$faction = $result->fetch_assoc();
	//map
	$result = $db->query("SELECT srv.map,COUNT(srv.lasttime) as cnt FROM glestserver AS srv INNER JOIN glestgameplayerstats AS ply ON srv.gameUUID=ply.gameUUID WHERE ply.playerName='$name' AND ply.wonGame = 1 GROUP BY srv.map ORDER BY cnt DESC");

	$maps = [];
	$map = '';
	$mi = false;
	while($row = $result->fetch_assoc()) {
        $maps[$row['map']] = $row['cnt'];
        if(!$mi) {
            $map = ['map'=>$row['map'],'count'=>$row['cnt']];
            $mi = true;
        }
	}
	//tileset
	$result = $db->query("SELECT a.tileset as tileset, COUNT(*) as count FROM glestserver a LEFT JOIN glestgamestats b ON a.gameUUID = b.gameUUID INNER JOIN glestgameplayerstats c ON a.gameUUID = c.gameUUID AND c.playerName='$name' WHERE status = 3 GROUP BY a.tileset ORDER BY count DESC");
	$tileset = $result->fetch_assoc();

    ?>
    <div class="row">
      <div class="col-md-12">
        <h2><?=$name."s profile"?> <?=isset($pdet[$name]) ? '<img src="img/flags/'.strtolower($pdet[$name]["country"]).'.png"/>':''?></h2>
      </div>

      <div class="col-md-4">
        <h3>General Stats</h3>
        <table class="table table-bordered table-striped">
          <tbody>
            <tr>
              <td>Total Games</td>
              <td><?='<b>'.$totalGames.'</b> (<small>Won:</small> '.$won.' <small>Lost:</small> '.$lost.')'?></td>
            </tr>            <tr>
              <td>Total Time played</td>
              <td><?php
              if($playtime['d']>0){
                echo "<b>".$playtime['d']."</b> <small>days</small> ";
              }
              echo "<b>".$playtime['h']."</b> <small>hours</small> <b>".$playtime['m']."</b> <small>min.</small> ";
              ?></td>
            </tr>
           <tr>
              <td>Most played Faction</td>
              <td><?='<b>'.$faction['factionTypeName'].'</b> ('.$faction['count'].' <small>times</small>)'?></td>
            </tr>
            <tr>
              <td>Most played Map</td>
              <!-- we don't use this now, maybe later <td><?='<b>'.ucfirst($map['map']).'</b> ('.$map['count'].' <small>times</small>)'//map is lowercase somehow?></td>-->
              <td><?='<b>'.$map['map'].'</b> ('.$map['count'].' <small>times</small>)'//map is lowercase somehow?></td>
            </tr>
            <tr>
              <td>Most played Tileset</td>
              <!-- we don't use this now, maybe later <td><?='<b>'.ucfirst($tileset['tileset']).'</b> ('.$tileset['count'].' <small>times</small>)'//tileset is lowercase somehow?></td>-->
              <td><?='<b>'.$tileset['tileset'].'</b> ('.$tileset['count'].' <small>times</small>)'//tileset is lowercase somehow?></td>
            </tr>
            <tr>
              <td><img height="20px" src="img/medal_gold.png" title="Gold Medal" alt="Gold Medal" /> Gold Medals</td>
              <td><?=$player['medalGold']?></td>
            </tr>
            <tr>
              <td><img height="20px" src="img/medal_silver.png" title="Silver Medal" alt="Silver Medal" /> Silver Medals</td>
              <td><?=$player['medalSilver']?></td>
            </tr>
            <tr>
              <td><img height="20px" src="img/medal_bronze.png" title="Bronze Medal" alt="Bronze Medal" /> Bronze Medals</td>
              <td><?=$player['medalBronze']?></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="col-md-4">
        <h3>Last 8 games</h3>
        <?php
        $result = $db->query("SELECT gameUUID FROM glestgameplayerstats WHERE playerName='$name' ORDER BY lasttime DESC LIMIT 0,8");
        $games = array();
        while($row = $result->fetch_assoc()) {
            array_push($games,$row['gameUUID']);
        }
        unset($result);
        ?>
        <table class="table table-bordered table-striped">
          <?php
          foreach($games as $game){
            unset($result);
            unset($row);
            $result = $db->query("SELECT * FROM glestserver WHERE gameUUID='$game' LIMIT 1");
            $row = $result->fetch_assoc();
            if($row['serverTitle'] != ""){
            ?>
            <tr>
            <td><a href="game.php?uuid=<?=$game?>"><?=$row['serverTitle']?></a></td>
            <td><?=$row['lasttime']?></td>
            </tr>
            <?php
          }
          }
          ?>
        </table>
      </div>
    <div class="col-md-4">
        <h3>Top 8 buddies</h3>
        <?php

        /*
        $player_games = $db->select("glestgameplayerstats","gameUUID",["playerName"=>$playerName ]);
        $buddies = [];

        foreach($player_games as $gameUUID) {
            $buddies[] = $db->select("glestgameplayerstats","playerName",["gameUUID"=>$gameUUID,"playerName[!]"=>[$playerName,"AI1","AI2","AI3","AI4","AI5","KI1","KI2","KI3","KI4"]]);
        }
        $buddies = call_user_func_array('array_merge', $buddies);
        $buddies = array_count_values($buddies);
        arsort($buddies);
        print_r($buddies);
        */

        $player_games = $db->query("SELECT gameUUID FROM glestgameplayerstats WHERE playerName='$name'");
        $buddies = [];
        while($row = $player_games->fetch_assoc()) {
                $gameUUID = $row['gameUUID'];
                $buddies_p = $db->query("SELECT playerName FROM glestgameplayerstats WHERE gameUUID='$gameUUID' AND playerName NOT IN ('$name','AI1','AI2','AI3','AI4','AI5','AI6','KI1','KI2','KI3','KI4','CPUAI1','CPUAI2')");
               while($row2 = $buddies_p->fetch_assoc()) {
                 $buddies[] = $row2['playerName'];
               }
        }


        //$buddies = call_user_func_array('array_merge', $buddies);
        $buddies = array_count_values($buddies);
        arsort($buddies);

        $buddies = array_slice($buddies,0,8);

        ?>
        <table class="table table-bordered table-striped">
          <?php foreach($buddies as $buddy=>$bgames){ ?>
            <tr>
                <td><a href="player.php?name=<?=$buddy?>"><?=$buddy?></a></td>
                <td><?=$bgames?></td>
            </tr>
        <?php } ?>
        </table>
      </div>
    </div>

    <?php
    $result = $db->query("SELECT COUNT(*) as count FROM glestgameplayerstats WHERE playerName='$name' AND wonGame=1 AND factionTypeName='indian'");
    $row = $result->fetch_assoc();
    $indian = intVal($row['count']);

    $result = $db->query("SELECT COUNT(*) as count FROM glestgameplayerstats WHERE playerName='$name' AND wonGame=1 AND factionTypeName='tech'");
    $row = $result->fetch_assoc();
    $tech = intVal($row['count']);

    $result = $db->query("SELECT COUNT(*) as count FROM glestgameplayerstats WHERE playerName='$name' AND wonGame=1 AND factionTypeName='magic'");
    $row = $result->fetch_assoc();
    $magic = intVal($row['count']);

    $result = $db->query("SELECT COUNT(*) as count FROM glestgameplayerstats WHERE playerName='$name' AND wonGame=1 AND factionTypeName='egypt'");
    $row = $result->fetch_assoc();
    $egypt = intVal($row['count']);

    $result = $db->query("SELECT COUNT(*) as count FROM glestgameplayerstats WHERE playerName='$name' AND wonGame=1 AND factionTypeName='norsemen'");
    $row = $result->fetch_assoc();
    $norsemen = intVal($row['count']);

    $result = $db->query("SELECT COUNT(*) as count FROM glestgameplayerstats WHERE playerName='$name' AND wonGame=1 AND factionTypeName='persian'");
    $row = $result->fetch_assoc();
    $persian = intVal($row['count']);

    $result = $db->query("SELECT COUNT(*) as count FROM glestgameplayerstats WHERE playerName='$name' AND wonGame=1 AND factionTypeName='romans'");
    $row = $result->fetch_assoc();
    $romans = intVal($row['count']);

    $result = $db->query("SELECT COUNT(*) as count FROM glestgameplayerstats WHERE playerName='$name' AND factionTypeName NOT IN ('tech','magic','indian','persian','egypt','romans','norsemen') AND wonGame=1");
    $row = $result->fetch_assoc();
    $other = intVal($row['count']);

    ?>

      <div class="col-md-12">
        <div class="row">
            <div class="col-md-12">
              <h3>Achievements (WIP)</h3>
            </div>
            <!--<div class="col-md-3 ach-box ach-locked">
              <h3>Master</h3>
              <img src="img/achievements/wiseoldman.png"/>
              <p>Won more than 50 games with CPU Mega 2.5</p>
            </div>
            <div class="col-md-3 ach-box ach-locked">
              <h3>Explorer</h3>
              <img src="img/achievements/pirate-penguin.png"/>
              <p>Played 50 different maps</p>
            </div> -->
            <div class="col-md-3 ach-box <?=intVal($won)>=10?'ach-unlocked':'ach-locked'?>">
              <h3>Pioneer</h3>
              <img src="img/achievements/1382196361.png"/>
              <p>Won more than 10 games</p>
            </div>
            <div class="col-md-3 ach-box <?=intVal($won)>=100?'ach-unlocked':'ach-locked'?>">
              <h3>Eraser</h3>
              <img src="img/achievements/nuclear-skull.png"/>
              <p>Won more than 100 games</p>
            </div>
            <div class="col-md-3 ach-box <?=intVal($won)>=500?'ach-unlocked':'ach-locked'?>">
              <h3>Unstoppable</h3>
              <img src="img/achievements/ryanlerch-sword-battleaxe-shield.png"/>
              <p>Won more than 500 games</p>
            </div>
            <div class="col-md-3 ach-box <?=intVal($won)>=1000?'ach-unlocked':'ach-locked'?>">
              <h3>Barbarian</h3>
              <img src="img/achievements/barbarian.png"/>
              <p>Won more than 1000 games</p>
            </div>
           <!-- <div class="col-md-3 ach-box ach-locked">
              <h3>One For All</h3>
              <img src="img/achievements/Pagan-Knife.png"/>
              <p>Had the biggest number of kills while lowest resource</p>
            </div>
            <div class="col-md-3 ach-box ach-locked">
              <h3>Knight</h3>
              <img src="img/achievements/tonyk-Gnu-Knight.png"/>
              <p>Won 1 vs 1</p>
            </div>
            <div class="col-md-3 ach-box ach-locked">
              <h3>Guard</h3>
              <img src="img/achievements/warrior2.png"/>
              <p>Won map Trapped vs CPU Mega 2.5</p>
            </div>
            <div class="col-md-3 ach-box ach-locked">
              <h3>Tux</h3>
              <img src="img/achievements/1309441605.png"/>
              <p>Played at least one game in Linux</p>
            </div>-->


            <div class="col-md-3 ach-box <?=$egypt>=10?'ach-unlocked':'ach-locked'?>">
              <h3>Pharaoh</h3>
              <img src="img/achievements/pharaoh.png"/>
              <p>Won 10 games as Egypt</p>
            </div>
            <div class="col-md-3 ach-box <?=$romans>=10?'ach-unlocked':'ach-locked'?>">
              <h3>Colosseum</h3>
              <img src="img/achievements/roman.png"/>
              <p>Won 10 games as Roman</p>
            </div>
            <div class="col-md-3 ach-box <?=$persian>=10?'ach-unlocked':'ach-locked'?>">
              <h3>Achaemenes</h3>
              <img src="img/achievements/persian.png"/>
              <p>Won 10 games as Persian</p>
            </div>
            <div class="col-md-3 ach-box <?=$indian>=10?'ach-unlocked':'ach-locked'?>">
              <h3>Bears Claw</h3>
              <img src="img/achievements/indian.png"/>
              <p>Won 10 games as Indian</p>
            </div>
            <div class="col-md-3 ach-box <?=$norsemen>=10?'ach-unlocked':'ach-locked'?>">
              <h3>Thors hammer</h3>
              <img src="img/achievements/norsmen.png"/>
              <p>Won 10 games as Norsemen</p>
            </div>
            <div class="col-md-3 ach-box <?=$magic>=10?'ach-unlocked':'ach-locked'?>">
              <h3>Cauldron</h3>
              <img src="img/achievements/magic.png"/>
              <p>Won 10 games as Magic</p>
            </div>
            <div class="col-md-3 ach-box <?=$tech>=10?'ach-unlocked':'ach-locked'?>">
              <h3>Technician</h3>
              <img src="img/achievements/tech.png"/>
              <p>Won 10 games as Tech</p>
            </div>
            <div class="col-md-3 ach-box <?=$other>=10?'ach-unlocked':'ach-locked'?>">
              <h3>Adventure</h3>
              <img src="img/achievements/adventure.png"/>
              <p>Won 10 games with non-MegaPack faction</p>
            </div>

            <div class="col-md-3 ach-box <?=$maps['conflict']>=10 ? 'ach-unlocked':'ach-locked'?>">
              <h3>Conflict</h3>
              <img src="https://docs.megaglest.org/images/thumb/8/8f/Conflict_Map.png/300px-Conflict_Map.png" class="img-full"/>
              <p>Won map "Conflict" 10 times</p>
            </div>
            <div class="col-md-3 ach-box <?=$maps['loggerheads']>=10 ? 'ach-unlocked':'ach-locked'?>">
              <h3>Loggerheads</h3>
              <img src="https://docs.megaglest.org/images/thumb/0/06/Loggerheads_Map.png/300px-Loggerheads_Map.png" class="img-full"/>
              <p>Won map "Loggerheads" 10 times</p>
            </div>
            <div class="col-md-3 ach-box <?=$maps['baseit']>=10 ? 'ach-unlocked':'ach-locked'?>">
              <h3>Base it</h3>
              <img src="img/achievements/unkown_map.png" class="img-full"/>
              <p>Won map "Base it" 10 times</p>
            </div>
            <div class="col-md-3 ach-box <?=$maps['nomasters']>=10 ? 'ach-unlocked':'ach-locked'?>">
              <h3>No masters</h3>
              <img src="img/achievements/unkown_map.png" class="img-full"/>
              <p>Won map "No masters" 10 times</p>
            </div>
            <div class="col-md-3 ach-box <?=$maps['riveroftears']>=10 ? 'ach-unlocked':'ach-locked'?>">
              <h3>River of tears</h3>
              <img src="img/achievements/unkown_map.png" class="img-full"/>
              <p>Won map "River of tears" 10 times</p>
            </div>
            <div class="col-md-3 ach-box <?=$maps['foresthighland']>=10 ? 'ach-unlocked':'ach-locked'?>">
              <h3>Forest highland</h3>
              <img src="img/achievements/unkown_map.png" class="img-full"/>
              <p>Won map "Forest highland" 10 times</p>
            </div>
            <div class="col-md-3 ach-box <?=$maps['trapped']>=10 ? 'ach-unlocked':'ach-locked'?>">
              <h3>Trapped</h3>
              <img src="img/achievements/unkown_map.png" class="img-full"/>
              <p>Won map "Trapped" 10 times</p>
            </div>
            <div class="col-md-3 ach-box <?=$maps['goldenspots']>=10 ? 'ach-unlocked':'ach-locked'?>">
              <h3>Golden spots</h3>
              <img src="img/achievements/unkown_map.png" class="img-full"/>
              <p>Won map "Golden spots" 10 times</p>
            </div>
            <div class="col-md-3 ach-box <?=$maps['fiveonthree']>=10 ? 'ach-unlocked':'ach-locked'?>">
              <h3>Five on three</h3>
              <img src="img/achievements/unkown_map.png" class="img-full"/>
              <p>Won map "Five on three" 10 times</p>
            </div>
            <div class="col-md-3 ach-box <?=$maps['intermezzo']>=10 ? 'ach-unlocked':'ach-locked'?>">
              <h3>Intermezzo</h3>
              <img src="img/achievements/unkown_map.png" class="img-full"/>
              <p>Won map "Intermezzo" 10 times</p>
            </div>
            <div class="col-md-3 ach-box <?=$maps['pathfinder']>=10 ? 'ach-unlocked':'ach-locked'?>">
              <h3>Pathfinder</h3>
              <img src="img/achievements/unkown_map.png" class="img-full"/>
              <p>Won map "Pathfinder" 10 times</p>
            </div>
            <div class="col-md-3 ach-box <?=$maps['aroundthelake']>=10 ? 'ach-unlocked':'ach-locked'?>">
              <h3>Around the lake</h3>
              <img src="img/achievements/unkown_map.png" class="img-full"/>
              <p>Won map "Around the lake" 10 times</p>
            </div>


            <div class="col-md-3 ach-box <?=$totalGames>=1000 ? 'ach-unlocked':'ach-locked'?>">
              <h3>Slave</h3>
              <img src="img/achievements/slave.png" class="img-full"/>
              <p>Played more than 1000 games</p>
            </div>
            <div class="col-md-3 ach-box <?=$totalGames>=2000 ? 'ach-unlocked':'ach-locked'?>">
              <h3>Archmage</h3>
              <img src="img/achievements/archmage.png" class="img-full"/>
              <p>Played more than 2000 games</p>
            </div>
            <div class="col-md-3 ach-box <?=$totalGames>=3000 ? 'ach-unlocked':'ach-locked'?>">
              <h3>Why so serious ?</h3>
              <img src="img/achievements/Joker-Smile-by-Merlin2525.png"/>
              <p>Played more than 3000 games</p>
            </div>
            <div class="col-md-3 ach-box <?=$totalGames>=4000 ? 'ach-unlocked':'ach-locked'?>">
              <h3>MegaGlest Honorary citizenship</h3>
              <img src="img/achievements/city.png"/>
              <p>Played more than 4000 games</p>
            </div>
        </div>
      </div>

  </div>



<?php include("foot.php"); ?>
</body>

</html>
