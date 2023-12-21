
function initStock() {
    let boutonCategorie = document.getElementById('ingredient_form_categorie');
    let inputQuantite = document.getElementsByTagName('input');
    let validation = document.getElementById('changement');

    for (let i =0 ; i < inputQuantite.length ; i++){
        inputQuantite[i].addEventListener('change', function() {
            modificationQuantite(inputQuantite[i].id, inputQuantite[i].value);
        });

    }

    validation.addEventListener('click', envoiDonnee)
    boutonCategorie.addEventListener('change', affichageIngredient);
}
let tabChangement = {};
window.initStock = initStock;
function affichageIngredient() {

    let categories = [
        document.getElementsByClassName('Viande'),
        document.getElementsByClassName('Légumes'),
        document.getElementsByClassName('Fromages'),
        document.getElementsByClassName('Sauces'),
        document.getElementsByClassName('Herbes et épices'),
        document.getElementsByClassName('Fruits de mer'),
        document.getElementsByClassName('Garnitures spéciales'),
    ]

    let valeurBouton = document.getElementById('ingredient_form_categorie').value;
    if(!valeurBouton || valeurBouton === 0){
        hideAllCategory(categories);
    } else {
        let index = parseInt(valeurBouton) - 1;
        showCategory(categories[index]);
        hideCategory(categories, index);
    }
}
function hideCategory(categories, index) {
    console.log('je passe dans le hidden');
    for (let i = 0; i < categories.length; i++) {
        if(i !== index){
            for(let j = 0; j < categories[i].length; j++){
                categories[i][j].classList.add('hidden');
            }
        }
    }
}

function hideAllCategory(categories) {
    for (let category of categories) {
        for (let i = 0; i < category.length; i++) {
            category[i].classList.add('hidden');
        }
    }
}
function showCategory(category) {
    console.log('je passe dans le show');
    for (let i = 0; i < category.length; i++) {
        category[i].classList.remove('hidden');
    }
}
function modificationQuantite( id, value) {
    showButton();
    console.log('je passe dans le modif quantite');
    console.log('id : ' + id + 'value : ' + value);

    tabChangement[id] = value;
    console.log(tabChangement);
}
function showButton() {
  let bouton = document.getElementById('changement');
  bouton.classList.remove('hidden');
}
function envoiDonnee() {
    console.log('Envoi de données');
    let url = 'https://127.0.0.1:8000/changementQuantite';
    let options = {
        method: 'POST', // Correction de la faute de frappe ici
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(tabChangement)
    };

    fetch(url, options)
        .then(response => response.json())
        .then(data => {
            console.log('Réponse du serveur : ', data);
            window.location.href = '/gerant';

        })
        .catch(error => {
            console.error('Erreur lors de la requête : ', error);
        });
}
