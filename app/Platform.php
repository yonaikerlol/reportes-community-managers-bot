<?php declare(strict_types=1);

namespace App;

use App\Platforms\Instagram;

class Platform
{
    /**
     * Map de the raw data obtained by fetchData method of GoogleSpreadheet class, getting data of the platforms, like followers, post count, etc.
     *
     * @param array $data The data to map.
     *
     * @return array
     */
    public static function getDataOfAllPlatforms(array $data): array
    {
        $output = [];

        foreach ($data as $platformAccounts) {
            $platform = $platformAccounts["platform"];
            $outputPlatform = [
                "platform" => $platform,
                "accounts" => [],
                "counts" => $platformAccounts["counts"],
            ];

            switch ($platform) {
            case "Instagram":
                $rateLimitPerMinute = 0;

                foreach ($platformAccounts["accounts"] as $account) {
                    if ($rateLimitPerMinute === 5) {
                        sleep(90);
                        $rateLimitPerMinute = 0;
                    }

                    $instagramAccount = new Instagram(
                        str_replace("@", "", $account["username"])
                    );
                    $instagramAccountData = $account["status"] === "active" ? $instagramAccount->fetchData() : null;

                    array_push($outputPlatform["accounts"], [
                        "username" => $account["username"],
                        "administrator" => $account["administrator"],
                        "status" => $account["status"],
                        "data" => $instagramAccountData,
                    ]);

                    $rateLimitPerMinute++;
                }

                break;
            }

            array_push($output, $outputPlatform);
        }

        return $output;
    }
}
