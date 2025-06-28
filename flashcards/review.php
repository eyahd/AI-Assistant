<?php
session_start();

$availableSets = ['html', 'css', 'js'];
$chosenSet = $_GET['set'] ?? '';

// Show category selection UI if no set chosen
if (!$chosenSet) {
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Select Category</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="min-h-screen bg-gradient-to-r from-pink-100 via-blue-100 to-yellow-100 flex flex-col items-center justify-center p-8">
    <div class="bg-white p-8 rounded-3xl shadow-lg text-center w-full max-w-md">
      <h1 class="text-3xl font-bold text-purple-700 mb-6">Choose a Category to Review</h1>
      <form method="GET">
        <select name="set" class="w-full px-4 py-2 rounded-xl border border-purple-300 mb-4 focus:ring-2 focus:ring-purple-400">
          <?php foreach ($availableSets as $set): ?>
            <option value="<?= $set ?>"><?= strtoupper($set) ?></option>
          <?php endforeach; ?>
          <option value="random">Random (All)</option>
        </select>
        <button type="submit" class="bg-purple-500 text-white px-6 py-2 rounded-lg hover:bg-purple-600 transition font-semibold">
          Start Review ➤
        </button>
      </form>
    </div>
  </body>
  </html>
  <?php
  exit;
}

// Load flashcards based on chosen category
$setsToLoad = ($chosenSet === 'random') ? $availableSets : [$chosenSet];
$allCards = [];
$now = time();

foreach ($setsToLoad as $setName) {
  $file = "flashcards/$setName.json";
  if (!file_exists($file)) continue;

  $cards = json_decode(file_get_contents($file), true);

  foreach ($cards as $i => $card) {
    if (empty($card['due']) || $card['due'] <= $now) {
      $card['__set'] = $setName;
      $card['__index'] = $i;
      $allCards[] = $card;
    }
  }
}

// Track reviewed cards
$_SESSION['reviewed'] = $_SESSION['reviewed'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $set = $_POST['set'];
  $index = $_POST['index'];
  $result = $_POST['result'];

  $file = "flashcards/$set.json";
  $cards = json_decode(file_get_contents($file), true);
  $card = $cards[$index];

  $interval = $card['interval'] ?? 1;
  if ($result === 'correct') {
    $interval *= 2;
  } else {
    $customInterval = (int)($_POST['custom_interval'] ?? 1);
    $interval = $customInterval > 0 ? $customInterval : 1;
  }

  $cards[$index]['interval'] = $interval;
  $cards[$index]['due'] = strtotime("+$interval days");
  file_put_contents($file, json_encode($cards, JSON_PRETTY_PRINT));

  $_SESSION['reviewed'][] = "$set-$index";

  header("Location: review.php?set=$chosenSet");
  exit;
}

$dueCards = array_filter($allCards, function ($card) {
  return !in_array("{$card['__set']}-{$card['__index']}", $_SESSION['reviewed'] ?? []);
});

if (empty($dueCards)) {
  session_destroy();
  echo "<h1>🎉 You've reviewed all cards in this category!</h1>";
  echo "<a href='review.php'>⬅ Back</a>";
  exit;
}

$card = array_values($dueCards)[0];
$set = $card['__set'];
$index = $card['__index'];
$reviewed = count($_SESSION['reviewed'] ?? []);
$totalToReview = count($allCards);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Review Flashcards</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .flip-card { perspective: 1000px; }
    .flip-inner { transition: transform 0.6s; transform-style: preserve-3d; }
    .flipped .flip-inner { transform: rotateY(180deg); }
    .flip-front, .flip-back {
      backface-visibility: hidden;
      position: absolute;
      width: 100%;
      height: 100%;
      border-radius: 1rem;
      padding: 2rem;
    }
    .flip-front { background-color: #fef9c3; color: #374151; }
    .flip-back { transform: rotateY(180deg); background-color: #fde68a; color: #1f2937; }
  </style>
</head>
<body class="min-h-screen bg-gradient-to-r from-yellow-100 via-yellow-50 to-yellow-100 flex flex-col items-center justify-center p-6">
  <div class="w-full max-w-xl bg-white rounded-3xl shadow-lg p-8 relative">
    <h1 class="text-3xl font-bold text-yellow-600 mb-2 text-center">Review - <?= strtoupper($set) ?></h1>
    <p class="text-center text-gray-600 mb-6">Card <?= $reviewed + 1 ?> of <?= $totalToReview ?></p>

    <div id="flip-card" class="flip-card w-full h-48 relative mb-8 cursor-pointer" onclick="flipCard()">
      <div class="flip-inner w-full h-full relative">
        <div class="flip-front flex items-center justify-center text-xl font-semibold shadow-lg">
          <?= htmlspecialchars($card['question']) ?>
        </div>
        <div class="flip-back flex items-center justify-center text-xl font-medium shadow-lg">
          <?= htmlspecialchars($card['answer']) ?>
        </div>
      </div>
    </div>

    <div class="text-center text-sm text-gray-500 mb-4">Click the card to flip</div>

    <form method="POST" class="flex flex-col items-center gap-4">
      <input type="hidden" name="set" value="<?= htmlspecialchars($set) ?>">
      <input type="hidden" name="index" value="<?= htmlspecialchars($index) ?>">
      <div class="flex gap-4">
        <button name="result" value="correct" class="bg-green-500 text-white py-2 px-6 rounded-xl font-semibold hover:bg-green-600 transition">✔️ Correct</button>
        <button name="result" value="wrong" class="bg-red-500 text-white py-2 px-6 rounded-xl font-semibold hover:bg-red-600 transition">❌ Wrong</button>
      </div>
      <div class="w-full text-center">
        <label for="custom_interval" class="text-sm text-gray-700">If wrong, review again in:</label>
        <select name="custom_interval" id="custom_interval" class="ml-2 border rounded-md p-1">
          <option value="1">1 day</option>
          <option value="2">2 days</option>
          <option value="3">3 days</option>
          <option value="5">5 days</option>
          <option value="7">7 days</option>
        </select>
      </div>
    </form>

    <div class="mt-6 text-center">
      <a href="review.php" class="inline-block bg-yellow-100 text-yellow-600 rounded-lg px-6 py-3 hover:bg-yellow-200 transition shadow-md font-semibold">
        ⬅ Back to Category Selection
      </a>
    </div>
  </div>
  <div class="mt-6 text-center">
   <a href="cards.html" 
       class="bg-white text-pink-600 rounded-lg px-6 py-3 hover:bg-pink-200 transition shadow-md font-semibold">
      ⬅ Back to Categories
    </a>
  </div>
  </div>
  <script>
    function flipCard() {
      document.getElementById('flip-card').classList.toggle('flipped');
    }
  </script>
</body>
</html>