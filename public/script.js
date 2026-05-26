// --- Loading Overlay for List Lock ---
function showListLoadingOverlay(text = "Loading...") {
  let overlay = document.getElementById("listLoadingOverlay");
  if (!overlay) {
    overlay = document.createElement("div");
    overlay.id = "listLoadingOverlay";
    overlay.style.position = "fixed";
    overlay.style.top = 0;
    overlay.style.left = 0;
    overlay.style.right = 0;
    overlay.style.bottom = 0;
    overlay.style.background = "rgba(24,26,36,0.92)";
    overlay.style.zIndex = 99999;
    overlay.style.display = "flex";
    overlay.style.flexDirection = "column";
    overlay.style.alignItems = "center";
    overlay.style.justifyContent = "center";
    overlay.style.color = "#ffe66d";
    overlay.style.fontSize = "2rem";
    overlay.style.fontFamily = "'JetBrains Mono', monospace";
    overlay.innerHTML = `<div>${text}</div><div id='listLoadingSpinner' style='margin-top:24px;font-size:1.5rem;'>⏳</div>`;
    document.body.appendChild(overlay);
  } else {
    overlay.style.display = "flex";
    overlay.firstChild.textContent = text;
  }
  // Disable all controls
  document
    .querySelectorAll(
      "#dashboard input, #dashboard button, #dashboard select, #dashboard a, #dashboard .btn",
    )
    .forEach((el) => {
      el.setAttribute("disabled", "disabled");
      el.style.pointerEvents = "none";
      el.style.opacity = 0.5;
    });
}

function hideListLoadingOverlay() {
  const overlay = document.getElementById("listLoadingOverlay");
  if (overlay) overlay.style.display = "none";
  // Re-enable all controls
  document
    .querySelectorAll(
      "#dashboard input, #dashboard button, #dashboard select, #dashboard a, #dashboard .btn",
    )
    .forEach((el) => {
      el.removeAttribute("disabled");
      el.style.pointerEvents = "";
      el.style.opacity = "";
    });
}

// --- Patch display controls to show overlay ---
function renderUIWithOverlay() {
  showListLoadingOverlay("Loading...");
  setTimeout(() => {
    renderUI();
    hideListLoadingOverlay();
  }, 0);
}

const ENCRYPTION_KEY_STR = "h3y_gUyZ";

// Pagination & View Variables
let CURRENT_PAGE = 1;
let ITEMS_PER_PAGE = 16;
let CURRENT_SORT = "id";
let CURRENT_VIEW = "list";
let MASTER_LIST = [];
const MASTER_LIST_BY_ID = {};
let ACTIVE_TAGS = [];
let ACTIVE_STATUSES = [];
let ITEM_MAP = {};
let SHOW_UNOB = false;
let SHOW_ALL_IDS = false;
var HARDMODE = 0;
const RESEARCH_DEPENDENCIES = {
  5437: [5358, 5359, 5360, 5361], // Shellphone -> Home, Spawn, Ocean, Underworld
  5324: [5329, 5330], // Rubblemaker -> Medium, Large
  4131: [5325], // Void Bag -> Closed void bag
  4346: [5391], // Encubering stone -> Uncumbering
  4767: [5453], // Critter companionship -> inactive
  5309: [5454], // Environmental preservation -> inactive
  5323: [5455], // Peaceful coexistence -> inactive
  5526: [2611], // Flairon -> Flairoon
};

// Saves all user preferences (view, tags, status, page size) to localStorage.
let allItems = [];
let allTags = {}; // changed from [] to {} to hold category->tags mapping

async function loadItems() {
  const response = await fetch('/items.json');
  const items = await response.json();

  allItems = Object.values(items);

  loadDatabase();
}

async function loadTags() {
  const response = await fetch('/tags.json');
  const tags = await response.json();

  allTags = {};
  Object.values(tags).forEach((entry) => {
    if (entry && entry.category && Array.isArray(entry.tags)) {
      allTags[entry.category] = entry.tags;
    }
  });
}

loadItems();
loadTags();

document.addEventListener("DOMContentLoaded", () => {
});

function savePreferences() {
  const preferences = {
    view: CURRENT_VIEW,
    tags: ACTIVE_TAGS,
    statuses: ACTIVE_STATUSES,
    pageSize: ITEMS_PER_PAGE,
    currentPage: CURRENT_PAGE,
    displayUnobtainable: SHOW_UNOB,
    showAllIds: SHOW_ALL_IDS,
    hardmodeFilter: HARDMODE,
  };
  localStorage.setItem("terraria_user_prefs", JSON.stringify(preferences));
}

async function decryptPlayerFile(encryptedBuffer) {
  const keyBuf = new ArrayBuffer(ENCRYPTION_KEY_STR.length * 2);
  const keyView = new Uint16Array(keyBuf);
  for (let i = 0; i < ENCRYPTION_KEY_STR.length; i++)
    keyView[i] = ENCRYPTION_KEY_STR.charCodeAt(i);
  const keyBytes = new Uint8Array(keyBuf);
  const cryptoKey = await window.crypto.subtle.importKey(
    "raw",
    keyBytes,
    { name: "AES-CBC" },
    false,
    ["decrypt"],
  );
  try {
    const decrypted = await window.crypto.subtle.decrypt(
      { name: "AES-CBC", iv: keyBytes },
      cryptoKey,
      encryptedBuffer,
    );
    return new DataView(decrypted);
  } catch (e) {
    console.error("Decryption failed:", e);
    return new DataView(encryptedBuffer);
  }
}

// load preferences on startup (before database load)
function loadPreferences() {
  const savedPrefs = localStorage.getItem("terraria_user_prefs");
  if (savedPrefs) {
    const prefs = JSON.parse(savedPrefs);
    CURRENT_VIEW = prefs.view || "list";
    ACTIVE_TAGS = prefs.tags || [];
    ACTIVE_STATUSES = prefs.statuses || [];
    ITEMS_PER_PAGE = prefs.pageSize === null ? 16 : prefs.pageSize;
    SHOW_UNOB = prefs.displayUnobtainable || false;
    SHOW_ALL_IDS = prefs.showAllIds || false;
    HARDMODE = prefs.hardmodeFilter || 0;
  }
}

// Load the items database
function loadDatabase() {
  try {
    if (typeof allItems === "undefined") {
      document.getElementById("status").innerText =
        "Error: database not found.";
      return;
    }

    // Map the dictionary format to our internal MASTER_LIST
    MASTER_LIST = allItems.map((item) => {
      const obj = {
        id: item.id,
        display: item.name,
        internal: item.internalName,
        required: item.neededForResearch,
        wiki: "https://terraria.wiki.gg/wiki/" + item.itemUrl,
        icon: "/assets/icons/" + item.imageUrl,
        tags: item.tags,
        unobtainable: item.isUnobtainable || false, // Capture the boolean flag
        hardmode: item.isHm || false,
        current: 0,
      };
      // Add flatTags property for easier tag filtering
      obj.flatTags = Object.values(item.tags || {}).flat();
      ITEM_MAP[item.internalName] = obj;
      return obj;
    });

    // ✅ ADD THIS (fixes your error)
    MASTER_LIST.forEach((item) => {
      MASTER_LIST_BY_ID[item.id] = item;
    });

    // --- Retrieve Preferences ---
    const savedPrefs = localStorage.getItem("terraria_user_prefs");
    if (savedPrefs) {
      const prefs = JSON.parse(savedPrefs);

      CURRENT_VIEW = prefs.view || "list";
      ACTIVE_TAGS = prefs.tags || [];
      ACTIVE_STATUSES = prefs.statuses || [];
      ITEMS_PER_PAGE = prefs.pageSize || 50;

      // Sync UI elements to match loaded preferences
      document.getElementById("pageSize").value = ITEMS_PER_PAGE >= allItems.length ? "all" : ITEMS_PER_PAGE;
      document.getElementById("viewToggle").innerText =
        CURRENT_VIEW === "list" ? "Grid View" : "List View";

      // Sync status button visual state
      document.querySelectorAll("#statusGroup .btn").forEach((btn) => {
        if (ACTIVE_STATUSES.includes(btn.dataset.status)) {
          btn.classList.add("active");
        }
      });
    }

    // Load research progress from localStorage cache
    const cachedData = localStorage.getItem("terraria_research_data");
    const cachedName = localStorage.getItem("terraria_player_name");

    if (cachedData) {
      const savedCounts = JSON.parse(cachedData);
      MASTER_LIST.forEach((item) => {
        if (savedCounts === undefined || savedCounts === null) return;
        if (savedCounts[item.internal] !== undefined) {
          item.current = savedCounts[item.internal];
        }
      });
      if (cachedName)
        document.getElementById("playerName").innerText = cachedName;
    }

    renderUI();

    document.getElementById("plrInput").disabled = false;
    document.querySelector(".custom-file-upload").style.opacity = "1";
  } catch (err) {
    console.error(err);
    document.getElementById("status").innerText =
      "Error initializing database.";
  }
}

// Handles .plr file upload, decryption, and scanning for research keys.
document.getElementById("plrInput").addEventListener("change", async (e) => {
  const file = e.target.files[0];
  if (!file) return;

  document.getElementById("status").innerText = "Analyzing...";
  const buffer = await file.arrayBuffer();
  const view = await decryptPlayerFile(buffer);
  const decoder = new TextDecoder();
  const bufferUint8 = new Uint8Array(view.buffer);
  let plrName = file.name.replace(".plr", "");

  try {
    const nameLen = view.getUint8(24);
    if (nameLen > 0 && nameLen < 32) {
      plrName = decoder.decode(bufferUint8.slice(25, 25 + nameLen));
    }
  } catch (err) {
    /* fallback to filename */
  }

  document.getElementById("playerName").innerText = plrName;
  document.getElementById("status").innerText = "";

  // Reset current research then scan buffer for internalName strings
  MASTER_LIST.forEach((item) => (item.current = 0));

  for (let i = 0; i < view.byteLength - 10; i++) {
    const len = view.getUint8(i);
    if (len >= 2 && len < 64 && i + len + 4 < view.byteLength) {
      const key = decoder.decode(bufferUint8.slice(i + 1, i + 1 + len));
      const match = ITEM_MAP[key];
      if (match) {
        const count = view.getInt32(i + 1 + len, true);
        if (count >= 0 && count <= 5000) {
          if (count > match.current) match.current = count;
          i += len + 4 - 1;
        }
      }
    }
  }

  for (const [parentId, childIds] of Object.entries(RESEARCH_DEPENDENCIES)) {
    const parentItem = MASTER_LIST_BY_ID[parentId];
    if (parentItem && parentItem.current >= parentItem.required) {
      childIds.forEach((cId) => {
        const childItem = MASTER_LIST.find((item) => item.id == cId);
        if (childItem) {
          // Force child to be "Complete" by matching its own requirement
          childItem.current = childItem.required;
        }
      });
    }
  }

  // Save progress to cache
  const dataToSave = {};
  MASTER_LIST.forEach((item) => {
    if (item.current > 0) dataToSave[item.internal] = item.current;
  });
  localStorage.setItem("terraria_research_data", JSON.stringify(dataToSave));
  localStorage.setItem("terraria_player_name", plrName);

  CURRENT_PAGE = 1;
  renderUI();
});

function renderPaginationControls(totalItems) {
  const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE) || 1;
  const containers = [
    document.getElementById("paginationTop"),
  ];

  const html = `
    <div class="pagination-wrapper">
        <button class="btn" ${CURRENT_PAGE === 1 ? "disabled" : ""} id="prevPageBtn"><b><</b></button>
        <div class="page-jump">
            <span>Page</span>
            <input type="number" id="pageInput" class="page-num-input" value="${CURRENT_PAGE}" min="1" max="${totalPages}">
            <span>of ${totalPages}</span>
        </div>
        <button class="btn" ${CURRENT_PAGE >= totalPages ? "disabled" : ""} id="nextPageBtn"><b>></b></button>
    </div>`;

  containers.forEach((container) => {
    if (!container) return;
    container.innerHTML = html;

    container.querySelector("#prevPageBtn")?.addEventListener("click", () => {
      CURRENT_PAGE--;
      savePreferences();
      renderUI();
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
    container.querySelector("#nextPageBtn")?.addEventListener("click", () => {
      CURRENT_PAGE++;
      savePreferences();
      renderUI();
      window.scrollTo({ top: 0, behavior: "smooth" });
    });

    const pInput = container.querySelector("#pageInput");
    pInput?.addEventListener("change", (e) => {
      let val = parseInt(e.target.value);
      if (isNaN(val) || val < 1) val = 1;
      if (val > totalPages) val = totalPages;
      CURRENT_PAGE = val;
      savePreferences();
      renderUI();
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  });
}

function preloadNearbyPages(displayList) {
  const totalPages = Math.ceil(displayList.length / ITEMS_PER_PAGE);

  const startPage = Math.max(1, CURRENT_PAGE - 3);
  const endPage = Math.min(totalPages, CURRENT_PAGE + 3);

  const preloaded = new Set();

  for (let page = startPage; page <= endPage; page++) {
    const start = (page - 1) * ITEMS_PER_PAGE;
    const end = start + ITEMS_PER_PAGE;

    const pageItems = displayList.slice(start, end);

    for (const item of pageItems) {
      if (!item.icon || preloaded.has(item.icon)) continue;

      const img = new Image();
      img.src = item.icon;

      preloaded.add(item.icon);
    }
  }
}

function renderUI() {
  document.getElementById("dashboard").classList.remove("hidden");
  const container = document.getElementById("itemBody");
  const tableWrapper = document.querySelector(".table-wrapper");

  // load preferences in case they were changed outside of this function
  savePreferences();
  loadPreferences();

  const searchTerm = document.getElementById("search").value.toLowerCase();
  // const statusFilter = document.getElementById("statusFilter").value;
  // const showUnobtainable = SHOW_UNOB;

  if (
    document.getElementById("pageSize").value === "all" ||
    parseInt(document.getElementById("pageSize").value) === null
  ) {
    ITEMS_PER_PAGE = allItems.length;
  } else {
    ITEMS_PER_PAGE = Math.min(
      parseInt(document.getElementById("pageSize").value),
      allItems.length,
    );
  }

  if (isNaN(ITEMS_PER_PAGE) || ITEMS_PER_PAGE <= 0) {
    loadPreferences();
  }

  if (ITEMS_PER_PAGE >= allItems.length) {
    document.getElementById("pageSize").value = "all";
  } else {
    document.getElementById("pageSize").value = ITEMS_PER_PAGE;
  }

  let displayList = MASTER_LIST.filter((item) => {
    const searchTerm = document.getElementById("search").value.toLowerCase();
    const matchesSearch =
      item.display.toLowerCase().includes(searchTerm) ||
      item.id.toString().includes(searchTerm);

    // 1. Tag Filtering (OR Logic)
    if (ACTIVE_TAGS.length > 0) {
      const itemTagsFlat = item.flatTags;
      if (!ACTIVE_TAGS.some((tag) => itemTagsFlat.includes(tag))) return false;
    }

    // 2. Status & Unobtainable Filtering (OR Logic)
    if (!SHOW_ALL_IDS) {
      if (ACTIVE_STATUSES.length > 0) {
        const matchesStatus = ACTIVE_STATUSES.some((status) => {
          if (status === "complete")
            return item.current >= item.required && !item.unobtainable;
          if (status === "researching")
            return (
              item.current > 0 &&
              item.current < item.required &&
              !item.unobtainable
            );
          if (status === "not_started")
            return item.current === 0 && !item.unobtainable;
          if (status === "unobtainable") return item.unobtainable;
          return false;
        });
        if (!matchesStatus) return false;
      } else {
        // Default behavior: Hide unobtainable if no status filters are active
        if (item.unobtainable) return false;
      }
    }

    if (HARDMODE == Number(!item.hardmode) + 1) return false;

    return matchesSearch;
  });

  // If sorting by id, optionally fill in missing IDs with placeholders
  if (CURRENT_SORT === "id" && SHOW_ALL_IDS) {
    // Find min and max id in the filtered list
    const minId = displayList.length
      ? Math.min(...displayList.map((i) => i.id))
      : 1;
    const maxId = displayList.length
      ? Math.max(...displayList.map((i) => i.id))
      : 1;
    const idMap = new Map(displayList.map((i) => [i.id, i]));
    const filledList = [];
    for (let id = minId; id <= maxId; id++) {
      if (idMap.has(id)) {
        filledList.push(idMap.get(id));
      } else {
        // Add a placeholder for missing id
        filledList.push({
          id,
          display: "",
          internal: "",
          required: "",
          wiki: "",
          icon: "",
          tags: {},
          unobtainable: false,
          hardmode: false,
          current: "",
          isPlaceholder: true,
        });
      }
    }
    displayList = filledList;
  } else if (CURRENT_SORT === "name") {
    displayList.sort((a, b) => a.display.localeCompare(b.display));
  }
  // --- Toggle All IDs Checkbox ---
  const toggleAllIdsCheckbox = document.getElementById("toggleAllIdsCheckbox");
  toggleAllIdsCheckbox.checked = SHOW_ALL_IDS;
  toggleAllIdsCheckbox.addEventListener("change", (e) => {
    SHOW_ALL_IDS = e.target.checked;
    renderUI();
  });

  const totalFiltered = displayList.length;
  const start = (CURRENT_PAGE - 1) * ITEMS_PER_PAGE;
  const paginatedList = displayList.slice(start, start + ITEMS_PER_PAGE);

  document
    .querySelectorAll('link[rel="preload"][data-dynamic="true"]')
    .forEach((el) => el.remove());

  preloadNearbyPages(displayList);

  // Clear Grid if it exists
  let gridBox = document.getElementById("gridBox");
  if (gridBox) gridBox.innerHTML = "";

  if (CURRENT_VIEW === "list") {
    tableWrapper.style.display = "block";
    if (gridBox) gridBox.style.display = "none";
    const fragment = document.createDocumentFragment();

    paginatedList.forEach((item) => {
      const isDone = item.current >= item.required;
      const unobClass = item.unobtainable;
      const pct =
        item.required > 0
          ? Math.min(100, (item.current / item.required) * 100)
          : 0;

      const row = document.createElement("tr");

      row.innerHTML = `
    <td style="opacity:0.5">${item.id}</td>
    <td>
   <img src="${item.icon}" loading="lazy"
     onerror="this.src='/assets/icons/Default.png';">
    </td>
    <td>
      <a href="${item.wiki}" target="_blank" class="wiki-link">
        <strong>${item.display}</strong>
      </a>
    </td>
    <td>
      <div class="prog-bg">
        <div class="prog-fill" style="width:${unobClass ? 0 : pct}%"></div>
      </div>
      ${unobClass ? "" : item.current + "/" + item.required}
    </td>
    <td class="${unobClass ? "unob" : isDone ? "done" : item.current === 0 ? "none" : "mid"
        }">
      ${unobClass
          ? "UNOBTAINABLE"
          : isDone
            ? "COMPLETE"
            : item.current === 0
              ? "NOT STARTED"
              : "RESEARCHING"
        }
    </td>
    <td>
      <input type="checkbox" class="item-check" data-id="${item.id}" ${isDone ? "checked" : ""}>
    </td>
  `;

      fragment.appendChild(row);
    });

    // ONLY do this once (outside loop)
    container.innerHTML = "";
    container.appendChild(fragment);
  } else {
    tableWrapper.style.display = "none";
    if (!gridBox) {
      gridBox = document.createElement("div");
      gridBox.id = "gridBox";
      gridBox.className = "grid-container";
      tableWrapper.parentNode.insertBefore(gridBox, tableWrapper.nextSibling);
    }
    gridBox.style.display = "grid";
    gridBox.innerHTML = paginatedList
      .map((item) => {
        const isDone = item.current >= item.required;
        const unobClass = item.unobtainable ? "unobtainable" : "";
        const statusClass = unobClass
          ? "unob"
          : isDone
            ? "done"
            : item.current === 0
              ? "none"
              : "mid";

        // Added checkbox so grid supports toggling too
        return `
        <div class="item-card ${statusClass} ${unobClass}">
            <spam>${item.id}</spam>
            <a href="${item.wiki}" target="_blank" class="wiki-link">
              <img src="${item.icon}" onerror="this.src='/assets/icons/Default.png';">
              <div class="item-name">${item.display}</div>
            </a>
            <div class="mini-prog">${unobClass ? "Unobtainable" : item.current + "/" + item.required}</div>
            <input type="checkbox" class="item-check card-check" data-id="${item.id}" ${isDone ? "checked" : ""} title="Mark complete">
        </div>`;
      })
      .join("");
  }
  // Update Stats
  const obtainableItems = MASTER_LIST.filter((i) => !i.unobtainable);

  const totalObtainable = obtainableItems.length;
  const finished = obtainableItems.filter(
    (i) => i.current >= i.required,
  ).length;
  const progressing = obtainableItems.filter(
    (i) => i.current > 0 && i.current < i.required,
  ).length;

  // Calculate percentage based only on obtainable items
  const percent =
    totalObtainable > 0
      ? Math.round((finished / totalObtainable) * 10000) / 100
      : 0;

  document.getElementById("statTotal").innerText = totalObtainable;
  document.getElementById("statDone").innerText = finished;
  document.getElementById("statProgressing").innerText = progressing;
  document.getElementById("statPercent").innerText = percent + "%";

  renderPaginationControls(totalFiltered);
  renderActiveFilters();
  savePreferences();
}


// Persist research state (internalName -> current)
function saveResearchState() {
  const dataToSave = {};
  MASTER_LIST.forEach((item) => {
    if (item.current > 0) dataToSave[item.internal] = item.current;
  });
  localStorage.setItem("terraria_research_data", JSON.stringify(dataToSave));
}

function restoreResearchState() {
  if (localStorage.getItem("terraria_research_data") === null){
    if (localStorage.getItem("terraria_research_data_backup") !== null) {
      localStorage.setItem("terraria_research_data", localStorage.getItem("terraria_research_data_backup"));
      localStorage.removeItem("terraria_research_data_backup");
    } else {
      alert("No research data found to restore.");
      return; // No data to restore
    }
  }
  const savedData = localStorage.getItem("terraria_research_data");
  if (savedData) {
    const parsedData = JSON.parse(savedData);
    MASTER_LIST.forEach((item) => {
      if (parsedData[item.internal]) {
        item.current = parsedData[item.internal];
      }
    });
  }
}

function clearResearchState() {
  MASTER_LIST.forEach((item) => {
    item.current = 0;
  });
  localStorage.removeItem("terraria_research_data");
}

// Event delegation so checkbox handlers survive re-renders
document.addEventListener("change", (e) => {
  const target = e.target;
  if (!target.classList.contains("item-check")) return;
  const id = parseInt(target.dataset.id, 10);
  if (isNaN(id)) return;

  const item = MASTER_LIST.find((i) => i.id === id);
  if (!item) return;

  // Toggle: checked => complete, unchecked => reset to 0
  item.current = target.checked ? item.required : 0;
  saveResearchState();
  // Re-render to update UI/stats (keeps pagination & view)
  renderUI();
});

// --- Initialization & Listeners ---
document.getElementById("themeToggle").addEventListener("click", () => {
  const currentTheme = document.documentElement.dataset.theme;
  document.documentElement.dataset.theme =
    currentTheme === "dark" ? "light" : "dark";
});

window.addEventListener("DOMContentLoaded", () => {
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  document.documentElement.dataset.theme = prefersDark ? "dark" : "light";
  loadDatabase();
});

// Only attach to buttons that have a data-sort attribute
document.querySelectorAll(".btn[data-sort]").forEach((btn) => {
  btn.onclick = (e) => {
    document
      .querySelectorAll(".btn[data-sort]")
      .forEach((b) => b.classList.remove("active"));
    e.target.classList.add("active");
    CURRENT_SORT = e.target.dataset.sort;
    CURRENT_PAGE = 1;
    renderUI();
  };
});

document.getElementById("viewToggle").addEventListener("click", (e) => {
  CURRENT_VIEW = CURRENT_VIEW === "list" ? "grid" : "list";
  e.target.innerText = CURRENT_VIEW === "list" ? "Grid View" : "List View";
  savePreferences();
  renderUI();
});

let searchTimeout;
document.getElementById("search").oninput = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    CURRENT_PAGE = 1;
    renderUI();
  }, 200);
};

document.getElementById("pageSize").onchange = () => {
  CURRENT_PAGE = 1;
  savePreferences();
  renderUI();
};

const tagModal = document.getElementById("tagModal");
document.getElementById("tagFilterBtn").onclick = () => {
  renderTagModal();
  tagModal.classList.remove("hidden");
  showListLoadingOverlay("Loading tags...");
  setTimeout(() => hideListLoadingOverlay(), 300);
};

function renderTagModal() {
  const container = document.getElementById("tagContainer");
  container.innerHTML = "";

  // Helper to calculate completion for a specific tag
  const getTagStats = (tagName) => {
    const itemsWithTag = MASTER_LIST.filter(
      (item) =>
        !item.unobtainable && Object.values(item.tags).flat().includes(tagName),
    );
    if (itemsWithTag.length === 0) return null;
    const done = itemsWithTag.filter(
      (item) => item.current >= item.required,
    ).length;
    return {
      percent: Math.floor((done / itemsWithTag.length) * 100),
      count: itemsWithTag.length,
    };
  };

  for (let category in allTags) {
    // Calculate overall category completion
    const subTags = allTags[category];
    const catItems = MASTER_LIST.filter(
      (item) => !item.unobtainable && Object.keys(item.tags).includes(category),
    );
    const catDone = catItems.filter(
      (item) => item.current >= item.required,
    ).length;
    const catPercent =
      catItems.length > 0 ? Math.floor((catDone / catItems.length) * 100) : 0;

    const group = document.createElement("div");
    group.className = "tag-group";
    group.innerHTML = `
            <h3 class="tag-header" data-cat="${category}" style="cursor: pointer;">
              <img class="img-cat" src="/assets/icons/${category + ".png"}">
              <spam>${category}</spam>
              <span class="cat-pct">${catPercent}%</span>
            </h3>
            <div class="tag-list"></div>
        `;

    const list = group.querySelector(".tag-list");
    subTags.forEach((tag) => {
      const stats = getTagStats(tag);
      if (!stats) return; // Skip tags with no items

      const chip = document.createElement("div");
      const isActive = ACTIVE_TAGS.includes(tag);
      chip.className = `tag-chip ${isActive ? "active" : ""}`;

      // Added percentage to the chip text
      chip.innerHTML = `${tag} <span class="chip-pct">${stats.percent}%</span>`;

      chip.onclick = () => {
        if (ACTIVE_TAGS.includes(tag)) {
          ACTIVE_TAGS = ACTIVE_TAGS.filter((t) => t !== tag);
        } else {
          ACTIVE_TAGS.push(tag);
        }
        renderTagModal();
        CURRENT_PAGE = 1;
        savePreferences();
        renderUI();
      };
      list.appendChild(chip);
    });
    container.appendChild(group);
  }
}

document.getElementById("closeModal").onclick = () =>
  tagModal.classList.add("hidden");
document.getElementById("clearTags").onclick = () => {
  ACTIVE_TAGS = [];
  renderTagModal();
  renderUI();
};

document.getElementById("restoreAllResearch").onclick = () => {
  ACTIVE_TAGS = [];
  restoreResearchState();
  renderUI();
};

document.getElementById("clearAllResearch").onclick = () => {
  ACTIVE_TAGS = [];
  clearResearchState();
  renderUI();
};

document.getElementById("resetSearch").onclick = () => {
  let researchData = localStorage.getItem("terraria_research_data");
  localStorage.setItem("terraria_research_data_backup", researchData);
  clearResearchState();
  renderUI();
}

// Global click listener for category headers
document.addEventListener("click", (e) => {
  const header = e.target.closest(".tag-header");
  if (!header) return;

  const category = header.dataset.cat;
  const subTags = allTags[category];

  // Check if all subtags in this category are already in ACTIVE_TAGS
  const allSelected = subTags.every((tag) => ACTIVE_TAGS.includes(tag));

  if (allSelected) {
    // If all are selected, remove them all (Deselect All)
    ACTIVE_TAGS = ACTIVE_TAGS.filter((tag) => !subTags.includes(tag));
  } else {
    // Otherwise, add any that are missing (Select All)
    subTags.forEach((tag) => {
      if (!ACTIVE_TAGS.includes(tag)) ACTIVE_TAGS.push(tag);
    });
  }

  // Refresh UI
  renderTagModal();
  CURRENT_PAGE = 1;
  savePreferences();
  renderUI();
});

document.querySelectorAll("#phaseGroup .btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    const hardmode = Number(btn.dataset.status);
    document
      .querySelectorAll("#phaseGroup .btn")
      .forEach((b) => b.classList.remove("active"));

    if (hardmode != HARDMODE) {
      HARDMODE = hardmode;
      btn.classList.add("active");
    } else {
      HARDMODE = 0;
    }

    CURRENT_PAGE = 1;
    savePreferences();
    renderUI();
  });
});

document.querySelectorAll("#statusGroup .btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    const status = btn.dataset.status;

    if (ACTIVE_STATUSES.includes(status)) {
      ACTIVE_STATUSES = ACTIVE_STATUSES.filter((s) => s !== status);
      btn.classList.remove("active");
    } else {
      ACTIVE_STATUSES.push(status);
      btn.classList.add("active");
    }

    CURRENT_PAGE = 1;
    savePreferences();
    renderUI();
  });
});

function renderActiveFilters() {
  const container = document.getElementById("activeFiltersBar");
  const list = document.getElementById("activeFiltersList");
  list.innerHTML = "";
  if (ACTIVE_TAGS.length === 0) {
    container.classList.add("hidden");
    return;
  } else {
    container.classList.remove("hidden");
  }

  // Add Pills for Tags
  ACTIVE_TAGS.forEach((tag) => {
    const pill = document.createElement("div");
    pill.className = "filter-pill";
    pill.innerHTML = `<span>${tag}</span> <span class="remove-x">×</span>`;
    pill.onclick = () => {
      ACTIVE_TAGS = ACTIVE_TAGS.filter((t) => t !== tag);
      savePreferences();
      renderUI();
    };
    list.appendChild(pill);
  });
}

document.getElementById("clearAllFilters").onclick = () => {
  ACTIVE_TAGS = [];
  savePreferences();
  renderUI();
};
