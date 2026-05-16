<?php

class Controller {

    protected function model($model) {
        $modelFile = ROOT_PATH . '/app/models/' . $model . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        }
        die('Model not found: ' . $model);
    }

    protected function view($view, $data = []) {
        $viewFile = ROOT_PATH . '/app/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            extract($data);
            require_once $viewFile;
        } else {
            die('View not found: ' . $view);
        }
    }

    protected function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function getJsonInput() {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?? [];
    }

    protected function setFlash($type, $message) {
        $_SESSION['flash'] = [
            'type'    => $type,
            'message' => $message
        ];
    }

    protected function requireAuth($roles = []) {
        if (!isLoggedIn()) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            $this->redirect('/auth/login');
        }
        if (!empty($roles) && !in_array($_SESSION['user_role'], (array)$roles)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Forbidden'], 403);
            }
            $this->redirect('/auth/login');
        }
    }

    protected function validateCSRF() {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!validateCSRFToken($token)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
            }
            $this->setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('/');
        }
    }
}
