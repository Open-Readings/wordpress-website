const institutions = {
    "1": "Adam Mickiewicz University in Poznan",
    "2": "Aix-Marseille University",
    "3": "Amity University",
    "4": "Association Club Vanguard",
    "5": "Azerbaijan Medical University",
    "6": "Batumi Shota Rustaveli State University",
    "7": "Bauman Moscow State Technical University",
    "8": "Belarusian Medical Academy of Postgraduate Education",
    "9": "Belarusian State University",
    "10": "Belgorod State National Research University",
    "11": "Blue Marble Space Institute of Science",
    "12": "Catholic University of Louvain (UCLouvain)",
    "13": "Center for Innovative Medicine of Lithuania",
    "14": "Center for Physical Sciences and Technology (FTMC)",
    "15": "Center for Theoretical Particle Physics of Portugal",
    "16": "Complutense University of Madrid",
    "17": "Coventry University",
    "18": "Department of Chemistry, Biochemistry and Environmental Protection of Serbia",
    "19": "Dublin City University",
    "20": "DY Patil University",
    "21": "Federal University of Agriculture of Abeokuta",
    "22": "Federal University of Rio Grande",
    "23": "Galway-Mayo Institute of Technology (GMIT)",
    "24": "Gazi University",
    "25": "Ilmenau University of Technology (TU Ilmenau)",
    "26": "Institute of Hematology and Transfusion Medicine of Poland",
    "27": "Institute of Low Temperature and Structure Research (INTiBS)",
    "28": "Italian National Research Council (CNR)",
    "29": "ITMO University",
    "30": "Ivane Javakhishvili Tbilisi State University",
    "31": "Jagiellonian University",
    "32": "Kaunas College",
    "33": "Kaunas University of Technology",
    "34": "Khwaja Fareed University of Engineering and Information Technology",
    "35": "Kwame Nkrumah University of Science and Technology",
    "36": "Kyiv National University of Technologies and Design",
    "37": "Lithuanian Energy Institute",
    "38": "Lithuanian Research Centre for Agriculture and Forestry",
    "39": "Lithuanian University of Health Sciences (LSMU)",
    "40": "Lviv Polytechnic National University",
    "41": "M.V. Lomonosov Moscow State University",
    "42": "Malmo University",
    "43": "Maynooth University",
    "44": "National Academy of Medical Sciences of Ukraine",
    "45": "National Academy of Sciences of Belarus",
    "46": "National Academy of Sciences of Ukraine",
    "47": "National Cancer Institute of Lithuania",
    "48": "National Cancer Institute of Ukraine",
    "49": "National Technical University of Ukraine",
    "50": "National University of Food Technologies of Ukraine",
    "51": "National University of Ireland",
    "52": "Nature Research Center of Lithuania",
    "53": "No affiliation, private research",
    "54": "Olabisi Onbanjo University",
    "55": "Oles Honchar Dnipro National University",
    "56": "Peter the Great St. Petersburg Polytechnic University",
    "57": "Polish Academy of Sciences",
    "58": "Poznan University of Technology",
    "59": "Qilu University of Technology",
    "60": "RUDN University",
    "61": "Russian Academy of Sciences (INASAN)",
    "62": "Sikkim Manipal University",
    "63": "Silesian University of Technology",
    "64": "Smolensk State Medical University",
    "65": "Smolensk State University",
    "66": "Taras Shevchenko National University of Kyiv",
    "67": "Technical University in Zvolen",
    "68": "Technical University of Liberec",
    "69": "The Republican Scientific and Practical Center for Pediatric Surgery of Belarus",
    "70": "The State Forensic Medicine Service of Lithuania",
    "71": "Tribhuvan University",
    "72": "UAB Ekspla",
    "73": "UAB Femtika",
    "74": "UAB Nanoversa",
    "75": "UAB Sanpharm",
    "76": "University College London",
    "77": "University Mohammed V",
    "78": "University of Abuja",
    "79": "University of Amsterdam",
    "80": "University of Aveiro",
    "81": "University of Banja Luka",
    "82": "University of Belgrade",
    "83": "University of Bonn",
    "84": "University of Bordeaux",
    "85": "University of Bucharest",
    "86": "University of Chemical Technology and Metallurgy",
    "87": "University of Copenhagen",
    "88": "University of Duhok",
    "89": "University of Environmental and Life Sciences",
    "90": "University of Genoa",
    "91": "University of Ghana",
    "92": "University of Granada",
    "93": "University of Graz",
    "94": "University of Groningen",
    "95": "University of Lagos",
    "96": "University of Latvia",
    "97": "University of Lille",
    "98": "University of Lisabona",
    "99": "University of Lodz",
    "100": "University of Novi Sad",
    "101": "University of Oviedo",
    "102": "University of Patras",
    "103": "University of Sarajevo",
    "104": "University of South Bohemia",
    "105": "University of St Andrews",
    "106": "University of Strathclyde",
    "107": "University of Tartu",
    "108": "University of the Aegean",
    "109": "University of Warsaw",
    "110": "University of Zaragoza",
    "111": "Utenos College",
    "112": "Uzhgorod National University",
    "113": "V.N. Karazin Kharkiv National University",
    "114": "Vasyl Stus Donetsk National University",
    "115": "Vilnius Tech",
    "116": "Vilnius University",
    "117": "Vytautas Magnus University",
    "118": "Warsaw University of Technology",
    "119": "Weill Cornell Medicine of Cornell University",
    "120": "West University of Timisoara",
    "121": "Wroclaw Medical University",
    "122": "Wroclaw University",
    "123": "Wroclaw University of Environmental and Life Sciences",
    "124": "Wroclaw University of Science and Technology"
}

function institutionInputChange() {
    removeInstitutionDropdown();
    const value = institutionInputElement.value.toLowerCase();

    if (value.length < 4) return;
    const filteredNames = [];
    Object.values(institutions).forEach(name => {
        if (name.toLowerCase().includes(value)) {
            filteredNames.push(name);
        }
    });

    createInstitutionDropdown(filteredNames);
}

function createInstitutionDropdown(list) {
    const listEl = document.createElement("ul");
    listEl.className = 'registration-selection';
    listEl.id = 'registration-li';
    for (let i = 0; i < 40 && i < list.length; i++) {
        const listItem = document.createElement("li");
        const institutionButton = document.createElement("button");
        institutionButton.className = 'registration-dropdown-element';
        institutionButton.innerHTML = list[i];
        institutionButton.addEventListener("click", onInstitutionClick)
        listItem.appendChild(institutionButton);
        listEl.appendChild(listItem);
    }

    document.getElementById("institution-wrapper").appendChild(listEl);
}

function removeInstitutionDropdown() {
    const listEl = document.getElementById('registration-li');
    if (listEl) listEl.remove();
}

function onInstitutionClick(e) {
    e.preventDefault();
    const buttonEl = e.target;
    institutionInputElement.value = buttonEl.innerHTML;
    removeInstitutionDropdown();
    check_institution();
}

const myArray = Object.keys(institutions).map(key => institutions[key]);

function check_institution() {
    var instField = document.getElementById('institution-field');
    if (myArray.includes(instField.value)) {
        instField.style.backgroundColor = '#8f8';
    } else {
        instField.style.backgroundColor = '#f88';
    }
}