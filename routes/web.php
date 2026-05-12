<?php
use Bastien\TerrariaWikiCommunity\Controllers\HomeController;
use Bastien\TerrariaWikiCommunity\Controllers\ChatController;
use Bastien\TerrariaWikiCommunity\Controllers\AuthController;
use Bastien\TerrariaWikiCommunity\Controllers\UserController;

// Serve static icons directly (workaround for container/Apache path issues)
$app->get('/icons/{file:.+}', function ($request, $response, $args) {
	$fileName = $args['file'];
	$filePath = __DIR__ . '/../public/icons/' . $fileName;
	if (!is_file($filePath) || !is_readable($filePath)) {
		return $response->withStatus(404);
	}
	$mime = @mime_content_type($filePath) ?: 'application/octet-stream';
	$body = file_get_contents($filePath);
	$response->getBody()->write($body);
	return $response->withHeader('Content-Type', $mime);
});

// Always-available assets route: serve icons via the Slim app under /assets/icons/
// This avoids relying on Apache's file mapping and works even when DocumentRoot
// mapping causes unexpected 404s.
$app->get('/assets/icons/{file:.+}', function ($request, $response, $args) {
	$fileName = $args['file'];
	$filePath = __DIR__ . '/../public/icons/' . $fileName;
	if (!is_file($filePath) || !is_readable($filePath)) {
		return $response->withStatus(404);
	}
	$mime = @mime_content_type($filePath) ?: 'application/octet-stream';
	$stream = fopen($filePath, 'rb');
	if ($stream === false) return $response->withStatus(404);
	$response = $response->withHeader('Content-Type', $mime);
	$response->getBody()->write(stream_get_contents($stream));
	fclose($stream);
	return $response;
});

// Main entry point
$app->get('/', [HomeController::class, 'index']);

// Profile routes
$app->get('/profile', [UserController::class, 'showProfile']);
$app->post('/profile/pfp/add', [UserController::class, 'addPFP']);
$app->post('/profile/pfp/delete', [UserController::class, 'deletePFP']);
$app->post('/profile/delete', [UserController::class, 'deleteAccount']);

// Authentication routes
$app->get('/login', [AuthController::class, 'showLogin']);
$app->post('/login', [AuthController::class, 'login']);
$app->get('/register', [AuthController::class, 'showRegister']);
$app->post('/register', [AuthController::class, 'register']);
$app->get('/logout', [AuthController::class, 'logout']);

// Chat routes
$app->get('/chats', [ChatController::class, 'index']);
$app->get('/chats/json', [ChatController::class, 'fetchChats']);
$app->get('/chats/new', [ChatController::class, 'showNewChatForm']);
$app->post('/chats/new', [ChatController::class, 'createChat']);
$app->get('/chat/{id}', [ChatController::class, 'show']);
$app->get('/chat/{id}/exists', [ChatController::class, 'chatExists']);
$app->post('/chats/{id}/delete', [ChatController::class, 'deleteChat']);
$app->get('/chat/{id}/messages', [ChatController::class, 'fetchMessages']);
$app->post('/chat/{id}/message', [ChatController::class, 'sendMessage']);
$app->post('/chat/{id}/message/{msgId}/delete', [ChatController::class, 'deleteMessage']);
$app->post('/chat/{id}/message/{msgId}/edit', [ChatController::class, 'editMessage']);

// User file routes
$app->get('/user/files', [UserController::class, 'displayPlayerFiles']);
$app->post('/user/files/upload', [UserController::class, 'uploadPlayerFile']);
$app->post('/user/files/delete', [UserController::class, 'deletePlayerFile']);