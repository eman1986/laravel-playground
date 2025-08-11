<?php

namespace App\Services;

use DateInterval;
use DateTimeZone;
use Illuminate\Support\Carbon;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Encoding\CannotDecodeContent;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\UnsupportedHeaderFound;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Validator;
use Throwable;

class JwtService
{
    /**
     * Issues a new authentication token for a given user.
     *
     * @param  int  $userId  The ID of the user for whom the token is being issued.
     * @return string The generated authentication token.
     */
    public function issueAuthToken(int $userId): string
    {
        $now = Carbon::now()->toimmutable();
        $token = new Builder(new JoseEncoder, ChainedFormatter::default())
            ->issuedBy(config('jwt.issuer'))
            ->permittedFor(config('jwt.audience'))
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify('+15 minutes'))
            ->withClaim('uid', $userId)
            ->getToken(new Sha512, InMemory::plainText(config('jwt.secret')));

        return $token->toString();
    }

    /**
     * Parses a JWT and retrieves a specified claim.
     *
     * @param  string  $jwt  The JSON Web Token to be parsed.
     * @param  string  $claim  The claim to retrieve from the token.
     * @return string|null The value of the specified claim if present, or null if the token is invalid or the claim does not exist.
     */
    public function parseJwt(string $jwt, string $claim): ?string
    {
        $parser = new Parser(new JoseEncoder);
        $token = null;

        try {
            $token = $parser->parse($jwt);
        } catch (CannotDecodeContent|InvalidTokenStructure|UnsupportedHeaderFound $e) {
            report($e);
        }

        if ($token instanceof UnencryptedToken) {
            return $token->claims()->get($claim);
        }

        return null;
    }

    /**
     * Validates a JSON Web Token (JWT) based on the given constraints.
     *
     * @param  string  $jwt  The JWT to validate.
     * @return bool True if the token is valid, false otherwise.
     */
    public function isTokenValid(string $jwt): bool
    {
        $token = new Parser(new JoseEncoder)->parse($jwt);
        $clock = new SystemClock(new DateTimeZone('UTC'));
        $leeway = new DateInterval('PT1M'); // give our validator a 1-minute leeway

        try {
            new Validator()->assert(
                $token,
                new IssuedBy(config('jwt.issuer')),
                new PermittedFor(config('jwt.audience')),
                new StrictValidAt($clock, $leeway)
            );

            return true;
        } catch (CannotDecodeContent|InvalidTokenStructure|UnsupportedHeaderFound $e) {
            report($e);

            return false;
        } catch (RequiredConstraintsViolated $v) {
            report($v);

            return false;
        } catch (Throwable $t) {
            report($t);

            return false;
        }
    }
}
