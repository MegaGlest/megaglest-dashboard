<?php
include("config.php");

//$glestmedals_conf = $db->query("SELECT last_lasttime FROM glestmedals_conf");
//$glestmedals_conf = $glestmedals_conf->fetch_assoc();
//$last_lasttime = $glestmedals_conf['last_lasttime'];
$last_lasttime = file_get_contents('last_lasttime.txt');

	$approved_servers = array('94.23.148.250');
	$approved_tech = array('megapack');

	$count = 0;
	$worthMedal = True;
	$game_players = array();
	$ai_total = 0;
	$ai_multiply = 0;
	$ai_type = "";
	$ai_types = array();
	$no_players = 0;
	$no_ais = 0;
    $lasttime = '';
    $medals = [];


	$games = $db->query("SELECT * FROM glestserver WHERE lasttime > '$last_lasttime' ORDER BY lasttime");
	
	while($game = $games->fetch_assoc()){
		$gameUUID = $game['gameUUID'];

		if((in_array($game['tech'] , $approved_tech) && in_array($game['ip'], $approved_servers)) || $game['serverTitle'] == "titis game" || $game['serverTitle'] == "alkets game"){
			$players = $db->query("SELECT * FROM glestgameplayerstats WHERE gameUUID='$gameUUID'");
			if($players->num_rows > 0){
				while($player = $players->fetch_assoc()){
					if($player['quitBeforeGameEnd'] == 1){
						$worthMedal = False;
					}
					if($player['controlType'] == 7 || $player['controlType'] == 5 ){
						if($player['wonGame'] == 0){
							$worthMedal = False;
						}
						array_push($game_players, $player['playerName']);
						$no_players++;
					} else {
						if($player['controlType'] == 3 || $player['controlType'] == 4) {
							$ai_multiply += $player['resourceMultiplier'];
							$no_ais++;
							array_push($ai_types, $player['controlType']);
						} else {
							$worthMedal = False;
						}
					}
					if($player['playerName'] == "newbie"){
						$worthMedal = False;
					}
				}
				if($no_players > $no_ais) {
					$worthMedal = False;
				}


				if($no_ais != 0){
					$ai_total = $ai_multiply / $no_ais;
				} else {
					$worthMedal =False;
				}

				if($ai_total < 1.5){
					$worthMedal = False;
				}

				if($worthMedal) {
				
                    foreach($game_players as $game_player){
                        if(!isset($medals[$game_player])) {
                            $medals[$game_player]['medalGold'] = 0;
                            $medals[$game_player]['medalSilver'] = 0;
                            $medals[$game_player]['medalBronze'] = 0;
                        }
                    }
                    
					$count++;
					if(!in_array(3, $ai_types) && $ai_total >= 4.0) {
						foreach($game_players as $game_player){
                            $medals[$game_player]['medalGold'] += 1;
							//$db->query("INSERT INTO glestmedals (playerName,medalGold) VALUES('$game_player','1') ON DUPLICATE KEY UPDATE medalGold = medalGold + 1 ");
         
						}
					}
					else if ($ai_total >= 3.0 && $ai_total < 4.0){
						foreach($game_players as $game_player){
                            $medals[$game_player]['medalSilver'] += 1;
							//$db->query("INSERT INTO glestmedals (playerName,medalSilver) VALUES('$game_player','1')  ON DUPLICATE KEY UPDATE medalBronze = medalBronze + 1 ");
						}
					} else if ($ai_total < 3.0){
						foreach($game_players as $game_player){
                            $medals[$game_player]['medalBronze'] += 1;
							//$db->query("INSERT INTO glestmedals (playerName,medalBronze) VALUES('$game_player','1') ON DUPLICATE KEY UPDATE medalBronze = medalBronze + 1 ");
						}
					}
				}
			}
		}

        $lasttime = $game['lasttime'];
		// restore to default
		$worthMedal = True;
		$ai_multiply = 0;
		$ai_type = "";
		unset($game_players);
		$game_players = array();
		unset($ai_types);
		$ai_types = array();
		$no_players = 0;
		$no_ais = 0;
		$ai_total = 0;
		$gameUUID = "";
	}
	

	if($games->num_rows > 0) {
        $last_lasttime = file_put_contents('last_lasttime.txt',$lasttime);
        foreach($medals as $player=>$vals) {
            $getPlayerGames = $db->query("SELECT playerName FROM glestgameplayerstats WHERE playerName='$player'");
            $totalGames = $getPlayerGames->num_rows;
            $medals[$player]['gamesPlayed'] = $totalGames;
        }
	
        /*
        $db->query("UPDATE glestmedals_conf SET last_lasttime='$lasttime'");

        $currentMedalPlayer = "";
        $totalGames = 0;
        $medalPlayers = $db->query("SELECT playerName FROM glestmedals");
        while($medalPlayer = $medalPlayers->fetch_assoc()){
            $currentMedalPlayer = $medalPlayer['playerName'];
            $getPlayerGames = $db->query("SELECT playerName FROM glestgameplayerstats WHERE playerName='$currentMedalPlayer'");
            $totalGames = $getPlayerGames->num_rows;
            $db->query("UPDATE glestmedals SET gamesPlayed=$totalGames WHERE playerName='$currentMedalPlayer'");
        }
        */
    }

    $json = file_get_contents('medals.json');
    $json_data = json_decode($json,true);
    foreach($medals as $player=>$vals) {
        if(!isset($json_data[$player])){
            $json_data[$player] = $vals;
        } else {
            $json_data[$player]['medalGold'] += $vals['medalGold'];
            $json_data[$player]['medalSilver'] += $vals['medalSilver'];
            $json_data[$player]['medalBronze'] += $vals['medalBronze'];
        }
    }
    $sort_medals = $json_data;
    array_multisort($sort_medals, SORT_DESC, $json_data);
    $sort_medals = json_encode($sort_medals);
    file_put_contents("medals.json", $sort_medals);
    $json = file_get_contents('medals.json');
    $players = json_decode($json,true);
    
    $players = array_slice($players,0,100);

    include("pdet.php");
    
?>
<?php include("head.php"); ?>


<body>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
<?php include("navbar.php") ?>
      </div>
    </div>
    <div class="row">
       <div class="col-md-12 text-center">
       <?php
        $player = $value = NULL;
        foreach ($players as $player => $value) {
            break;
        }
       ?>
       <img src="img/cup.png" height="120px"/>
        <h2><?=$player?> <?=isset($pdet[$player]) ? '<img src="img/flags/'.strtolower($pdet[$player]["country"]).'.png"/>':''?></h2>
        <p>
        <br/>
        "Every master was once a beginner."<br/>
        <small>by david123</small>
        </p>
      </div>
      <div class="col-md-12">
        <h2>Top 100 Players</h2>
        <table class="table table-bordered table-striped">
          <tbody>
            <tr>
              <th>#</th>
              <th>Player Name</th>
              <th>Games Played</th>
              <th>Time Played</th>
              <th>
                <img height="20px" src="img/medal_gold.png" title="Gold Medal" alt="Gold Medal" />
              </th>
              <th>
                <img height="20px" src="img/medal_silver.png" title="Silver Medal" alt="Silver Medal" />
              </th>
              <th>
                <img height="20px" src="img/medal_bronze.png" title="Bronze Medal" alt="Bronze Medal" />
              </th>
            </tr>
            <?php
            $position = 0;
            foreach($players as $player=>$vals) {
            $position++;
            ?>
            <tr>
              <td><?=$position?></td>
              <td><a href="player.php?name=<?=$player?>"><?=$player?></a> <?=isset($pdet[$player]) ? '<img src="img/flags/'.strtolower($pdet[$player]["country"]).'.png"/>':''?></td>
              <td><?=$vals['gamesPlayed']?></td>
              <td>wip</td>
              <td><?=$vals['medalGold']?></td>
              <td><?=$vals['medalSilver']?></td>
              <td><?=$vals['medalBronze']?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>



  <?php include("foot.php"); ?>
</body>

</html>
