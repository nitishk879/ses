tinymce.init({
    selector: 'textarea.tinyEditor',  // change this value according to your HTML
    license_key: '5rfctkmlgw069tt5rp30sjxqu32fzlox611u5kxk18tm9g0k',
    height: 220
});
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
function openDynamicModal(id) {
    // Ensure elements exist
    const modalLabel = document.getElementById('staticBackdropLabel');
    const modalBody = document.querySelector('.cover-letter-description');
    const modalElement = document.getElementById('staticBackdrop');

    if (!modalLabel || !modalBody || !modalElement) {
        console.error('Modal elements are not found in the DOM.');
        return;
    }
    axios.get('/sample/' + id)
        .then(response => {
            const data = response.data;
            // Set the modal title and content dynamically
            modalLabel.textContent = data.title;
            modalBody.innerHTML = data.content;
        })
        .catch(error => {
            console.error('There was an errors fetching the data!', error);
            alert('Failed to fetch data.');
        });
}

// Get the select element and the additional input field
const selectBox = document.getElementById('possibleParticipation');
const additionalInput = document.getElementById('joiningDateField');

// Listen for changes in the dropdown
selectBox.addEventListener('change', function() {
    // Check if the selected value is "other"
    if (this.value !== 'IMMEDIATELY') {
        additionalInput.style.display = 'block'; // Show the input field
    } else {
        additionalInput.style.display = 'none';  // Hide the input field
    }
});
