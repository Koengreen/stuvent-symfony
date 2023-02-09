<?php
// config/routes.php
use App\Controller\HomeController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('share', '/share/{token}')
        ->controller([DefaultController::class, 'share'])
        ->requirements([
            'token' => '.+',
        ])
    ;

};

