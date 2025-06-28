<?php
$set = $_GET['set'] ?? 'html';
$file = "flashcards/$set.json";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $question = trim($_POST['question'] ?? '');
  $answer = trim($_POST['answer'] ?? '');

  if ($question !== '' && $answer !== '') {
    $cards = [];
    if (file_exists($file)) {
      $jsonContent = file_get_contents($file);
      $cards = json_decode($jsonContent, true);
      if (!is_array($cards)) {
        $cards = [];
      }
    }

    // Add new card
    $cards[] = [
      "question" => $question,
      "answer" => $answer
    ];

    // Save back to file with pretty print
    if (file_put_contents($file, json_encode($cards, JSON_PRETTY_PRINT)) === false) {
      die('Error: Could not save flashcards.');
    }

    // Redirect back to cards list
    header("Location: cards.php?set=" . urlencode($set));
    exit;
  } else {
    $error = "Both question and answer are required.";
  }
}

// Create folder if missing
if (!is_dir('flashcards')) {
    mkdir('flashcards', 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');

    if ($question !== '' && $answer !== '') {
        $cards = [];
        if (file_exists($file)) {
            $jsonContent = file_get_contents($file);
            $cards = json_decode($jsonContent, true);
            if (!is_array($cards)) {
                $cards = [];
            }
        }

        $cards[] = [
            "question" => $question,
            "answer" => $answer
        ];

        // Debug writable
        var_dump(is_writable('flashcards'));
        var_dump(is_writable($file));

        if (file_put_contents($file, json_encode($cards, JSON_PRETTY_PRINT)) === false) {
            die('Error: Could not save flashcards.');
        }

        header("Location: cards.php?set=" . urlencode($set));
        exit;
    } else {
        $error = "Both question and answer are required.";
    }
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Flashcard to <?= strtoupper(htmlspecialchars($set)) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-r from-pink-100 via-pink-50 to-pink-100 flex flex-col items-center justify-center p-8 font-sans">

  <div class="w-full max-w-xl bg-white rounded-3xl shadow-lg p-8">
    <h1 class="text-4xl font-extrabold text-pink-600 mb-8 text-center drop-shadow-md">
      Add New Flashcard to <?= strtoupper(htmlspecialchars($set)) ?>
    </h1>

    <form method="POST" class="flex flex-col gap-6">
      <input
        type="text"
        name="question"
        placeholder="Question"
        required
        class="px-5 py-3 rounded-xl border-2 border-pink-300 focus:border-pink-600 focus:outline-none text-lg transition"
      />

      <textarea
        name="answer"
        placeholder="Answer"
        required
        rows="5"
        class="px-5 py-3 rounded-xl border-2 border-pink-300 focus:border-pink-600 focus:outline-none text-lg resize-y transition"
      ></textarea>

      <button
        type="submit"
        class="bg-pink-500 text-white font-semibold rounded-xl py-3 hover:bg-pink-600 transition shadow-md"
      >
        Add Card
      </button>
    </form>

    <div class="mt-6 text-center">
<a
  href="cards.php"
  class="inline-block bg-pink-100 text-pink-600 rounded-lg px-6 py-3 hover:bg-pink-200 transition shadow-md font-semibold"
>
  ⬅ Back to Categories
</a>

    </div>
  </div>

</body>
</html>
