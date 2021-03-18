#!/usr/bin/env -S php -q
<?php
$db = new SQLite3('paperboy.db');

$db->query("CREATE TABLE IF NOT EXISTS links (
	url      text PRIMARY KEY,
	desc     text,
	added_on integer,
	read_on  integer
)");


$stmt = $db->prepare("insert INTO links ( url,  desc,  added_on)
                             VALUES     (:url, :desc, :added_on)");

// loop over every executable in gatherers/
$gatherers = new DirectoryIterator(dirname(__FILE__) . '/gatherers');
foreach ($gatherers as $file) {
	if ($file->isExecutable() and !$file->isDot()) {
		echo $file->getFilename() . "\n";

		// collect lines
		$out = null;
		exec($file->getPathname(), $out);
		foreach ($out as $entry) {
			echo $entry . "\n";
			$parts = explode("\t", $entry);
			$stmt->bindValue(":url",      $parts[0]);
			$stmt->bindValue(":desc",     $parts[1]);
			$stmt->bindValue(":added_on", $parts[2]);
			$stmt->execute();
		}
	}
}
?>
done!
