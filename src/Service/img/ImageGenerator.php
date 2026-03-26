<?php

namespace App\Service\Img;

use DateTime;
use Exception;
use GdImage;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class ImageGenerator
{
    public function __construct(
        private readonly ContainerBagInterface $container
    ) {}

    public function loadBackground(bool $isResult): GdImage {
        $bkground = $isResult ? 'results.png' : 'games.png';
        $path = $this->container->get('app.public_announcement_img').'/'.$bkground;

        if(!file_exists($path)){
            throw new Exception('Image de fond '.$bkground.' introuvable');
        }

        $canvas = imagecreatefrompng($path);

        if($canvas === false ){
            throw new Exception('Impossible de créer la base d l\'image pour les matchs');
        }

        return $canvas;
    }

    public function saveAnnouncement(GdImage $canvas, bool $isResult) : string {
        $type = $isResult ? 'results' : 'preview';
        $timestamp = (new DateTime('now')->format('Ymd-His'));
        $filePath = $this->container->get('app.public_announcement_img').'/'.$type.'_'.$timestamp.'.png';

        if(!imagepng($canvas, $filePath)){
            throw new Exception('Impossible de sauvegarder l\'image '.$type);
        }

        return $filePath;
    }
}
