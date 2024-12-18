// Prevent Bootstrap dialog from blocking focusin
document.addEventListener('focusin', (e) => {
    if (e.target.closest(".tox-tinymce, .tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
        e.stopImmediatePropagation();
    }
});
// Example starter JavaScript for disabling form submissions if there are invalid fields
(() => {
    'use strict'

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    const forms = document.querySelectorAll('.needs-validation')

    // Loop over them and prevent submission
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }

            form.classList.add('was-validated')
        }, false)
    })
})()

document.addEventListener("DOMContentLoaded", () => {
    // Get the select element and the additional input field
    const selectBox = document.getElementById('possibleParticipation');
    const additionalInput = document.getElementById('joiningDateField');

// Listen for changes in the dropdown
    selectBox?.addEventListener('change', function() {
        // Check if the selected value is "other"
        if (this.value !== 'IMMEDIATELY') {
            additionalInput.style.display = 'block'; // Show the input field
        } else {
            additionalInput.style.display = 'none';  // Hide the input field
        }
    });
})

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("progressForm");
    const progressBar = document.getElementById("progressBar");

    // Select all input types, select, textarea in the form
    const formElements = form?.querySelectorAll("input, select, textarea");
    const totalElements = formElements?.length;

    // Function to calculate progress
    function updateProgressBar() {
        let filledCount = 0;

        formElements.forEach(element => {
            switch (element.type) {
                case "checkbox":
                case "radio":
                    // Check if any checkbox or radio in its group is checked
                    const isChecked = document.querySelectorAll(`[name="${element.name}"]:checked`).length > 0;
                    if (isChecked) filledCount++;
                    break;

                case "file":
                    // File input is filled if a file is selected
                    if (element.files.length > 0) filledCount++;
                    break;

                default:
                    // For other inputs (text, select, textarea)
                    if (element.value.trim() !== "") filledCount++;
            }
        });

        // Calculate progress percentage
        const progress = Math.round((filledCount / totalElements) * 100);

        // Update progress bar
        progressBar.style.width = progress + "%";
        progressBar.setAttribute("aria-valuenow", progress);
        progressBar.textContent = progress + "%";
    }

    // Attach the update function to all input events
    formElements.forEach(element => {
        element.addEventListener("input", updateProgressBar);
        element.addEventListener("change", updateProgressBar); // For file, checkbox, and radio
    });

    // Run the function initially to account for pre-filled values
    updateProgressBar();
});
