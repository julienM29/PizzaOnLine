<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\DetailCommande;
use App\Repository\CommandeRepository;
use App\Repository\DetailCommandeRepository;
use App\Repository\EtatRepository;
use App\Repository\ProduitRepository;
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
        $lastCommandes = $commandeRepository->findOneBy([], ['id' => 'DESC']);

        $detailsCommandes = $lastCommandes->getDetailsCommande();
        return $this->render('panier/index.html.twig',compact('detailsCommandes'));
    }
    #[Route('/detailPizza/{id}', name: '_detailPizza')]
    public function detailPizza($id, ProduitRepository $produitRepository): Response
    {
        $pizza = $produitRepository->findOneBy(array('id' => $id));
        return $this->render('panier/detail.html.twig', compact('pizza'));
    }
    #[Route('/ajoutPanier/{id}', name: '_ajoutPanier')]
    public function ajoutPanier($id, ProduitRepository $produitRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager, CommandeRepository $commandeRepository): Response
    {

        $pizza = $produitRepository->findOneBy(array('id' => $id));
        $detailCommande = new DetailCommande();
        $now = new DateTime();
        $derniereCommande = $commandeRepository->findOneBy([], ['id' => 'DESC']);
        $etatDerniereCommande = $derniereCommande->getEtat();
        $etatCreer = $etatRepository->findOneBy(array('id' => 1));
//        dd( $etatDerniereCommande->getId());
//        dd($etatCreer->getId());

        $heurePreparation = $now->add(new DateInterval('PT30M'));
        $heureLivraison = $heurePreparation->add(new DateInterval('PT30M'));

        $detailCommande->setProduit($pizza);
        $detailCommande->setQuantite(1);

        if( $etatDerniereCommande->getId() === $etatCreer->getId() ){

            $detailCommande->setCommande($derniereCommande);

            $derniereCommande->setDateHeureLivraison($heureLivraison);
            $derniereCommande->setDateHeurePreparation($heurePreparation);
            $derniereCommande->addDetailsCommande($detailCommande);

            $entityManager->persist($detailCommande);
            $entityManager->persist($derniereCommande);
            $entityManager->flush();

            return $this->redirectToRoute('_accueil');

        } else if ($etatDerniereCommande->getId() >= $etatCreer->getId()){
            $commande = new Commande();

            $commande->setEtat($etatCreer);
            $commande->setDateHeureLivraison($heureLivraison);
            $commande->setDateHeurePreparation($heurePreparation);
            $commande->addDetailsCommande($detailCommande);

            $detailCommande->setCommande($commande);

            $entityManager->persist($detailCommande);
            $entityManager->persist($commande);
            $entityManager->flush();
            return $this->redirectToRoute('_accueil');
        }


        return $this->render('panier/detail.html.twig', compact('pizza'));
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
