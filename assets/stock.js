function initStock() {
    let boutonCategorie = document.getElementById('ingredient_form_categorie');
    let inputQuantite = document.getElementsByTagName('input');
    let validation = document.getElementById('changement');
    let search = document.getElementById('search');
    let decrement = document.getElementsByClassName('decrement-button');
    let increment = document.getElementsByClassName('increment-button');
    for(let i =0; i < decrement.length ; i++){
        decrement[i].addEventListener('click', function(){
            diminutionQuantite(decrement[i].classList)
        })
    }
    for(let i =0; i < increment.length ; i++){
        increment[i].addEventListener('click', function(){
            augmentationQuantite(increment[i].classList)
        })
    }
    for (let i = 0; i < inputQuantite.length; i++) {
        inputQuantite[i].addEventListener('change', function () {
            saveModifQuantite(inputQuantite[i].id, inputQuantite[i].value);
        });

    }

    validation.addEventListener('click', envoiDonnee);
    search.addEventListener('change', affichageIngredient);
    boutonCategorie.addEventListener('change', affichageIngredient);


}

let tabChangement = {};
let categories = [
    document.getElementsByClassName('Viande'),
    document.getElementsByClassName('Légumes'),
    document.getElementsByClassName('Fromages'),
    document.getElementsByClassName('Sauces'),
    document.getElementsByClassName('Herbes et épices'),
    document.getElementsByClassName('Fruits de mer'),
    document.getElementsByClassName('Garnitures spéciales'),
]
window.initStock = initStock;

function affichageIngredient() {

    let index = document.getElementById('ingredient_form_categorie').value;
    let texte = document.getElementById('search').value;

    if (!texte || texte !== '') {
        if (!index || index === '0') {
            showAllCategory(categories);
            searchIngredient(texte, index);
        } else {
            let indexCat = parseInt(index) - 1;
            showCategory(categories[indexCat]);
            hideCategory(categories, indexCat);
            searchIngredient(texte, indexCat);
        }
    } else {
        if (!index || index === '0') {
            showAllCategory(categories);
        } else {
            let indexCat = parseInt(index) - 1;
            showCategory(categories[indexCat]);
            hideCategory(categories, indexCat);
        }
    }

}

function hideCategory(categories, index) {
    for (let i = 0; i < categories.length; i++) {
        if (i !== index) {
            for (let j = 0; j < categories[i].length; j++) {
                categories[i][j].classList.add('hidden');
            }
        }
    }
}

function showAllCategory(categories) {
    for (let i = 0; i < categories.length; i++) {
        for (let j = 0; j < categories[i].length; j++) {
            categories[i][j].classList.remove('hidden');
        }
    }
}

function showCategory(category) {
    for (let i = 0; i < category.length; i++) {
        category[i].classList.remove('hidden');
    }
}
function diminutionQuantite(liste) {

    let idInput = liste[1];
    console.log('Diminution -> idInput : ' + idInput);
    let input = document.getElementById(idInput);
    let valueInput = parseInt(input.value);
    let newValue = (valueInput - 1);
    input.value = newValue;
    saveModifQuantite(idInput, newValue);
}
function augmentationQuantite(liste) {

    let idInput = liste[1];
    console.log('Augmentation -> idInput : ' + idInput);
    let input = document.getElementById(idInput);
    let valueInput = parseInt(input.value);
    let newValue = (valueInput + 1);
    input.value = newValue;
    saveModifQuantite(idInput, newValue);

}
function saveModifQuantite(id, value) {
    console.log('save')
    tabChangement[id] = value;
}


function envoiDonnee() {
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

function searchIngredient(texte, index) {

    if (texte !== '') { // Input plein
        if (index === '0') { // Aucune catégorie
            for (let i = 0; i < categories.length; i++) {
                for (let j = 0; j < categories[i].length; j++) {
                    if (!categories[i][j].id.toLowerCase().includes(texte)) {
                        if (categories[i][j].tagName.toLowerCase() === 'input') {
                            categories[i][j].classList.remove('hidden');
                        } else {
                            categories[i][j].classList.add('hidden');
                        }                    } else {
                        categories[i][j].classList.remove('hidden');
                    }
                }
            }
        } else { // Une catégorie sélectionnée
            for (let h = 0; h < categories[index].length; h++) {
                if (!categories[index][h].id.toLowerCase().includes(texte)) {
                    if (categories[index][h].tagName.toLowerCase() === 'input') {
                        categories[index][h].classList.remove('hidden');
                    } else {
                        categories[index][h].classList.add('hidden');
                    }
                } else {
                    categories[index][h].classList.remove('hidden');
                }
            }
        }
    }
}