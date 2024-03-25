<?php

namespace App\Controller;

use App\Form\ModificationMotDePasseFormType;
use App\Form\ModificationProfilFormType;
use App\Form\ProfilFormType;
use App\Repository\CollaborateurRepository;
use App\Repository\CommandeRepository;
use App\Repository\EtatRepository;
use App\Repository\IngredientRepository;
use App\Repository\ProduitRepository;
use App\Repository\TailleProduitRepository;
use App\Repository\UserMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil/{id}', name: '_profil')]
    public function index($id, CollaborateurRepository $collaborateurRepository, UserMessageRepository $userMessageRepository, CommandeRepository $commandeRepository, TailleProduitRepository $tailleProduitRepository, ProduitRepository $produitRepository): Response
    {
        $messagesNonLu = $userMessageRepository->findBy(['checked' => 0]);
        $utilisateur = $this->getUser();
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
        $user = $collaborateurRepository->findOneBy(array('id' => $id));
        $numero = $user->getTelephone();
        $numeroAvecEspaces = chunk_split($numero, 2, ' ');
        return $this->render('profil/index.html.twig', compact('user', 'detailsCommandePanier', 'prixDuPanier', 'numeroAvecEspaces', 'messagesNonLu'));
    }

    #[Route('/modificationProfil/{id}', name: '_modificationProfil')]
    public function modificationProfil($id, CommandeRepository $commandeRepository, UserMessageRepository $userMessageRepository, TailleProduitRepository $tailleProduitRepository, ProduitRepository $produitRepository, CollaborateurRepository $collaborateurRepository, Request $requete, EntityManagerInterface $entityManager): Response
    {
        $messagesNonLu = $userMessageRepository->findBy(['checked' => 0]);
        $user = $collaborateurRepository->findOneBy(array('id' => $id));
        $utilisateurConnecter = $this->getUser();
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
        $profilForm = $this->createForm(ModificationProfilFormType::class, $user);
        $profilForm->handleRequest($requete);

        if ($profilForm->isSubmitted() && $profilForm->isValid()) {

            $entityManager->persist($user);
            $entityManager->flush();
            if ($utilisateurConnecter !== null) {
                // Vérifiez ensuite le rôle de l'utilisateur grâce à al fonction isGranted qui fonctionne pareil que pour les twig
                if ($this->isGranted('ROLE_GERANT')) {
                    return $this->redirectToRoute('_gestionDesRoles'); // Si c'est le gérant on le renvoie vers tout les profils utilisateur
                } else {
                    return $this->redirectToRoute('_profil', ['id' => $id]); // Si c'est un utilisateur on le renvoie vers son profil
                }
            }
        }

        return $this->render('profil/modificationProfil.html.twig', compact('user', 'profilForm', 'detailsCommandePanier', 'prixDuPanier', 'messagesNonLu'));
    }

    #[Route('/modificationMotDePasse/{id}', name: '_modificationMotDePasse')]
    public function modificationMotDePasse($id, UserPasswordHasherInterface $userPasswordHasher, UserMessageRepository $userMessageRepository, CollaborateurRepository $collaborateurRepository, UserPasswordHasherInterface $passwordHasher, CommandeRepository $commandeRepository, TailleProduitRepository $tailleProduitRepository, ProduitRepository $produitRepository, Request $requete, EntityManagerInterface $entityManager): Response
    {
        $messagesNonLu = $userMessageRepository->findBy(['checked' => 0]);
        $user = $collaborateurRepository->findOneBy(array('id' => $id));
        $utilisateurConnecter = $this->getUser();
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

        $mdpForm = $this->createForm(ModificationMotDePasseFormType::class);
        $mdpForm->handleRequest($requete);

        if ($mdpForm->isSubmitted() && $mdpForm->isValid()) {
            $newPassword = $mdpForm->get('newPassword')->getData();
            $ancienPassword = $mdpForm->getData()['password'];
            if ($userPasswordHasher->isPasswordValid($user, $ancienPassword)) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $newPassword
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Le mot de passe a bien été modifié.');
                return $this->redirectToRoute('_profil', ['id' => $id]);
            } else {
                $this->addFlash('warning', 'Le mot de passe renseigné est incorrect');
                $this->addFlash('error', 'Mot de passe incorrect');
            }
        }
        return $this->render('profil/modificationPassword.html.twig', compact('mdpForm', 'prixDuPanier', 'detailsCommandePanier', 'messagesNonLu'));
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
                        $prixDuDetail = 5 + ($pizza->getPrix() * $detail->getQuantite());
                    } else {
                        $prixDuDetail = ($pizza->getPrix() * $detail->getQuantite());
                    }

                    $prixDuPanier = ($prixDuPanier + $prixDuDetail); // Total du panier
                }
            }
        }
        return $prixDuPanier;
    }

    #[Route('/profilsClient', name: '_profilsClient')]
    public function profilsClient(Request $request, IngredientRepository $ingredientRepository, EtatRepository $etatRepository, CommandeRepository $commandeRepository, EntityManagerInterface $entityManager)
    {
        $etatLivraison = $etatRepository->findOneBy(array('id' => 4)); // État livré
        $commandesALivrer = $commandeRepository->findBy(['etat' => $etatLivraison,]); // Récupération des commandes prêtes à être livrées
        $adresseLivraison = [];

        foreach ($commandesALivrer as $index => $commande) {
            $adresse = $commande->getCollaborateur()->getAdresse(); // Récupération de l'adresse de la commande
            $adresseLivraison[$index] = $adresse; // Tableau associatif

        }
        return new JsonResponse(['adresses' => $adresseLivraison]);
    }

    #[Route('/coordonneesClient', name: '_coordonneesClient')]
    public function coordonneesClient(Request $request, IngredientRepository $ingredientRepository, EtatRepository $etatRepository, CommandeRepository $commandeRepository, EntityManagerInterface $entityManager)
    {
        $etatLivraison = $etatRepository->findOneBy(array('id' => 4)); // État livré
        $commandesALivrer = $commandeRepository->findBy(['etat' => $etatLivraison,]); // Récupération des commandes prêtes à être livrées
        $adresseLivraison = [];

        foreach ($commandesALivrer as $index => $commande) {
            $latitude = $commande->getCollaborateur()->getLatitude();
            $longitude = $commande->getCollaborateur()->getLongitude(); // Récupération de l'adresse de la commande
            $adresseLivraison[$index] = [$latitude, $longitude]; // Tableau associatif
        }
        return new JsonResponse(['adresses' => $adresseLivraison]);
    }

    #[Route('/profilDuClient', name: '_profilDuClient')]
    public function profilDuClient(Request $request, IngredientRepository $ingredientRepository, EtatRepository $etatRepository, CommandeRepository $commandeRepository, EntityManagerInterface $entityManager)
    {
        $etatLivraison = $etatRepository->findOneBy(array('id' => 4)); // État en livraison
        $idClient = $this->getUser();
        $commandesALivrer = $commandeRepository->findBy(['etat' => $etatLivraison,
            'collaborateur' => $idClient]); // Récupération des commandes prêtes à être livrées
        $adresseLivraison = [];

        foreach ($commandesALivrer as $index => $commande) {
            $adresse = $commande->getCollaborateur()->getAdresse(); // Récupération de l'adresse de la commande
            $adresseLivraison[$index] = $adresse; // Tableau associatif

        }
        return new JsonResponse(['adressesDuClient' => $adresseLivraison]);
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
