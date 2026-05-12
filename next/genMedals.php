<?php
// OBSOLETE
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
include("config.php");

$glestmedals_conf = $db->query("SELECT last_lasttime FROM glestmedals_conf");
$glestmedals_conf = $glestmedals_conf->fetch_assoc();
$last_lasttime = $glestmedals_conf['last_lasttime'];

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


	$games = $db->query("SELECT * FROM glestserver WHERE lasttime > '$last_lasttime' ORDER BY lasttime");

	while($game = $games->fetch_assoc()){
		$gameUUID = $game['gameUUID'];

		if(in_array($game['tech'] , $approved_tech) && in_array($game['ip'], $approved_servers)){
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
                    
					$count++;
					if(!in_array(3, $ai_types) && $ai_total == 4.9) {

						foreach($game_players as $game_player){
							if(!$db->query("INSERT INTO glestmedals (playerName,medalGold) VALUES('$game_player','1') ON DUPLICATE KEY UPDATE medalGold = medalGold + 1 ")) {
                                print_r($db->error);
                                echo "<br/>";
							}
						}
					}
					else if ($ai_total >= 3.0 && $ai_total <= 4.9){
						foreach($game_players as $game_player){
							$db->query("INSERT INTO glestmedals (playerName,medalSilver) VALUES('$game_player','1') ");
						}
					} else if ($ai_total < 3.0){
						foreach($game_players as $game_player){
							$db->query("INSERT INTO glestmedals (playerName,medalBronze) VALUES('$game_player','1') ON DUPLICATE KEY UPDATE medalBronze = medalBronze + 1 ");
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



$showTable = $db->query("SELECT * FROM glestmedals ORDER BY medalGold DESC,medalSilver DESC,medalBronze DESC,gamesPlayed DESC");
$count = 0;
echo '<table>';
while($showPlayers = $showTable->fetch_assoc()){
	$count++;
	echo '<tr><td>'.$count.'</td><td>'.$showPlayers["playerName"].'</td><td>'.$showPlayers["medalGold"].'</td><td>'.$showPlayers["medalSilver"].'</td><td>'.$showPlayers["medalBronze"].'</td><td>'.$showPlayers["gamesPlayed"].'</td></tr>';
}
echo '</table>';
?>
