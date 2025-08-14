<?php
declare(strict_types=1);

namespace App;

use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Router;

use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;

use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Policy\OrmResolver;

use Cake\Http\Middleware\CorsMiddleware;
use App\Middleware\CorsMiddleware as MiddlewareCorsMiddleware;


use Psr\Http\Message\ServerRequestInterface;

class Application extends BaseApplication implements AuthenticationServiceProviderInterface, AuthorizationServiceProviderInterface
{
	public function bootstrap(): void
	{
		parent::bootstrap();

		$this->addPlugin('Authentication');
		$this->addPlugin('Authorization');

		if (PHP_SAPI === 'cli') {
			$this->bootstrapCli();
		} else {
			FactoryLocator::add(
				'Table',
				(new TableLocator())->allowFallbackClass(false)
			);
		}

		if (Configure::read('debug')) {
			$this->addPlugin('DebugKit');
		}
	}

	protected function bootstrapCli(): void
	{
		$this->addOptionalPlugin('Bake');
		$this->addPlugin('Migrations');
	}

	public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
	{
		$csrf = new CsrfProtectionMiddleware([
            'httponly' => true,
            //'disabled' => true,
        ]);
		

        
        // Token check will be skipped when callback returns `true`.
        $csrf->skipCheckCallback(function ($request) {
            // Skip token check for API URLs.
            if ($request->is(['post', 'json'])) {
                return true;
            }
        });
		$middlewareQueue
			// Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // Add routing middleware.
            // If you have a large number of routes connected, turning on routes
            // caching in production could improve performance.
            // See https://github.com/CakeDC/cakephp-cached-routing
            ->add(new RoutingMiddleware($this))

            // Parse various types of encoded request bodies so that they are
            // available as array through $request->getData()
            // https://book.cakephp.org/4/en/controllers/middleware.html#body-parser-middleware
            ->add(new BodyParserMiddleware())

            ->add(new MiddlewareCorsMiddleware([
                'origin' => ['*'], // Configurar as origens permitidas conforme necessário
                'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], // Métodos permitidos
                'headers' => ['Authorization', 'Content-Type'], // Cabeçalhos permitidos
                'credentials' => false, // Permitir credenciais (cookies)
                'exposeHeaders' => [], // Cabeçalhos expostos
                'maxAge' => 3600, // Tempo de vida da preflight request (em segundos)
            ]))
            ->add(new AuthenticationMiddleware($this))
            ->add(new AuthorizationMiddleware($this))
            ->add($csrf)
            ;

		return $middlewareQueue;
	}


	public function services(ContainerInterface $container): void
	{
		// Registre serviços no container se necessário
	}

	public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
    {
        $resolver = new OrmResolver();

        return new AuthorizationService($resolver);
    }

	public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
	{
		$url = '/users/login';

		$authenticationService = new AuthenticationService([
			'unauthenticatedRedirect' => Router::url($url),
			'queryParam' => 'redirect',
		]);

		$authenticationService->loadIdentifier('Authentication.Password', [
			'fields' => [
				'username' => 'email',
				'password' => 'password'
			]
		]);

		// Load the authenticators, you want session first
		$authenticationService->loadAuthenticator('Authentication.Session');
		// Configure form data check to pick email and password
		$authenticationService->loadAuthenticator('Authentication.Form', [
			'fields' => [
				'username' => 'email',
				'password' => 'password'
			],
			'loginUrl' => [Router::url('/'), Router::url($url)],
		]);

		return $authenticationService;
	}
	
}
