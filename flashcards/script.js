const topics = ['html', 'css', 'js'];
const sound = document.getElementById('click-sound');

// Sound on click
document.querySelectorAll('.flashcard').forEach(card => {
  card.addEventListener('click', (e) => {
    // Avoid sound if "Surprise Me!" doesn't navigate immediately
    if (!card.classList.contains('random')) {
      sound.play();
    }
  });
});


