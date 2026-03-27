<?php

namespace App\Controller;

use App\Exception\CsvException;
use App\Service\GameFactory;
use App\Service\GameTypeDispatcher;
use App\Service\Img\ImageFactory;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AnnouncementController extends AbstractController
{

    public function __construct(
        private readonly GameTypeDispatcher $csvDispatcher,
        private readonly GameFactory $serviceGame,
        private readonly ImageFactory $serviceImage
    ) {}

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(
        Request $req
    ): Response {

        $existingGamesInSession = $req->getSession()->get('toConfirm');
        if ($existingGamesInSession) {
            $req->getSession()->remove('toConfirm');
        }

        return $this->render('announcement/index.html.twig', []);
    }

    #[Route('/confirm', name: 'app_confirmation', methods: ['POST'])]
    public function confirm(
        Request $req
    ): Response {
        $uploadedFiles = [
            $req->files->get('csv_file_1'),
            $req->files->get('csv_file_2')
        ];

        $uploadedFiles = array_filter($uploadedFiles, fn($f) => $f !== null);

        if (empty($uploadedFiles)) {
            $this->addFlash('danger', 'Veuillez uploader au moins un fichier CSV');
            return $this->redirectToRoute('app_home');
        }

        $gamesOnHold = [];

        foreach ($uploadedFiles as $file) {
            $fileExtension = $file->getClientOriginalExtension();
            $fileCompleteName = $file->getClientOriginalName() . uniqid('_') . '.' . $fileExtension;
            $filePath = $this->getParameter('app.public_import_directory') . '/' . $fileCompleteName;

            if ($fileExtension !== 'csv') {
                $this->addFlash('danger', 'Le fichier ' . $file->getClientOriginalName() . ' n\'est pas un CSV');
                return $this->redirectToRoute('app_home');
            }

            try {
                $file->move($this->getParameter('app.public_import_directory'), $fileCompleteName);
                $gamesOnHold[] = $this->csvDispatcher->processCSVImport($filePath);
            } catch (CsvException $e) {
                $this->addFlash('danger', 'Une erreur s\'est produite au traitement des fichiers : ' . $e->getMessage());
                return $this->redirectToRoute('app_home');
            } finally {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        $req->getSession()->set('toConfirm', $gamesOnHold);

        return $this->redirectToRoute('app_confirmation_get');
    }

    #[Route('/confirm', name: 'app_confirmation_get', methods: ['GET'])]
    public function confirmView(
        Request $req
    ): Response {
        $toConfirm = $req->getSession()->get('toConfirm');

        if (empty($toConfirm)) {
            $this->addFlash('danger', 'Session expirée, veuillez retransmettre les fichiers d\'import.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('announcement/confirm.html.twig', [
            'data' => $toConfirm,
        ]);
    }

    #[Route('/generate', name: 'app_generate', methods: ['GET','POST'])]
    public function generator(
        Request $req
    ): Response {
        $gamesOnHold = $req->getSession()->get('toConfirm');
        if (empty($gamesOnHold)) {
            $this->addFlash('danger', 'Session expirée, veuillez retransmettre les fichier d\'import.');
            return $this->redirectToRoute('app_home');
        }

        try {
            $images = [];
            $imgUrls = [];

            $announcements = $this->serviceGame->createGames($gamesOnHold);

            foreach ($announcements as $announce) {
                $isResult = $announce['isResult'];
                $codes = array_slice($announce, 1);
                
                $images = array_merge($images, $this->serviceImage->createAnnouncments($codes, $isResult));
            }

            $req->getSession()->remove('toConfirm');

            foreach ($images as $image) {
                $imgUrls[] = str_replace(
                    $this->getParameter('kernel.project_dir') . '/public',
                    '',
                    $image
                );
            }
        } catch (Exception $e) {
            $this->addFlash('danger', 'Une erreur s\'est produite lors de la génération d\'image : ' . $e->getMessage());
            return $this->redirectToRoute('app_home');
        }

        return $this->render('announcement/result.html.twig', [
            'imageUrls' => $imgUrls
        ]);
    }
}
