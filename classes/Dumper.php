<?php
// TODO: classes autoload through namespaces
/**
 * Class for fetch photos from VK messages history.
 */


class Dumper
{
    private $token;
    public $photosCount;
    public $executionTime;
    protected $userId;

    public function __construct($config)
    {
        $config = json_decode($config, true);
        if (isset($config) && !empty($config)) {
            list($this->token, $this->userId) = array_values($config);
        } else {
            throw new \Exception('Can\'t obtain token and userId. Please, fill config.json');
        }
    }

    /**
     * Starting benchmark of fetch.
     *
     * @return bool status of execution
     */
    public function startBenchmark()
    {
        return $this->executionTime = microtime(true);
    }

    /**
     * Stopping current benchmark.
     *
     * @return string time for operation tooks
     */
    public function endBenchmark()
    {
        $currentTime = microtime(true);
        $timeTook = $currentTime - $this->executionTime;

        return $timeTook;
    }

    /**
     * Recursively get photos with offset.
     *
     * @param string $start initial step to start downloading
     *
     * @return bool status of downloading
     */
    public function getPhotos($start)
    {
        $response = $this->useCurl("https://api.vk.com/method/messages.getHistoryAttachments?peer_id=$this->userId&count=200&access_token=$this->token&start=$start&media_type=photo");

        if (!empty($response->items)) {
            $pictures = $response->items;
            foreach ($pictures->photo as $picture) {
                var_dump($picture);
                $resolution = $this->getHighResolution($picture);
                echo $resolution;
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Search the most high resolution.
     *
     * @param object $data object with response from VK
     *
     * @return string highest resolution url
     */
    public function getHighResolution($data)
    {
        $data = get_object_vars($data);

        return json_encode($data);
    }

    /**
     * Use curl lib for receive data.
     *
     * @param string $url Host with get params to connect
     *
     * @return object decoded json object
     */
    public function useCurl($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }
}
