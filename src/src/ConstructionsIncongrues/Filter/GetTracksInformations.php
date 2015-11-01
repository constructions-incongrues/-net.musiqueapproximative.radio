<?php
namespace ConstructionsIncongrues\Filter;

use ConstructionsIncongrues\Entity\AudioFile;
use ConstructionsIncongrues\Entity\Playlist;
use GuzzleHttp\Client;
use UriTemplate\Processor;

class GetTracksInformations
{
    private $parameters = [];

    public function __construct($parameters = [])
    {
        $this->parameters = array_merge(
            ['uriTemplate' => 'http://www.musiqueapproximative.net/frontend_dev.php/post/md5/{uid}'],
            $parameters
        );
    }

    public function filter(Playlist $playlist)
    {
        $client = new Client();
        $playlist->each(function (AudioFile $audioFile) use ($client) {
            $url = new Processor($this->parameters['uriTemplate'], ['uid' => $audioFile->getMd5()]);
            $response = $client->request('GET', $url->process(), ['headers' => ['Accept' => 'application/json']]);
            $response = json_decode($response->getBody()->getContents(), true);
            if ($response && isset($response['track'])) {
                $audioFile->setArtist($response['track']['author']);
                $audioFile->setDescription($response['body']['markdown']);
                $audioFile->setTitle($response['track']['title']);
            } else {
                $audioFile->setArtist('Unknown Artist');
                $audioFile->setTitle($audioFile->getFile()->getFilename());
            }
        });

        return $playlist;
    }
}
