function initContact() {
    document.body.style.overflow = 'hidden'; // Désactive le défilement de la page

    document.addEventListener('wheel', function (event) {
        if (event.deltaY > 0) {
            // Scrolling down
            scrollToSection(currentSection + 1);
        } else {
            // Scrolling up
            scrollToSection(currentSection - 1);
        }
    });

    document.getElementById('imgSection1').addEventListener('click', function () {
        scrollToSection(2);
    });

    document.getElementById('imgSection2').addEventListener('click', function () {
        scrollToSection(3);
    });
}

window.initContact = initContact;


let currentSection = 1;
function scrollToSection(sectionNumber) {
    const totalSections = 3;

    if (sectionNumber >= 1 && sectionNumber <= totalSections) {
        currentSection = sectionNumber;
        const sectionId = 'section' + currentSection;
        const section = document.getElementById(sectionId);

        if (section) {
            const topMargin = 70;
            const offset = section.getBoundingClientRect().top + window.scrollY - topMargin;

            window.scrollTo({
                top: offset,
                behavior: 'smooth'
            });
        }
    }
}