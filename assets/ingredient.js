
function initIngredient() {
    let boutonCategorie = document.getElementById('ingredient_form_categorie');

    boutonCategorie.addEventListener('change', affichageIngredient);
}
window.initIngredient = initIngredient;
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

    if(!valeurBouton){
        hideAllCategory(categories);
    } else {
        let index = parseInt(valeurBouton) - 1;
        showCategory(categories[index]);
        hideCategory(categories, index);
    }
}
function hideCategory(category, index) {
    for (let i = 0; i < category.length; i++) {
        if(i !== index){
            for(let j = 0; j < category[i].length; j++){
                category[i][j].classList.add('hidden');
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
    for (let i = 0; i < category.length; i++) {
        category[i].classList.remove('hidden');
    }
}