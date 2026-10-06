(function () {
  var body = document.body;
  var toggle = document.getElementById("admin-sidebar-toggle");
  var closeBtn = document.getElementById("admin-sidebar-close");
  var scrim = document.getElementById("admin-sidebar-scrim");
  var sidebar = document.getElementById("admin-sidebar");

  function setOpen(open) {
    if (open) {
      body.classList.add("admin-sidebar-open");
      if (toggle) toggle.setAttribute("aria-expanded", "true");
      if (scrim) scrim.removeAttribute("hidden");
    } else {
      body.classList.remove("admin-sidebar-open");
      if (toggle) toggle.setAttribute("aria-expanded", "false");
      if (scrim) scrim.setAttribute("hidden", "hidden");
    }
  }

  if (toggle && sidebar) {
    toggle.addEventListener("click", function () {
      var open = !body.classList.contains("admin-sidebar-open");
      setOpen(open);
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", function () {
      setOpen(false);
    });
  }

  if (scrim) {
    scrim.addEventListener("click", function () {
      setOpen(false);
    });
  }

  window.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      setOpen(false);
    }
  });

  window.addEventListener("resize", function () {
    if (window.matchMedia("(min-width: 901px)").matches) {
      setOpen(false);
    }
  });

  document.querySelectorAll(".admin-alert[data-dismissible] .admin-alert__dismiss").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var root = btn.closest(".admin-alert");
      if (root && root.parentElement) {
        root.parentElement.removeChild(root);
      }
    });
  });

  /** Keep the current sidebar item visible after full page navigation (desktop scrollable nav). */
  function scrollActiveSidebarLinkIntoView() {
    var nav = document.querySelector(".admin-sidebar__nav");
    var active = nav && nav.querySelector("a.admin-navlink.is-active");
    if (!nav || !active) {
      return;
    }
    requestAnimationFrame(function () {
      active.scrollIntoView({ block: "center", inline: "nearest", behavior: "auto" });
    });
  }

  scrollActiveSidebarLinkIntoView();
})();

/* ============================================================
   Theme switcher
   Source of truth: PHP session (cms-admin/actions/theme-update.php
   writes $_SESSION['wpm_theme']; includes/header.php reads it and
   renders data-theme on <html> server-side — no FOUC, and it follows
   the admin across every page navigation). This block:
     1. Applies the chosen theme to <html> immediately on change.
     2. Persists it to the session via fetch() so the next page load
        (any menu, any tab) keeps the same theme.
     3. Also mirrors it to localStorage as a same-tab fallback only.
   ============================================================ */
(function () {
  var THEME_KEY    = "wpm-theme";
  var DEFAULT      = "light-modern";
  var VALID_THEMES = ["dark-modern", "light-modern", "deep-purple"];
  var html         = document.documentElement;

  function persistToSession(theme, select) {
    var action = select.dataset.themeAction;
    var token  = select.dataset.csrfToken;
    if (!action) { return; }
    fetch(action, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-CSRF-Token": token || ""
      },
      body: "theme=" + encodeURIComponent(theme),
      credentials: "same-origin"
    }).catch(function () {
      /* Network hiccup: theme still applied for this page view via
         localStorage/dataset; it will just re-sync from session on the
         next successful navigation instead. */
    });
  }

  function applyTheme(theme, select) {
    if (VALID_THEMES.indexOf(theme) === -1) { theme = DEFAULT; }
    html.dataset.theme = theme;
    try { localStorage.setItem(THEME_KEY, theme); } catch (e) {}
    if (select) {
      if (select.value !== theme) { select.value = theme; }
      persistToSession(theme, select);
    }
  }

  function initSelect() {
    var select = document.getElementById("theme-switcher");
    if (!select) { return; }
    var current = html.dataset.theme || DEFAULT;
    select.value = current;
    select.addEventListener("change", function () {
      applyTheme(select.value, select);
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initSelect);
  } else {
    initSelect();
  }
}());

/* ============================================================
   Sidebar menu search (navbar)
   Pure client-side filter over the role-filtered sidebar menu list
   embedded by includes/navbar.php (data-menu) — no server request.
   ============================================================ */
(function () {
  var input = document.getElementById("admin-search-input");
  var resultsBox = document.getElementById("admin-search-results");
  if (!input || !resultsBox) { return; }

  var wrapper = input.closest(".admin-search");
  var menu = [];
  try { menu = JSON.parse((wrapper && wrapper.dataset.menu) || "[]"); } catch (e) {}

  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function hideResults() {
    resultsBox.setAttribute("hidden", "hidden");
    resultsBox.innerHTML = "";
  }

  function render(query) {
    var q = query.toLowerCase();
    var matches = menu.filter(function (m) {
      return (m.label + " " + m.group).toLowerCase().indexOf(q) !== -1;
    });
    if (!matches.length) {
      resultsBox.innerHTML =
        '<div class="admin-search__empty">Menu “' + escapeHtml(query) + '” tidak ditemukan.</div>';
    } else {
      resultsBox.innerHTML = matches.map(function (m) {
        return '<a class="admin-search__item" href="' + escapeHtml(m.href) + '">' +
          '<span class="admin-search__item-title">' + escapeHtml(m.label) + "</span>" +
          '<span class="admin-search__item-subtitle">' + escapeHtml(m.group) + "</span></a>";
      }).join("");
    }
    resultsBox.removeAttribute("hidden");
  }

  input.addEventListener("input", function () {
    var query = input.value.trim();
    if (!query) { hideResults(); return; }
    render(query);
  });

  input.addEventListener("focus", function () {
    if (input.value.trim()) { render(input.value.trim()); }
  });

  document.addEventListener("click", function (e) {
    if (!wrapper.contains(e.target)) { hideResults(); }
  });

  input.addEventListener("keydown", function (e) {
    if (e.key === "Escape") { hideResults(); input.blur(); }
    if (e.key === "Enter") {
      var first = resultsBox.querySelector("a.admin-search__item");
      if (first) { e.preventDefault(); window.location.href = first.getAttribute("href"); }
    }
  });
}());

/* ============================================================
   Notification bell — Growth Agent jobs needing attention
   (failed generations, SEO recommendations awaiting review).
   Server-rendered dropdown, just a plain show/hide toggle —
   same click-outside-to-close pattern as the search box above.
   ============================================================ */
(function () {
  var toggle = document.getElementById("admin-notif-toggle");
  var panel = document.getElementById("admin-notif-panel");
  var wrapper = document.getElementById("admin-notif");
  if (!toggle || !panel || !wrapper) { return; }

  function isOpen() {
    return !panel.hasAttribute("hidden");
  }

  function setOpen(open) {
    if (open) {
      panel.removeAttribute("hidden");
      toggle.setAttribute("aria-expanded", "true");
    } else {
      panel.setAttribute("hidden", "hidden");
      toggle.setAttribute("aria-expanded", "false");
    }
  }

  toggle.addEventListener("click", function (e) {
    e.stopPropagation();
    setOpen(!isOpen());
  });

  document.addEventListener("click", function (e) {
    if (!wrapper.contains(e.target)) {
      setOpen(false);
    }
  });

  window.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      setOpen(false);
    }
  });
}());
