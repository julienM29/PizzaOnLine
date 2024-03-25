<?php

namespace App\Controller;

use App\Entity\CategorieIngredient;
use App\Entity\Ingredient;
use App\Entity\Produit;
use App\Form\IngredientFormType;
use App\Form\ProduitFormType;
use App\Repository\CategorieIngredientRepository;
use App\Repository\CollaborateurRepository;
use App\Repository\CommandeRepository;
use App\Repository\IngredientRepository;
use App\Repository\ProduitRepository;
use App\Repository\TailleProduitRepository;
use App\Repository\UserMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class GerantController extends AbstractController
{
    #[Route('/gerant', name: '_gerant')]
    public function index(CommandeRepository $commandeRepository,UserMessageRepository $userMessageRepository, TailleProduitRepository $tailleProduitRepository, ProduitRepository $produitRepository): Response
    {
        $messagesNonLu = $userMessageRepository->findBy(['checked' => 0]);
        $result = $this->tooltip($commandeRepository, $tailleProduitRepository, $produitRepository);
        $detailsCommandePanier = $result['detailsCommandePanier'];
        $prixDuPanier = $result['prixDuPanier'];


        return $this->render('gerant/index.html.twig', compact('prixDuPanier', 'detailsCommandePanier','messagesNonLu'));
    }

    ////////////////////////////////////////////////////// CREATION /////////////////////////////////////////////////////////////////////////////////////////////
    #[Route('/ingredient', name: '_ingredient')]
    public function creationIngredient(Request $requete, EntityManagerInterface $entityManager,UserMessageRepository $userMessageRepository, IngredientRepository $ingredientRepository, CommandeRepository $commandeRepository, TailleProduitRepository $tailleProduitRepository, ProduitRepository $produitRepository): Response
    {
        $messagesNonLu = $userMessageRepository->findBy(['checked' => 0]);
        $ingredient = new Ingredient();
        $allIngredient = $ingredientRepository->findAll();
        $count = count($allIngredient) - 1;
        $result = $this->tooltip($commandeRepository, $tailleProduitRepository, $produitRepository);
        $detailsCommandePanier = $result['detailsCommandePanier'];
        $prixDuPanier = $result['prixDuPanier'];

        $ingredientForm = $this->createForm(IngredientFormType::class, $ingredient);
        $ingredientForm->handleRequest($requete);

        if ($ingredientForm->isSubmitted() && $ingredientForm->isValid()) {
            $entityManager->persist($ingredient);
            $entityManager->flush();
            return $this->redirectToRoute('_ingredient');
        }

        return $this->render('gerant/creationIngredient.html.twig', compact('ingredientForm', 'allIngredient', 'count', 'prixDuPanier', 'detailsCommandePanier','messagesNonLu'));
    }

    #[Route('/produit', name: '_produit')]
    public function creationProduit(Request $requete, CommandeRepository $commandeRepository,UserMessageRepository $userMessageRepository, IngredientRepository $ingredientRepository, CategorieIngredientRepository $categorieIngredientRepository, SluggerInterface $slugger, TailleProduitRepository $tailleProduitRepository, ProduitRepository $produitRepository, EntityManagerInterface $entityManager): Response
    {
        $messagesNonLu = $userMessageRepository->findBy(['checked' => 0]);
        $produit = new Produit();
        $produitForm = $this->createForm(ProduitFormType::class, $produit);
        $result = $this->tooltip($commandeRepository, $tailleProduitRepository, $produitRepository);
        $detailsCommandePanier = $result['detailsCommandePanier'];
        $prixDuPanier = $result['prixDuPanier'];
        $categories = $categorieIngredientRepository->findAll();
        $ingredients = $ingredientRepository->findAll();
        $categoriesByIngredient = [];

        foreach ($ingredients as $ingredient) {
            $categoryName = $ingredient->getCategorie()->getLibelle();
            $categoriesByIngredient[$ingredient->getId()] = $categoryName;
        }

        $produitForm->handleRequest($requete);

        if ($produitForm->isSubmitted() && $produitForm->isValid()) {
            $file = $produitForm->get('urlImage')->getData();
            if ($file) {
                $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFileName);
                $newFilename = $safeFilename . '.' . $file->guessExtension();

                $file->move(
                    $this->getParameter('uploaded_images_directory'),
                    $newFilename
                );

            }
            $produit->setUrlImage($newFilename);
            $produit->setDisponible(true);
            $ingredientsPizza = $produitForm->get('ingredients')->getData();
            $type = $produitForm->get('typeProduit')->getData();
            if($type === '1'){
                foreach ($ingredientsPizza as $ingredient) {
                    $QuantiteIngredient = $ingredient->getQuantite();
                    if ($QuantiteIngredient == 0) {
                        $produit->setDisponible(false);
                    } else {
                        $produit->setDisponible(true);
                    }
                }
            } elseif ($type === '2'){
                $produit->setDisponible(true);
            }

            $entityManager->persist($produit);
            $entityManager->flush();
            return $this->redirectToRoute('_gerant');
        }

        return $this->render('gerant/creationProduit.html.twig', compact('produitForm', 'ingredients', 'detailsCommandePanier', 'prixDuPanier', 'categories', 'categoriesByIngredient','messagesNonLu'));
    }

    ////////////////////////////////////////////////////// MODIFICATION /////////////////////////////////////////////////////////////////////////////////////////////
    #[Route('/listeProduit', name: '_listeProduit')]
    public function listeProduit(Request $requete, EntityManagerInterface $entityManager,UserMessageRepository $userMessageRepository, ProduitRepository $produitRepository, CommandeRepository $commandeRepository, TailleProduitRepository $tailleProduitRepository): Response
    {
        $messagesNonLu = $userMessageRepository->findBy(['checked' => 0]);
        $allPizzas = $produitRepository->findAll();
        $result = $this->tooltip($commandeRepository, $tailleProduitRepository, $produitRepository);
        $detailsCommandePanier = $result['detailsCommandePanier'];
        $prixDuPanier = $result['prixDuPanier'];
        return $this->render('gerant/listeProduit.html.twig', compact('allPizzas', 'detailsCommandePanier', 'prixDuPanier','messagesNonLu'));
    }

    #[Route('/modificationProduit/{id}', name: '_modificationProduit')]
    public function modificationProduit($id, Request $requete,IngredientRepository $ingredientRepository, CategorieIngredientRepository $categorieIngredientRepository,UserMessageRepository $userMessageRepository, CommandeRepository $commandeRepository, SluggerInterface $slugger, TailleProduitRepository $tailleProduitRepository, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): Response
    {
        $messagesNonLu = $userMessageRepository->findBy(['checked' => 0]);
        $result = $this->tooltip($commandeRepository, $tailleProduitRepository, $produitRepository);
        $detailsCommandePanier = $result['detailsCommandePanier'];
        $prixDuPanier = $result['prixDuPanier'];
        $pizza = $produitRepository->findOneBy(array('id' => $id));

        $produitForm = $this->createForm(ProduitFormType::class, $pizza);
        $categories = $categorieIngredientRepository->findAll();
        $ingredients = $ingredientRepository->findAll();
        $categoriesByIngredient = [];

        foreach ($ingredients as $ingredient) {
            $categoryName = $ingredient->getCategorie()->getLibelle();
            $categoriesByIngredient[$ingredient->getId()] = $categoryName;
        }

        $produitForm->handleRequest($requete);

        if ($produitForm->isSubmitted() && $produitForm->isValid()) {
            $file = $produitForm->get('urlImage')->getData();
            if ($file) {
                $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFileName);
                $newFilename = $safeFilename . '.' . $file->guessExtension();

                $file->move(
                    $this->getParameter('uploaded_images_directory'),
                    $newFilename
                );

            }
            $pizza->setUrlImage($newFilename);

            $entityManager->persist($pizza);
            $entityManager->flush();
            return $this->redirectToRoute('_listeProduit');
        }

        return $this->render('gerant/modificationProduit.html.twig', compact('produitForm', 'pizza', 'detailsCommandePanier', 'prixDuPanier','messagesNonLu','categories', 'categoriesByIngredient'));
    }

    #[Route('/supprimerProduit/{id}', name: '_supprimerProduit')]
    public function suppressionProduit($id, ProduitRepository $produitRepository, EntityManagerInterface $entityManager): Response
    {
        $pizza = $produitRepository->findOneBy(array('id' => $id));
        if ($pizza) {
            $entityManager->remove($pizza);
            $entityManager->flush();
            return $this->redirectToRoute('_gerant');
        }
        return $this->render('gerant/listeProduit.html.twig');
    }

    ////////////////////////////////////////////////////// Gestion /////////////////////////////////////////////////////////////////////////////////////////////
    #[Route('/gestionDesRoles', name: '_gestionDesRoles')]
    public function gestionDesRoles(CollaborateurRepository $collaborateurRepository,UserMessageRepository $userMessageRepository, CommandeRepository $commandeRepository, TailleProduitRepository $tailleProduitRepository, ProduitRepository $produitRepository, EntityManagerInterface $entityManager): Response
    {
        $messagesNonLu = $userMessageRepository->findBy(['checked' => 0]);
        $collaborateurs = $collaborateurRepository->findAll();
        $result = $this->tooltip($commandeRepository, $tailleProduitRepository, $produitRepository);
        $detailsCommandePanier = $result['detailsCommandePanier'];
        $prixDuPanier = $result['prixDuPanier'];
        return $this->render('gerant/gestionDesRoles.html.twig', compact('collaborateurs', 'prixDuPanier', 'detailsCommandePanier','messagesNonLu'));
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

    #[Route('/gestionStock', name: '_gestionStock')]
    public function gestionStock(CommandeRepository $commandeRepository,UserMessageRepository $userMessageRepository, CategorieIngredientRepository $categorieIngredientRepository, IngredientRepository $ingredientRepository, TailleProduitRepository $tailleProduitRepository, ProduitRepository $produitRepository)
    {
        $messagesNonLu = $userMessageRepository->findBy(['checked' => 0]);
        $ingredients = $ingredientRepository->findAll();
        $categories = $categorieIngredientRepository->findAll();


        $result = $this->tooltip($commandeRepository, $tailleProduitRepository, $produitRepository);
        $detailsCommandePanier = $result['detailsCommandePanier'];
        $prixDuPanier = $result['prixDuPanier'];

        return $this->render('gerant/gestionStock.html.twig', compact('prixDuPanier', 'detailsCommandePanier', 'categories', 'ingredients','messagesNonLu'));
    }

    public function tooltip($commandeRepository, $tailleProduitRepository, $produitRepository)
    {
        $utilisateur = $this->getUser();
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );
        $detailsCommandePanier = $derniereCommande->getDetailsCommandeTrieesParNomProduit();
        $prixDuPanier = $this->prixDuPanier($derniereCommande, $tailleProduitRepository, $produitRepository, $detailsCommandePanier);
        $result = [];
        $result = [
            'detailsCommandePanier' => $detailsCommandePanier,
            'prixDuPanier' => $prixDuPanier,
        ];
        return $result;
    }

    #[Route('/changementQuantite', name: '_changementQuantite')]
    public function changementQuantite(Request $request, IngredientRepository $ingredientRepository, EntityManagerInterface $entityManager)
    {
        $data = json_decode($request->getContent(), true); // Convertir les données JSON en tableau associatif

        if ($data) {
            foreach ($data as $key => $value) {
                $ingredient = $ingredientRepository->findOneBy(['id' => $key]);
                $ingredient->setQuantite($value);
                $entityManager->flush();
            }
            return new JsonResponse(['success' => true]);
        }
    }
}
