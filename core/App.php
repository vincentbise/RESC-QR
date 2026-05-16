<?php

class App {
    protected $controller = 'AuthController';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        $controllerName = !empty($url[0]) ? ucfirst($url[0]) . 'Controller' : $this->controller;

        $controllerMap = [
            'AuthController'          => 'AuthController',
            'DashboardController'     => 'DashboardController',
            'StudentController'       => 'StudentController',
            'QrController'            => 'QRController',
            'QRController'            => 'QRController',
            'ScanController'          => 'ScanController',
            'EventController'        => 'EventController',
            'ReportController'        => 'ReportController',
            'StudentviewController'   => 'StudentViewController',
            'StudentViewController'   => 'StudentViewController',
            'AttendanceController'    => 'AttendanceController',
            'ApiController'           => 'ApiController',
        ];

        $urlSegment = !empty($url[0]) ? $url[0] : 'auth';
        $possibleController = ucfirst(strtolower($urlSegment)) . 'Controller';

        if (isset($controllerMap[$possibleController])) {
            $controllerName = $controllerMap[$possibleController];
        } elseif ($urlSegment === 'api' && !empty($url[1])) {
            $possibleController = ucfirst(strtolower($url[1])) . 'Controller';
            if (isset($controllerMap[$possibleController])) {
                $controllerName = $controllerMap[$possibleController];
                array_shift($url);
            }
        } else {
            $controllerName = $this->controller;
        }

        $controllerFile = ROOT_PATH . '/app/controllers/' . $controllerName . '.php';

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $this->controller = new $controllerName();
        } else {
            require_once ROOT_PATH . '/app/controllers/AuthController.php';
            $this->controller = new AuthController();
        }

        array_shift($url);

        if (!empty($url[0]) && method_exists($this->controller, $url[0])) {
            $this->method = $url[0];
            array_shift($url);
        }

        $this->params = $url ? array_values($url) : [];

        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
