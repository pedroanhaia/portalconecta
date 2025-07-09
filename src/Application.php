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
		$middlewareQueue
			->add(new ErrorHandlerMiddleware(Configure::read('Error')))
			->add(new AssetMiddleware())
			->add(new RoutingMiddleware($this))
			// O BodyParserMiddleware vem ANTES do AuthenticationMiddleware
			->add(new BodyParserMiddleware())
			->add(new AuthenticationMiddleware($this))
			->add(new AuthorizationMiddleware($this))
			->add(new CsrfProtectionMiddleware([
				'httponly' => true,
			]));

		return $middlewareQueue;
	}


	public function services(ContainerInterface $container): void
	{
		// Registre serviços no container se necessário
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
	
	public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
	{
		return new AuthorizationService(new OrmResolver());
	}
}
