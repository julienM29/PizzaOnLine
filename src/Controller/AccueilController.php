<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\DetailCommande;
use App\Entity\Etat;
use App\Form\DetailCommandeFormType;
use App\Repository\CollaborateurRepository;
use App\Repository\CommandeRepository;
use App\Repository\EtatRepository;
use App\Repository\IngredientRepository;
use App\Repository\ProduitRepository;
use App\Repository\TailleProduitRepository;
use App\Repository\TypeProduitRepository;
use App\Repository\UserMessageRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    #[Route('/accueil', name: '_accueil')]
    public function index(EntityManagerInterface $entityManager, UserMessageRepository $userMessageRepository, TypeProduitRepository $typeProduitRepository, IngredientRepository $ingredientRepository, TailleProduitRepository $tailleProduitRepository, CollaborateurRepository $collaborateurRepository, Request $requete, EtatRepository $etatRepository, ProduitRepository $produitRepository, CommandeRepository $commandeRepository): Response
    {
        $pizzas = $produitRepository->findAll();
        $typesProduits = $typeProduitRepository->findAll();
        $detailCommande = new DetailCommande();
        $now = new DateTime();
        $utilisateur = $this->getUser();
        $etatCreer = $etatRepository->findOneBy(array('id' => 1));
        $heurePreparation = $now->add(new DateInterval('PT30M'));
        $heureLivraison = $heurePreparation->add(new DateInterval('PT30M'));
        $detailCommandeForm = $this->createForm(DetailCommandeFormType::class, $detailCommande);
        //////////////////////////////// TOOLTIP ///////////////////////////////////////////////////////////////////////////////////////////
        $result = $this->tooltip($commandeRepository, $tailleProduitRepository, $produitRepository);
        if($result){
            $detailsCommandePanier = $result['detailsCommandePanier'];
            $prixDuPanier = $result['prixDuPanier'];
        } else {
            $detailsCommandePanier = [];
            $prixDuPanier = 0;
        }
        //////////////////////////////////// MODAL PANIER ///////////////////////////////////////////////////////////////////////////////////
        $ingredientIndisponible = $ingredientRepository->findBy(['quantite' => 0]);
        foreach ($pizzas as $pizza) {
            $ingredients = $pizza->getIngredients();
            $disponible = true;
            foreach ($ingredients as $ingredient) {
                if ($ingredient->getQuantite() == 0) {
                    $disponible = false;
                    break;
                }
            }
            $pizza->setDisponible($disponible);
        }

//////////////////////////// FORMULAIRE //////////////////////////////////////////////////////////////////////////////////////////////////////////
        $detailCommandeForm->handleRequest($requete); //Récupération des données
        if ($detailCommandeForm->isSubmitted() && $detailCommandeForm->isValid()) { // Validation du formulaire

            $id = $requete->request->get('idPizzaModal'); // Récupération de l'ID de l'input
            $pizza = $produitRepository->findOneBy(array('id' => $id)); // Récupération de la pizza correspondantes

            $donneeFormulaire = $detailCommandeForm->getData();
            $this->soustraireIngredient($pizza, $donneeFormulaire, $entityManager);
            $detailCommande->setProduit($pizza);
//// COMMANDE EXISTANTE EN BDD /////////////////////////////////////////////
            if (!empty($derniereCommande)) {  // Vérification d'une commande déjà existante
                $etatDerniereCommande = $derniereCommande->getEtat(); // Récupération de l'état

////////////////////////// ETAT = CREER ////////////////////////////////////////////
                if ($etatDerniereCommande->getId() === $etatCreer->getId()) {
                    $this->modificationCommande($etatDerniereCommande, $derniereCommande, $etatCreer, $detailCommandeForm, $pizza, $heureLivraison, $heurePreparation, $entityManager, $detailCommande, $collaborateurRepository);
                    return $this->redirectToRoute('_accueil');
                } ///////////////////////// ETAT = PREPARATION LIVRAISON .... ///////////////////////
                else if ($etatDerniereCommande->getId() >= $etatCreer->getId()) // SI LA COMMANDE EST EN COURS DE PREPARATION OU AUTRES
                {
                    $this->creationCommande($etatCreer, $utilisateur, $heureLivraison, $heurePreparation, $detailCommande, $collaborateurRepository, $entityManager);
                    return $this->redirectToRoute('_accueil');
                }
            } ////////////////////////// PAS DE COMMANDE /////////////////////////////////////////////
            else if (empty($derniereCommande)) // SI IL N Y A PAS DE COMMANDE
            {
                $this->creationCommande($etatCreer, $utilisateur, $heureLivraison, $heurePreparation, $detailCommande, $collaborateurRepository, $entityManager);
                return $this->redirectToRoute('_accueil');
            }

        }
        $messagesNonLu = $userMessageRepository->findBy(['checked' => 0]);
        return $this->render('accueil/index.html.twig', compact('pizzas', 'detailCommandeForm', 'detailsCommandePanier', 'prixDuPanier', 'typesProduits', 'messagesNonLu'));
    }

    #[Route('/detailPizza/{id}', name: '_detailPizza')]
    public function detailPizza($id, Request $requete, ProduitRepository $produitRepository, UserMessageRepository $userMessageRepository, TailleProduitRepository $tailleProduitRepository, CommandeRepository $commandeRepository, EntityManagerInterface $entityManager, EtatRepository $etatRepository, CollaborateurRepository $collaborateurRepository): Response
    {
////////////////////////////////////////// VARIABLES //////////////////////////////////////////////////////////////////////////////////////
        $pizza = $produitRepository->findOneBy(array('id' => $id));
        $detailCommande = new DetailCommande();
        $now = new DateTime();
        $utilisateur = $this->getUser();

        $etatCreer = $etatRepository->findOneBy(array('id' => 1));
        $heurePreparation = $now->add(new DateInterval('PT30M'));
        $heureLivraison = $heurePreparation->add(new DateInterval('PT30M'));
        $messagesNonLu = $userMessageRepository->findBy(['checked' => 0]);
        $detailCommande->setProduit($pizza);
        $detailCommandeForm = $this->createForm(DetailCommandeFormType::class, $detailCommande);
////////////////////////////////////////// Liste Mot //////////////////////////////////////////////////////////////////
        $viandes = ["Jambon Cru", "Lardon", "Viande haché", "Merguez", "Jambon"];

//////////////////////////////// TOOLTIP ///////////////////////////////////////////////////////////////////////////////////////////
        $result = $this->tooltip($commandeRepository, $tailleProduitRepository, $produitRepository);
        if($result){
            $detailsCommandePanier = $result['detailsCommandePanier'];
            $prixDuPanier = $result['prixDuPanier'];
        } else {
            $detailsCommandePanier = [];
            $prixDuPanier = 0;
        }
////////////////////////////////////////// FORMULAIRE ET TRAITEMENT //////////////////////////////////////////////////////////////////

        $detailCommandeForm->handleRequest($requete);
        if ($detailCommandeForm->isSubmitted() && $detailCommandeForm->isValid()) {

            if (!empty($derniereCommande)) {
                $etatDerniereCommande = $derniereCommande->getEtat();

                if ($etatDerniereCommande->getId() === $etatCreer->getId()) {
                    $this->modificationCommande($etatDerniereCommande, $derniereCommande, $etatCreer, $detailCommandeForm, $pizza, $heureLivraison, $heurePreparation, $entityManager, $detailCommande, $collaborateurRepository);
                    return $this->redirectToRoute('_accueil');
                } else if ($etatDerniereCommande->getId() >= $etatCreer->getId()) // SI LA COMMANDE EST EN COURS DE PREPARATION OU AUTRES
                {
                    $this->creationCommande($etatCreer, $utilisateur, $heureLivraison, $heurePreparation, $detailCommande, $collaborateurRepository, $entityManager);
                    return $this->redirectToRoute('_accueil');
                }
            } else if (empty($derniereCommande)) // SI IL N Y A PAS DE COMMANDE
            {
                $this->creationCommande($etatCreer, $utilisateur, $heureLivraison, $heurePreparation, $detailCommande, $collaborateurRepository, $entityManager);
                return $this->redirectToRoute('_accueil');
            }

        }
        return $this->render('panier/detail.html.twig', compact('pizza', 'detailCommandeForm', 'viandes', 'detailsCommandePanier', 'prixDuPanier', 'messagesNonLu'));
    }

// CREATION D UNE COMMANDE
    public function creationCommande($etatCreer, $utilisateur, $heureLivraison, $heurePreparation, $detailCommande, $collaborateurRepository, $entityManager)
    {
        $commande = new Commande();

        $commande->setEtat($etatCreer);
        $commande->setCollaborateur($utilisateur);
        $commande->setDateHeureLivraison($heureLivraison);
        $commande->setDateHeurePreparation($heurePreparation);
        $commande->addDetailsCommande($detailCommande);
        $this->panierPleinUtilisateur($collaborateurRepository, $entityManager);
        $this->envoieBaseDeDonnee($entityManager, $detailCommande, $commande);

    }

// MODIFICATION COMMANDE DEJA EXISTANTE EN BDD
    public function modificationCommande($etatDerniereCommande, $derniereCommande, $etatCreer, $detailCommandeForm, $pizza, $heureLivraison, $heurePreparation, $entityManager, $detailCommande, $collaborateurRepository)
    {
        //////////////// SI ETAT COMMANDE EST CREER /////////////////
        $utilisateur = $this->getUser();
        if ($etatDerniereCommande->getId() === $etatCreer->getId()) { // Si état = créer, on peut ajouter des pizzas dessus
            $produitATrouver = false;   // Variable pour savoir si il y a déjà une pizza correspondante au formulaire dans le panier
            $detailDuPanier = $derniereCommande->getDetailsCommande(); // Récupération des détails du panier
            $donneesFormulaire = $detailCommandeForm->getData(); // Récupération des données du formulaire
            $taille = $donneesFormulaire->getTaille(); // Récupération de la taille de la pizza
            foreach ($detailDuPanier as $detail) { // On parcours toutes les pizzas du panier
                // Si il y a une pizza avec la taille correspondante, on modifie la quantité
                if ($detail->getProduit() === $pizza && $detail->getTaille()->getId() === $taille->getId()) {
                    $quantite = $donneesFormulaire->getQuantite(); // Récupération quantité
                    $nouvelleQuantite = ($detail->getQuantite()) + $quantite; // Ajout quantité
                    $detail->setQuantite($nouvelleQuantite);
                    $produitATrouver = true; // Le produit a été trouvé
                    $derniereCommande->setDateHeureLivraison($heureLivraison);
                    $derniereCommande->setDateHeurePreparation($heurePreparation);
                    $entityManager->persist($derniereCommande);
                    $entityManager->flush();

                }
            }
            if ($produitATrouver === false) { // Le produit n'a pas été trouvé
                $this->panierPleinUtilisateur($collaborateurRepository, $entityManager, $utilisateur);
                $detailCommande->setCommande($derniereCommande);
                $derniereCommande->setDateHeureLivraison($heureLivraison);
                $derniereCommande->setDateHeurePreparation($heurePreparation);
                $derniereCommande->addDetailsCommande($detailCommande);

                $entityManager->persist($detailCommande);
                $entityManager->persist($derniereCommande);
                $entityManager->flush();

            }
        }
    }

// LIEN BASE DE DONNEE
    public function envoieBaseDeDonnee($entityManager, $detailCommande, $commande)
    {
        $detailCommande->setCommande($commande);

        $entityManager->persist($detailCommande);
        $entityManager->persist($commande);
        $entityManager->flush();

    }

// FONCTION POUR METTRE LA VARIABLE PANIER DE L UTILISATEUR A 1
    public function panierPleinUtilisateur($collaborateurRepository, $entityManager)
    {
        $utilisateur = $this->getUser();
        $user = $collaborateurRepository->findOneBy(array('id' => $utilisateur->getId()));
        $user->setPanier(1);
        $entityManager->persist($user);
        $entityManager->flush();

    }

// FONCTION POUR ENVOYER LE PRIX DU PANIER TOTAL AU TOOLTIP
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

    public function soustraireIngredient($pizza, $donneeFormulaire, $entityManager)
    {

        $quantite = $donneeFormulaire->getQuantite();
        $ingredientsPizza = $pizza->getIngredients();

        foreach ($ingredientsPizza as $ingredient) {
            $stockIngredient = $ingredient->getQuantite();
            $nouveauStock = $stockIngredient - $quantite;
            $ingredient->setQuantite($nouveauStock);

        }
        $entityManager->flush();
    }

    #[Route('/typeProduit', name: '_typeProduit')]
    public function typeProduit(Request $request, IngredientRepository $ingredientRepository, TypeProduitRepository $typeProduitRepository, CommandeRepository $commandeRepository, EntityManagerInterface $entityManager)
    {
        $typeProduits = $typeProduitRepository->findAll();
//
        foreach ($typeProduits as $index => $type) {

            $typesDeProduit[$index] = [$type->getLibelle()]; // Tableau associatif
        }
        return new JsonResponse(['typesProduits' => $typesDeProduit]);
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
