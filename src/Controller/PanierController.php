<?php

namespace App\Controller;


use App\Repository\CollaborateurRepository;
use App\Repository\CommandeRepository;
use App\Repository\DetailCommandeRepository;
use App\Repository\EtatRepository;
use App\Repository\ProduitRepository;
use App\Repository\TailleProduitRepository;
use App\Repository\UserMessageRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    #[Route('/panier', name: '_panier')]
    public function index(TailleProduitRepository $tailleProduitRepository, UserMessageRepository $userMessageRepository, CommandeRepository $commandeRepository, ProduitRepository $produitRepository, EtatRepository $etatRepository): Response
    {
        $messagesNonLu = $userMessageRepository->findBy(['checked' => 0]);
        $utilisateur = $this->getUser();
        $etatCreer = $etatRepository->findOneBy(['id' => 1]);
        $pizzas = $produitRepository->findAll();

//////////////////////////////// TOOLTIP ///////////////////////////////////////////////////////////////////////////////////////////
        $result = $this->tooltip($commandeRepository, $tailleProduitRepository, $produitRepository);
        if($result){
            $detailsCommandePanier = $result['detailsCommandePanier'];
            $prixDuPanier = $result['prixDuPanier'];
        } else {
            $detailsCommandePanier = [];
            $prixDuPanier = 0;
        }
//////////////////////////////////////////////////////////////////////////////

        return $this->render('panier/index.html.twig', compact('detailsCommandePanier', 'prixDuPanier', 'pizzas', 'messagesNonLu'));
    }

    #[Route('/payementPanier', name: '_payementPanier')]
    public function payementPanier(ProduitRepository $produitRepository, CommandeRepository $commandeRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {
        $etatCreer = $etatRepository->findOneBy(array('id' => 1));
        $etatPayer = $etatRepository->findOneBy(array('id' => 2));
        $now = new DateTime();
        $utilisateur = $this->getUser();
        $heurePreparation = $now->add(new DateInterval('PT30M'));
        $heureLivraison = $heurePreparation->add(new DateInterval('PT30M'));
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur, 'etat' => '1'],
            ['id' => 'DESC']
        );

        if (($derniereCommande->getEtat()->getId()) === $etatCreer->getId()) {
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
    public function suppressionDuPanier($id, DetailCommandeRepository $detailCommandeRepository, EntityManagerInterface $entityManager, CollaborateurRepository $collaborateurRepository): Response
    {
        $articleASupprimer = $detailCommandeRepository->findOneBy(array('id' => $id));
        $commande = $articleASupprimer->getCommande();
        $idCommande = $commande->getId();
        if ($articleASupprimer) {
            $entityManager->remove($articleASupprimer);
            $entityManager->flush();
            $detailCommandeRestant = $detailCommandeRepository->findBy(['commande' => $idCommande]);
            if (empty($detailCommandeRestant)) {
                $utilisateur = $this->getUser();
                $user = $collaborateurRepository->findOneBy(array('id' => $utilisateur->getId()));
                $user->setPanier(0);
                $entityManager->persist($user);
                $entityManager->flush();
            }
            return $this->redirectToRoute('_accueil');
        }
        return $this->render('panier/detail.html.twig');
    }

    #[Route('/etatPrepare{id}/{idLivreur}', name: '_etatPrepare')]
    public function etatPrepare($id, $idLivreur, CommandeRepository $commandeRepository, CollaborateurRepository $collaborateurRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {
        $commande = $commandeRepository->findOneBy(array('id' => $id));
        $livreur = $collaborateurRepository->findOneBy(array('id' => $idLivreur));
        $etatPreparee = $etatRepository->findOneBy(array('id' => 3));
        if ($commande) {
            $commande->setEtat($etatPreparee);
            $commande->setLivreur($livreur);
            $entityManager->flush();
            return $this->redirectToRoute('_preparationCommande');
        }
        return $this->render('commande/preparationCommande.html.twig');
    }

    public function prixDuPanier($derniereCommande, $tailleProduitRepository, $produitRepository, $detailsCommandePanier)
    {
        $prixDuPanier = 0; // Instancie une variable de prix de panier à 0 qui sera envoyé au twig
        $tailleLarge = $tailleProduitRepository->findOneBy(array('id' => 2)); // récupère la taille large pour une condition (prix)
        if ($derniereCommande != null) { // Si il y a une dernière commande
            $etatDerniereCommande = $derniereCommande->getEtat();
            if ($etatDerniereCommande->getId() == 1) { // Si elle est a l'état créé
                foreach ($detailsCommandePanier as $detail) {
                    $idProduit = $detail->getProduit()->getId();
                    $pizza = $produitRepository->findOneBy(array('id' => $idProduit));
                    if ($detail->getTaille() === $tailleLarge) { // Si il y a une taille large on change le prix
                        $prixDuDetail = (($pizza->getPrix() + 5) * $detail->getQuantite());
                    } else {
                        $prixDuDetail = ($pizza->getPrix() * $detail->getQuantite());
                    }

                    $prixDuPanier = ($prixDuPanier + $prixDuDetail); // Total du panier
                }
            }
        }
        return $prixDuPanier;
    }

    public function tooltip($commandeRepository, $tailleProduitRepository, $produitRepository)
    {
        $result = [];
        $utilisateur = $this->getUser();
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur, 'etat' => '1'],
            ['id' => 'DESC']
        );
        if ($derniereCommande) {
            $detailsCommandePanier = $derniereCommande->getDetailsCommandeTrieesParNomProduit();
            $prixDuPanier = $this->prixDuPanier($derniereCommande, $tailleProduitRepository, $produitRepository, $detailsCommandePanier);

            $result = [
                'detailsCommandePanier' => $detailsCommandePanier,
                'prixDuPanier' => $prixDuPanier,
            ];
        }
        return $result;
    }
}
