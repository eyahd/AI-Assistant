<?php
$set = $_GET['set'] ?? 'html';
$index = isset($_GET['index']) ? intval($_GET['index']) : -1;
$file = "flashcards/$set.json";

$cards = json_decode(file_get_contents($file), true);

if ($index < 0 || $index >= count($cards)) {
  die('Invalid card index.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $question = trim($_POST['question']);
  $answer = trim($_POST['answer']);
  
  if ($question && $answer) {
    $cards[$index]['question'] = $question;
    $cards[$index]['answer'] = $answer;
    if (file_put_contents($file, json_encode($cards, JSON_PRETTY_PRINT)) === false) {
      die('Error: Could not save flashcards.');
    }
    header("Location: cards.php?set=$set");
    exit;
  } else {
    $error = 'Please fill out both fields.';
  }
}

$card = $cards[$index];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Flashcard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: linear-gradient(270deg, #a8edea, #fed6e3, #f9f586, #a8edea);
      background-size: 800% 800%;
      animation: gradientShift 15s ease infinite;
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
    }
    @keyframes gradientShift {
      0% {background-position:0% 50%;}
      50% {background-position:100% 50%;}
      100% {background-position:0% 50%;}
    }
  </style>
</head>
<body class="flex flex-col items-center p-10">

  <!-- Navbar -->
  <nav class="w-full max-w-5xl flex justify-between items-center mb-10 px-4 py-4 bg-white bg-opacity-70 rounded-xl shadow-md">
    <a href="cards.php?set=<?= htmlspecialchars($set) ?>" class="text-pink-500 font-bold hover:underline">⬅ Back to Cards</a>
    <span class="text-lg text-pink-700 font-semibold">Edit Card #<?= $index + 1 ?></span>
    <div></div> <!-- Spacer -->
  </nav>

  <h1 class="text-3xl font-bold text-pink-600 mb-6">Edit Flashcard in <?= strtoupper(htmlspecialchars($set)) ?></h1>

  <?php if (!empty($error)): ?>
    <p class="text-red-500 mb-4"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="POST" class="bg-white bg-opacity-80 rounded-2xl p-8 w-full max-w-xl shadow-lg space-y-6">
    <div>
      <label class="block text-pink-700 font-medium mb-1">Question:</label>
      <input type="text" name="question" required
             value="<?= htmlspecialchars($card['question']) ?>"
             class="w-full px-4 py-2 rounded-lg border border-pink-300 focus:ring-2 focus:ring-pink-400 focus:outline-none" />
    </div>
    <div>
      <label class="block text-pink-700 font-medium mb-1">Answer:</label>
      <textarea name="answer" required
                class="w-full px-4 py-2 rounded-lg border border-pink-300 focus:ring-2 focus:ring-pink-400 focus:outline-none"
                rows="4"><?= htmlspecialchars($card['answer']) ?></textarea>
    </div>
    <button type="submit"
            class="bg-pink-500 text-white px-6 py-2 rounded-lg hover:bg-pink-600 transition font-semibold">
      💾 Save Changes
    </button>
  </form>
</body>
</html>
