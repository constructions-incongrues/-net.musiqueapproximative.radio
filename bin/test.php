<?php
// Autoload
require_once(__DIR__.'/../vendor/autoload.php');

// Use
use ConstructionsIncongrues\Entity\AudioFile;
use ConstructionsIncongrues\Entity\Playlist;
use ConstructionsIncongrues\Filter\Silence;
use ConstructionsIncongrues\Filter\Homogenize;
use ConstructionsIncongrues\Filter\Combine;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

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

$fs = new Filesystem();

// Configuration
$dirFixtures = __DIR__.'/../src/fixtures';
$dirEnding = sprintf('%s/%s', $dirFixtures, 'ending/real');
$dirJingles = sprintf('%s/%s', $dirFixtures, 'jingles/real');
$dirOpening = sprintf('%s/%s', $dirFixtures, 'opening/real');
$dirTracks = sprintf('%s/%s', $dirFixtures, 'tracks/real');
$dirVirgules = sprintf('%s/%s', $dirFixtures, 'virgules/dummy');
$dirWorkingDirectories = sprintf('%s/%s', $dirFixtures, 'working_directories');
$dirWorkingDirectory = sprintf('%s/%s', $dirWorkingDirectories, uniqid());
var_dump($dirWorkingDirectory);
$maxDuration = 600;
$playlists = [];

// Create playlist for starting and ending files
$playlists['startEnd'] = new Playlist(
    [decorate(getRandomFiles($dirOpening, '*.mp3', 1))[0], decorate(getRandomFiles($dirEnding, '*.mp3', 1))[0]]
);

// Create playlist for in-show jingles
$playlists['jingles'] = new Playlist(decorate(getRandomFiles($dirJingles, '*.mp3', 1)));

// Create playlist for tracks
$playlists['tracks'] = new Playlist(decorate(getRandomFiles($dirTracks, '*.mp3', 5)));

// Mirror playlists to working directories and apply filters
foreach ($playlists as $name => $playlist) {
    // Mirror
    $playlists[$name] = $playlist->mirrorTo(sprintf('%s/%s', $dirWorkingDirectory, $name));

    // Trim silence
    $filterSilence = new Silence();
    $playlists[$name] = $filterSilence->filter($playlists[$name]);

    // Make tracks characteristics similar. This is required for SoX combination
    $filterHomogenize = new Homogenize();
    $playlists[$name] = $filterHomogenize->filter($playlists[$name]);
}

$durationNonTracks = $playlists['startEnd']->getDuration() + $playlists['jingles']->getDuration();
$durationLeftForTracks = $maxDuration - $durationNonTracks;
var_dump(sprintf('maximum duration : %s', $maxDuration));
var_dump(sprintf('non tracks duration : %s', $durationNonTracks));
var_dump(sprintf('duration left for tracks : %s', $durationLeftForTracks));
var_dump(sprintf('tracks playlist original duration : %s', $playlists['tracks']->getDuration()));
$playlists['tracks']->shrinkTo($durationLeftForTracks);
var_dump(sprintf('tracks playlist new duration : %s', $playlists['tracks']->getDuration()));

// Distribute jingles
$playlists['shows'] = new Playlist();
$chunks = $playlists['tracks']->chunk(floor(count($playlists['tracks']) / count($playlists['jingles'])));
for ($i = 0; $i < count($chunks); $i++) {
    if ($playlists['jingles']->has($i)) {
        $chunks[$i]->push($playlists['jingles'][$i]);
    }
    $playlists['shows'] = $playlists['shows']->merge($chunks[$i]);
}

// Prepend opening credit
$playlists['shows']->prepend($playlists['startEnd'][0]);

// Append closing credits
$playlists['shows']->push($playlists['startEnd'][1]);

// Combine tracks
$filterCombine = new Combine(['outputFilename' => '/tmp/test.mp3']);
$playlistFinale = $filterCombine->filter($playlists['shows']);

echo $playlists['shows'];
echo $playlistFinale;
