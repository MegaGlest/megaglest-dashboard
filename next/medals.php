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
        <h2>Medals ? How do I get one ?</h2>
        <h4>General Medal Rules</h4>
        <ul>
          <li>MG-Team Headless servers or hosted by titi or alket.</li>
          <li>MegaPack only.</li>
          <li>Only coop (Humans vs CPUs).</li>
          <li>Number of human players should be equal or lower than CPUs.</li>
          <li>All humans must win.</li>
          <li>No human should leave the game (quit or timeout) before game ends.</li>
          <li>All CPUs should be Ultra or Mega and Resource Multiplier should be 1.5 or higher.</li>
        </ul>
        <h4>Specific Medal Rules</h4>
        <ul>
          <li><img height="20px" src="img/medal_gold.png" title="Gold Medal" alt="Gold Medal" /> GOLD: All CPUs <b>Mega</b> and Resource Multiplier should be equal or higher than 4.0</li>
          <li><img height="20px" src="img/medal_silver.png" title="Silver Medal" alt="Silver Medal" /> SILVER: All CPUs Resource Multiplier should be higher or equal to 3.0 and lower than 4.0</li>
          <li><img height="20px" src="img/medal_bronze.png" title="Bronze Medal" alt="Bronze Medal" /> BRONZE: All CPUs Resource Multiplier should be higher or equal to 1.5 and lower than 3.0</li>
        </ul>
      </div>
    </div>

  </div>



  <?php include("foot.php"); ?>
</body>

</html>
