<?php
$index = $_POST['index'];
$set = $_POST['set'];
$file = "flashcards/$set.json";

$cards = json_decode(file_get_contents($file), true);
array_splice($cards, $index, 1);
file_put_contents($file, json_encode($cards, JSON_PRETTY_PRINT));

header("Location: cards.php?set=$set");
