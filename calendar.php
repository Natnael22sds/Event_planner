<?php
// calendar.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Space Event Planner — Calendar</title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Font: Orbitron for space vibe -->
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600&display=swap" rel="stylesheet">

  <!-- FullCalendar CSS & JS (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

  <style>
    body { font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }
    .orbitron { font-family: 'Orbitron', sans-serif; }
    /* subtle starfield background */
    .star-bg {
      background: radial-gradient(ellipse at bottom, rgba(6,10,30,0.95), rgba(2,6,23,0.98));
      background-image:
        radial-gradient(1px 1px at 20% 30%, rgba(255,255,255,0.06) 30%, transparent 30%),
        radial-gradient(1px 1px at 80% 60%, rgba(255,255,255,0.05) 30%, transparent 30%),
        radial-gradient(1px 1px at 50% 80%, rgba(255,255,255,0.04) 30%, transparent 30%);
    }
    /* glowy event dot */
    .fc-event {
      box-shadow: 0 6px 18px rgba(72,172,255,0.12);
      border: 1px solid rgba(72,172,255,0.18);
    }
    .glow-btn:hover { box-shadow: 0 6px 20px rgba(72,172,255,0.25); }
  </style>
</head>
<body class="min-h-screen star-bg text-white">

  <nav class="p-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-gradient-to-r from-cyan-400 to-indigo-600 rounded-full flex items-center justify-center orbitron text-black font-bold">SS</div>
      <div>
        <h1 class="text-xl orbitron">Space Science Events</h1>
        <p class="text-sm text-gray-300">Company internal planner</p>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <span class="text-sm text-gray-300">Hi, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
      <a href="logout.php" class="px-3 py-1 rounded bg-gray-700 text-gray-200">Logout</a>
      <a href="dashboard.php" class="px-3 py-1 rounded bg-gray-700 text-gray-200">Go back to dashboard</a>
    </div>
  </nav>

  <main class="p-6 max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Left: controls + upcoming list -->
    <aside class="lg:col-span-1 space-y-4">
      <div class="bg-gray-800 p-4 rounded-lg">
        <h2 class="font-semibold orbitron text-lg">Filters</h2>

        <label class="block mt-3 text-xs text-gray-400">Category</label>
        <select id="filterCategory" class="w-full mt-1 bg-gray-700 p-2 rounded">
          <option value="">All</option>
          <option>Meeting</option>
          <option>Training</option>
          <option>Social</option>
        </select>

        <label class="block mt-3 text-xs text-gray-400">From</label>
        <input id="filterFrom" type="date" class="w-full mt-1 bg-gray-700 p-2 rounded" />

        <label class="block mt-3 text-xs text-gray-400">To</label>
        <input id="filterTo" type="date" class="w-full mt-1 bg-gray-700 p-2 rounded" />

        <button id="applyFilters" class="mt-4 w-full bg-cyan-500 text-black py-2 rounded orbitron">Apply</button>
        <button id="clearFilters" class="mt-2 w-full bg-gray-700 text-gray-200 py-2 rounded">Clear</button>
      </div>

      <div id="upcoming" class="bg-gray-800 p-4 rounded-lg">
        <h3 class="font-semibold orbitron">Upcoming Events</h3>
        <div id="upcomingList" class="mt-3 text-sm text-gray-300 space-y-3">
          <!-- Filled by JS -->
        </div>
      </div>
    </aside>

    <!-- Right: calendar -->
    <section class="lg:col-span-3 bg-transparent">
      <div id="calendar" class="bg-transparent rounded-lg p-2"></div>
    </section>
  </main>

  <!-- Event details modal -->
  <div id="eventModal" class="fixed inset-0 hidden items-center justify-center z-50">
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="relative bg-gray-900 w-full max-w-2xl rounded-lg p-6">
      <button id="closeModal" class="absolute top-3 right-3 text-gray-300">✕</button>
      <h2 id="modalTitle" class="text-2xl orbitron font-semibold"></h2>
      <p id="modalWhen" class="text-sm text-gray-300 mt-1"></p>
      <p id="modalLocation" class="text-sm text-gray-300 mt-1"></p>
      <p id="modalOrganizer" class="text-sm text-gray-300 mt-1"></p>
      <div id="modalDesc" class="mt-4 text-gray-200"></div>

      <div class="mt-6 flex gap-3">
        <form id="rsvpForm" method="POST" action="rsvp.php">
          <input type="hidden" name="event_id" id="rsvpEventId" />
          <input type="hidden" name="status" id="rsvpStatus" />
          <button type="button" data-status="going" class="rsvpBtn px-4 py-2 rounded bg-green-500 text-black orbitron">Going</button>
          <button type="button" data-status="maybe" class="rsvpBtn px-4 py-2 rounded bg-yellow-500 text-black orbitron">Maybe</button>
          <button type="button" data-status="not_going" class="rsvpBtn px-4 py-2 rounded bg-red-500 text-black orbitron">Not going</button>
        </form>
        <a id="openEventEdit" class="ml-auto px-3 py-2 text-sm rounded bg-gray-700 text-gray-200" href="#">Edit</a>
      </div>
    </div>
  </div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // initialize calendar
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      themeSystem: 'standard',
      height: 'auto',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,listWeek'
      },
      views: {
        dayGridMonth: { titleFormat: { year: 'numeric', month: 'long' } }
      },
      eventColor: '#4FD1C5', // cyan-ish glow
      eventTextColor: '#020617',
      events: fetchEventsFeed, // function below
      eventClick: function(info) {
        showEventModal(info.event);
      },
      loading: function(isLoading) {
        // optional: show loader
      }
    });
    calendar.render();

    // fetch events callback for FullCalendar
    function fetchEventsFeed(fetchInfo, successCallback, failureCallback) {
      const params = new URLSearchParams();
      params.set('start', fetchInfo.startStr);
      params.set('end', fetchInfo.endStr);
      // include category filter
      const cat = document.getElementById('filterCategory').value;
      if (cat) params.set('category', cat);

      fetch('events_feed.php?' + params.toString())
        .then(r => r.json())
        .then(data => successCallback(data))
        .catch(err => failureCallback(err));
    }

    // Filters
    document.getElementById('applyFilters').addEventListener('click', () => {
      calendar.refetchEvents();
      loadUpcoming(); // update side list
    });
    document.getElementById('clearFilters').addEventListener('click', () => {
      document.getElementById('filterCategory').value = '';
      document.getElementById('filterFrom').value = '';
      document.getElementById('filterTo').value = '';
      calendar.refetchEvents();
      loadUpcoming();
    });

    // Upcoming side list loader
    function loadUpcoming() {
      // build query for next 30 days or date range from inputs
      const cat = document.getElementById('filterCategory').value;
      const from = document.getElementById('filterFrom').value;
      const to = document.getElementById('filterTo').value;
      let params = new URLSearchParams();
      if (cat) params.set('category', cat);
      if (from) params.set('start', from + 'T00:00:00');
      if (to) params.set('end', to + 'T23:59:59');

      fetch('events_feed.php?' + params.toString())
        .then(r => r.json())
        .then(events => {
          const el = document.getElementById('upcomingList');
          el.innerHTML = '';
          if (events.length === 0) {
            el.innerHTML = '<div class="text-gray-500">No events found</div>';
            return;
          }
          events.slice(0, 8).forEach(ev => {
            const dt = new Date(ev.start);
            const formatted = dt.toLocaleString(undefined, { month:'short', day:'numeric', hour:'numeric', minute:'2-digit' });
            const item = document.createElement('div');
            item.className = 'p-2 rounded hover:bg-gray-700 transition cursor-pointer';
            item.innerHTML = `<div class="text-sm font-semibold">${ev.title}</div>
              <div class="text-xs text-gray-400">${formatted} — ${ev.extendedProps.location || 'No location'}</div>`;
            item.addEventListener('click', () => {
              // show event modal (create a pseudo FullCalendar Event object)
              const fakeEvent = { id: ev.id, title: ev.title, start: ev.start, end: ev.end, extendedProps: ev.extendedProps };
              showEventModal(fakeEvent);
            });
            el.appendChild(item);
          });
        });
    }

    // initial load
    loadUpcoming();

    // Modal helpers
    const modal = document.getElementById('eventModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalWhen = document.getElementById('modalWhen');
    const modalLocation = document.getElementById('modalLocation');
    const modalOrganizer = document.getElementById('modalOrganizer');
    const modalDesc = document.getElementById('modalDesc');
    const rsvpEventId = document.getElementById('rsvpEventId');
    const rsvpStatus = document.getElementById('rsvpStatus');
    const openEventEdit = document.getElementById('openEventEdit');

    function showEventModal(ev) {
      // support both FullCalendar Event object and our fake event
      const id = ev.id;
      const title = ev.title;
      const start = new Date(ev.start);
      const end = ev.end ? new Date(ev.end) : null;
      const ext = ev.extendedProps || (ev.extendedProps === undefined ? {} : ev.extendedProps);

      modalTitle.textContent = title;
      modalWhen.textContent = end ? `${start.toLocaleString()} — ${end.toLocaleString()}` : start.toLocaleString();
      modalLocation.textContent = ext.location ? 'Location: ' + ext.location : '';
      modalOrganizer.textContent = ext.organizer ? 'Organizer: ' + ext.organizer : '';
      modalDesc.innerHTML = ext.description ? ext.description.replace(/\n/g, '<br>') : '';

      rsvpEventId.value = id;
      openEventEdit.href = 'edit_event.php?id=' + encodeURIComponent(id);

      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    document.getElementById('closeModal').addEventListener('click', () => {
      modal.classList.add('hidden'); modal.classList.remove('flex');
    });

    // RSVP buttons
    document.querySelectorAll('.rsvpBtn').forEach(btn => {
      btn.addEventListener('click', () => {
        const status = btn.getAttribute('data-status');
        // set and POST via fetch to rsvp.php
        const fd = new FormData();
        fd.append('event_id', document.getElementById('rsvpEventId').value);
        fd.append('status', status);

        fetch('rsvp.php', {
          method: 'POST',
          body: fd
        }).then(r => r.json())
          .then(res => {
            if (res.success) {
              alert('RSVP saved: ' + status);
            } else {
              alert('Error: ' + (res.message || 'Unable to save'));
            }
          }).catch(err => alert('Network error'));
      });
    });

  });
</script>
</body>
</html>
