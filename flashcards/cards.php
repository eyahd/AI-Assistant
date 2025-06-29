<?php
$set = $_GET['set'] ?? 'html';
$file = "flashcards/$set.json";

$cards = file_exists($file) ? json_decode(file_get_contents($file), true) ?? [] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?= strtoupper(htmlspecialchars($set)) ?> Flashcards</title>
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
    .answer {
      display: none;
    }
  </style>
</head>
<body class="flex flex-col items-center p-8">

  <!-- Navbar -->
  <nav class="w-full max-w-5xl flex justify-between items-center mb-10 px-4 py-4 bg-white bg-opacity-70 rounded-xl shadow-md">
    <a href="cards.html" class="text-pink-500 font-bold hover:underline">⬅ Home</a>
    <span class="text-lg text-pink-700 font-semibold"><?= strtoupper(htmlspecialchars($set)) ?> Flashcards</span>
    <a href="add.php?set=<?= htmlspecialchars($set) ?>" class="text-pink-500 hover:underline">➕ Add Card</a>
  </nav>

  <!-- Title -->
  <h1 class="text-4xl font-extrabold mb-10 text-pink-600 drop-shadow-lg"><?= strtoupper(htmlspecialchars($set)) ?> Flashcards</h1>

  <!-- Flashcards -->
  <div class="w-full max-w-3xl grid gap-6">
    <?php if (empty($cards)): ?>
      <p class="text-center text-pink-500 italic">No flashcards found in this set.</p>
    <?php endif; ?>

    <?php foreach ($cards as $i => $card): ?>
      <div 
        class="bg-white bg-opacity-80 rounded-2xl shadow-lg p-6 relative cursor-pointer select-none hover:shadow-pink-400 transition"
        onclick="toggleAnswer('answer-<?= $i ?>')"
        tabindex="0"
        onkeypress="if(event.key==='Enter') toggleAnswer('answer-<?= $i ?>')"
      >
        <p class="text-lg font-semibold mb-2">Q<?= $i + 1 ?>: <?= htmlspecialchars($card['question']) ?></p>
        <p id="answer-<?= $i ?>" class="answer mt-3 text-pink-700 font-medium">Answer: <?= htmlspecialchars($card['answer']) ?></p>
        
        <div class="absolute top-4 right-4 flex gap-2">
          <a href="edit.php?set=<?= urlencode($set) ?>&index=<?= $i ?>" class="text-xl hover:text-pink-700">✏️</a>
          <form method="POST" action="delete.php" onsubmit="return confirm('Delete this card?')">
            <input type="hidden" name="index" value="<?= $i ?>">
            <input type="hidden" name="set" value="<?= htmlspecialchars($set) ?>">
            <button type="submit" class="text-xl hover:text-pink-700" aria-label="Delete card">🗑️</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Bottom Buttons -->
  <div class="mt-12 flex gap-6">
    <a href="add.php?set=<?= htmlspecialchars($set) ?>" 
       class="bg-pink-500 text-white rounded-lg px-6 py-3 hover:bg-pink-600 transition shadow-md font-semibold">
      ➕ Add New Card
    </a>

    <a href="cards.html" 
       class="bg-white text-pink-600 rounded-lg px-6 py-3 hover:bg-pink-200 transition shadow-md font-semibold">
      ⬅ Back to Categories
    </a>
  </div>

  <script>
    function toggleAnswer(id) {
      const el = document.getElementById(id);
      if (el) el.style.display = el.style.display === 'block' ? 'none' : 'block';
    }
  </script>
</body>
</html>
