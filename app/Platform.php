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
        $outputInstagram = [
            "platform" => "Instagram",
            "accounts" => [],
        ];

        foreach ($data as $platformAccounts) {
            $platform = $platformAccounts["platform"];

            switch ($platform) {
                case "Instagram":
                    $rateLimit = 0;

                    foreach ($platformAccounts["accounts"] as $account) {
                        if ($rateLimit === 5) {
                            sleep(90);
                            $rateLimit = 0;
                        }

                        $instagramAccount = new Instagram(
                            str_replace("@", "", $account["username"])
                        );
                        $instagramAccountData = $instagramAccount->fetchData();

                        array_push($outputInstagram["accounts"], [
                            "username" => $account["username"],
                            "administrator" => $account["administrator"],
                            "data" => $instagramAccountData,
                        ]);

                        $rateLimit++;
                    }

                    break;
            }
        }

        return [$outputInstagram];
    }
}
