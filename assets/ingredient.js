function initIngredient() {
    let boutonCategorie = document.getElementById('ingredient_form_categorie');
    let checkbox = document.getElementsByClassName('custom-checkbox');

    for (let i = 0; i < checkbox.length; i++) {
        if(checkbox[i].checked){
            afficherIngredient('produit_form_ingredients_'+ (i+1), true);
        }
        checkbox[i].addEventListener('change', function () {
            afficherIngredient(this.id, checkbox[i].checked);

        });
    }
    let decrement = document.getElementById('decrement');
    let increment = document.getElementById('increment');
    if (decrement) {
        decrement.addEventListener('click', diminutionQuantite);
    }
    if (increment) {
        increment.addEventListener('click', augmentationQuantite);
    }


    boutonCategorie.addEventListener('change', affichageIngredient);

    hideAllCategory(categories);
    hiddenIngredientPart();
    var selectType = document.getElementById('produit_form_typeProduit');
    var selectCategorie = document.getElementById('ingredient_form_categorie');

    if (selectCategorie) {
        for (let i = 0; i < selectCategorie.options.length; i++) {
            if (selectCategorie.options[i].innerText === 'Boisson') {
                selectCategorie.options[i].classList.add('hidden');
                break;
            }
        }
    }
    if (selectType) {
        if(selectType.value !== '0'){
            if (selectType.value === '1') {
                showIngredientPizza();
                showIngredientPart();
            }
            if (selectType.value === '2') {
                showIngredientBoisson();
                showIngredientPart();
                showMessageTypeBoisson();
                verificationCheckboxBoisson();
            }
            if (selectType.value === '3') {
                showIngredientBoisson();
                showIngredientPart();
                showMessageTypeBoisson();
                verificationCheckboxBoisson();
            }
        }
        selectType.addEventListener('change', function () {
            var typeValue = selectType.value;
            if (typeValue === '1') {
                showIngredientPizza();
                showIngredientPart();
                showMessageTypePizza();
            }
            if (typeValue === '2') {
                showIngredientBoisson();
                showIngredientPart();
                showMessageTypeBoisson();
                verificationCheckboxBoisson();
            }
            if (typeValue === '3') {
                showIngredientBoisson();
                showIngredientPart();
                showMessageTypeBoisson();
                verificationCheckboxBoisson();
            }

        });
    }

    let inputQuantite = document.getElementById('inputQuantite');
    if (inputQuantite) {
        inputQuantite.addEventListener('change', inputForm);
    }
}

window.initIngredient = initIngredient;

let categories = [
    document.getElementsByClassName('Viande'),
    document.getElementsByClassName('Légumes'),
    document.getElementsByClassName('Fromages'),
    document.getElementsByClassName('Sauces'),
    document.getElementsByClassName('Herbes et épices'),
    document.getElementsByClassName('Fruits de mer'),
    document.getElementsByClassName('Garnitures spéciales'),
    document.getElementsByClassName('Boisson'),
]
let ingredients = []; // Tableau pour l'affichage des ingrédients

function affichageIngredient() {

    let search = document.getElementById('verifNomNewIngredient');
    if(search){
        search.addEventListener('input', verificationNewIngredient);
    }
    let valeurBouton = document.getElementById('ingredient_form_categorie').value;


    if (!valeurBouton || valeurBouton === 0 || valeurBouton === '0') { // Page création produit c'est un select donc '0' et dans la page gestion stock c'est un bouton valeur 0 int
        showAllCategory(categories);
    } else {
        let index = parseInt(valeurBouton) - 1;
        showCategory(categories[index]);
        hideCategory(categories, index);
    }
}

function afficherIngredient(value, checked) {
    let labels = document.querySelector(`label[for="${value}"]`).innerText; // Label de l'input qui a été cliqué pour récupérer le nom associé
    if (checked === true) { // On vérifie si c'est coché ou décoché
        if (!ingredients.includes(labels)) { // Si il n'est pas dans le tableau
            ingredients.push(labels); // On push
        }
    } else {
        const index = ingredients.indexOf(labels);
        if (index !== -1) {
            ingredients.splice(index, 1); // Sinon on enlève
        }
    }

    let ingredientsText = ingredients.join(', '); // On ajoute un espace et une virgule pour l'affichage

    let conteneurIngredient = document.getElementById('ingredients');
    conteneurIngredient.innerText = ingredientsText;
}

// Affichage ou rendre invisible les champs non correspondants ///
function hideCategory(category, index) {
    for (let i = 0; i < category.length; i++) {
        if (i !== index) {
            for (let j = 0; j < category[i].length; j++) {
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

function showAllCategory(categories) {
    for (let category of categories) {
        for (let i = 0; i < category.length; i++) {
            category[i].classList.remove('hidden');
        }
    }
}

function showCategory(category) {
    if (category.length === 0) {

    } else {
        for (let i = 0; i < category.length; i++) {
            category[i].classList.remove('hidden');
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
}

function inputForm() {
    let inputForm = document.getElementById('quantiteModal');
    let input = document.getElementById('inputQuantite');
    inputForm.value = parseInt(input.value);
}

function hiddenIngredientPart() {
    let elementIngredient = document.getElementsByClassName('ingredientPart');
    for (let i = 0; i < elementIngredient.length; i++) {
        elementIngredient[i].classList.add('hidden');
    }
}

function showIngredientPart() {
    let elementIngredient = document.getElementsByClassName('ingredientPart');
    for (let i = 0; i < elementIngredient.length; i++) {
        elementIngredient[i].classList.remove('hidden');
    }
}

function showIngredientPizza() {
    hideIngredientBoisson();
    for (let i = 0; i < categories.length; i++) {
        if (categories[i] !== document.getElementsByClassName('Boisson')) {
            for (let j = 0; j < categories[i].length; j++) {
                categories[i][j].classList.remove('hidden');
            }
        }
    }
}

function hideIngredientPizza() {
    for (let i = 0; i < categories.length; i++) {
        if (categories[i] !== document.getElementsByClassName('Boisson')) {
            for (let j = 0; j < categories[i].length; j++) {
                categories[i][j].classList.add('hidden');
            }
        }
    }
}

function showIngredientBoisson() {
    hideIngredientPizza();
    for (let i = 0; i < categories.length; i++) {
        if (categories[i] === document.getElementsByClassName('Boisson')) {
            for (let j = 0; j < categories[i].length; j++) {
                categories[i][j].classList.remove('hidden');
            }
        }
    }
}

function hideIngredientBoisson() {
    for (let i = 0; i < categories.length; i++) {
        if (categories[i] === document.getElementsByClassName('Boisson')) {
            for (let j = 0; j < categories[i].length; j++) {
                categories[i][j].classList.add('hidden');
            }
        }
    }
}

function showMessageTypeBoisson() {
    let conteneurMessage = document.getElementById('messageTypeBoisson');
    let conteneurCategorieIngredient = document.getElementById('messageCategorieIngredient');
    let selectCategorieIngredient = document.getElementById('selectCategorieIngredient');
    let inputIngredient = document.getElementById('inputIngredient');
    conteneurMessage.classList.remove('hidden');
    conteneurCategorieIngredient.classList.add('hidden');
    selectCategorieIngredient.classList.add('hidden');
    inputIngredient.classList.add('hidden');
}
function showMessageTypePizza() {
    let conteneurMessage = document.getElementById('messageTypeBoisson');
    let conteneurCategorieIngredient = document.getElementById('messageCategorieIngredient');
    conteneurMessage.classList.add('hidden');
    conteneurCategorieIngredient.classList.remove('hidden');
}

function verificationCheckboxBoisson() {
    // Sélectionnez toutes les divs avec la classe "Boisson"
    var divsBoisson = document.querySelectorAll('.Boisson');
    let allCheckbox = document.querySelectorAll('input[type="checkbox"]');


// Parcourez chaque div Boisson
    divsBoisson.forEach(function (divBoisson) {
        // Sélectionnez toutes les cases à cocher à l'intérieur de chaque div Boisson
        var checkboxes = divBoisson.querySelectorAll('input[type="checkbox"]');

        // Ajoutez un écouteur d'événements pour chaque case à cocher
        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                // Si la case à cocher est cochée, décochez les autres dans la même div
                if (checkbox.checked) {
                    allCheckbox.forEach(function (otherCheckbox) {
                        if (otherCheckbox !== checkbox) {
                            otherCheckbox.checked = false;
                        }
                    });
                }
            });
        });
    });

}

function verificationNewIngredient() {
    let nomInscrit = document.getElementById('verifNomNewIngredient').value;
    let valueCategorie = document.getElementById('ingredient_form_categorie').value;
    if (!nomInscrit || nomInscrit !== '') {
        if (!valueCategorie || valueCategorie !== '0') {
            let indexCat = parseInt(valueCategorie) - 1;
            searchIngredient(nomInscrit, indexCat);
        }
    }
}

function searchIngredient(texte, index) {
    let input = document.getElementById('verifNomNewIngredient');
    let label = document.getElementById('labelNom');
    let boutonAjout = document.getElementById('boutonAjouter');
    let nomExistant = document.getElementById('nomExistant');

    // Supprimer le surlignement de l'ingrédient si il a été trouvé avant
    let elementsSurlignes = document.querySelectorAll('.bg-yellow-300');
    elementsSurlignes.forEach(function(element) {
        element.classList.remove('bg-yellow-300');
    });

    if (input.classList.contains('border-red-700') && boutonAjout.classList.contains('hidden') && !nomExistant.classList.contains('hidden') && label.classList.contains('text-red-700')) {
        nomExistant.classList.add('hidden');
        boutonAjout.classList.remove('hidden');
        input.classList.remove('border-4');
        input.classList.replace
        ('border-red-700', 'border-gray-200');
        input.classList.replace
        ('text-red-700', 'text-gray-700');
        label.classList.remove('text-red-700');
    }

    if (texte !== '') { // Input plein
        if (index !== '0') { // Une catégorie sélectionnée

            for (let h = 0; h < categories[index].length; h++) {
                if (categories[index][h].id.toLowerCase() === texte.toLowerCase()) {
                    document.getElementById(categories[index][h].id).classList.add('bg-yellow-300');
                    nomExistant.classList.remove('hidden');
                    boutonAjout.classList.add('hidden');
                    input.classList.add('border-4');
                    input.classList.replace
                    ('border-gray-200', 'border-red-700');
                    input.classList.replace
                    ('text-gray-700', 'text-red-700');
                    label.classList.add('text-red-700');
                }
            }

        }
    }
}

