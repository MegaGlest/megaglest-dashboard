<!DOCTYPE html>
<head>
	<title>misc stats</title>
	<style type="text/css">
	body {
		padding-left:10px;
	}
	</style>
</head>
<body>
<?php
include("../config.php");

echo "<h2>Total games</h2>";

$result = $db->query("SELECT gameUUID FROM glestgameplayerstats GROUP BY gameUUID");
echo "<p>".$result->num_rows."</p>";

echo "<h2>Factions</h2>";
echo "<ol>";
unset($result);
$result = $db->query("SELECT factionTypeName, COUNT(*) as countx FROM glestgameplayerstats GROUP BY factionTypeName ORDER BY countx DESC");
while($row = $result->fetch_assoc()){
	echo "<li>".$row['factionTypeName']." - ".$row['countx']."</li>";
}
echo "</ol>";

echo "<h2>Maps</h2>";
echo "<ol>";
unset($result);
$result = $db->query("SELECT map, COUNT(*) as countx FROM glestserver GROUP BY map ORDER BY countx DESC");
while($row = $result->fetch_assoc()){
	echo "<li>".$row['map']." - ".$row['countx']."</li>";
}
echo "</ol>";

echo "<h2>Tilesets</h2>";
echo "<ol>";
unset($result);
$result = $db->query("SELECT tileset, COUNT(*) as countx FROM glestserver GROUP BY tileset ORDER BY countx DESC");
while($row = $result->fetch_assoc()){
	echo "<li>".$row['tileset']." - ".$row['countx']."</li>";
}
echo "</ol>";

echo "<h2>All players</h2>";
echo "<ol>";
unset($result);
$result = $db->query("SELECT playerName, COUNT(*) as countx FROM glestgameplayerstats GROUP BY playerName ORDER BY countx DESC");
while($row = $result->fetch_assoc()){
	echo "<li>".$row['playerName']." - ".$row['countx']."</li>";
}
echo "</ol>";
?>
</body>