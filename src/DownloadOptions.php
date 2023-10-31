<?php

namespace YouTube;

use YouTube\Models\SplitStream;
use YouTube\Models\StreamFormat;
use YouTube\Models\VideoDetails;
use YouTube\Utils\Utils;

// TODO: rename DownloaderResponse
class DownloadOptions
{
    /** @var StreamFormat[] $formats */
    private array $formats = [];

    /** @var VideoDetails|null */
    private ?VideoDetails $info;

    public function __construct($formats, $info = null)
    {
        $this->formats = $formats;
        $this->info = $info;
    }

    /**
     * @return StreamFormat[]
     */
    public function getAllFormats(): array
    {
        return $this->formats;
    }

    /**
     * @return VideoDetails|null
     */
    public function getInfo(): ?VideoDetails
    {
        return $this->info;
    }

    // Will not include Videos with Audio
    public function getVideoFormats(): array
    {
        return Utils::arrayFilterReset($this->getAllFormats(), function ($format) {
            /** @var $format StreamFormat */
            return strpos($format->mimeType, 'video') === 0 && empty($format->audioQuality);
        });
    }

    public function getAudioFormats(): array
    {
        return Utils::arrayFilterReset($this->getAllFormats(), function ($format) {
            /** @var $format StreamFormat */
            return strpos($format->mimeType, 'audio') === 0;
        });
    }

    /**
     * @return StreamFormat[]
     */
    public function getCombinedFormats(): array
    {
        return Utils::arrayFilterReset($this->getAllFormats(), function ($format) {
            /** @var $format StreamFormat */
            return strpos($format->mimeType, 'video') === 0 && !empty($format->audioQuality);
        });
    }

    /**
     * @return StreamFormat|null
     */
    public function getFirstCombinedFormat(): ?StreamFormat
    {
        $combined = $this->getCombinedFormats();
        return count($combined) ? $combined[0] : null;
    }

    protected function getLowToHighVideoFormats(): array
    {
        $copy = array_values($this->getVideoFormats());

        usort($copy, function ($a, $b) {

            /** @var StreamFormat $a */
            /** @var StreamFormat $b */

            return $a->height - $b->height;
        });

        return $copy;
    }

    protected function getLowToHighAudioFormats(): array
    {
        $copy = array_values($this->getAudioFormats());

        // just assume higher filesize => higher quality...
        usort($copy, function ($a, $b) {

            /** @var StreamFormat $a */
            /** @var StreamFormat $b */

            return $a->contentLength - $b->contentLength;
        });

        return $copy;
    }
}