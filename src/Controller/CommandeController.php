<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\DetailCommande;
use App\Form\CommandeFormType;
use App\Repository\CommandeRepository;
use App\Repository\EtatRepository;
use App\Repository\ProduitRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/commande', name: '_commande')]
    public function index(EtatRepository $etatRepository): Response
    {
        $etatCreer = $etatRepository->findOneBy(array('id' => 1));
        $commande = new Commande();
        $commande->setEtat($etatCreer);
        $commandeForm = $this->createForm(CommandeFormType::class, $commande);
        return $this->render('commande/index.html.twig', compact('commandeForm'));
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
}
