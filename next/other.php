<table>
<?php
include("config.php");
$result = $db->query("SELECT map, COUNT(*) as count FROM glestserver WHERE map IN('foresthighland','goldenspots','4bridge8kings','trapped','pathfinder','2rivers','cauldron','forestdivide','islandsiege','orlog','simpleriver8player','tropicalarena.gbm4hills8kings','centercastle','forestforfour','kaleidoscope','overgrowncity','sixswampofsorrow','ttgoldrushv2.gbm4kingdoms8kings','clinch','fourfree','lakesway','overthebridge','stoneandwood','twistycanyon.gbm6isle','conflict6xtreed','fourrivers','landofplenty2','paraisobr','straightbattle','unforgotten','6player','conflict','fracture','laulagoon','pentagon','swampofhope','valleyofdeath.gbmallyresistclone','confrontation','grandezza','loggerheads','pothole','swampofsorrow','wadinefud2.gbmallyresist','coop6bigriver','hellsclam','megakill3vs1','ragor','teamisland4','waterworld.gbmanotherriverside','dominationisles','highcliff','mountains','redoubt','teamisland','aroundthelake','eightgradient','highlands','nomasters','renkontre','teamplay','baseit','endtimes','hills','onekingfairplay','rivercrossing','threebridges.gbmbeattheenemy','fight4yourgoldenright','intermezzo','onekingrulesthemall','riverraid2','tourdeforce','canoe','fiveonthree','intheforest','oneonone','showdown3way','tropical4v4') GROUP BY map ORDER BY COUNT(*) DESC
");
while($row = $result->fetch_assoc()){
  ?>
  <tr>
    <td><?=$row['map']?></td>
    <td><?=$row['count']?></td>
  </tr>
  <?php
}
?>
</table>
