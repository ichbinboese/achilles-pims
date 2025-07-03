<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LdapApiAuthenticator extends AbstractAuthenticator
{
    private Ldap $ldap;
    private string $baseDn;
    private string $searchDn;
    private string $searchPassword;
    private string $uidKey;

    public function __construct(
        LoggerInterface $logger,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->ldap = Ldap::create('ext_ldap', [
            'host' => $_ENV['LDAP_BASE_ADDRESS'],
            'port' => 389,
            'encryption' => 'none',
            'options' => [
                'protocol_version' => 3,
                'referrals' => false,
            ],
        ]);
        $this->baseDn = $_ENV['LDAP_BASE_DN'];
        $this->searchDn = $_ENV['LDAP_SEARCH_DN'];
        $this->searchPassword = $_ENV['LDAP_SEARCH_PASSWORD'];
        $this->uidKey = $_ENV['LDAP_UID_KEY'] ?? 'sAMAccountName';
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function supports(Request $request): ?bool
    {
        return $request->getPathInfo() === '/api/login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$username || !$password) {
            throw new AuthenticationException('Fehlende Zugangsdaten.');
        }

        try {
            $this->logger->info('Binde mit: ' . $this->searchDn . ' gegen ' . $_ENV['LDAP_BASE_ADDRESS']);
            $this->ldap->bind($this->searchDn, $this->searchPassword);

            $query = $this->ldap->query($this->baseDn, sprintf('(%s=%s)', $this->uidKey, $username));
            $results = $query->execute();

            if (count($results) === 0) {
                throw new AuthenticationException('Benutzer nicht gefunden.');
            }

            $userDn = $results[0]->getDn();

            $this->ldap->bind($userDn, $password);
        } catch (\Exception $e) {
            $this->logger->error('LDAP error: ' . $e->getMessage());
            throw new AuthenticationException('LDAP-Anmeldung fehlgeschlagen: ' . $e->getMessage());
        }

        return new SelfValidatingPassport(
            new UserBadge($username)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?JsonResponse
    {
        $user = $token->getUser();

        return new JsonResponse([
            'status' => 'ok',
            'username' => $user instanceof UserInterface ? $user->getUserIdentifier() : null,
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        return new JsonResponse([
            'status' => 'error',
            'message' => $exception->getMessage(),
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
