// ============================================================
//  assets/js/app.js
//  MODIFIÉ :
//    - Notifications liées aux vraies dates des tâches
//    - setInterval toutes les 60 secondes (au lieu de setTimeout fixe)
//    - Évite les doublons grâce à localStorage
//    - Demande de permission au clic utilisateur (bonne pratique)
// ============================================================

// ── 1. Demande permission notifications ─────────────────────
function requestNotifPermission() {
  if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
  }
}

// Demande à la première interaction utilisateur (recommandé par les navigateurs)
document.addEventListener('click', requestNotifPermission, { once: true });

// ── 2. Affiche une notification navigateur ───────────────────
function showNotification(title, body) {
  if (Notification.permission !== 'granted') return;
  new Notification(title, {
    body: body,
    icon: 'https://cdn-icons-png.flaticon.com/512/1827/1827392.png',
  });
}

// ── 3. Vérifie les tâches proches — NOUVEAU ─────────────────
function checkUpcomingTasks() {
  // TASKS est injecté par dashboard.php via json_encode
  if (typeof TASKS === 'undefined' || !Array.isArray(TASKS)) return;

  const now       = Date.now();
  const oneHour   = 60 * 60 * 1000;
  const tenMin    = 10 * 60 * 1000;

  // Récupère les IDs déjà notifiés pour éviter les doublons
  let notified = JSON.parse(localStorage.getItem('notified_tasks') || '{}');

  TASKS.forEach(task => {
    if (task.status === 'completed') return;          // ignore les tâches finies
    if (!task.reminder)              return;          // pas de date de rappel

    const reminderTime = new Date(task.reminder).getTime();
    const diff         = reminderTime - now;

    // Rappel 1 heure avant
    if (diff > 0 && diff <= oneHour) {
      const key = `${task.id}_1h`;
      if (!notified[key]) {
        const mins = Math.round(diff / 60000);
        showNotification(
          '⏰ Rappel dans ~' + mins + ' min',
          task.title
        );
        notified[key] = true;
      }
    }

    // Rappel 10 minutes avant
    if (diff > 0 && diff <= tenMin) {
      const key = `${task.id}_10m`;
      if (!notified[key]) {
        showNotification(
          '🔔 Dans moins de 10 min !',
          task.title
        );
        notified[key] = true;
      }
    }

    // Rappel à l'heure exacte (±2 min)
    if (Math.abs(diff) <= 2 * 60 * 1000) {
      const key = `${task.id}_now`;
      if (!notified[key]) {
        showNotification(
          "🚨 C'est maintenant !",
          task.title
        );
        notified[key] = true;
      }
    }
  });

  // Sauvegarde les IDs notifiés
  localStorage.setItem('notified_tasks', JSON.stringify(notified));
}

// ── 4. Lance la vérification toutes les 60 secondes ─────────
// MODIFIÉ : setInterval au lieu d'un setTimeout unique à 5s
checkUpcomingTasks(); // vérifie dès le chargement
setInterval(checkUpcomingTasks, 60 * 1000);

// ── 5. Feedback visuel : cards "completed" grisées ──────────
document.querySelectorAll('.task-card.task-done').forEach(card => {
  card.style.opacity = '0.65';
});