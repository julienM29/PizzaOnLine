function initTest() {


    let decrement = document.getElementById('decrement');
    let increment = document.getElementById('increment');
    increment.addEventListener('click', augmentationQuantite);
    decrement.addEventListener('click', diminutionQuantite);

    hiddenTaille();

}

window.initTest = initTest;
let prixPizza = 0;

///////////////////////////// AFFICHAGE MODAL AJOUT ///////////////////////////////////////
function openModal(id, nom, prix) {
    document.getElementById('prixPizza').classList.remove('hidden');
    prixPizza = prix;
    let prixPizzaLarge = (parseFloat(prix) + 5);
    document.getElementById('nom').innerText = nom;
    const champId = document.getElementById('idPizzaModal');
    const champQuantite = document.getElementById('quantiteModal');
    let detail_commande_form_taille_1 = document.getElementById('detail_commande_form_taille_1');
    let detail_commande_form_taille_2 = document.getElementById('detail_commande_form_taille_2');
    if (detail_commande_form_taille_1) {
        detail_commande_form_taille_1.checked = false;
    }
    if (detail_commande_form_taille_2) {
        detail_commande_form_taille_2.checked = false;
    }
    champQuantite.value = null;
    champId.value = id;
    document.getElementById('modalAjout').classList.remove('hidden');

    // document.getElementById('detail_commande_form_taille_1').addEventListener('click', function () {
    //     console.log(document.getElementById('prixPizza').innerText);
    //     document.getElementById('prixPizza').innerText = prix;
    // })
    // document.getElementById('detail_commande_form_taille_2').addEventListener('click', function () {
    //     console.log(document.getElementById('prixPizza').innerText);
    //     document.getElementById('prixPizza').innerText = prixPizzaLarge; }
    // )


}

function hiddenModal() {
    document.getElementById('prixPizza').classList.add('hidden');
    document.getElementById('modalAjout').classList.add('hidden');
}

///////////////////////////// MODAL AJOUT ///////////////////////////////////////

function ajouterPanier() {
    const boutonsAjouter = document.querySelectorAll('.afficherModal');
    const boutonsClose = document.querySelectorAll('.hiddenModal');
    const modalAjout = document.getElementById('modalAjout');

    boutonsClose.forEach(function (bouton) {
        bouton.addEventListener('click', function (event) {
            hiddenModal();
        });
    });
    boutonsAjouter.forEach(function (bouton) {
        bouton.addEventListener('click', function (event) {
            const pizzaId = event.target.dataset.pizzaId;
            const pizzaNom = event.target.dataset.pizzaNom;
            const pizzaPrix = event.target.dataset.pizzaPrix;
            console.log(`Pizza ID: ${pizzaId}, Nom: ${pizzaNom}, Prix: ${pizzaPrix}`);

            openModal(pizzaId, pizzaNom, pizzaPrix);
        });
    });
    window.onclick = function (event) {
        if (event.target === modalAjout) {
            hiddenModal();
        }
    }
}

function modificationPrixModal() {
    let detail_commande_form_taille_1 = document.getElementById('detail_commande_form_taille_1');
    let detail_commande_form_taille_2 = document.getElementById('detail_commande_form_taille_2');


    if (detail_commande_form_taille_1.checked === true || detail_commande_form_taille_2.checked === true) { // Une taille a été sélectionnée
        if (detail_commande_form_taille_1.checked === true) { // Taille normale
            let quantiteChoisie = document.getElementById('quantiteModal').value;
            let quantiteInt = parseInt(quantiteChoisie);
            let prixInt = parseFloat(prixPizza);
            let prixTotal = 0;
            if(quantiteChoisie === 0 || quantiteChoisie == null || quantiteChoisie === ''){ // Empêcher que quand une quantité est choisie et qu'on change de taille, de remettre le prix initial au lieu du prix total
                prixTotal = 0;
                console.log('bonjour');
            } else {
                prixTotal = quantiteInt * prixInt;
            }
            document.getElementById('prixPizza').innerText = prixTotal;
        }
        if (detail_commande_form_taille_2.checked === true) { // Taille large
            let quantiteChoisie = document.getElementById('quantiteModal').value;
            let quantiteInt = parseInt(quantiteChoisie);
            let prixInt = parseFloat(prixPizza) + 5;
            let prixTotal = 0;
            if(quantiteChoisie === 0 || quantiteChoisie == null || quantiteChoisie === ''){ // Empêcher que quand une quantité est choisie et qu'on change de taille, de remettre le prix initial au lieu du prix total
                console.log('bonjour');
                prixTotal = 0;
            } else {
                prixTotal = quantiteInt * prixInt;
            }
            document.getElementById('prixPizza').innerText = prixTotal;
        }
    }
}

function diminutionQuantite() {
    let input = document.getElementById('inputQuantite');
    let valueInput = parseInt(input.value);
    let newValue = 0;

    if (valueInput == null) {
        valueInput = 0;
    }
    if (valueInput <= 0 || isNaN(valueInput)) {
        newValue = 0;
    } else {
        newValue = (valueInput - 1);
    }
    input.value = newValue;
    inputForm();
    modificationPrixModal();
}

function augmentationQuantite() {
    let input = document.getElementById('inputQuantite');
    let valueInput = parseInt(input.value);
    let newValue = 0;

    if (valueInput == null) {
        valueInput = 0;
    }
    if (valueInput >= 0) {
        newValue = (valueInput + 1);
    } else {
        newValue = 1;
    }
    input.value = newValue;
    inputForm();
    modificationPrixModal();
}

function verificationInput() {

    let input = document.getElementById('inputQuantite');
    let message = document.getElementById('messageError');
    let decimal = document.getElementById('messageDecimal');

    let regexNonNumeric = /\D/.test(input.value); // Vérification des caractères non numériques

    if (regexNonNumeric) {
        message.classList.remove('hidden');
        decimal.classList.add('hidden');
    } else {
        inputForm();
        modificationPrixModal();
    }

}

function inputForm() {
    let inputForm = document.getElementById('quantiteModal');
    let input = document.getElementById('inputQuantite');
    inputForm.value = parseInt(input.value);
}
function hiddenTaille (){
    let checkboxs = [
        document.getElementById('detail_commande_form_taille_1'),
        document.getElementById('detail_commande_form_taille_2'),
        document.getElementById('detail_commande_form_taille_3'),
        document.getElementById('detail_commande_form_taille_4'),
        document.getElementById('detail_commande_form_taille_5'),
        document.getElementById('detail_commande_form_taille_6')
    ];
    let labels = [
        document.querySelector('label[for="detail_commande_form_taille_1"]'),
        document.querySelector('label[for="detail_commande_form_taille_2"]'),
        document.querySelector('label[for="detail_commande_form_taille_3"]'),
        document.querySelector('label[for="detail_commande_form_taille_4"]'),
        document.querySelector('label[for="detail_commande_form_taille_5"]'),
        document.querySelector('label[for="detail_commande_form_taille_6"]')
    ];
    let type = document.getElementById('typeProduit').innerText;
    console.log(type);
    if (type === 'Pizza') {
        console.log('je passe ici car pizza = type ');

        for (let i = 0; i < checkboxs.length; i++) {
            if (i === 0 || i === 1) {
                checkboxs[i].classList.remove('hidden');
                labels[i].classList.remove('hidden');
                checkboxs[i].parentNode.classList.add('mr-6');
            } else {
                checkboxs[i].classList.add('hidden');
                labels[i].classList.add('hidden');
            }
        }
    }
    if (type === 'Canette') {
        for (let i = 0; i < checkboxs.length; i++) {
            if (i === 2 || i === 3) {
                checkboxs[i].classList.remove('hidden');
                labels[i].classList.remove('hidden');
                checkboxs[i].parentNode.classList.add('mr-6');
            } else {
                checkboxs[i].classList.add('hidden');
                labels[i].classList.add('hidden');
            }
        }
    }
    if (type === 'Bouteille') {
        console.log('je passe dans bouteille')
        for (let i = 0; i < checkboxs.length; i++) {
            if (i === 4 || i === 5) {
                checkboxs[i].classList.remove('hidden');
                labels[i].classList.remove('hidden');
                checkboxs[i].parentNode.classList.add('mr-6');
            } else {
                checkboxs[i].classList.add('hidden');
                labels[i].classList.add('hidden');
            }
        }
    }
}