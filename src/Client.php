<?php

namespace Blockvis\Civic\Sip;

use DateTimeImmutable;
use GuzzleHttp\ClientInterface as HttpClient;
use Lcobucci\Jose\Parsing\Parser;
use Lcobucci\JWT\Signer\Ecdsa;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Hmac;
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
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var GeneratorPoint
     */
    private $eccGenerator;

    /**
     * Client constructor.
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
     * @return array
     */
    public function exchangeToken(string $jwtToken)
    {
        $method = 'POST';
        $path = 'scopeRequest/authCode';
        $body = ['authToken' => $jwtToken];

        $response = $this->httpClient->request(
            $method,
            sprintf('%s/%s/%s', $this->baseUri, $this->config->env(), $path), [
                'headers' => [
                    'Content-Length' => strlen(json_encode($body)),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->makeAuthorizationHeader($path, $method, $body),
                ],
                'body' => ['json' => $body]
            ]);

        $result = json_decode($response->getBody());

        /** @var Plain $token */
        $token = (new TokenParser(new Parser))->parse((string) $result->data);

        $this->verify($token);

        return $this->decode($token);
    }

    /**
     * @param Plain $token
     * @return array
     */
    private function decode(Plain $token): array
    {
        $data = $token->claims()->get('data');
        $iv = substr($data,0, 32);
        $encoded = substr($data, 32);

        $plaintext = openssl_decrypt(
            base64_decode($encoded),
            'AES-128-CBC',
            hex2bin($this->config->secret()),
            OPENSSL_RAW_DATA,
            hex2bin($iv)
        );

        return json_decode($plaintext);
    }

    /**
     * @param Plain $token
     */
    private function verify(Plain $token)
    {
        (new Validator)->assert($token, new SignedWith(Ecdsa\Sha256::create(), $this->getTokenVerificationKey()));
    }

    /**
     * @return Key
     */
    private function getTokenVerificationKey(): Key
    {
        $publicKeySerializer = new PemPublicKeySerializer(new DerPublicKeySerializer(EccFactory::getAdapter()));
        $publicKey = $this->eccGenerator->getPublicKeyFrom(
            gmp_init(substr(self::SIP_PUB_HEX, 2, 64), 16),
            gmp_init(substr(self::SIP_PUB_HEX, 66,64), 16)
        );

        return new Key($publicKeySerializer->serialize($publicKey));
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
     * @param string $targetPath
     * @param string $targetMethod
     * @param $requestBody
     * @return string
     */
    private function makeAuthorizationHeader(string $targetPath, string $targetMethod, $requestBody)
    {
        $parser = new Parser;
        $tokenBuilder = (new TokenBuilder($parser))
            ->issuedBy($this->config->id())
            ->permittedFor($this->baseUri)
            ->relatedTo($this->config->id())
            ->identifiedBy(Uuid::uuid4())
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter((new DateTimeImmutable())->modify('+1 minute'))
            ->expiresAt((new DateTimeImmutable())->modify('+3 minute'))
            ->withClaim('data', [
                'method' => $targetMethod,
                'path' => $targetPath
            ]);

        // Generate signed token.
        $token = $tokenBuilder->getToken(Ecdsa\Sha256::create(), $this->getTokenSingingKey());
        $extension = base64_encode(
            (new Hmac\Sha256())->sign($parser->jsonEncode($requestBody), new Key($this->config->secret()))
        );

        return sprintf('Civic %s.%s', $token, $extension);
    }
}
