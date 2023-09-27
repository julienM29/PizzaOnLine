<?php

namespace App\Controller;


use App\Repository\CommandeRepository;
use App\Repository\DetailCommandeRepository;
use App\Repository\EtatRepository;
use App\Repository\ProduitRepository;
use App\Repository\TailleProduitRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    #[Route('/panier', name: '_panier')]
    public function index(TailleProduitRepository $tailleProduitRepository, CommandeRepository $commandeRepository, ProduitRepository $produitRepository, EtatRepository $etatRepository): Response
    {
        $utilisateur = $this->getUser();
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );
        $prixDuPanier = 0;
        $tailleLarge = $tailleProduitRepository->findOneBy(array('id' => 2));
        if ($derniereCommande != null) {
            $etatDerniereCommande = $derniereCommande->getEtat();
            if ($etatDerniereCommande->getId() == 1) {
                $detailsCommandes = $derniereCommande->getDetailsCommande();
                foreach ($detailsCommandes as $detail) {
                    $idProduit = $detail->getProduit()->getId();
                    $pizza = $produitRepository->findOneBy(array('id' => $idProduit));
                    if ($detail->getTaille() === $tailleLarge) {
                        $prixDuDetail = 1.20 * ($pizza->getPrix() * $detail->getQuantite());
                    } else {
                        $prixDuDetail = ($pizza->getPrix() * $detail->getQuantite());
                    }

                    $prixDuPanier = ($prixDuPanier + $prixDuDetail);
                }
            } else {
                $detailsCommandes = [];
            }
        }
        return $this->render('panier/index.html.twig', compact('detailsCommandes', 'prixDuPanier'));
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
    public function suppressionDuPanier($id, DetailCommandeRepository $detailCommandeRepository, EntityManagerInterface $entityManager): Response
    {
        $articleASupprimer = $detailCommandeRepository->findOneBy(array('id' => $id));
        if ($articleASupprimer) {
            $entityManager->remove($articleASupprimer);
            $entityManager->flush();
            return $this->redirectToRoute('_accueil');
        }
        return $this->render('panier/detail.html.twig');
    }

    #[Route('/etatPrepare/{id}', name: '_etatPrepare')]
    public function etatPrepare($id, CommandeRepository $commandeRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {
        $article = $commandeRepository->findOneBy(array('id' => $id));
        $etatPreparee = $etatRepository->findOneBy(array('id' => 3));
        if ($article) {
            $article->setEtat($etatPreparee);
            $entityManager->flush();
            return $this->redirectToRoute('_accueil');
        }
        return $this->render('commande/preparationCommande.html.twig');
    }
}
