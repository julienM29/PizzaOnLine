<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\DetailCommande;
use App\Form\CommandeFormType;
use App\Repository\CollaborateurRepository;
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

        if ($etatDerniereCommande->getId() === $etatCreer->getId()) {

            $detailCommande->setCommande($derniereCommande);

            $derniereCommande->setDateHeureLivraison($heureLivraison);
            $derniereCommande->setDateHeurePreparation($heurePreparation);
            $derniereCommande->addDetailsCommande($detailCommande);

            $entityManager->persist($detailCommande);
            $entityManager->persist($derniereCommande);
            $entityManager->flush();

            return $this->redirectToRoute('_accueil');

        } else if ($etatDerniereCommande->getId() >= $etatCreer->getId()) {
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

    #[Route('/preparationCommande', name: '_preparationCommande')]
    public function preparationCommande(DetailCommandeRepository $detailCommandeRepository, EtatRepository $etatRepository, CommandeRepository $commandeRepository, ProduitRepository $produitRepository, EntityManagerInterface $entityManager): Response
    {
        $etatPayeer = $etatRepository->findOneBy(['id' => 2]);
        $commandes = $commandeRepository->findBy(['etat' => $etatPayeer]);
        $etatPrepare = $etatRepository->findOneBy(array('id' => 3));
        $pizzas = $produitRepository->findAll();
        $commandesAttenteLivraison = $commandeRepository->findBy(['etat' => $etatPrepare]);
        $premierePizza = null;
        if($commandes){
            $premierePizza = $commandes[0];

        }
        $commandeDetails = [];
        $commandeDetailsLivraison =[];
        foreach ($commandes as $commande) {
            $detailsCommande = $detailCommandeRepository->findBy(['commande' => $commande]);
            $commandeDetails[$commande->getId()] = $detailsCommande;
        }
        foreach ($commandesAttenteLivraison as $commandeAttenteLivraison) {
            $detailsCommandeEnAttente = $detailCommandeRepository->findBy(['commande' => $commandeAttenteLivraison]);
            $commandeDetailsLivraison[$commandeAttenteLivraison->getId()] = $detailsCommandeEnAttente;
        }
        return $this->render('commande/preparationCommande.html.twig', [
            'commandes' => $commandes,
            'commandeDetails' => $commandeDetails,
            'pizzas' => $pizzas,
            'commandeDetailsLivraison' => $commandeDetailsLivraison,
            'premierePizza' => $premierePizza
        ]);
    }
    #[Route('/livrer/{id}', name: '_livrer')]
    public function livrerCommande($id,EntityManagerInterface $entityManager, CommandeRepository $commandeRepository, EtatRepository $etatRepository): Response
    {
        $commande = $commandeRepository->findOneBy(array('id' => $id));
        $etatEnLivraison = $etatRepository->findOneBy(array('id' => 4));

        if ($commande) {
            $commande->setEtat($etatEnLivraison);
            $entityManager->persist($commande);
            $entityManager->flush();
            return $this->redirectToRoute('_preparationCommande');
        }

        return $this->render('commande/preparationCommande.html.twig');
    }
    #[Route('/finDeLivraison/{id}', name: '_finDeLivraison')]
    public function finDeLivraison($id,EntityManagerInterface $entityManager, CommandeRepository $commandeRepository, EtatRepository $etatRepository): Response
    {
        $commande = $commandeRepository->findOneBy(array('id' => $id));
        $etatLivree = $etatRepository->findOneBy(array('id' => 5));

        if ($commande) {
            $commande->setEtat($etatLivree);
            $entityManager->persist($commande);
            $entityManager->flush();
            return $this->redirectToRoute('_preparationCommande');
        }

        return $this->render('livraisonCommandeLivreur.html.twig');
    }
    #[Route('/livraisonCommande/{id}', name: '_livraisonCommande')]
    public function livraisonCommande($id,CollaborateurRepository $collaborateurRepository, CommandeRepository $commandeRepository, EtatRepository $etatRepository, ProduitRepository $produitRepository, DetailCommandeRepository $detailCommandeRepository): Response
    {

        $client = $collaborateurRepository->findOneBy(array('id' => $id));
        $livreur = $collaborateurRepository->findUsersByRole('ROLE_LIVREUR');
        $allCommande = $commandeRepository->findAll();
        $etatLivraison = $etatRepository->findOneBy(array('id' => 4));
        $commandesClient = $commandeRepository->findBy([
            'collaborateur' => $client,
            'etat' => $etatLivraison,
        ]);
        $commandesLivreur = $commandeRepository->findBy([
            'etat' => $etatLivraison,
        ]);
        $commandeVide = null;
        $premiereCommandeLivreur = null;
        $premiereAdresseLivreur = null;
        if( empty($commandesClient)){
            $commandeVide = [];
        }
        if($commandesLivreur){
            $premiereCommandeLivreur = $commandesLivreur[0];
            $premiereAdresseLivreur = $premiereCommandeLivreur->getCollaborateur()->getAdresse();
        }
        $commandeDetailsClient = [];
        $commandeDetailsLivreur = [];
        $idClients = [];

        foreach ($commandesClient as $commandeClient) {
            $detailsCommande = $detailCommandeRepository->findBy(['commande' => $commandeClient]);
            $commandeDetailsClient[$commandeClient->getId()] = $detailsCommande;
        }
        foreach ($commandesLivreur as $commandeLivreur) {
            $detailsCommande = $detailCommandeRepository->findBy(['commande' => $commandeLivreur]);
            $idClient = $commandeLivreur->getCollaborateur()->getId();
            $idClients[]= $idClient;
            $commandeDetailsLivreur[$commandeLivreur->getId()] = $detailsCommande;
        }
        return $this->render('livraisonCommandeLivreur.html.twig', compact( 'commandeDetailsClient','commandeDetailsLivreur', 'idClients','allCommande','premiereAdresseLivreur', 'commandeVide', 'livreur'));
    }
    #[Route('/livraisonClient/{id}', name: '_livraisonClient')]
    public function livraisonClient($id,CollaborateurRepository $collaborateurRepository, CommandeRepository $commandeRepository, EtatRepository $etatRepository, ProduitRepository $produitRepository, DetailCommandeRepository $detailCommandeRepository): Response
    {

        $client = $collaborateurRepository->findOneBy(array('id' => $id));
        $livreur = $collaborateurRepository->findUsersByRole('ROLE_LIVREUR');

        $allCommande = $commandeRepository->findAll();
        $etatLivraison = $etatRepository->findOneBy(array('id' => 4));
        $commandesClient = $commandeRepository->findBy([
            'collaborateur' => $client,
            'etat' => $etatLivraison,
        ]);
        $commandesLivreur = $commandeRepository->findBy([
            'etat' => $etatLivraison,
        ]);
        $commandeVide = null;
        $premiereCommandeLivreur = null;
        $premiereAdresseLivreur = null;
        if( empty($commandesClient)){
            $commandeVide = [];
        }
        if($commandesLivreur){
            $premiereCommandeLivreur = $commandesLivreur[0];
            $premiereAdresseLivreur = $premiereCommandeLivreur->getCollaborateur()->getAdresse();
        }
        $commandeDetailsClient = [];
        $commandeDetailsLivreur = [];
        $idClients = [];

        foreach ($commandesClient as $commandeClient) {
            $detailsCommande = $detailCommandeRepository->findBy(['commande' => $commandeClient]);
            $commandeDetailsClient[$commandeClient->getId()] = $detailsCommande;
        }
        foreach ($commandesLivreur as $commandeLivreur) {
            $detailsCommande = $detailCommandeRepository->findBy(['commande' => $commandeLivreur]);
            $idClient = $commandeLivreur->getCollaborateur()->getId();
            $idClients[]= $idClient;
            $commandeDetailsLivreur[$commandeLivreur->getId()] = $detailsCommande;
        }
        return $this->render('commande/livraisonClient.html.twig', compact( 'commandeDetailsClient','commandeDetailsLivreur', 'idClients','allCommande','premiereAdresseLivreur', 'commandeVide', 'livreur'));
    }
    #[Route('/commandeEnCours', name: '_commandeEnCours')]
    public function commandeEnCours(EntityManagerInterface $entityManager,ProduitRepository $produitRepository, CommandeRepository $commandeRepository, EtatRepository $etatRepository, CollaborateurRepository $collaborateurRepository, DetailCommandeRepository $detailCommandeRepository): Response
    {

        $client = $collaborateurRepository->findOneBy(array('id' => $this->getUser()->getId()));
        $etatLivraison = $etatRepository->findOneBy(array('id' => 4));
        $pizzas = $produitRepository->findAll();
        $commandesClient = $commandeRepository->findBy([
            'collaborateur' => $client,
        ]);
        $commandeDetailsClient = [];
        $etatsCommandes=[];
        foreach ($commandesClient as $commandeClient) {
            $detailsCommande = $detailCommandeRepository->findBy(['commande' => $commandeClient]);
            $etatCommande = $commandeClient->getEtat();
            $commandeDetailsClient[] = $detailsCommande;
            $etatsCommandes[]=$etatCommande;
        }
        return $this->render('commande/commandeEnCours.html.twig', compact('commandesClient','commandeDetailsClient','pizzas','etatsCommandes'));
    }
}
