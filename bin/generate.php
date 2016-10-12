<?php
// Autoload
require_once(__DIR__.'/../vendor/autoload.php');

// Use
use ConstructionsIncongrues\Entity\AudioFile;
use ConstructionsIncongrues\Entity\Playlist;
use ConstructionsIncongrues\Filter\Combine;
use ConstructionsIncongrues\Filter\GetTracksInformations;
use ConstructionsIncongrues\Filter\Homogenize;
use ConstructionsIncongrues\Filter\Normalize;
use ConstructionsIncongrues\Filter\Silence;
use ConstructionsIncongrues\PlaylistRenderer\Text;
use Illuminate\Support\Collection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;
use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;

// Helpers
$fs = new Filesystem();

// Options
$options = getopt("c:d:n:");
if (!isset($options['c'])) {
    throw new \Exception('Missing option - option=c');
}
if (!isset($options['d'])) {
    $options['d'] = date('YmdHis');
}

// Create channel web directory
$dirChannel = sprintf('%s/../src/web/shows/%s', __DIR__, $options['c']);
if (!is_dir($dirChannel)) {
    $fs->mkdir($dirChannel);
}

if (!isset($options['n'])) {
    $pattern = '/musiqueapproximative_.+_(\d+)_.*\.mp3/';
    $files = glob($dirChannel.'/*.mp3');
    $highestNumber = 0;
    foreach ($files as $file) {
        $matches = [];
        if (preg_match($pattern, basename($file), $matches)) {
            if ((int)$matches[1] > $highestNumber) {
                $highestNumber = $matches[1];
            }
        }
        $options['n'] = $highestNumber + 1;
    }
}

// Base filename for show files
$outputFilename = sprintf('musiqueapproximative_%s_%d_%s', $options['c'], $options['n'], $options['d']);
// var_dump($outputFilename);

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
$yaml = new Parser();
$configuration = $yaml->parse(file_get_contents(__DIR__.'/../src/parameters.yml'));
$dirWorkingDirectory = sprintf('%s/%s', __DIR__.'/'.$configuration['directories']['working_directories'], uniqid());
// var_dump($dirWorkingDirectory);
$maxDuration = $configuration['show']['duration'];
$playlists = [];

// Create playlist for starting and ending files
$playlists['startEnd'] = new Playlist([
    decorate(getRandomFiles(__DIR__.'/'.$configuration['directories']['opening'], '*.mp3', 1))[0],
    decorate(getRandomFiles(__DIR__.'/'.$configuration['directories']['ending'], '*.mp3', 1))[0]
]);

// Create playlist for in-show jingles
$playlists['jingles'] = new Playlist(decorate(getRandomFiles(__DIR__.'/'.$configuration['directories']['jingles'], '*.mp3', $configuration['show']['jingles'])));

// Create playlist for tracks
$playlists['tracks'] = new Playlist(decorate(getRandomFiles(__DIR__.'/'.$configuration['directories']['tracks'], '*.mp3', $configuration['show']['tracks'])));

// Mirror playlists to working directories and apply filters
foreach ($playlists as $name => $playlist) {
    // Get file informations
    $filterGetInformations = new GetTracksInformations(['workingDirectory' => $dirWorkingDirectory]);
    $playlists[$name] = $filterGetInformations->filter($playlists[$name]);

    // Mirror
    /** @var Playlist $playlist */
    $playlists[$name] = $playlist->mirrorTo(sprintf('%s/%s', $dirWorkingDirectory, $name));

    // Trim silence
    $filterSilence = new Silence(['workingDirectory' => $dirWorkingDirectory]);
    $playlists[$name] = $filterSilence->filter($playlists[$name]);

    // Make tracks characteristics similar. This is required for SoX combination
    $filterHomogenize = new Homogenize(['workingDirectory' => $dirWorkingDirectory]);
    $playlists[$name] = $filterHomogenize->filter($playlists[$name]);
}

$durationNonTracks = $playlists['startEnd']->getDuration() + $playlists['jingles']->getDuration();
$durationLeftForTracks = $maxDuration - $durationNonTracks;
// var_dump(sprintf('maximum duration : %s', $maxDuration));
// var_dump(sprintf('non tracks duration : %s', $durationNonTracks));
// var_dump(sprintf('duration left for tracks : %s', $durationLeftForTracks));
// var_dump(sprintf('tracks playlist original duration : %s', $playlists['tracks']->getDuration()));
$playlists['tracks']->shrinkTo($durationLeftForTracks);
// var_dump(sprintf('tracks playlist new duration : %s', $playlists['tracks']->getDuration()));

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

// Normalize tracks
$filterNormalize = new Normalize(['workingDirectory' => $dirWorkingDirectory]);
$playlist['shows'] = $filterNormalize->filter($playlists['shows']);

// Combine tracks
$filterCombine = new Combine(
    ['workingDirectory' => $dirWorkingDirectory,
    'outputFilename' => sprintf('%s/%s.mp3', $dirChannel, $outputFilename)]
);
$playlistFinale = $filterCombine->filter($playlists['shows']);

// Store playlist
$renderer = new Text();
file_put_contents(
    sprintf('%s/%s.txt', $dirChannel, $outputFilename),
    $renderer->render($playlists['shows'])
);

// Display playlist
echo $renderer->render($playlists['shows']);

// Display show URL
echo sprintf(
    "Show is available at : http://radio.musiqueapproximative.net/shows/%s/%s.mp3\n",
    $options['c'],
    $outputFilename
);
echo sprintf(
    "Show playlist is available at : http://radio.musiqueapproximative.net/shows/%s/%s.txt\n",
    $options['c'],
    $outputFilename
);

// Cleanup
$fs->remove($dirWorkingDirectory);
