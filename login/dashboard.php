<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

$conn = new mysqli("localhost", "root", "", "user_registration");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Track streaks
$today = date("Y-m-d");
$yesterday = date("Y-m-d", strtotime("-1 day"));

// Fetch streak
$streakStmt = $conn->prepare("SELECT last_activity, current_streak FROM streaks WHERE username = ?");
$streakStmt->bind_param("s", $username);
$streakStmt->execute();
$streakResult = $streakStmt->get_result();

if ($streakResult->num_rows > 0) {
    $row = $streakResult->fetch_assoc();
    if ($row['last_activity'] == $today) {
        $streak = $row['current_streak'];
    } elseif ($row['last_activity'] == $yesterday) {
        $streak = $row['current_streak'] + 1;
        $updateStreak = $conn->prepare("UPDATE streaks SET last_activity = ?, current_streak = ? WHERE username = ?");
        $updateStreak->bind_param("sis", $today, $streak, $username);
        $updateStreak->execute();
    } else {
        $streak = 1;
        $resetStreak = $conn->prepare("UPDATE streaks SET last_activity = ?, current_streak = 1 WHERE username = ?");
        $resetStreak->bind_param("ss", $today, $username);
        $resetStreak->execute();
    }
} else {
    $streak = 1;
    $newStreak = $conn->prepare("INSERT INTO streaks (username, last_activity, current_streak) VALUES (?, ?, 1)");
    $newStreak->bind_param("ss", $username, $today);
    $newStreak->execute();
}
$streakStmt->close();

// Count completed lessons


// Check for badge if not yet earned


$topics = ['Lesson 1', 'Lesson 2', 'Lesson 3', 'Lesson 4'];
$inProgress = [];
$completed = [];
$recommendNext = null;
$completedCount = count($completed);
$badgeStmt = $conn->prepare("SELECT * FROM achievements WHERE username = ? AND badge_type = ?");
$badgeType = "3_lessons_done";
$badgeStmt->bind_param("ss", $username, $badgeType);
$badgeStmt->execute();
$badgeResult = $badgeStmt->get_result();

if ($completedCount >= 3 && $badgeResult->num_rows == 0) {
    $insertBadge = $conn->prepare("INSERT INTO achievements (username, badge_type) VALUES (?, ?)");
    $insertBadge->bind_param("ss", $username, $badgeType);
    $insertBadge->execute();
}
$badgeStmt->close();
$stmt = $conn->prepare("SELECT progress FROM dashboard WHERE username = ? AND topic = ?");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard - WebBloom</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <style>
    .lesson { border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; width: 300px; }
    progress { width: 100%; height: 20px; }
  </style>
</head>
<body class="bg-gradient-to-r from-blue-50 to-green-100 min-h-screen text-gray-800 font-sans">

<!-- Header -->
<header class="bg-white shadow sticky top-0 z-20">
  <div class="max-w-5xl mx-auto flex justify-between items-center px-2 py-2">
    
    <!-- Logo aligned to the far left -->
    <div class="flex items-center">
      <img src="webbloom.png" alt="webbloom" class="h-24 w-auto object-contain" />
    </div>

    <div class="flex items-center space-x-4">
      <span>Welcome, <strong><?php echo htmlspecialchars($username); ?></strong></span>
      <a href="chatbot/index1.html" class="hover:text-blue-500">chatbot</a>
      <a href="fix-my-code.html" class="hover:text-blue-500">Fix My Code</a>
      <a href="quiz-battles.html" class="hover:text-blue-500">Quiz Battles</a>
      <a href="flashcards/cards.html" class="hover:text-blue-500">Flashcards</a> 
      <a href="logout.php" class="bg-red-400 hover:bg-red-500 text-white px-4 py-2 rounded-full">Logout</a>
    </div>
  </div>
</header>

<!-- Streak and Badges Display -->
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded mb-6 shadow">
  <p class="font-bold text-yellow-700">🔥 Current Streak: <?php echo $streak; ?> day<?php echo $streak > 1 ? 's' : ''; ?> in a row!</p>
</div>

<?php if ($completedCount >= 3): ?>
  <div class="bg-green-100 border-l-4 border-green-500 p-4 rounded mb-6 shadow">
    <p class="font-bold text-green-700">🏅 Achievement Unlocked: Completed 3 Lessons!</p>
  </div>
<?php endif; ?>


<!-- Main Dashboard -->
<main class="max-w-4xl mx-auto py-10">
  <div class="flex justify-center mb-8">
    <button onclick="window.location.href='paths.html'" 
            class="bg-pink-400 hover:bg-pink-500 text-white font-semibold text-lg py-3 px-6 rounded-full shadow-md transition duration-300 ease-in-out">
      Choose Your Learning Path 🌱
    </button>
  </div>

  <!-- Recommended Lesson -->
  <?php
  foreach ($topics as $topic) {
      $stmt->bind_param("ss", $username, $topic);
      $stmt->execute();
      $result = $stmt->get_result();

      $progress = 0;
      if ($result->num_rows > 0) {
          $row = $result->fetch_assoc();
          $progress = (int)$row['progress'];
      }

      if ($progress >= 100) {
          $completed[] = ['topic' => $topic, 'progress' => $progress];
      } else {
          $inProgress[] = ['topic' => $topic, 'progress' => $progress];
          if (!$recommendNext) {
              $recommendNext = $topic;
          }
      }
  }
  $stmt->close();
  $conn->close();
  ?>

  <?php if ($recommendNext): ?>
    <section class="mb-12">
      <div class="bg-white border-l-4 border-pink-400 shadow p-6 rounded-xl">
        <h2 class="text-xl font-bold text-pink-500 mb-2">🌟 Recommendation</h2>
        <p class="text-gray-700">Based on your progress, we recommend starting:</p>
        <a href="lesson.php?topic=<?php echo urlencode($recommendNext); ?>" class="text-blue-600 underline font-semibold text-lg">
          <?php echo htmlspecialchars($recommendNext); ?>
        </a>
      </div>
    </section>
  <?php endif; ?>

  <!-- Currently Learning Section -->
  <section class="mt-12">
    <h2 class="text-2xl font-bold text-green-700 mb-6">📘 Currently Learning</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
      <?php foreach ($inProgress as $lesson): ?>
        <a href="lesson.php?topic=<?php echo urlencode($lesson['topic']); ?>"
           class="transform transition hover:scale-[1.03] hover:shadow-lg bg-white p-6 rounded-2xl shadow flex flex-col justify-between">
          <div>
            <h3 class="text-xl font-bold mb-2 text-pink-500"><?php echo htmlspecialchars($lesson['topic']); ?></h3>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
              <div class="h-full bg-yellow-400" style="width: <?php echo $lesson['progress']; ?>%;"></div>
            </div>
            <div class="mt-2 text-sm text-gray-700 text-center font-medium">Progress: <?php echo $lesson['progress']; ?>%</div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Completed Lessons -->
  <?php if (!empty($completed)): ?>
    <section class="mt-16">
      <h2 class="text-2xl font-bold text-blue-700 mb-6">✅ Completed Lessons</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <?php foreach ($completed as $lesson): ?>
          <div class="transform transition hover:scale-[1.02] bg-green-50 border border-green-300 p-6 rounded-2xl shadow">
            <h3 class="text-xl font-bold mb-2 text-green-600"><?php echo htmlspecialchars($lesson['topic']); ?></h3>
            <p class="text-sm text-gray-600">🎉 You completed this lesson!</p>
            <div class="mt-2 text-sm text-gray-700 text-center font-medium">Progress: 100%</div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</main>

</body>
</html>
