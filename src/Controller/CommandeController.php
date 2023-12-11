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
use App\Repository\TailleProduitRepository;
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
    public function preparationCommande(DetailCommandeRepository $detailCommandeRepository,TailleProduitRepository $tailleProduitRepository, EtatRepository $etatRepository, CommandeRepository $commandeRepository, ProduitRepository $produitRepository, EntityManagerInterface $entityManager): Response
    {
        $etatPayeer = $etatRepository->findOneBy(['id' => 2]);
        $commandes = $commandeRepository->findBy(['etat' => $etatPayeer]);
        $etatPrepare = $etatRepository->findOneBy(array('id' => 3));
        $pizzas = $produitRepository->findAll();
        $commandesAttenteLivraison = $commandeRepository->findBy(['etat' => $etatPrepare]);
        $utilisateur = $this->getUser();
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );
        $detailsCommandePanier = $derniereCommande->getDetailsCommande();
        $prixDuPanier = $this->prixDuPanier($derniereCommande, $tailleProduitRepository, $produitRepository,$detailsCommandePanier);


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
            'premierePizza' => $premierePizza,
            'detailsCommandePanier' => $detailsCommandePanier,
            'prixDuPanier' => $prixDuPanier
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
    public function livraisonCommande($id,CollaborateurRepository $collaborateurRepository,TailleProduitRepository $tailleProduitRepository, CommandeRepository $commandeRepository, EtatRepository $etatRepository, ProduitRepository $produitRepository, DetailCommandeRepository $detailCommandeRepository): Response
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
        $utilisateur = $this->getUser();
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );
        $detailsCommandePanier = $derniereCommande->getDetailsCommande();
        $prixDuPanier = $this->prixDuPanier($derniereCommande, $tailleProduitRepository, $produitRepository,$detailsCommandePanier);


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
        return $this->render('commande/livraisonCommandeLivreur.html.twig', compact( 'commandeDetailsClient','commandeDetailsLivreur', 'idClients','allCommande','premiereAdresseLivreur', 'commandeVide', 'livreur', 'detailsCommandePanier', 'prixDuPanier'));
    }
    #[Route('/livraisonClient/{id}', name: '_livraisonClient')]
    public function livraisonClient($id,CollaborateurRepository $collaborateurRepository,TailleProduitRepository $tailleProduitRepository, CommandeRepository $commandeRepository, EtatRepository $etatRepository, ProduitRepository $produitRepository, DetailCommandeRepository $detailCommandeRepository): Response
    {

        $client = $collaborateurRepository->findOneBy(array('id' => $id));
        $livreur = $collaborateurRepository->findUsersByRole('ROLE_LIVREUR');
        $etatLivraison = $etatRepository->findOneBy(array('id' => 4));

        $commandesClient = $commandeRepository->findBy([
            'collaborateur' => $client,
            'etat' => $etatLivraison,
        ]);
        $utilisateur = $this->getUser();
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );
        $detailsCommandePanier = $derniereCommande->getDetailsCommande();
        $prixDuPanier = $this->prixDuPanier($derniereCommande, $tailleProduitRepository, $produitRepository,$detailsCommandePanier);


        $premiereCommandeLivreur = $commandesClient[0];
        $premiereAdresseLivreur = $premiereCommandeLivreur->getCollaborateur()->getAdresse();

        $commandeDetailsClient = [];

        foreach ($commandesClient as $commandeClient) {
            $detailsCommande = $detailCommandeRepository->findBy(['commande' => $commandeClient]);
            $commandeDetailsClient[$commandeClient->getId()] = $detailsCommande;
            $numeroLivreur = $commandeClient->getLivreur()->getTelephone();
            $numeroLivreurAvecEspaces = chunk_split($numeroLivreur, 2, ' ');
        }

        return $this->render('commande/livraisonClient.html.twig', compact( 'commandeDetailsClient','commandesClient', 'premiereAdresseLivreur',  'livreur', 'detailsCommandePanier', 'prixDuPanier', 'utilisateur','numeroLivreurAvecEspaces'));
    }
    #[Route('/commandeEnCours', name: '_commandeEnCours')]
    public function commandeEnCours(EntityManagerInterface $entityManager, TailleProduitRepository $tailleProduitRepository,ProduitRepository $produitRepository, CommandeRepository $commandeRepository, EtatRepository $etatRepository, CollaborateurRepository $collaborateurRepository, DetailCommandeRepository $detailCommandeRepository): Response
    {

        $client = $collaborateurRepository->findOneBy(array('id' => $this->getUser()->getId()));
        $etatLivraison = $etatRepository->findOneBy(array('id' => 4));
        $pizzas = $produitRepository->findAll();
        $commandesClient = $commandeRepository->findBy([
            'collaborateur' => $client,
        ]);
        $utilisateur = $this->getUser();
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );
        $detailsCommandePanier = $derniereCommande->getDetailsCommande();
        $prixDuPanier = $this->prixDuPanier($derniereCommande, $tailleProduitRepository, $produitRepository,$detailsCommandePanier);
        $commandeDetailsClient = [];
        $etatsCommandes = [];
        foreach ($commandesClient as $commandeClient) {
            $detailsCommande = $detailCommandeRepository->findBy(['commande' => $commandeClient]);
            $commandeDetailsClient[] = $detailsCommande;

            $etatCommande = $commandeClient->getEtat();
            $etatString = '';

            switch ($etatCommande->getId()) {
                case 1:
                    $etatString = "En attente de paiement";
                    break;
                case 2:
                    $etatString = "En préparation";
                    break;
                case 3:
                    $etatString = "En attente de livraison";
                    break;
                case 4:
                    $etatString = "En livraison";
                    break;
                case 5:
                    $etatString = "Pizza livrée";
                    break;
                default:
                    $etatString = "État inconnu";
                    break;
            }

            $etatsCommandes[] = $etatString;
        }

        return $this->render('commande/commandeEnCours.html.twig', compact('commandesClient','commandeDetailsClient','pizzas','etatsCommandes','detailsCommandePanier', 'prixDuPanier'));
    }
    public function prixDuPanier ($derniereCommande, $tailleProduitRepository, $produitRepository,$detailsCommandePanier){
        $prixDuPanier = 0; // Instancie une variable de prix de panier à 0 qui sera envoyé au twig
        $tailleLarge = $tailleProduitRepository->findOneBy(array('id' => 2)); // récupère la taille large pour une condition (prix)
        if ($derniereCommande != null) { // Si il y a une dernière commande
            $etatDerniereCommande = $derniereCommande->getEtat();
            if ($etatDerniereCommande->getId() == 1) { // Si elle est a l'état créé
                foreach ($detailsCommandePanier as $detail) {
                    $idProduit = $detail->getProduit()->getId();
                    $pizza = $produitRepository->findOneBy(array('id' => $idProduit));
                    if ($detail->getTaille() === $tailleLarge) { // Si il y a une taille large on change le prix
                        $prixDuDetail = 5 + ($pizza->getPrix() * $detail->getQuantite());
                    } else {
                        $prixDuDetail = ($pizza->getPrix() * $detail->getQuantite());
                    }

                    $prixDuPanier = ($prixDuPanier + $prixDuDetail); // Total du panier
                }
            } }
        return $prixDuPanier;
    }
}
