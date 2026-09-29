{{--
    The sample-input dialog and the rich-text editor, shared by create and edit.

    Pulled out alongside projects/_form for the same reason: the fields are
    useless without the editor that renders them and the dialog their "sample
    input" buttons open, and a second copy would be the one that stops matching.
--}}
    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="d-flex justify-content-center align-items-center w-100">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">{{ __("common/common.sample_data_title") }}</h1>
                </div>
                <div class="modal-body">
                    <div class="cover-letter ">
                        <h4 id="modalTitle">{{ __("common/common.modal_title") }}</h4>
                        <div class="cover-letter-description border border-secondary-subtle p-4"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("common/common.close") }}</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{{ __("common/common.use") }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <!---- Summer note libraries -->
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>
        <script>
            // One init per editor, not one for the whole page.
            //
            // `$('.tinyEditor').summernote({placeholder: ...})` applies a single
            // string to every box it matches, and Summernote hides the original
            // textarea — so the per-field `placeholder` attribute set in the
            // markup was never the one on screen. Both boxes showed the talent
            // profile's "enter your resume" text, on a form about a project.
            // Reading each element's own attribute keeps the wording next to the
            // field it belongs to.
            $('.tinyEditor').each(function () {
                $(this).summernote({
                    placeholder: $(this).attr('placeholder') || '',
                    tabsize: 2,
                    height: 120,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture', 'video']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ]
                });
            });
        </script>
        <!---- Summer note libraries -->
        <!-- Add Axios via CDN (optional if not already included) -->
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <!-- Modal Popup call -->
        <script>
            function openDynamicModal(id) {
                // Make an Axios request to fetch data for the modal
                axios.get('/sample/' + id)
                    .then(response => {
                        const data = response.data;
                        console.log(data);
                        // Set the modal title and content dynamically
                        document.getElementById('staticBackdropLabel').textContent = data.title;
                        document.getElementById('modalTitle').textContent = data.title;
                        document.querySelector('.cover-letter-description').innerHTML = data.content;
                    })
                    .catch(error => {
                        console.error('There was an error fetching the data!', error);
                        alert('Failed to fetch data.');
                    });
            }
        </script>
        <!-- Modal Popup call -->
    @endpush
