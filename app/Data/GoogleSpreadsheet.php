<?php declare(strict_types=1);

namespace App\Data;

use GuzzleHttp\Client;

class GoogleSpreadsheet
{
    /**
     * The Google Spreadsheet Id.
     *
     * @var string
     */
    public $id;

    /**
     * Construct the class.
     *
     * @param string $id The id of your Google Spreadsheet
     *
     * @return self
     */
    public function __construct(string $id)
    {
        $this->_ensureIsValidId($id);

        $this->id = $id;
    }

    /**
     * Get the Google Spreadsheet Id.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the Google Spreadsheet Id.
     *
     * @param string $id The new id.
     *
     * @return void
     */
    public function setId(string $id): void
    {
        $this->_ensureIsValidId($id);

        $this->id = $id;
    }

    /**
     * Ensure that is a valid Google Spreadsheet ID.
     *
     * @param string $id The id to check.
     *
     * @return void
     */
    private function _ensureIsValidId(string $id): void
    {
        $isEmpty = empty($id);
        $notString = !is_string($id);
        $notMatch = !preg_match("/([a-zA-Z0-9-_]+)/", $id);

        if ($isEmpty || $notString || $notMatch) {
            throw new \InvalidArgumentException(
                sprintf('"%s" is not a valid Google Spreadsheet Id', $id)
            );
        }
    }

    /**
     * Fetch data from the Google Spreadsheet API.
     *
     * @return array
     */
    public function fetchData(): array
    {
        $client = new Client();
        $response = $client->get(
            "https://spreadsheets.google.com/feeds/list/" .
                $this->id .
                "/od6/public/basic?alt=json"
        );
        $body = $response->getBody();
        $json = json_decode((string) $body, true);

        return $this->_parseData($json);
    }

    /**
     * Parse the data returned by the `fetchData` method of this class.
     *
     * @param array $data The data returned from `fetchData` method.
     *
     * @return array
     */
    private function _parseData(array $data): array
    {
        $updatedAt = $data["feed"]["updated"]['$t'];
        $entrys = $data["feed"]["entry"];

        // Accounts filtering by Platform
        $platforms = $this->_getPlatforms($entrys);
        $accounts = [];

        foreach ($platforms as $platform) {
            $platformAccounts = $this->_filterByPlatform($entrys, $platform);

            array_push($accounts, [
                "platform" => $platform,
                "accounts" => $platformData["accounts"],
                "counts" => $platformData["counts"],
            ]);
        }

        // Get admins and his count of Accounts
        $adminsRawData = $this->_getAdmins($entrys);
        $admins = [];

        foreach ($adminsRawData as $admin) {
            $adminAccounts = $this->_filterByAdmin($entrys, $admin);

            array_push($admins, [
                "administrator" => $admin,
                "accounts" => $adminAccounts,
            ]);
        }

        return [
            "updatedAt" => $updatedAt,
            "platforms" => $platforms,
            "accounts" => $accounts,
            "administrators" => $admins,
        ];
    }

    /**
     * Get the Platforms available in a Google Spreadsheet data.
     *
     * @param array $entrys The entrys from `_parseData` method.
     *
     * @return array
     */
    private function _getPlatforms(array $entrys): array
    {
        $platforms = array_unique(
            array_map(function ($entry) {
                $split = explode(", ", $entry["content"]['$t']);
                $splitPlatform = explode(": ", $split[0])[1];

                return ucfirst($splitPlatform);
            }, $entrys)
        );

        return $platforms;
    }

    /**
     * Filter the data from the Google Spreadsheet by a Platform.
     *
     * @param array $entrys The Entrys from `_parseData` method.
     * @param string $platform The platform to filter.
     *
     * @return array
     */
    private function _filterByPlatform(array $entrys, string $platform): array
    {
        $filtered = array_filter($entrys, function ($entry) use ($platform) {
            $split = explode(", ", $entry["content"]['$t']);
            $splitPlatform = explode(": ", $split[0])[1];

            return strtolower($splitPlatform) === strtolower($platform);
        });

        $countBlocked = 0;
        $countActive = 0;

        $accounts = array_map(function ($entry) {
            $split = explode(", ", $entry["content"]['$t']);

            $username = explode(": ", $split[1])[1];
            $admin = explode(": ", $split[2])[1];
            $status = explode(": ", $split[3])[1];

            switch (strtolower($status)) {
            case "activa":
                $countActive++;
                $status = "active";

                break;
            case "bloqueada":
                $countBlocked++;
                $status = "blocked";

                break;
            }

            return [
                "username" => $username,
                "administrator" => $admin,
                "status" => $status
            ];
        }, $filtered);

        return [
            "accounts" => $accounts,
            "counts" => [
                "total" => count($accounts),
                "blocked" => $countBlocked,
                "active" => $countActive,
            ],
        ];
    }

    /**
     * Get the administrators available in a Google Spreadsheet Data.
     *
     * @param array $entrys The Entrys from `_parseData` method.
     *
     * @return array
     */
    private function _getAdmins(array $entrys): array
    {
        $admins = array_unique(
            array_map(function ($entry) {
                $split = explode(", ", $entry["content"]['$t']);

                $admin = explode(": ", $split[2])[1];

                return $admin;
            }, $entrys)
        );

        return $admins;
    }

    /**
     * Filter data by a Administrator, and get his account count.
     *
     * @param array $entrys The entrys from `_parseData` method.
     * @param array $admin The admin to filter.
     *
     * @return array
     */
    private function _filterByAdmin(array $entrys, string $admin): array
    {
        // First filter the array by the admin
        $filtered = array_filter($entrys, function ($entry) use ($admin) {
            $split = explode(", ", $entry["content"]['$t']);

            $splitAdmin = explode(": ", $split[2])[1];

            return $splitAdmin === $admin;
        });

        // Then, create the output array
        $accounts = [
            "totalCount" => count($filtered),
            "byPlatform" => [],
        ];

        // Count the accounts by Plaform
        $platforms = $this->_getPlatforms($filtered);

        foreach ($platforms as $platform) {
            $platformAccounts = $this->_filterByPlatform($filtered, $platform);

            array_push($accounts["byPlatform"], [
                "platform" => $platform,
                "count" => count($platformAccounts),
            ]);
        }

        return $accounts;
    }
}
