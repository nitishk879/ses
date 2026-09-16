// Prevent Bootstrap dialog from blocking focusin
document.addEventListener('focusin', (e) => {
    if (e.target.closest(".tox-tinymce, .tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
        e.stopImmediatePropagation();
    }
});
// Client-side validation for the long registration forms.
//
// This was the Bootstrap docs' starter snippet: block the submit, add
// .was-validated, done. On a short demo form that is enough, because the red
// field is on screen. These forms are several screens tall, so the first
// invalid control — Affiliation, say — sits far above the Submit button the
// person just clicked. Nothing moved, nothing was said, and the form appeared
// to be broken rather than incomplete.
//
// So a failed submit now has to answer "what is wrong and where": say how many
// fields need attention, then take the person to the first one.
(() => {
    'use strict'

    /** Controls the browser considers invalid, in document order. */
    const invalidControls = (form) =>
        Array.from(form.querySelectorAll('input, select, textarea'))
            .filter(el => !el.disabled && el.willValidate && !el.checkValidity())

    /**
     * The message lives on the form as a data attribute because this file is a
     * compiled bundle with no access to the translation catalogue, and the app
     * is served in both English and Japanese.
     */
    const announce = (form, count) => {
        const template = form.dataset.invalidMessage
        if (!template) return null

        let box = form.querySelector('[data-validation-summary]')
        if (!box) {
            box = document.createElement('div')
            box.setAttribute('data-validation-summary', '')
            box.className = 'alert alert-danger mt-3'
            box.setAttribute('role', 'alert')
            box.setAttribute('aria-live', 'assertive')
            const submit = form.querySelector('[type="submit"]')
            // Next to the button that was just pressed, which is where the
            // person is looking, not at the top of a page they cannot see.
            ;(submit?.parentElement ?? form).appendChild(box)
        }
        box.textContent = template.replace(':count', String(count))
        return box
    }

    Array.from(document.querySelectorAll('.needs-validation')).forEach(form => {
        form.addEventListener('submit', event => {
            form.classList.add('was-validated')

            if (form.checkValidity()) {
                form.querySelector('[data-validation-summary]')?.remove()
                return
            }

            event.preventDefault()
            event.stopPropagation()

            const invalid = invalidControls(form)
            announce(form, invalid.length)

            const first = invalid[0]
            if (!first) return

            // Scroll before focus: focus() alone jumps the page with no sense of
            // movement, and on a form this long that reads as a glitch.
            first.scrollIntoView({ behavior: 'smooth', block: 'center' })
            // A select2-hidden original cannot take focus; focus its visible proxy.
            const visible = first.offsetParent !== null
                ? first
                : first.closest('.mb-3, .col-md-6, .col-md-12')?.querySelector('input, button, [tabindex]')
            setTimeout(() => (visible ?? first).focus({ preventScroll: true }), 300)
        }, false)

        // Once a person starts fixing things, the count is stale. Drop it and
        // let the next submit recount rather than showing an old number.
        form.addEventListener('input', () => {
            form.querySelector('[data-validation-summary]')?.remove()
        })
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

    // Select all visible input types, select, textarea in the form
    const formElements = Array.from(form?.querySelectorAll("input, select, textarea"));
    const visibleElements = formElements.filter(el => el.type !== "hidden" && el.type !== "submit");

    const totalElements = visibleElements.length;

    // Function to calculate progress
    function updateProgressBar() {
        if (totalElements === 0) {
            progressBar.style.width = "0%";
            progressBar.setAttribute("aria-valuenow", "0");
            progressBar.classList.remove("bg-success", "bg-warning");
            progressBar.textContent = "0%";
            return;
        }

        let filledCount = 0;

        visibleElements.forEach(element => {
            switch (element.type) {
                case "checkbox":
                case "radio":
                    // Count a group as filled if at least one option is checked
                    if (document.querySelectorAll(`[name="${element.name}"]:checked`).length > 0) {
                        filledCount++;
                    }
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
        const bgColor = progress >= 50 ? "bg-success" : "bg-warning";

        // Update progress bar
        progressBar.style.width = progress + "%";
        progressBar.setAttribute("aria-valuenow", progress);
        progressBar.classList.remove("bg-warning");
        progressBar.classList.add(bgColor);
        progressBar.textContent = progress + "%";
    }

    // Attach the update function to all input events
    visibleElements.forEach(element => {
        element.addEventListener("input", updateProgressBar);
        element.addEventListener("change", updateProgressBar); // For file, checkbox, and radio
    });

    // Run the function initially to account for pre-filled values
    updateProgressBar();
});
