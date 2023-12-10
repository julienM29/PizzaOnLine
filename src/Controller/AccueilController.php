<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\DetailCommande;
use App\Entity\Etat;
use App\Form\DetailCommandeFormType;
use App\Repository\CollaborateurRepository;
use App\Repository\CommandeRepository;
use App\Repository\EtatRepository;
use App\Repository\ProduitRepository;
use App\Repository\TailleProduitRepository;
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
    public function index(EntityManagerInterface $entityManager,TailleProduitRepository $tailleProduitRepository, CollaborateurRepository $collaborateurRepository, Request $requete, EtatRepository $etatRepository,ProduitRepository $produitRepository, CommandeRepository $commandeRepository): Response
    {
        $pizzas = $produitRepository->findAll();
        $detailCommande = new DetailCommande();
        $now = new DateTime();
        $utilisateur = $this->getUser();
        $etatCreer = $etatRepository->findOneBy(array('id' => 1));
        $heurePreparation = $now->add(new DateInterval('PT30M'));
        $heureLivraison = $heurePreparation->add(new DateInterval('PT30M'));
        $detailCommandeForm = $this->createForm(DetailCommandeFormType::class, $detailCommande);

        //////////////////////////////////// MODAL PANIER ///////////////////////////////////////////////////////////////////////////////////

        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );
        $detailsCommandePanier = $derniereCommande->getDetailsCommande(); // Detail commande envoyer directement au twig

        $prixDuPanier = $this->prixDuPanier($derniereCommande, $tailleProduitRepository, $produitRepository,$detailsCommandePanier);

//////////////////////////// FORMULAIRE //////////////////////////////////////////////////////////////////////////////////////////////////////////
        $detailCommandeForm->handleRequest($requete); //Récupération des données
        if($detailCommandeForm->isSubmitted() && $detailCommandeForm->isValid()){ // Validation du formulaire
            $id = $requete->request->get('idPizzaModal'); // Récupération de l'ID de l'input
            $pizza =  $produitRepository->findOneBy(array('id' => $id)); // Récupération de la pizza correspondantes
            $detailCommande->setProduit($pizza);
            if($derniereCommande != null){ // Vérification d'une commande déjà existante
                $etatDerniereCommande = $derniereCommande->getEtat(); // Récupération de l'état
                if($etatDerniereCommande->getId() === $etatCreer->getId()){ // Si état = créer, on peut ajouter des pizzas dessus
                    $produitATrouver = false;   // Variable pour savoir si il y a déjà une pizza correspondante au formulaire dans le panier
                    $detailDuPanier = $derniereCommande->getDetailsCommande(); // Récupération des détails du panier
                    $donneesFormulaire = $detailCommandeForm->getData(); // Récupération des données du formulaire
                    $taille = $donneesFormulaire->getTaille(); // Récupération de la taille de la pizza
                    foreach($detailDuPanier as $detail){ // On parcours toutes les pizzas du panier
                        // Si il y a une pizza avec la taille correspondante, on modifie la quantité
                        if($detail->getProduit() === $pizza && $detail->getTaille()->getId() === $taille->getId()){
                            $quantite = $donneesFormulaire->getQuantite(); // Récupération quantité
                            $nouvelleQuantite = ($detail->getQuantite())+ $quantite; // Ajout quantité
                            $detail->setQuantite($nouvelleQuantite);
                            $produitATrouver = true; // Le produit a été trouvé
                            $derniereCommande->setDateHeureLivraison($heureLivraison);
                            $derniereCommande->setDateHeurePreparation($heurePreparation);
                            $entityManager->persist($derniereCommande);
                            $entityManager->flush();
                            return $this->redirectToRoute('_accueil');
                        }
                    }
                    if($produitATrouver === false){ // Le produit n'a pas été trouvé
                        $this->panierPleinUtilisateur($collaborateurRepository , $entityManager, $utilisateur);
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
                    $this->panierPleinUtilisateur($collaborateurRepository , $entityManager, $utilisateur);
                    $commande = $this->creationCommande($etatCreer, $utilisateur, $heureLivraison, $heurePreparation, $detailCommande);
                    $this->envoieBaseDeDonnee($entityManager, $detailCommande, $commande);
                    return $this->redirectToRoute('_accueil');
                }
            }else if($derniereCommande === null)
            {
                $this->panierPleinUtilisateur($collaborateurRepository , $entityManager, $utilisateur);
                $commande = $this->creationCommande($etatCreer, $utilisateur, $heureLivraison, $heurePreparation, $detailCommande);
                $this->envoieBaseDeDonnee($entityManager, $detailCommande, $commande);
                return $this->redirectToRoute('_accueil');
            }

        }
        return $this->render('accueil/index.html.twig',compact('pizzas','detailCommandeForm', 'detailsCommandePanier','prixDuPanier'));
    }

    #[Route('/detailPizza/{id}', name: '_detailPizza')]
    public function detailPizza($id, Request $requete, ProduitRepository $produitRepository,TailleProduitRepository $tailleProduitRepository, CommandeRepository $commandeRepository, EntityManagerInterface $entityManager, EtatRepository $etatRepository, CollaborateurRepository $collaborateurRepository): Response
    {
////////////////////////////////////////// VARIABLES //////////////////////////////////////////////////////////////////////////////////////
        $pizza = $produitRepository->findOneBy(array('id' => $id));
        $detailCommande = new DetailCommande();
        $now = new DateTime();
        $utilisateur = $this->getUser();

        $etatCreer = $etatRepository->findOneBy(array('id' => 1));
        $heurePreparation = $now->add(new DateInterval('PT30M'));
        $heureLivraison = $heurePreparation->add(new DateInterval('PT30M'));
////////////////////////////////////////// Liste Mot //////////////////////////////////////////////////////////////////
        $viandes = ["Jambon Cru" ,"Lardon", "Viande haché", "Merguez","Jambon"];
////////////////////////////////////////// FORMULAIRE ET TRAITEMENT //////////////////////////////////////////////////////////////////

        $detailCommande->setProduit($pizza);
        $detailCommandeForm = $this->createForm(DetailCommandeFormType::class, $detailCommande);
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );
        $detailsCommandePanier = $derniereCommande->getDetailsCommande();
        $prixDuPanier = $this->prixDuPanier($derniereCommande, $tailleProduitRepository, $produitRepository,$detailsCommandePanier);

        $detailCommandeForm->handleRequest($requete);

        if($detailCommandeForm->isSubmitted() && $detailCommandeForm->isValid()){
        if ($derniereCommande != null){
            $etatDerniereCommande = $derniereCommande->getEtat();
            if($etatDerniereCommande->getId() === $etatCreer->getId()){
                $produitATrouver = false;
                $detailDuPanier = $derniereCommande->getDetailsCommande();
                $donneesFormulaire = $detailCommandeForm->getData();
                $taille = $donneesFormulaire->getTaille();
                foreach($detailDuPanier as $detail){
                    if($detail->getProduit() === $pizza && $detail->getTaille()->getId() === $taille->getId()){
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
                $this->panierPleinUtilisateur($collaborateurRepository , $entityManager);
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
                $this->panierPleinUtilisateur($collaborateurRepository , $entityManager);
                $commande = $this->creationCommande($etatCreer, $utilisateur, $heureLivraison, $heurePreparation, $detailCommande);
                $this->envoieBaseDeDonnee($entityManager, $detailCommande, $commande);
                return $this->redirectToRoute('_accueil');
            }
        }else if($derniereCommande === null)
        {
            $this->panierPleinUtilisateur($collaborateurRepository , $entityManager);
            $commande = $this->creationCommande($etatCreer, $utilisateur, $heureLivraison, $heurePreparation, $detailCommande);
            $this->envoieBaseDeDonnee($entityManager, $detailCommande, $commande);
            return $this->redirectToRoute('_accueil');
        }

        }
        return $this->render('panier/detail.html.twig', compact('pizza', 'detailCommandeForm', 'viandes','detailsCommandePanier','prixDuPanier'));
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
    public function panierPleinUtilisateur($collaborateurRepository, $entityManager){
        $utilisateur = $this->getUser();
        $user= $collaborateurRepository->findOneBy(array('id' => $utilisateur->getId()));
        $user->setPanier(1);
        $entityManager->persist($user);
        $entityManager->flush();

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
