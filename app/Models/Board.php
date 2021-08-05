<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;

class Board extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function updateTokens($accessToken): void
    {
        $this->accessToken = $accessToken->getToken();
        $this->refreshToken = $accessToken->getRefreshToken();
        $this->tokenExpires = $accessToken->getExpires();
        $this->save();
    }

    public function clearTokens(): void
    {
        $this->update([
            'accessToken' => null,
            'refreshToken' => null,
            'tokenExpires' => null,
            'userTimeZone' => null
        ]);
    }

    public function storeToken($accessToken): void
    {
        $this->update([
            'accessToken' => $accessToken->getToken(),
            'refreshToken' => $accessToken->getRefreshToken(),
            'tokenExpires' => $accessToken->getExpires(),
        ]);
    }

    public function getAccessToken()
    {
        // Check if tokens exist
        if ($this->accessToken === null ||
            $this->refreshToken === null ||
            $this->tokenExpires === null) {
            return '';
        }

        // Check if token is expired
        //Get current time + 5 minutes (to allow for time differences)
        $now = time() + 300;
        if ($this->tokenExpires <= $now) {
            // Token is expired (or very close to it)
            // so let's refresh

            // Initialize the OAuth client
            $oauthClient = new GenericProvider([
                'clientId'                => config('azure.appId'),
                'clientSecret'            => config('azure.appSecret'),
                'redirectUri'             => config('azure.redirectUri'),
                'urlAuthorize'            => config('azure.authority').config('azure.authorizeEndpoint'),
                'urlAccessToken'          => config('azure.authority').config('azure.tokenEndpoint'),
                'urlResourceOwnerDetails' => '',
                'scopes'                  => config('azure.scopes')
            ]);
            try {
                $newToken = $oauthClient->getAccessToken('refresh_token', [
                    'refresh_token' => $this->refreshToken
                ]);
                // Store the new values
                $this->updateTokens($newToken);

                return $newToken->getToken();
            } catch (IdentityProviderException $e) {
                return '';
            }
        }

        // Token is still valid, just return it
        return $this->accessToken;
    }
}
