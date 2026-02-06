// var id = affiliation_ajax.id;
var affAddButtons = document.querySelectorAll('.aff-add');

affAddButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        const affiliationList = document.getElementById("affList");
        const affiliationField = document.createElement("div");
        var childDivs = affiliationList.querySelectorAll("div");
        var divCount = childDivs.length;
        affiliationField.innerHTML = `<label class="aff-label">` + (divCount + 1) + `.` + `</label>` +
            `<input type="text" class="aff-width form-padding elementor-field elementor-field-textual" name="affiliation[]" placeholder="(e.g. Vilnius University, Faculty of Physics, Institute of Chemical Physics, Lithuania)" >
        `;
        affiliationField.className = "aff-div";
        if (divCount < 10)
            affiliationList.appendChild(affiliationField);
    });
});

var affRemButtons = document.querySelectorAll('.aff-rem');

affRemButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        var id = button.getAttribute('data-field-id');
        const formFields = document.getElementById("affList");
        var childDivs = formFields.querySelectorAll("div");
        var divCount = childDivs.length;

        if (formFields.lastChild && divCount > 1) {
            formFields.removeChild(formFields.lastChild);
        }
    });
});

const researchAreaField = document.getElementById("form-field-research_area");
  const selectWrapper = researchAreaField.closest(".elementor-field-group-research_area");
  

  // Hide the old dropdown
  // selectWrapper.style.display = "none";

  // Create main container
  const accordionContainer = document.createElement("div");
  const labelHeader = document.createElement("label");
  labelHeader.textContent = "Research Areas (you may select multiple):"; // 🧭 your header text
  labelHeader.style.display = "block";
  labelHeader.className = "elementor-field-label";
  labelHeader.htmlFor = "research-accordion-container"; // optional, for accessibility

// Insert the label into the accordion container
accordionContainer.appendChild(labelHeader);
  accordionContainer.id = "research-accordion-container";
  accordionContainer.className = "elementor-field-group elementor-field-type-checkbox";
  accordionContainer.style.marginTop = "10px";
  accordionContainer.style.width = "100%";
  selectWrapper.insertAdjacentElement("beforebegin", accordionContainer);

  // Subareas mapping (without "Other")
  const subareas = {
    "Lasers and Optical Technologies": [
      "Laser Physics", "High Intensity Lasers", "X-Ray Lasers", "Laser Processing", "Laser Induced Damage",
      "Ultrafast Lasers", "Nonlinear Optics", "Photonics and Fibers", "LIBS", "Lenses and Mirrors",
      "Optical Coatings", "Metrology"
    ],
    "Spectroscopy and Imaging": [
      "UV/VIS", "Infrared", "Raman", "THZ Imaging", "XRD", "Optical Microscopy", "Atomic Force Microscopy",
      "Electron Microscopy", "Nuclear Magnetic Resonance", "Photoacoustic Imaging",
      "Ultrasonic Spectroscopy", "Microwave Spectroscopy"
    ],
    "Astrophysics and Theoretical Physics": [
      "Astrophysics and Astronomy", "Quantum Physics", "Theoretical Physics",
      "Particle Physics", "Simulations"
    ],
    "Biology and Medicine": [
      "Biophysics", "Biochemistry", "Genetics", "DNR/RNA", "Cancer", "Diseases", "Viruses",
      "Cells", "Proteins", "Microorganisms", "Lipids", "Pharmaceuticals", "Neuroscience",
      "CRISPR", "Biomarkers"
    ],
    "Chemistry": [
      "Chemical Physics", "Organic Synthesis", "Inorganic Synthesis", "Electrochemistry", "Catalysis"
    ],
    "Materials Science and Nanotechnology": [
      "Semiconductor Growth Technologies", "Carrier Transport", "LED’s and OLED’s", "Photovoltaics",
      "Perovskites", "Ceramics", "Fuel Cells", "Detectors and Sensors", "Photoluminescence",
      "Micro and Nano Structures", "Graphene Based Materials", "Nanotubes", "Thin Films", "Polymers", "Metal-Organic Frameworks"
    ],
    "Ecology, Geology and Environmental Sciences": [
      "Ecology", "Geology", "Agriculture", "Aquatic Ecology", "Pollution", "Climatology",
      "Fungi", "Forests", "Allelopathy", "Microplastics", "Soil Studies", "Minerology",
      "Hydrology", "Geochemistry", "Geochronology", "Geomorphology", "Paleontology"
    ],
    "Applied Electrodynamics": [
      "Dielectrics", "Nanoionics", "Telecommunication",
      "THz Sensors and Emitters", "Noise Characterization", "Electromagnetism"
    ]
  };

  // Build collapsible sections
  Object.entries(subareas).forEach(([area, subs]) => {
    const section = document.createElement("div");
    section.className = "accordion-section";
    section.style.marginBottom = "4px";
    section.style.border = "1px solid #ddd";
    section.style.borderRadius = "6px";
    section.style.overflow = "hidden";
    section.style.background = "#f9f9f9";

    // Header button
    const header = document.createElement("button");
    header.type = "button";
    header.className = "accordion-header";
    header.textContent = "+ " + area;
    header.style.width = "100%";
    header.style.textAlign = "left";
    header.style.padding = "2px 6px";
    header.style.fontWeight = "600";
    header.style.border = "none";
    header.style.background = "transparent";
    header.style.cursor = "pointer";
    header.style.color = "#222";
    header.style.lineHeight = "1.2";
    header.style.margin = "0";
    header.style.display = "flex";
    header.style.alignItems = "center";

    // Content
    const content = document.createElement("div");
    content.className = "accordion-content";
    content.style.display = "none";
    content.style.padding = "4px 10px";
    content.style.background = "#fff";

    subs.forEach(sub => {
      const label = document.createElement("label");
      label.style.display = "block";
      label.style.padding = "1px 0";
      const checkbox = document.createElement("input");
      checkbox.type = "checkbox";
      checkbox.name = "form_fields[research_subarea][]";
      checkbox.value = sub;
      label.appendChild(checkbox);
      label.append(" " + sub);
      content.appendChild(label);
    });

    // Toggle open/close
    header.addEventListener("click", () => {
      const isOpen = content.style.display === "block";
      document.querySelectorAll(".accordion-content").forEach(c => c.style.display = "none");
      document.querySelectorAll(".accordion-header").forEach(h => h.textContent = "+ " + h.textContent.replace(/^[-−+]\s*/, ""));
      if (!isOpen) {
        content.style.display = "block";
        header.textContent = "− " + area;
      }
    });

    section.appendChild(header);
    section.appendChild(content);
    accordionContainer.appendChild(section);
  });

  // ✅ Add single "Other Research Area" section
  const otherSection = document.createElement("div");
  otherSection.className = "accordion-section";
  otherSection.style.border = "1px solid #ddd";
  otherSection.style.borderRadius = "6px";
  otherSection.style.overflow = "hidden";
  otherSection.style.background = "#f9f9f9";

  const otherHeader = document.createElement("button");
  otherHeader.type = "button";
  otherHeader.className = "accordion-header";
  otherHeader.textContent = "+ Other Research Area";
  otherHeader.style.width = "100%";
  otherHeader.style.textAlign = "left";
  otherHeader.style.padding = "2px 6px";
  otherHeader.style.fontWeight = "600";
  otherHeader.style.border = "none";
  otherHeader.style.background = "transparent";
  otherHeader.style.cursor = "pointer";
  otherHeader.style.color = "#222";
  otherHeader.style.lineHeight = "1.2";
  otherHeader.style.margin = "0";
  otherHeader.style.display = "flex";
  otherHeader.style.alignItems = "center";

  const otherContent = document.createElement("div");
  otherContent.className = "accordion-content";
  otherContent.style.display = "none";
  otherContent.style.padding = "6px 10px";
  otherContent.style.background = "#fff";

// Hidden "Other" checkbox
const otherCheckbox = document.createElement("input");
otherCheckbox.type = "checkbox";
otherCheckbox.name = "form_fields[research_subarea][]";
otherCheckbox.value = "Other";
otherCheckbox.style.display = "none"; // keep it hidden
otherContent.appendChild(otherCheckbox);

// Visible text input
const otherField = document.createElement("input");
otherField.type = "text";
otherField.name = "form_fields[research_subarea][]";
otherField.placeholder = "Please specify your research area";
otherField.className = "elementor-field elementor-size-sm elementor-field-textual elementor-column";
otherField.style.width = "100%";
otherContent.appendChild(otherField);

// Automatically manage hidden checkbox
otherField.addEventListener("input", () => {
  const hasText = otherField.value.trim().length > 0;
  otherCheckbox.checked = hasText; // ✅ if user types → checkbox checked
});

  otherHeader.addEventListener("click", () => {
    const isOpen = otherContent.style.display === "block";
    document.querySelectorAll(".accordion-content").forEach(c => c.style.display = "none");
    document.querySelectorAll(".accordion-header").forEach(h => h.textContent = "+ " + h.textContent.replace(/^[-−+]\s*/, ""));
    if (!isOpen) {
      otherContent.style.display = "block";
      otherHeader.textContent = "− Other Research Area";
    }
  });

  otherSection.appendChild(otherHeader);
  otherSection.appendChild(otherContent);
  accordionContainer.appendChild(otherSection);

// function addAffiliation() {
//     const affiliationList = document.getElementById(id + "-List");
//     const affiliationField = document.createElement("div");
//     var childDivs = affiliationList.querySelectorAll("div");
//     var divCount = childDivs.length;
//     affiliationField.innerHTML = `<label>` + (divCount+1) + `. ` + `</label>` +
//     `<input type="text" name="aff-` + id + `[]" placeholder="Affiliation">
//     `;
//     affiliationList.appendChild(affiliationField);
// }

// function removeAffiliation(){
//     const formFields = document.getElementById(id + "-List");
//     var childDivs = formFields.querySelectorAll("div");
//     var divCount = childDivs.length;

//         if (formFields.lastChild && divCount > 1) {
//             formFields.removeChild(formFields.lastChild);
//         }
// }