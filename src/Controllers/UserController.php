<?php

declare(strict_types=1);

namespace Bastien\TerrariaWikiCommunity\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Bastien\TerrariaWikiCommunity\Model\User;

/**
 * Class UserController
 * 
 * Handles user profile related actions such as viewing profile,
 * adding/deleting profile pictures, and deleting user accounts.
 */
class UserController extends BaseController
{
    /**
     * Displays the user's profile page.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @return ResponseInterface The rendered profile page response.
     */
    public function showProfile(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (empty($_SESSION['user'])) {
            session_destroy();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $userModel = new User();
        $user = $userModel->findById($_SESSION['user']['idUser']);

        $errorMsg = null;
        $uploadsDir = __DIR__ . '/../../public/uploads/';

        if (!is_dir($uploadsDir))
            mkdir($uploadsDir, 0755, true);

        $filePath = __DIR__ . '/../../public' . $user->profilePic;
        if (!file_exists($filePath) && !str_starts_with($user->profilePic, 'https://ui-avatars.com/api/?name=')) {
            $defaultPic = 'https://ui-avatars.com/api/?name=' . $user->userName;
            $userModel->updateProfilePic($user->idUser, $defaultPic);
            $user->profilePic = $defaultPic;
            $_SESSION['user']['profile_pic'] = $defaultPic;
        }

        return $this->view->render($response, 'user/profile.php', [
            'user' => $user,
            'errorMsg' => $errorMsg
        ]);
    }

    /**
     * Displays the player's files.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @param array $args The route arguments.
     * @return ResponseInterface The rendered player files page response.
     */
    public function displayPlayerFiles(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (empty($_SESSION['user'])) {
            session_destroy();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $baseDir = __DIR__ . '/../../public/users/';
        $userPrefix = $_SESSION['user']['userName'] . '-';

        $userDirs = glob($baseDir . $userPrefix . '*');

        if (empty($userDirs)) {
            $newDir = $baseDir . $userPrefix . uniqid();
            mkdir($newDir, 0755, true);
            $userDirs = [$newDir];
        }

        $userFiles = [];

        foreach ($userDirs as $dir) {
            $files = glob($dir . '/*');
            $files = array_filter($files, 'is_file');

            foreach ($files as $file) {
                $relativePath = substr($file, strlen($baseDir));
                $userFiles[] = $relativePath;
            }
        }

        $userFiles = array_unique($userFiles);

        $dbFiles = User::getAllPlayerFiles($_SESSION['user']['idUser']) ?? [];

        if (empty($dbFiles)) {
            User::savePlayerFiles($_SESSION['user']['idUser'], $userFiles);
        }

        $filesToAdd = array_diff($userFiles, $dbFiles);
        if (!empty($filesToAdd)) {
            User::savePlayerFiles($_SESSION['user']['idUser'], $filesToAdd);
        }

        $filesToDelete = array_diff($dbFiles, $userFiles);
        if (!empty($filesToDelete)) {
            User::deletePlayerFiles($_SESSION['user']['idUser'], $filesToDelete);
        }

        return $this->view->render($response, 'user/playerFiles.php', [
            'playerFiles' => $userFiles
        ]);
    }

    /**
     * Handles uploading a new player file.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @param array $args The route arguments.
     * @return ResponseInterface The response after processing the file upload.
     */
    public function uploadPlayerFile(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (empty($_SESSION['user'])) {
            session_destroy();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $uploadedFiles = $request->getUploadedFiles();

        if (empty($uploadedFiles['player_file'])) {
            return $response->withStatus(400);
        }

        $file = $uploadedFiles['player_file'];
        if ($file->getError() === UPLOAD_ERR_OK) {
            $baseDir = __DIR__ . '/../../public/users/';
            $userPrefix = $_SESSION['user']['userName'] . '-';

            $userDirs = glob($baseDir . $userPrefix . '*');

            if (empty($userDirs)) {
                $newDir = $baseDir . $userPrefix . uniqid();
                mkdir($newDir, 0755, true);
                $userDirs = [$newDir];
            }

            $dir = $userDirs[0];

            $originalName = $file->getClientFilename();
            $targetPath = $dir . '/' . $originalName;

                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
                $targetPath = $dir . '/' . $nameWithoutExt . '_' . time() . '.' . $ext;

            $file->moveTo($targetPath);

            $relativePath = substr($targetPath, strlen($baseDir));
            User::savePlayerFiles($_SESSION['user']['idUser'], [$relativePath]);

            return $response->withHeader('Location', '/user/files')->withStatus(302);
        }

        return $response->withStatus(400);
    }

    /**
     * Handles deleting a player file.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @param array $args The route arguments.
     * @return ResponseInterface The response after processing the file deletion.
     */
    public function deletePlayerFile(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (empty($_SESSION['user'])) {
            session_destroy();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $postData = $request->getParsedBody();
        $fileToDelete = $postData['file'] ?? '';

        if (!$fileToDelete) {
            return $response->withStatus(400);
        }

        $baseDir = __DIR__ . '/../../public/users/';
        $filePath = $baseDir . $fileToDelete;

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Remove from DB
        User::deletePlayerFiles($_SESSION['user']['idUser'], [$fileToDelete]);

        return $response->withHeader('Location', '/user/files')->withStatus(302);
    }

    /**
     * Handles adding or updating the user's profile picture.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @return ResponseInterface The response after processing the profile picture update.
     */
    public function addPFP(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (empty($_SESSION['user'])) {
            session_destroy();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $userModel = new User();
        $user = $userModel->findById($_SESSION['user']['idUser']);

        $errorMsg = null;

        $postData = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();
        $uploadsDir = __DIR__ . '/../../public/uploads/';

        if (!is_dir($uploadsDir))
            mkdir($uploadsDir, 0755, true);

        if ($request->getMethod() === 'POST') {
            $profilePic = null;

            $source = $postData['pic_source'] ?? 'url';

            if ($source === 'file' && !empty($uploadedFiles['profile_pic_file'])) {
                $this->deleteAllProfilePics($user->userName, $uploadsDir);
                $file = $uploadedFiles['profile_pic_file'];
                if ($file->getError() === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (in_array($ext, $allowed)) {
                        $filename = strtolower(preg_replace('/[^a-z0-9]/', '_', $user->userName)) . '-profile-picture.' . $ext;
                        $filePath = $uploadsDir . $filename;
                        $file->moveTo($filePath);

                        if ($ext !== 'gif')
                            $this->resizeImage($filePath, 200, 200);
                        $profilePic = '/uploads/' . $filename;
                    } else {
                        $errorMsg = "File type not allowed. Use jpg, png, gif, or webp.";
                    }
                } else {
                    $errorMsg = "Upload failed with error code " . $file->getError();
                }
            }

            if ($source === 'url' && empty($profilePic) && !empty($postData['profile_pic_url'])) {
                $this->deleteAllProfilePics($user->userName, $uploadsDir);
                $url = trim($postData['profile_pic_url'] ?? '');
                if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
                    $imageData = @file_get_contents($url);
                    if ($imageData) {
                        $finfo = finfo_open();
                        $mimeType = finfo_buffer($finfo, $imageData, FILEINFO_MIME_TYPE);
                        finfo_close($finfo);

                        $mimeMap = [
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/gif' => 'gif',
                            'image/webp' => 'webp',
                        ];

                        if (!isset($mimeMap[$mimeType])) {
                            $errorMsg = "URL must point to a valid image type (jpg, png, gif, webp).";
                        } else {
                            $ext = $mimeMap[$mimeType];
                            $filename = strtolower(preg_replace('/[^a-z0-9]/', '_', $user->userName)) . '-profile-picture.' . $ext;
                            file_put_contents($uploadsDir . $filename, $imageData);
                            if ($ext !== 'gif')
                                $this->resizeImage($uploadsDir . $filename, 200, 200);
                            $profilePic = '/uploads/' . $filename;
                        }
                    } else {
                        $errorMsg = "Cannot download image from URL.";
                    }
                }
            }

            if ($profilePic) {
                $userModel->updateProfilePic($user->idUser, $profilePic);
                $user->profilePic = $profilePic;

                $_SESSION['user']['profile_pic'] = $profilePic;
            } else {
                $errorMsg = "Please provide a valid URL or upload a file.";
            }
        }

        if ($errorMsg) {
            return $this->view->render($response, 'user/profile.php', [
                'user' => $user,
                'errorMsg' => $errorMsg
            ]);
        }

        return $response
            ->withHeader('Location', '/profile')
            ->withStatus(302);
    }

    /**
     * Handles deleting the user's profile picture.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @return ResponseInterface The response after processing the profile picture deletion.
     */
    public function deletePFP(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (empty($_SESSION['user'])) {
            session_destroy();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $userModel = new User();
        $user = $userModel->findById($_SESSION['user']['idUser']);

        $postData = $request->getParsedBody();
        $uploadsDir = __DIR__ . '/../../public/uploads/';

        if (!is_dir($uploadsDir))
            mkdir($uploadsDir, 0755, true);

        $this->deleteAllProfilePics($user->userName, $uploadsDir);

        if (!empty($postData['delete_image'])) {
            if (!empty($user->profilePic) && str_starts_with($user->profilePic, '/uploads/')) {
                $filePath = __DIR__ . '/../../public' . $user->profilePic;
                if (file_exists($filePath))
                    @unlink($filePath);
            }

            $defaultPic = 'https://ui-avatars.com/api/?name=' . $user->userName;
            $userModel->updateProfilePic($user->idUser, $defaultPic);
            $user->profilePic = $defaultPic;
            $_SESSION['user']['profile_pic'] = $defaultPic;
        }

        return $response
            ->withHeader('Location', '/profile')
            ->withStatus(302);
    }

    /**
     * Handles deleting the user's account.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @param ResponseInterface $response The HTTP response.
     * @return ResponseInterface The response after processing the account deletion.
     */
    public function deleteAccount(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (empty($_SESSION['user'])) {
            session_destroy();
            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        $userId = $_SESSION['user']['idUser'] ?? null;

        $profilePic = $_SESSION['user']['profile_pic'] ?? null;

        $uploadsDir = __DIR__ . '/../../public/uploads/';

        if (!is_dir($uploadsDir))
            mkdir($uploadsDir, 0755, true);

        if ($userId !== null) {
            $userModel = new User();
            $user = $userModel->findById($userId);
            $this->deleteAllProfilePics($user->userName, $uploadsDir);
        }

        if ($userId === null) {
            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        if ($profilePic && str_starts_with($profilePic, '/uploads/')) {
            $filePath = __DIR__ . '/../../public' . $profilePic;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        $userModel = new User();
        $userModel->deleteUser($userId);

        session_destroy();

        return $response
            ->withHeader('Location', '/register')
            ->withStatus(302);
    }

    /**
     * Resizes an image to the specified width and height.
     * 
     * @param string $filePath The path to the image file.
     * @param int $width The desired width.
     * @param int $height The desired height.
     * @return void
     */
    private function resizeImage(string $filePath, int $width, int $height): void
    {
        [$origWidth, $origHeight, $type] = getimagesize($filePath);

        switch ($type) {
            case IMAGETYPE_JPEG:
                $srcImage = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $srcImage = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_GIF:
                $srcImage = imagecreatefromgif($filePath);
                break;
            case IMAGETYPE_WEBP:
                $srcImage = imagecreatefromwebp($filePath);
                break;
            default:
                return;
        }

        $dstImage = imagecreatetruecolor($width, $height);

        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF])) {
            imagecolortransparent($dstImage, imagecolorallocatealpha($dstImage, 0, 0, 0, 127));
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
        }

        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);

        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($dstImage, $filePath, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($dstImage, $filePath);
                break;
            case IMAGETYPE_GIF:
                imagegif($dstImage, $filePath);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($dstImage, $filePath);
                break;
        }

        imagedestroy($srcImage);
        imagedestroy($dstImage);
    }

    /**
     * Deletes all profile picture files for a user in the uploads directory.
     *
     * @param string $userName
     * @param string $uploadsDir
     * @return void
     */
    private function deleteAllProfilePics(string $userName, string $uploadsDir): void
    {
        $base = strtolower(preg_replace('/[^a-z0-9]/', '_', $userName)) . '-profile-picture.';
        foreach (glob($uploadsDir . $base . '*') as $file) {
            @unlink($file);
        }
    }
}