<?php
$db = new SQLite3('../paperboy.db');

function redirect_back() {
	$ref = $_SERVER['HTTP_REFERER'];
	if (!$ref) $ref = '/';
	header('Location: ' . $ref);
	exit;
}

if ($_GET['mark']) {
	$stmt = $db->prepare("UPDATE links SET read_on = :ts WHERE url = :url ");
	$stmt->bindValue(":url", urldecode($_GET['mark']));
	$stmt->bindValue(":ts", time());
	$stmt->execute();
	redirect_back();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>paperboy</title>

	<link rel="stylesheet" href="style.css">
</head>
<body>

<header>
	<h1>paperboy</h1>
</header>

<main>
	<ol>
<?php
$res = $db->query("SELECT url, desc FROM links
                   WHERE read_on IS NULL ORDER BY added_on DESC");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) { ?>
	<li>
		<a href="<?= $row['url'] ?>" target=_blank>
			<?= $row['desc'] ?></a>
		<a title="mark" href="?mark=<?= urlencode($row['url']) ?>">[mark]</a>
	</li>
<?php } ?>
	</ol>
</main>

</body>
</html>
