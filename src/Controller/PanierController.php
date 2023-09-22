<?php

namespace App\Controller;


use App\Repository\CommandeRepository;
use App\Repository\DetailCommandeRepository;
use App\Repository\EtatRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    #[Route('/panier', name: '_panier')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        $utilisateur = $this->getUser();
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );
        if($derniereCommande != null){
        $detailsCommandes = $derniereCommande->getDetailsCommande();
    } else {
            $detailsCommandes = [];
        }
        return $this->render('panier/index.html.twig',compact('detailsCommandes'));
    }

    #[Route('/payementPanier', name: '_payementPanier')]
    public function payementPanier(CommandeRepository $commandeRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {
        $etatCreer = $etatRepository->findOneBy(array('id' => 1));
        $etatPayer = $etatRepository->findOneBy(array('id' => 2));
        $now = new DateTime();
        $utilisateur = $this->getUser();
        $heurePreparation = $now->add(new DateInterval('PT30M'));
        $heureLivraison = $heurePreparation->add(new DateInterval('PT30M'));
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );

        if( ($derniereCommande->getEtat()->getId()) === $etatCreer->getId() ) {
            $derniereCommande->setEtat($etatPayer);
            $derniereCommande->setDateHeurePreparation($heurePreparation);
            $derniereCommande->setDateHeureLivraison($heureLivraison);
            $entityManager->persist($derniereCommande);
            $entityManager->flush();
            return $this->redirectToRoute('_accueil');
        }
        return $this->render('panier/index.html.twig');
    }
    #[Route('/suppressionDuPanier/{id}', name: '_suppressionDuPanier')]
    public function suppressionDuPanier($id,DetailCommandeRepository $detailCommandeRepository, EntityManagerInterface $entityManager): Response
    {
        $articleASupprimer = $detailCommandeRepository->findOneBy(array('id' => $id));
        if($articleASupprimer){
            $entityManager->remove($articleASupprimer);
            $entityManager->flush();
            return $this->redirectToRoute('_accueil');
        }
        return $this->render('panier/detail.html.twig');
    }
}
