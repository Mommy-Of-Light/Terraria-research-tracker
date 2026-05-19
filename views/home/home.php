<script type="text/javascript">
    (function () {
        var container = document.querySelector('#_tcx-yuektbfs75');
        if (!container) {
            return;
        }

        function addToContainer(url, text) {
            const wrapper = document.createElement('div');
            wrapper.setAttribute('data-tcx-url', url);
            wrapper.innerText = text;
            container.appendChild(wrapper);
        }

        const fetch = window.fetch
        window.fetch = function () {
            return Promise.resolve(fetch.apply(window, arguments))
                .then(async response => {
                    if (response.ok) {
                        try {
                            const clone = response.clone();
                            const json = await clone.json();
                            addToContainer(clone.url, JSON.stringify(json));
                        } catch (err) { }
                    }
                    return response;
                });
        };

        var XHR = XMLHttpRequest.prototype;
        var send = XHR.send;
        var open = XHR.open;
        XHR.open = function (method, url) {
            this.url = url;
            return open.apply(this, arguments);
        };
        XHR.send = function () {
            this.addEventListener('load', function () {
                try {
                    const response = this.response;
                    if (response && response.length) {
                        const firstChar = response[0];
                        if (firstChar === '[' || firstChar === '{') {
                            addToContainer(this.url, response);
                        }
                    }
                } catch (err) {
                    // No-op.
                }
            });
            return send.apply(this, arguments);
        };
    })();
</script>

    <div class="container">
        <header>
            <!-- <h1>🌳 Journey Research Tracker</h1> -->
            <button id="themeToggle" class="btn hidden">🌓 Toggle Theme</button>
        </header>

        <div class="upload-group full-width">
            <div class="file-upload-wrapper">
                <label for="plrInput" class="custom-file-upload">
                    <span>📁 Upload player file</span>
                </label>
                <input type="file" id="plrInput" accept=".plr,.dcy">
            </div>
        </div>

        <div id="status" class="status-msg"></div>

        <div id="dashboard" class="hidden">
            <div class="player-info">
                <h2 id="playerName">Unknown player</h2>
            </div>
            <div class="stats-bar">
                <div class="stat">
                    <span class="label">Researchable Items</span>
                    <span id="statTotal" class="val">0</span>
                </div>
                <div class="stat">
                    <span class="label">Completed</span>
                    <span id="statDone" class="val">0</span>
                </div>
                <div class="stat">
                    <span class="label">In Progress</span>
                    <span id="statProgressing" class="val">0</span>
                </div>
                <div class="stat">
                    <span class="label">Completion %</span>
                    <span id="statPercent" class="val">0%</span>
                </div>
            </div>

            <div id="activeFiltersBar" class="active-filters-container hidden">
                <span class="filter-label">Active Filters:</span>
                <div id="activeFiltersList" class="filter-pills"></div>
                <button id="clearAllFilters" class="clear-all-link">Clear All</button>
            </div>


            <div class="toolbar compact-toolbar" style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px; flex-wrap: wrap;">
                <input type="text" id="search" placeholder="Search item name or id..." style="padding: 4px 8px; font-size: 0.95em; height: 32px; min-width: 190px; max-width: 190px;">
                <select id="pageSize" class="filter-select" style="padding: 4px 8px; font-size: 0.95em; height: 32px;">
                    <option value="8">8 per page</option>
                    <option value="16" selected>16 per page</option>
                    <option value="32">32 per page</option>
                    <option value="64">64 per page</option>
                    <option value="100">100 per page</option>
                    <option value="all">All per page</option>
                </select>
                <div class="sort-group" style="gap: 4px;">
                    <button class="btn active" data-sort="id" style="padding: 4px 8px; font-size: 0.95em; height: 32px;">ID</button>
                    <button class="btn" data-sort="name" style="padding: 4px 8px; font-size: 0.95em; height: 32px;">A-Z</button>
                </div>
                <div class="sort-group" id="statusGroup" style="gap: 4px;">
                    <button class="btn" data-status="complete" style="padding: 4px 8px; font-size: 0.95em; height: 32px;">Complete</button>
                    <button class="btn" data-status="researching" style="padding: 4px 8px; font-size: 0.95em; height: 32px;">Researching</button>
                    <button class="btn" data-status="not_started" style="padding: 4px 8px; font-size: 0.95em; height: 32px;">Not Started</button>
                    <button class="btn" data-status="unobtainable" style="padding: 4px 8px; font-size: 0.95em; height: 32px;">Unobtainable</button>
                </div>
                <div class="sort-group" id="phaseGroup" style="gap: 4px;">
                    <button class="btn" data-status=1 style="padding: 4px 8px; font-size: 0.95em; height: 32px;">Pre-Hardmode</button>
                    <button class="btn" data-status=2 style="padding: 4px 8px; font-size: 0.95em; height: 32px;">Hardmode</button>
                </div>
                <button id="viewToggle" class="btn" style="padding: 4px 10px; font-size: 0.95em; height: 32px;">Grid View</button>
                <button id="tagFilterBtn" class="btn" style="padding: 4px 10px; font-size: 0.95em; height: 32px;">Filter by Tags</button>
                                <label class="filter-checkbox" style="margin-left:8px;user-select:none;">
                                    <input type="checkbox" id="toggleAllIdsCheckbox" style="margin-right:6px;">
                                    Show All IDs (incl. non-existent)
                                </label>
            </div>

            <div id="tagModal" class="modal hidden">
                <div class="modal-content">
                    <header>
                        <h2>Filter by Categories</h2>
                        <button id="closeModal" class="btn">✕</button>
                    </header>
                    <div id="tagContainer"></div>
                    <div class="modal-footer">
                        <button id="clearTags" class="btn">Clear All</button>
                        <button onclick="document.getElementById('tagModal').classList.add('hidden')" class="btn">
                            Done
                        </button>
                    </div>
                </div>
            </div>


            <div id="paginationTop" class="pagination-container"></div>

            <div id="listView" class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th style="max-width: 15px;">Icon</th>
                            <th>Item Name</th>
                            <th>Research Progress</th>
                            <th>Status</th>
                            <th>Check</th>
                        </tr>
                    </thead>
                    <tbody id="itemBody" class="item-list"></tbody>
                </table>
            </div>
            <div id="gridView" class="grid-scroll-wrapper" style="display: none;">
                <ul id="itemGrid" class="item-list grid"></ul>
            </div>
            <button class="btn" id="restoreAllFilters">Clear All Filters</button>
            <button class="btn" id="clearAllFilters">Clear All Research</button>
        </div>
    </div>

    <script src="database.js"></script>
    <script src="script.js"></script>