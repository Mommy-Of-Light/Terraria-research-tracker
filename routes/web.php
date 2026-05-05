<?php
use Bastien\TerrariaWikiCommunity\Controllers\HomeController;
use Bastien\TerrariaWikiCommunity\Controllers\ChatController;
use Bastien\TerrariaWikiCommunity\Controllers\AuthController;
use Bastien\TerrariaWikiCommunity\Controllers\UserController;

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