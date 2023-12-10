function initModal() {
    console.log('Je suis dans modal.js');

    ajouterPanier();
    document.getElementById('quantiteModal').addEventListener('change', modificationPrixModal);

}
window.initModal = initModal;
let prixPizza = 0;

///////////////////////////// AFFICHAGE MODAL AJOUT ///////////////////////////////////////
function openModal(id, nom, prix) {
    prixPizza = prix;
    console.log("Je passe dans openModal");
    let prixPizzaLarge = (parseFloat(prix))+ 5;
    document.getElementById('nom').innerText= nom;
    const champId = document.getElementById('idPizzaModal');
    const champQuantite = document.getElementById('quantiteModal');
    let detail_commande_form_taille_1 = document.getElementById('detail_commande_form_taille_1');
    let detail_commande_form_taille_2 = document.getElementById('detail_commande_form_taille_2');
    if(detail_commande_form_taille_1){
        detail_commande_form_taille_1.checked = false;
    }
    if(detail_commande_form_taille_2){
        detail_commande_form_taille_2.checked = false;
    }
    champQuantite.value = null;
        champId.value = id;
    document.getElementById('modalAjout').classList.remove('hidden');
    document.getElementById('detail_commande_form_taille_1').addEventListener('click', function (){
        document.getElementById('prixPizza').classList.remove('hidden');
        document.getElementById('prixPizza').innerText = prix ;
    })
    document.getElementById('detail_commande_form_taille_2').addEventListener('click', function (){
        document.getElementById('prixPizza').classList.remove('hidden');
        document.getElementById('prixPizza').innerText = prixPizzaLarge ;
    })
}
function hiddenModal() {
    console.log("Je passe dans hidden modal");
    document.getElementById('prixPizza').classList.add('hidden');
    document.getElementById('modalAjout').classList.add('hidden');
}
///////////////////////////// MODAL AJOUT ///////////////////////////////////////

function ajouterPanier(){
    const boutonsAjouter = document.querySelectorAll('.afficherModal');
    const boutonsClose = document.querySelectorAll('.hiddenModal');
    const modalAjout = document.getElementById('modalAjout');

    boutonsClose.forEach(function(bouton) {
        bouton.addEventListener('click', function(event) {
            hiddenModal();
        });
    });
    boutonsAjouter.forEach(function(bouton) {
        bouton.addEventListener('click', function(event) {
            const pizzaId = event.target.dataset.pizzaId;
            const pizzaNom = event.target.dataset.pizzaNom;
            const pizzaPrix = event.target.dataset.pizzaPrix;
            console.log(`Pizza ID: ${pizzaId}, Nom: ${pizzaNom}, Prix: ${pizzaPrix}`);

            openModal(pizzaId, pizzaNom, pizzaPrix);
        });
    });
    window.onclick = function(event) {
        if (event.target === modalAjout) {
            hiddenModal();
        }
    }
}
///////////////////////////// MODAL PANIER ///////////////////////////////////////
function afficherModalPanier (){
    console.log('affichage');
    const fenetrePanierModal = document.getElementById('fenetreModale');
                fenetrePanierModal.classList.remove('hidden');
}
function cacherModalPanier(){
    console.log('cacher');
    const fenetrePanierModal = document.getElementById('fenetreModale');
        fenetrePanierModal.classList.add('hidden');
}


function modificationPrixModal(){
    let detail_commande_form_taille_1 = document.getElementById('detail_commande_form_taille_1');
    let detail_commande_form_taille_2 = document.getElementById('detail_commande_form_taille_2');

    if(detail_commande_form_taille_1.checked === true || detail_commande_form_taille_2.checked === true  ){ // Une taille a été sélectionnée
        if(detail_commande_form_taille_1.checked === true){ // Taille normale
            let quantiteChoisie = document.getElementById('quantiteModal').value;
            let quantiteInt = parseInt(quantiteChoisie);
            let prixInt = parseFloat(prixPizza);
            let prixTotal = quantiteInt * prixInt;
            document.getElementById('prixPizza').innerText = prixTotal;
        }
        if(detail_commande_form_taille_2.checked === true){ // Taille normale
            let quantiteChoisie = document.getElementById('quantiteModal').value;
            let quantiteInt = parseInt(quantiteChoisie);
            let prixInt = parseFloat(prixPizza)+ 5;
            let prixTotal = quantiteInt * prixInt;
            document.getElementById('prixPizza').innerText = prixTotal;
        }
    }
}

////////////////////////////// FONCTION PAS UTILISER SERVANT A CONNAITRE LA POSITION DU CURSEUR ET D UN BOUTON ///////////////////////////////////////////

// document.addEventListener('mousemove', PositionCurseur); A METTRE DANS INIT

// function PositionCurseur(e) {
//     const fenetrePanierModal = document.getElementById('fenetreModale');
//
//     // Position curseur
//     // let positionLargeurCurseur = e.screenX;
//     // let positionHauteurCurseur = e.screenY;
//
//     // Position bouton
//     // const bouton = document.getElementById('panierModalPlein');
//     // const rect = bouton.getBoundingClientRect();
//     // const positionX = rect.left + window.scrollX;
//     // const positionY = rect.top + window.scrollY;
//
//     // Marges par rapport au bouton
//     const topMargin = 92;
//     const bottomMargin = 122;
//     const leftMargin = 1654;
//     const rightMargin = 1689;
//
//     if ( // Condition vérifiant si on dépasse de chaque côté des bornes
//         positionLargeurCurseur < leftMargin ||
//         positionLargeurCurseur > rightMargin ||
//         positionHauteurCurseur < topMargin ||
//         positionHauteurCurseur > bottomMargin
//     ) {
//         if(!fenetrePanierModal.classList.contains('hidden')){
//             cacherModalPanier();
//         }
//     } else {
//         if(fenetrePanierModal.classList.contains('hidden')){
//             afficherModalPanier();
//         }
//     }
//
// }