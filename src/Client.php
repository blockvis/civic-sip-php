<?php

namespace Blockvis\Civic\Sip;

use DateTimeImmutable;
use GuzzleHttp\ClientInterface as HttpClient;
use GuzzleHttp\Psr7\Request;
use Lcobucci\Jose\Parsing\Parser;
use Lcobucci\JWT\Signer\Ecdsa;
use Lcobucci\JWT\Signer\Hmac;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token\Builder as TokenBuilder;
use Lcobucci\JWT\Token\Parser as TokenParser;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Primitives\GeneratorPoint;
use Mdanter\Ecc\Serializer\PrivateKey\DerPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PrivateKey\PemPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\PemPublicKeySerializer;
use Ramsey\Uuid\Uuid;

class Client
{
    const SIP_PUB_HEX = '049a45998638cfb3c4b211d72030d9ae8329a242db63bfb0076a54e7647370a8ac5708b57af6065805d5a6be72332620932dbb35e8d318fce18e7c980a0eb26aa1';

    /**
     * @var string
     */
    private $baseUri = 'https://api.civic.com/sip';

    /**
     * @var AppConfig
     */
    private $config;

    /**
     * @var GeneratorPoint
     */
    private $eccGenerator;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * Client constructor.
     *
     * @param AppConfig $config
     * @param HttpClient $httpClient
     */
    public function __construct(AppConfig $config, HttpClient $httpClient)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->eccGenerator = EccFactory::getSecgCurves()->generator256r1();
        $this->parser = new Parser;
    }

    /**
     * @param string $jwtToken
     * @return UserData
     */
    public function exchangeToken(string $jwtToken): UserData
    {
        $requestMethod = 'POST';
        $path = 'scopeRequest/authCode';
        $requestBody = $this->parser->jsonEncode(['authToken' => $jwtToken]);

        $request = new Request(
            $requestMethod,
            sprintf('%s/%s/%s', $this->baseUri, $this->config->env(), $path), [
                'Content-Length' => strlen($requestBody),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => $this->makeAuthorizationHeader($path, $requestMethod, $requestBody),
            ],
            $requestBody
        );

        $response = $this->httpClient->send($request);
        $payload = json_decode($response->getBody());

        /** @var Plain $token */
        $token = (new TokenParser(new Parser))->parse((string)$payload->data);
        $this->verify($token);

        $userData = $token->claims()->get('data');
        if ($payload->encrypted) {
            $userData = $this->decrypt($userData);
        }

        return new UserData($payload->userId, $this->parser->jsonDecode($userData));
    }

    /**
     * @param string $encrypted
     * @return string
     */
    private function decrypt(string $encrypted): string
    {
        $iv = substr($encrypted, 0, 32);
        $encodedData = substr($encrypted, 32);

        return openssl_decrypt(
            base64_decode($encodedData),
            'AES-128-CBC',
            hex2bin($this->config->secret()),
            OPENSSL_RAW_DATA,
            hex2bin($iv)
        );
    }

    /**
     * @return Key
     */
    private function getTokenSingingKey(): Key
    {
        $privateKeySerializer = new PemPrivateKeySerializer(new DerPrivateKeySerializer(EccFactory::getAdapter()));
        $privateKey = $this->eccGenerator->getPrivateKeyFrom(gmp_init($this->config->privateKey(), 16));

        return new Key($privateKeySerializer->serialize($privateKey));
    }

    /**
     * @return Key
     */
    private function getTokenVerificationKey(): Key
    {
        $publicKeySerializer = new PemPublicKeySerializer(new DerPublicKeySerializer(EccFactory::getAdapter()));
        $publicKey = $this->eccGenerator->getPublicKeyFrom(
            gmp_init(substr(self::SIP_PUB_HEX, 2, 64), 16),
            gmp_init(substr(self::SIP_PUB_HEX, 66, 64), 16)
        );

        return new Key($publicKeySerializer->serialize($publicKey));
    }

    /**
     * @param string $targetPath
     * @param string $requestMethod
     * @param $requestBody
     * @return string
     */
    private function makeAuthorizationHeader(string $targetPath, string $requestMethod, string $requestBody)
    {
        $tokenBuilder = (new TokenBuilder($this->parser))
            ->issuedBy($this->config->id())
            ->permittedFor($this->baseUri)
            ->relatedTo($this->config->id())
            ->identifiedBy(Uuid::uuid4())
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter((new DateTimeImmutable())->modify('+1 minute'))
            ->expiresAt((new DateTimeImmutable())->modify('+3 minute'))
            ->withClaim(
                'data',
                [
                    'method' => $requestMethod,
                    'path' => $targetPath,
                ]
            );

        // Generate signed token.
        $token = $tokenBuilder->getToken(Ecdsa\Sha256::create(), $this->getTokenSingingKey());
        $extension = base64_encode(
            (new Hmac\Sha256())->sign($requestBody, new Key($this->config->secret()))
        );

        return sprintf('Civic %s.%s', $token, $extension);
    }

    /**
     * @param Plain $token
     */
    private function verify(Plain $token)
    {
        (new Validator)->assert($token, new SignedWith(Ecdsa\Sha256::create(), $this->getTokenVerificationKey()));
    }
}
