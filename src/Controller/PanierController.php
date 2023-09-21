<?php

namespace App\Controller;


use App\Repository\CommandeRepository;
use App\Repository\DetailCommandeRepository;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    #[Route('/panier', name: '_panier')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        $lastCommandes = $commandeRepository->findOneBy([], ['id' => 'DESC']);

        $detailsCommandes = $lastCommandes->getDetailsCommande();
        return $this->render('panier/index.html.twig',compact('detailsCommandes'));
    }

    #[Route('/payementPanier', name: '_payementPanier')]
    public function payementPanier(CommandeRepository $commandeRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {
        $lastCommandes = $commandeRepository->findOneBy([], ['id' => 'DESC']);
        $etatCreer = $etatRepository->findOneBy(array('id' => 1));
        $etatPayer = $etatRepository->findOneBy(array('id' => 2));

        if( ($lastCommandes->getEtat()->getId()) === $etatCreer->getId() ) {
            $lastCommandes->setEtat($etatPayer);
            $entityManager->persist($lastCommandes);
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
