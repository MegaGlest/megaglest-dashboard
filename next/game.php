<?php include("config.php"); ?>
<?php include("head.php"); ?>


<body>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
<?php include("navbar.php") ?>
      </div>
    </div>
    <div class="row">

      <div class="col-md-12">
        <?php
        $gameUUID  = $db->real_escape_string($_GET['uuid']);
        $gameUUID = filter_var($gameUUID, FILTER_SANITIZE_STRING);

        $result = $db->query("SELECT * FROM glestserver WHERE gameUUID='$gameUUID' LIMIT 1");
        $row = $result->fetch_assoc();
        $note_tech = $row['tech'];
        $note_ip = $row['ip'];
        $serverTitle = $row['serverTitle'];
        ?>
        <h2><?=$row['serverTitle']?></h2>

        <table class="table table-bordered table-striped">
          <tbody>
            <tr>
              <th>Title</th>
              <th>Ver</th>
              <th>Country</th>
              <th>Techtree</th>
              <th>Map</th>
              <th>Tileset</th>
              <th>Platform</th>
              <th>Play Date</th>
            </tr>
            <tr>
              <td><a href="game.php?uuid=<?=$row['gameUUID']?>"><?=$row['serverTitle']?></a></td>
              <td><?=$row['glestVersion']?></td>
              <?php if($row['country'] !=""){ ?>
              <td><img src="img/flags/<?=strtolower($row['country'])?>.png" title="<?=$row['country']?>"/></td>
              <?php
            }else {
              ?>
              <td>?</td>
              <?php } ?>
              <td><?=$row['tech']?></td>
              <td><?=$row['map']?></td>
              <td><?=$row['tileset']?></td>
              <td>
                <?php
                $platform = $row['platform'];
                $platform_short = substr($platform,0,1);
                if($platform_short == "L"){
                ?>
                  <img src="img/os/linux.png" title="<?=$platform?>"/>
                <?php
              } else if($platform_short == "W"){
                ?>
                <img src="img/os/windows.png" title="<?=$platform?>"/>
                <?php
              } else {
                echo $platform;
              }
                ?>
                </td>
              <td><?=$row['lasttime']?></td>
            </tr>
          </tbody>
        </table>

        <table class="table table-bordered table-striped">
          <tbody>
            <tr>
              <th>Player</th>
              <th>Faction</th>
              <th>Team</th>
              <th>Winner</th>
              <th>Kills</th>
              <th>Enemy Kills</th>
              <th>Units Produced</th>
              <th>Quit</th>
              <th>Resources</th>
              <th>Score</th>
            </tr>
            <?php
            $note_quit = False;
            $note_win = False;
            $note_humans = 0;
            $note_ais = 0;
            $note_ai_strength = True;
            $note_ai_ultra = 0;
            $note_ai_mega = 0;
            $note_ai_multiply = 0;

            $result = $db->query("SELECT * FROM glestgameplayerstats WHERE gameUUID='$gameUUID'");
            while($row = $result->fetch_assoc()){
            ?>
            <tr>
              <?php
              $controlType = $row['controlType'];
                if($controlType == 5 || $controlType == 7){
                  $note_humans++;
              ?>
                <td><a href="player.php?name=<?=$row['playerName']?>"><?=$row['playerName']?></a></td>
              <?php } else {
                  $note_ais++;
                $CPUtype = "";
                if($controlType == 1){
                  $CPUtype = "Easy";
                  $note_ai_strength = False;
                } else if($controlType == 2){
                  $CPUtype = "Normal";
                  $note_ai_strength = False;
                } else if($controlType == 3){
                  $note_ai_ultra++;
                  $note_ai_multiply += $row['resourceMultiplier'];
                  $CPUtype = "Ultra";
                } else if($controlType == 4){
                  $note_ai_mega++;
                  $note_ai_multiply += $row['resourceMultiplier'];
                  $CPUtype = "Mega";
                }
              ?>

                <td>CPU <?=$CPUtype?> <?=substr($row['resourceMultiplier'],0,3)?></td>
              <?php } ?>
              <td><?=$row['factionTypeName']?></td>
              <td><?=intval($row['teamIndex'])+1?></td>
              <td>
                <?php
                if($row['wonGame'] == 1){
                  echo '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
                } else {
                  echo '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>';
                  if($controlType == 5 || $controlType == 7){
                    $note_win = True;
                  }
                }
                ?>
                </td>
              <td><?=$row['killCount']?></td>
              <td><?=$row['enemyKillCount']?></td>
              <td><?=$row['unitsProducedCount']?></td>
              <td>
                <?php
                if($row['quitBeforeGameEnd'] == 1){
                  echo '<span class="glyphicon glyphicon glyphicon-off" aria-hidden="true"></span>';
                  $note_quit = True;
                } else {
                  echo ' ';
                }
                ?>
              </td>
              <td><?=$row['resourceHarvestedCount']?></td>
              <td><?=ceil($row['enemyKillCount'] * 100 + $row['unitsProducedCount'] * 50 + $row['resourceHarvestedCount'] / 10)?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
        <h2>Notes</h2>
        <?php
        $note_medal = False;
        if($note_ais > 0){
          $note_ai_multiply = $note_ai_multiply / $note_ais;
        } else {
          $note_ai_multiply = 0;
        }
        if(!$note_quit && !$note_win && $note_humans <= $note_ais && $note_ais > 0 && $note_ai_strength && $note_ai_multiply >= 1.5 && $note_tech == "megapack" && ($note_ip != "94.23.148.250" || $serverTitle != "titis game")) {
          $note_medal = True;
        }
        if($note_medal) {
          echo '<p>Playerss in this game were awarded with:</p>';
          if($note_ai_ultra == $note_ais || $note_ai_multiply < 3.0) {
            echo '<p><img height="20px" src="img/medal_bronze.png" title="Bronze Medal" alt="Bronze Medal" /> Bronze Medal</p>';
          } else if($note_ai_mega == $note_ais && $note_ai_multiply >= 4.0){
            echo '<p><img height="20px" src="img/medal_gold.png" title="Gold Medal" alt="Gold Medal" /> Gold Medal</p>';
          } else if($note_ai_multiply > 2.9 && $note_ai_multiply < 4.0) {
            echo '<p><img height="20px" src="img/medal_silver.png" title="Silver Medal" alt="Silver Medal" /> Silver Medal</p>';
          }
        } else {
          echo '<p>Players in this game were <b>not</b> awared medals because:</p><ul>';
          if($note_quit) {
            echo '<li>One or more players <b>quit</b> before game end.</li>';
          }
          if($note_win) {
            echo '<li>One or more players <b>lost</b> the game.</li>';
          }
          if($note_humans > $note_ais) {
            echo '<li>There were more Human players than CPU ones.</li>';
          }
          if($note_ais == 0){
            echo '<li>This wasn\'t a COOP game.</li>';
          }
          if(!$note_ai_strength) {
            echo '<li>One or more CPUs were Easy or Normal.</li>';
          }
          if($note_ai_multiply < 1.5) {
            echo '<li>CPU multiplier wasn\'t bigger than 1.5.</li>';
          }
          if($note_tech != "megapack") {
            echo '<li>The techtree wasn\'t in our approved list.</li>';
          }
          if($note_ip != "94.23.148.250" && $serverTitle != "titis game"){
            echo '<li>The game was not played on MG Team Headless or approved server.</li>';
          }
          echo '</ul>';
        }
        ?>
      </div>
    </div>
  </div>



  <?php include("foot.php"); ?>
</body>

</html>
