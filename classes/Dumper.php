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
    public $folder;

    public function __construct($config)
    {
        $config = json_decode($config, true);
        if (isset($config) && !empty($config)) {
            list($this->token, $this->userId) = array_values($config);
        } else {
            throw new \Exception('Can\'t obtain token and userId. Please, fill config.json');
        }
        $user = $this->useCurl("https://api.vk.com/method/users.get?user_ids=$this->userId&v=5.60");
        if (!empty($user->response[0]->id)) {
            $this->folder = '['.$user->response[0]->id.'] '.$user->response[0]->first_name.' '.$user->response[0]->last_name;
        } else {
            throw new \Exception('Can\'t find user. Please, fill config.json');
        }
        if (!is_dir($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'result'.DIRECTORY_SEPARATOR)) {
            mkdir($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'result'.DIRECTORY_SEPARATOR, 0777);
        }
        if (!is_dir($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'result'.DIRECTORY_SEPARATOR.$this->folder.DIRECTORY_SEPARATOR)) {
            mkdir($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'result'.DIRECTORY_SEPARATOR.$this->folder.DIRECTORY_SEPARATOR, 0777);
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
     * @return mixed|bool status of downloading
     */
    public function getPhotos($start)
    {
        $response = $this->useCurl("https://api.vk.com/method/messages.getHistoryAttachments?peer_id=$this->userId&count=10&access_token=$this->token&start=$start&media_type=photo&v=5.60");
        if (!empty($response->response->items)) {
            $pictures = $response->response->items;
            foreach ($pictures as $picture) {
                $resolution = $this->getHighestResolutionUrl($picture->photo);
                echo $this->savePicture('photo'.$picture->photo->owner_id. '_' .$picture->photo->id, $resolution);
            }
        }
        if (isset($response->response->next_from)){
            return $this->getPhotos($response->response->next_from);
        } else {
            return true;
        }
    }

    /**
     * Search the most high resolution.
     *
     * @param object $data object with response from VK
     *
     * @return string highest resolution url
     */
    public function getHighestResolutionUrl($data)
    {
        $photoSizes = [];
        $dataArray = (array) $data;
        foreach (array_keys($dataArray) as $key) {
            if (preg_match('/^photo_[0-9]+/i', $key, $matches)) {
                $photoSizes[] = $key;
            }
        }
        end($photoSizes);

        return $data->{$photoSizes[key($photoSizes)]};
    }

    /**
     * Saving picture by url.
     *
     * @param string $name file name
     * @param string $url  picture url
     *
     * @return string status or notify about saving
     */
    public function savePicture($name, $url)
    {
        if (file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'result'.DIRECTORY_SEPARATOR.$this->folder.DIRECTORY_SEPARATOR."$name.jpg", $this->useCurl($url))) {
            return date('[d.m.Y H:i:s]').' photo saved'.'<br/>'.PHP_EOL;
        } else {
            return date('[d.m.Y H:i:s]'). ' error with saving photo '.$name.'<br/>'.PHP_EOL;
        }
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
