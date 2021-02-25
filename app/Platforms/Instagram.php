<?php declare(strict_types=1);

namespace App\Platforms;

use App\Platform;
use GuzzleHttp\Client;

class Instagram extends Platform
{
    /**
     * The username of the account.
     *
     * @var string
     */
    public $username;

    /**
     * Construct the class.
     *
     * @param string $username The username of the account.
     *
     * @return self
     */
    public function __construct(string $username)
    {
        $this->_ensureIsValidUsername($username);

        $this->username = $username;
    }

    /**
     * Get the username of this account.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set a new Username.
     *
     * @param string $username The username to set.
     *
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->_ensureIsValidUsername($username);

        $this->username = $username;
    }

    /**
     * Fetch the data of the account.
     *
     * @return array
     */
    public function fetchData(): array
    {
        $client = new Client();
        $response = $client->request(
            "GET",
            "https://" .
                getenv("RAPIDAPI_INSTAGRAM_HOST") .
                "/v1/profile/" .
                $this->getUsername(),
            [
                "headers" => [
                    "X-Rapidapi-Key" => getenv("RAPIDAPI_KEY"),
                    "X-Rapidapi-Host" => getenv("RAPIDAPI_INSTAGRAM_HOST"),
                ],
            ]
        );

        $json = json_decode((string) $response->getBody(), true);

        return [
            "fullName" => $json["fullName"],
            "biography" => $json["biography"],
            "followersCount" => $json["followersCount"],
            "followingCount" => $json["followingCount"],
            "profilePhoto" => $json["profilePhotoHd"],
            "feedItemsCount" => $json["feedItemsCount"],
        ];
    }

    /**
     * Ensure is a valid username.
     *
     * @param string $username The username to check.
     *
     * @return void
     */
    private function _ensureIsValidUsername(string $username): void
    {
        $isEmpty = empty($username);
        $notString = !is_string($username);
        $notMatch = !preg_match("/^[a-zA-Z0-9._]+$/", $username);

        if ($isEmpty || $notString || $notMatch) {
            throw new \InvalidArgumentException(
                sprintf('"%s" is not a valid username', $username)
            );
        }
    }
}
