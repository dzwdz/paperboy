<?php
$dbpath = dirname(realpath(__FILE__)) . '/../paperboy.db';
$db = new SQLite3($dbpath);


function redirect_back($default) {
	$ref = $_SERVER['HTTP_REFERER'];
	if (!$ref) $ref = $default;
	header('Location: ' . $ref);
	exit;
}

function get_title($url) {
	// try to get the title from meta tags
	$meta = get_meta_tags($url);
	if (isset($meta['title'])) return $meta['title'];

	// let's try searching for the <title> tag
	$data = file_get_contents($url);
	return preg_match('/<title[^>]*>(.*?)/is', $data, $matches)
		? $matches[1]
		: $url; // fallback: the url
}

if (isset($_GET['mark'])) {
	$stmt = $db->prepare("UPDATE links SET read_on = :ts WHERE url = :url ");
	$stmt->bindValue(":url", urldecode($_GET['mark']));
	$stmt->bindValue(":ts", time());
	$stmt->execute();
	redirect_back("/");
}

if (isset($_GET['unmark'])) {
	$stmt = $db->prepare("UPDATE links SET read_on = NULL WHERE url = :url ");
	$stmt->bindValue(":url", urldecode($_GET['unmark']));
	$stmt->execute();
	redirect_back("/?archive");
}


if (isset($_GET['add'])) {
	$stmt = $db->prepare("insert INTO links ( url,  desc,  added_on, tags)
	                         VALUES     (:url, :desc, :added_on, :tags)");
	$stmt->bindValue(":url", urldecode($_GET['add']));
	$stmt->bindValue(":desc", get_title(urldecode($_GET['add'])));
	$stmt->bindValue(":added_on", time());
	$stmt->bindValue(":tags", "manual");
	$stmt->execute();

	redirect_back("/");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>paperboy</title>

	<style>
body {
	margin: 2rem;
}

h1 {
	font-size: 1rem;
	display: inline;
}
	</style>
</head>
<body>

<header>
	<h1>paperboy</h1>
	<nav>
<?php if(isset($_GET['archive'])) { ?>
		<a href="/">go back</a>
<?php } else { ?>
		<a href="?archive">archive</a>
<?php } ?>
	</nav>
</header>

<main>
	<ol>
<?php

if (isset($_GET['archive'])) {
    $res = $db->query("SELECT url, desc, read_on, tags FROM links
                       WHERE read_on IS NOT NULL ORDER BY read_on DESC");
} else {
    $res = $db->query("SELECT url, desc, read_on, tags FROM links
                       WHERE read_on IS NULL ORDER BY added_on DESC");
}

while ($row = $res->fetchArray(SQLITE3_ASSOC)) { ?>
	<li>
		<a class="link" href="<?= $row['url'] ?>" target=_blank>
			<?= $row['desc'] ?></a>
		<br />

		<?php if (!$row['read_on']) {?>
			<a href="?mark=<?= urlencode($row['url']) ?>">[mark]</a>
		<?php } else { ?>
			<a href="?unmark=<?= urlencode($row['url']) ?>">[unmark]</a>
		<?php } ?>

		<?= $row['tags'] ?>
	</li>
<?php } ?>
	</ol>
</main>

</body>
</html>
