<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\DetailCommande;
use App\Form\DetailCommandeFormType;
use App\Repository\CommandeRepository;
use App\Repository\EtatRepository;
use App\Repository\ProduitRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    #[Route('/accueil', name: '_accueil')]
    public function index(ProduitRepository $produitRepository): Response
    {
        $pizzas = $produitRepository->findAll();

        return $this->render('accueil/index.html.twig',compact('pizzas'));
    }
    #[Route('/detailPizza/{id}', name: '_detailPizza')]
    public function detailPizza($id, Request $requete, ProduitRepository $produitRepository, CommandeRepository $commandeRepository, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
////////////////////////////////////////// VARIABLES //////////////////////////////////////////////////////////////////////////////////////
        $pizza = $produitRepository->findOneBy(array('id' => $id));
        $detailCommande = new DetailCommande();
        $now = new DateTime();
        $utilisateur = $this->getUser();

        $etatCreer = $etatRepository->findOneBy(array('id' => 1));
        $heurePreparation = $now->add(new DateInterval('PT30M'));
        $heureLivraison = $heurePreparation->add(new DateInterval('PT30M'));

////////////////////////////////////////// FORMULAIRE ET TRAITEMENT //////////////////////////////////////////////////////////////////

        $detailCommande->setProduit($pizza);
        $detailCommandeForm = $this->createForm(DetailCommandeFormType::class, $detailCommande);
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );
        $detailCommandeForm->handleRequest($requete);

        if($detailCommandeForm->isSubmitted() && $detailCommandeForm->isValid()){
        if ($derniereCommande != null){
            $etatDerniereCommande = $derniereCommande->getEtat();
            if($etatDerniereCommande->getId() === $etatCreer->getId()){
                $produitATrouver = false;
                $detailDuPanier = $derniereCommande->getDetailsCommande();
                foreach($detailDuPanier as $detail){
                    if($detail->getProduit() === $pizza){
                        $donneesFormulaire = $detailCommandeForm->getData();
                        $quantite = $donneesFormulaire->getQuantite();
                        $nouvelleQuantite = ($detail->getQuantite())+ $quantite;
                        $detail->setQuantite($nouvelleQuantite);
                        $produitATrouver = true;
                        $derniereCommande->setDateHeureLivraison($heureLivraison);
                        $derniereCommande->setDateHeurePreparation($heurePreparation);
                        $entityManager->persist($derniereCommande);
                        $entityManager->flush();
                        return $this->redirectToRoute('_accueil');
                    }
                }
                if($produitATrouver === false){
//                    dd("je passe dans le produit non trouvé");
                $detailCommande->setCommande($derniereCommande);
                $derniereCommande->setDateHeureLivraison($heureLivraison);
                $derniereCommande->setDateHeurePreparation($heurePreparation);
                $derniereCommande->addDetailsCommande($detailCommande);

                $entityManager->persist($detailCommande);
                $entityManager->persist($derniereCommande);
                $entityManager->flush();
                return $this->redirectToRoute('_accueil');
                }
            } else if ($etatDerniereCommande->getId() >= $etatCreer->getId())
            {
                $commande = $this->creationCommande($etatCreer, $utilisateur, $heureLivraison, $heurePreparation, $detailCommande);
                $this->envoieBaseDeDonnee($entityManager, $detailCommande, $commande);
                return $this->redirectToRoute('_accueil');
            }
        }else if($derniereCommande === null)
        {
            $commande = $this->creationCommande($etatCreer, $utilisateur, $heureLivraison, $heurePreparation, $detailCommande);
            $this->envoieBaseDeDonnee($entityManager, $detailCommande, $commande);
            return $this->redirectToRoute('_accueil');
        }

        }
        return $this->render('panier/detail.html.twig', compact('pizza', 'detailCommandeForm'));
    }

    public function creationCommande($etatCreer, $utilisateur, $heureLivraison, $heurePreparation, $detailCommande){
        $commande = new Commande();

        $commande->setEtat($etatCreer);
        $commande->setCollaborateur($utilisateur);
        $commande->setDateHeureLivraison($heureLivraison);
        $commande->setDateHeurePreparation($heurePreparation);
        $commande->addDetailsCommande($detailCommande);

        return $commande;
    }
     public function envoieBaseDeDonnee($entityManager,$detailCommande, $commande){
         $detailCommande->setCommande($commande);

         $entityManager->persist($detailCommande);
         $entityManager->persist($commande);
         $entityManager->flush();

}
}
