<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Available Chats</h2>
    <?php if (!empty($_SESSION['user'])): ?>
        <a href="/chats/new" class="btn btn-success">+ New Chat</a>
    <?php endif; ?>
</div>

<div id="chat-list" class="row g-4">
    <?php foreach ($chats as $chat): ?>
        <div class="col-md-4">
            <div class="card shadow-sm p-3 h-100">
                <h4><?= htmlspecialchars($chat->chatName) ?></h4>
                <?php
                    $user = array_filter($users, fn($u) => $u->idUser === $chat->idUser);
                    $user = array_values($user)[0];
                ?>
                <p>Created by <?= htmlspecialchars($user->userName) ?></p>

                <div class="d-flex justify-content-between mt-auto">
                    <a href="/chat/<?= $chat->idChat ?>" class="btn btn-primary">Enter Chat</a>

                    <?php if (!empty($_SESSION['user']) && $_SESSION['user']['idUser'] == $chat->idUser): ?>
                        <form method="POST" action="/chats/<?= $chat->idChat ?>/delete"
                            onsubmit="return confirm('Are you sure you want to delete this chat?');">
                            <button class="btn btn-danger">Delete</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    const currentUserId = <?= !empty($_SESSION['user']['idUser']) ? (int) $_SESSION['user']['idUser'] : 0 ?>;
    let pauseRefresh = false;

    function refreshChats() {
        if (pauseRefresh) return;

        fetch("/chats/json")
            .then(res => res.json())
            .then(chats => {
                const chatList = document.getElementById("chat-list");
                chatList.innerHTML = ""; // Clear existing cards

                chats.forEach(chat => {
                    // Create column
                    const col = document.createElement("div");
                    col.classList.add("col-md-4");

                    // Create card
                    const card = document.createElement("div");
                    card.classList.add("card", "shadow-sm", "p-3", "h-100");

                    // Chat title
                    const title = document.createElement("h4");
                    title.textContent = chat.chatName;

                    // Chat creator
                    const creator = document.createElement("p");
                    creator.textContent = `Created by ${chat.UserName}`;

                    // Buttons container
                    const btnContainer = document.createElement("div");
                    btnContainer.classList.add("d-flex", "justify-content-between", "mt-auto");

                    // Enter chat button
                    const enterBtn = document.createElement("a");
                    enterBtn.href = `/chat/${chat.idChat}`;
                    enterBtn.classList.add("btn", "btn-primary");
                    enterBtn.textContent = "Enter Chat";
                    btnContainer.appendChild(enterBtn);

                    // Delete button if current user is owner
                    if (chat.idUser == currentUserId) {
                        const deleteForm = document.createElement("form");
                        deleteForm.method = "POST";
                        deleteForm.action = `/chats/${chat.idChat}/delete`;
                        deleteForm.onsubmit = () => confirm("Are you sure you want to delete this chat?");

                        const deleteBtn = document.createElement("button");
                        deleteBtn.type = "submit";
                        deleteBtn.classList.add("btn", "btn-danger");
                        deleteBtn.textContent = "Delete";

                        deleteForm.appendChild(deleteBtn);
                        btnContainer.appendChild(deleteForm);
                    }

                    // Assemble card
                    card.appendChild(title);
                    card.appendChild(creator);
                    card.appendChild(btnContainer);

                    col.appendChild(card);
                    chatList.appendChild(col);
                });
            })
            .catch(err => console.error("Error fetching chats:", err));
    }

    // Refresh every 3 seconds
    setInterval(refreshChats, 3000);
    refreshChats();
</script>