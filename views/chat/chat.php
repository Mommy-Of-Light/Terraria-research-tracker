<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card text-dark border-0" style="height: 70vh;" id="chat-card">
            <div class="card-header bg-primary text-white">
                <h4 id="chat-title"><?= htmlspecialchars($chat->chatName) ?></h4>
            </div>
            <div class="card-body overflow-auto" id="chat-messages" style="height: 400px;">
                <?php if (empty($messages)): ?>
                    <div class="text-center text-muted mt-3">No messages yet...</div>
                <?php else: ?>
                    <?php foreach ($messages as $msg):
                        $isMine = $msg['idUser'] == ($_SESSION['user']['idUser'] ?? 0);
                        $isChatAdmin = $_SESSION['user']['idUser'] == $chat->idUser;
                        $canModify = $isMine;
                        $ChatAdminDelete = $isChatAdmin;

                        $alignment = $isMine ? 'text-end' : 'text-start';
                        $bgClass = $isMine ? 'bg-primary text-white' : 'bg-secondary text-white';
                        ?>
                        <div class="mb-2 p-2 rounded <?= $alignment ?> <?= $bgClass ?>">
                            <?php if (($canModify ^ $ChatAdminDelete) || $ChatAdminDelete): ?>
                                <form method="POST" action="/chat/<?= $chat->idChat ?>/message/<?= $msg['idMessage'] ?>/delete"
                                    style="display:inline;">
                                    <button class="btn btn-sm btn-danger ms-2">Delete</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($canModify): ?>
                                <button class="btn btn-sm btn-warning ms-2"
                                    onclick="toggleEdit(<?= $msg['idMessage'] ?>)">Edit</button>
                            <?php endif; ?>
                            <div id="msg-view-<?= $msg['idMessage'] ?>" style="margin: 5px 0;">
                                <?php if ($isMine): ?>
                                    <div>
                                        <strong><?= htmlspecialchars($msg['UserName']) ?></strong>
                                        <img src="<?= $_SESSION['user']['profile_pic'] ?>" class="rounded-circle"
                                            style="width:30px;height:30px;">
                                    </div>
                                    <?= htmlspecialchars($msg['Content']) ?>
                                <?php else: ?>
                                    <div>
                                        <img src="<?= htmlspecialchars($msg['profile_pic'] ?? 'https://ui-avatars.com/api/?name=' . $msg['UserName']) ?>"
                                            class="rounded-circle me-2" style="width:30px;height:30px;">
                                        <strong><?= htmlspecialchars($msg['UserName']) ?></strong>
                                    </div>
                                    <?= htmlspecialchars($msg['Content']) ?>
                                <?php endif; ?>
                            </div>

                            <?php if ($canModify): ?>
                                <form id="msg-edit-<?= $msg['idMessage'] ?>" method="POST"
                                    action="/chat/<?= $chat->idChat ?>/message/<?= $msg['idMessage'] ?>/edit" class="d-none mt-2">
                                    <input type="hidden" name="msgOwner" value="<?= $msg['idUser'] ?>">
                                    <input name="content" class="form-control mb-2"
                                        value="<?= htmlspecialchars($msg['Content']) ?>">
                                    <button class="btn btn-sm btn-success" onclick="resumeReload()">Save</button>
                                    <button type="button" class="btn btn-sm btn-secondary"
                                        onclick="cancelEdit(<?= $msg['idMessage'] ?>)">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card-footer">
                <form id="chat-form" class="d-flex" method="POST" action="/chat/<?= $chat->idChat ?>/message">
                    <input type="text" name="message" id="chat-input" class="form-control me-2"
                        placeholder="Type your message..." required value="">
                    <button type="submit" class="btn btn-primary">Send</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let autoRefreshPaused = false;

    function toggleEdit(id) {
        const view = document.getElementById("msg-view-" + id);
        const edit = document.getElementById("msg-edit-" + id);
        if (view && edit) {
            const isOpening = edit.classList.contains('d-none');
            autoRefreshPaused = isOpening;
            edit.classList.toggle("d-none");
            view.classList.toggle("d-none");
        }
    }

    function cancelEdit(id) {
        toggleEdit(id);
        autoRefreshPaused = false;
    }

    function resumeReload() {
        autoRefreshPaused = false;
    }

    function renderMessages(messages, chatId, currentUserId, chatOwnerId) {
        const container = document.getElementById("chat-messages");
        container.innerHTML = '';

        if (messages.length === 0) {
            container.innerHTML = '<div class="text-center text-muted mt-3">No messages yet...</div>';
            return;
        }

        messages.forEach(msg => {
            const div = document.createElement('div');
            const isMine = msg.idUser === currentUserId;
            const isChatAdmin = currentUserId === chatOwnerId;
            const canModify = isMine;
            const ChatAdminDelete = isChatAdmin;

            div.className = `mb-2 p-2 rounded ${isMine ? 'text-end bg-primary text-white' : 'text-start bg-secondary text-white'}`;

            let inner = '';
            if ((canModify ^ ChatAdminDelete) || ChatAdminDelete) {
                inner += `<form method="POST" action="/chat/${chatId}/message/${msg.idMessage}/delete" style="display:inline;">
                        <button class="btn btn-sm btn-danger ms-2">Delete</button>
                      </form>`;
            } 
            if (canModify) {
                inner += `<button class="btn btn-sm btn-warning ms-2" onclick="toggleEdit(${msg.idMessage})">Edit</button>`;
            }

            inner += '<div id="msg-view-' + msg.idMessage + '" style="margin: 5px 0;">';
            if (isMine) {
                inner += `<div><strong>${msg.UserName}</strong> <img src="${msg.pfp || ("https://ui-avatars.com/api/?name=" + msg.UserName)}" class="rounded-circle" style="width:30px;height:30px;"></div>${msg.Content} `;
            } else {
                inner += `<div><img src="${msg.pfp || ("https://ui-avatars.com/api/?name=" + msg.UserName)}" class="rounded-circle me-2" style="width:30px;height:30px;">`;
                inner += `<strong>${msg.UserName}</strong></div> ${msg.Content}`;
            }
            inner += '</div>';

            if (canModify) {
                inner += `<form id="msg-edit-${msg.idMessage}" method="POST" action="/chat/${chatId}/message/${msg.idMessage}/edit" class="d-none mt-2">
                        <input type="hidden" name="msgOwner" value="${msg.idUser}">
                        <input name="content" class="form-control mb-2" value="${msg.Content}">
                        <button class="btn btn-sm btn-success" onclick="resumeReload()">Save</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="cancelEdit(${msg.idMessage})">Cancel</button>
                      </form>`;
            }

            div.innerHTML = inner;
            container.appendChild(div);
        });
    }

    setInterval(() => {
        if (!autoRefreshPaused) {
            fetch("/chat/<?= $chat->idChat ?>/messages")
                .then(res => res.json())
                // .then(data => console.log(data, <?= $chat->idChat ?>, <?= $_SESSION['user']['idUser'] ?? 0 ?>, <?= $chat->idUser ?>));
                .then(data => renderMessages(data, <?= $chat->idChat ?>, <?= $_SESSION['user']['idUser'] ?? 0 ?>, <?= $chat->idUser ?>));
        }
    }, 2000);

    setInterval(() => {
        fetch("/chat/<?= $chat->idChat ?>/exists")
            .then(res => res.json())
            .then(data => {
                if (!data.exists) {
                    alert("This chat has been deleted by its creator.");
                    window.location.href = "/chats";
                }
            });
    }, 3000);

    window.onload = () => {
        const container = document.getElementById("chat-messages");
        container.addEventListener('scroll', () => {
            localStorage.setItem(`chatScroll_<?= $chat->idChat ?>`, container.scrollTop);
        });

        fetch("/chat/<?= $chat->idChat ?>/messages")
            .then(res => res.json())
            .then(data => renderMessages(data, <?= $chat->idChat ?>, <?= $_SESSION['user']['idUser'] ?? 0 ?>, <?= $chat->idUser ?>));
    };
</script>