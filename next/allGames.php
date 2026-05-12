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
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    ?>
    
    <div class="row">
      <div class="col-md-12">
        <h2><?=$name."s profile"?> <?=isset($pdet[$name]) ? '<img src="img/flags/'.strtolower($pdet[$name]["country"]).'.png"/>':''?></h2>
      </div>
      </div>
      <div class="col-md-6">
        <h3>All games</h3>
        <?php
        $result = $db->query("SELECT gameUUID FROM glestgameplayerstats WHERE playerName='$name' ORDER BY lasttime DESC");
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
    <div class="col-md-6">
        <h3>Top buddies</h3>
        <?php
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

        ?>
        <table class="table table-bordered table-striped">
          <?php foreach($buddies as $buddy=>$bgames){ ?>
            <tr>
                <td><a href="allGames.php?name=<?=$buddy?>"><?=$buddy?></a></td>
                <td><?=$bgames?></td>
            </tr>
        <?php } ?>
        </table>
      </div>
    </div>
    

  </div>



<?php include("foot.php"); ?>
</body>

</html>
