<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-4 text-center">Create New Chat</h2>
        <form method="POST" action="/chats/new" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label class="form-label">Chat Name</label>
                <input type="text" name="chatName" class="form-control" required autofocus>
            </div>
            <button class="btn btn-success w-100" type="submit">Create Chat</button>
            <div class="text-center mt-3">
                <a href="/chats">Back to Chats</a>
            </div>
        </form>
    </div>
</div>
