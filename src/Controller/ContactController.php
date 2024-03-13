<?php

namespace App\Controller;

use App\Entity\UserMessage;
use App\Form\UserMessageType;
use App\Repository\CollaborateurRepository;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use App\Repository\TailleProduitRepository;
use App\Repository\UserMessageRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: '_contact')]
    public function index(CommandeRepository $commandeRepository,Request $request,EntityManagerInterface $entityManager,CollaborateurRepository $collaborateurRepository, UserMessageType $messageType, TailleProduitRepository $tailleProduitRepository, ProduitRepository $produitRepository): Response
    {
        $messagesNonLu = $userMessageRepository->findBy(['checked' => 0]);
        $utilisateur = $this->getUser();
        //////////////////////////////// TOOLTIP ///////////////////////////////////////////////////////////////////////////////////////////
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );
        $detailsCommandePanier = [];
        $prixDuPanier = 0;
        if ($derniereCommande) {

            $detailsCommandePanier = $derniereCommande->getDetailsCommande(); // Detail commande envoyer directement au twig
            if ($detailsCommandePanier) {
                $prixDuPanier = $this->prixDuPanier($derniereCommande, $tailleProduitRepository, $produitRepository, $detailsCommandePanier);
            } else {
                $detailsCommandePanier = [];
            }
        } else {
            $derniereCommande = [];
        }
        /////////////// Pizza du moment /////////////////////////////
//        $pizzas = $produitRepository->findAll();
        $pizzas = $produitRepository->findBy(['typeProduit' => 1,
            'Disponible' => 1]);
//        $nombrePizzas = count($pizzas);
        $nombreAleatoire = array_rand($pizzas);
        $pizzaDuMoment = $pizzas[$nombreAleatoire];
///////////// PARTIE MESSAGE UTILISATEUR /////////////////////////////
        $newMessage = new UserMessage();
        $messageForm = $this->createForm(UserMessageType::class, $newMessage);
        $messageForm->handleRequest($request);
        if($messageForm->isSubmitted() && $messageForm->isValid()){
            $utilisateurConnecter = $collaborateurRepository->findOneBy(['id' => $utilisateur->getId()]);
            if($utilisateurConnecter){
                $newMessage->setUserId($utilisateurConnecter);
                $newMessage->setChecked(0);
                $newMessage->setNom($utilisateurConnecter->getNom());
                $newMessage->setPrenom($utilisateurConnecter->getPrenom());
                $entityManager->persist($newMessage);
                $entityManager->flush();
                return $this->redirectToRoute('_accueil');            }

        }

        return $this->render('contact/index.html.twig', compact('prixDuPanier', 'detailsCommandePanier', 'pizzaDuMoment', 'messageForm', 'messagesNonLu'));
    }
    #[Route('/messagerie', name: '_messagerie')]
    public function messagerie(CommandeRepository $commandeRepository,UserMessageRepository $userMessageRepository, Request $request,EntityManagerInterface $entityManager,CollaborateurRepository $collaborateurRepository, UserMessageType $messageType, TailleProduitRepository $tailleProduitRepository, ProduitRepository $produitRepository): Response
    {
        $utilisateur = $this->getUser();
        //////////////////////////////// TOOLTIP ///////////////////////////////////////////////////////////////////////////////////////////
        $derniereCommande = $commandeRepository->findOneBy(
            ['collaborateur' => $utilisateur],
            ['id' => 'DESC']
        );
        $detailsCommandePanier = [];
        $prixDuPanier = 0;
        if ($derniereCommande) {

            $detailsCommandePanier = $derniereCommande->getDetailsCommande(); // Detail commande envoyer directement au twig
            if ($detailsCommandePanier) {
                $prixDuPanier = $this->prixDuPanier($derniereCommande, $tailleProduitRepository, $produitRepository, $detailsCommandePanier);
            } else {
                $detailsCommandePanier = [];
            }
        } else {
            $derniereCommande = [];
        }
        $messages = $userMessageRepository->findAll();
        $messagesNonLu = $userMessageRepository->findBy(['checked' => 0]);
        return $this->render('contact/messagerie.html.twig', compact('prixDuPanier', 'detailsCommandePanier', 'messages', 'messagesNonLu'));
    }
    public function prixDuPanier($derniereCommande, $tailleProduitRepository, $produitRepository, $detailsCommandePanier)
    {
        $prixDuPanier = 0;
        $tailleLarge = $tailleProduitRepository->findOneBy(array('id' => 2)); // récupère la taille large pour une condition (prix)
        if ($derniereCommande != null) { // Si il y a une dernière commande
            $etatDerniereCommande = $derniereCommande->getEtat();
            if ($etatDerniereCommande->getId() == 1) { // Si elle est a l'état créé
                foreach ($detailsCommandePanier as $detail) {
                    $idProduit = $detail->getProduit()->getId();
                    $pizza = $produitRepository->findOneBy(array('id' => $idProduit));
                    if ($detail->getTaille() === $tailleLarge) { // Si il y a une taille large on change le prix
                        $prixDuDetail =  (($pizza->getPrix()+5 ) * $detail->getQuantite());
                    } else {
                        $prixDuDetail = ($pizza->getPrix() * $detail->getQuantite());
                    }

                    $prixDuPanier = ($prixDuPanier + $prixDuDetail); // Total du panier
                }
            }
        }
        return $prixDuPanier;
    }
}
