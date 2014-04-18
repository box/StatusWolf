<?php
/**
 * LdapListener
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 2/27/14
 *
 */

namespace StatusWolf\Security\Firewall;

use StatusWolf\Security\Authentication\Token\SWChainAuthToken;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

class SWChainAuthListener extends AbstractAuthenticationListener {

    private $_csrf_provider;
    protected $logger;

    public function __construct(
        SecurityContextInterface $security_context,
        AuthenticationManagerInterface $authentication_manager,
        SessionAuthenticationStrategyInterface $session_strategy,
        HttpUtils $http_utils,
        $provider_key,
        AuthenticationSuccessHandlerInterface $success_handler,
        AuthenticationFailureHandlerInterface $failure_handler,
        array $options = array(),
        LoggerInterface $logger = null,
        EventDispatcherInterface $event_dispatcher = null,
        CsrfProviderInterface $csrf_provider = null
    ) {

        parent::__construct(
            $security_context,
            $authentication_manager,
            $session_strategy,
            $http_utils,
            $provider_key,
            $success_handler,
            $failure_handler,
            array_merge(array(
                'username_parameter' => '_username',
                'password_parameter' => '_password',
                'csrf_parameter' => '_csrf_token',
                'intention' => 'authenticate',
            ), $options),
            $logger,
            $event_dispatcher
        );

        $this->_csrf_provider = $csrf_provider;
        $this->logger = $logger;
    }

    protected function requiresAuthentication(Request $request) {
        return parent::requiresAuthentication($request);
    }

    protected function attemptAuthentication(Request $request) {
        if ($this->_csrf_provider !== null) {
            $csrf_token = $request->get($this->options['csrf_parameter'], null, true);
        }

        if (!$this->_csrf_provider->isCsrfTokenValid($this->options['intention'], $csrf_token)) {
            throw new InvalidCsrfTokenException('Invalid CSRF Token');
        }

        if ($this->options['post_only']) {
            $username = trim($request->request->get($this->options['username_parameter'], null, true));
            $password = $request->request->get($this->options['password_parameter'], null, true);
        } else {
            $username = trim($request->get($this->options['username_parameter'], null, true));
            $password = $request->get($this->options['password_parameter'], null, true);
        }

        $request->getSession()->set(SecurityContextInterface::LAST_USERNAME, $username);

        return $this->authenticationManager->authenticate(new SWChainAuthToken($username, $password, $this->providerKey));
    }

}
