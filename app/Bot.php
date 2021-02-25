<?php declare(strict_types=1);

namespace App;

use App\Data\GoogleSpreadsheet;
use App\Platform;
use App\Utils\DateTime;

class Bot
{
    /**
     * The output path where save the data.
     *
     * @var string
     */
    public $outputPath;

    /**
     * Construct the class.
     *
     * @param string $outputPath The output path where save the data.
     *
     * @return self
     */
    public function __construct(
        string $outputPath = __DIR__ . "/../storage/data/"
    ) {
        $this->_ensureIsValidOutputPath($outputPath);

        $this->outputPath = $outputPath;
    }

    /**
     * Get the output path.
     *
     * @return string
     */
    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    /**
     * Set the output path.
     *
     * @param string $outputPath The output path to set.
     *
     * @return void
     */
    public function setOutputPath(string $outputPath): void
    {
        $this->_ensureIsValidOutputPath($outputPath);

        $this->outputPath = $outputPath;
    }

    /**
     * Run the Bot.
     *
     * @return void
     */
    public function run(): void
    {
        $spreadsheet = new GoogleSpreadsheet(getenv("GOOGLE_SPREADSHEET_ID"));
        $spreadsheetData = $spreadsheet->fetchData();

        // $platformsData = Platform::getDataOfAllPlatforms(
        //     $spreadsheetData["accounts"]
        // );
        // $spreadsheetData["accounts"] = $platformsData;
        $spreadsheetData["generatedAt"] = date("h:i:s A");
        $spreadsheetData["weekOfMonth"] = DateTime::weekOfMonth(date("Y-m-d"));

        $output = json_encode($spreadsheetData);
        $outputFilename = $this->outputPath . "/" . date("d-m-Y") . ".json";

        $file = fopen($outputFilename, "w");
        fwrite($file, $output);
        fclose($file);
    }

    /**
     * Ensure is a valid output path.
     *
     * @param string $outputPath The output path to check.
     *
     * @return void
     */
    private function _ensureIsValidOutputPath(string $outputPath): void
    {
        $isEmpty = empty($outputPath);
        $notString = !is_string($outputPath);
        $notExists = !file_exists($outputPath);

        if ($isEmpty || $notString) {
            throw new \InvalidArgumentException(
                sprintf('"%s" is not a valid output path', $outputPath)
            );
        } elseif ($notExists) {
            throw new \InvalidArgumentException(
                sprintf('"%s" no such directory', $outputPath)
            );
        }
    }
}
