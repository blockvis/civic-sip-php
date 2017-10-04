<?php

namespace Blockvis\Civic\Sip;

use GuzzleHttp\ClientInterface as HttpClient;
use GuzzleHttp\Psr7\Request;
use Jose\Factory\JWKFactory;
use Jose\Factory\JWSFactory;
use Jose\Loader;
use Jose\Object\JWKInterface;
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
    }

    /**
     * @param string $jwtToken
     * @return UserData
     */
    public function exchangeToken($jwtToken)
    {
        $requestMethod = 'POST';
        $path = 'scopeRequest/authCode';
        $requestBody = \GuzzleHttp\json_encode(['authToken' => $jwtToken]);

        $request = new Request(
            $requestMethod,
            sprintf('%s/%s/%s', $this->baseUri, $this->config->env(), $path),
            [
                'Content-Length' => strlen($requestBody),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => $this->makeAuthorizationHeader($path, $requestMethod, $requestBody),
            ],
            $requestBody
        );

        $response = $this->httpClient->send($request);
        $payload = json_decode($response->getBody());

        $jws = (new Loader())->loadAndVerifySignatureUsingKey(
            (string)$payload->data,
            $this->getTokenVerificationKey(),
            ['ES256']
        );

        $userData = $jws->getClaim('data');
        if ($payload->encrypted) {
            $userData = $this->decrypt($userData);
        }

        return new UserData($payload->userId, \GuzzleHttp\json_decode($userData));
    }

    /**
     * @param string $encrypted
     * @return string
     */
    private function decrypt($encrypted)
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
     * @return JWKInterface
     */
    private function getTokenSingingKey()
    {
        $privateKeySerializer = new PemPrivateKeySerializer(new DerPrivateKeySerializer(EccFactory::getAdapter()));
        $privateKey = $this->eccGenerator->getPrivateKeyFrom(gmp_init($this->config->privateKey(), 16));

        return JWKFactory::createFromKey($privateKeySerializer->serialize($privateKey));
    }

    /**
     * @return JWKInterface
     */
    private function getTokenVerificationKey()
    {
        $publicKeySerializer = new PemPublicKeySerializer(new DerPublicKeySerializer(EccFactory::getAdapter()));
        $publicKey = $this->eccGenerator->getPublicKeyFrom(
            gmp_init(substr(self::SIP_PUB_HEX, 2, 64), 16),
            gmp_init(substr(self::SIP_PUB_HEX, 66, 64), 16)
        );

        return JWKFactory::createFromKey($publicKeySerializer->serialize($publicKey));
    }

    /**
     * @param string $targetPath
     * @param string $requestMethod
     * @param $requestBody
     * @return string
     */
    private function makeAuthorizationHeader($targetPath, $requestMethod, $requestBody)
    {
        $jws = JWSFactory::createJWSToCompactJSON(
            [
                'nbf'  => time() + 60,         // Not before
                'iat'  => time(),              // Issued at
                'exp'  => time() + 60 * 3,     // Expires at
                'iss'  => $this->config->id(), // Issuer
                'aud'  => $this->baseUri,      // Audience
                'sub'  => $this->config->id(), // Subject
                'jti'  => Uuid::uuid4(),       // ID
                'data' => [                    // Custom claim
                    'method' => $requestMethod,
                    'path'   => $targetPath,
                ]
            ],
            $this->getTokenSingingKey(),
            [
                'alg' => 'ES256',
            ]
        );

        $extension = base64_encode(hash_hmac('sha256', $requestBody, $this->config->secret(), true));

        return sprintf('Civic %s.%s', $jws, $extension);
    }

}
