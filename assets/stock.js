
function initStock() {
    let boutonCategorie = document.getElementById('ingredient_form_categorie');

    boutonCategorie.addEventListener('change', affichageIngredient);
}
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