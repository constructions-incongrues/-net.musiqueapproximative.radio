<?php
// web/index.php
require_once __DIR__.'/../../vendor/autoload.php';

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Symfony\Component\Finder\Finder;

$app = new Silex\Application();

// App configuration
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), ['twig.path' => __DIR__.'/../views']);

// Controllers

// Homepage
$app->get('/', function (Silex\Application $app) {

    // @see https://flysystem.thephpleague.com/adapter/local/
    $adapter = new Local(__DIR__.'/collections/channels');
    $filesystem = new Filesystem($adapter);

    // Browse collections
    $channels = $filesystem->listContents();

    return $app['twig']->render('homepage.twig', ['channels' => $channels]);
})
->bind('homepage');

// About
$app->get('/about', function (Silex\Application $app) {
    return $app['twig']->render('about.twig');
})
->bind('about');

// Channel
$app->get('/{channel}', function (Silex\Application $app, $channel) {
    $finder = new Finder();
    $finder
        ->files()
        ->name('*.mp3')
        ->sort(function ($a, $b) {
            return strcmp($b->getRealpath(), $a->getRealpath());
        })
        ->in(__DIR__.'/collections/channels/'.$channel);


    $shows = [];
    foreach ($finder as $directory) {
        $matches = [];
        preg_match(
            '/musiqueapproximative_radiopulsar_(\d{3})_(\d{14})/',
            $directory->getBasename('.mp3'),
            $matches
        );
        $show = [
            'title'    => $directory->getBasename('.mp3'),
            'number'   => $matches[1],
            'date' => \DateTime::createFromFormat('YmdHis', $matches[2])->format('Y-m-d')
        ];
        $shows[] = $show;
    }

    return $app['twig']->render('channel.twig', ['channel' => $channel, 'shows' => $shows]);
})
->bind('channel');

// Show
$app->get('/{channel}/{show}', function (Silex\Application $app, $channel, $show) {
    // Get manifest contents
    $manifest = file_get_contents(sprintf('%s/collections/channels/%s/%s.txt', __DIR__, $channel, $show));

    // Analyse show title
    $matches = [];
    preg_match(
        '/musiqueapproximative_radiopulsar_(\d{3})_(\d{14})/',
        $show,
        $matches
    );
    $show = [
        'title'    => $show,
        'number'   => $matches[1],
        'date' => \DateTime::createFromFormat('YmdHis', $matches[2])->format('Y-m-d')
    ];

    // Parse contents as Markdown
    $parser = new \cebe\markdown\Markdown();
    $manifest = $parser->parse($manifest);
    return $app['twig']->render('show.twig', ['channel' => $channel, 'show' => $show, 'manifest' => $manifest]);
})
->bind('show');

$app->run();
