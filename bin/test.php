<?php
// Autoload
require_once(__DIR__.'/../vendor/autoload.php');

// Use
use ConstructionsIncongrues\Entity\AudioFile;
use ConstructionsIncongrues\Entity\Playlist;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;

// Helpers

/**
 * @return  [\SplFileInfo]
 */
function getRandomFiles($directory, $glob, $limit = 1, Collection $files = null)
{
    if (is_null($files)) {
        $files = new Collection();
    }

    $filesPaths = new Collection(glob(sprintf('%s/%s', $directory, $glob)));
    for ($i = count($files); $i < $limit; $i++) {
        $files[] = $filesPaths->random();
    }

    $files = $files->unique()->values();

    if (count($files) < $limit) {
        $files = getRandomFiles($directory, $glob, $limit, $files);
    }

    return $files;
}

function decorate(Collection $filesPaths)
{
    $audioFiles = [];
    foreach ($filesPaths as $filesPath) {
        $audioFiles[] = new AudioFile(new \SplFileInfo($filesPath));
    }

    return $audioFiles;
}

// Configuration
$dirFixtures = __DIR__.'/../src/fixtures';
$dirEnding = sprintf('%s/%s', $dirFixtures, 'ending/real');
$dirJingles = sprintf('%s/%s', $dirFixtures, 'jingles/real');
$dirOpening = sprintf('%s/%s', $dirFixtures, 'opening/real');
$dirTracks = sprintf('%s/%s', $dirFixtures, 'tracks/real');
$dirVirgules = sprintf('%s/%s', $dirFixtures, 'virgules/dummy');
$dirWorkingDirectories = sprintf('%s/%s', $dirFixtures, 'working_directories');

// 1 - Create playlist for starting and ending files
$playlistStartEnd = new Playlist(
    [decorate(getRandomFiles($dirOpening, '*.mp3', 1))[0], decorate(getRandomFiles($dirEnding, '*.mp3', 1))[0]]
);

// 2 - Create playlist for in-show jingles
$playlistJingles = new Playlist(decorate(getRandomFiles($dirJingles, '*.mp3', 4)));

// 3 - Create playlist for tracks
$playlistTracks = new Playlist(decorate(getRandomFiles($dirTracks, '*.mp3', 27)));

// 4 - Intelligently merge playlists to create final show playlist

// Distribute jingles
$playlistShow = new Playlist();
$chunks = $playlistTracks->chunk(floor(count($playlistTracks) / count($playlistJingles)));
for ($i = 0; $i < count($chunks); $i++) {
    if ($playlistJingles->has($i)) {
        $chunks[$i]->push($playlistJingles[$i]);
    }
    $playlistShow = $playlistShow->merge($chunks[$i]);
}

// Prepend opening credit
$playlistShow->prepend($playlistStartEnd[0]);

// Append closing credits
$playlistShow->push($playlistStartEnd[1]);

echo $playlistShow;

exit;




// sizeof($asteroids) / sizeof($planets)
